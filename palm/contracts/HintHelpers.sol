// SPDX-License-Identifier: UNLICENSED

pragma solidity 0.6.11;

import "./Interfaces/IAssetPortfolioManager.sol";
import "./Interfaces/ISortedAssetPortfolios.sol";
import "./Interfaces/IPalmController.sol";
import "./Dependencies/LiquityBase.sol";
import "./Dependencies/Ownable.sol";

/** 
 * Hint helpers is a contract for giving approximate insert positions for a assetPortfolio after
 * an operation, such as partial redemption re-insert, adjust assetPortfolio, etc.
 */

contract HintHelpers is LiquityBase, Ownable {
    bytes32 constant public NAME = "HintHelpers";

    ISortedAssetPortfolios internal sortedAssetPortfolios;
    IAssetPortfolioManager internal assetPortfolioManager;

    // --- Events ---


    // --- Dependency setters ---

    function setAddresses(
        address _sortedAssetPortfoliosAddress,
        address _assetPortfolioManagerAddress,
        address _controllerAddress
    )
        external
        onlyOwner
    {
        sortedAssetPortfolios = ISortedAssetPortfolios(_sortedAssetPortfoliosAddress);
        assetPortfolioManager = IAssetPortfolioManager(_assetPortfolioManagerAddress);
        controller = IPalmController(_controllerAddress);

        _renounceOwnership();
    }

    // --- Functions ---

    /* getRedemptionHints() - Helper function for finding the right hints to pass to redeemCollateral().
     *
     * It simulates a redemption of `_PUSTamount` to figure out where the redemption sequence will start and what state the final AssetPortfolio
     * of the sequence will end up in.
     *
     * Returns three hints:
     *  - `firstRedemptionHint` is the address of the first AssetPortfolio with AICR >= MCR (i.e. the first AssetPortfolio that will be redeemed).
     *  - `partialRedemptionHintAICR` is the final AICR of the last AssetPortfolio of the sequence after being hit by partial redemption,
     *     or zero in case of no partial redemption.
     *  - `truncatedPUSTamount` is the maximum amount that can be redeemed out of the the provided `_PUSTamount`. This can be lower than
     *    `_PUSTamount` when redeeming the full amount would leave the last AssetPortfolio of the redemption sequence with less net debt than the
     *    minimum allowed value (i.e. MIN_NET_DEBT).
     *
     * The number of AssetPortfolios to consider for redemption can be capped by passing a non-zero value as `_maxIterations`, while passing zero
     * will leave it uncapped.
     */


    function getRedemptionHints(
        uint _PUSTamount,
        uint _maxIterations
    )
        external
        view
        returns (
            address firstRedemptionHint,
            uint partialRedemptionHintAICR,
            uint truncatedPUSTamount
        )
    {
        ISortedAssetPortfolios sortedAssetPortfoliosCached = sortedAssetPortfolios;

        uint remainingPUST = _PUSTamount;
        address currentAssetPortfoliouser = sortedAssetPortfoliosCached.getLast();

        while (currentAssetPortfoliouser != address(0) && sortedAssetPortfoliosCached.getOldBoostedAICR(currentAssetPortfoliouser) < MCR) {
            currentAssetPortfoliouser = sortedAssetPortfoliosCached.getPrev(currentAssetPortfoliouser);
        }

        firstRedemptionHint = currentAssetPortfoliouser;

        if (_maxIterations == 0) {
            _maxIterations = uint(-1);
        }

        while (currentAssetPortfoliouser != address(0) && remainingPUST != 0 && _maxIterations-- != 0) {
            uint netPUSTDebt = _getNetDebt(assetPortfolioManager.getAssetPortfolioDebt(currentAssetPortfoliouser))
                .add(assetPortfolioManager.getPendingPUSTDebtReward(currentAssetPortfoliouser));

            if (netPUSTDebt > remainingPUST) { // Partial redemption
                if (netPUSTDebt > MIN_NET_DEBT) { // MIN NET DEBT = 1800
                    uint maxRedeemablePUST = PalmMath._min(remainingPUST, netPUSTDebt.sub(MIN_NET_DEBT));

                    uint newColl = _calculateRVCAfterRedemption(currentAssetPortfoliouser, maxRedeemablePUST);
                    uint newDebt = netPUSTDebt.sub(maxRedeemablePUST);

                    uint compositeDebt = _getCompositeDebt(newDebt);
                    partialRedemptionHintAICR = _computeCR(newColl, compositeDebt);

                    remainingPUST = remainingPUST.sub(maxRedeemablePUST);
                }
                break;
            } else { // Full redemption in this case
                remainingPUST = remainingPUST.sub(netPUSTDebt);
            }

            currentAssetPortfoliouser = sortedAssetPortfoliosCached.getPrev(currentAssetPortfoliouser);
        }

        truncatedPUSTamount = _PUSTamount.sub(remainingPUST);
    }

    // Function for calculating the RVC of a assetPortfolio after a redemption, since the value is given out proportionally to the
    // USD Value of the collateral. Same function is used in AssetPortfolioManagerRedemptions for the same purpose.
    function _calculateRVCAfterRedemption(address _borrower, uint _PUSTAmount) internal view returns (uint newCollRVC) {
        newColls memory colls;
        (colls.tokens, colls.amounts, ) = assetPortfolioManager.getCurrentAssetPortfolioState(_borrower);

        uint256[] memory finalAmounts = new uint256[](colls.tokens.length);

        uint totalCollUSD = _getUSDColls(colls);
        uint baseLot = _PUSTAmount.mul(DECIMAL_PRECISION);

        // redemption addresses are the same as coll addresses for assetPortfolio
        uint256 tokensLen = colls.tokens.length;
        for (uint256 i; i < tokensLen; ++i) {
            uint tokenAmount = colls.amounts[i];
            uint tokenAmountToRedeem = baseLot.mul(tokenAmount).div(totalCollUSD).div(DECIMAL_PRECISION);
            finalAmounts[i] = tokenAmount.sub(tokenAmountToRedeem);
        }

        newCollRVC = _getRVC(colls.tokens, finalAmounts);
    }


    /* getApproxHint() - return address of a AssetPortfolio that is, on average, (length / numTrials) positions away in the
    sortedAssetPortfolios list from the correct insert position of the AssetPortfolio to be inserted.
    
    Note: The output address is worst-case O(n) positions away from the correct insert position, however, the function 
    is probabilistic. Input can be tuned to guarantee results to a high degree of confidence, e.g:

    Submitting numTrials = k * sqrt(length), with k = 15 makes it very, very likely that the ouput address will 
    be <= sqrt(length) positions away from the correct insert position.
    */
    function getApproxHint(uint _CR, uint _numTrials, uint _inputRandomSeed)
        external
        view
        returns (address hintAddress, uint diff, uint latestRandomSeed)
    {
        uint arrayLength = assetPortfolioManager.getAssetPortfolioOwnersCount();

        if (arrayLength == 0) {
            return (address(0), 0, _inputRandomSeed);
        }

        hintAddress = sortedAssetPortfolios.getLast();
        diff = PalmMath._getAbsoluteDifference(_CR, sortedAssetPortfolios.getOldBoostedAICR(hintAddress));
        latestRandomSeed = _inputRandomSeed;

        uint i = 1;

        while (i < _numTrials) {
            latestRandomSeed = uint(keccak256(abi.encodePacked(latestRandomSeed)));

            uint arrayIndex = latestRandomSeed % arrayLength;
            address currentAddress = assetPortfolioManager.getAssetPortfolioFromAssetPortfolioOwnersArray(arrayIndex);
            uint currentAICR = sortedAssetPortfolios.getOldBoostedAICR(currentAddress);

            // check if abs(current - CR) > abs(closest - CR), and update closest if current is closer
            uint currentDiff = PalmMath._getAbsoluteDifference(currentAICR, _CR);

            if (currentDiff < diff) {
                diff = currentDiff;
                hintAddress = currentAddress;
            }
            ++i;
        }
    }
}