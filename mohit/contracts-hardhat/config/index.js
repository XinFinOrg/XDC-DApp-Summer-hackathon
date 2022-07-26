const privateKeys = require('./privateKeys.json')

module.exports = {
  NETWORKS: {
    ETHEREUM: {
      RPC_URL: process.env.ETHEREUM_RPC_URL,
      CHAIN_ID: 1
    },
    KOVAN: {
      RPC_URL: process.env.KOVAN_RPC_URL,
      CHAIN_ID: 42
    },
    RINKEBY: {
      RPC_URL: process.env.RINKEBY_RPC_URL,
      CHAIN_ID: 4
    },
    ROPSTEN: {
      RPC_URL: process.env.ROPSTEN_RPC_URL,
      CHAIN_ID: 3
    },
    GORLI: {
      RPC_URL: process.env.GORLI_RPC_URL,
      CHAIN_ID: 5
    },
    BINANCE_CHAIN: {
      RPC_URL: process.env.BINANCE_CHAIN_MAINNET_RPC_URL,
      CHAIN_ID: 56
    },
    BINANCE_CHAIN_TESTNET: {
      RPC_URL: process.env.BINANCE_CHAIN_TESTNET_RPC_URL,
      CHAIN_ID: 97
    },
    POLYGON_MAINNET: {
      RPC_URL: process.env.POLYGON_MAINNET_RPC_URL,
      CHAIN_ID: 137
    },
    POLYGON_TESTNET: {
      RPC_URL: process.env.POLYGON_TESTNET_RPC_URL,
      CHAIN_ID: 80001
    },
    CUSTOM: {
      RPC_URL: process.env.CUSTOM_RPC_URL,
      CHAIN_ID: process.env.CUSTOM_CHAIN_ID
    },
    BTTC_TESTNET: {
      RPC_URL: 'https://api.shasta.trongrid.io/jsonrpc',
      CHAIN_ID: 1028
    },
    XDC_TESTNET: {
      RPC_URL: 'https://rpc.apothem.network',
      CHAIN_ID: 51
    }

  },

  PRIVATE_KEYS: privateKeys,

  REPORT_GAS: process.env.REPORT_GAS || true,

  ETHERSCAN_API_KEY: process.env.ETHERSCAN_API_KEY
}
