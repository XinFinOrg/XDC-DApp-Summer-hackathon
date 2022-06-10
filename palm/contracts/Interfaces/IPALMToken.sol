// SPDX-License-Identifier: MIT

pragma solidity 0.6.11;

import "./IERC20.sol";
import "./IERC2612.sol";

interface IPALMToken is IERC20, IERC2612 {

    function sendToSPALM(address _sender, uint256 _amount) external;

    function getDeploymentStartTime() external view returns (uint256);

}