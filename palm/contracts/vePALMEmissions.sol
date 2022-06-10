pragma solidity 0.6.11;

import "./Dependencies/PalmMath.sol";
import "./Dependencies/SafeMath.sol";
import "./Dependencies/OwnableUpgradeable.sol";
import "./Dependencies/SafeERC20.sol";

interface IvePALM {
    function totalPalm() external view returns (uint256);
    function getTotalPalm(address _user) external view returns (uint256);
}


contract vePALMEmissions is OwnableUpgradeable {
    using SafeMath for uint256;
    using SafeERC20 for IERC20;

    IERC20 public palmToken;
    IvePALM public vePALM;

    uint256 public periodFinish;
    uint256 public rewardRate;
    uint256 public lastUpdateTime;
    uint256 public rewardPerTokenStored;
    mapping(address => uint256) public userRewardPerTokenPaid;
    mapping(address => uint256) public rewards;

    event RewardAdded(uint256 reward, uint256 duration, uint256 periodFinish);
    event RewardPaid(address indexed user, uint256 reward);


    modifier onlyvePALM() {
        require(msg.sender == address(vePALM));
        _;
    }


    // ========== EXTERNAL FUNCTIONS ==========


    bool private addressSet;
    function initialize(IERC20 _PALM, IvePALM _vePALM) external {
        require(!addressSet, "Addresses already set");

        addressSet = true;
        _transferOwnership(msg.sender);
        palmToken = _PALM;
        vePALM = _vePALM;
    }


    // update user rewards at the time of staking or unstakeing
    function updateUserRewards(address _user) external onlyvePALM {
        _updateReward(_user);
    }


    // collect pending farming reward
    function getReward() external {
        _updateReward(msg.sender);
        uint256 reward = earned(msg.sender);
        if (reward > 0) {
            rewards[msg.sender] = 0;
            palmToken.safeTransfer(msg.sender, reward);
            emit RewardPaid(msg.sender, reward);
        }
    }


    /* Used to update reward rate by the owner
     * Owner can only update reward to a reward such that
     * there is enough Palm in the contract to emit
     * _reward Palm tokens across _duration
    */
    function notifyRewardAmount(uint256 _reward, uint256 _duration) external onlyOwner {
        _updateReward(address(0));

        rewardRate = _reward.div(_duration);
        lastUpdateTime = block.timestamp;
        periodFinish = block.timestamp.add(_duration);

        emit RewardAdded(_reward, _duration, periodFinish);
    }


    //  ========== INTERNAL FUNCTIONS ==========

    function _updateReward(address account) internal {
        rewardPerTokenStored = rewardPerToken();
        lastUpdateTime = lastTimeRewardApplicable();
        if (account != address(0)) {
            rewards[account] = earned(account);
            userRewardPerTokenPaid[account] = rewardPerTokenStored;
        }
    }


    //  ========== PUBLIC VIEW FUNCTIONS ==========


    function lastTimeRewardApplicable() public view returns (uint256) {
        return PalmMath._min(block.timestamp, periodFinish);
    }

    function rewardPerToken() public view returns (uint256) {
        uint256 totalPalmStaked = vePALM.totalPalm();
        if (totalPalmStaked == 0) {
            return rewardPerTokenStored;
        }

        return rewardPerTokenStored.add(
            lastTimeRewardApplicable()
            .sub(lastUpdateTime)
            .mul(rewardRate)
            .mul(1e18)
            .div(totalPalmStaked)
        );
    }

    // earned Palm Emissions
    function earned(address account) public view returns (uint256) {
        return
        vePALM.getTotalPalm(account)
        .mul(rewardPerToken().sub(userRewardPerTokenPaid[account]))
        .div(1e18)
        .add(rewards[account]);
    }

    // returns how much Palm you would earn depositing _amount for _time
    function rewardToEarn(uint _amount, uint _time) public view returns (uint256) {
        if (vePALM.totalPalm() == 0) {
            return rewardRate.mul(_time);
        }
        return rewardRate.mul(_time).mul(_amount).div(vePALM.totalPalm().add(_amount));
    }

}