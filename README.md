# PayPlic - A Crypto to Crypto Gatway
## By PayPlic Team for XDC dAPP Summer Hackathon - 2022

PayPlic enables a cross-chain gateway, from Ethereum Blockchain to XDC Network Blockchain. XDC EcoSystem can create a bridge that allows users from other blockchains can purchase XDC Token easily

## Inspiration
Crypto to Crypto Gateway is a bridge between two blockchains. For an instance, a USDT holder in Ethereum can purchase tokens from the XDC Ecosystem.

## What it does
- Crypto to Crypto Gateway is a bridge between two blockchains. For an instance, a USDT holder in Ethereum can purchase tokens from the XDC Ecosystem.
- Token founders, can sign-up with their ERC20 wallet and get an API Key. 
- Using this API Key, they can enable crypto payment in USDT / USDC / DAI and they can decide which token to pair with
- Deposit their funds in the XRC20 Gateway contract
- User should be able to purchase using the stablecoins and get the relevant XDC Token 

## How we built it
- ERC20 Gateway Solidity Contract
- XRC20 Gateway Solidity Contract
- Plugin Decentralized Oracle for bridge
- External Adapter to pull the pricing of XDC EcoSystem from the external world

## Challenges we ran into
- 
## Accomplishments that we're proud of
- Bridge has been enabled between the blockchains.
- This can be taken to any EVM-compatible blockchains.

## What we learned
- Integration of two different blockchains.
- How to bridge using the event mechanism

## What's next for Crypto2Crypto Gateway - PayPlic
- Building a nice user interface
- Create a widget, so anyone can integrate this widget into their website

## How to Run
## Installation

[Node.js](https://nodejs.org/) v10+ to run.
Install the dependencies and devDependencies and start the server.

```sh
git clone https://github.com/LogeswaranA/PayPlic.git
cd PayPlic
```
# Install necessary packages for app folder and start backend api
```
cd app
yarn install
yarn start
```
Result should be something like below
```
Compiled successfully!
You can now view client in the browser.
  Local:            http://localhost:3000
  On Your Network:  http://192.168.1.36:3000
Note that the development build is not optimized.
To create a production build, use yarn build.
```
# Install necessary packages for external-adapter folder and start backend api

'''
cd external-adapter
yarn install
yarn start
'''
Result should be something like this
'''
yarn run v1.22.19
$ node server.js
Listening on port 5002!
'''
# Install necessary packages for xrcserver folder and start backend api
'''
cd xrcserver
yarn install
yarn start
'''
Result should be something like this
'''
yarn run v1.22.19
$ node server.js
Listening on port 5001!
'''

## Start Plugin node & EXternal-initiator
- Follow plugin node setup guide https://docs.goplugin.co/plugin-installations/how-to-install-plugin-node/modular-method-deployment-recommended-approach 
- Make sure, your node is pointed to "Apothem" network
- Create a bridge in Plugin UI & point that to 5002, so the plugin & external-initiator can communicate with external-adapter