// SPDX-License-Identifier: UNLICENSED

pragma solidity 0.6.11;

import "./Interfaces/IBorrowerOperations.sol";
import "./Interfaces/IAssetPortfolioManager.sol";
import "./Interfaces/IPUSTToken.sol";
import "./Interfaces/ICollSurplusPool.sol";
import "./Interfaces/ISortedAssetPortfolios.sol";
import "./Interfaces/IPalmController.sol";
import "./Interfaces/IPalmLever.sol";
import "./Interfaces/IERC20.sol";
import "./Interfaces/IPalmVaultToken.sol";
import "./Dependencies/LiquityBase.sol";
import "./Dependencies/SafeMath.sol";
import "./Dependencies/ReentrancyGuardUpgradeable.sol";
import "./Dependencies/SafeERC20.sol";



/**
 * @title Handles most of external facing assetPortfolio activities that a user would make with their own assetPortfolio
 * @notice AssetPortfolio activities like opening, closing, adjusting, increasing leverage, etc
 *
 *
 * A summary of Lever Up:
 * Takes in a collateral token A, and simulates borrowing of PUST at a certain collateral ratio and
 * buying more token A, putting back into protocol, buying more A, etc. at a certain leverage amount.
 * So if at 3x leverage and 1000$ token A, it will mint 1000 * 3x * 2/3 = $2000 PUST, then swap for
 * token A by using some router strategy, returning a little under $2000 token A to put back in the
 * assetPortfolio. The number here is 2/3 because the math works out to be that collateral ratio is 150% if
 * we have a 3x leverage. They now have a assetPortfolio with $3000 of token A and a collateral ratio of 150%.
 * Using leverage will not return PUST debt for the borrower.
 *
 * Unlever is the opposite of this, and will take collateral in a borrower's assetPortfolio, sell it on the market
 * for PUST, and attempt to pay back a certain amount of PUST debt in a user's assetPortfolio with that amount.
 *
 */

