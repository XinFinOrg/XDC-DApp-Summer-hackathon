// SPDX-License-Identifier: UNLICENSED

pragma solidity 0.6.11;

import "../Dependencies/PalmCustomBase.sol";
import "./ICollateralReceiver.sol";


interface ICollSurplusPool is ICollateralReceiver {

    // --- Events ---

    event CollBalanceUpdated(address indexed _account);
    event CollateralSent(address _to);

    // --- Contract setters ---

    function setAddresses(
        address _borrowerOperationsAddress,
        address _assetPortfolioManagerLiquidationsAddress,
        address _assetPortfolioManagerRedemptionsAddress,
        address _activePoolAddress,
        address _controllerAddress,
        address _pustTokenAddress
    ) external;

    function getCollVC() external view returns (uint);

    function getTotalRedemptionBonus() external view returns (uint256);

    function getAmountClaimable(address _account, address _collateral) external view returns (uint);

    function getAmountsClaimable(address _account)
    external
    view
    returns (address[] memory, uint256[] memory);

    function hasClaimableCollateral(address _account) external view returns (bool);

    function getRedemptionBonus(address _account) external view returns (uint256);

    function getCollateral(address _collateral) external view returns (uint);

    function getAllCollateral() external view returns (address[] memory, uint256[] memory);

    function accountSurplus(address _account, address[] memory _tokens, uint[] memory _amounts) external;

    function accountRedemptionBonus(address _account, uint256 _amount) external;

    function claimCollateral() external;

    function addCollateralType(address _collateral) external;
}