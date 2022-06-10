// SPDX-License-Identifier: UNLICENSED

pragma solidity 0.6.11;

import "./Interfaces/ICollSurplusPool.sol";
import "./Interfaces/IPalmController.sol";
import "./Dependencies/SafeMath.sol";
import "./Dependencies/PoolBase.sol";
import "./Interfaces/IPalmVaultToken.sol";
import "./Dependencies/SafeERC20.sol";



/**
 * @notice The CollSurplusPool holds all the bonus collateral that occurs from liquidations and
 * redemptions, to be claimed by the assetPortfolio owner. If they have been liquidated, their PUST debt has been paid back, and a max
 * of 110% of their debt value, in USD of collateral, is distributed to the StabilityPool or is redistributed. If the collateral
 * weight of that collateral is < 1, then their USD ICR can be > 110% when they are liquidated, so the coll surplus is sent here
 * in that case.
 * It also has a redemption bonus, which is sent to the user if their entire assetPortfolio is redeemed. Full redemptions will always yield
 * coll surplus since redemptions are not allowed < 110% ICR. Additionally, 20% (by default) of the redemption fee is given here
 * and is claimable by the borrower.
 */

contract CollSurplusPool is ICollSurplusPool, PoolBase {
    using SafeMath for uint256;
    using SafeERC20 for IERC20;

    string public constant NAME = "CollSurplusPool";

    address internal borrowerOperationsAddress;
    address internal assetPortfolioManagerLiquidationsAddress;
    address internal assetPortfolioManagerRedemptionsAddress;
    address internal activePoolAddress;
    IERC20 internal pustToken;

    // deposited collateral tracker. Colls is always the controller list of all collateral tokens. Amounts
    newColls internal poolColl;

    // PUST token balance from redemption bonus
    mapping(address => uint256) public redemptionBonus;
    uint256 public totalRedemptionBonus;

    // Collateral surplus claimable by assetPortfolio owners
    mapping(address => newColls) internal balances;

    // --- Events ---

    event CollBalanceUpdated(address indexed _account);
    event CollateralSent(address _to);

    event RedemptionBonusSent(address _to, uint256 thisRedemptionBonus);
    event RedemptionBonusLogged(address _account, uint256 thisRedemptionBonus);

    // --- Contract setters ---
    bool private addressSet;
    /**
     * @notice setAddresses
     * @dev checks addresses to ensure they are valid
     * @param _borrowerOperationsAddress, address
     * @param _assetPortfolioManagerLiquidationsAddress, address
     * @param _assetPortfolioManagerRedemptionsAddress, address
     * @param _activePoolAddress, address
     * @param _controllerAddress, address
     * @param _pustTokenAddress address
     */
    function setAddresses(
        address _borrowerOperationsAddress,
        address _assetPortfolioManagerLiquidationsAddress,
        address _assetPortfolioManagerRedemptionsAddress,
        address _activePoolAddress,
        address _controllerAddress,
        address _pustTokenAddress
    ) external override {
        require(addressSet == false, "Addresses already set");
        addressSet = true;

        borrowerOperationsAddress = _borrowerOperationsAddress;
        assetPortfolioManagerLiquidationsAddress = _assetPortfolioManagerLiquidationsAddress;
        assetPortfolioManagerRedemptionsAddress = _assetPortfolioManagerRedemptionsAddress;
        activePoolAddress = _activePoolAddress;
        controller = IPalmController(_controllerAddress);
        pustToken = IERC20(_pustTokenAddress);
    }

    // --- Coll Surplus Functionality ---

    /**
     * @notice Function which claims the collateral that is owned by the sender's assetPortfolio.
     *   Gets the claimable colls and sets the state variables to nothing. Then transfers out and
     *   rids of the collateral from internal tracking as well.
     */
    function claimCollateral() external override {
        newColls memory claimableColl = balances[msg.sender];
        if (_collsIsNonZero(claimableColl)) {
            balances[msg.sender].tokens = new address[](0);
            balances[msg.sender].amounts = new uint256[](0); // sets balance of account to 0
            emit CollBalanceUpdated(msg.sender);

            poolColl.amounts = _leftSubColls(poolColl, claimableColl.tokens, claimableColl.amounts);
            emit CollateralSent(msg.sender);

            _sendColl(msg.sender, claimableColl);
        }
        // sendRedemption bonus also resets msg.sender's redemption bonus to 0
        _sendRedemptionBonus(msg.sender);
    }

    // --- Information tracking functionality ---

    /**
     * @notice Surplus value is accounted by the assetPortfolio manager.
     * @param _account address
     * @param _tokens array of address
     * @param _amounts array of uint256
     */
    function accountSurplus(
        address _account,
        address[] memory _tokens,
        uint256[] memory _amounts
    ) external override {
        _requireCallerIsTMRorTML();
        balances[_account] = _sumColls(balances[_account], newColls(_tokens, _amounts));
        emit CollBalanceUpdated(_account);
    }



    /**
     * @notice  Function called from TMR which adds the redemption bonus.
     * @dev On redemption, a portion of fees are eligible to be claimed by the
     * user who was redeemed against.
     * @param _account address
     * @param _amount uint256
     */
    function accountRedemptionBonus(address _account, uint256 _amount) external override {
        _requireCallerIsTMR();
        redemptionBonus[_account] = redemptionBonus[_account].add(_amount);
        totalRedemptionBonus = totalRedemptionBonus.add(_amount);
        emit RedemptionBonusLogged(_account, _amount);
    }

    /**
     * @notice get collateral from active pool
     * @dev this happens when a assetPortfolio gets liquidated and some
     * of the liquidated collateral is claimable as a surplus
     * @param _tokens array of address
     * @param _amounts array of uint256
     */
    function receiveCollateral(address[] memory _tokens, uint256[] memory _amounts)
        external
        override
    {
        _requireCallerIsActivePool();
        poolColl.amounts = _leftSumColls(poolColl, _tokens, _amounts);
    }

    /**
     * @notice Adds collateral type from the controller.
     * This is to keep the array updated to we can always do
     * leftSumColls when receiving new collateral.
     * @param _collateral address
     */
    function addCollateralType(address _collateral) external override {
        _requireCallerIsPalmController();
        poolColl.tokens.push(_collateral);
        poolColl.amounts.push(0);
    }

    // --- Internal functions for sending to borrower ---

    /**
     * @notice Function to send collateral out to an address, and checks if the asset is wrapped so that it can
     * unwrap in that case.
     * @param _to address
     * @param _colls newColls struct
     */
    function _sendColl(address _to, newColls memory _colls) internal {
        uint256 tokensLen = _colls.tokens.length;
        for (uint256 i; i < tokensLen; ++i) {
            address token = _colls.tokens[i];
            if (controller.isWrapped(token)) {
                // Unwraps for original owner. _amounts[i] is in terms of the receipt token, and
                // the user will receive back the underlying based on the current exchange rate.
                IPalmVaultToken(token).redeem(_to, _colls.amounts[i]);
            } else {
                // Otherwise transfer like normal ERC20
                IERC20(token).safeTransfer(_to, _colls.amounts[i]);
            }
        }
    }

    /**
     * @notice Function to send redemption bonus to user
     * @dev sets user's redemption bonus to 0 and reduces
     * totalRedemptionBonus by user's redemptionBonus
     * @param _to address
     */
    function _sendRedemptionBonus(address _to) internal {
        // Send PUST Redemption bonus if applicable
        uint256 thisRedemptionBonus = redemptionBonus[_to];
        if (thisRedemptionBonus != 0) {
            redemptionBonus[_to] = 0;
            totalRedemptionBonus = totalRedemptionBonus.sub(thisRedemptionBonus);
            pustToken.safeTransfer(_to, thisRedemptionBonus);
            emit RedemptionBonusSent(_to, thisRedemptionBonus);
        }
    }

    // --- Information getters ---

    /**
     * @notice Returns the VC of the contract
     *
     * Not necessarily equal to the the contract's raw VC balance - Collateral can be forcibly sent to contracts.
     *
     * @dev Computed when called by taking the collateral balances and
     * multiplying them by the corresponding price and ratio and then summing that
     * @return uint256 the VC
     */
    function getCollVC() external view override returns (uint256) {
        return _getVCColls(poolColl);
    }

    /**
     * @notice View function for getting the amount claimable by a particular assetPortfolio owner.
     * @dev User may have claimable assets if they get liquidated and the value of their
     * collateral is greater than 110% of their assetPortfolio's debt. In this case, the additional
     * collateral is sent to the CollSurplusPool and is available to be reclaimed.
     * This situation can occur during Recovery Mode with a collateral with safety ratio = 1,
     * but also can happen in Normal Mode with a assetPortfolio with collaterals with safety ratio < 1.
     * @param _account address
     * @param _collateral address
     * @return uint256 amount claimable
     */
    function getAmountClaimable(address _account, address _collateral)
        external
        view
        override
        returns (uint256)
    {
        address[] memory accountTokens = balances[_account].tokens;
        uint256 len = accountTokens.length;
        for (uint256 i; i < len; ++i) {
            if (accountTokens[i] == _collateral) {
                return balances[_account].amounts[i];
            }
        }
        return 0;
    }

    /** 
     * @notice Returns the total coll surplus bonus for one previous borrower. 
     */
    function getAmountsClaimable(address _account) 
        external 
        view 
        override 
        returns (address[] memory, uint256[] memory)
    {
        return (balances[_account].tokens, balances[_account].amounts);
    }

    /**
     * @notice View function for checking if that address has collateral which can be claimed.
     * @param _account address of previous assetPortfolio owner
     */
    function hasClaimableCollateral(address _account) external view override returns (bool) {
        return _collsIsNonZero(balances[_account]);
    }

    /**
     * @notice View function for checking if that returns redemption bonus for a particular account.
     * @param _account address of previous assetPortfolio owner
     */
    function getRedemptionBonus(address _account) external view override returns (uint256) {
        return redemptionBonus[_account];
    }

    /**
     * @notice Returns the collateralBalance for a given collateral
     *
     * @dev Returns the amount of a given collateral in state. Not necessarily this contract's actual balance.
     * @param _collateral address
     * @return uint256 collateral amount
     */
    function getCollateral(address _collateral) external view override returns (uint256) {
        uint256 collateralIndex = controller.getIndex(_collateral);
        return poolColl.amounts[collateralIndex];
    }

    /**
     * @notice Returns all collateral balances in state. Not necessarily this contract's actual balances.
     * @return array of address array of uint256
     */
    function getAllCollateral() external view override returns (address[] memory, uint256[] memory) {
        return (poolColl.tokens, poolColl.amounts);
    }

    /**
     * @notice totalRedemptionBonus
     * @dev this is the total amonut of PUST in this contract that is
     * claimable by users who have been redeemed against.
     * @return uint256
     */
    function getTotalRedemptionBonus() external view override returns (uint256) {
        return totalRedemptionBonus;
    }

    // --- 'require' functions ---

    /**
     * @notice check msg.sender and ensure it is assetPortfolio Manager Liquidations or assetPortfolioManagerRedemptions
     */
    function _requireCallerIsTMRorTML() internal view {
        if (msg.sender != assetPortfolioManagerLiquidationsAddress && msg.sender != assetPortfolioManagerRedemptionsAddress) {
            _revertWrongFuncCaller();
        }
    }

    /**
     * @notice check msg.sender and ensure it is assetPortfolio Manager Redemptions
     */
    function _requireCallerIsTMR() internal view {
        if (msg.sender != assetPortfolioManagerRedemptionsAddress) {
            _revertWrongFuncCaller();
        }
    }

    /**
     * @notice check msg.sender and ensure it is active Pool
     */
    function _requireCallerIsActivePool() internal view {
        if (msg.sender != activePoolAddress) {
            _revertWrongFuncCaller();
        }
    }
}