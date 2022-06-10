// SPDX-License-Identifier: UNLICENSED

pragma solidity 0.6.11;

import "./ICollateralReceiver.sol";

// Common interface for the Pools.
interface IPool is ICollateralReceiver {

    // --- Events ---

    event ETHBalanceUpdated(uint _newBalance);
    event PUSTBalanceUpdated(uint _newBalance);
    event EtherSent(address _to, uint _amount);
    event CollateralSent(address _collateral, address _to, uint _amount);

    // --- Functions ---

    function getVC() external view returns (uint totalVC);

    function getVCAndRVC() external view returns (uint totalVC, uint totalRVC);

    function getCollateral(address collateralAddress) external view returns (uint);

    function getAllCollateral() external view returns (address[] memory, uint256[] memory);

    function getPUSTDebt() external view returns (uint);

    function increasePUSTDebt(uint _amount) external;

    function decreasePUSTDebt(uint _amount) external;

}