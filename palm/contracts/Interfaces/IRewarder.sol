// SPDX-License-Identifier: MIT

pragma solidity 0.8.13;

interface IRewarder {
    function updateFactor(address _user, uint256 _newvePALMBalance) external;
}