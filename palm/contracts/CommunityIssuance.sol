// SPDX-License-Identifier: MIT

pragma solidity 0.6.11;

import "./Interfaces/IPALMToken.sol";
import "./Interfaces/ICommunityIssuance.sol";
import "./Dependencies/PalmMath.sol";
import "./Dependencies/OwnableUpgradeable.sol";
import "./Dependencies/CheckContract.sol";
import "./Dependencies/SafeMath.sol";


/*
 * The treasury needs to keep this contract supplied with PALM tokens.
 * The amount of issued PALM is equal to rewardRate * deltaT (time since emission)
 * Palm emissions are split on a pro-rata basis across stability pool depositors.
 * This tracking is done with the "G" parameter in the stability pool
 */
contract CommunityIssuance is ICommunityIssuance, OwnableUpgradeable, CheckContract {
    using SafeMath for uint256;

    // --- Data ---

    string public constant NAME = "CommunityIssuance";

    IPALMToken public palmToken;

    address public stabilityPoolAddress;

    uint256 rewardRate;
    uint256 public newRate;
    uint256 public newRateUpdateTime;
    bool public rateUpdate;

    uint256 public lastUpdateTime;
    uint256 public unclaimedIssuedPalm;
    uint256 public totalPalmIssued;

    bool internal addressesSet;


    // --- Functions ---


    function setAddresses(
        address _palmTokenAddress,
        address _stabilityPoolAddress
    ) external override {
        require(!addressesSet, "Addresses Already Set");
        _transferOwnership(msg.sender);
        lastUpdateTime = block.timestamp;

        palmToken = IPALMToken(_palmTokenAddress);
        stabilityPoolAddress = _stabilityPoolAddress;

        addressesSet = true;
    }


    /**
     * @notice rewardRate will be set to newRate
     * @param _newRewardRate New PALM / second emission rate
     */
    function setRate(uint256 _newRewardRate) external override onlyOwner {
        newRate = _newRewardRate;
        newRateUpdateTime = block.timestamp;
        rateUpdate = true;

        emit NewRewardRate(_newRewardRate, newRateUpdateTime);
    }


    /**
     * @notice Called by SP whenever an action takes place.
     * New PALM tokens are emitted and the amount emitted
     * is returned
    */
    function issuePALM() external override returns (uint256) {
        _requireCallerIsSP();

        uint256 issuance = _getNewIssuance();

        unclaimedIssuedPalm = unclaimedIssuedPalm.add(issuance);
        totalPalmIssued = totalPalmIssued.add(issuance);

        lastUpdateTime = block.timestamp;

        emit NewPalmIssued(issuance);
        emit TotalPALMIssuedUpdated(totalPalmIssued);

        return issuance;
    }


    /**
     * @notice Returns the amount of Palm to emit given the reward rate
     * and how long it has been since the last emission.
     * Returns the minimum between the amount of Palm tokens
     * in this contract that have not been allocated,
     * and the amount of PALM that should be issued at the
     * given reward rate.
     */
    function _getNewIssuance() internal returns (uint256) {
        uint256 newIssuance;
        if (rateUpdate) {
            // New Issuance will be split between new rate and old at that update time. 
            newIssuance = (block.timestamp.sub(newRateUpdateTime)).mul(newRate);
            newIssuance = newIssuance.add((newRateUpdateTime.sub(lastUpdateTime)).mul(rewardRate));
            rewardRate = newRate;
            rateUpdate = false;
        } else {
            newIssuance = (block.timestamp.sub(lastUpdateTime)).mul(rewardRate);
        }

        uint256 availableToEmit;
        if (palmToken.balanceOf(address(this)) > unclaimedIssuedPalm) {
            availableToEmit = palmToken.balanceOf(address(this)).sub(unclaimedIssuedPalm);
        }

        return PalmMath._min(newIssuance, availableToEmit);
    }


    /**
     * @notice Send PALM to an address-only callable by SP
     */
    function sendPALM(address _account, uint256 _PALMamount) external override {
        _requireCallerIsSP();

        unclaimedIssuedPalm = unclaimedIssuedPalm.sub(_PALMamount);
        require(palmToken.transfer(_account, _PALMamount));

        emit RewardPaid(_account, _PALMamount);
    }


    /**
     * @notice Returns current reward rate
     */
    function getRewardRate() external view override returns (uint256) {
        return rewardRate;
    }


    /**
     * @notice Checks that caller is the Stability Pool, reverts if not
     */
    function _requireCallerIsSP() internal view {
        require(msg.sender == stabilityPoolAddress, "CommunityIssuance: caller is not SP");
    }

}