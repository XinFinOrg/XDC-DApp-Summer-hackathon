// SPDX-License-Identifier: UNLICENSED

pragma solidity 0.6.11;

import "./Interfaces/IERC20.sol";

contract PalmFinanceTreasury {

    address teamWallet;

    constructor() public {
        teamWallet = msg.sender;
    }

    modifier onlyTeam() {
        require(msg.sender == teamWallet);
        _;
    }

    function sendToken(IERC20 _token, address _to, uint _amount) external onlyTeam {
        _token.transfer(_to, _amount);
    }

    function updateTeamWallet(address _newTeamWallet) external onlyTeam {
        teamWallet = _newTeamWallet;
    }

    function getTeamWallet() external view returns (address) {
        return teamWallet;
    }

}