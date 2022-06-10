// SPDX-License-Identifier: UNLICENSED

pragma solidity 0.6.11;

import "./Dependencies/AssetPortfolioManagerBase.sol";



/**
 * @notice AssetPortfolioManagerLiquidations is derived from AssetPortfolioManager and has all the functions
 * related to Liquidations.
 */

contract AssetPortfolioManagerLiquidations is AssetPortfolioManagerBase, IAssetPortfolioManagerLiquidations {
    bytes32 public constant NAME = "AssetPortfolioMLiquidations";

    uint256 internal constant _100pct = 1e18; // 1e18 == 100%

    // Additional 1e9 precision for the SP Ratio calculation
    uint256 internal constant SPRatioPrecision = 1e27;

    uint256 internal constant PERCENT_DIVISOR = 200; // dividing by 200 yields 0.5%

    IStabilityPool internal stabilityPoolContract;

    IAssetPortfolioManager internal assetPortfolioManager;

    IPUSTToken internal pustTokenContract;

    address internal gasPoolAddress;

    ICollSurplusPool internal collSurplusPool;

    struct LiquidationValues {
        uint256 entireAssetPortfolioDebt;
        newColls entireAssetPortfolioColl;
        newColls collGasCompensation;
        uint256 PUSTGasCompensation;
        uint256 debtToOffset;
        newColls collToSendToSP;
        uint256 debtToRedistribute;
        newColls collToRedistribute;
        newColls collSurplus;
    }

    struct LiquidationTotals {
        uint256 totalVCInSequence;
        uint256 totalDebtInSequence;
        newColls totalCollGasCompensation;
        uint256 totalPUSTGasCompensation;
        uint256 totalDebtToOffset;
        newColls totalCollToSendToSP;
        uint256 totalDebtToRedistribute;
        newColls totalCollToRedistribute;
        newColls totalCollSurplus;
    }

    struct LocalVariables_LiquidationSequence {
        uint256 remainingPUSTInStabPool;
        uint256 i;
        uint256 ICR;
        address user;
        bool backToNormalMode;
    }

    struct LocalVariables_OuterLiquidationFunction {
        uint256 PUSTInStabPool;
        bool recoveryModeAtStart;
        uint256 liquidatedDebt;
    }

    struct LocalVariables_InnerSingleLiquidateFunction {
        newColls collToLiquidate;
        uint256 pendingDebtReward;
        newColls pendingCollReward;
    }

    struct LocalVariables_ORVals {
        uint256 debtToOffset;
        newColls collToSendToSP;
        uint256 debtToRedistribute;
        newColls collToRedistribute;
        newColls collSurplus;
    }

    event AssetPortfolioLiquidated(
        address indexed _borrower,
        uint256 _debt,
        AssetPortfolioManagerOperation _operation
    );
    event Liquidation(
        uint256 liquidatedAmount,
        uint256 totalPUSTGasCompensation,
        address[] totalCollTokens,
        uint256[] totalCollAmounts,
        address[] totalCollGasCompTokens,
        uint256[] totalCollGasCompAmounts
    );
    
    bool private addressSet;
    function setAddresses(
        address _activePoolAddress,
        address _defaultPoolAddress,
        address _stabilityPoolAddress,
        address _gasPoolAddress,
        address _collSurplusPoolAddress,
        address _pustTokenAddress,
        address _controllerAddress,
        address _assetPortfolioManagerAddress
    ) external {
        require(addressSet == false, "Addresses already set");
        addressSet = true;
        activePool = IActivePool(_activePoolAddress);
        defaultPool = IDefaultPool(_defaultPoolAddress);
        stabilityPoolContract = IStabilityPool(_stabilityPoolAddress);
        controller = IPalmController(_controllerAddress);
        gasPoolAddress = _gasPoolAddress;
        collSurplusPool = ICollSurplusPool(_collSurplusPoolAddress);
        pustTokenContract = IPUSTToken(_pustTokenAddress);
        assetPortfolioManager = IAssetPortfolioManager(_assetPortfolioManagerAddress);
    }

    /**
     * @notice Function for liquidating a list of assetPortfolios in a single transaction
     * @dev Will perform as many as it can and looks at if it is eligible for liquidation based on the current ICR value
      */
    function batchLiquidateAssetPortfolios(address[] memory _assetPortfolioArray, address _liquidator)
        external
        override
    {
        _requireCallerisAssetPortfolioManager();
        require(_assetPortfolioArray.length != 0, "TML: One assetPortfolio must exist");

        IActivePool activePoolCached = activePool;
        IDefaultPool defaultPoolCached = defaultPool;
        IStabilityPool stabilityPoolCached = stabilityPoolContract;

        LocalVariables_OuterLiquidationFunction memory vars;
        LiquidationTotals memory totals;

        vars.PUSTInStabPool = stabilityPoolCached.getTotalPUSTDeposits();
        uint256 systemCollVC;
        uint256 systemDebt;
        // System coll RVC not needed here. 
        (vars.recoveryModeAtStart, systemCollVC, , systemDebt) = _checkRecoveryModeAndSystem();

        // Perform the appropriate liquidation sequence - tally values and obtain their totals.
        if (vars.recoveryModeAtStart) {
            totals = _getTotalFromBatchLiquidate_RecoveryMode(
                activePoolCached,
                defaultPoolCached,
                vars.PUSTInStabPool,
                systemCollVC,
                systemDebt,
                _assetPortfolioArray
            );
        } else {
            //  if !vars.recoveryModeAtStart
            totals = _getTotalsFromBatchLiquidate_NormalMode(
                activePoolCached,
                defaultPoolCached,
                vars.PUSTInStabPool,
                _assetPortfolioArray
            );
        }

        require(totals.totalDebtInSequence != 0, "TML: nothing to liquidate");
        // Move liquidated Collateral and PUST to the appropriate pools
        stabilityPoolCached.offset(
            totals.totalDebtToOffset,
            totals.totalCollToSendToSP.tokens,
            totals.totalCollToSendToSP.amounts
        );
        assetPortfolioManager.redistributeDebtAndColl(
            activePoolCached,
            defaultPoolCached,
            totals.totalDebtToRedistribute,
            totals.totalCollToRedistribute.tokens,
            totals.totalCollToRedistribute.amounts
        );
        if (_collsIsNonZero(totals.totalCollSurplus)) {
            activePoolCached.sendCollaterals(
                address(collSurplusPool),
                totals.totalCollSurplus.tokens,
                totals.totalCollSurplus.amounts
            );
        }

        // Update system snapshots
        assetPortfolioManager.updateSystemSnapshots_excludeCollRemainder(
            activePoolCached,
            totals.totalCollGasCompensation.tokens,
            totals.totalCollGasCompensation.amounts
        );

        vars.liquidatedDebt = totals.totalDebtInSequence;

        // merge the colls into one to emit correct event.
        newColls memory sumCollsResult = _sumColls(
            totals.totalCollToSendToSP,
            totals.totalCollToRedistribute
        );
        sumCollsResult = _sumColls(sumCollsResult, totals.totalCollSurplus);

        emit Liquidation(
            vars.liquidatedDebt,
            totals.totalPUSTGasCompensation,
            sumCollsResult.tokens,
            sumCollsResult.amounts,
            totals.totalCollGasCompensation.tokens,
            totals.totalCollGasCompensation.amounts
        );

        // Send gas compensation to caller
        _sendGasCompensation(
            activePoolCached,
            _liquidator,
            totals.totalPUSTGasCompensation,
            totals.totalCollGasCompensation.tokens,
            totals.totalCollGasCompensation.amounts
        );
    }

    /**
     * @notice This function is used when the batch liquidation sequence starts during Recovery Mode
     * @dev It handles the case where the system *leaves* Recovery Mode, part way through the liquidation sequence
     * @return totals from batch liquidate
      */
    function _getTotalFromBatchLiquidate_RecoveryMode(
        IActivePool _activePool,
        IDefaultPool _defaultPool,
        uint256 _PUSTInStabPool,
        uint256 _systemCollVC,
        uint256 _systemDebt,
        address[] memory _assetPortfolioArray
    ) internal returns (LiquidationTotals memory totals) {
        LocalVariables_LiquidationSequence memory vars;
        LiquidationValues memory singleLiquidation;

        vars.remainingPUSTInStabPool = _PUSTInStabPool;
        vars.backToNormalMode = false;
        uint256 assetPortfolioArrayLen = _assetPortfolioArray.length;
        for (vars.i = 0; vars.i < assetPortfolioArrayLen; ++vars.i) {
            vars.user = _assetPortfolioArray[vars.i];

            // Skip non-active assetPortfolios
            Status userStatus = Status(assetPortfolioManager.getAssetPortfolioStatus(vars.user));
            if (userStatus != Status.active) {
                continue;
            }
            vars.ICR = assetPortfolioManager.getCurrentICR(vars.user);

            if (!vars.backToNormalMode) {
                // Skip this assetPortfolio if ICR is greater than MCR and Stability Pool is empty
                if (vars.ICR >= MCR && vars.remainingPUSTInStabPool == 0) {
                    continue;
                }

                uint256 TCR = _computeCR(_systemCollVC, _systemDebt);

                singleLiquidation = _liquidateRecoveryMode(
                    _activePool,
                    _defaultPool,
                    vars.user,
                    vars.ICR,
                    vars.remainingPUSTInStabPool,
                    TCR
                );

                // Update aggregate trackers
                vars.remainingPUSTInStabPool = vars.remainingPUSTInStabPool.sub(
                    singleLiquidation.debtToOffset
                );

                _systemDebt = _systemDebt.sub(singleLiquidation.debtToOffset);

                uint256 collToSendToSpVc = _getVCColls(singleLiquidation.collToSendToSP);
                uint256 collGasCompensationTotal = _getVCColls(
                    singleLiquidation.collGasCompensation
                );
                uint256 collSurplusTotal = _getVCColls(singleLiquidation.collSurplus);

                // Two calls stack too deep
                _systemCollVC = _systemCollVC.sub(collToSendToSpVc).sub(collGasCompensationTotal);
                _systemCollVC = _systemCollVC.sub(collSurplusTotal);

                // Add liquidation values to their respective running totals
                totals = _addLiquidationValuesToTotals(totals, singleLiquidation);

                vars.backToNormalMode = !_checkPotentialRecoveryMode(
                    _systemCollVC,
                    _systemDebt
                );
            } else if (vars.backToNormalMode && vars.ICR < MCR) {
                singleLiquidation = _liquidateNormalMode(
                    _activePool,
                    _defaultPool,
                    vars.user,
                    vars.remainingPUSTInStabPool
                );
                vars.remainingPUSTInStabPool = vars.remainingPUSTInStabPool.sub(
                    singleLiquidation.debtToOffset
                );

                // Add liquidation values to their respective running totals
                totals = _addLiquidationValuesToTotals(totals, singleLiquidation);
            } else continue; // In Normal Mode skip assetPortfolios with ICR >= MCR
        }
    }

    /**
     * @notice This function is used when the batch liquidation sequence starts during Normal Mode
     * @return totals from batch liquidate
      */
    function _getTotalsFromBatchLiquidate_NormalMode(
        IActivePool _activePool,
        IDefaultPool _defaultPool,
        uint256 _PUSTInStabPool,
        address[] memory _assetPortfolioArray
    ) internal returns (LiquidationTotals memory totals) {
        LocalVariables_LiquidationSequence memory vars;
        LiquidationValues memory singleLiquidation;

        vars.remainingPUSTInStabPool = _PUSTInStabPool;
        uint256 assetPortfolioArrayLen = _assetPortfolioArray.length;
        for (vars.i = 0; vars.i < assetPortfolioArrayLen; ++vars.i) {
            vars.user = _assetPortfolioArray[vars.i];
            vars.ICR = assetPortfolioManager.getCurrentICR(vars.user);
            if (vars.ICR < MCR) {
                singleLiquidation = _liquidateNormalMode(
                    _activePool,
                    _defaultPool,
                    vars.user,
                    vars.remainingPUSTInStabPool
                );
                vars.remainingPUSTInStabPool = vars.remainingPUSTInStabPool.sub(
                    singleLiquidation.debtToOffset
                );

                // Add liquidation values to their respective running totals
                totals = _addLiquidationValuesToTotals(totals, singleLiquidation);
            }
        }
    }

    /**
     * @notice Liquidate one assetPortfolio, in Normal Mode
     * @return singleLiquidation values
     */
    function _liquidateNormalMode(
        IActivePool _activePool,
        IDefaultPool _defaultPool,
        address _borrower,
        uint256 _PUSTInStabPool
    ) internal returns (LiquidationValues memory singleLiquidation) {
        LocalVariables_InnerSingleLiquidateFunction memory vars;
        (
            singleLiquidation.entireAssetPortfolioDebt,
            singleLiquidation.entireAssetPortfolioColl.tokens,
            singleLiquidation.entireAssetPortfolioColl.amounts,
            vars.pendingDebtReward,
            vars.pendingCollReward.tokens,
            vars.pendingCollReward.amounts
        ) = assetPortfolioManager.getEntireDebtAndColls(_borrower);

        _movePendingAssetPortfolioRewardsToActivePool(
            _activePool,
            _defaultPool,
            vars.pendingDebtReward,
            vars.pendingCollReward.tokens,
            vars.pendingCollReward.amounts
        );
        assetPortfolioManager.removeStake(_borrower);

        singleLiquidation.collGasCompensation = _getCollGasCompensation(
            singleLiquidation.entireAssetPortfolioColl
        );

        singleLiquidation.PUSTGasCompensation = PUST_GAS_COMPENSATION;

        vars.collToLiquidate.tokens = singleLiquidation.entireAssetPortfolioColl.tokens;
        uint256 collToLiquidateLen = vars.collToLiquidate.tokens.length;
        vars.collToLiquidate.amounts = new uint256[](collToLiquidateLen);
        for (uint256 i; i < collToLiquidateLen; ++i) {
            vars.collToLiquidate.amounts[i] = singleLiquidation.entireAssetPortfolioColl.amounts[i].sub(
                singleLiquidation.collGasCompensation.amounts[i]
            );
        }

        LocalVariables_ORVals memory or_vals = _getOffsetAndRedistributionVals(
            singleLiquidation.entireAssetPortfolioDebt,
            vars.collToLiquidate,
            _PUSTInStabPool
        );

        singleLiquidation = _updateSingleLiquidation(or_vals, singleLiquidation);
        assetPortfolioManager.closeAssetPortfolioLiquidation(_borrower);

        if (_collsIsNonZero(singleLiquidation.collSurplus)) {
            collSurplusPool.accountSurplus(
                _borrower,
                singleLiquidation.collSurplus.tokens,
                singleLiquidation.collSurplus.amounts
            );
        }

        emit AssetPortfolioLiquidated(
            _borrower,
            singleLiquidation.entireAssetPortfolioDebt,
            AssetPortfolioManagerOperation.liquidateInNormalMode
        );
        newColls memory borrowerColls;
        emit AssetPortfolioUpdated(
            _borrower,
            0,
            borrowerColls.tokens,
            borrowerColls.amounts,
            AssetPortfolioManagerOperation.liquidateInNormalMode
        );
    }

    /**
     * @notice Liquidate one assetPortfolio, in Recovery Mode
     * @return singleLiquidation Liquidation Values 
     */
    function _liquidateRecoveryMode(
        IActivePool _activePool,
        IDefaultPool _defaultPool,
        address _borrower,
        uint256 _ICR,
        uint256 _PUSTInStabPool,
        uint256 _TCR
    ) internal returns (LiquidationValues memory singleLiquidation) {
        LocalVariables_InnerSingleLiquidateFunction memory vars;

        if (assetPortfolioManager.getAssetPortfolioOwnersCount() <= 1) {
            return singleLiquidation;
        } // don't liquidate if last assetPortfolio

        (
            singleLiquidation.entireAssetPortfolioDebt,
            singleLiquidation.entireAssetPortfolioColl.tokens,
            singleLiquidation.entireAssetPortfolioColl.amounts,
            vars.pendingDebtReward,
            vars.pendingCollReward.tokens,
            vars.pendingCollReward.amounts
        ) = assetPortfolioManager.getEntireDebtAndColls(_borrower);

        singleLiquidation.collGasCompensation = _getCollGasCompensation(
            singleLiquidation.entireAssetPortfolioColl
        );

        singleLiquidation.PUSTGasCompensation = PUST_GAS_COMPENSATION;

        vars.collToLiquidate.tokens = singleLiquidation.entireAssetPortfolioColl.tokens;
        uint256 collToLiquidateLen = vars.collToLiquidate.tokens.length;
        vars.collToLiquidate.amounts = new uint256[](collToLiquidateLen);
        for (uint256 i; i < collToLiquidateLen; ++i) {
            vars.collToLiquidate.amounts[i] = singleLiquidation.entireAssetPortfolioColl.amounts[i].sub(
                singleLiquidation.collGasCompensation.amounts[i]
            );
        }

        // If ICR <= 100%, purely redistribute the AssetPortfolio across all active AssetPortfolios
        if (_ICR <= _100pct) {
            _movePendingAssetPortfolioRewardsToActivePool(
                _activePool,
                _defaultPool,
                vars.pendingDebtReward,
                vars.pendingCollReward.tokens,
                vars.pendingCollReward.amounts
            );
            assetPortfolioManager.removeStake(_borrower);

            singleLiquidation.debtToOffset = 0;
            newColls memory emptyColls;
            singleLiquidation.collToSendToSP = emptyColls;
            singleLiquidation.debtToRedistribute = singleLiquidation.entireAssetPortfolioDebt;
            singleLiquidation.collToRedistribute = vars.collToLiquidate;

            assetPortfolioManager.closeAssetPortfolioLiquidation(_borrower);
            emit AssetPortfolioLiquidated(
                _borrower,
                singleLiquidation.entireAssetPortfolioDebt,
                AssetPortfolioManagerOperation.liquidateInRecoveryMode
            );
            newColls memory borrowerColls;
            emit AssetPortfolioUpdated(
                _borrower,
                0,
                borrowerColls.tokens,
                borrowerColls.amounts,
                AssetPortfolioManagerOperation.liquidateInRecoveryMode
            );

            // If 100% < ICR < MCR, offset as much as possible, and redistribute the remainder
            // ICR > 100% is implied by prevoius state.
        } else if (_ICR < MCR) {
            _movePendingAssetPortfolioRewardsToActivePool(
                _activePool,
                _defaultPool,
                vars.pendingDebtReward,
                vars.pendingCollReward.tokens,
                vars.pendingCollReward.amounts
            );

            assetPortfolioManager.removeStake(_borrower);

            LocalVariables_ORVals memory or_vals = _getOffsetAndRedistributionVals(
                singleLiquidation.entireAssetPortfolioDebt,
                vars.collToLiquidate,
                _PUSTInStabPool
            );

            singleLiquidation = _updateSingleLiquidation(or_vals, singleLiquidation);

            assetPortfolioManager.closeAssetPortfolioLiquidation(_borrower);
            emit AssetPortfolioLiquidated(
                _borrower,
                singleLiquidation.entireAssetPortfolioDebt,
                AssetPortfolioManagerOperation.liquidateInRecoveryMode
            );
            newColls memory borrowerColls;
            emit AssetPortfolioUpdated(
                _borrower,
                0,
                borrowerColls.tokens,
                borrowerColls.amounts,
                AssetPortfolioManagerOperation.liquidateInRecoveryMode
            );
            /*
             * If 110% <= AICR < current TCR (accounting for the preceding liquidations in the current sequence)
             * and there is PUST in the Stability Pool, only offset, with no redistribution,
             * but at a capped rate of 1.1 and only if the whole debt can be liquidated.
             * The remainder due to the capped rate will be claimable as collateral surplus.
             * ICR >= 110% is implied from last else if statement. AICR is always >= ICR since that is a rule of 
             * the recovery ratio. 
             * We use AICR here instead of ICR since for assetPortfolios with stablecoins or anything
             * with recovery ratio > safety ratio, liquidating a assetPortfolio with ICR < TCR may not increase the TCR
             * since recovery ratios are used to calculate the TCR. The purpose of recovery mode is to increase the 
             * TCR and this may put all stablecoin assetPortfolios at risk of liquidation instantly if we used ICR. So, we only
             * do actions which will increase the TCR. 
             */
        } else if ((assetPortfolioManager.getCurrentAICR(_borrower) < _TCR) && (singleLiquidation.entireAssetPortfolioDebt <= _PUSTInStabPool)) {
            _movePendingAssetPortfolioRewardsToActivePool(
                _activePool,
                _defaultPool,
                vars.pendingDebtReward,
                vars.pendingCollReward.tokens,
                vars.pendingCollReward.amounts
            );

            assetPortfolioManager.removeStake(_borrower);

            singleLiquidation = _getCappedOffsetVals(
                singleLiquidation.entireAssetPortfolioDebt,
                vars.collToLiquidate.tokens,
                vars.collToLiquidate.amounts,
                singleLiquidation.entireAssetPortfolioColl.amounts,
                singleLiquidation.collGasCompensation.amounts
            );

            assetPortfolioManager.closeAssetPortfolioLiquidation(_borrower);

            emit AssetPortfolioLiquidated(
                _borrower,
                singleLiquidation.entireAssetPortfolioDebt,
                AssetPortfolioManagerOperation.liquidateInRecoveryMode
            );
            newColls memory borrowerColls;
            emit AssetPortfolioUpdated(
                _borrower,
                0,
                borrowerColls.tokens,
                borrowerColls.amounts,
                AssetPortfolioManagerOperation.liquidateInRecoveryMode
            );
        } else {
            // if (_ICR >= MCR && ( AICR >= _TCR || singleLiquidation.entireAssetPortfolioDebt > _PUSTInStabPool))
            LiquidationValues memory zeroVals;
            return zeroVals;
        }

        if (_collsIsNonZero(singleLiquidation.collSurplus)) {
            collSurplusPool.accountSurplus(
                _borrower,
                singleLiquidation.collSurplus.tokens,
                singleLiquidation.collSurplus.amounts
            );
        }
    }

    function _updateSingleLiquidation(
        LocalVariables_ORVals memory or_vals,
        LiquidationValues memory singleLiquidation
    ) internal pure returns (LiquidationValues memory) {
        singleLiquidation.debtToOffset = or_vals.debtToOffset;
        singleLiquidation.collToSendToSP = or_vals.collToSendToSP;
        singleLiquidation.debtToRedistribute = or_vals.debtToRedistribute;
        singleLiquidation.collToRedistribute = or_vals.collToRedistribute;
        singleLiquidation.collSurplus = or_vals.collSurplus;
        return singleLiquidation;
    }

    /**
     * @notice Move a AssetPortfolio's pending debt and collateral rewards from distributions, from the Default Pool to the Active Pool
     */
    function _movePendingAssetPortfolioRewardsToActivePool(
        IActivePool _activePool,
        IDefaultPool _defaultPool,
        uint256 _PUST,
        address[] memory _tokens,
        uint256[] memory _amounts
    ) internal {
        _defaultPool.decreasePUSTDebt(_PUST);
        _activePool.increasePUSTDebt(_PUST);
        _defaultPool.sendCollsToActivePool(_tokens, _amounts);
    }

    /**
     * @notice In a full liquidation, returns the values for a assetPortfolio's coll and debt to be offset, and coll and debt to be redistributed to active assetPortfolios
     * @dev _colls parameters is the _colls to be liquidated (total assetPortfolio colls minus collateral for gas compensation)
     * collsToRedistribute.tokens and collsToRedistribute.amounts should be the same length and should be the same length as _colls.tokens and _colls.amounts.
     * If there is any colls redistributed to stability pool, collsToSendToSP.tokens and collsToSendToSP.amounts
     * will be length equal to _colls.tokens and _colls.amounts. However, if no colls are redistributed to stability pool (which is the case when _PUSTInStabPool == 0),
     * then collsToSendToSP.tokens and collsToSendToSP.amounts will be empty.
     * @return or_vals Values for assetPortfolio's collateral and debt to be offset
     */
    function _getOffsetAndRedistributionVals(
        uint256 _entireAssetPortfolioDebt,
        newColls memory _collsToLiquidate,
        uint256 _PUSTInStabPool
    ) internal view returns (LocalVariables_ORVals memory or_vals) {
        or_vals.collToRedistribute.tokens = _collsToLiquidate.tokens;
        uint256 collsToLiquidateLen = _collsToLiquidate.tokens.length;
        or_vals.collToRedistribute.amounts = new uint256[](collsToLiquidateLen);

        if (_PUSTInStabPool != 0) {
            /*
             * Offset as much debt & collateral as possible against the Stability Pool, and redistribute the remainder
             * between all active assetPortfolios.
             *
             *  If the assetPortfolio's debt is larger than the deposited PUST in the Stability Pool:
             *
             *  - Offset an amount of the assetPortfolio's debt equal to the PUST in the Stability Pool
             *  - Remainder of assetPortfolio's debt will be redistributed
             *  - AssetPortfolio collateral can be partitioned into two parts:
             *  - (1) Offsetting Collateral = (debtToOffset / assetPortfolioDebt) * Collateral
             *  - (2) Redistributed Collateral = Total Collateral - Offsetting Collateral
             *  - The max offsetting collateral that can be sent to the stability pool is an amount of collateral such that
             *  - the stability pool receives 110% of value of the debtToOffset. Any extra Offsetting Collateral is
             *  - sent to the collSurplusPool and can be claimed by the borrower.
             */
            or_vals.collToSendToSP.tokens = _collsToLiquidate.tokens;
            or_vals.collToSendToSP.amounts = new uint256[](collsToLiquidateLen);

            or_vals.collSurplus.tokens = _collsToLiquidate.tokens;
            or_vals.collSurplus.amounts = new uint256[](collsToLiquidateLen);

            or_vals.debtToOffset = PalmMath._min(_entireAssetPortfolioDebt, _PUSTInStabPool);

            or_vals.debtToRedistribute = _entireAssetPortfolioDebt.sub(or_vals.debtToOffset);

            uint256 toLiquidateCollValueUSD = _getUSDColls(_collsToLiquidate);

            // collOffsetRatio: max percentage of the collateral that can be sent to the SP as offsetting collateral
            // collOffsetRatio = percentage of the assetPortfolio's debt that can be offset by the stability pool
            uint256 collOffsetRatio = SPRatioPrecision.mul(or_vals.debtToOffset).div(
                _entireAssetPortfolioDebt
            );

            // SPRatio: percentage of liquidated collateral that needs to be sent to SP in order to give SP depositors
            // $110 of collateral for every 100 PUST they are using to liquidate.
            uint256 SPRatio = or_vals.debtToOffset.mul(1e9).mul(MCR).div(
                toLiquidateCollValueUSD
            );

            // But SP ratio is capped at collOffsetRatio:
            SPRatio = PalmMath._min(collOffsetRatio, SPRatio);

            // if there is extra collateral left in the offset portion of the collateral after
            // giving stability pool holders $110 of collateral for every 100 PUST that is taken from them,
            // then this is surplus collateral that can be claimed by the borrower
            uint256 collSurplusRatio = collOffsetRatio.sub(SPRatio);

            for (uint256 i; i < collsToLiquidateLen; ++i) {
                or_vals.collToSendToSP.amounts[i] = _collsToLiquidate
                .amounts[i]
                .mul(SPRatio)
                .div(SPRatioPrecision);

                or_vals.collSurplus.amounts[i] = _collsToLiquidate
                .amounts[i]
                .mul(collSurplusRatio)
                .div(SPRatioPrecision);

                // remaining collateral is redistributed:
                or_vals.collToRedistribute.amounts[i] = _collsToLiquidate
                .amounts[i]
                .sub(or_vals.collToSendToSP.amounts[i])
                .sub(or_vals.collSurplus.amounts[i]);
            }
        } else {
            // all colls are redistributed because no PUST in stability pool to liquidate
            or_vals.debtToOffset = 0;
            for (uint256 i; i < collsToLiquidateLen; ++i) {
                or_vals.collToRedistribute.amounts[i] = _collsToLiquidate.amounts[i];
            }
            or_vals.debtToRedistribute = _entireAssetPortfolioDebt;
        }
    }

    /**
     * @notice Adds liquidation values to totals
     */
    function _addLiquidationValuesToTotals(
        LiquidationTotals memory oldTotals,
        LiquidationValues memory singleLiquidation
    ) internal view returns (LiquidationTotals memory newTotals) {
        // Tally all the values with their respective running totals
        //update one of these
        newTotals.totalCollGasCompensation = _sumColls(
            oldTotals.totalCollGasCompensation,
            singleLiquidation.collGasCompensation
        );
        newTotals.totalPUSTGasCompensation = oldTotals.totalPUSTGasCompensation.add(
            singleLiquidation.PUSTGasCompensation
        );
        newTotals.totalDebtInSequence = oldTotals.totalDebtInSequence.add(
            singleLiquidation.entireAssetPortfolioDebt
        );
        newTotals.totalDebtToOffset = oldTotals.totalDebtToOffset.add(
            singleLiquidation.debtToOffset
        );
        newTotals.totalCollToSendToSP = _sumColls(
            oldTotals.totalCollToSendToSP,
            singleLiquidation.collToSendToSP
        );
        newTotals.totalDebtToRedistribute = oldTotals.totalDebtToRedistribute.add(
            singleLiquidation.debtToRedistribute
        );
        newTotals.totalCollToRedistribute = _sumColls(
            oldTotals.totalCollToRedistribute,
            singleLiquidation.collToRedistribute
        );
        newTotals.totalCollSurplus = _sumColls(
            oldTotals.totalCollSurplus,
            singleLiquidation.collSurplus
        );
    }

    /**
     * @notice Get its offset coll/debt and Collateral gas comp, and close the assetPortfolio
    */
    function _getCappedOffsetVals(
        uint256 _entireAssetPortfolioDebt,
        address[] memory _assetPortfolioTokens,
        uint256[] memory _assetPortfolioAmountsToLiquidate,
        uint256[] memory _entireAssetPortfolioAmounts,
        uint256[] memory _collGasCompensation
    ) internal view returns (LiquidationValues memory singleLiquidation) {

        uint256 USD_Value_To_Send_To_SP_Base_100 = MCR.mul(_entireAssetPortfolioDebt);
        uint256 USD_Value_of_AssetPortfolio_Colls = _getUSDColls(newColls(_assetPortfolioTokens, _assetPortfolioAmountsToLiquidate));

        uint256 SPRatio = USD_Value_To_Send_To_SP_Base_100.mul(1e9).div(USD_Value_of_AssetPortfolio_Colls);
        // Min between 100% with extra 1e9 precision, and SPRatio. 
        SPRatio = PalmMath._min(SPRatio, SPRatioPrecision);

        singleLiquidation.entireAssetPortfolioDebt = _entireAssetPortfolioDebt;
        singleLiquidation.entireAssetPortfolioColl.tokens = _assetPortfolioTokens;
        singleLiquidation.entireAssetPortfolioColl.amounts = _entireAssetPortfolioAmounts;

        singleLiquidation.PUSTGasCompensation = PUST_GAS_COMPENSATION;

        singleLiquidation.debtToOffset = _entireAssetPortfolioDebt;
        singleLiquidation.debtToRedistribute = 0;

        singleLiquidation.collToSendToSP.tokens = _assetPortfolioTokens;
        uint256 assetPortfolioTokensLen = _assetPortfolioTokens.length;

        singleLiquidation.collToSendToSP.amounts = new uint256[](assetPortfolioTokensLen);

        singleLiquidation.collSurplus.tokens = _assetPortfolioTokens;
        singleLiquidation.collSurplus.amounts = new uint256[](assetPortfolioTokensLen);

        singleLiquidation.collGasCompensation.tokens = _assetPortfolioTokens;
        singleLiquidation.collGasCompensation.amounts = _collGasCompensation;

        for (uint256 i; i < assetPortfolioTokensLen; ++i) {
            uint256 _cappedCollAmount = SPRatio.mul(_assetPortfolioAmountsToLiquidate[i]).div(SPRatioPrecision);
            uint256 _collSurplus = _assetPortfolioAmountsToLiquidate[i].sub(_cappedCollAmount);

            singleLiquidation.collToSendToSP.amounts[i] = _cappedCollAmount;
            singleLiquidation.collSurplus.amounts[i] = _collSurplus;
        }
    }

    function _sendGasCompensation(
        IActivePool _activePool,
        address _liquidator,
        uint256 _PUST,
        address[] memory _tokens,
        uint256[] memory _amounts
    ) internal {
        if (_PUST != 0) {
            pustTokenContract.returnFromPool(gasPoolAddress, _liquidator, _PUST);
        }

        _activePool.sendCollateralsUnwrap(_liquidator, _tokens, _amounts);
    }

    function _requireCallerisAssetPortfolioManager() internal view {
        require(msg.sender == address(assetPortfolioManager), "Caller not TM");
    }

    /**
     * @notice Return the amount of collateral to be drawn from a assetPortfolio's collateral and sent as gas compensation
     */
    function _getCollGasCompensation(newColls memory _coll) internal pure returns (newColls memory) {
        uint256[] memory amounts = new uint256[](_coll.tokens.length);
        for (uint256 i; i < _coll.tokens.length; ++i) {
            amounts[i] = _coll.amounts[i] / PERCENT_DIVISOR;
        }
        return newColls(_coll.tokens, amounts);
    }

    /**
     * @notice Check whether or not the system *would be* in Recovery Mode, given the entire system coll and debt
     * @param _entireSystemColl The collateral of the entire system
     * @param _entireSystemDebt The debt of the entire system
     * @return returns true if the system would be in recovery mode and false if not
     */
    function _checkPotentialRecoveryMode(uint256 _entireSystemColl, uint256 _entireSystemDebt)
        internal
        pure
        returns (bool)
    {
        uint256 TCR = _computeCR(_entireSystemColl, _entireSystemDebt);

        return TCR < CCR;
    }
}