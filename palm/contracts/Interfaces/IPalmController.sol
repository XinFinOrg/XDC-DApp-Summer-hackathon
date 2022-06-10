// SPDX-License-Identifier: UNLICENSED

pragma solidity 0.6.11;


interface IPalmController {

    // ======== Mutable Only Owner-Instantaneous ========
    function setAddresses(
        address _activePoolAddress,
        address _defaultPoolAddress,
        address _stabilityPoolAddress,
        address _collSurplusPoolAddress,
        address _borrowerOperationsAddress,
        address _pustTokenAddress,
        address _PUSTFeeRecipientAddress,
        address _palmFinanceTreasury,
        address _sortedAssetPortfoliosAddress,
        address _vePALMAddress,
        address _assetPortfolioManagerRedemptionsAddress,
        address _claimAddress,
        address _threeDayTimelock,
        address _twoWeekTimelock
    ) external;
    function endBootstrap() external;
    function deprecateAllCollateral() external;
    function deprecateCollateral(address _collateral) external;
    function setLeverUp(bool _enabled) external;
    function setFeeBootstrapPeriodEnabled(bool _enabled) external;
    function updateGlobalPUSTMinting(bool _canMint) external;
    function removeValidPUSTMinter(address _minter) external;
    function removevePALMCaller(address _contractAddress) external;
    function updateRedemptionsEnabled(bool _enabled) external;
    function changeFeeCurve(address _collateral, address _feeCurve) external;


    // ======== Mutable Only Owner-3 Day TimeLock ========
    function addCollateral(
        address _collateral,
        uint256 _safetyRatio,
        uint256 _recoveryRatio,
        address _oracle,
        uint256 _decimals,
        address _feeCurve,
        bool _isWrapped,
        address _routerAddress
    ) external;
    function unDeprecateCollateral(address _collateral) external;
    function updateMaxCollsInAssetPortfolio(uint _newMax) external;
    function changeRatios(address _collateral, uint256 _newSafetyRatio, uint256 _newRecoveryRatio) external;
    function setDefaultRouter(address _collateral, address _router) external;
    function changePalmFinanceTreasury(address _newTreasury) external;
    function changeClaimAddress(address _newClaimAddress) external;
    function changePUSTFeeRecipient(address _newFeeRecipient) external;
    function changePalmFinanceTreasurySplit(uint256 _newSplit) external;
    function changeRedemptionBorrowerFeeSplit(uint256 _newSplit) external;
    function updateAbsorptionColls(address[] memory _colls, uint[] memory _weights) external;
    function changeOracle(address _collateral, address _oracle) external;


    // ======== Mutable Only Owner-2 Week TimeLock ========
    function addValidPUSTMinter(address _minter) external;
    function changeBoostMinuteDecayFactor(uint256 _newBoostMinuteDecayFactor) external;
    function changeGlobalBoostMultiplier(uint256 _newBoostMinuteDecayFactor) external;
    function addvePALMCaller(address _contractAddress) external;
    function updateMaxSystemColls(uint _newMax) external;


    // ======= VIEW FUNCTIONS FOR COLLATERAL PARAMS =======
    function getValidCollateral() view external returns (address[] memory);
    function getOracle(address _collateral) view external returns (address);
    function getSafetyRatio(address _collateral) view external returns (uint256);
    function getRecoveryRatio(address _collateral) view external returns (uint256);
    function getIsActive(address _collateral) view external returns (bool);
    function getFeeCurve(address _collateral) external view returns (address);
    function getDecimals(address _collateral) external view returns (uint256);
    function getIndex(address _collateral) external view returns (uint256);
    function getIndices(address[] memory _colls) external view returns (uint256[] memory indices);
    function checkCollateralListSingle(address[] memory _colls, bool _deposit) external view;
    function checkCollateralListDouble(address[] memory _depositColls, address[] memory _withdrawColls) external view;
    function isWrapped(address _collateral) external view returns (bool);
    function isWrappedMany(address[] memory _collaterals) external view returns (bool[] memory wrapped);
    function getDefaultRouterAddress(address _collateral) external view returns (address);


    // ======= VIEW FUNCTIONS FOR VC / USD VALUE =======
    function getPrice(address _collateral) external view returns (uint256);
    function getValuesVC(address[] memory _collaterals, uint[] memory _amounts) view external returns (uint);
    function getValuesRVC(address[] memory _collaterals, uint[] memory _amounts) view external returns (uint);
    function getValuesVCAndRVC(address[] memory _collaterals, uint[] memory _amounts) view external returns (uint VC, uint256 RVC);
    function getValuesUSD(address[] memory _collaterals, uint[] memory _amounts) view external returns (uint256);
    function getValueVC(address _collateral, uint _amount) view external returns (uint);
    function getValueRVC(address _collateral, uint _amount) view external returns (uint);
    function getValueUSD(address _collateral, uint _amount) view external returns (uint256);
    function getValuesVCIndividual(address[] memory _collaterals, uint256[] memory _amounts) external view returns (uint256[] memory);


    // ======= VIEW FUNCTIONS FOR CONTRACT FUNCTIONALITY =======
    function getPalmFinanceTreasury() external view returns (address);
    function getPalmFinanceTreasurySplit() external view returns (uint256);
    function getRedemptionBorrowerFeeSplit() external view returns (uint256);
    function getPUSTFeeRecipient() external view returns (address);
    function leverUpEnabled() external view returns (bool);
    function getMaxCollsInAssetPortfolio() external view returns (uint);
    function getFeeSplitInformation() external view returns (uint256, address, address);
    function getClaimAddress() external view returns (address);
    function getAbsorptionCollParams() external view returns (address[] memory, uint[] memory);
    function getVariableDepositFee(address _collateral, uint _collateralVCInput, uint256 _collateralVCBalancePost, uint256 _totalVCBalancePre, uint256 _totalVCBalancePost) external view returns (uint256 fee);



    // ======== Mutable Function For Fees ========
    function getTotalVariableDepositFeeAndUpdate(
        address[] memory _tokensIn,
        uint256[] memory _amountsIn,
        uint256[] memory _leverages,
        uint256 _entireSystemCollVC,
        uint256 _VCin,
        uint256 _VCout
    ) external returns (uint256 PUSTFee, uint256 boostFactor);

}