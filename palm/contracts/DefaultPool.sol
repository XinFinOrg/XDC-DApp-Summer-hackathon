// SPDX-License-Identifier: UNLICENSED

pragma solidity 0.6.11;

import "./Interfaces/IDefaultPool.sol";
import "./Interfaces/IActivePool.sol";
import "./Interfaces/IPalmController.sol";
import "./Interfaces/IERC20.sol";
import "./Dependencies/SafeMath.sol";
import "./Dependencies/PoolBase2.sol";
import "./Dependencies/SafeERC20.sol";



/**
 * @notice The Default Pool holds the collateral and PUST debt (but not PUST tokens) from liquidations that have been redistributed
 * to active assetPortfolios but not yet "applied", i.e. not yet recorded on a recipient active assetPortfolio's struct.
 *
 * @dev When a assetPortfolio makes an operation that applies its pending collateral and PUST debt, its pending collateral and PUST debt is moved
 * from the Default Pool to the Active Pool. This contract has never been used in Liquity.
 */

contract DefaultPool is IDefaultPool, PoolBase2 {
    using SafeMath for uint256;
    using SafeERC20 for IERC20;

    string public constant NAME = "DefaultPool";

    address internal assetPortfolioManagerAddress;
    address internal assetPortfolioManagerLiquidationsAddress;
    address internal activePoolAddress;

    // deposited collateral tracker. Colls is always the controller list of all collateral tokens. Amounts
    newColls internal poolColl;

    uint256 public PUSTDebt;

    event DefaultPoolPUSTDebtUpdated(uint256 _PUSTDebt);
    event DefaultPoolBalanceUpdated(address _collateral, uint256 _amount);
    event DefaultPoolBalancesUpdated(address[] _collaterals, uint256[] _amounts);

    // --- Dependency setters ---
    bool private addressSet;
    /**
     * @notice set the addresses
     * @dev Also make sure addresses are valid
     * @param _assetPortfolioManagerAddress address
     * @param _activePoolAddress address
     * @param _controllerAddress address
     */
    function setAddresses(
        address _assetPortfolioManagerAddress,
        address _assetPortfolioManagerLiquidationsAddress,
        address _activePoolAddress,
        address _controllerAddress
    ) external {
        require(addressSet == false, "Addresses already set");
        addressSet = true;

        assetPortfolioManagerAddress = _assetPortfolioManagerAddress;
        assetPortfolioManagerLiquidationsAddress = _assetPortfolioManagerLiquidationsAddress;
        activePoolAddress = _activePoolAddress;
        controller = IPalmController(_controllerAddress);
    }

    // --- Getters for public variables. Required by IPool interface ---

    /**
     * @notice Returns the collateralBalance for a given collateral
     * @dev Returns the amount of a given collateral in state. Not necessarily the contract's actual balance.
     * @param _collateral address collateral
     * @return uint256 collateral amount
     */
    function getCollateral(address _collateral) public view override returns (uint256) {
        return poolColl.amounts[controller.getIndex(_collateral)];
    }

    /**
     * @notice Returns all collateral balances in state. Not necessarily the contract's actual balances.
     * @return colls of addresses of tokens, array of uint256 of amounts for each token
     * @return amounts of collateral in state
     */
    function getAllCollateral() external view override returns (address[] memory, uint256[] memory) {
        return (poolColl.tokens, poolColl.amounts);
    }

    /**
     * @notice Returns all collateral balances in state. Not necessarily the contract's actual balances.
     * @return array of uint256 of amounts for each token
     */
    function getAllAmounts() external view override returns (uint256[] memory) {
        return poolColl.amounts;
    }

    /**
     * @notice returns the VC value of a given collateralAddress in this contract
     */
    function getCollateralVC(address _collateral) external view override returns (uint256) {
        return controller.getValueVC(_collateral, getCollateral(_collateral));
    }

    /** 
     * @notice returns a subset amount of the collateral balances in this contract
     * intended for use in getVCSubsetSystem in ActivePool
     */
    function getAmountsSubset(address[] memory _collaterals) external view override returns (uint256[] memory amounts, uint256[] memory controllerIndices) {
        controllerIndices = controller.getIndices(_collaterals);
        uint256 len = _collaterals.length;
        amounts = new uint256[](len);
        for (uint256 i = 0; i < len; i++) {
            amounts[i] = poolColl.amounts[controllerIndices[i]];
        }
    }

    /**
     * @notice Returns the VC of the contract
     *
     * Not necessarily equal to the the contract's raw VC balance - Collateral can be forcibly sent to contracts.
     *
     * @dev Computed when called by taking the collateral balances and
     * multiplying them by the corresponding price and ratio and then summing that
     * @return totalVC total VC uint256
     */
    function getVC() external view override returns (uint256 totalVC) {
        return controller.getValuesVC(poolColl.tokens, poolColl.amounts);
    }

    /**
     * @notice Returns VC as well as RVC of the collateral in this contract
     * @return totalVC the VC using collateral weight
     * @return totalRVC the VC using redemption collateral weight
     */
    function getVCAndRVC() external view override returns (uint256 totalVC, uint256 totalRVC) {
        (totalVC, totalRVC) = controller.getValuesVCAndRVC(poolColl.tokens, poolColl.amounts);
    }

    /**
     * @notice Debt that DefaultPool holds in total
     */
    function getPUSTDebt() external view override returns (uint256) {
        return PUSTDebt;
    }

    // --- Functions for sending to Active Pool ---

    /**
     * @notice Sends collaterals to the active pool when 'rewards' are claimed.
     * @dev must be called by AssetPortfolioManager
     *   This is called when a user adjusts their assetPortfolio who has pending colls.
     *   Those pending colls were sitting in the DefaultPool but now
     *   they will be a part of the user's assetPortfolio and go to the ActivePool.
     *   This is also called when a assetPortfolio gets liquidated as that user's pendingColl
     *   needs to go to the Active Pool before being distributed to relevant parties.
     * @param _tokens array of addresses
     * @param _amounts array of uint256
     */
    function sendCollsToActivePool(
        address[] memory _tokens,
        uint256[] memory _amounts
    ) external override {
        _requireCallerIsAssetPortfolioManager();
        uint256 tokensLen = _tokens.length;
        require(tokensLen == _amounts.length, "DP:Length mismatch");
        uint256[] memory indices = controller.getIndices(_tokens);
        for (uint256 i; i < tokensLen; ++i) {
            uint256 thisAmounts = _amounts[i];
            if (thisAmounts != 0) {
                address thisToken = _tokens[i];
                _sendCollateral(thisToken, thisAmounts, indices[i]);
            }
        }
        IActivePool(activePoolAddress).receiveCollateral(_tokens, _amounts);
    }

    /**
     * @notice Internal function to send collateral to the ActivePool
     * @dev this is only utilized in sendCollsToActivePool function
     */
    function _sendCollateral(address _collateral, uint256 _amount, uint256 _index) internal {
        address activePool = activePoolAddress;
        poolColl.amounts[_index] = poolColl.amounts[_index].sub(_amount);

        IERC20(_collateral).safeTransfer(activePool, _amount);

        emit DefaultPoolBalanceUpdated(_collateral, _amount);
        emit CollateralSent(_collateral, activePool, _amount);
    }

    // --- Pool accounting functions ---

    /**
     * @notice Should be called by ActivePool
     * @dev __after__ collateral is transferred to this contract from Active Pool
     * This is only called during a redistribution.
     */
    function receiveCollateral(address[] memory _tokens, uint256[] memory _amounts)
        external
        override
    {
        _requireCallerIsActivePool();
        poolColl.amounts = _leftSumColls(poolColl, _tokens, _amounts);
        emit DefaultPoolBalancesUpdated(_tokens, _amounts);
    }

    /**
     * @notice Adds collateral type from controller.
     * @dev This is to keep the array updated to we can always do
     *   leftSumColls when receiving new collateral.
     */
    function addCollateralType(address _collateral) external override {
        _requireCallerIsPalmController();
        poolColl.tokens.push(_collateral);
        poolColl.amounts.push(0);
    }

    /**
     * @notice Increases the PUST Debt of this pool. Called when new PUST is sent to this contract
     *   by redistribution.
     */
    function increasePUSTDebt(uint256 _amount) external override {
        _requireCallerIsAssetPortfolioManager();
        PUSTDebt = PUSTDebt.add(_amount);
        emit DefaultPoolPUSTDebtUpdated(PUSTDebt);
    }

    /**
     * @notice Decreases the PUST Debt of this pool. Called when PUST is sent to active pool when 'rewards'
     *   are claimed.
     */
    function decreasePUSTDebt(uint256 _amount) external override {
        _requireCallerIsAssetPortfolioManager();
        PUSTDebt = PUSTDebt.sub(_amount);
        emit DefaultPoolPUSTDebtUpdated(PUSTDebt);
    }

    // --- 'require' functions ---
    /**
     * @notice Checks if caller is Active Pool
     */
    function _requireCallerIsActivePool() internal view {
        if (msg.sender != activePoolAddress) {
            _revertWrongFuncCaller();
        }
    }

    /**
     * @notice Checks if caller is AssetPortfolio Manager
     */
    function _requireCallerIsAssetPortfolioManager() internal view {
        if (msg.sender != assetPortfolioManagerAddress && msg.sender != assetPortfolioManagerLiquidationsAddress) {
            _revertWrongFuncCaller();
        }
    }
}