// SPDX-License-Identifier: UNLICENSED

pragma solidity 0.6.11;

import "./Interfaces/IPalmController.sol";
import "./Interfaces/IPriceFeed.sol";
import "./Interfaces/IFeeCurve.sol";
import "./Interfaces/IActivePool.sol";
import "./Interfaces/IDefaultPool.sol";
import "./Interfaces/IStabilityPool.sol";
import "./Interfaces/ISortedAssetPortfolios.sol";
import "./Interfaces/ICollSurplusPool.sol";
import "./Interfaces/IERC20.sol";
import "./Interfaces/IPUSTToken.sol";
import "./Interfaces/IvePALM.sol";
import "./Interfaces/IAssetPortfolioManagerRedemptions.sol";
import "./Dependencies/OwnableUpgradeable.sol";
import "./Dependencies/PalmMath.sol";
import "./Dependencies/CheckContract.sol";
import "./Dependencies/LiquityBase.sol";



/**
 * @notice PalmController is the contract that controls system parameters.
 * This includes things like enabling leverUp, feeBootstrap, and pausing the system.
 * PalmController also keeps track of all collateral parameters and allos
 * the team to update these. This includes: change the fee
 * curve, price feed, safety ratio, recovery ratio etc. as well
 * as adding or deprecating new collaterals. Some parameter changes
 * can be executed instantly, while others can only be updated by certain
 * Timelock contracts to ensure the community has a fair warning before updates.
 * PalmController also has view functions to get
 * prices and VC, and RVC values.
 */

