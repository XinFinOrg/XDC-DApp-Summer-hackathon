// SPDX-License-Identifier: UNLICENSED

pragma solidity 0.6.11;

import "./ILiquityBase.sol";
import "./IStabilityPool.sol";
import "./IPUSTToken.sol";
import "./IPALMToken.sol";
import "./IActivePool.sol";
import "./IDefaultPool.sol";


// Common interface for the AssetPortfolio Manager.
interface IAssetPortfolioManager is ILiquityBase {

    // --- Events ---

    event Redemption(uint _attemptedPUSTAmount, uint _actualPUSTAmount, uint PUSTfee, address[] tokens, uint[] amounts);
    event AssetPortfolioLiquidated(address indexed _borrower, uint _debt, uint _coll, uint8 operation);
    event BaseRateUpdated(uint _baseRate);
    event LastFeeOpTimeUpdated(uint _lastFeeOpTime);
    event TotalStakesUpdated(address token, uint _newTotalStakes);
    event SystemSnapshotsUpdated(uint _totalStakesSnapshot, uint _totalCollateralSnapshot);
    event LTermsUpdated(uint _L_ETH, uint _L_PUSTDebt);
    event AssetPortfolioSnapshotsUpdated(uint _L_ETH, uint _L_PUSTDebt);
    event AssetPortfolioIndexUpdated(address _borrower, uint _newIndex);

    // --- Functions ---

    function setAddresses(
        address _borrowerOperationsAddress,
        address _activePoolAddress,
        address _defaultPoolAddress,
        address _sortedAssetPortfoliosAddress,
        address _controllerAddress,
        address _assetPortfolioManagerRedemptionsAddress,
        address _assetPortfolioManagerLiquidationsAddress
    )
    external;

    function getAssetPortfolioOwnersCount() external view returns (uint);

    function getAssetPortfolioFromAssetPortfolioOwnersArray(uint _index) external view returns (address);

    function getCurrentICR(address _borrower) external view returns (uint);

    function getCurrentAICR(address _borrower) external view returns (uint);

    function liquidate(address _borrower) external;

    function batchLiquidateAssetPortfolios(address[] calldata _assetPortfolioArray, address _liquidator) external;

    function redeemCollateral(
        uint _PUSTAmount,
        uint _PUSTMaxFee,
        address _firstRedemptionHint,
        address _upperPartialRedemptionHint,
        address _lowerPartialRedemptionHint,
        uint _partialRedemptionHintNICR,
        uint _maxIterations
    ) external;

    function redeemCollateralSingle(
        uint256 _PUSTamount,
        uint256 _PUSTMaxFee,
        address _target, 
        address _upperHint, 
        address _lowerHint, 
        uint256 _hintAICR,
        address _collToRedeem
    ) external;

    function updateAssetPortfolioRewardSnapshots(address _borrower) external;

    function addAssetPortfolioOwnerToArray(address _borrower) external returns (uint index);

    function applyPendingRewards(address _borrower) external;

    function getPendingCollRewards(address _borrower) external view returns (address[] memory, uint[] memory);

    function getPendingPUSTDebtReward(address _borrower) external view returns (uint);

     function hasPendingRewards(address _borrower) external view returns (bool);

    function removeStakeAndCloseAssetPortfolio(address _borrower) external;

    function updateAssetPortfolioDebt(address _borrower, uint debt) external;

    function getRedemptionRate() external view returns (uint);
    function getRedemptionRateWithDecay() external view returns (uint);

    function getRedemptionFeeWithDecay(uint _ETHDrawn) external view returns (uint);

    function getBorrowingRate() external view returns (uint);
    function getBorrowingRateWithDecay() external view returns (uint);

    function getBorrowingFee(uint PUSTDebt) external view returns (uint);
    function getBorrowingFeeWithDecay(uint _PUSTDebt) external view returns (uint);

    function decayBaseRateFromBorrowingAndCalculateFee(uint256 _PUSTDebt) external returns (uint);

    function getAssetPortfolioStatus(address _borrower) external view returns (uint);

    function isAssetPortfolioActive(address _borrower) external view returns (bool);

    function getAssetPortfolioStake(address _borrower, address _token) external view returns (uint);

    function getTotalStake(address _token) external view returns (uint);

    function getAssetPortfolioDebt(address _borrower) external view returns (uint);

    function getL_Coll(address _token) external view returns (uint);

    function getL_PUST(address _token) external view returns (uint);

    function getRewardSnapshotColl(address _borrower, address _token) external view returns (uint);

    function getRewardSnapshotPUST(address _borrower, address _token) external view returns (uint);

    function getAssetPortfolioVC(address _borrower) external view returns (uint);

    function getAssetPortfolioColls(address _borrower) external view returns (address[] memory, uint[] memory);

    function getCurrentAssetPortfolioState(address _borrower) external view returns (address[] memory, uint[] memory, uint);

    function setAssetPortfolioStatus(address _borrower, uint num) external;

    function updateAssetPortfolioCollAndStakeAndTotalStakes(address _borrower, address[] memory _tokens, uint[] memory _amounts) external;

    function increaseAssetPortfolioDebt(address _borrower, uint _debtIncrease) external returns (uint);

    function decreaseAssetPortfolioDebt(address _borrower, uint _collDecrease) external returns (uint);

    function getTCR() external view returns (uint);

    function checkRecoveryMode() external view returns (bool);

    function closeAssetPortfolioRedemption(address _borrower) external;

    function closeAssetPortfolioLiquidation(address _borrower) external;

    function removeStake(address _borrower) external;

    function updateBaseRate(uint newBaseRate) external;

    function calcDecayedBaseRate() external view returns (uint);

    function redistributeDebtAndColl(IActivePool _activePool, IDefaultPool _defaultPool, uint _debt, address[] memory _tokens, uint[] memory _amounts) external;

    function updateSystemSnapshots_excludeCollRemainder(IActivePool _activePool, address[] memory _tokens, uint[] memory _amounts) external;

    function getEntireDebtAndColls(address _borrower) external view
    returns (uint, address[] memory, uint[] memory, uint, address[] memory, uint[] memory);

    function updateAssetPortfolios(address[] calldata _borrowers, address[] calldata _lowerHints, address[] calldata _upperHints) external;

    function updateUnderCollateralizedAssetPortfolios(address[] memory _ids) external;

    function getMCR() external view returns (uint256);

    function getCCR() external view returns (uint256);
    
    function getPUST_GAS_COMPENSATION() external view returns (uint256);
    
    function getMIN_NET_DEBT() external view returns (uint256);
    
    function getBORROWING_FEE_FLOOR() external view returns (uint256);

    function getREDEMPTION_FEE_FLOOR() external view returns (uint256);
}