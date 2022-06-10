// SPDX-License-Identifier: UNLICENSED

pragma solidity 0.6.11;

import "./ICollateralReceiver.sol";

/*
 * The Stability Pool holds PUST tokens deposited by Stability Pool depositors.
 *
 * When a assetPortfolio is liquidated, then depending on system conditions, some of its PUST debt gets offset with
 * PUST in the Stability Pool:  that is, the offset debt evaporates, and an equal amount of PUST tokens in the Stability Pool is burned.
 *
 * Thus, a liquidation causes each depositor to receive a PUST loss, in proportion to their deposit as a share of total deposits.
 * They also receive an ETH gain, as the ETH collateral of the liquidated assetPortfolio is distributed among Stability depositors,
 * in the same proportion.
 *
 * When a liquidation occurs, it depletes every deposit by the same fraction: for example, a liquidation that depletes 40%
 * of the total PUST in the Stability Pool, depletes 40% of each deposit.
 *
 * A deposit that has experienced a series of liquidations is termed a "compounded deposit": each liquidation depletes the deposit,
 * multiplying it by some factor in range ]0,1[
 *
 * Please see the implementation spec in the proof document, which closely follows on from the compounded deposit / ETH gain derivations:
 * https://github.com/liquity/liquity/blob/master/papers/Scalable_Reward_Distribution_with_Compounding_Stakes.pdf
 *
 * --- PALM ISSUANCE TO STABILITY POOL DEPOSITORS ---
 *
 * An PALM issuance event occurs at every deposit operation, and every liquidation.
 *
 * Each deposit is tagged with the address of the front end through which it was made.
 *
 * All deposits earn a share of the issued PALM in proportion to the deposit as a share of total deposits. The PALM earned
 * by a given deposit, is split between the depositor and the front end through which the deposit was made, based on the front end's kickbackRate.
 *
 * Please see the system Readme for an overview:
 * https://github.com/liquity/dev/blob/main/README.md#palm-issuance-to-stability-providers
 */
interface IStabilityPool is ICollateralReceiver {

    // --- Events ---

    event StabilityPoolETHBalanceUpdated(uint _newBalance);
    event StabilityPoolPUSTBalanceUpdated(uint _newBalance);

    event P_Updated(uint _P);
    event S_Updated(uint _S, uint128 _epoch, uint128 _scale);
    event G_Updated(uint _G, uint128 _epoch, uint128 _scale);
    event EpochUpdated(uint128 _currentEpoch);
    event ScaleUpdated(uint128 _currentScale);


    event DepositSnapshotUpdated(address indexed _depositor, uint _P, uint _S, uint _G);
    event UserDepositChanged(address indexed _depositor, uint _newDeposit);

    event ETHGainWithdrawn(address indexed _depositor, uint _ETH, uint _PUSTLoss);
    event PALMPaidToDepositor(address indexed _depositor, uint _PALM);
    event EtherSent(address _to, uint _amount);

    // --- Functions ---

    /*
     * Called only once on init, to set addresses of other Palm contracts
     * Callable only by owner, renounces ownership at the end
     */
    function setAddresses(
        address _borrowerOperationsAddress,
        address _assetPortfolioManagerAddress,
        address _activePoolAddress,
        address _pustTokenAddress,
        address _sortedAssetPortfoliosAddress,
        address _communityIssuanceAddress,
        address _controllerAddress,
        address _assetPortfolioManagerLiquidationsAddress
    )
    external;

    /*
     * Initial checks:
     * - _amount is not zero
     * ---
     * - Triggers a PALM issuance, based on time passed since the last issuance. The PALM issuance is shared between *all* depositors and front ends
     * - Tags the deposit with the provided front end tag param, if it's a new deposit
     * - Sends depositor's accumulated gains (PALM, ETH) to depositor
     * - Sends the tagged front end's accumulated PALM gains to the tagged front end
     * - Increases deposit and tagged front end's stake, and takes new snapshots for each.
     */
    function provideToSP(uint _amount) external;

    /*
     * Initial checks:
     * - _amount is zero or there are no under collateralized assetPortfolios left in the system
     * - User has a non zero deposit
     * ---
     * - Triggers a PALM issuance, based on time passed since the last issuance. The PALM issuance is shared between *all* depositors and front ends
     * - Removes the deposit's front end tag if it is a full withdrawal
     * - Sends all depositor's accumulated gains (PALM, ETH) to depositor
     * - Sends the tagged front end's accumulated PALM gains to the tagged front end
     * - Decreases deposit and tagged front end's stake, and takes new snapshots for each.
     *
     * If _amount > userDeposit, the user withdraws all of their compounded deposit.
     */
    function withdrawFromSP(uint _amount) external;

    function claimRewardsSwap(uint256 _pustMinAmountTotal) external returns (uint256 amountFromSwap);


    /*
     * Initial checks:
     * - Caller is AssetPortfolioManager
     * ---
     * Cancels out the specified debt against the PUST contained in the Stability Pool (as far as possible)
     * and transfers the AssetPortfolio's ETH collateral from ActivePool to StabilityPool.
     * Only called by liquidation functions in the AssetPortfolioManager.
     */
    function offset(uint _debt, address[] memory _assets, uint[] memory _amountsAdded) external;

    //    /*
    //     * Returns the total amount of ETH held by the pool, accounted in an internal variable instead of `balance`,
    //     * to exclude edge cases like ETH received from a self-destruct.
    //     */
    //    function getETH() external view returns (uint);

    //*
    //     * Calculates and returns the total gains a depositor has accumulated
    //     */
    function getDepositorGains(address _depositor) external view returns (address[] memory assets, uint[] memory amounts);


    /*
     * Returns the total amount of VC held by the pool, accounted for by multipliying the
     * internal balances of collaterals by the price that is found at the time getVC() is called.
     */
    function getVC() external view returns (uint);

    /*
     * Returns PUST held in the pool. Changes when users deposit/withdraw, and when AssetPortfolio debt is offset.
     */
    function getTotalPUSTDeposits() external view returns (uint);

    /*
     * Calculate the PALM gain earned by a deposit since its last snapshots were taken.
     * If not tagged with a front end, the depositor gets a 100% cut of what their deposit earned.
     * Otherwise, their cut of the deposit's earnings is equal to the kickbackRate, set by the front end through
     * which they made their deposit.
     */
    function getDepositorPALMGain(address _depositor) external view returns (uint);


    /*
     * Return the user's compounded deposit.
     */
    function getCompoundedPUSTDeposit(address _depositor) external view returns (uint);

    /*
     * Add collateral type to totalColl
     */
    function addCollateralType(address _collateral) external;

    function getDepositSnapshotS(address depositor, address collateral) external view returns (uint);

    function getCollateral(address _collateral) external view returns (uint);

    function getAllCollateral() external view returns (address[] memory, uint256[] memory);

    function getEstimatedPALMPoolRewards(uint _amount, uint _time) external view returns (uint256);

}