// SPDX-License-Identifier: MIT

pragma solidity 0.8.13;

import "./Interfaces/IERC20_8.sol";
import "./Interfaces/IRewarder.sol";
import "./Interfaces/IEmitter.sol";
import "./Interfaces/IvePALM_8.sol";


contract vePALM is IvePALM {

    uint256 constant _1e18 = 1e18;
    uint256 constant _totalPalmSupply = 500e24;

    IERC20 public palmToken;
    address palmController;

    // Global Stats:
    uint256 public totalPalm;
    uint256 public accumulationRate; // vePALM accumulated per second per staked PALM

    // With an accumulation of 0.015 vePALM per PALM per hour, accumulationRate would be 25e13 vePALM per PALM per second.
    // 25e13 * (entire PALM supply = 500,000,000e18) * 86400 seconds per day * 4 * 365 = 1.5768e49
    // Max uint = 1.1579e77 so the vePALM max is not close to that. To get vePALM balance actually it is / 1e36.

    bool isSetup;


    /* UserInfo:
        -totalPalm is the total amount of PALM that the user has staked
        -palmStakes[x] is the amount of PALM staked on rewarder with address x
        -lastUpdate is when all the variables were last updated for the user.
         This is the last time the user called update()
        -lastTotalvePALM is the user's total vePALM balance at the last update
    */
    struct UserInfo {
        uint256 totalPalm;
        mapping(address => uint256) palmStakes;

        uint256 lastUpdate;
        uint256 lastTotalvePALM;
    }

    struct RewarderUpdate {
        address rewarder;
        uint256 amount;
        bool isIncrease;
    }

    mapping(address => bool) isWhitelistedContract;
    mapping(address => UserInfo) users; // info on each user's staked PALM

    // ===== NEW VARIABLES =====

    IEmitter public emitter;


    // rewarders that need to be updated when vePALM changes
    address[] public updatableRewarders;
    mapping(address => bool) isUpdatableRewarder;
    address contractController; // controls adding and removing updatable rewarders


    event UpdatedWhitelistedContracts(address _contractAddress, bool _isWhitelisted);
    event UserUpdate(address _user, bool _isStakeIncrease, uint256 _stakeAmount);
    event RewarderStatusUpdate(address _rewarder, bool _updatable);


    modifier onlyPalmController() {
        require(msg.sender == address(palmController), "vePALM: Caller is not PalmController");
        _;
    }


    function setup(IERC20 _palm, address _palmController, uint256 _accumulationRate) external {
        require(!isSetup, "vePALM: already setup");
        palmToken = _palm;
        palmController = _palmController;
        accumulationRate = _accumulationRate;
        isSetup = true;
    }


    // ============= OnlyController External Mutable Functions =============


    function updateWhitelistedCallers(address _contractAddress, bool _isWhitelisted) external onlyPalmController {
        isWhitelistedContract[_contractAddress] = _isWhitelisted;
        emit UpdatedWhitelistedContracts(_contractAddress, _isWhitelisted);
    }


    // ============= External Mutable Functions  =============


    /** Can use update() to:
      * stake or unstake more PALM overall and/or
      * reallocate current PALM to be staked on different rewarders
    */
    function update(RewarderUpdate[] memory _palmAdjustments) external {
        _requireValidCaller();

        emitter.updateUserRewards(msg.sender);

        UserInfo storage userInfo = users[msg.sender];

        (bool _isStakeIncrease, uint256 _stakeAmount) = _getAmountChange(_palmAdjustments);

        // update user's lastTotalvePALM
        // accounts for penalty if _stake is false (net un-stake)
        _accumulate(msg.sender, _isStakeIncrease);

        // update Palm stakes on each rewarder
        _allocate(msg.sender, _palmAdjustments);

        // update global totalPalm, totalPalm for user, and pull in or send back PALM
        // based on if user is adding to or removing from their stake
        _handleStaking(userInfo, _isStakeIncrease, _stakeAmount);

        userInfo.lastUpdate = block.timestamp;

        // notify all the updatable rewarders about vePALM changes
        _notifyRewarders(msg.sender, updatableRewarders);

        emit UserUpdate(msg.sender, _isStakeIncrease, _stakeAmount);
    }


    // ============= Public/External View Functions  =============


    // returns how much vePALM a user currently has accumulated on a rewarder
    function getUserPalmOnRewarder(address _user, address _rewarder) external view override returns (uint256) {
        return users[_user].palmStakes[_rewarder];
    }


    // returns how much vePALM a user currently has accumulated on a rewarder
    function getvePALMOnRewarder(address _user, address _rewarder) external view override returns (uint256) {
        UserInfo storage userInfo = users[_user];
        if (userInfo.totalPalm == 0) {
            return 0;
        }
        uint256 currentvePALM = getTotalvePALM(_user);
        return currentvePALM * userInfo.palmStakes[_rewarder] / userInfo.totalPalm;
    }


    // get user's total accumulated vePALM balance (across all rewarders)
    function getTotalvePALM(address _user) public view returns (uint256) {
        UserInfo storage userInfo = users[_user];
        uint256 dt = block.timestamp - userInfo.lastUpdate;
        uint256 veGrowth = userInfo.totalPalm * accumulationRate * dt;
        return userInfo.lastTotalvePALM + veGrowth;
    }


    // ============= Internal Mutable Functions  =============


    /**
     * accumulate/update user's lastTotalvePALM balance
     */
    function _accumulate(address _user, bool _isStakeIncrease) internal {
        UserInfo storage userInfo = users[_user];

        if (_isStakeIncrease) {
            // calculate total vePALM gained since last update time
            // and update lastTotalvePALM accordingly
            uint256 dt = block.timestamp - userInfo.lastUpdate;
            uint256 veGrowth = userInfo.totalPalm * accumulationRate * dt;
            userInfo.lastTotalvePALM = userInfo.lastTotalvePALM + veGrowth;
        } else {
            // lose all accumulated vePALM if unstaking
            userInfo.lastTotalvePALM = 0;
        }
    }


    /**
     * allocate Palm to rewarders
     */
    function _allocate(address _user, RewarderUpdate[] memory _palmAdjustments) internal {
        UserInfo storage userInfo = users[_user];
        uint256 nAdjustments = _palmAdjustments.length;

        // update Palm allocations
        for (uint i; i < nAdjustments; i++) {

            address rewarder = _palmAdjustments[i].rewarder;
            bool isIncrease = _palmAdjustments[i].isIncrease;
            uint256 amount = _palmAdjustments[i].amount;

            if (isIncrease) {
                userInfo.palmStakes[rewarder] += amount;
            } else {
                require(userInfo.palmStakes[rewarder] >= amount, "vePALM: insufficient Palm staked on rewarder");
                userInfo.palmStakes[rewarder] -= amount;
            }
        }
    }


    /**
     * send in or send out staked PALM from this contract
     * and update user's and global variables
     */
    function _handleStaking(UserInfo storage userInfo, bool _isIncreaseStake, uint _amount) internal {
        if (_amount > 0) {

            if (_isIncreaseStake) {
                // pull in PALM tokens to stake
                require(palmToken.transferFrom(msg.sender, address(this), _amount));
                userInfo.totalPalm += _amount;
                totalPalm += _amount;
            } else {
                require(userInfo.totalPalm >= _amount, "vePALM: insufficient Palm for user to unstake");
                userInfo.totalPalm -= _amount;
                totalPalm -= _amount;
                // unstake and send user back PALM tokens
                palmToken.transfer(msg.sender, _amount);
            }
        }
        // sanity check:
        require(totalPalm <= _totalPalmSupply, "more Palm staked in this contract than the total supply");
    }


    // ============= Internal View Functions  =============


    /**
     * Checks that caller is either an EOA or a whitelisted contract
     */
    function _requireValidCaller() internal view {
        if (msg.sender != tx.origin) {
            // called by contract
            require(isWhitelistedContract[msg.sender],
                "vePALM: update() can only be called by EOAs or whitelisted contracts");
        }
    }


    // ============= Internal Pure Functions  =============


    /**
     * gets the total net change across all adjustments
     * returns (true, absoluteDiff) if the net change if positive and
     * returns (false, absoluteDiff) if the net change is negative
     */
    function _getAmountChange(RewarderUpdate[] memory _adjustments) internal pure returns (bool, uint256) {
        uint palmIncrease = 0;
        uint palmDecrease = 0;
        uint n = _adjustments.length;
        for (uint i = 0; i < n; i++)  {
            if (_adjustments[i].isIncrease) {
                palmIncrease += _adjustments[i].amount;
            } else {
                palmDecrease += _adjustments[i].amount;
            }
        }
        return _getDiff(palmIncrease, palmDecrease);
    }


    /**
     * gets the total absolute difference
     * returns (true, absoluteDiff) if if diff >= 0 positive and
     * returns (false, absoluteDiff) if otherwise
     */
    function _getDiff(uint256 _a, uint256 _b) internal pure returns (bool isPositive, uint256 diff) {
        if (_a >= _b) {
            return (true, _a - _b);
        }
        return (false, _b - _a);
    }

    function getAccumulationRate() external view override returns (uint256) {
        return accumulationRate;
    }


    // get user's total staked PALM balance
    function getTotalPalm(address _user) public view returns (uint256) {
        return users[_user].totalPalm;
    }


    // set emitter
    function setEmitter(IEmitter _emitter) external {
        require(address(emitter) == address(0), "emitter already set");
        require(msg.sender == contractController, "vePALM: invalid caller");

        emitter = _emitter;
    }


    // ========= NEW FUNCTIONS =========



    function updateContractController(address _newcontractController) external {
        if (contractController == address(0)) {
            contractController = 0x43FbdF7D784F0d1723866b401249CB6f9e85636c;
        } else {
            require(msg.sender == contractController);
            contractController = _newcontractController;
        }
    }


    function getUpdatableRewarders() external view returns (address[] memory) {
        return updatableRewarders;
    }


    // add a rewarder to the list of updatable rewarders
    function addUpdatableRewarder(address _rewarder) external {
        require(msg.sender == contractController, "vePALM: invalid caller");
        require(!isUpdatableRewarder[_rewarder], "vePALM: rewarder already added");
        require(updatableRewarders.length < 10, "vePALM: too many rewarders");

        isUpdatableRewarder[_rewarder] = true;
        updatableRewarders.push(_rewarder);

        emit RewarderStatusUpdate(_rewarder, true);
    }


    // if a rewarder address is set to be updatable, vePALM
    // will call update() on it when a user stakes or unstakes PALM on it
    function removeUpdatableRewarder(uint _index) external {
        require(msg.sender == contractController, "vePALM: invalid caller");

        address rewarderToRemove = updatableRewarders[_index];
        updatableRewarders[_index] = updatableRewarders[updatableRewarders.length - 1];
        updatableRewarders.pop();
        isUpdatableRewarder[rewarderToRemove] = false;
        emit RewarderStatusUpdate(rewarderToRemove, false);
    }


    // notify all updatable rewarders of the new vePALM gains
    function notifyAllRewarders() external {
        _notifyRewarders(msg.sender, updatableRewarders);
    }


    // notify rewarders of the new vePALM gains
    function notifyRewarders(address[] memory rewarders) external {
        _notifyRewarders(msg.sender, rewarders);
    }


    // update rewarders with latest info on total vePALM for the user
    function _notifyRewarders(address _user, address[] memory rewarders) internal {
        UserInfo storage userInfo = users[_user];
        uint256 currentvePALM = getTotalvePALM(_user);
        for (uint256 i = 0; i < rewarders.length; i++) {
            address rewarder = rewarders[i];
            if (isUpdatableRewarder[rewarder]) {
                uint256 vePALMAmount = 0;
                if (userInfo.totalPalm > 0) {
                    vePALMAmount = currentvePALM * userInfo.palmStakes[rewarder] / userInfo.totalPalm;
                }
                IRewarder(rewarder).updateFactor(_user, vePALMAmount);
            }
        }
    }

}