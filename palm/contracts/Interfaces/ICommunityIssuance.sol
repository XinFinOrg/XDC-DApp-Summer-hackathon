// SPDX-License-Identifier: MIT

pragma solidity 0.6.11;

interface ICommunityIssuance {

    // --- Events ---

    event NewPalmIssued(uint256 _amountIssued);
    event TotalPALMIssuedUpdated(uint256 _totalPalmIssued);
    event NewRewardRate(uint256 _newRewardRate, uint256 _time);
    event RewardPaid(address _user, uint256 _reward);

    // --- Functions ---

    function setAddresses(address _palmTokenAddress, address _stabilityPoolAddress) external;

    function setRate(uint256 _newRewardRate) external;

    function issuePALM() external returns (uint256);

    function sendPALM(address _account, uint256 _PALMamount) external;

    function getRewardRate() external view returns (uint256);

}