// SPDX-License-Identifier: UNLICENSED

pragma solidity 0.6.11;

import "./Dependencies/PalmMath.sol";
import "./Dependencies/SafeMath.sol";
import "./Dependencies/OwnableUpgradeable.sol";
import "./Dependencies/SafeERC20.sol";

/**
 * @notice Contains functions for tracking user balances of staked tokens
 * and staking and un-staking LP tokens
 */

contract LPTokenWrapper {
    using SafeMath for uint256;
    using SafeERC20 for IERC20;

    IERC20 public lpToken;

    uint256 private _totalSupply;
    mapping(address => uint256) private _balances;

    event Staked(address indexed user, uint256 amount);
    event Withdrawn(address indexed user, uint256 amount);

    /**
     * @dev This empty reserved space is put in place to allow future versions to add new
     * variables without shifting down storage in the inheritance chain.
     * See https://docs.openzeppelin.com/contracts/4.x/upgradeable#storage_gaps
     */
    uint256[47] private __gap;

    function totalSupply() public view returns (uint256) {
        return _totalSupply;
    }

    function balanceOf(address account) public view returns (uint256) {
        return _balances[account];
    }

    function _stakeTokens(uint256 amount) internal {
        require(amount > 0, "Cannot stake 0");

        _totalSupply = _totalSupply.add(amount);
        _balances[msg.sender] = _balances[msg.sender].add(amount);
        lpToken.safeTransferFrom(msg.sender, address(this), amount);

        emit Staked(msg.sender, amount);
    }

    function _withdrawTokens(uint256 amount) internal {
        require(amount > 0, "Cannot withdraw 0");

        _totalSupply = _totalSupply.sub(amount);
        _balances[msg.sender] = _balances[msg.sender].sub(amount);
        lpToken.safeTransfer(msg.sender, amount);

        emit Withdrawn(msg.sender, amount);
    }
}

/*
 * Staking contract to reward users with farming rewards
 * for providing liquidity in Palm-related LP tokens.
 * Palm tokens are allocated to this contract and a reward rate
 * is set by the team in PALM / sec. These PALM are distributed
 * on a pro-rata basis to all stakers in this contract. As a staker,
 * your rewards are equivalent to:
 * pct_share_of_staked_assets * reward_rate * time
*/
contract Farm is OwnableUpgradeable, LPTokenWrapper {
    IERC20 public palmToken;

    uint256 public periodFinish;
    uint256 public rewardRate;
    uint256 public lastUpdateTime;
    uint256 public rewardPerTokenStored;
    mapping(address => uint256) public userRewardPerTokenPaid;
    mapping(address => uint256) public rewards;

    event RewardAdded(uint256 reward);
    event RewardPaid(address indexed user, uint256 reward);


    modifier updateReward(address account) {
        rewardPerTokenStored = rewardPerToken();
        lastUpdateTime = lastTimeRewardApplicable();
        if (account != address(0)) {
            rewards[account] = earned(account);
            userRewardPerTokenPaid[account] = rewardPerTokenStored;
        }
        _;
    }

    bool private addressSet;
    function initialize(IERC20 _LP, IERC20 _PALM) external {
        require(addressSet == false, "Addresses already set");
        addressSet = true;
        _transferOwnership(msg.sender);
        lpToken = _LP;
        palmToken = _PALM;
    }


    // ========== EXTERNAL FUNCTIONS ==========

    // stake token to start farming
    function stake(uint256 amount) external updateReward(msg.sender) {
        _stakeTokens(amount);
    }

    // withdraw staked tokens but don't collect accumulated farming rewards
    function withdraw(uint256 amount) public updateReward(msg.sender) {
        _withdrawTokens(amount);
    }


    // withdraw all staked tokens and also collect accumulated farming reward
    function exit() external {
        withdraw(balanceOf(msg.sender));
        getReward();
    }


    // collect pending farming reward
    function getReward() public updateReward(msg.sender) {
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
    function notifyRewardAmount(uint256 _reward, uint256 _duration) external onlyOwner updateReward(address(0)) {
        require(
            (palmToken.balanceOf(address(this)) >= _reward),
            "Insufficient PALM in contract");

        rewardRate = _reward.div(_duration);
        lastUpdateTime = block.timestamp;
        periodFinish = block.timestamp.add(_duration);

        emit RewardAdded(_reward);
    }


    //  ========== VIEW FUNCTIONS ==========
    function lastTimeRewardApplicable() public view returns (uint256) {
        return PalmMath._min(block.timestamp, periodFinish);
    }

    function rewardPerToken() public view returns (uint256) {
        if (totalSupply() == 0) {
            return rewardPerTokenStored;
        }

        return rewardPerTokenStored.add(
            lastTimeRewardApplicable()
            .sub(lastUpdateTime)
            .mul(rewardRate)
            .mul(1e18)
            .div(totalSupply())
        );
    }

    function earned(address account) public view returns (uint256) {
        return
        balanceOf(account)
        .mul(rewardPerToken().sub(userRewardPerTokenPaid[account]))
        .div(1e18)
        .add(rewards[account]);
    }

    // returns how much Palm you would earn depositing _amount for _time
    function rewardToEarn(uint _amount, uint _time) public view returns (uint256) {
        if (totalSupply() == 0) {
            return rewardRate.mul(_time);
        }
        return rewardRate.mul(_time).mul(_amount).div(totalSupply().add(_amount));
    }
}