require('dotenv').config();

require("@nomiclabs/hardhat-waffle");
require("@nomiclabs/hardhat-ethers");

task("accounts", "Prints the list of accounts", async () => {
  const accounts = await ethers.getSigners();

  for (const account of accounts) {
    console.log(account.address);
  }
});
console.log("ashdogahsdgsd", process.env.PRIVATE_KEY)
/**
 * @type import('hardhat/config').HardhatUserConfig
 */
module.exports = {
  solidity: {
    version: "0.8.2",
    settings: {
      optimizer: {
        enabled: true,
        runs: 200
      }
    }
  },
  networks: {
    local: {
      url: 'http://localhost:8545'
    },
    xdai: {
      url: 'https://dai.poa.network',
      accounts: [process.env.PRIVATE_KEY],
    },
    xdcapothem: {
      url: 'https://rpc.apothem.network',
      accounts: [process.env.XDCPRIVATE_KEY],
    },
    mumbai: {
      url: 'https://rpc-mumbai.maticvigil.com/',
      accounts: [process.env.PRIVATE_KEY],
    },
    rinkeby: {
      url: 'https://rinkeby.infura.io/v3/8d4b9c6cf9a942bd9c0468942a96fce0',
      accounts: [process.env.PRIVATE_KEY],
    },
    ropsten: {
      url: 'https://ropsten.infura.io/v3/8d4b9c6cf9a942bd9c0468942a96fce0',
      accounts: [process.env.PRIVATE_KEY],
    }
  },
  paths: {
    artifacts: "./app/src/artifacts"
  }
};
