# Hardhat For contracts Deployment

This module/folder is in commonjs format as esm is not supported yet with hardhat. There are multiple predefined scripts, tasks and utils added.

# To Setup
- Put all required fields in .env file under this folder only. env-example file is given. Enter RPC url like of infura/quicknode/etc for whichever blockchain you are using
- REPORT_GAS set to true or false if you want gas analysis while testing, Default is true
- Put your private keys in config folder under file name privateKeys.json. An example file named privateKeys-example.json is given for your reference

# Commands for multiple operation
- To compile SmartContracts:  npx hardhat compile
- To deploy SmartContracts:  npx hardhat run scripts/deploy --network ropsten (if you don't mention network it will deploy to local testnetwork created by hardhat)
- To see accounts registered:  npx hardhat accounts
- To see accounts and there balances:  npx hardhat balance --network ropsten
- To get smart contract coverage: npx hardhat coverage
- To run test : npx hardhat test
- To clean artifacts : npx hardhat clean
- For hardhat help : npx hardhat help


# Etherscan verification

To try out Etherscan verification, you first need to deploy a contract to an Ethereum network that's supported by Etherscan, such as Ropsten.

Then, copy the deployment address and paste it in to replace `DEPLOYED_CONTRACT_ADDRESS` in this command:

```shell
npx hardhat verify --network ropsten DEPLOYED_CONTRACT_ADDRESS "Hello, Hardhat!"
```
