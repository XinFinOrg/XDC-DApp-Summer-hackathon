/**
 * List of all the networks supported by the Uniswap Interface
 */
export enum SupportedChainId {
  MAINNET = 1,
  // ROPSTEN = 3,
  // RINKEBY = 4,
  // GOERLI = 5,
  // KOVAN = 42,

  // ARBITRUM_ONE = 42161,
  // ARBITRUM_RINKEBY = 421611,

  // OPTIMISM = 10,
  // OPTIMISTIC_KOVAN = 69,

  // POLYGON = 137,
  // POLYGON_MUMBAI = 80001,

  FUSE_MAINNET = 122,
  // FUSE_SPARK = 123, // FUSE testnet

  BSC_MAINNET = 56,
  XDC_APOTHEM = 51,
  // BSC_TESTNET = 97,

  METIS_ANDROMEDA = 1088,
  // METIS_STARDUST = 588,
}

export const CHAIN_IDS_TO_NAMES = {
  // [SupportedChainId.MAINNET]: 'mainnet',
  // [SupportedChainId.ROPSTEN]: 'ropsten',
  // [SupportedChainId.RINKEBY]: 'rinkeby',
  // [SupportedChainId.GOERLI]: 'goerli',
  // [SupportedChainId.KOVAN]: 'kovan',
  // [SupportedChainId.POLYGON]: 'polygon',
  // [SupportedChainId.POLYGON_MUMBAI]: 'polygon_mumbai',
  // [SupportedChainId.ARBITRUM_ONE]: 'arbitrum',
  // [SupportedChainId.ARBITRUM_RINKEBY]: 'arbitrum_rinkeby',
  // [SupportedChainId.OPTIMISM]: 'optimism',
  // [SupportedChainId.OPTIMISTIC_KOVAN]: 'optimistic_kovan',
  [SupportedChainId.MAINNET]: 'mainnet',
  [SupportedChainId.FUSE_MAINNET]: 'fuse',
  // [SupportedChainId.FUSE_SPARK]: 'spark',
  [SupportedChainId.BSC_MAINNET]: 'bsc',
  // [SupportedChainId.BSC_TESTNET]: 'bsc_testnet',
  [SupportedChainId.METIS_ANDROMEDA]: 'metis_andromeda',
  [SupportedChainId.XDC_APOTHEM]: 'xinfin_apothem',
  // [SupportedChainId.METIS_STARDUST]: 'metis_stardust',
}

export const CHAIN_IDS_TO_LONG_NAMES = {
  [SupportedChainId.MAINNET]: 'Ethereum Mainnet',
  [SupportedChainId.FUSE_MAINNET]: 'Fuse Mainnet',
  // [SupportedChainId.FUSE_SPARK]: 'Fuse Spark',
  [SupportedChainId.BSC_MAINNET]: 'Binance Smart Chain',
  // [SupportedChainId.BSC_TESTNET]: 'Binance Smart Chain Testnet',
  [SupportedChainId.METIS_ANDROMEDA]: 'Metis Andromeda',
  [SupportedChainId.XDC_APOTHEM]: 'XinFin Apothem',
  // [SupportedChainId.METIS_STARDUST]: 'Metis Stardust',
}

/**
 * Array of all the supported chain IDs
 */
export const ALL_SUPPORTED_CHAIN_IDS: SupportedChainId[] = Object.values(SupportedChainId).filter(
  (id) => typeof id === 'number'
) as SupportedChainId[]

export const SUPPORTED_GAS_ESTIMATE_CHAIN_IDS = [
  // SupportedChainId.MAINNET,
  // SupportedChainId.POLYGON,
  // SupportedChainId.OPTIMISM,
  // SupportedChainId.ARBITRUM_ONE,
]

/**
 * All the chain IDs that are running the Ethereum protocol.
 */
export const L1_CHAIN_IDS = [
  SupportedChainId.MAINNET,
  // SupportedChainId.ROPSTEN,
  // SupportedChainId.RINKEBY,
  // SupportedChainId.GOERLI,
  // SupportedChainId.KOVAN,
  // SupportedChainId.POLYGON,
  // SupportedChainId.POLYGON_MUMBAI,
  SupportedChainId.FUSE_MAINNET,
  SupportedChainId.XDC_APOTHEM,
  // SupportedChainId.FUSE_SPARK,
  SupportedChainId.BSC_MAINNET,
  // SupportedChainId.BSC_TESTNET,
] as const

export type SupportedL1ChainId = typeof L1_CHAIN_IDS[number]

/**
 * Controls some L2 specific behavior, e.g. slippage tolerance, special UI behavior.
 * The expectation is that all of these networks have immediate transaction confirmation.
 */
export const L2_CHAIN_IDS = [
  // SupportedChainId.ARBITRUM_ONE,
  // SupportedChainId.ARBITRUM_RINKEBY,
  // SupportedChainId.OPTIMISM,
  // SupportedChainId.OPTIMISTIC_KOVAN,
  SupportedChainId.METIS_ANDROMEDA,
  // SupportedChainId.METIS_STARDUST,
] as const

export type SupportedL2ChainId = typeof L2_CHAIN_IDS[number]