contract PalmController is OwnableUpgradeable, IPalmController, CheckContract, LiquityBase {
    using SafeMath for uint256;

    struct CollateralParams {
        // Ratios: 10**18 * the ratio. i.e. ratio = 95E16 for 95%.
        // More risky collateral has a lower ratio
        uint256 safetyRatio;
        // Ratio used for recovery mode for the TCR as well as assetPortfolio ordering in SortedAssetPortfolios
        uint256 recoveryRatio;
        address oracle;
        uint256 decimals;
        address feeCurve;
        uint256 index;
        address defaultRouter;
        bool active;
        bool isWrapped;
    }

    struct DepositFeeCalc {
        // Calculated fee for that collateral local variable
        uint256 collateralPUSTFee;
        // VC value of collateral of that type in the system, from AP and DP balances.
        uint256[] systemCollateralVCs;
        // VC value of collateral of this type inputted
        uint256 collateralInputVC;
        // collateral we are dealing with
        address token;
        // active pool total VC post adding and removing all collaterals
        // This transaction adds VCin which is the sum of all collaterals added in from adjust assetPortfolio or
        // open assetPortfolio and VCout which is the sum of all collaterals withdrawn from adjust assetPortfolio.
        uint256 activePoolVCPost;
    }

    IStabilityPool private stabilityPool;
    ICollSurplusPool private collSurplusPool;
    IPUSTToken private pustToken;
    ISortedAssetPortfolios private sortedAssetPortfolios;
    IvePALM private vePALM;
    address private claimAddress;
    address private borrowerOperationsAddress;
    IAssetPortfolioManagerRedemptions private assetPortfolioManagerRedemptions;

    address public PUSTFeeRecipient;
    address public palmFinanceTreasury;
    uint256 public palmFinanceTreasurySplit;
    uint256 public redemptionBorrowerFeeSplit;

    bool public bootstrapEnded;
    bool public isLeverUpEnabled;
    bool public feeBootstrapPeriodEnabled;
    uint256 public maxCollsInAssetPortfolio;
    uint256 public maxSystemColls;
    address[] public absorptionColls;
    uint[] public absorptionWeights;

    address public threeDayTimelock;
    address public twoWeekTimelock;

    mapping(address => CollateralParams) public collateralParams;
    // list of all collateral types in collateralParams (active and deprecated)
    // Addresses for easy access
    address[] public validCollateral; // index maps to token address.

    event CollateralAdded(address _collateral);
    event CollateralDeprecated(address _collateral);
    event CollateralUndeprecated(address _collateral);
    event OracleChanged(address _collateral, address _newOracle);
    event FeeCurveChanged(address _collateral, address _newFeeCurve);
    event SafetyRatioChanged(address _collateral, uint256 _newSafetyRatio);
    event RecoveryRatioChanged(address _collateral, uint256 _newRecoveryRatio);

    // ======== Events for timelocked functions ========
    event LeverUpChanged(bool _enabled);
    event FeeBootstrapPeriodEnabledChanged(bool _enabled);
    event GlobalPUSTMintOn(bool _canMint);
    event PUSTMinterChanged(address _minter, bool _canMint);
    event DefaultRouterChanged(address _collateral, address _newDefaultRouter);
    event PalmFinanceTreasuryChanged(address _newTreasury);
    event ClaimAddressChanged(address _newClaimAddress);
    event PalmFinanceTreasurySplitChanged(uint256 _newSplit);
    event RedemptionBorrowerFeeSplitChanged(uint256 _newSplit);
    event PUSTFeeRecipientChanged(address _newFeeRecipient);
    event GlobalBoostMultiplierChanged(uint256 _newGlobalBoostMultiplier);
    event BoostMinuteDecayFactorChanged(uint256 _newBoostMinuteDecayFactor);
    event MaxCollsInAssetPortfolioChanged(uint256 _newMaxCollsInAssetPortfolio);
    event MaxSystemCollsChanged(uint256 _newMaxSystemColls);
    event UpdatevePALMCallers(address _contractAddress, bool _isWhitelisted);
    event RedemptionsEnabledUpdated(bool _enabled);


    // Require that the collateral exists in the controller. If it is not the 0th index, and the
    // index is still 0 then it does not exist in the mapping.
    // no require here for valid collateral 0 index because that means it exists.
    modifier exists(address _collateral) {
        _exists(_collateral);
        _;
    }

    // Calling from here makes it not inline, reducing contract size significantly.
    function _exists(address _collateral) internal view {
        if (validCollateral[0] != _collateral) {
            require(collateralParams[_collateral].index != 0, "collateral does not exist");
        }
    }

    // ======== Timelock modifiers ========


    function _requireThreeDayTimelock() internal view {
        if (bootstrapEnded) {
            require(msg.sender == threeDayTimelock, "Caller Not Three Day Timelock");
        } else {
            require(msg.sender == owner(), "Caller Not Owner");
        }
    }


    function _requireTwoWeekTimelock() internal view {
        if (bootstrapEnded) {
            require(msg.sender == twoWeekTimelock, "Caller Not Two Week Timelock");
        } else {
            require(msg.sender == owner(), "Caller Not Owner");
        }
    }


    // ======== Mutable Only Owner-Instantaneous ========
    bool private addressSet;
    function setAddresses(
        address _activePoolAddress,
        address _defaultPoolAddress,
        address _stabilityPoolAddress,
        address _collSurplusPoolAddress,
        address _borrowerOperationsAddress,
        address _pustTokenAddress,
        address _PUSTFeeRecipientAddress,  // _palmFinanceTreasury
        address _palmFinanceTreasury,
        address _sortedAssetPortfoliosAddress,
        address _vePALMAddress,
        address _assetPortfolioManagerRedemptionsAddress,
        address _claimAddress,  // _palmFinanceTreasury
        address _threeDayTimelock,  // TimelockAddress
        address _twoWeekTimelock // TimelockAddress
    ) external override {
        require(addressSet == false, "Addresses already set");
        addressSet = true;
        _transferOwnership(msg.sender);
        
        activePool = IActivePool(_activePoolAddress);
        defaultPool = IDefaultPool(_defaultPoolAddress);
        stabilityPool = IStabilityPool(_stabilityPoolAddress);
        collSurplusPool = ICollSurplusPool(_collSurplusPoolAddress);
        pustToken = IPUSTToken(_pustTokenAddress);
        PUSTFeeRecipient = _PUSTFeeRecipientAddress;
        borrowerOperationsAddress = _borrowerOperationsAddress;
        _requireAddressNonzero(_palmFinanceTreasury != address(0));
        palmFinanceTreasury = _palmFinanceTreasury;
        sortedAssetPortfolios = ISortedAssetPortfolios(_sortedAssetPortfoliosAddress);
        vePALM = IvePALM(_vePALMAddress);
        assetPortfolioManagerRedemptions = IAssetPortfolioManagerRedemptions(_assetPortfolioManagerRedemptionsAddress);
        _requireAddressNonzero(_claimAddress != address(0));
        claimAddress = _claimAddress;
        threeDayTimelock = _threeDayTimelock;
        twoWeekTimelock = _twoWeekTimelock;
        emit ClaimAddressChanged(_claimAddress);
        emit PalmFinanceTreasuryChanged(_palmFinanceTreasury);

        feeBootstrapPeriodEnabled = true;
        palmFinanceTreasurySplit = 1e18;
        redemptionBorrowerFeeSplit = 2e17;
        maxCollsInAssetPortfolio = 10;
        maxSystemColls = 50;
    }

    /**
     * Ends bootstrap period, which means that
     * all functions can only be updated based on
     * actual timelocks now
     */
    function endBootstrap() external override onlyOwner {
        bootstrapEnded = true;
    }

    /**
     * Function to change fee curve
     */
    function changeFeeCurve(address _collateral, address _feeCurve)
        external
        override
        exists(_collateral)
        onlyOwner
    {
        checkContract(_feeCurve);
        require(IFeeCurve(_feeCurve).initialized(), "fee curve not set");
        (uint256 lastFeePercent, uint256 lastFeeTime) = IFeeCurve(
            collateralParams[_collateral].feeCurve
        ).getFeeCapAndTime();
        IFeeCurve(_feeCurve).setFeeCapAndTime(lastFeePercent, lastFeeTime);
        collateralParams[_collateral].feeCurve = _feeCurve;

        // throw event
        emit FeeCurveChanged(_collateral, _feeCurve);
    }


    /**
     * Can be used to quickly shut down new collateral from entering
     * Palm in the event of a potential hack.
     */
    function deprecateAllCollateral() external override onlyOwner {
        uint256 len = validCollateral.length;
        for (uint256 i; i < len; i++) {
            address collateral = validCollateral[i];
            if (collateralParams[collateral].active) {
                _deprecateCollateral(collateral);
            }
        }
    }

    /**
     * Deprecate collateral by not allowing any more collateral to be added of this type.
     * Still can interact with it via validCollateral and CollateralParams
     */
    function deprecateCollateral(address _collateral) external override exists(_collateral) onlyOwner {
        require(collateralParams[_collateral].active, "collateral already deprecated");
        _deprecateCollateral(_collateral);
    }

    function _deprecateCollateral(address _collateral) internal {
        collateralParams[_collateral].active = false;
        // throw event
        emit CollateralDeprecated(_collateral);
    }

    function setFeeBootstrapPeriodEnabled(bool _enabled) external override onlyOwner {
        feeBootstrapPeriodEnabled = _enabled;
        emit FeeBootstrapPeriodEnabledChanged(_enabled);
    }

    function updateGlobalPUSTMinting(bool _canMint) external override onlyOwner {
        pustToken.updateMinting(_canMint);
        emit GlobalPUSTMintOn(_canMint);
    }

    function removeValidPUSTMinter(address _minter) external override onlyOwner {
        require(_minter != borrowerOperationsAddress);
        pustToken.removeValidMinter(_minter);
        emit PUSTMinterChanged(_minter, false);
    }

    // remove vePALM caller
    function removevePALMCaller(address _contractAddress) external override onlyOwner {
        vePALM.updateWhitelistedCallers(_contractAddress, false);
        emit UpdatevePALMCallers(_contractAddress, false);
    }

    function updateRedemptionsEnabled(bool _enabled) external override onlyOwner {
        assetPortfolioManagerRedemptions.updateRedemptionsEnabled(_enabled);
        emit RedemptionsEnabledUpdated(_enabled);
    }

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
    ) external override {
        _requireThreeDayTimelock();
        checkContract(_collateral);
        checkContract(_oracle);
        checkContract(_feeCurve);
        checkContract(_routerAddress);
        require(IFeeCurve(_feeCurve).initialized(), "fee curve not set"); // 防止设置错了，不能修改
        // If collateral list is not 0, and if the 0th index is not equal to this collateral,
        // then if index is 0 that means it is not set yet.
        _requireSplitOrRatioValid(_safetyRatio < 11e17); //=> greater than 1.1 would mean taking out more PUST than collateral VC
        _requireSplitOrRatioValid(_recoveryRatio >= _safetyRatio);
        require(validCollateral.length < maxSystemColls, "Already hit max system colls");

        if (validCollateral.length != 0) {
            require(
                validCollateral[0] != _collateral && collateralParams[_collateral].index == 0,
                "collateral already exists"
            );
        }

        validCollateral.push(_collateral);
        collateralParams[_collateral] = CollateralParams(
            _safetyRatio,
            _recoveryRatio,
            _oracle,
            _decimals,
            _feeCurve,
            validCollateral.length - 1,
            _routerAddress,
            true,
            _isWrapped
        );

        activePool.addCollateralType(_collateral);
        defaultPool.addCollateralType(_collateral);
        stabilityPool.addCollateralType(_collateral);
        collSurplusPool.addCollateralType(_collateral);

        // throw event
        emit CollateralAdded(_collateral);
        emit SafetyRatioChanged(_collateral, _safetyRatio);
        emit RecoveryRatioChanged(_collateral, _recoveryRatio);
    }

    /**
     * @notice Undeprecate collateral by allowing more collateral to be added of this type.
     * Still can interact with it via validCollateral and CollateralParams
     */
    function unDeprecateCollateral(address _collateral)
        external
        override
        exists(_collateral)
    {
        _requireThreeDayTimelock();
        require(!collateralParams[_collateral].active, "collateral is already active");

        collateralParams[_collateral].active = true;

        // throw event
        emit CollateralUndeprecated(_collateral);
    }

    function setLeverUp(bool _enabled) external override {
        if (_enabled) {
            _requireThreeDayTimelock();
        } else {
            require(msg.sender == owner(), "Caller Not Owner");
        }
        isLeverUpEnabled = _enabled;
        emit LeverUpChanged(_enabled);
    }

    function updateMaxCollsInAssetPortfolio(uint256 _newMax) external override {
        _requireThreeDayTimelock();
        maxCollsInAssetPortfolio = _newMax;
        emit MaxCollsInAssetPortfolioChanged(_newMax);
    }

    /**
     * Function to change oracles
     */
    function changeOracle(address _collateral, address _oracle)
        external
        override
        exists(_collateral)
    {
        _requireThreeDayTimelock();
        checkContract(_oracle);
        collateralParams[_collateral].oracle = _oracle;

        // throw event
        emit OracleChanged(_collateral, _oracle);
    }


    /**
     * Function to change Safety and Recovery Ratio
     */
    function changeRatios(
        address _collateral,
        uint256 _newSafetyRatio,
        uint256 _newRecoveryRatio
    ) external override exists(_collateral) {
        _requireThreeDayTimelock();
        _requireSplitOrRatioValid(_newSafetyRatio < 11e17); //=> greater than 1.1 would mean taking out more PUST than collateral VC
        _requireSplitOrRatioValid(_newRecoveryRatio < 2e18); // => Greater than 2 would be too large
        uint256 oldRecoveryRatio = collateralParams[_collateral].recoveryRatio;
        // Must increase new safety ratio
        _requireSplitOrRatioValid(
            collateralParams[_collateral].safetyRatio <= _newSafetyRatio
        );
        // RR must always be > SR
        _requireSplitOrRatioValid(_newRecoveryRatio >= _newSafetyRatio);

        collateralParams[_collateral].safetyRatio = _newSafetyRatio;
        collateralParams[_collateral].recoveryRatio = _newRecoveryRatio;

        bool _recModeAfter = _checkRecoveryMode();

        if (_recModeAfter) {
            // This transaction must not have lowered the TCR if rec mode after, which could have caused
            // bringing it into recovery mode, or lowering of TCR if it was already in recovery mode.
            _requireSplitOrRatioValid(_newRecoveryRatio >= oldRecoveryRatio);
        }

        // throw events
        emit SafetyRatioChanged(_collateral, _newSafetyRatio);
        emit RecoveryRatioChanged(_collateral, _newRecoveryRatio);
    }

    function setDefaultRouter(address _collateral, address _router)
        external
        override
        exists(_collateral)
    {
        _requireThreeDayTimelock();
        checkContract(_router);
        collateralParams[_collateral].defaultRouter = _router;
        emit DefaultRouterChanged(_collateral, _router);
    }

    function changePalmFinanceTreasury(address _newTreasury) external override {
        _requireThreeDayTimelock();
        _requireAddressNonzero(_newTreasury != address(0));
        palmFinanceTreasury = _newTreasury;
        emit PalmFinanceTreasuryChanged(_newTreasury);
    }

    function changeClaimAddress(address _newClaimAddress) external override {
        _requireThreeDayTimelock();
        _requireAddressNonzero(_newClaimAddress != address(0));
        claimAddress = _newClaimAddress;
        emit ClaimAddressChanged(_newClaimAddress);
    }

    function changePUSTFeeRecipient(address _newFeeRecipient) external override {
        _requireThreeDayTimelock();
        _requireAddressNonzero(_newFeeRecipient != address(0));
        PUSTFeeRecipient = _newFeeRecipient;
        emit PUSTFeeRecipientChanged(_newFeeRecipient);
    }


    function changePalmFinanceTreasurySplit(uint256 _newSplit)
        external
        override
    {
        _requireThreeDayTimelock();
        // 20% goes to the borrower for redemptions, taken out of this portion if it is more than 80%
        _requireSplitOrRatioValid(_newSplit <= DECIMAL_PRECISION);
        palmFinanceTreasurySplit = _newSplit;
        emit PalmFinanceTreasurySplitChanged(_newSplit);
    }

    function changeRedemptionBorrowerFeeSplit(uint256 _newSplit)
        external
        override
    {
        _requireThreeDayTimelock();
        _requireSplitOrRatioValid(_newSplit <= DECIMAL_PRECISION);
        redemptionBorrowerFeeSplit = _newSplit;
        emit RedemptionBorrowerFeeSplitChanged(_newSplit);
    }

    /**
     * @notice Change boost minute decay factor which is calculated as a half life of a particular fraction for SortedAssetPortfolios
     * @dev Half-life of 5d = 120h. 120h = 7200 min
     * (1/2) = d^7200 => d = (1/2)^(1/7200) = 999903734192105837 by default
     * Two week timelocked.
     * @param _newBoostMinuteDecayFactor the new boost decay factor
     */
    function changeBoostMinuteDecayFactor(uint256 _newBoostMinuteDecayFactor)
        external
        override
    {
        _requireThreeDayTimelock();
        sortedAssetPortfolios.changeBoostMinuteDecayFactor(_newBoostMinuteDecayFactor);
        emit BoostMinuteDecayFactorChanged(_newBoostMinuteDecayFactor);
    }

    /**
     * @notice Change Boost factor multiplied by new input for SortedAssetPortfolios
     * @dev If fee is 5% of total, then the boost factor will be 5e16 * boost / 1e18 added to AICR for sorted assetPortfolios reinsert
     * Default is 0 for boost multiplier at contract deployment. 1e18 would mean 100% of the fee % is added to AICR as a %.
     * @param _newGlobalBoostMultiplier new boost multiplier
     */
    function changeGlobalBoostMultiplier(uint256 _newGlobalBoostMultiplier)
        external
        override
    {
        _requireThreeDayTimelock();
        sortedAssetPortfolios.changeGlobalBoostMultiplier(_newGlobalBoostMultiplier);
        emit GlobalBoostMultiplierChanged(_newGlobalBoostMultiplier);
    }

    function updateAbsorptionColls(
        address[] memory _colls,
        uint[] memory _weights) external override {
        _requireThreeDayTimelock();
        uint256 weightsLen = _weights.length;
        _requireInputLengthChange(_colls.length == weightsLen);
        // Absorption colls must not be empty
        _requireInputLengthChange(weightsLen != 0);
        uint sum;
        for (uint i; i < weightsLen; ++i) {
            sum = sum.add(_weights[i]);
            _requireCollateralActive(_colls[i]);
        }
        require(sum == DECIMAL_PRECISION, "absorptionWeights doesn't add to 1");

        absorptionColls = _colls;
        absorptionWeights = _weights;
    }

    // ======== Mutable Only Owner-2 Weeks TimeLock ========

    function addValidPUSTMinter(address _minter) external override {
        _requireTwoWeekTimelock();
        _requireAddressNonzero(_minter != address(0));
        pustToken.addValidMinter(_minter);
        emit PUSTMinterChanged(_minter, true);
    }

    function addvePALMCaller(address _contractAddress) external override {
        _requireTwoWeekTimelock();
        vePALM.updateWhitelistedCallers(_contractAddress, true);
        emit UpdatevePALMCallers(_contractAddress, true);
    }

    // update max system collaterals
    function updateMaxSystemColls(uint _newMax) external override {
        _requireTwoWeekTimelock();
        require(_newMax > validCollateral.length,
            "invalid newMax");
        maxSystemColls = _newMax;
        emit MaxSystemCollsChanged(_newMax);
    }


    // ======= VIEW FUNCTIONS FOR COLLATERAL =======

    function getDefaultRouterAddress(address _collateral)
        external
        view
        override
        exists(_collateral)
        returns (address)
    {
        return collateralParams[_collateral].defaultRouter;
    }

    function isWrapped(address _collateral) external view override returns (bool) {
        return collateralParams[_collateral].isWrapped;
    }

    function isWrappedMany(address[] memory _collaterals) external view override returns (bool[] memory wrapped) {
        wrapped = new bool[](_collaterals.length);
        for (uint i = 0; i < _collaterals.length; i++) {
            wrapped[i] = collateralParams[_collaterals[i]].isWrapped;
        }
    }

    function getValidCollateral() external view override returns (address[] memory) {
        return validCollateral;
    }

    // Get safety ratio used in VC Calculation
    function getSafetyRatio(address _collateral) external view override returns (uint256) {
        return collateralParams[_collateral].safetyRatio;
    }

    // Get adjusted safety ratio used in TCR calculation, as well as for redemptions.
    // Often similar to Safety Ratio except for stables.
    function getRecoveryRatio(address _collateral)
        external
        view
        override
        exists(_collateral)
        returns (uint256)
    {
        return collateralParams[_collateral].recoveryRatio;
    }

    function getOracle(address _collateral)
        external
        view
        override
        exists(_collateral)
        returns (address)
    {
        return collateralParams[_collateral].oracle;
    }

    function getFeeCurve(address _collateral)
        external
        view
        override
        exists(_collateral)
        returns (address)
    {
        return collateralParams[_collateral].feeCurve;
    }

    function getIsActive(address _collateral)
        external
        view
        override
        exists(_collateral)
        returns (bool)
    {
        return collateralParams[_collateral].active;
    }

    function getDecimals(address _collateral)
        external
        view
        override
        exists(_collateral)
        returns (uint256)
    {
        return collateralParams[_collateral].decimals;
    }

    function getIndex(address _collateral)
        external
        view
        override
        exists(_collateral)
        returns (uint256)
    {
        return (collateralParams[_collateral].index);
    }

    function getIndices(address[] memory _colls)
        external
        view
        override
        returns (uint256[] memory indices)
    {
        uint256 len = _colls.length;
        indices = new uint256[](len);

        for (uint256 i; i < len; ++i) {
            _exists(_colls[i]);
            indices[i] = collateralParams[_colls[i]].index;
        }
    }

    /**
     * @notice This function is used to check the deposit and withdraw coll lists of the adjust assetPortfolio transaction.
     * @dev The coll list must be not overlapping, and strictly increasing. Strictly increasing implies not overlapping,
     * so we just check that. If it is a deposit, the coll also has to be active. The collateral also has to exist. Special
     * case is done for the first collateral, where we don't check the index. Reverts if any case is not met.
     * @param _colls Collateral to check
     * @param _deposit True if deposit, false if withdraw.
     */
    function checkCollateralListSingle(address[] memory _colls, bool _deposit)
        external
        view
        override
    {
        _checkCollateralListSingle(_colls, _deposit);
    }

    function _checkCollateralListSingle(address[] memory _colls, bool _deposit) internal view {
        uint256 len = _colls.length;
        if (len == 0) {
            return;
        }
        uint256 prevIndex;
        for (uint256 i; i < len; ++i) {
            address thisColl = _colls[i];
            _exists(thisColl);
            _requireCollListSortedByIndex(
                collateralParams[thisColl].index > prevIndex || i == 0
            );
            prevIndex = collateralParams[thisColl].index;
            if (_deposit) {
                _requireCollateralActive(thisColl);
            }
        }
    }

    /**
     * @notice This function is used to check the deposit and withdraw coll lists of the adjust assetPortfolio transaction.
     * @dev The colls must be not overlapping, and each strictly increasing. While looping through both, we check that
     * the indices are not shared, and we then increment by each list whichever is smaller at that time, whie simultaneously
     * checking if the indices of that list are strictly increasing. It also ensures that collaterals exist in the system,
     * and deposited collateral is active. Reverts if any case is not met.
     * @param _depositColls Collateral to check for deposits
     * @param _withdrawColls Collateral to check for withdrawals
     */
    function checkCollateralListDouble(
        address[] memory _depositColls,
        address[] memory _withdrawColls
    ) external view override {
        uint256 _depositLen = _depositColls.length;
        uint256 _withdrawLen = _withdrawColls.length;
        if (_depositLen == 0) {
            if (_withdrawLen == 0) {
                // Both empty, nothing to check
                return;
            } else {
                // Just withdraw check
                _checkCollateralListSingle(_withdrawColls, false);
                return;
            }
        }
        if (_withdrawLen == 0) {
            // Just deposit check
            _checkCollateralListSingle(_depositColls, true);
            return;
        }
        address dColl = _depositColls[0];
        address wColl = _withdrawColls[0];
        uint256 dIndex = collateralParams[dColl].index;
        uint256 wIndex = collateralParams[wColl].index;
        uint256 d_i;
        uint256 w_i;
        while (true) {
            require(dIndex != wIndex, "No overlap in withdraw and deposit");
            if (dIndex < wIndex) {
                // update d coll
                if (d_i == _depositLen) {
                    break;
                }
                dColl = _depositColls[d_i];
                _exists(dColl);
                _requireCollateralActive(dColl);
                uint256 dIndexNew = collateralParams[dColl].index;
                _requireCollListSortedByIndex(dIndexNew > dIndex || d_i == 0);
                dIndex = dIndexNew;
                ++d_i;
            } else {
                // update w coll
                if (w_i == _withdrawLen) {
                    break;
                }
                wColl = _withdrawColls[w_i];
                _exists(wColl);
                uint256 wIndexNew = collateralParams[wColl].index;
                _requireCollListSortedByIndex(wIndexNew > wIndex || w_i == 0);
                wIndex = wIndexNew;
                ++w_i;
            }
        }
        // No further check of dIndex == wIndex is needed, because to close out of the loop above, we have
        // to have advanced d_i or w_i whichever reached the end. Say d_i reached the end, which means that
        // dIndex was less than wIndex. dIndex has already been updated for the last time, and wIndex is now
        // required to be larger than dIndex. So, no wIndex, unless if it wasn't strictly increasing, can be
        // equal to dIndex. Therefore we only need to check for wIndex to be strictly increasing. Same argument
        // for the vice versa case.
        while (d_i < _depositLen) {
            dColl = _depositColls[d_i];
            _exists(dColl);
            _requireCollateralActive(dColl);
            uint256 dIndexNew = collateralParams[dColl].index;
            _requireCollListSortedByIndex(dIndexNew > dIndex || d_i == 0);
            dIndex = dIndexNew;
            ++d_i;
        }
        while (w_i < _withdrawLen) {
            wColl = _withdrawColls[w_i];
            _exists(wColl);
            uint256 wIndexNew = collateralParams[wColl].index;
            _requireCollListSortedByIndex(wIndexNew > wIndex || w_i == 0);
            wIndex = wIndexNew;
            ++w_i;
        }
    }

    // ======= VIEW FUNCTIONS FOR VC / USD VALUE =======

    // Returns 10**18 times the price in USD of 1 of the given _collateral
    function getPrice(address _collateral) public view override returns (uint256) {
        IPriceFeed collateral_priceFeed = IPriceFeed(collateralParams[_collateral].oracle);
        return collateral_priceFeed.fetchPrice_v();
    }

    // Returns the value of that collateral type, of that amount in dollars
    function getValueUSD(address _collateral, uint256 _amount)
        external
        view
        override
        returns (uint256)
    {
        return _getValueUSD(_collateral, _amount);
    }

    // Aggregates all usd values of passed in collateral / amounts
    function getValuesUSD(address[] memory _collaterals, uint256[] memory _amounts)
        external
        view
        override
        returns (uint256 USDValue)
    {
        uint256 tokensLen = _collaterals.length;
        for (uint256 i; i < tokensLen; ++i) {
            USDValue = USDValue.add(_getValueUSD(_collaterals[i], _amounts[i]));
        }
    }

    // Gets the value of that collateral type, of that amount, in VC terms.
    function getValueVC(address _collateral, uint256 _amount)
        external
        view
        override
        returns (uint256)
    {
        return _getValueVC(_collateral, _amount);
    }

    function getValuesVC(address[] memory _collaterals, uint256[] memory _amounts)
        external
        view
        override
        returns (uint256 VCValue)
    {
        uint256 tokensLen = _collaterals.length;
        for (uint256 i; i < tokensLen; ++i) {
            VCValue = VCValue.add(_getValueVC(_collaterals[i], _amounts[i]));
        }
    }

    /**
     * @notice External Function to get the VC balance and return them as an array of values instead
     * of summing them like in getValuesVC.
     */
    function getValuesVCIndividual(address[] memory _collaterals, uint256[] memory _amounts)
        external
        view
        override
        returns (uint256[] memory)
    {
        return _getValuesVCIndividual(_collaterals, _amounts);
    }

    /**
     * @notice Function to get the VC balance and return them as an array of values instead
     * of summing them like in getValuesVC.
     */
    function _getValuesVCIndividual(address[] memory _collaterals, uint256[] memory _amounts)
        internal
        view
        returns (uint256[] memory VCValues)
    {
        uint256 tokensLen = _collaterals.length;
        VCValues = new uint256[](tokensLen);
        for (uint256 i; i < tokensLen; ++i) {
            VCValues[i] = _getValueVC(_collaterals[i], _amounts[i]);
        }
    }

    // Gets the value of that collateral type, of that amount, in Recovery VC terms.
    function getValueRVC(address _collateral, uint256 _amount)
        external
        view
        override
        returns (uint256)
    {
        return _getValueRVC(_collateral, _amount);
    }

    function getValuesRVC(address[] memory _collaterals, uint256[] memory _amounts)
        external
        view
        override
        returns (uint256 RVCValue)
    {
        uint256 tokensLen = _collaterals.length;
        for (uint256 i; i < tokensLen; ++i) {
            RVCValue = RVCValue.add(_getValueRVC(_collaterals[i], _amounts[i]));
        }
    }

    function _getValueRVC(address _collateral, uint256 _amount) internal view returns (uint256) {
        // Multiply price by amount and recovery ratio to get in Recovery VC terms, as well as dividing by amount of decimals to normalize.
        return (
            (getPrice(_collateral))
                .mul(_amount)
                .mul(collateralParams[_collateral].recoveryRatio)
                .div(10**(18 + collateralParams[_collateral].decimals))
        );
    }

    function getValuesVCAndRVC(address[] memory _collaterals, uint256[] memory _amounts)
        external
        view
        override
        returns (uint256 VCValue, uint256 RVCValue)
    {
        uint256 tokensLen = _collaterals.length;
        for (uint256 i; i < tokensLen; ++i) {
            (uint256 tempVCValue, uint256 tempRVCValue) = _getValueVCAndRVC(
                _collaterals[i],
                _amounts[i]
            );
            VCValue = VCValue.add(tempVCValue);
            RVCValue = RVCValue.add(tempRVCValue);
        }
    }

    // ===== VIEW FUNCTIONS FOR CONTRACT FUNCTIONALITY ======

    function getPalmFinanceTreasury() external view override returns (address) {
        return palmFinanceTreasury;
    }

    function getPalmFinanceTreasurySplit() external view override returns (uint256) {
        return palmFinanceTreasurySplit;
    }

    function getRedemptionBorrowerFeeSplit() external view override returns (uint256) {
        return redemptionBorrowerFeeSplit;
    }

    function getPUSTFeeRecipient() external view override returns (address) {
        return PUSTFeeRecipient;
    }

    function leverUpEnabled() external view override returns (bool) {
        return isLeverUpEnabled;
    }

    function getMaxCollsInAssetPortfolio() external view override returns (uint256) {
        return maxCollsInAssetPortfolio;
    }

    /**
     * Returns the treasury split, treasury address, and the fee recipient. This is for use of borrower
     * operations when fees are sent, as well as redemption fees.
     */
    function getFeeSplitInformation()
        external
        view
        override
        returns (
            uint256,
            address,
            address
        )
    {
        return (palmFinanceTreasurySplit, palmFinanceTreasury, PUSTFeeRecipient);
    }

    /**
     * Returns the address that will receive collateral in the case of a redistribution
     * where there is no other assetPortfolios with the collateral
     */
    function getClaimAddress() external override view returns (address) {
        return claimAddress;
    }

    /**
     * Returns the parameters related to the collaterals that are used to absorb
     * a redistribution where there are no other assetPortfolios with the collateral
     */
    function getAbsorptionCollParams() external override view returns (address[] memory, uint[] memory) {
        return (absorptionColls, absorptionWeights);
    }


    // Returned as fee percentage * 10**18. View function for external callers.
    function getVariableDepositFee(
        address _collateral,
        uint256 _collateralVCInput,
        uint256 _collateralVCSystemBalance,
        uint256 _totalVCBalancePre,
        uint256 _totalVCBalancePost
    ) external view override exists(_collateral) returns (uint256) {
        IFeeCurve feeCurve = IFeeCurve(collateralParams[_collateral].feeCurve);
        uint256 uncappedFee = feeCurve.getFee(
                _collateralVCInput,
                _collateralVCSystemBalance,
                _totalVCBalancePre,
                _totalVCBalancePost
            );
        if (feeBootstrapPeriodEnabled) {
            return PalmMath._min(uncappedFee, 1e16); // cap at 1%
        } else {
            return uncappedFee;
        }
    }

    // ====== MUTABLE FUNCTIONS FOR FEES ======

    /**
     * @notice Gets total variable fees from all collaterals with entire system collateral,
     * calculates using pre and post balances. For each token, get the active pool and
     * default pool balance of that collateral, and call the correct fee curve function
     * If the fee bootstrap period is on then cap it at a certain percent, otherwise
     * continue looping through all collaterals.
     * To calculate the boost factor, we multiply the fee * leverage amount. Leverage
     * passed in as 0 is actually 1x.
     * @param _tokensIn the tokens to get the variable fees for
     * @param _leverages the leverage of that collateral. Used for calculating boost on collateral
     *   one time deposit fees. Passed in as 0 if not a token that is leveraged.
     * @param _entireSystemCollVC the entire system collateral VC value calculated previously in
     *   recovery mode check calculations
     * @param _VCin the sum of all collaterals added in from adjustAssetPortfolio or openAssetPortfolio
     * @param _VCout the sum of all collaterals withdrawn from adjustAssetPortfolio
     * @return PUSTFee the total variable fees for all tokens in this transaction
     * @return boostFactor the boost factor for all tokens in this transaction based on the leverage and
     *    fee applied.
     */
    function getTotalVariableDepositFeeAndUpdate(
        address[] memory _tokensIn,
        uint256[] memory _amountsIn,
        uint256[] memory _leverages,
        uint256 _entireSystemCollVC,
        uint256 _VCin,
        uint256 _VCout
    ) external override returns (uint256 PUSTFee, uint256 boostFactor) {
        require(msg.sender == borrowerOperationsAddress, "caller must be BO");
        if (_VCin == 0) {
            return (0, 0);
        }
        DepositFeeCalc memory vars;
        // active pool total VC at current state is passed in as _entireSystemCollVC
        // active pool total VC post adding and removing all collaterals
        vars.activePoolVCPost = _entireSystemCollVC.add(_VCin).sub(_VCout);
        uint256 tokensLen = _tokensIn.length;
        // VC value of collateral of this type inputted, from AP and DP balances.
        vars.systemCollateralVCs = _getValuesVCIndividual(_tokensIn, activePool.getAmountsSubsetSystem(_tokensIn));
        for (uint256 i; i < tokensLen; ++i) {
            vars.token = _tokensIn[i];
            // VC value of collateral of this type inputted
            vars.collateralInputVC = _getValueVC(vars.token, _amountsIn[i]);

            // (collateral VC In) * (Collateral's Fee Given Palm Protocol Backed by Given Collateral)
            uint256 controllerFee = _getFeeAndUpdate(
                vars.token,
                vars.collateralInputVC,
                vars.systemCollateralVCs[i],
                _entireSystemCollVC,
                vars.activePoolVCPost
            );
            if (feeBootstrapPeriodEnabled) {
                controllerFee = PalmMath._min(controllerFee, 1e16); // cap at 1%
            }
            vars.collateralPUSTFee = vars.collateralInputVC.mul(controllerFee).div(DECIMAL_PRECISION);

            // If lower than 1, then it was not leveraged (1x)
            uint256 thisLeverage = PalmMath._max(DECIMAL_PRECISION, _leverages[i]);

            uint256 collBoostFactor = vars.collateralPUSTFee.mul(thisLeverage).div(_VCin);
            boostFactor = boostFactor.add(collBoostFactor);

            PUSTFee = PUSTFee.add(vars.collateralPUSTFee);
        }
    }

    // Returned as fee percentage * 10**18. View function for call to fee originating from BOps callers.
    function _getFeeAndUpdate(
        address _collateral,
        uint256 _collateralVCInput,
        uint256 _collateralVCSystemBalance,
        uint256 _totalVCBalancePre,
        uint256 _totalVCBalancePost
    ) internal exists(_collateral) returns (uint256 fee) {
        IFeeCurve feeCurve = IFeeCurve(collateralParams[_collateral].feeCurve);
        return
            feeCurve.getFeeAndUpdate(
                _collateralVCInput,
                _collateralVCSystemBalance,
                _totalVCBalancePre,
                _totalVCBalancePost
            );
    }

    // ======== INTERNAL VIEW FUNCTIONS ========

    function _getValueVCAndRVC(address _collateral, uint256 _amount)
        internal
        view
        returns (uint256 VC, uint256 RVC)
    {
        uint256 price = getPrice(_collateral);
        uint256 decimals = collateralParams[_collateral].decimals;
        uint256 safetyRatio = collateralParams[_collateral].safetyRatio;
        uint256 recoveryRatio = collateralParams[_collateral].recoveryRatio;
        VC = price.mul(_amount).mul(safetyRatio).div(10**(18 + decimals));
        RVC = price.mul(_amount).mul(recoveryRatio).div(10**(18 + decimals));
    }

    function _getValueUSD(address _collateral, uint256 _amount) internal view returns (uint256) {
        uint256 decimals = collateralParams[_collateral].decimals;
        uint256 price = getPrice(_collateral);
        return price.mul(_amount).div(10**decimals);
    }

    function _getValueVC(address _collateral, uint256 _amount) internal view returns (uint256) {
        // Multiply price by amount and safety ratio to get in VC terms, as well as dividing by amount of decimals to normalize.
        return (
            (getPrice(_collateral)).mul(_amount).mul(collateralParams[_collateral].safetyRatio).div(
                10**(18 + collateralParams[_collateral].decimals)
            )
        );
    }

    function _requireCollateralActive(address _collateral) internal view {
        require(collateralParams[_collateral].active, "Collateral not active");
    }

    function _requireCollListSortedByIndex(bool _sorted) internal pure returns (uint256) {
        require(_sorted, "Collateral list not sorted");
    }

    function _requireAddressNonzero(bool _nonzero) internal pure returns (address) {
        require(_nonzero, "Address nonzero");
    }

    function _requireSplitOrRatioValid(bool _valid) internal pure returns (uint256) {
        require(_valid, "invalid new split/ratio");
    }

    function _requireInputLengthChange(bool _validLength) internal pure returns (uint256) {
        require(_validLength, "invalid input length");
    }
}