contract BorrowerOperations is LiquityBase, IBorrowerOperations, ReentrancyGuardUpgradeable {
    using SafeMath for uint256;
    using SafeERC20 for IERC20;
    bytes32 public constant NAME = "BorrowerOperations";

    // --- Connected contract declarations ---

    IAssetPortfolioManager internal assetPortfolioManager;

    address internal gasPoolAddress;

    ICollSurplusPool internal collSurplusPool;

    IPUSTToken internal pustToken;

    ISortedAssetPortfolios internal sortedAssetPortfolios;

    address internal activePoolAddress;

    /* --- Variable container structs  ---

    Used to hold, return and assign variables inside a function, in order to avoid the error:
    "CompilerError: Stack too deep". */

    struct AdjustAssetPortfolio_Params {
        uint256[] _leverages;
        address[] _collsIn;
        uint256[] _amountsIn;
        address[] _collsOut;
        uint256[] _amountsOut;
        uint256[] _maxSlippages;
        uint256 _PUSTChange;
        uint256 _totalPUSTDebtFromLever;
        address _upperHint;
        address _lowerHint;
        uint256 _maxFeePercentage;
        bool _isDebtIncrease;
        bool _isUnlever;
    }

    struct LocalVariables_adjustAssetPortfolio {
        uint256 netDebtChange;
        uint256 collChangeRVC;
        uint256 currVC;
        uint256 currRVC;
        uint256 newVC;
        uint256 newRVC;
        uint256 debt;
        address[] currAssets;
        uint256[] currAmounts;
        address[] newAssets;
        uint256[] newAmounts;
        uint256 oldICR;
        uint256 newICR;
        uint256 PUSTFee;
        uint256 variablePUSTFee;
        uint256 newDebt;
        uint256 VCin;
        uint256 RVCin;
        uint256 VCout;
        uint256 RVCout;
        uint256 maxFeePercentageFactor;
        uint256 entireSystemCollVC;
        uint256 entireSystemCollRVC;
        uint256 entireSystemDebt;
        uint256 boostFactor;
        bool isRVCIncrease;
        bool isRecoveryMode;
    }

    struct OpenAssetPortfolio_Params {
        uint256[] _leverages;
        uint256 _maxFeePercentage;
        uint256 _PUSTAmount;
        uint256 _totalPUSTDebtFromLever;
        address _upperHint;
        address _lowerHint;
    }

    struct LocalVariables_openAssetPortfolio {
        uint256 PUSTFee;
        uint256 netDebt;
        uint256 compositeDebt;
        uint256 ICR;
        uint256 VC;
        uint256 RVC;
        uint256 entireSystemCollVC;
        uint256 entireSystemCollRVC;
        uint256 entireSystemDebt;
        uint256 boostFactor;
        bool isRecoveryMode;
    }

    struct LocalVariables_closeAssetPortfolio {
        uint256 entireSystemCollRVC;
        uint256 entireSystemDebt;
        uint256 debt;
        address[] colls;
        uint256[] amounts;
        uint256 assetPortfolioRVC;
        bool isRecoveryMode;
    }

    struct ContractsCache {
        IAssetPortfolioManager assetPortfolioManager;
        IActivePool activePool;
        IPUSTToken pustToken;
        IPalmController controller;
    }

    enum BorrowerOperation {
        openAssetPortfolio,
        closeAssetPortfolio,
        adjustAssetPortfolio
    }

    event AssetPortfolioCreated(address indexed _borrower, uint256 arrayIndex);

    event AssetPortfolioUpdated(
        address indexed _borrower,
        uint256 _debt,
        address[] _tokens,
        uint256[] _amounts,
        BorrowerOperation operation
    );
    event PUSTBorrowingFeePaid(address indexed _borrower, uint256 _PUSTFee);

    event VariableFeePaid(address indexed _borrower, uint256 _PUSTVariableFee);

    // --- Dependency setters ---
    bool private addressSet;
    /**
     * @notice Sets the addresses of all contracts used. Can only be called once. 
     */
    function setAddresses(
        address _assetPortfolioManagerAddress,
        address _activePoolAddress,
        address _defaultPoolAddress,
        address _gasPoolAddress,
        address _collSurplusPoolAddress,
        address _sortedAssetPortfoliosAddress,
        address _pustTokenAddress,
        address _controllerAddress
    ) external override {
        require(addressSet == false, "Addresses already set");
        addressSet = true;
        __ReentrancyGuard_init();
        
        assetPortfolioManager = IAssetPortfolioManager(_assetPortfolioManagerAddress);
        activePool = IActivePool(_activePoolAddress);
        activePoolAddress = _activePoolAddress;
        defaultPool = IDefaultPool(_defaultPoolAddress);
        controller = IPalmController(_controllerAddress);
        gasPoolAddress = _gasPoolAddress;
        collSurplusPool = ICollSurplusPool(_collSurplusPoolAddress);
        sortedAssetPortfolios = ISortedAssetPortfolios(_sortedAssetPortfoliosAddress);
        pustToken = IPUSTToken(_pustTokenAddress);
    }

    // --- Borrower AssetPortfolio Operations ---

    /**
     * @notice Main function to open a new assetPortfolio. Takes in collateral and adds it to a assetPortfolio, resulting in
     *  a collateralized debt position. The resulting ICR (individual collateral ratio) of the assetPortfolio is indicative
     *  of the safety of the assetPortfolio.
     * @param _maxFeePercentage The maximum percentage of the Collateral VC in that can be taken as fee.
     * @param _PUSTAmount Amount of PUST to open the assetPortfolio with. The resulting PUST Amount + 200 PUST Gas compensation
     *  plus any PUST fees that occur must be > 2000. This min debt amount is intended to reduce the amount of small assetPortfolios
     *  that are opened, since liquidating small assetPortfolios may clog the network and we want to prioritize liquidations of larger
     *  assetPortfolios in turbulant gas conditions.
     * @param _upperHint The address of the assetPortfolio above this one in the sorted assetPortfolios list.
     * @param _lowerHint The address of the assetPortfolio below this one in the sorted assetPortfolios list.
     * @param _colls The addresses of collaterals to be used in the assetPortfolio. Must be passed in, in order of the whitelisted collateral.
     * @param _amounts The amounts of each collateral to be used in the assetPortfolio. If passing in a vault token, the amount must be the
     *  amount of the underlying asset, but the address passed in must be the vault token address. So, for example, if trying to
     *  open a assetPortfolio with Benqi USDC (qiUSDC), then the address passed in must be Palm Vault qiUSDC, but the amount must be of
     *  qiUSDC in your wallet. The resulting amount in your assetPortfolio will be of the vault token, so to see how much actual qiUSDC you have
     *  you must use the conversion ratio on the vault contract. 
     */
    function openAssetPortfolio(
        uint256 _maxFeePercentage,
        uint256 _PUSTAmount,
        address _upperHint,
        address _lowerHint,
        address[] calldata _colls,
        uint256[] memory _amounts
    ) external override nonReentrant {
        ContractsCache memory contractsCache = ContractsCache(
            assetPortfolioManager,
            activePool,
            pustToken,
            controller
        );
        _requireInputCorrect(_amounts.length != 0);

        // check that all _colls collateral types are in the controller and in correct order.
        _requireValidCollateral(_colls, _amounts, contractsCache.controller, true);

        // Check that below max colls in assetPortfolio.
        _requireValidAssetPortfolioCollsLen(contractsCache.controller, _colls.length);

        // transfer collateral into ActivePool
        _transferCollateralsIntoActivePool(_colls, _amounts);

        OpenAssetPortfolio_Params memory params = OpenAssetPortfolio_Params(
            new uint256[](_colls.length),
            _maxFeePercentage,
            _PUSTAmount,
            0,
            _upperHint,
            _lowerHint
        );
        _openAssetPortfolioInternal(params, _colls, _amounts, contractsCache);
    }

    /**
     * @notice Opens a assetPortfolio while leveraging up on the collateral passed in.
     * @dev Takes in a leverage amount (11x) and a token, and calculates the amount
     * of that token that would be at the specific collateralization ratio. Mints PUST
     * according to the price of the token and the amount. Calls internal leverUp
     * function to perform the swap through a route.
     * Then opens a assetPortfolio with the new collateral from the swap, ensuring that
     * the amount is enough to cover the debt. Reverts if the swap was
     * not able to get the correct amount of collateral according to slippage passed in.
     * _leverage is like 11e18 for 11x.
     * @param _maxFeePercentage The maximum percentage of the Collateral VC in that can be taken as fee.
     * @param _PUSTAmount Amount of PUST to open the assetPortfolio with. This is separate from the amount of PUST taken against the leveraged amounts
     *  for each collateral which is levered up on. The resulting PUST Amount + 200 PUST Gas compensation plus any PUST
     *  fees plus amount from leverages must be > 2000. This min debt amount is intended to reduce the amount of small assetPortfolios
     *  that are opened, since liquidating small assetPortfolios may clog the network and we want to prioritize liquidations of larger
     *  assetPortfolios in turbulant gas conditions.
     * @param _upperHint The address of the assetPortfolio above this one in the sorted assetPortfolios list.
     * @param _lowerHint The address of the assetPortfolio below this one in the sorted assetPortfolios list.
     * @param _colls The addresses of collaterals to be used in the assetPortfolio. Must be passed in, in order of the whitelisted collateral.
     * @param _amounts The amounts of each collateral to be used in the assetPortfolio. If passing in a vault token, the amount must be the
     *  amount of the underlying asset, but the address passed in must be the vault token address. So, for example, if trying to
     *  open a assetPortfolio with Benqi USDC (qiUSDC), then the address passed in must be Palm Vault qiUSDC, but the amount must be of
     *  qiUSDC in your wallet. The resulting amount in your assetPortfolio will be of the vault token, so to see how much actual qiUSDC you have
     *  you must use the conversion ratio on the vault contract. 
     * @param _leverages The leverage amounts on each collateral to be used in the lever up function. If 0 there is no leverage on that coll
     * @param _maxSlippages The max slippage amount when swapping PUST for collateral
     */
    function openAssetPortfolioLeverUp(
        uint256 _maxFeePercentage,
        uint256 _PUSTAmount,
        address _upperHint,
        address _lowerHint,
        address[] memory _colls,
        uint256[] memory _amounts,
        uint256[] memory _leverages,
        uint256[] calldata _maxSlippages
    ) external override nonReentrant {
        ContractsCache memory contractsCache = ContractsCache(
            assetPortfolioManager,
            activePool,
            pustToken,
            controller
        );
        _requireLeverUpEnabled(contractsCache.controller);
        uint256 collsLen = _colls.length;
        _requireInputCorrect(collsLen != 0);
        // check that all _colls collateral types are in the controller and in correct order.
        _requireValidCollateral(_colls, _amounts, contractsCache.controller, true);
        // Check that below max colls in assetPortfolio.
        _requireValidAssetPortfolioCollsLen(contractsCache.controller, _colls.length);
        // Must check additional passed in arrays
        _requireInputCorrect(collsLen == _leverages.length && collsLen == _maxSlippages.length);
        // Keep track of total PUST from lever and pass into internal open assetPortfolio.
        uint256 totalPUSTDebtFromLever;
        for (uint256 i; i < collsLen; ++i) {
            if (_maxSlippages[i] != 0) {
                (uint256 additionalTokenAmount, uint256 additionalPUSTDebt) = _singleLeverUp(
                    _colls[i],
                    _amounts[i],
                    _leverages[i],
                    _maxSlippages[i],
                    contractsCache
                );
                // Transfer into active pool, non levered amount, and add to additional token amount returned. 
                // additional token amount was set to the original amount * leverage.
                // The amount of receipt tokens received back is the amount which we will use to open the assetPortfolio.
                _amounts[i] = additionalTokenAmount.add(_singleTransferCollateralIntoActivePool(_colls[i], _amounts[i]));
                totalPUSTDebtFromLever = totalPUSTDebtFromLever.add(additionalPUSTDebt);
            } else {
                // Otherwise skip and do normal transfer that amount into active pool.
                require(_leverages[i] == 0, "2");
                _amounts[i] = _singleTransferCollateralIntoActivePool(_colls[i], _amounts[i]);
            }
        }
        _PUSTAmount = _PUSTAmount.add(totalPUSTDebtFromLever);

        OpenAssetPortfolio_Params memory params = OpenAssetPortfolio_Params(
            _leverages,
            _maxFeePercentage,
            _PUSTAmount,
            totalPUSTDebtFromLever,
            _upperHint,
            _lowerHint
        );
        _openAssetPortfolioInternal(params, _colls, _amounts, contractsCache);
    }

    /**
     * @notice internal function for minting pust at certain leverage and max slippage, and then performing
     * swap with controller's approved router.
     * @param _token collateral address
     * @param _amount amount of collateral to lever up on
     * @param _leverage amount to leverage. 11e18 = 11x
     * @param _maxSlippage max slippage amount for swap PUST to collateral
     * @return _finalTokenAmount final amount of the collateral token
     * @return _additionalPUSTDebt Total amount of PUST Minted to be added to total.
     */
    function _singleLeverUp(
        address _token,
        uint256 _amount,
        uint256 _leverage,
        uint256 _maxSlippage,
        ContractsCache memory contractsCache
    ) internal returns (uint256 _finalTokenAmount, uint256 _additionalPUSTDebt) {
        require(_leverage > DECIMAL_PRECISION && _maxSlippage <= DECIMAL_PRECISION, "2");
        address router = _getDefaultRouterAddress(contractsCache.controller, _token);
        // leverage is 5e18 for 5x leverage. Minus 1 for what the user already has in collateral value.
        uint256 _additionalTokenAmount = _amount.mul(_leverage.sub(DECIMAL_PRECISION)).div(
            DECIMAL_PRECISION
        );
        // Calculate USD value to see how much PUST to mint.
        _additionalPUSTDebt = _getValueUSD(
            contractsCache.controller,
            _token,
            _additionalTokenAmount
        );

        // 1/(1-1/ICR) = leverage. (1 - 1/ICR) = 1/leverage
        // 1 - 1/leverage = 1/ICR. ICR = 1/(1 - 1/leverage) = (1/((leverage-1)/leverage)) = leverage / (leverage - 1)
        // ICR = leverage / (leverage - 1)

        // ICR = VC value of collateral / debt
        // debt = VC value of collateral / ICR.
        // debt = VC value of collateral * (leverage - 1) / leverage

        uint256 slippageAdjustedValue = _additionalTokenAmount
            .mul(DECIMAL_PRECISION.sub(_maxSlippage))
            .div(DECIMAL_PRECISION);

        // Mint to the router.
        _pustTokenMint(contractsCache.pustToken, router, _additionalPUSTDebt);

        // route will swap the tokens and transfer it to the active pool automatically. Router will send to active pool
        IERC20 erc20Token = IERC20(_token);
        uint256 balanceBefore = _IERC20TokenBalanceOf(erc20Token, activePoolAddress);
        _finalTokenAmount = IPalmLever(router).route(
            activePoolAddress,
            address(contractsCache.pustToken),
            _token,
            _additionalPUSTDebt,
            slippageAdjustedValue
        );
        require(
            _IERC20TokenBalanceOf(erc20Token, activePoolAddress) ==
                balanceBefore.add(_finalTokenAmount),
            "4"
        );
    }

    /**
     * @notice Opens AssetPortfolio Internal
     * @dev amounts should be a uint array giving the amount of each collateral
     * to be transferred in in order of the current controller
     * Should be called *after* collateral has been already sent to the active pool
     * Should confirm _colls, is valid collateral prior to calling this
     */
    function _openAssetPortfolioInternal(
        OpenAssetPortfolio_Params memory params,
        address[] memory _colls,
        uint256[] memory _amounts,
        ContractsCache memory contractsCache
    ) internal {
        LocalVariables_openAssetPortfolio memory vars;
        (
            vars.isRecoveryMode,
            vars.entireSystemCollVC,
            vars.entireSystemCollRVC,
            vars.entireSystemDebt
        ) = _checkRecoveryModeAndSystem();

        _requireValidMaxFeePercentage(params._maxFeePercentage, vars.isRecoveryMode);
        _requireAssetPortfolioStatus(contractsCache.assetPortfolioManager, false);

        // Start with base amount before adding any fees.
        vars.netDebt = params._PUSTAmount;

        // For every collateral type in, calculate the VC, RVC, and get the variable fee
        (vars.VC, vars.RVC) = _getValuesVCAndRVC(contractsCache.controller, _colls, _amounts);

        if (!vars.isRecoveryMode) {
            // when not in recovery mode, add in the 0.5% fee
            vars.PUSTFee = _triggerBorrowingFee(
                contractsCache,
                params._PUSTAmount,
                vars.VC, // here it is just VC in, which is always larger than PUST amount
                params._maxFeePercentage
            );
            params._maxFeePercentage = params._maxFeePercentage.sub(
                vars.PUSTFee.mul(DECIMAL_PRECISION).div(vars.VC)
            );
        }

        // Add in variable fee. Always present, even in recovery mode.
        {
            uint256 variableFee;
            (variableFee, vars.boostFactor) = _getTotalVariableDepositFeeAndUpdate(
                contractsCache.controller,
                _colls,
                _amounts,
                params._leverages,
                vars.entireSystemCollVC,
                vars.VC,
                0
            );
            _requireUserAcceptsFee(variableFee, vars.VC, params._maxFeePercentage);
            _mintPUSTFeeAndSplit(contractsCache, variableFee);
            vars.PUSTFee = vars.PUSTFee.add(variableFee);
            emit VariableFeePaid(msg.sender, variableFee);
        }

        // Adds total fees to netDebt
        vars.netDebt = vars.netDebt.add(vars.PUSTFee); // The raw debt change includes the fee

        _requireAtLeastMinNetDebt(vars.netDebt);
        // ICR is based on the composite debt,
        // i.e. the requested PUST amount + PUST borrowing fee + PUST deposit fee + PUST gas comp.
        // _getCompositeDebt returns  vars.netDebt + PUST gas comp = 200
        vars.compositeDebt = _getCompositeDebt(vars.netDebt);

        vars.ICR = _computeCR(vars.VC, vars.compositeDebt);

        if (vars.isRecoveryMode) {
            _requireICRisAboveCCR(vars.ICR);
        } else {
            _requireICRisAboveMCR(vars.ICR);
            _requireNewTCRisAboveCCR(
                _getNewTCRFromAssetPortfolioChange(
                    vars.entireSystemCollRVC,
                    vars.entireSystemDebt,
                    vars.RVC,
                    vars.compositeDebt,
                    true,
                    true
                )
            ); // bools: coll increase, debt increase);
        }

        // Set the assetPortfolio struct's properties (1 = active)
        contractsCache.assetPortfolioManager.setAssetPortfolioStatus(msg.sender, 1);

        _increaseAssetPortfolioDebt(contractsCache.assetPortfolioManager, vars.compositeDebt);

        _updateAssetPortfolioCollAndStakeAndTotalStakes(contractsCache.assetPortfolioManager, _colls, _amounts);

        contractsCache.assetPortfolioManager.updateAssetPortfolioRewardSnapshots(msg.sender);

        // Pass in fee as percent of total VC in for boost.
        sortedAssetPortfolios.insert(
            msg.sender,
            _computeCR(vars.RVC, vars.compositeDebt), // insert with new AICR.
            params._upperHint,
            params._lowerHint,
            vars.boostFactor
        );

        // Emit with assetPortfolio index calculated once inserted
        emit AssetPortfolioCreated(msg.sender, contractsCache.assetPortfolioManager.addAssetPortfolioOwnerToArray(msg.sender));

        // Receive collateral for tracking by active pool
        _activePoolReceiveCollateral(contractsCache.activePool, _colls, _amounts);

        // Send the user the PUST debt
        _withdrawPUST(
            contractsCache.activePool,
            contractsCache.pustToken,
            msg.sender,
            params._PUSTAmount.sub(params._totalPUSTDebtFromLever),
            vars.netDebt
        );

        // Move the PUST gas compensation to the Gas Pool
        _withdrawPUST(
            contractsCache.activePool,
            contractsCache.pustToken,
            gasPoolAddress,
            PUST_GAS_COMPENSATION,
            PUST_GAS_COMPENSATION
        );

        emit AssetPortfolioUpdated(
            msg.sender,
            vars.compositeDebt,
            _colls,
            _amounts,
            BorrowerOperation.openAssetPortfolio
        );
        emit PUSTBorrowingFeePaid(msg.sender, vars.PUSTFee);
    }

    /**
     * @notice add collateral to assetPortfolio. If leverage is provided then it will lever up on those collaterals using single lever up function.
     *  Can also be used to just add collateral to the assetPortfolio.
     * @dev Calls _adjustAssetPortfolio with correct params. Can only increase collateral and leverage, and add more debt.
     * @param _collsIn The addresses of collaterals to be added to this assetPortfolio. Must be passed in, in order of the whitelisted collateral.
     * @param _amountsIn The amounts of each collateral to be used in the assetPortfolio. If passing in a vault token, the amount must be the
     *  amount of the underlying asset, but the address passed in must be the vault token address. So, for example, if trying to
     *  open a assetPortfolio with Benqi USDC (qiUSDC), then the address passed in must be Palm Vault qiUSDC, but the amount must be of
     *  qiUSDC in your wallet. The resulting amount in your assetPortfolio will be of the vault token, so to see how much actual qiUSDC you have
     *  you must use the conversion ratio on the vault contract. 
     * @param _leverages The leverage amounts on each collateral to be used in the lever up function. If 0 there is no leverage on that coll
     * @param _maxSlippages The max slippage amount when swapping PUST for collateral
     * @param _PUSTAmount Amount of PUST to add to the assetPortfolio debt. This is separate from the amount of PUST taken against the leveraged amounts
     *  for each collateral which is levered up on. isDebtIncrease is automatically true.
     * @param _upperHint The address of the assetPortfolio above this one in the sorted assetPortfolios list.
     * @param _lowerHint The address of the assetPortfolio below this one in the sorted assetPortfolios list.
     * @param _maxFeePercentage The maximum percentage of the Collateral VC in that can be taken as fee.
     */
    function addCollLeverUp(
        address[] memory _collsIn,
        uint256[] memory _amountsIn,
        uint256[] memory _leverages,
        uint256[] memory _maxSlippages,
        uint256 _PUSTAmount,
        address _upperHint,
        address _lowerHint,
        uint256 _maxFeePercentage
    ) external override nonReentrant {
        ContractsCache memory contractsCache = ContractsCache(
            assetPortfolioManager,
            activePool,
            pustToken,
            controller
        );
        _requireLeverUpEnabled(contractsCache.controller);
        uint256 collsLen = _collsIn.length;

        // check that all _collsIn collateral types are in the controller and in correct order.
        _requireValidCollateral(_collsIn, _amountsIn, contractsCache.controller, true);

        // Must check that other passed in arrays are correct length
        _requireInputCorrect(collsLen == _leverages.length && collsLen == _maxSlippages.length);

        // Keep track of total PUST from levering up to pass into adjustAssetPortfolio
        uint256 totalPUSTDebtFromLever;
        for (uint256 i; i < collsLen; ++i) {
            if (_maxSlippages[i] != 0) {
                (uint256 additionalTokenAmount, uint256 additionalPUSTDebt) = _singleLeverUp(
                    _collsIn[i],
                    _amountsIn[i],
                    _leverages[i],
                    _maxSlippages[i],
                    contractsCache
                );
                // Transfer into active pool, non levered amount, and add to additional token amount returned. 
                // additional token amount was set to the original amount * leverage.
                _amountsIn[i] = additionalTokenAmount.add(_singleTransferCollateralIntoActivePool(_collsIn[i], _amountsIn[i]));
                totalPUSTDebtFromLever = totalPUSTDebtFromLever.add(additionalPUSTDebt);
            } else {
                require(_leverages[i] == 0, "2");
                // Otherwise skip and do normal transfer that amount into active pool.
                _amountsIn[i] = _singleTransferCollateralIntoActivePool(_collsIn[i], _amountsIn[i]);
            }
        }
        AdjustAssetPortfolio_Params memory params;
        params._upperHint = _upperHint;
        params._lowerHint = _lowerHint;
        params._maxFeePercentage = _maxFeePercentage;
        params._leverages = _leverages;
        _PUSTAmount = _PUSTAmount.add(totalPUSTDebtFromLever);
        params._totalPUSTDebtFromLever = totalPUSTDebtFromLever;

        params._PUSTChange = _PUSTAmount;
        params._isDebtIncrease = true;

        params._collsIn = _collsIn;
        params._amountsIn = _amountsIn;
        _adjustAssetPortfolio(params, contractsCache);
    }

    /**
     * @notice Adjusts assetPortfolio with multiple colls in / out. Can either add or remove collateral. No leverage available with this function.
     *   Can increase or remove debt as well. Cannot do both adding and removing the same collateral at the same time.
     * @dev Calls _adjustAssetPortfolio with correct params
     * @param _collsIn The addresses of collaterals to be added to this assetPortfolio. Must be passed in, in order of the whitelisted collateral.
     * @param _amountsIn The amounts of each collateral to be used in the assetPortfolio. If passing in a vault token, the amount must be the
     *  amount of the underlying asset, but the address passed in must be the vault token address. So, for example, if trying to
     *  open a assetPortfolio with Benqi USDC (qiUSDC), then the address passed in must be Palm Vault qiUSDC, but the amount must be of
     *  qiUSDC in your wallet. The resulting amount in your assetPortfolio will be of the vault token, so to see how much actual qiUSDC you have
     *  you must use the conversion ratio on the vault contract. 
     * @param _collsOut The addresses of collaterals to be removed from this assetPortfolio. Must be passed in, in order of the whitelisted collateral.
     * @param _amountsOut The amounts of each collateral to be removed from this assetPortfolio. Withdrawing a vault token would require you to have
     *  the amount of the vault token, unlike when depositing. So, for example, if trying to open a assetPortfolio with Benqi USDC (qiUSDC), then the
     *  address passed in must be Palm Vault qiUSDC, and the amount is also Palm Vault qi
     * @param _PUSTChange Amount of PUST to either withdraw or pay back. The resulting PUST Amount + 200 PUST Gas compensation plus any PUST
     *  fees plus amount from leverages must be > 2000. This min debt amount is intended to reduce the amount of small assetPortfolios
     *  that are opened, since liquidating small assetPortfolios may clog the network and we want to prioritize liquidations of larger
     *  assetPortfolios in turbulant gas conditions.
     * @param _isDebtIncrease True if more debt is withdrawn, false if it is paid back.
     * @param _upperHint The address of the assetPortfolio above this one in the sorted assetPortfolios list.
     * @param _lowerHint The address of the assetPortfolio below this one in the sorted assetPortfolios list.
     * @param _maxFeePercentage The maximum percentage of the Collateral VC in that can be taken as fee. There is an edge case here if the
     *   VC in is less than the new debt taken out, then it will be assessed on the debt instead.
     */
    function adjustAssetPortfolio(
        address[] calldata _collsIn,
        uint256[] memory _amountsIn,
        address[] calldata _collsOut,
        uint256[] calldata _amountsOut,
        uint256 _PUSTChange,
        bool _isDebtIncrease,
        address _upperHint,
        address _lowerHint,
        uint256 _maxFeePercentage
    ) external override nonReentrant {
        ContractsCache memory contractsCache = ContractsCache(
            assetPortfolioManager,
            activePool,
            pustToken,
            controller
        );
        // check that all _collsIn collateral types are in the controller
        // Replaces calls to requireValidCollateral and condenses them into one controller call.
        {
            uint256 collsInLen = _collsIn.length;
            uint256 collsOutLen = _collsOut.length;
            _requireInputCorrect(
                collsOutLen == _amountsOut.length && collsInLen == _amountsIn.length
            );
            for (uint256 i; i < collsInLen; ++i) {
                _requireInputCorrect(_amountsIn[i] != 0);
            }
            for (uint256 i; i < collsOutLen; ++i) {
                _requireInputCorrect(_amountsOut[i] != 0);
            }
        }

        // Checks that the collateral list is in order of the whitelisted collateral efficiently in controller.
        contractsCache.controller.checkCollateralListDouble(_collsIn, _collsOut);

        // pull in deposit collateral
        _transferCollateralsIntoActivePool(_collsIn, _amountsIn);

        AdjustAssetPortfolio_Params memory params;
        params._leverages = new uint256[](_collsIn.length);
        params._collsIn = _collsIn;
        params._amountsIn = _amountsIn;
        params._collsOut = _collsOut;
        params._amountsOut = _amountsOut;
        params._PUSTChange = _PUSTChange;
        params._isDebtIncrease = _isDebtIncrease;
        params._upperHint = _upperHint;
        params._lowerHint = _lowerHint;
        params._maxFeePercentage = _maxFeePercentage;

        _adjustAssetPortfolio(params, contractsCache);
    }

    /**
     * @notice Alongside a debt change, this function can perform either a collateral top-up or a collateral withdrawal
     * @dev the ith element of _amountsIn and _amountsOut corresponds to the ith element of the addresses _collsIn and _collsOut passed in
     * Should be called after the collsIn has been sent to ActivePool. Adjust assetPortfolio params are defined in above functions.
     */
    function _adjustAssetPortfolio(AdjustAssetPortfolio_Params memory params, ContractsCache memory contractsCache)
        internal
    {
        LocalVariables_adjustAssetPortfolio memory vars;

        // Checks if we are in recovery mode, and since that requires calculations of entire system coll and debt, return that here too. 
        (
            vars.isRecoveryMode,
            vars.entireSystemCollVC,
            vars.entireSystemCollRVC,
            vars.entireSystemDebt
        ) = _checkRecoveryModeAndSystem();

        // Require that the max fee percentage is correct (< 100, and if not recovery mode > 0.5)
        _requireValidMaxFeePercentage(params._maxFeePercentage, vars.isRecoveryMode);

        // Checks that at least one array is non-empty, and also that at least one value is 1.
        _requireNonZeroAdjustment(params._amountsIn, params._amountsOut, params._PUSTChange);

        // Require assetPortfolio is active
        _requireAssetPortfolioStatus(contractsCache.assetPortfolioManager, true);

        // Apply pending rewards so that assetPortfolio info is up to date
        _applyPendingRewards(contractsCache.assetPortfolioManager);

        (vars.VCin, vars.RVCin) = _getValuesVCAndRVC(contractsCache.controller, params._collsIn, params._amountsIn);
        (vars.VCout, vars.RVCout) = _getValuesVCAndRVC(contractsCache.controller, params._collsOut, params._amountsOut);

        // If it is a debt increase then we need to take the max of VCin and debt increase and use that number to assess
        // the fee based on the new max fee percentage factor. 
        if (params._isDebtIncrease) {
            vars.maxFeePercentageFactor = (vars.VCin >= params._PUSTChange)
                ? vars.VCin
                : params._PUSTChange;
        } else {
            vars.maxFeePercentageFactor = vars.VCin;
        }
        
        vars.netDebtChange = params._PUSTChange;

        // If the adjustment incorporates a debt increase and system is in Normal Mode, then trigger a borrowing fee
        if (params._isDebtIncrease && !vars.isRecoveryMode) {
            vars.PUSTFee = _triggerBorrowingFee(
                contractsCache,
                params._PUSTChange,
                vars.maxFeePercentageFactor, // max of VC in and PUST change here to see what the max borrowing fee is triggered on.
                params._maxFeePercentage
            );
            // passed in max fee minus actual fee percent applied so far
            params._maxFeePercentage = params._maxFeePercentage.sub(
                vars.PUSTFee.mul(DECIMAL_PRECISION).div(vars.maxFeePercentageFactor)
            );
            vars.netDebtChange = vars.netDebtChange.add(vars.PUSTFee); // The raw debt change includes the fee
        }

        // get current portfolio in assetPortfolio
        (vars.currAssets, vars.currAmounts, vars.debt) = _getCurrentAssetPortfolioState(
            contractsCache.assetPortfolioManager
        );

        // current VC based on current portfolio and latest prices 
        (vars.currVC, vars.currRVC) = _getValuesVCAndRVC(contractsCache.controller, vars.currAssets, vars.currAmounts);

        // get new portfolio in assetPortfolio after changes. Will error if invalid changes, if coll decrease is more
        // than the amount possible. 
        (vars.newAssets, vars.newAmounts) = _subColls(
            _sumColls(
                newColls(vars.currAssets, vars.currAmounts),
                newColls(params._collsIn, params._amountsIn)
            ),
            params._collsOut,
            params._amountsOut
        );

        // If there is an increase in the amount of assets in a assetPortfolio
        if (vars.currAssets.length < vars.newAssets.length) {
            // Check that the result is less than the maximum amount of assets in a assetPortfolio
            _requireValidAssetPortfolioCollsLen(contractsCache.controller, vars.currAssets.length);
        }

        // new RVC based on new portfolio and latest prices.
        vars.newVC = vars.currVC.add(vars.VCin).sub(vars.VCout);
        vars.newRVC = vars.currRVC.add(vars.RVCin).sub(vars.RVCout);

        vars.isRVCIncrease = vars.newRVC > vars.currRVC;

        if (vars.isRVCIncrease) {
            vars.collChangeRVC = (vars.newRVC).sub(vars.currRVC);
        } else {
            vars.collChangeRVC = (vars.currRVC).sub(vars.newRVC);
        }

        // If passing in collateral, then get the total variable deposit fee and boost factor. If fee is 
        // nonzero, then require the user accepts this fee as well. 
        if (params._collsIn.length != 0) {
            (vars.variablePUSTFee, vars.boostFactor) = _getTotalVariableDepositFeeAndUpdate(
                contractsCache.controller,
                params._collsIn,
                params._amountsIn,
                params._leverages,
                vars.entireSystemCollVC,
                vars.VCin,
                vars.VCout
            );
            if (vars.variablePUSTFee != 0) {
                _requireUserAcceptsFee(
                    vars.variablePUSTFee,
                    vars.maxFeePercentageFactor,
                    params._maxFeePercentage
                );
                _mintPUSTFeeAndSplit(contractsCache, vars.variablePUSTFee);
                emit VariableFeePaid(msg.sender, vars.variablePUSTFee);
            }
        }

        // Get the assetPortfolio's old ICR before the adjustment, and what its new ICR will be after the adjustment
        vars.oldICR = _computeCR(vars.currVC, vars.debt);

        vars.debt = vars.debt.add(vars.variablePUSTFee);
        vars.newICR = _computeCR(
            vars.newVC, // if debt increase, then add net debt change and subtract otherwise.
            params._isDebtIncrease
                ? vars.debt.add(vars.netDebtChange)
                : vars.debt.sub(vars.netDebtChange)
        );

        // Check the adjustment satisfies all conditions for the current system mode
        // In Recovery Mode, only allow:
        // - Pure collateral top-up
        // - Pure debt repayment
        // - Collateral top-up with debt repayment
        // - A debt increase combined with a collateral top-up which makes the ICR >= 150% and improves the ICR (and by extension improves the TCR).
        //
        // In Normal Mode, ensure:
        // - The new ICR is above MCR
        // - The adjustment won't pull the TCR below CCR
        if (vars.isRecoveryMode) {
            // Require no coll withdrawal. Require that there is no coll withdrawal. The condition that _amountOut, if
            // nonzero length, has a nonzero amount in each is already checked previously, so we only need to check length here.
            require(params._amountsOut.length == 0, "3");
            if (params._isDebtIncrease) {
                _requireICRisAboveCCR(vars.newICR);
                require(vars.newICR >= vars.oldICR, "3");
            }
        } else {
            // if Normal Mode
            _requireICRisAboveMCR(vars.newICR);
            _requireNewTCRisAboveCCR(
                _getNewTCRFromAssetPortfolioChange(
                    vars.entireSystemCollRVC,
                    vars.entireSystemDebt,
                    vars.collChangeRVC,
                    vars.netDebtChange,
                    vars.isRVCIncrease,
                    params._isDebtIncrease
                )
            );
        }

        // If eligible, then active pool receives the collateral for its internal logging. 
        if (params._collsIn.length != 0) {
            _activePoolReceiveCollateral(
                contractsCache.activePool,
                params._collsIn,
                params._amountsIn
            );
        }

        // If debt increase, then add pure debt + fees 
        if (params._isDebtIncrease) {
            // if debt increase, increase by both amounts
            vars.newDebt = _increaseAssetPortfolioDebt(
                contractsCache.assetPortfolioManager,
                vars.netDebtChange.add(vars.variablePUSTFee)
            );
        } else {
            if (vars.netDebtChange > vars.variablePUSTFee) {
                // if debt decrease, and greater than variable fee, decrease
                vars.newDebt = contractsCache.assetPortfolioManager.decreaseAssetPortfolioDebt(
                    msg.sender,
                    vars.netDebtChange - vars.variablePUSTFee
                ); // already checked no safemath needed
            } else {
                // otherwise increase by opposite subtraction
                vars.newDebt = _increaseAssetPortfolioDebt(
                    contractsCache.assetPortfolioManager,
                    vars.variablePUSTFee - vars.netDebtChange
                );
            }
        }

        // Based on new assets, update assetPortfolio coll and stakes.
        _updateAssetPortfolioCollAndStakeAndTotalStakes(
            contractsCache.assetPortfolioManager,
            vars.newAssets,
            vars.newAmounts
        );

        // Re-insert assetPortfolio in to the sorted list
        sortedAssetPortfolios.reInsertWithNewBoost(
            msg.sender,
            _computeCR(vars.newRVC, vars.newDebt), // Insert with new AICR
            params._upperHint,
            params._lowerHint,
            vars.boostFactor,
            vars.VCin,
            vars.currVC
        );

        // in case of unlever up
        if (params._isUnlever) {
            // 1. Withdraw the collateral from active pool and perform swap using single unlever up and corresponding router.
            _unleverColls(
                contractsCache,
                params._collsOut,
                params._amountsOut,
                params._maxSlippages
            );
        }

        // When the adjustment is a debt repayment, check it's a valid amount and that the caller has enough PUST
        if ((!params._isDebtIncrease && params._PUSTChange != 0) || params._isUnlever) {
            _requireAtLeastMinNetDebt(_getNetDebt(vars.debt).sub(vars.netDebtChange));
            _requireValidPUSTRepayment(vars.debt, vars.netDebtChange);
            _requireSufficientPUSTBalance(contractsCache.pustToken, vars.netDebtChange);
        }

        if (params._isUnlever) {
            // 2. update the assetPortfolio with the new collateral and debt, repaying the total amount of PUST specified.
            // if not enough coll sold for PUST, must cover from user balance
            _repayPUST(
                contractsCache.activePool,
                contractsCache.pustToken,
                msg.sender,
                params._PUSTChange
            );
        } else {
            // Use the unmodified _PUSTChange here, as we don't send the fee to the user
            _movePUST(
                contractsCache.activePool,
                contractsCache.pustToken,
                params._PUSTChange.sub(params._totalPUSTDebtFromLever), // 0 in non lever case
                params._isDebtIncrease,
                vars.netDebtChange
            );

            // Additionally move the variable deposit fee to the active pool manually, as it is always an increase in debt
            _withdrawPUST(
                contractsCache.activePool,
                contractsCache.pustToken,
                msg.sender,
                0,
                vars.variablePUSTFee
            );

            // transfer withdrawn collateral to msg.sender from ActivePool
            _sendCollateralsUnwrap(contractsCache.activePool, params._collsOut, params._amountsOut);
        }

        emit AssetPortfolioUpdated(
            msg.sender,
            vars.newDebt,
            vars.newAssets,
            vars.newAmounts,
            BorrowerOperation.adjustAssetPortfolio
        );
        
        emit PUSTBorrowingFeePaid(msg.sender, vars.PUSTFee);
    }

    /**
     * @notice internal function for un-levering up. Takes the collateral amount specified passed in, and swaps it using the whitelisted
     * router back into PUST, so that the debt can be paid back for a certain amount.
     * @param _token The address of the collateral to swap to PUST
     * @param _amount The amount of collateral to be swapped
     * @param _maxSlippage The maximum slippage allowed in the swap
     * @return _finalPUSTAmount The amount of PUST to be paid back to the borrower.
     */
    function _singleUnleverUp(
        ContractsCache memory contractsCache,
        address _token,
        uint256 _amount,
        uint256 _maxSlippage
    ) internal returns (uint256 _finalPUSTAmount) {
        _requireInputCorrect(_maxSlippage <= DECIMAL_PRECISION);
        // Send collaterals to the whitelisted router from the active pool so it can perform the swap
        address router = _getDefaultRouterAddress(contractsCache.controller, _token);
        contractsCache.activePool.sendSingleCollateral(router, _token, _amount);

        // then calculate value amount of expected PUST output based on amount of token to sell
        uint256 valueOfCollateral = _getValueUSD(contractsCache.controller, _token, _amount);
        uint256 slippageAdjustedValue = valueOfCollateral
            .mul(DECIMAL_PRECISION.sub(_maxSlippage))
            .div(DECIMAL_PRECISION);

        // Perform swap in the router using router.unRoute, which sends the PUST back to the msg.sender, guaranteeing at least slippageAdjustedValue out.
        _finalPUSTAmount = IPalmLever(router).unRoute(
            msg.sender,
            _token,
            address(contractsCache.pustToken),
            _amount,
            slippageAdjustedValue
        );
    }

    /**
     * @notice Takes the colls and amounts, transfer non levered from the active pool to the user, and unlevered to this contract
     * temporarily. Then takes the unlevered ones and calls relevant router to swap them to the user.
     * @dev Not called by close assetPortfolio due to difference in total amount unlevered, ability to swap back some amount as well as unlevering
     * when closing assetPortfolio.
     * @param _colls addresses of collaterals to unlever 
     * @param _amounts amounts of collaterals to unlever
     * @param _maxSlippages maximum slippage allowed for each swap. If 0, then just send collateral. 
     */
    function _unleverColls(
        ContractsCache memory contractsCache,
        address[] memory _colls,
        uint256[] memory _amounts,
        uint256[] memory _maxSlippages
    ) internal {
        uint256 balanceBefore = _IERC20TokenBalanceOf(contractsCache.pustToken, msg.sender);
        uint256 totalPUSTUnlevered;
        for (uint256 i; i < _colls.length; ++i) {
            // If max slippages is 0, then it is a normal withdraw. Otherwise it needs to be unlevered.
            if (_maxSlippages[i] != 0) {
                totalPUSTUnlevered = totalPUSTUnlevered.add(
                    _singleUnleverUp(contractsCache, _colls[i], _amounts[i], _maxSlippages[i])
                );
            } else {
                _sendSingleCollateralUnwrap(contractsCache.activePool, _colls[i], _amounts[i]);
            }
        }
        // Do manual check of if balance increased by correct amount of PUST
        require(
            _IERC20TokenBalanceOf(contractsCache.pustToken, msg.sender) ==
                balanceBefore.add(totalPUSTUnlevered),
            "6"
        );
    }

    /**
     * @notice Withdraw collateral from a assetPortfolio
     * @dev Calls _adjustAssetPortfolio with correct params.
     * Specifies amount of collateral to withdraw and how much debt to repay,
     * Can withdraw coll and *only* pay back debt using this function. Will take
     * the collateral given and send PUST back to user. Then they will pay back debt
     * first transfers amount of collateral from active pool then sells.
     * calls _singleUnleverUp() to perform the swaps using the wrappers. should have no fees.
     * @param _collsOut The addresses of collaterals to be removed from this assetPortfolio. Must be passed in, in order of the whitelisted collateral.
     * @param _amountsOut The amounts of each collateral to be removed from this assetPortfolio.
     *   The ith element of this array is the amount of the ith collateral in _collsOut
     * @param _maxSlippages Max slippage for each collateral type. If 0, then just withdraw without unlever
     * @param _PUSTAmount Amount of PUST to pay back. Pulls from user's balance after doing the unlever swap, so it can be from the swap itself
     *  or it can be from their existing balance of PUST. The resulting PUST Amount + 200 PUST Gas compensation plus any PUST
     *  fees plus amount from leverages must be > 2000. This min debt amount is intended to reduce the amount of small assetPortfolios
     *  that are opened, since liquidating small assetPortfolios may clog the network and we want to prioritize liquidations of larger
     *  assetPortfolios in turbulant gas conditions.
     * @param _upperHint The address of the assetPortfolio above this one in the sorted assetPortfolios list.
     * @param _lowerHint The address of the assetPortfolio below this one in the sorted assetPortfolios list.
     */
    function withdrawCollUnleverUp(
        address[] calldata _collsOut,
        uint256[] calldata _amountsOut,
        uint256[] calldata _maxSlippages,
        uint256 _PUSTAmount,
        address _upperHint,
        address _lowerHint
    ) external override nonReentrant {
        ContractsCache memory contractsCache = ContractsCache(
            assetPortfolioManager,
            activePool,
            pustToken,
            controller
        );
        // check that all _collsOut collateral types are in the controller, as well as that it doesn't overlap with itself.
        _requireValidCollateral(_collsOut, _amountsOut, contractsCache.controller, false);
        _requireInputCorrect(_amountsOut.length == _maxSlippages.length);

        AdjustAssetPortfolio_Params memory params;
        params._collsOut = _collsOut;
        params._amountsOut = _amountsOut;
        params._maxSlippages = _maxSlippages;
        params._PUSTChange = _PUSTAmount;
        params._upperHint = _upperHint;
        params._lowerHint = _lowerHint;
        // Will not be used but set to 100% to pass check for valid percent. 
        params._maxFeePercentage = DECIMAL_PRECISION;
        params._isUnlever = true;

        _adjustAssetPortfolio(params, contractsCache);
    }

    /**
     * @notice Close assetPortfolio and unlever a certain amount of collateral. For all amounts in amountsOut, transfer out that amount
     *   of collateral and swap them for PUST. Use that PUST and PUST from borrower's account to pay back remaining debt.
     * @dev Calls _adjustAssetPortfolio with correct params. nonReentrant
     * @param _collsOut Collateral types to withdraw
     * @param _amountsOut Amounts to withdraw. If 0, then just withdraw without unlever
     * @param _maxSlippages Max slippage for each collateral type
     */
    function closeAssetPortfolioUnlever(
        address[] calldata _collsOut,
        uint256[] calldata _amountsOut,
        uint256[] calldata _maxSlippages
    ) external override nonReentrant {
        _closeAssetPortfolio(_collsOut, _amountsOut, _maxSlippages, true);
    }

    /**
     * @notice Close assetPortfolio and send back collateral to user. Pays back debt from their address.
     * @dev Calls _adjustAssetPortfolio with correct params. nonReentrant
     */
    function closeAssetPortfolio() external override nonReentrant {
        _closeAssetPortfolio(new address[](0), new uint256[](0), new uint256[](0), false);
    }

    /**
     * @notice Closes assetPortfolio by applying pending rewards, making sure that the PUST Balance is sufficient, and transferring the
     * collateral to the owner, and repaying the debt.
     * @dev if it is a unlever, then it will transfer the collaterals / sell before. Otherwise it will just do it last.
     */
    function _closeAssetPortfolio(
        address[] memory _collsOut,
        uint256[] memory _amountsOut,
        uint256[] memory _maxSlippages,
        bool _isUnlever
    ) internal {
        ContractsCache memory contractsCache = ContractsCache(
            assetPortfolioManager,
            activePool,
            pustToken,
            controller
        );
        LocalVariables_closeAssetPortfolio memory vars;

        // Require assetPortfolio is active
        _requireAssetPortfolioStatus(contractsCache.assetPortfolioManager, true);
        // Check recovery mode + get entire system coll RVC and debt. Can't close assetPortfolio in recovery mode.
        (
            vars.isRecoveryMode,
            ,
            vars.entireSystemCollRVC,
            vars.entireSystemDebt
        ) = _checkRecoveryModeAndSystem();
        require(!vars.isRecoveryMode, "7");

        _applyPendingRewards(contractsCache.assetPortfolioManager);

        // Get current assetPortfolio colls to send back to user or unlever.
        (vars.colls, vars.amounts, vars.debt) = _getCurrentAssetPortfolioState(contractsCache.assetPortfolioManager);
        (, vars.assetPortfolioRVC) = _getValuesVCAndRVC(contractsCache.controller, vars.colls, vars.amounts);
        {
            // if unlever, will do extra.
            if (_isUnlever) {
                // Withdraw the collateral from active pool and perform swap using single unlever up and corresponding router.
                // tracks the amount of PUST that is received from swaps. Will send the _PUSTAmount back to repay debt while keeping remainder.
                // The router itself handles unwrapping
                uint256 j;
                uint256 balanceBefore = _IERC20TokenBalanceOf(contractsCache.pustToken, msg.sender);
                uint256 totalPUSTUnlevered;
                for (uint256 i; i < vars.colls.length; ++i) {
                    uint256 thisAmount = vars.amounts[i];
                    if (j < _collsOut.length && vars.colls[i] == _collsOut[j]) {
                        totalPUSTUnlevered = totalPUSTUnlevered.add(
                            _singleUnleverUp(
                                contractsCache,
                                _collsOut[j],
                                _amountsOut[j],
                                _maxSlippages[j]
                            )
                        );
                        // In the case of unlever, only unlever the amount passed in, and send back the difference
                        thisAmount = thisAmount.sub(_amountsOut[j]);
                        ++j;
                    }
                    // Send back remaining collateral
                    if (thisAmount > 0) {
                        _sendSingleCollateralUnwrap(
                            contractsCache.activePool,
                            vars.colls[i],
                            thisAmount
                        );
                    }
                }
                // Do manual check of if balance increased by correct amount of PUST
                require(
                    _IERC20TokenBalanceOf(contractsCache.pustToken, msg.sender) ==
                        balanceBefore.add(totalPUSTUnlevered),
                    "6"
                );
            }
        }

        // do check after unlever (if applies)
        _requireSufficientPUSTBalance(
            contractsCache.pustToken,
            vars.debt.sub(PUST_GAS_COMPENSATION)
        );
        _requireNewTCRisAboveCCR(
            _getNewTCRFromAssetPortfolioChange(
                vars.entireSystemCollRVC,
                vars.entireSystemDebt,
                vars.assetPortfolioRVC,
                vars.debt,
                false,
                false
            )
        );

        contractsCache.assetPortfolioManager.removeStakeAndCloseAssetPortfolio(msg.sender);

        // Burn the repaid PUST from the user's balance and the gas compensation from the Gas Pool
        _repayPUST(
            contractsCache.activePool,
            contractsCache.pustToken,
            msg.sender,
            vars.debt.sub(PUST_GAS_COMPENSATION)
        );
        _repayPUST(
            contractsCache.activePool,
            contractsCache.pustToken,
            gasPoolAddress,
            PUST_GAS_COMPENSATION
        );

        // Send the collateral back to the user
        // Also sends the rewards
        if (!_isUnlever) {
            _sendCollateralsUnwrap(contractsCache.activePool, vars.colls, vars.amounts);
        }

        // Essentially delete assetPortfolio event.
        emit AssetPortfolioUpdated(
            msg.sender,
            0,
            new address[](0),
            new uint256[](0),
            BorrowerOperation.closeAssetPortfolio
        );
    }

    // --- Helper functions ---

    /**
     * @notice Transfer in collateral and send to ActivePool
     * @dev Active pool is where the collateral is held
     */
    function _transferCollateralsIntoActivePool(address[] memory _colls, uint256[] memory _amounts)
        internal
    {
        uint256 amountsLen = _amounts.length;
        for (uint256 i; i < amountsLen; ++i) {
            // this _amounts array update persists during the code that runs after
            _amounts[i] = _singleTransferCollateralIntoActivePool(_colls[i], _amounts[i]);
        }
    }

    /**
     * @notice does one transfer of collateral into active pool. Checks that it transferred to the active pool correctly
     * In the case that it is wrapped token, it will wrap it on transfer in.
     * @return  the amount of receipt tokens it receives back if it is a vault token or otherwise
     * returns the amount of the collateral token returned
     */
    function _singleTransferCollateralIntoActivePool(address _coll, uint256 _amount) internal returns (uint256) {
        if (controller.isWrapped(_coll)) {
            // If vault asset then it wraps it and sends the wrapped version to the active pool
            // The amount is returned as the amount of receipt tokens that the user has. 
            return IPalmVaultToken(_coll).depositFor(msg.sender, address(activePool), _amount);
        } else {
            IERC20(_coll).safeTransferFrom(msg.sender, activePoolAddress, _amount);
            return _amount;
        }
    }

    /**
     * @notice Triggers normal borrowing fee
     * @dev Calculated from base rate and on PUST amount.
     * @param _PUSTAmount PUST amount sent in
     * @param _maxFeePercentageFactor the factor to assess the max fee on 
     * @param _maxFeePercentage the passed in max fee percentage. 
     * @return PUSTFee The resulting one time borrow fee.
     */
    function _triggerBorrowingFee(
        ContractsCache memory contractsCache,
        uint256 _PUSTAmount,
        uint256 _maxFeePercentageFactor,
        uint256 _maxFeePercentage
    ) internal returns (uint256 PUSTFee) {
        PUSTFee = contractsCache.assetPortfolioManager.decayBaseRateFromBorrowingAndCalculateFee(_PUSTAmount); // decay the baseRate state variable

        _requireUserAcceptsFee(PUSTFee, _maxFeePercentageFactor, _maxFeePercentage);

        // Send fee to PUST Fee recipient (sPALM) contract
        _mintPUSTFeeAndSplit(contractsCache, PUSTFee);
    }

    /** 
     * @notice Function for minting PUST to the treasury and to the recipient sPALM based on params in palm controller
     * @param _PUSTFee total fee to split
     */
    function _mintPUSTFeeAndSplit(ContractsCache memory contractsCache, uint256 _PUSTFee) internal {
        // Get fee splits and treasury address. 
        (uint256 feeSplit, address palmTreasury, address PUSTFeeRecipient) = contractsCache
            .controller
            .getFeeSplitInformation();
        uint256 treasurySplit = feeSplit.mul(_PUSTFee).div(DECIMAL_PRECISION);
        // Mint a percentage to the treasury
        _pustTokenMint(contractsCache.pustToken, palmTreasury, treasurySplit);
        // And the rest to PUST Fee recipient
        _pustTokenMint(contractsCache.pustToken, PUSTFeeRecipient, _PUSTFee - treasurySplit);
    }

    /**
     * @notice Moves the PUST around based on whether it is an increase or decrease in debt. Mints to active pool or takes from active pool
     * @param _PUSTChange amount of PUST to mint or burn
     * @param _isDebtIncrease if true then withdraw (mint) PUST, otherwise burn it.
     */
    function _movePUST(
        IActivePool _activePool,
        IPUSTToken _pustToken,
        uint256 _PUSTChange,
        bool _isDebtIncrease,
        uint256 _netDebtChange
    ) internal {
        if (_isDebtIncrease) {
            _withdrawPUST(_activePool, _pustToken, msg.sender, _PUSTChange, _netDebtChange);
        } else {
            _repayPUST(_activePool, _pustToken, msg.sender, _PUSTChange);
        }
    }

    /**
     * @notice Issue the specified amount of PUST to _account and increases the total active debt
     * @dev _netDebtIncrease potentially includes a PUSTFee
     */
    function _withdrawPUST(
        IActivePool _activePool,
        IPUSTToken _pustToken,
        address _account,
        uint256 _PUSTAmount,
        uint256 _netDebtIncrease
    ) internal {
        _activePool.increasePUSTDebt(_netDebtIncrease);
        _pustTokenMint(_pustToken, _account, _PUSTAmount);
    }

    /**
     * @notice Burn the specified amount of PUST from _account and decreases the total active debt
     */
    function _repayPUST(
        IActivePool _activePool,
        IPUSTToken _pustToken,
        address _account,
        uint256 _PUSTAmount
    ) internal {
        _activePool.decreasePUSTDebt(_PUSTAmount);
        _pustToken.burn(_account, _PUSTAmount);
    }

    /**
     * @notice Returns _coll1.amounts minus _amounts2. Used 
     * @dev Invariant that _coll1.tokens and _tokens2 are sorted by whitelist order of token indices from the PalmController.
     *    So, if WETH is whitelisted first, then WETH, then USDC, then [WETH, USDC] is a valid input order but [USDC, WETH] is not.
     *    This is done for gas efficiency. It will revert if there is a token existing in _tokens2 that is not in _coll1.tokens.
     *    Each iteration we increase the index for _coll1.tokens, and if the token is next in _tokens2, we perform the subtraction 
     *    which will throw an error if it underflows. Since they are ordered, if that next index in _coll1.tokens is less than the next 
     *    index in _tokens2, that means that next index in _tokens 2 is not in _coll1.tokens. If it reaches the end of _tokens2, then 
     *    we add the remaining collaterals in _coll1 to the result and we are done. If it reaches the end of _coll1, then check that 
     *    _coll2 is also empty. We are not sure how many tokens are nonzero so we also have to keep track of it to make their token
     *    array not keep 0 values. It will fill the first k entries post subtraction, so we can loop through the first k entries in 
     *    coll3.tokens, returning the final result coll4. This gives O(n) time complexity for the first loop where n is the number
     *    of tokens in _coll1.tokens. The second loop is O(k) where k is the number of resulting nonzero values. k is bounded by n 
     *    so the resulting time upper bound is O(2n), not depending on L = number of whitelisted collaterals. Since we are using 
     *    _coll1.tokens as the baseline the result of _subColls will also be sorted, keeping the invariant. 
     */
    function _subColls(
        newColls memory _coll1,
        address[] memory _tokens2,
        uint256[] memory _amounts2
    ) internal view returns (address[] memory, uint256[] memory) {
        // If subtracting nothing just return the _coll1 tokens and amounts. 
        if (_tokens2.length == 0) {
            return (_coll1.tokens, _coll1.amounts);
        }
        uint256 coll1Len = _coll1.tokens.length;

        newColls memory coll3;
        coll3.tokens = new address[](coll1Len);
        coll3.amounts = new uint256[](coll1Len);

        uint256[] memory tokenIndices1 = _getIndices(_coll1.tokens);
        uint256[] memory tokenIndices2 = _getIndices(_tokens2);

        // Tracker for the tokens1 array
        uint256 i;
        // Tracker for the tokens2 array
        uint256 j;
        // number of nonzero entries post subtraction.
        uint256 k;

        // Tracker for token whitelist index for all coll2. 
        uint256 tokenIndex2 = tokenIndices2[j];
        // Loop through all tokens1 in order. 
        for (; i < coll1Len; ++i) {
            uint256 tokenIndex1 = tokenIndices1[i];
            // If skipped past tokenIndex 2, then that means it was not seen in token index 1 array and this is an invalid sub. 
            _requireInputCorrect(tokenIndex2 >= tokenIndex1);
            // If they are equal do the subtraction and increment j / token index 2. 
            if (tokenIndex1 == tokenIndex2) {
                coll3.amounts[k] = _coll1.amounts[i].sub(_amounts2[j]);
                // if nonzero, add to coll3 and increment k
                if (coll3.amounts[k] != 0) {
                    coll3.tokens[k] = _coll1.tokens[i];
                    ++k;
                }
                // If we have reached the end of tokens2, exit out to finish adding the remaining coll1 values.
                if (j == _tokens2.length - 1) {
                    ++i;
                    break;
                }
                ++j;
                tokenIndex2 = tokenIndices2[j];
            } else { // Otherwise just add just add the coll1 value without subtracting. 
                coll3.amounts[k] = _coll1.amounts[i];
                coll3.tokens[k] = _coll1.tokens[i];
                ++k;
            }
        }
        while (i < coll1Len) {
            coll3.tokens[k] = _coll1.tokens[i];
            coll3.amounts[k] = _coll1.amounts[i];
            ++i;
            ++k;
        }
        // Require no additional token2 to be processed.
        _requireInputCorrect(j == _tokens2.length - 1);

        // Copy in all nonzero values from coll3 to coll4. The first k values in coll3 will be nonzero. 
        newColls memory coll4;
        coll4.tokens = new address[](k);
        coll4.amounts = new uint256[](k);
        for (i = 0; i < k; ++i) {
            coll4.tokens[i] = coll3.tokens[i];
            coll4.amounts[i] = coll3.amounts[i];
        }
        return (coll4.tokens, coll4.amounts);
    }

    // --- 'Require' wrapper functions ---

    /**
     * @notice Require that the amount of collateral in the assetPortfolio is not more than the max
     */
    function _requireValidAssetPortfolioCollsLen(IPalmController controller, uint256 _n) internal view {
        require(_n <= controller.getMaxCollsInAssetPortfolio());
    }

    /**
     * @notice Checks that amounts are nonzero, that the the length of colls and amounts are the same, that the coll is active,
     * and that there is no overlap collateral in the list. Calls controller version, which does these checks.
     */
    function _requireValidCollateral(
        address[] memory _colls,
        uint256[] memory _amounts,
        IPalmController controller,
        bool _deposit
    ) internal view {
        uint256 collsLen = _colls.length;
        _requireInputCorrect(collsLen == _amounts.length);
        for (uint256 i; i < collsLen; ++i) {
            _requireInputCorrect(_amounts[i] != 0);
        }
        controller.checkCollateralListSingle(_colls, _deposit);
    }

    /**
     * @notice Whether amountsIn is 0 or amountsOut is 0
     * @dev Condition of whether amountsIn is 0 amounts, or amountsOut is 0 amounts, is checked in previous call
     * to _requireValidCollateral
     */
    function _requireNonZeroAdjustment(
        uint256[] memory _amountsIn,
        uint256[] memory _amountsOut,
        uint256 _PUSTChange
    ) internal pure {
        require(_PUSTChange != 0 || _amountsIn.length != 0 || _amountsOut.length != 0, "1");
    }

    /** 
     * @notice require that lever up is enabled, stored in the Palm Controller.
     */
    function _requireLeverUpEnabled(IPalmController _controller) internal view {
        require(_controller.leverUpEnabled(), "13");
    }

    /** 
     * @notice Require assetPortfolio is active or not, depending on what is passed in.
     */
    function _requireAssetPortfolioStatus(IAssetPortfolioManager _assetPortfolioManager, bool _active) internal view {
        require(_assetPortfolioManager.isAssetPortfolioActive(msg.sender) == _active, "1");
    }

    /**
     * @notice Function require length equal, used to save contract size on revert strings
     */
    function _requireInputCorrect(bool lengthCorrect) internal pure {
        require(lengthCorrect, "19");
    }

    /** 
     * @notice Require that ICR is above the MCR of 110% 
     */
    function _requireICRisAboveMCR(uint256 _newICR) internal pure {
        require(_newICR >= MCR, "20");
    }

    /** 
     * @notice Require that ICR is above CCR of 150%, used in Recovery mode 
     */
    function _requireICRisAboveCCR(uint256 _newICR) internal pure {
        require(_newICR >= CCR, "21");
    }

    /** 
     * @notice Require that new TCR is above CCR of 150%, to prevent drop into Recovery mode
     */
    function _requireNewTCRisAboveCCR(uint256 _newTCR) internal pure {
        require(_newTCR >= CCR, "23");
    }

    /** 
     * @notice Require that the debt is above 2000
     */
    function _requireAtLeastMinNetDebt(uint256 _netDebt) internal pure {
        require(_netDebt >= MIN_NET_DEBT, "8");
    }

    /** 
     * @notice Require that the PUST repayment is valid at current debt.
     */
    function _requireValidPUSTRepayment(uint256 _currentDebt, uint256 _debtRepayment) internal pure {
        require(_debtRepayment <= _currentDebt.sub(PUST_GAS_COMPENSATION), "9");
    }

    /** 
     * @notice Require the borrower has enough PUST to pay back the debt they are supposed to pay back.
     */
    function _requireSufficientPUSTBalance(
        IPUSTToken _pustToken,
        uint256 _debtRepayment
    ) internal view {
        require(_IERC20TokenBalanceOf(_pustToken, msg.sender) >= _debtRepayment, "26");
    }

    /**
     * @notice requires that the max fee percentage is <= than 100%, and that the fee percentage is >= borrowing floor except in rec mode
     */
    function _requireValidMaxFeePercentage(uint256 _maxFeePercentage, bool _isRecoveryMode)
        internal
        pure
    {
        // Alwawys require max fee to be less than 100%, and if not in recovery mode then max fee must be greater than 0.5%
        if (
            _maxFeePercentage > DECIMAL_PRECISION ||
            (!_isRecoveryMode && _maxFeePercentage < BORROWING_FEE_FLOOR)
        ) {
            revert("27");
        }
    }

    // --- ICR and TCR getters ---

    /**
     * Calculates new TCR from the assetPortfolio change based on coll increase and debt change.
     */
    function _getNewTCRFromAssetPortfolioChange(
        uint256 _entireSystemColl,
        uint256 _entireSystemDebt,
        uint256 _collChange,
        uint256 _debtChange,
        bool _isCollIncrease,
        bool _isDebtIncrease
    ) internal pure returns (uint256) {
        _entireSystemColl = _isCollIncrease
            ? _entireSystemColl.add(_collChange)
            : _entireSystemColl.sub(_collChange);
        _entireSystemDebt = _isDebtIncrease
            ? _entireSystemDebt.add(_debtChange)
            : _entireSystemDebt.sub(_debtChange);

        return _computeCR(_entireSystemColl, _entireSystemDebt);
    }

    // --- External call functions included in internal functions to reduce contract size ---

    /** 
     * @notice calls apply pending rewards from assetPortfolio manager
     */
    function _applyPendingRewards(IAssetPortfolioManager _assetPortfolioManager) internal {
        _assetPortfolioManager.applyPendingRewards(msg.sender);
    }

    /** 
     * @notice calls pust token mint function
     */
    function _pustTokenMint(
        IPUSTToken _pustToken,
        address _to,
        uint256 _amount
    ) internal {
        _pustToken.mint(_to, _amount);
    }

    /** 
     * @notice calls send collaterals unwrap function in active pool 
     */
    function _sendCollateralsUnwrap(
        IActivePool _activePool,
        address[] memory _collsOut,
        uint256[] memory _amountsOut
    ) internal {
        _activePool.sendCollateralsUnwrap(msg.sender, _collsOut, _amountsOut);
    }

    /** 
     * @notice calls send single collateral unwrap function in active pool 
     */
    function _sendSingleCollateralUnwrap(
        IActivePool _activePool,
        address _collOut,
        uint256 _amountOut
    ) internal {
        _activePool.sendSingleCollateralUnwrap(msg.sender, _collOut, _amountOut);
    }

    /** 
     * @notice calls increase assetPortfolio debt from assetPortfolio manager
     */
    function _increaseAssetPortfolioDebt(IAssetPortfolioManager _assetPortfolioManager, uint256 _amount)
        internal
        returns (uint256)
    {
        return _assetPortfolioManager.increaseAssetPortfolioDebt(msg.sender, _amount);
    }

    /** 
     * @notice calls update assetPortfolio coll, and updates stake and total stakes for the borrower as well.
     */
    function _updateAssetPortfolioCollAndStakeAndTotalStakes(
        IAssetPortfolioManager _assetPortfolioManager,
        address[] memory _colls,
        uint256[] memory _amounts
    ) internal {
        _assetPortfolioManager.updateAssetPortfolioCollAndStakeAndTotalStakes(msg.sender, _colls, _amounts);
    }

    /** 
     * @notice calls receive collateral from the active pool 
     */
    function _activePoolReceiveCollateral(
        IActivePool _activePool,
        address[] memory _colls,
        uint256[] memory _amounts
    ) internal {
        _activePool.receiveCollateral(_colls, _amounts);
    }

    /** 
     * @notice gets the current assetPortfolio state (colls, amounts, debt)
     */
    function _getCurrentAssetPortfolioState(IAssetPortfolioManager _assetPortfolioManager)
        internal
        view
        returns (
            address[] memory,
            uint256[] memory,
            uint256
        )
    {
        return _assetPortfolioManager.getCurrentAssetPortfolioState(msg.sender);
    }

    /** 
     * @notice Gets the default router address from the palm controller.
     */
    function _getDefaultRouterAddress(IPalmController _controller, address _token)
        internal
        view
        returns (address)
    {
        return _controller.getDefaultRouterAddress(_token);
    }

    /** 
     * @notice Gets the value in USD of the collateral (no collateral weight)
     */
    function _getValueUSD(
        IPalmController _controller,
        address _token,
        uint256 _amount
    ) internal view returns (uint256) {
        return _controller.getValueUSD(_token, _amount);
    }

    /** 
     * @notice Gets the value in both VC and RVC from Controller at once to prevent additional loops. 
     */
    function _getValuesVCAndRVC(
        IPalmController _controller,
        address[] memory _colls,
        uint256[] memory _amounts
    ) internal view returns (uint256, uint256) {
        return _controller.getValuesVCAndRVC(_colls, _amounts);
    }

    /** 
     * @notice Gets the total variable deposit fee, and updates the last fee seen. See 
     *   PalmController and ThreePieceWiseFeeCurve for implementation details.
     */
    function _getTotalVariableDepositFeeAndUpdate(
        IPalmController controller,
        address[] memory _colls,
        uint256[] memory _amounts,
        uint256[] memory _leverages,
        uint256 _entireSystemColl,
        uint256 _VCin,
        uint256 _VCout
    ) internal returns (uint256, uint256) {
        return
            controller.getTotalVariableDepositFeeAndUpdate(
                _colls,
                _amounts,
                _leverages,
                _entireSystemColl,
                _VCin,
                _VCout
            );
    }

    /** 
     * @notice Gets PUST or some other token balance of an account.
     */
    function _IERC20TokenBalanceOf(IERC20 _token, address _borrower)
        internal
        view
        returns (uint256)
    {
        return _token.balanceOf(_borrower);
    }

    /** 
     * @notice calls multi getter for indices of collaterals passed in. 
     */
    function _getIndices(address[] memory colls) internal view returns (uint256[] memory) {
        return controller.getIndices(colls);
    }
}