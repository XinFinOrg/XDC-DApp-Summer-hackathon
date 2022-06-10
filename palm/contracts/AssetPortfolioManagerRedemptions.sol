// SPDX-License-Identifier: UNLICENSED

pragma solidity 0.6.11;

import "./Dependencies/AssetPortfolioManagerBase.sol";
import "./Dependencies/SafeERC20.sol";



/**
 * @notice AssetPortfolioManagerRedemptions is derived from AssetPortfolioManager and handles all redemption activity of assetPortfolios.
 * Instead of calculating redemption fees in ETH like Liquity used to, we now calculate it as a portion
 * of PUST passed in to redeem. The PUSTAmount is still how much we would like to redeem, but the
 * PUSTFee is now the maximum amount of PUST extra that will be paid and must be in the balance of the
 * redeemer for the redemption to succeed. This fee is the same as before in terms of percentage of value,
 * but now it is in terms of PUST. We now use a helper function to be able to estimate how much PUST will
 * be actually needed to perform a redemption of a certain amount, and also given an amount of PUST balance,
 * the max amount of PUST that can be used for a redemption, and a max fee such that it will always go through.
 *
 * Given a balance of PUST, Z, the amount that can actually be redeemed is :
 * Y = PUST you can actually redeem
 * BR = decayed base rate
 * X = PUST Fee
 * S = Total PUST Supply
 * The redemption fee rate is = (Y / S * 1 / BETA + BR + 0.5%)
 * This is because the new base rate = BR + Y / S * 1 / BETA
 * We pass in X + Y = Z, and want to find X and Y.
 * Y is calculated to be = S * (sqrt((1.005 + BR)**2 + BETA * Z / S) - 1.005 - BR)
 * through the quadratic formula, and X = Z - Y.
 * Therefore the amount we can actually redeem given Z is Y, and the max fee is X.
 *
 * To find how much the fee is given Y, we can multiply Y by the new base rate, which is BR + Y / S * 1 / BETA.
 *
 * To the redemption function, we pass in Y and X.
 */

