This project is built by [Dhruv Agarwal](https://github.com/Dhruv-2003/) & [Kushagra Sarathe](https://github.com/kushagrasarathe/)

We have built a Collection of 3 DEFI contracts namely Staking , Lending Pool and Vault , which are very necessary first step to build a DEFI protocol . The contracts are well tested on other blockchains and are completely working from end to end seamlessly .

## How we built it
We used Solidity lang to build the Smart Contracts , the contracts are tested for all the cases .
The frontend website is built with Next.js , CSS /HTML and Javascript to showcase our contracts to the users and devs building on XDC . 
New contracts can be added

## Staking Contract

--> Rewards user for staking their tokens in the contract

- User can withdraw and deposit at an point of time
- Tokens Earned can be withdrawed any time
- Rewards are calculated with reward rate and time period staked for
- The balance and reward earned can be checked at any point of time

## Lending Pool Contract

--> Create a pool contract that accepts deposit from lenders and borrow money to the borrowers

- Lenders can lend any amount of money and earn some interest for it.
- User or borrower can borrow some amount of tokens (limited) , and pay back with interest for some time period.
- Interest is calculated according the interest rate and borrowing time peroid
- Lender can withdraw the amount later with extra interest earning
- Other functions can be called to determine the balance at any point of time , and the rewards earned

## Vault Contract

--> Sharing of Yield For the no. of shares owned

- user can deposit their money
- Some shares are minted according to the value deposited
- Vault generate some yield by a puropose and the value of share increases
- user can withdraw the amount by burning those share at any point of time .
