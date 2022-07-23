export const NFT_PORT_KEY = process.env.REACT_APP_NFT_PORT_KEY; // nft port key

export const APP_NAME = "Accord";
export const APP_DESC = "XDC-backed esignature requests"

export const CHAIN_OPTIONS = {
  50: {
    name: "XDC Mainnet",
    symbol: "XDC",
    rpc: "https://rpc.xinfin.network",
    url: "https://explorer.xinfin.network/",
    id: 50
  },
  51: {
    name: "XDC Testnet",
    symbol: "TXDC",
    rpc: "https://rpc.apothem.network",
    url: "https://explorer.apothem.network/",
    id: 51
  },
};

export const CHAIN_IDS = Object.keys(CHAIN_OPTIONS)

// 1: { name: "ethereum", url: "https://etherscan.io/tx/", id: 1 },
// 42: { name: "kovan", url: "https://kovan.etherscan.io/tx/", id: 42 },
// 4: { name: "rinkeby", url: "https://rinkeby.etherscan.io/tx/", id: 4 },


const USE_MAINNET = process.env.REACT_APP_USE_MAINNET === 'true'
export const ACTIVE_CHAIN = CHAIN_OPTIONS[USE_MAINNET ? "50" : "51"];

export const EXAMPLE_FORM = {
  title: "Renter agreement",
  description: "Please agree to the included renters agreement document",
  signerAddress: "0xD7e02fB8A60E78071D69ded9Eb1b89E372EE2292",
  files: [],
};

export const IPFS_BASE_URL = "https://ipfs.io/ipfs"

console.log("config", NFT_PORT_KEY, ACTIVE_CHAIN);