contract AssetPortfolioManagerRedemptions is AssetPortfolioManagerBase, IAssetPortfolioManagerRedemptions {
    bytes32 public constant NAME = "AssetPortfolioManagerRedemptions";

    using SafeERC20 for IPUSTToken;

    IAssetPortfolioManager internal assetPortfolioManager;

    IPUSTToken internal pustTokenContract;

    address internal gasPoolAddress;

    ISortedAssetPortfolios internal sortedAssetPortfolios;

    ICollSurplusPool internal collSurplusPool;

    struct RedemptionTotals {
        uint256 remainingPUST;
        uint256 totalPUSTToRedeem;
        newColls CollsDrawn;
        uint256 PUSTfee;
        uint256 decayedBaseRate;
        uint256 totalPUSTSupplyAtStart;
        uint256 maxPUSTFeeAmount;
    }

    struct SingleRedemptionValues {
        uint256 PUSTLot;
        newColls CollLot;
        uint256 assetPortfolioDebt;
        bool cancelledPartial;
    }

    struct Hints {
        address upper;
        address lower;
        address target;
        uint256 AICR;
    }

    /*
     * BETA: 18 digit decimal. Parameter by which to divide the redeemed fraction, in order to calc the new base rate from a redemption.
     * Corresponds to (1 / ALPHA) in the white paper.
     */
    uint256 public constant BETA = 2;

    bool redemptionsEnabled;

    // The borrower Fee Split is also parameter important for this contract, but it is mutable by timelock through PalmController.sol
    // thorugh function controller.getRedemptionBorrowerFeeSplit()
    // By default it is 20%

    event Redemption(
        uint256 _attemptedPUSTAmount,
        uint256 _actualPUSTAmount,
        uint256 PUSTfee,
        address[] tokens,
        uint256[] amounts
    );

    bool private addressSet;
    function setAddresses(
        address _activePoolAddress,
        address _defaultPoolAddress,
        address _gasPoolAddress,
        address _collSurplusPoolAddress,
        address _pustTokenAddress,
        address _sortedAssetPortfoliosAddress,
        address _controllerAddress,
        address _assetPortfolioManagerAddress
    ) external {
        require(addressSet == false, "Addresses already set");
        addressSet = true;
        activePool = IActivePool(_activePoolAddress);
        defaultPool = IDefaultPool(_defaultPoolAddress);
        controller = IPalmController(_controllerAddress);
        gasPoolAddress = _gasPoolAddress;
        collSurplusPool = ICollSurplusPool(_collSurplusPoolAddress);
        pustTokenContract = IPUSTToken(_pustTokenAddress);
        sortedAssetPortfolios = ISortedAssetPortfolios(_sortedAssetPortfoliosAddress);
        assetPortfolioManager = IAssetPortfolioManager(_assetPortfolioManagerAddress);
    }

    /**
     * @notice Main function for redeeming collateral. See above for how PUSTMaxFee is calculated.
     * @param _PUSTamount is equal to the amount of PUST to actually redeem.
     * @param _PUSTMaxFee is equal to the max fee in PUST that the sender is willing to pay
     * @param _firstRedemptionHint is the hint for the first assetPortfolio to redeem against
     * @param _upperPartialRedemptionHint is the upper hint for reinsertion of last assetPortfolio
     * @param _lowerPartialRedemptionHint is the lower hint for reinsertion of last assetPortfolio
     * @param _partialRedemptionHintAICR is the target hint AICR for the last assetPortfolio redeemed
     * @param _maxIterations is the maximum number of iterations to run the loop
     * @param _redeemer is the redeemer address
     * _PUSTamount + _PUSTMaxFee must be less than the balance of the sender.
     */
    function redeemCollateral(
        uint256 _PUSTamount,
        uint256 _PUSTMaxFee,
        address _firstRedemptionHint,
        address _upperPartialRedemptionHint,
        address _lowerPartialRedemptionHint,
        uint256 _partialRedemptionHintAICR,
        uint256 _maxIterations,
        address _redeemer
    ) external override {
        _requireCallerisAssetPortfolioManager();
        ContractsCache memory contractsCache = ContractsCache(
            activePool,
            defaultPool,
            pustTokenContract,
            sortedAssetPortfolios,
            collSurplusPool,
            gasPoolAddress,
            controller
        );
        RedemptionTotals memory totals;

        _requireValidMaxFee(_PUSTamount, _PUSTMaxFee);
        _requireRedemptionsEnabled();
        _requireTCRoverMCR();
        _requireAmountGreaterThanZero(_PUSTamount);

        totals.totalPUSTSupplyAtStart = getEntireSystemDebt();

        // Confirm redeemer's balance is less than total PUST supply
        require(
            contractsCache.pustToken.balanceOf(_redeemer) <= totals.totalPUSTSupplyAtStart,
            "TMR: redeemer balance too high"
        );

        totals.remainingPUST = _PUSTamount;
        address currentBorrower;
        if (_isValidFirstRedemptionHint(contractsCache.sortedAssetPortfolios, _firstRedemptionHint)) {
            currentBorrower = _firstRedemptionHint;
        } else {
            currentBorrower = contractsCache.sortedAssetPortfolios.getLast();
            // Find the first assetPortfolio with ICR >= MCR
            while (
                currentBorrower != address(0) && assetPortfolioManager.getCurrentAICR(currentBorrower) < MCR
            ) {
                currentBorrower = contractsCache.sortedAssetPortfolios.getPrev(currentBorrower);
            }
        }
        // Loop through the AssetPortfolios starting from the one with lowest collateral ratio until _amount of PUST is exchanged for collateral
        if (_maxIterations == 0) {
            _maxIterations = uint256(-1);
        }
        uint256 borrowerFeeSplit = contractsCache.controller.getRedemptionBorrowerFeeSplit();
        while (currentBorrower != address(0) && totals.remainingPUST != 0 && _maxIterations != 0) {
            _maxIterations--;
            // Save the address of the AssetPortfolio preceding the current one, before potentially modifying the list
            address nextUserToCheck = contractsCache.sortedAssetPortfolios.getPrev(currentBorrower);

            if (assetPortfolioManager.getCurrentAICR(currentBorrower) >= MCR) {
                assetPortfolioManager.applyPendingRewards(currentBorrower);

                SingleRedemptionValues memory singleRedemption = _redeemCollateralFromAssetPortfolio(
                    contractsCache,
                    currentBorrower,
                    _redeemer,
                    totals.remainingPUST,
                    _upperPartialRedemptionHint,
                    _lowerPartialRedemptionHint,
                    _partialRedemptionHintAICR,
                    borrowerFeeSplit
                );

                if (singleRedemption.cancelledPartial) {
                    // Partial redemption was cancelled (out-of-date hint, or new net debt < minimum), therefore we could not redeem from the last AssetPortfolio
                    // The PUST Amount actually redeemed is thus less than the intended amount by some amount. totalPUSTToRedeem holds the correct value
                    // Otherwise totalPUSTToRedeem == _PUSTAmount
                    break;
                }

                totals.totalPUSTToRedeem = totals.totalPUSTToRedeem.add(singleRedemption.PUSTLot);

                totals.CollsDrawn = _sumColls(totals.CollsDrawn, singleRedemption.CollLot);
                totals.remainingPUST = totals.remainingPUST.sub(singleRedemption.PUSTLot);
            }

            currentBorrower = nextUserToCheck;
        }

        require(isNonzero(totals.CollsDrawn), "TMR:noCollsDrawn");
        // Decay the baseRate due to time passed, and then increase it according to the size of this redemption.
        // Use the saved total PUST supply value, from before it was reduced by the redemption.
        _updateBaseRateFromRedemption(totals.totalPUSTToRedeem, totals.totalPUSTSupplyAtStart);

        totals.PUSTfee = _getRedemptionFee(totals.totalPUSTToRedeem);
        uint256 borrowerSplitInPUST = totals
            .totalPUSTToRedeem
            .mul(5e15)
            .div(DECIMAL_PRECISION)
            .mul(contractsCache.controller.getRedemptionBorrowerFeeSplit())
            .div(DECIMAL_PRECISION);
        // check user has enough PUST to pay fee and redemptions
        // Already paid borrower split fee.
        _requirePUSTBalanceCoversRedemption(
            contractsCache.pustToken,
            _redeemer,
            totals.totalPUSTToRedeem.add(totals.PUSTfee).sub(borrowerSplitInPUST)
        );

        // check to see that the fee doesn't exceed the max fee
        _requireUserAcceptsFeeRedemption(totals.PUSTfee, _PUSTMaxFee);

        // send fee from user to PALM stakers and treasury
        _transferAndSplitFee(contractsCache, _redeemer, totals.PUSTfee, borrowerSplitInPUST);

        emit Redemption(
            _PUSTamount,
            totals.totalPUSTToRedeem,
            totals.PUSTfee,
            totals.CollsDrawn.tokens,
            totals.CollsDrawn.amounts
        );
        // Burn the total PUST that is cancelled with debt
        contractsCache.pustToken.burn(_redeemer, totals.totalPUSTToRedeem);
        // Update Active Pool PUST, and send Collaterals to account
        contractsCache.activePool.decreasePUSTDebt(totals.totalPUSTToRedeem);

        contractsCache.activePool.sendCollateralsUnwrap(
            _redeemer,
            totals.CollsDrawn.tokens,
            totals.CollsDrawn.amounts
        );
    }

    /**
     * @notice Secondary function for redeeming collateral. See above for how PUSTMaxFee is calculated.
     *         Redeems one collateral type from only one assetPortfolio. Included for gas efficiency of arbitrages.
     * @param _PUSTamount is equal to the amount of PUST to actually redeem.
     * @param _PUSTMaxFee is equal to the max fee in PUST that the sender is willing to pay
     * @param _target is the hint for the single assetPortfolio to redeem against
     * @param _upperHint is the upper hint for reinsertion of the assetPortfolio
     * @param _lowerHint is the lower hint for reinsertion of the assetPortfolio
     * @param _hintAICR is the target hint AICR for the the assetPortfolio redeemed
     * @param _collToRedeem is the collateral address to redeem. Only this token.
     * _PUSTamount + _PUSTMaxFee must be less than the balance of the sender.
     */
    function redeemCollateralSingle(
        uint256 _PUSTamount,
        uint256 _PUSTMaxFee,
        address _target, // _firstRedemptionHint
        address _upperHint, // _upperPartialRedemptionHint
        address _lowerHint, // _lowerPartialRedemptionHint
        uint256 _hintAICR, // _partialRedemptionHintAICR
        address _collToRedeem,
        address _redeemer
    ) external override {
        _requireCallerisAssetPortfolioManager();
        ContractsCache memory contractsCache = ContractsCache(
            activePool,
            defaultPool,
            pustTokenContract,
            sortedAssetPortfolios,
            collSurplusPool,
            gasPoolAddress,
            controller
        );
        RedemptionTotals memory totals;

        _requireValidMaxFee(_PUSTamount, _PUSTMaxFee);
        _requireRedemptionsEnabled();
        _requireTCRoverMCR();
        _requireAmountGreaterThanZero(_PUSTamount);
        totals.totalPUSTSupplyAtStart = getEntireSystemDebt();

        // Confirm redeemer's balance is less than total PUST supply
        require(
            contractsCache.pustToken.balanceOf(_redeemer) <= totals.totalPUSTSupplyAtStart,
            "TMR:Redeemer PUST Bal too high"
        );

        totals.remainingPUST = _PUSTamount;
        require(
            _isValidFirstRedemptionHint(contractsCache.sortedAssetPortfolios, _target),
            "TMR:Invalid first redemption hint"
        );
        assetPortfolioManager.applyPendingRewards(_target);

        SingleRedemptionValues memory singleRedemption;
        // Determine the remaining amount (lot) to be redeemed, capped by the entire debt of the AssetPortfolio minus the liquidation reserve

        uint256[] memory amounts;
        (singleRedemption.CollLot.tokens, amounts, singleRedemption.assetPortfolioDebt) = assetPortfolioManager
            .getCurrentAssetPortfolioState(_target);

        singleRedemption.PUSTLot = PalmMath._min(
            totals.remainingPUST,
            singleRedemption.assetPortfolioDebt.sub(PUST_GAS_COMPENSATION)
        );

        uint256 i; // i term will be used as the index of the collateral to redeem later too
        uint256 tokensLen = singleRedemption.CollLot.tokens.length;
        {
            //Make sure single collateral to redeem exists in assetPortfolio
            bool foundCollateral;

            for (i = 0; i < tokensLen; ++i) {
                if (singleRedemption.CollLot.tokens[i] == _collToRedeem) {
                    foundCollateral = true;
                    break;
                }
            }
            require(foundCollateral, "TMR:Coll not in assetPortfolio");
        }

        {
            // Get usd value of only the collateral being redeemed
            uint256 singleCollUSD = contractsCache.controller.getValueUSD(_collToRedeem, amounts[i]);

            // Cap redemption amount to the max amount of collateral that can be redeemed
            singleRedemption.PUSTLot = PalmMath._min(singleCollUSD, singleRedemption.PUSTLot);

            // redemption addresses are the same as coll addresses for assetPortfolio
            // Calculation for how much collateral to send of each type.
            singleRedemption.CollLot.amounts = new uint256[](tokensLen);

            uint256 tokenAmountToRedeem = singleRedemption.PUSTLot.mul(amounts[i]).div(
                singleCollUSD
            );
            amounts[i] = amounts[i].sub(tokenAmountToRedeem);
            singleRedemption.CollLot.amounts[i] = tokenAmountToRedeem;
        }

        // Send the assetPortfolio being redeemed against 20% of the minimum fee of 0.5%
        _sendBorrowerFeeSplit(contractsCache, _redeemer, _target, singleRedemption.PUSTLot, contractsCache.controller.getRedemptionBorrowerFeeSplit());

        // Decrease the debt and collateral of the current AssetPortfolio according to the PUST lot and corresponding Collateral to send
        singleRedemption.assetPortfolioDebt = singleRedemption.assetPortfolioDebt.sub(singleRedemption.PUSTLot);

        if (singleRedemption.assetPortfolioDebt == PUST_GAS_COMPENSATION) {
            // No debt left in the AssetPortfolio (except for the liquidation reserve), therefore the assetPortfolio gets closed
            assetPortfolioManager.removeStake(_target);
            assetPortfolioManager.closeAssetPortfolioRedemption(_target);
            _redeemCloseAssetPortfolio(
                contractsCache,
                _target,
                PUST_GAS_COMPENSATION,
                singleRedemption.CollLot.tokens,
                amounts
            );

            emit AssetPortfolioUpdated(
                _target,
                0,
                new address[](0),
                new uint256[](0),
                AssetPortfolioManagerOperation.redeemCollateral
            );
        } else {
            uint256 newAICR = _getAICRColls(
                newColls(singleRedemption.CollLot.tokens, amounts),
                singleRedemption.assetPortfolioDebt
            );

            /*
             * If the provided hint is too inaccurate of date, we bail since trying to reinsert without a good hint will almost
             * certainly result in running out of gas. Arbitrary measures of this mean newAICR must be greater than hint AICR - 2%,
             * and smaller than hint ICR + 2%.
             *
             * If the resultant net debt of the partial is less than the minimum, net debt we bail.
             */
            {
                // Stack scope
                if (
                    newAICR >= _hintAICR.add(2e16) ||
                    newAICR <= _hintAICR.sub(2e16) ||
                    _getNetDebt(singleRedemption.assetPortfolioDebt) < MIN_NET_DEBT
                ) {
                    revert("Invalid partial redemption hint or remaining debt is too low");
                }

                contractsCache.sortedAssetPortfolios.reInsert(_target, newAICR, _upperHint, _lowerHint);
            }
            assetPortfolioManager.updateAssetPortfolioDebt(_target, singleRedemption.assetPortfolioDebt);
            assetPortfolioManager.updateAssetPortfolioCollAndStakeAndTotalStakes(_target, singleRedemption.CollLot.tokens, amounts);

            emit AssetPortfolioUpdated(
                _target,
                singleRedemption.assetPortfolioDebt,
                singleRedemption.CollLot.tokens,
                amounts,
                AssetPortfolioManagerOperation.redeemCollateral
            );
        }

        totals.totalPUSTToRedeem = singleRedemption.PUSTLot;

        totals.CollsDrawn = singleRedemption.CollLot;

        require(isNonzero(totals.CollsDrawn), "TMR: non zero collsDrawn");
        // Decay the baseRate due to time passed, and then increase it according to the size of this redemption.
        // Use the saved total PUST supply value, from before it was reduced by the redemption.
        _updateBaseRateFromRedemption(totals.totalPUSTToRedeem, totals.totalPUSTSupplyAtStart);

        totals.PUSTfee = _getRedemptionFee(totals.totalPUSTToRedeem);

        uint256 borrowerSplitInPUST = totals
            .totalPUSTToRedeem
            .mul(5e15)
            .div(DECIMAL_PRECISION)
            .mul(contractsCache.controller.getRedemptionBorrowerFeeSplit())
            .div(DECIMAL_PRECISION);

        // check user has enough PUST to pay fee and redemptions
        // Already paid borrower split fee.
        _requirePUSTBalanceCoversRedemption(
            contractsCache.pustToken,
            _redeemer,
            totals.remainingPUST.add(totals.PUSTfee).sub(borrowerSplitInPUST)
        );

        // check to see that the fee doesn't exceed the max fee
        _requireUserAcceptsFeeRedemption(totals.PUSTfee, _PUSTMaxFee);

        // send fee from user to PALM stakers and treasury
        _transferAndSplitFee(contractsCache, _redeemer, totals.PUSTfee, borrowerSplitInPUST);

        emit Redemption(
            totals.remainingPUST,
            totals.totalPUSTToRedeem,
            totals.PUSTfee,
            totals.CollsDrawn.tokens,
            totals.CollsDrawn.amounts
        );
        // Burn the total PUST that is cancelled with debt
        contractsCache.pustToken.burn(_redeemer, totals.totalPUSTToRedeem);
        // Update Active Pool PUST, and send Collaterals to account
        contractsCache.activePool.decreasePUSTDebt(totals.totalPUSTToRedeem);

        contractsCache.activePool.sendCollateralsUnwrap(
            _redeemer, // tokens to
            totals.CollsDrawn.tokens,
            totals.CollsDrawn.amounts
        );
    }

    /**
     * @notice Redeem as much collateral as possible from _borrower's AssetPortfolio in exchange for PUST up to _maxPUSTamount
     * Special calculation for determining how much collateral to send of each type to send.
     * We want to redeem equivalent to the USD value instead of the VC value here, so we take the PUST amount
     * which we are redeeming from this assetPortfolio, and calculate the ratios at which we would redeem a single
     * collateral type compared to all others.
     * For example if we are redeeming 10,000 from this assetPortfolio, and it has collateral A with a safety ratio of 1,
     * collateral B with safety ratio of 0.5. Let's say their price is each 1. The assetPortfolio is composed of 10,000 A and
     * 10,000 B, so we would redeem 5,000 A and 5,000 B, instead of 6,666 A and 3,333 B. To do calculate this we take
     * the USD value of that collateral type, and divide it by the total USD value of all collateral types. The price
     * actually cancels out here so we just do PUST amount * token amount / total USD value, instead of
     * PUST amount * token value / total USD value / token price, since we are trying to find token amount.
     * @param _borrower The address of the borrower
     * @param _redeemer The address of the redeemer
     * @param _maxPUSTAmount Passed in, try to redeem up to this amount of PUST
     * @param _upperPartialRedemptionHint is the upper hint for reinsertion of last assetPortfolio
     * @param _lowerPartialRedemptionHint is the lower hint for reinsertion of last assetPortfolio
     * @param _partialRedemptionHintAICR is the target hint AICR for the last assetPortfolio redeemed
     * @return singleRedemption is the data about the redemption that was made, including collsDrawn, debtDrawn, etc.
     */
    function _redeemCollateralFromAssetPortfolio(
        ContractsCache memory contractsCache,
        address _borrower,
        address _redeemer,
        uint256 _maxPUSTAmount,
        address _upperPartialRedemptionHint,
        address _lowerPartialRedemptionHint,
        uint256 _partialRedemptionHintAICR,
        uint256 _redemptionBorrowerFeeSplit
    ) internal returns (SingleRedemptionValues memory singleRedemption) {
        uint256[] memory amounts;
        (singleRedemption.CollLot.tokens, amounts, singleRedemption.assetPortfolioDebt) = assetPortfolioManager
            .getCurrentAssetPortfolioState(_borrower);

        uint256 collsLen = singleRedemption.CollLot.tokens.length;
        uint256[] memory finalAmounts = new uint256[](collsLen);

        // Determine the remaining amount (lot) to be redeemed, capped by the entire debt of the AssetPortfolio minus the liquidation reserve
        singleRedemption.PUSTLot = PalmMath._min(
            _maxPUSTAmount,
            singleRedemption.assetPortfolioDebt.sub(PUST_GAS_COMPENSATION)
        );

        // redemption addresses are the same as coll addresses for assetPortfolio
        // Calculation for how much collateral to send of each type.
        singleRedemption.CollLot.amounts = new uint256[](collsLen);
        {
            uint256 totalCollUSD = _getUSDColls(newColls(singleRedemption.CollLot.tokens, amounts));
            uint256 baseLot = singleRedemption.PUSTLot.mul(DECIMAL_PRECISION);
            for (uint256 i; i < collsLen; ++i) {
                uint256 tokenAmountToRedeem = baseLot.mul(amounts[i]).div(totalCollUSD).div(1e18);

                finalAmounts[i] = amounts[i].sub(tokenAmountToRedeem);
                singleRedemption.CollLot.amounts[i] = tokenAmountToRedeem;
            }
        }

        // Decrease the debt and collateral of the current AssetPortfolio according to the PUST lot and corresponding Collateral to send
        uint256 newDebt = singleRedemption.assetPortfolioDebt.sub(singleRedemption.PUSTLot);

        if (newDebt == PUST_GAS_COMPENSATION) {
            // No debt left in the AssetPortfolio (except for the liquidation reserve), therefore the assetPortfolio gets closed
            assetPortfolioManager.removeStake(_borrower);
            assetPortfolioManager.closeAssetPortfolioRedemption(_borrower);
            _redeemCloseAssetPortfolio(
                contractsCache,
                _borrower,
                PUST_GAS_COMPENSATION,
                singleRedemption.CollLot.tokens,
                finalAmounts
            );

            emit AssetPortfolioUpdated(
                _borrower,
                0,
                new address[](0),
                new uint256[](0),
                AssetPortfolioManagerOperation.redeemCollateral
            );
        } else {
            uint256 newAICR = _computeCR(
                _getRVC(singleRedemption.CollLot.tokens, finalAmounts),
                newDebt
            );

            /*
             * If the provided hint is too inaccurate of date, we bail since trying to reinsert without a good hint will almost
             * certainly result in running out of gas. Arbitrary measures of this mean newICR must be greater than hint ICR - 2%,
             * and smaller than hint ICR + 2%.
             *
             * If the resultant net debt of the partial is less than the minimum, net debt we bail.
             */

            if (
                newAICR >= _partialRedemptionHintAICR.add(2e16) ||
                newAICR <= _partialRedemptionHintAICR.sub(2e16) ||
                _getNetDebt(newDebt) < MIN_NET_DEBT
            ) {
                singleRedemption.cancelledPartial = true;
                return singleRedemption;
            }

            contractsCache.sortedAssetPortfolios.reInsert(
                _borrower,
                newAICR,
                _upperPartialRedemptionHint,
                _lowerPartialRedemptionHint
            );

            assetPortfolioManager.updateAssetPortfolioDebt(_borrower, newDebt);
            collsLen = singleRedemption.CollLot.tokens.length;
            for (uint256 i; i < collsLen; ++i) {
                amounts[i] = finalAmounts[i];
            }
            assetPortfolioManager.updateAssetPortfolioCollAndStakeAndTotalStakes(_borrower, singleRedemption.CollLot.tokens, amounts);

            emit AssetPortfolioUpdated(
                _borrower,
                newDebt,
                singleRedemption.CollLot.tokens,
                finalAmounts,
                AssetPortfolioManagerOperation.redeemCollateral
            );
        }

        // Send the assetPortfolio being redeemed against 20% of the minimum fee of 0.5%
        // Send after all other logic to skip the cancelledPartial possibility, where they are eligible for no fee.
        _sendBorrowerFeeSplit(contractsCache, _redeemer, _borrower, singleRedemption.PUSTLot, _redemptionBorrowerFeeSplit);
    }


    function updateRedemptionsEnabled(bool _enabled) external override {
        _requireCallerisController();
        redemptionsEnabled = _enabled;
    }


    /*
     * @notice Called when a full redemption occurs, and closes the assetPortfolio.
     * The redeemer swaps (debt - liquidation reserve) PUST for (debt - liquidation reserve) worth of Collateral, so the PUST liquidation reserve left corresponds to the remaining debt.
     * In order to close the assetPortfolio, the PUST liquidation reserve is burned, and the corresponding debt is removed from the active pool.
     * The debt recorded on the assetPortfolio's struct is zero'd elswhere, in _closeAssetPortfolio.
     * Any surplus Collateral left in the assetPortfolio, is sent to the Coll surplus pool, and can be later claimed by the borrower.
     * @param _PUST Liquidation reserve to burn
     * @param _colls Collateral to send to coll surplus pool
     * @param _collsAmounts Amounts of collateral to send to coll surplus pool
     */
    function _redeemCloseAssetPortfolio(
        ContractsCache memory contractsCache,
        address _borrower,
        uint256 _PUST,
        address[] memory _remainingColls,
        uint256[] memory _remainingCollsAmounts
    ) internal {
        contractsCache.pustToken.burn(gasPoolAddress, _PUST);
        // Update Active Pool PUST, and send Collateral to account
        contractsCache.activePool.decreasePUSTDebt(_PUST);

        // send Collaterals from Active Pool to CollSurplus Pool
        contractsCache.collSurplusPool.accountSurplus(
            _borrower,
            _remainingColls,
            _remainingCollsAmounts
        );
        contractsCache.activePool.sendCollaterals(
            address(contractsCache.collSurplusPool),
            _remainingColls,
            _remainingCollsAmounts
        );
    }

    /*
     * @notice This function has two impacts on the baseRate state variable:
     * 1) decays the baseRate based on time passed since last redemption or PUST borrowing operation.
     * then,
     * 2) increases the baseRate based on the amount redeemed, as a proportion of total supply
     * @param _PUSTDrawn : Amount of PUST Drawn total from this redemption
     * @param _totalPUSTSupply : Total PUST supply to decay base rate from.
     */
    function _updateBaseRateFromRedemption(uint256 _PUSTDrawn, uint256 _totalPUSTSupply)
        internal
        returns (uint256)
    {
        uint256 decayedBaseRate = assetPortfolioManager.calcDecayedBaseRate();

        /* Convert the drawn Collateral back to PUST at face value rate (1 PUST:1 USD), in order to get
         * the fraction of total supply that was redeemed at face value. */
        uint256 redeemedPUSTFraction = _PUSTDrawn.mul(1e18).div(_totalPUSTSupply);

        uint256 newBaseRate = decayedBaseRate.add(redeemedPUSTFraction.div(BETA));
        newBaseRate = PalmMath._min(newBaseRate, DECIMAL_PRECISION); // cap baseRate at a maximum of 100%

        assetPortfolioManager.updateBaseRate(newBaseRate);
        return newBaseRate;
    }

    /**  
     * @notice Checks that the first redemption hint is correct considering the state of sortedAssetPortfolios
     */
    function _isValidFirstRedemptionHint(ISortedAssetPortfolios _sortedAssetPortfolios, address _firstRedemptionHint)
        internal
        view
        returns (bool)
    {
        if (
            _firstRedemptionHint == address(0) ||
            !_sortedAssetPortfolios.contains(_firstRedemptionHint) ||
            assetPortfolioManager.getCurrentICR(_firstRedemptionHint) < MCR
        ) {
            return false;
        }

        address nextAssetPortfolio = _sortedAssetPortfolios.getNext(_firstRedemptionHint);
        return nextAssetPortfolio == address(0) || assetPortfolioManager.getCurrentICR(nextAssetPortfolio) < MCR;
    }

    function _requireUserAcceptsFeeRedemption(uint256 _actualFee, uint256 _maxFee) internal pure {
        require(_actualFee <= _maxFee, "TMR:User must accept fee");
    }

    function _requireValidMaxFee(uint256 _PUSTAmount, uint256 _maxPUSTFee) internal pure {
        uint256 _maxFeePercentage = _maxPUSTFee.mul(DECIMAL_PRECISION).div(_PUSTAmount);
        require(_maxFeePercentage >= REDEMPTION_FEE_FLOOR, "TMR:Passed in max fee <0.5%");
        require(_maxFeePercentage <= DECIMAL_PRECISION, "TMR:Passed in max fee >100%");
    }

    function _requireRedemptionsEnabled() internal view {
        require(
            redemptionsEnabled,
            "TMR:RedemptionsDisabled"
        );
    }

    function _requireTCRoverMCR() internal view {
        require(_getTCR() >= MCR, "TMR: Cannot redeem when TCR<MCR");
    }

    function _requireAmountGreaterThanZero(uint256 _amount) internal pure {
        require(_amount != 0, "TMR:ReqNonzeroAmount");
    }

    function _requirePUSTBalanceCoversRedemption(
        IPUSTToken _pustToken,
        address _redeemer,
        uint256 _amount
    ) internal view {
        require(_pustToken.balanceOf(_redeemer) >= _amount, "TMR:InsufficientPUSTBalance");
    }

    function isNonzero(newColls memory coll) internal pure returns (bool) {
        uint256 collsLen = coll.amounts.length;
        for (uint256 i; i < collsLen; ++i) {
            if (coll.amounts[i] != 0) {
                return true;
            }
        }
        return false;
    }

    function _requireCallerisAssetPortfolioManager() internal view {
        require(msg.sender == address(assetPortfolioManager), "TMR:Caller not TM");
    }

    function _requireCallerisController() internal view {
        require(msg.sender == address(controller), "TMR:Caller not Controller");
    }

    function _getRedemptionFee(uint256 _PUSTRedeemed) internal view returns (uint256) {
        return _calcRedemptionFee(assetPortfolioManager.getRedemptionRate(), _PUSTRedeemed);
    }

    function _calcRedemptionFee(uint256 _redemptionRate, uint256 _PUSTRedeemed)
        internal
        pure
        returns (uint256)
    {
        uint256 redemptionFee = _redemptionRate.mul(_PUSTRedeemed).div(DECIMAL_PRECISION);
        require(redemptionFee < _PUSTRedeemed, "TM: Fee > PUST Redeemed");
        return redemptionFee;
    }

    /**
     * @notice Transfers the fee from the redeemer to the treasury partially, and the rest to the Fee recipient (sPALM) Contract
     * @param _PUSTFee : PUST Fee which has been calculated from the amount redeemed
     * @param _borrowerSplitInPUST : The amount in PUST which has already been transferred to the borrower
     */
    function _transferAndSplitFee(
        ContractsCache memory contractsCache,
        address _redeemer,
        uint256 _PUSTFee,
        uint256 _borrowerSplitInPUST
    ) internal {
        (uint256 treasuryFeeSplit, address palmTreasury, address PUSTFeeRecipient) = contractsCache
            .controller
            .getFeeSplitInformation();
        // Get the treasury split in PUST
        uint256 treasurySplitInPUST = treasuryFeeSplit.mul(_PUSTFee).div(DECIMAL_PRECISION);
        // If the treasury fee split is more than 1 - borrower split, then the treasury will receive the remainder instead of its supposed split
        treasurySplitInPUST = PalmMath._min(
            treasurySplitInPUST,
            _PUSTFee.sub(_borrowerSplitInPUST)
        );

        // Send a percentage to the treasury
        contractsCache.pustToken.safeTransferFrom(_redeemer, palmTreasury, treasurySplitInPUST);

        // And send the rest to PUSTFeeRecipient
        contractsCache.pustToken.safeTransferFrom(
            _redeemer,
            PUSTFeeRecipient,
            _PUSTFee.sub(treasurySplitInPUST).sub(_borrowerSplitInPUST)
        );
    }

    /**
     * @notice Send a flat rate of the base redeem fee to the borrower who is being redeemed again.
     * The extra is accounted for in the collsurpluspool
     * @param _redeemedAmount : Amount redeemed, send 20% * 0.5% to the borrower.
     */
    function _sendBorrowerFeeSplit(
        ContractsCache memory contractsCache,
        address _redeemer,
        address _borrower,
        uint256 _redeemedAmount, 
        uint256 _redemptionBorrowerFeeSplit
    ) internal {
        uint256 toSendToBorrower = (_redeemedAmount)
            .mul(5e15)
            .div(DECIMAL_PRECISION)
            .mul(_redemptionBorrowerFeeSplit)
            .div(DECIMAL_PRECISION);
        contractsCache.pustToken.safeTransferFrom(
            _redeemer,
            address(contractsCache.collSurplusPool),
            toSendToBorrower
        );
        contractsCache.collSurplusPool.accountRedemptionBonus(_borrower, toSendToBorrower);
    }
}