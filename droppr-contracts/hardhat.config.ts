/* eslint-disable node/no-unsupported-features/es-syntax */
import * as dotenv from "dotenv";

import { HardhatUserConfig } from "hardhat/config";
import "@nomiclabs/hardhat-etherscan";
import "@nomiclabs/hardhat-waffle";
import "@typechain/hardhat";
import "hardhat-gas-reporter";
import "solidity-coverage";
import "hardhat-deploy";

dotenv.config();
const PRIVATE_KEY =
  process.env.PRIVATE_KEY ||
  "0xabc123abc123abc123abc123abc123abc123abc123abc123abc123abc123abc1";

let config: HardhatUserConfig = {
  defaultNetwork: "hardhat",
  solidity: {
    compilers: [
      {
        version: "0.4.17",
        settings: {
          optimizer: {
            enabled: true,
            runs: 200,
          },
        },
      },
      {
        version: "0.8.0",
        settings: {
          optimizer: {
            enabled: true,
            runs: 200,
          },
        },
      },
      {
        version: "0.8.1",
        settings: {
          optimizer: {
            enabled: true,
            runs: 200,
          },
        },
      },
      {
        version: "0.4.18",
        settings: {
          optimizer: {
            enabled: true,
            runs: 200,
          },
        },
      },
    ],
  },
  networks: {
    hardhat: {
      deploy: ["./deploy/bsc"],
    },
    andromeda: {
      url: "https://andromeda.metis.io/?owner=1088",
      accounts: [PRIVATE_KEY],
      deploy: ["./deploy/andromeda"],
    },
    stardust: {
      url: "https://stardust.metis.io/?owner=588",
      accounts: [PRIVATE_KEY],
    },
    fuse: {
      url: "https://rpc.fuse.io",
      accounts: [PRIVATE_KEY],
      deploy: ["./deploy/fuse"],
    },
    spark: {
      url: "https://rpc.fusespark.io",
      gasPrice: 1000000000,
      accounts: [PRIVATE_KEY],
    },
    bsc: {
      url: "https://bsc-dataseed.binance.org/",
      accounts: [PRIVATE_KEY],
      deploy: ["./deploy/bsc"],
    },
    mainnet: {
      url: `https://mainnet.infura.io/v3/${process.env.INFURA_ID}`,
      accounts: [PRIVATE_KEY],
      deploy: ["./deploy/mainnet"],
    },
    apothem: {
      url: "https://rpc.apothem.network",
      accounts: [PRIVATE_KEY],
      deploy: ["./deploy/xinfin"],
    },
    xinfin: {
      url: "https://rpc.xinfin.network",
      accounts: [PRIVATE_KEY],
      deploy: ["./deploy/xinfin"],
    },
  },
  paths: {
    sources: "./contracts",
    artifacts: "./build/artifacts",
    cache: "./build/cache",
  },
  typechain: {
    outDir: "./build/typechain/",
    target: "ethers-v5",
  },
  namedAccounts: {
    deployer: {
      default: 0, // here this will by default take the first account as deployer
      1: 0,
      56: 0,
      122: 0,
      1088: 0,
    },
    libraryDeployer: {
      default: 1, // use a different account for deploying libraries on the hardhat network
      1: 0,
      56: 0,
      122: 0,
      1088: 0,
    },
  },
};

if (process.env.FORK_MAINNET === "true" && config.networks) {
  console.log("FORK_MAINNET is set to true");
  config = {
    ...config,
    networks: {
      ...config.networks,
      hardhat: {
        ...config.networks.hardhat,
        forking: {
          url: process.env.ALCHEMY_API ? process.env.ALCHEMY_API : "",
        },
        chainId: 1,
      },
    },
    external: {
      deployments: {
        localhost: ["deployments/mainnet"],
      },
    },
  };
}

export default config;
