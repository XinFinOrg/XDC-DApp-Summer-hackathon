import * as dotenv from "dotenv";

import { HardhatUserConfig, task } from "hardhat/config";
import "@nomiclabs/hardhat-etherscan";
import "@nomiclabs/hardhat-waffle";
import "@typechain/hardhat";
import "hardhat-gas-reporter";
import "solidity-coverage";

dotenv.config();

// This is a sample Hardhat task. To learn how to create your own go to
// https://hardhat.org/guides/create-task.html
task("accounts", "Prints the list of accounts", async (taskArgs, hre) => {
  const accounts = await hre.ethers.getSigners();

  for (const account of accounts) {
    console.log(account.address);
  }
});

// You need to export an object to set up your config
// Go to https://hardhat.org/config/ to learn more

const config: HardhatUserConfig = {
  solidity: "0.8.9",
  networks: {
    ropsten: {
      url: process.env.ROPSTEN_URL || "",
      accounts: getDeploymentAccount(),
    },
    rinkeby: {
      url: process.env.RINKEBY_URL,
      accounts: getDeploymentAccount(),
    },
    mumbai: {
      url: process.env.MUMBAI_URL,
      accounts: getDeploymentAccount(),
    },
  },
  gasReporter: {
    enabled: true,
    coinmarketcap: 'af8ddfb6-5886-41fe-80b5-19259a3a03be',
    currency: 'ETH',
    token: 'ETH',
    gasPriceApi: 'https://api.polygonscan.com/api?module=proxy&action=eth_gasPrice',
  },
  etherscan: {
    apiKey: process.env.ETHERSCAN_API_KEY,
  },
};

export default config;

function getDeploymentAccount(): string[] {
  return process.env.PRIVATE_KEY !== undefined ? [process.env.PRIVATE_KEY] : []
}