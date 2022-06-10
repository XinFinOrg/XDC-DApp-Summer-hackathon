// SPDX-License-Identifier: UNLICENSED

pragma solidity 0.6.11;

import "./Interfaces/IActivePool.sol";
import "./Interfaces/IPalmController.sol";
import "./Interfaces/IERC20.sol";
import "./Interfaces/IPalmVaultToken.sol";
import "./Interfaces/IDefaultPool.sol";
import "./Dependencies/SafeMath.sol";
import "./Dependencies/PoolBase2.sol";
import "./Dependencies/SafeERC20.sol";



/**
 * @title Holds the all collateral and PUST debt (but not PUST tokens) for all active assetPortfolios
 * @notice When a assetPortfolio is liquidated, its collateral and PUST debt are transferred from the Active Pool, to either the
 * Stability Pool, the Default Pool, or both, depending on the liquidation conditions
 */
contract ActivePool is IActivePool, PoolBase2 {
    using SafeMath for uint256;
    using SafeERC20 for IERC20;

    bytes32 public constant NAME = "ActivePool";

    address internal borrowerOperationsAddress;
    address internal assetPortfolioManagerAddress;
    address internal stabilityPoolAddress;
    address internal defaultPoolAddress;
    address internal assetPortfolioManagerLiquidationsAddress;
    address internal assetPortfolioManagerRedemptionsAddress;
    address internal collSurplusPoolAddress;

    // deposited collateral tracker. Colls is always the controller list of all collateral tokens. Amounts
    newColls internal poolColl;

    // PUST Debt tracker. Tracker of all debt in the system.
    uint256 public PUSTDebt;

    // --- Events ---

    event ActivePoolPUSTDebtUpdated(uint _PUSTDebt);
    event ActivePoolBalanceUpdated(address _collateral, uint _amount);
    event ActivePoolBalancesUpdated(address[] _collaterals, uint256[] _amounts);
    event CollateralsSent(address[] _collaterals, uint256[] _amounts, address _to);

    // --- Contract setters ---
    bool private addressSet;
    /**
     * @notice Sets the addresses of all contracts used
     */
    function setAddresses(
        address _borrowerOperationsAddress,
        address _assetPortfolioManagerAddress,
        address _stabilityPoolAddress,
        address _defaultPoolAddress,
        address _controllerAddress,
        address _assetPortfolioManagerLiquidationsAddress,
        address _assetPortfolioManagerRedemptionsAddress,
        address _collSurplusPoolAddress
    ) external {
        require(addressSet == false, "Addresses already set");
        addressSet = true;

        borrowerOperationsAddress = _borrowerOperationsAddress;
        assetPortfolioManagerAddress = _assetPortfolioManagerAddress;
        stabilityPoolAddress = _stabilityPoolAddress;
        defaultPoolAddress = _defaultPoolAddress;
        controller = IPalmController(_controllerAddress);
        assetPortfolioManagerLiquidationsAddress = _assetPortfolioManagerLiquidationsAddress;
        assetPortfolioManagerRedemptionsAddress = _assetPortfolioManagerRedemptionsAddress;
        collSurplusPoolAddress = _collSurplusPoolAddress;
    }

    // --- Getters for public variables. Required by IPool interface ---

    /**
     * @notice Returns the amount of a given collateral in state. Not necessarily the contract's actual balance since people can
     *  send collateral in
     */
    function getCollateral(address _collateral) public view override returns (uint256) {
        return poolColl.amounts[controller.getIndex(_collateral)];
    }

    /**
     * @notice Returns all collateral balances in state. Not necessarily the contract's actual balances. since people can send collateral in
     */
    function getAllCollateral() external view override returns (address[] memory, uint256[] memory) {
        return (poolColl.tokens, poolColl.amounts);
    }

    /**
     * @notice returns the VC value of a given collateralAddress in this contract
     * @param _collateral The address of the collateral
     */
    function getCollateralVC(address _collateral) external view override returns (uint256) {
        return controller.getValueVC(_collateral, getCollateral(_collateral));
    }

    /** 
     * @notice returns the individual Amount value of a subset of collaterals in this contract and the Default Pool 
     * contract as well. AP + DP Balance 
     * @dev used in getTotalVariableDepositFeeAndUpdate in PalmController
     * @param _collaterals collaterals to get the amount value of
     * @return the Amounts of the collaterals in this contract and the Default Pool
     */
    function getAmountsSubsetSystem(address[] memory _collaterals) external view override returns (uint256[] memory) {
        (uint256[] memory summedAmounts, uint256[] memory controllerIndices) = IDefaultPool(defaultPoolAddress).getAmountsSubset(_collaterals);
        for (uint i = 0; i < _collaterals.length; i++) {
            summedAmounts[i] = summedAmounts[i].add(poolColl.amounts[controllerIndices[i]]);
        }
        return summedAmounts;
    }

    /**
     * @notice Returns the VC value of the contract's collateral held
     * @dev Not necessarily equal to the the contract's raw VC balance - Collateral can be forcibly sent to contracts
     *  Computed when called by taking the collateral balances and multiplying them by the corresponding price and ratio and then summing that
     */
    function getVC() external view override returns (uint256 totalVC) {
        return controller.getValuesVC(poolColl.tokens, poolColl.amounts);
    }

    /**
     * @notice Function for aggregating active pool and default pool amounts when looping through
     * @dev more gas efficient than looping through through all coll in both default pool and this pool
     */
    function getVCSystem() external view override returns (uint256 totalVCSystem) {
        uint256 len = poolColl.tokens.length;
        uint256[] memory summedAmounts = IDefaultPool(defaultPoolAddress).getAllAmounts();
        for (uint256 i; i < len; ++i) {
            summedAmounts[i] = summedAmounts[i].add(poolColl.amounts[i]);
        }
        return controller.getValuesVC(poolColl.tokens, summedAmounts);
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
     * @notice Function for getting the VC value but using the Recovery ratio instead of the safety ratio
     * @dev Aggregates active pool and default pool amounts in one function loop for gas efficiency
     * @return totalVC VC value of the collateral in this contract, using safety ratio
     * @return totalRVC VC value of the collateral in this contract, using recovery ratio
     */
    function getVCAndRVCSystem()
        external
        view
        override
        returns (uint256 totalVC, uint256 totalRVC)
    {
        uint256 len = poolColl.tokens.length;
        uint256[] memory summedAmounts = IDefaultPool(defaultPoolAddress).getAllAmounts();
        for (uint256 i; i < len; ++i) {
            summedAmounts[i] = summedAmounts[i].add(poolColl.amounts[i]);
        }
        (totalVC, totalRVC) = controller.getValuesVCAndRVC(poolColl.tokens, summedAmounts);
    }

    /**
     * @notice returns PUST Debt that this pool holds
     */
    function getPUSTDebt() external view override returns (uint256) {
        return PUSTDebt;
    }

    // --- Pool functionality ---

    /**
     * @notice Internal function to send collateral out of this contract
     * @param _to Address to sent collateral to
     * @param _collateral Address of collateral
     * @param _amount The amount of collateral to be sent
     */
    function _sendCollateral(
        address _to,
        address _collateral,
        uint256 _amount,
        uint256 _index
    ) internal {
        _logCollateralDecrease(_to, _collateral, _amount, _index);
        IERC20(_collateral).safeTransfer(_to, _amount);
    }

    /** 
     * @notice Internal function to log collateral decrease, after sending
     * collateral out either from just a transfer or from vault token action
     */
    function _logCollateralDecrease(
        address _to,
        address _collateral,
        uint256 _amount,
        uint256 _index
    ) internal {
        poolColl.amounts[_index] = poolColl.amounts[_index].sub(_amount);
        emit ActivePoolBalanceUpdated(_collateral, _amount);
        emit CollateralSent(_collateral, _to, _amount);
    }

    /**
     * @notice Function sends multiple collaterals from active pool. If the receiver is a pool, updates the balance.
     * @dev Must be called by borrower operations, assetPortfolio manager, or stability pool
     * @param _to Address to send collateral to
     * @param _tokens Number of tokens
     * @param _amounts Amount of collateral to be sent
     */
    function sendCollaterals(
        address _to,
        address[] calldata _tokens,
        uint256[] calldata _amounts
    ) external override {
        _requireCallerIsBOorAssetPortfolioMorTMLorSP();
        uint256 len = _tokens.length;
        require(len == _amounts.length, "AP:Lengths");
        uint256[] memory indices = controller.getIndices(_tokens);
        for (uint256 i; i < len; ++i) {
            uint256 thisAmount = _amounts[i];
            if (thisAmount != 0) {
                _sendCollateral(_to, _tokens[i], thisAmount, indices[i]); // reverts if send fails
            }
        }

        if (_needsUpdateCollateral(_to)) {
            ICollateralReceiver(_to).receiveCollateral(_tokens, _amounts);
        }

        emit CollateralsSent(_tokens, _amounts, _to);
    }

    /**
     * @notice This function calls unwraps the collaterals and sends them to _to, if they are vault tokens assets. 
     * @dev Not callable from outside the protocol
     * @param _to Address of where collaterals send to
     * @param _tokens Collateral list addresses
     * @param _amounts Amount list of collateral to be sent
     */
    function sendCollateralsUnwrap(
        address _to,
        address[] calldata _tokens,
        uint256[] calldata _amounts
    ) external override {
        _requireCallerIsBOorAssetPortfolioMorTMLorSP();
        uint256 tokensLen = _tokens.length;
        require(tokensLen == _amounts.length, "AP:Lengths");
        uint256[] memory indices = controller.getIndices(_tokens);
        bool[] memory isWrapped = controller.isWrappedMany(_tokens);
        for (uint256 i; i < tokensLen; ++i) {
            if (isWrapped[i]) {
                address collateral = _tokens[i];
                uint256 amount = _amounts[i];

                // Update pool coll tracker
                _logCollateralDecrease(_to, collateral, amount, indices[i]);

                // Unwraps for original owner. _amounts[i] is in terms of the receipt token, and
                // the user will receive back the underlying based on the current exchange rate. 
                IPalmVaultToken(collateral).redeem(_to, amount);

            } else {
                _sendCollateral(_to, _tokens[i], _amounts[i], indices[i]); // reverts if send fails
            }
        }
    }

    /**
     * @notice Function for sending single collateral
     */
    function sendSingleCollateral(
        address _to,
        address _token,
        uint256 _amount
    ) external override {
        _requireCallerIsBOorTMorTML();
        _sendCollateral(_to, _token, _amount, controller.getIndex(_token)); // reverts if send fails
    }

    /**
     * @notice Function for sending single collateral and unwrapping. Currently only used by borrower operations unlever up functionality
     * Unwraps asset for the user in that case.
     */
    function sendSingleCollateralUnwrap(
        address _to,
        address _token,
        uint256 _amount
    ) external override {
        _requireCallerIsBorrowerOperations();
        if (controller.isWrapped(_token)) {
            // Unwraps for original owner. _amounts[i] is in terms of the receipt token, and
            // the user will receive back the underlying based on the current exchange rate.
            _logCollateralDecrease(_to, _token, _amount, controller.getIndex(_token));
            IPalmVaultToken(_token).redeem(_to, _amount);
        } else {
            _sendCollateral(_to, _token, _amount, controller.getIndex(_token)); // reverts if send fails
        }
    }

    /**
     * @notice View function that returns if the contract transferring to needs to have its balances updated, aka is a pool in the protocol other than this one.
     * @param _contractAddress The address of the contract
     * @return True if balances need to be updated, False if balances don't need to be updated
     */
    function _needsUpdateCollateral(address _contractAddress) internal view returns (bool) {
        return ((_contractAddress == defaultPoolAddress) ||
            (_contractAddress == stabilityPoolAddress) ||
            (_contractAddress == collSurplusPoolAddress));
    }

    /**
     * @notice Increases the tracked PUST Debt of this pool.
     * @param _amount to increase by
     */
    function increasePUSTDebt(uint256 _amount) external override {
        _requireCallerIsBOorTMorTML();
        PUSTDebt = PUSTDebt.add(_amount);
        emit ActivePoolPUSTDebtUpdated(PUSTDebt);
    }

    /**
     * @notice Increases the tracked PUST Debt of this pool.
     * @param _amount to decrease by
     */
    function decreasePUSTDebt(uint256 _amount) external override {
        _requireCallerIsBOorAssetPortfolioMorSP();
        PUSTDebt = PUSTDebt.sub(_amount);
        emit ActivePoolPUSTDebtUpdated(PUSTDebt);
    }

    /**
     * @dev should be called by BorrowerOperations or DefaultPool
     * __after__ collateral is transferred to this contract
     */
    function receiveCollateral(address[] calldata _tokens, uint256[] calldata _amounts)
        external
        override
    {
        _requireCallerIsBorrowerOperationsOrDefaultPool();
        poolColl.amounts = _leftSumColls(poolColl, _tokens, _amounts);
        emit ActivePoolBalancesUpdated(_tokens, _amounts);
    }

    /**
     * @notice Adds collateral type from controller. The controller whitelisted list of collateral should always be
     * equal to the whitelisted ActivePool poolColl list.
     * @param _collateral The address of the collateral
     */
    function addCollateralType(address _collateral) external override {
        _requireCallerIsPalmController();
        poolColl.tokens.push(_collateral);
        poolColl.amounts.push(0);
    }

    // --- 'require' functions ---

    function _requireCallerIsBOorAssetPortfolioMorTMLorSP() internal view {
        if (
            msg.sender != borrowerOperationsAddress &&
            msg.sender != assetPortfolioManagerAddress &&
            msg.sender != stabilityPoolAddress &&
            msg.sender != assetPortfolioManagerLiquidationsAddress &&
            msg.sender != assetPortfolioManagerRedemptionsAddress
        ) {
            _revertWrongFuncCaller();
        }
    }

    function _requireCallerIsBorrowerOperationsOrDefaultPool() internal view {
        if (msg.sender != borrowerOperationsAddress && msg.sender != defaultPoolAddress) {
            _revertWrongFuncCaller();
        }
    }

    function _requireCallerIsBorrowerOperations() internal view {
        if (msg.sender != borrowerOperationsAddress) {
            _revertWrongFuncCaller();
        }
    }

    function _requireCallerIsBOorAssetPortfolioMorSP() internal view {
        if (
            msg.sender != borrowerOperationsAddress &&
            msg.sender != assetPortfolioManagerAddress &&
            msg.sender != stabilityPoolAddress &&
            msg.sender != assetPortfolioManagerRedemptionsAddress
        ) {
            _revertWrongFuncCaller();
        }
    }

    function _requireCallerIsBOorTMorTML() internal view {
        if (msg.sender != borrowerOperationsAddress && msg.sender != assetPortfolioManagerAddress && msg.sender != assetPortfolioManagerLiquidationsAddress) {
            _revertWrongFuncCaller();
        }
    }
}