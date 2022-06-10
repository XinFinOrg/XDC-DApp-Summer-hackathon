// SPDX-License-Identifier: UNLICENSED

pragma solidity 0.8.13;

interface IvePALM {
    function updateWhitelistedCallers(address _contractAddress, bool _isWhitelisted) external;
    function getvePALMOnRewarder(address _user, address _rewarder) external view returns (uint256);
    function getUserPalmOnRewarder(address _user, address _rewarder) external view returns (uint256);
    function getAccumulationRate() external view returns (uint256);
}