// SPDX-License-Identifier: UNLICENSED

pragma solidity 0.6.11;

interface IvePALM {
    function updateWhitelistedCallers(address _contractAddress, bool _isWhitelisted) external;
}