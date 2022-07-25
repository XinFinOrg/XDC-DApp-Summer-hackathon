// SPDX-License-Identifier: MIT
pragma solidity ^0.8.13;

// interface of the tokens to be awarded as rewards for the user
interface IERC20 {
    function totalSupply() external view returns (uint256);

    function balanceOf(address account) external view returns (uint256);

    function transfer(address recipient, uint256 amount)
        external
        returns (bool);

    function allowance(address owner, address spender)
        external
        view
        returns (uint256);

    function approve(address spender, uint256 amount) external returns (bool);

    function transferFrom(
        address sender,
        address recipient,
        uint256 amount
    ) external returns (bool);

    event Transfer(address indexed from, address indexed to, uint256 value);
    event Approval(
        address indexed owner,
        address indexed spender,
        uint256 value
    );
}

// - Rewards user for staking their tokens
// - User can withdraw and deposit
// - Earns token while withdrawing

/// rewards are calculated with reward rate and time period staked for

contract Staking {
    // tokens intialized
    IERC20 public rewardsToken;
    IERC20 public stakingToken;

    // 100 wei per second , calculated for per anum
    uint256 public rewardRate = 100;
    uint256 public lastUpdateTime;
    uint256 public rewardPerTokenStored;

    // mapping for the rewards for an address
    mapping(address => uint256) public rewards;

    // mapping for the rewards per token paid
    mapping(address => uint256) public rewardsPerTokenPaid;

    // mapping for staked amount by an address
    mapping(address => uint256) staked;

    // total supply for the staked token in the contract
    uint256 public _totalSupply;

    constructor(address _stakingToken, address _rewardsToken) {
        stakingToken = IERC20(_stakingToken);
        rewardsToken = IERC20(_rewardsToken);
    }

    /// @dev - to calculate the amount of rewards per token staked at current instance
    /// @return uint - the amount of rewardspertoken
    function rewardPerToken() public view returns (uint256) {
        if (_totalSupply == 0) {
            return rewardPerTokenStored;
        }
        return
            rewardPerTokenStored +
            (((block.timestamp - lastUpdateTime) * rewardRate * 1e18) /
                _totalSupply);
    }

    /// @dev - to calculate the earned rewards for the token staked
    /// @param account - for which it is to be calculated
    /// @return uint -  amount of earned rewards
    function earned(address account) public view returns (uint256) {
        /// amount will be the earned amount according to the staked + the rewards the user earned earlier
        return
            ((staked[account] *
                (rewardPerToken() - rewardsPerTokenPaid[account])) / 1e18) +
            rewards[account];
    }

    /// modifier that will calculate the amount every time the user calls , and update them in the rewards array
    modifier updateReward(address account) {
        rewardPerTokenStored = rewardPerToken();
        lastUpdateTime = block.timestamp;

        /// updating the total rewards owned by the user
        rewards[account] = earned(account);
        /// updatig per token reward amount in the mapping
        rewardsPerTokenPaid[account] = rewardPerTokenStored;
        _;
    }

    /// @dev to stake some amount of token
    /// @param _amount -  amount to be staked
    function stake(uint256 _amount) external updateReward(msg.sender) {
        _totalSupply += _amount;
        staked[msg.sender] += _amount;
        stakingToken.transferFrom(msg.sender, address(this), _amount);
    }

    /// @dev to withdraw the staked amount
    /// @param _amount - amount to be withdrawn
    function withdraw(uint256 _amount) external updateReward(msg.sender) {
        _totalSupply -= _amount;
        staked[msg.sender] -= _amount;
        stakingToken.transfer(msg.sender, _amount);
    }

    /// @dev to withdraw the reward token
    function getReward() external updateReward(msg.sender) {
        uint256 reward = rewards[msg.sender];
        rewards[msg.sender] = 0;
        rewardsToken.transfer(msg.sender, reward);
    }
}
