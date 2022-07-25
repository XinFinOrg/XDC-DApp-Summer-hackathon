import Head from "next/head";
import Image from "next/image";
import styles from "../styles/Home.module.css";
import logo from "../src/assets/logo.png";
import xdc from "../src/assets/xdc.png";
import title from "../src/assets/title.png";
import Link from "next/link";
import { useEffect } from "react";

import Prism from "prismjs";

import "prismjs/themes/prism-okaidia.css";
import "prismjs/components/prism-jsx.js";
import "prismjs/plugins/line-numbers/prism-line-numbers.js";
import "prismjs/plugins/line-numbers/prism-line-numbers.css";

export default function Home() {
  useEffect(() => {
    Prism.highlightAll();
  }, []);

  const lending = `// SPDX-License-Identifier: MIT
  pragma solidity ^0.8.13;
  
  // - Create a pool contract that accepts deposit from lenders , who earn interest on lending
  // - User  or borrower can borrow some amount of tokens (limited) , and pay back with some interest for some time period.
  // - lender can withdraw the amount later with some interest
  
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
  
  contract LiquidityPool {
      /// intialize token
      IERC20 token;
      uint256 totalSupply;
  
      /// the rate earned by the lender per second
      uint256 lendRate = 100;
      /// the rate paid by the borrower per second
      uint256 borrowRate = 130;
  
      uint256 peroidBorrowed;
  
      ///  struct with amount and date of borrowing or lending
      struct amount {
          uint256 amount;
          uint256 start;
      }
  
      // mapping to check if the address has lended any amount
      mapping(address => amount) lendAmount;
      // mapping for the interest earned by the lender ;
      mapping(address => uint256) earnedInterest;
  
      // arrays to store the info about lender & borrowers
      mapping(address => bool) lenders;
      mapping(address => bool) borrowers;
  
      // mapping to check if the address has borrowed any amount
      mapping(address => amount) borrowAmount;
      // mapping for the interest to be paid by the borrower ;
      mapping(address => uint256) payInterest;
  
      /// events
  
      /// making the contract payable and adding the tokens in starting to the pool
  
      constructor(address _tokenAddress, uint256 _amount) payable {
          token = IERC20(_tokenAddress);
          token.transferFrom(msg.sender, address(this), _amount);
      }
  
      /// @dev - to lend the amount by  , add liquidity
      /// @param _amount - the amount to be lender
      function lend(uint256 _amount) external {
          require(_amount != 0, " amount can not be 0");
  
          /// transferring the tokens to the pool contract
          token.transferFrom(msg.sender, address(this), _amount);
  
          /// adding in lending and lenders array for record
          lendAmount[msg.sender].amount = _amount;
          lendAmount[msg.sender].start = block.timestamp;
          lenders[msg.sender] = true;
  
          /// updating total supply
          totalSupply += _amount;
      }
  
      /// @dev - to borrow token
      /// @param _amount - amount to be withdraw
      function borrow(uint256 _amount) external {
          require(_amount != 0, " amount can not be 0");
  
          /// updating records first
          borrowAmount[msg.sender].amount = _amount;
          borrowAmount[msg.sender].start = block.timestamp;
          totalSupply -= _amount;
  
          /// then transfer
          token.transfer(msg.sender, _amount);
          borrowers[msg.sender] = true;
      }
  
      /// @dev  - repay the whole loan
      function repay() external {
          /// check borrower
          require(borrowers[msg.sender], "not a borrower");
  
          /// total amount to be repaid with intrest
          amount storage amount_ = borrowAmount[msg.sender];
          uint256 _amount = (amount_.amount +
              (amount_.amount *
                  ((block.timestamp - amount_.start) * borrowRate * 1e18)) /
              totalSupply);
  
          require(_amount != 0, " amount can not be 0");
  
          /// transferring the tokens
          token.transferFrom(msg.sender, address(this), _amount);
  
          /// updating records and deleting the record of borrowing
          delete borrowAmount[msg.sender];
          borrowers[msg.sender] = false;
  
          /// update total supply at the end
          totalSupply += _amount;
      }
  
      /// @dev  - to withdraw the amount for the lender
      function withdraw() external {
          /// checking if the caller is a lender or not
          require(lenders[msg.sender], "you are not a lender");
  
          // calculating the total amount along with the interest
          amount storage amount_ = lendAmount[msg.sender];
          uint256 _amount = (amount_.amount +
              (amount_.amount *
                  ((block.timestamp - amount_.start) * lendRate * 1e18)) /
              totalSupply);
  
          require(_amount != 0, " amount can not be 0");
  
          /// deleting the records and updating the list
          delete lendAmount[msg.sender];
          lenders[msg.sender] = false;
  
          /// updating total supply earlier before transfering token , so as to be safe from attacks
          totalSupply -= _amount;
  
          /// transferring the tokens in the end
          token.transfer(msg.sender, _amount);
      `;

  const staking = `// SPDX-License-Identifier: MIT
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
  }`;

  const vault = `// SPDX-License-Identifier: MIT
  pragma solidity ^0.8.13;
  
  /// user can deposit his money
  /// it wll mint some share
  /// vault generate some yield
  /// user can withdraw the shares with the increased amount
  
  contract Vault {
      IERC20 public immutable token;
      uint256 public totalSupply;
  
      mapping(address => uint256) public balanceOf;
  
      constructor(address _token) {
          token = IERC20(_token);
      }
  
      function mint(address _to, uint256 shares) private {
          totalSupply += shares;
          balanceOf[_to] += shares;
      }
  
      function burn(address _from, uint256 shares) private {
          totalSupply -= shares;
          balanceOf[_from] -= shares;
      }
  
      function deposit(uint256 _amount) external {
          uint256 shares;
          if (totalSupply == 0) {
              shares = _amount;
          } else {
              shares = (_amount * totalSupply) / token.balanceOf(address(this));
          }
  
          mint(msg.sender, shares);
          token.transferFrom(msg.sender, address(this), _amount);
      }
  
      function withdraw(uint256 _shares) external {
          uint256 amount = (_shares * token.balanceOf(address(this))) /
              totalSupply;
          burn(msg.sender, _shares);
          token.transfer(msg.sender, amount);
      }
  }
  
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
  
      event Transfer(address indexed from, address indexed to, uint256 amount);
      event Approval(
          address indexed owner,
          address indexed spender,
          uint256 amount
      );
  }`;

  return (
    <div className={styles.container}>
      <Head>
        <title>Create Next App</title>
        <meta name="description" content="Generated by create next app" />
        <link rel="icon" href="/favicon.ico" />
      </Head>

      <main className={styles.main}>
        <div>
          <Image src={title} />
        </div>
        <div className={styles.xdc}>
          <Image src={xdc} />
        </div>
        <p className={styles.about}>
          We have built a collection of DeFi Smart-Contracts for XDC chain
        </p>

        <h1 className={styles.contract}>Contracts</h1>
        <hr className={styles.hr} />
        <p id="lending" className={styles.contract}>
          Lending Contract
        </p>

        <span className={styles.features}>
          <ul>
            <li>Create a pool contract that accepts deposit from lenders and borrow money to the borrowers</li>
            <li>Lenders can lend any amount of money and earn some interest for it.</li>
            <li>User or borrower can borrow some amount of tokens (limited) , and pay back with interest for some time period.</li>
            <li>Interest is calculated according the interest rate and borrowing time peroid</li>
            <li>Lender can withdraw the amount later with extra interest earning</li>
            <li>Other functions can be called to determine the balance at any point of time , and the rewards earned</li>
          </ul>
        </span>
        <button className={styles.button}>
          <a
            target="_blank"
            rel="noopener noreferrer"
            href="https://github.com/Dhruv-2003/DefiforXDC/blob/main/contracts/LendingPool.sol"
            className={styles.navlink}
          >
            View on GitHub ↗
          </a>
        </button>
        <span className={styles.code}>
          <pre className="line-numbers">
            <code className="language-jsx">{lending}</code>
          </pre>
        </span>

        <p id="staking" className={styles.contract}>
          Staking Contract
        </p>
         <span className={styles.features}>
          <ul>
            <li> Sharing of Yield For the no. of shares owned</li>
            <li>User can deposit their money</li>
            <li>Some shares are minted according to the value deposited</li>
            <li>Vault generate some yield by a puropose and the value of share increases</li>
            <li>user can withdraw the amount by burning those share at any point of time .</li>
          </ul>
        </span>
        <button className={styles.button}>
          <a
            target="_blank"
            rel="noopener noreferrer"
            href="https://github.com/Dhruv-2003/DefiforXDC/blob/main/contracts/Staking.sol"
            className={styles.navlink}
          >
            View on GitHub ↗
          </a>
        </button>

        <span className={styles.code}>
          <pre className="line-numbers">
            <code className="language-jsx">{staking}</code>
          </pre>
        </span>

        <p id="vault" className={styles.contract}>
          Vault Contract
        </p>
         <span className={styles.features}>
          <ul>
            <li>Rewards user for staking their tokens in the contract</li>
            <li>User can withdraw and deposit at an point of time</li>
            <li>Tokens Earned can be withdrawed any time</li>
            <li>
              Rewards are calculated with reward rate and time period staked for
            </li>
            <li>
              The balance and reward earned can be checked at any point of time
            </li>
          </ul>
        </span>
        <button className={styles.button}>
          <a
            target="_blank"
            rel="noopener noreferrer"
            href="https://github.com/Dhruv-2003/DefiforXDC/blob/main/contracts/Vault.sol"
            className={styles.navlink}
          >
            View on GitHub ↗
          </a>
        </button>
        <span className={styles.code}>
          <pre className="line-numbers">
            <code className="language-jsx">{vault}</code>
          </pre>
        </span>
      </main>
    </div>
  );
}
