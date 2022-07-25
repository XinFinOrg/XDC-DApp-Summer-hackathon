import bscLogo from 'assets/images/bsc-logo.png'
import ethereumLogoUrl from 'assets/images/ethereum-logo.png'
import fuseLogo from 'assets/images/fuse-logo.png'
import xinfinLogo from 'assets/images/xinfin-logo.png'
// import arbitrumLogoUrl from 'assets/svg/arbitrum_logo.svg'
import metisLogo from 'assets/svg/metis-logo.svg'

// import optimismLogoUrl from 'assets/svg/optimistic_ethereum.svg'
// import polygonMaticLogo from 'assets/svg/polygon-matic-logo.svg'
// import ms from 'ms.macro'
import { SupportedChainId, SupportedL1ChainId, SupportedL2ChainId } from './chains'
import { NETWORK_SPECIFIC_LISTS } from './lists'

export enum NetworkType {
  L1,
  L2,
}

interface BaseChainInfo {
  readonly networkType: NetworkType
  readonly blockWaitMsBeforeWarning?: number
  readonly docs: string
  readonly bridge?: string
  readonly explorer: string
  readonly infoLink: string
  readonly logoUrl: string
  readonly label: string
  readonly helpCenterUrl?: string
  readonly nativeCurrency: {
    name: string // e.g. 'Goerli ETH',
    symbol: string // e.g. 'gorETH',
    decimals: number // e.g. 18,
  }
}

export interface L1ChainInfo extends BaseChainInfo {
  readonly networkType: NetworkType.L1
}

export interface L2ChainInfo extends BaseChainInfo {
  readonly networkType: NetworkType.L2
  readonly bridge: string
  readonly statusPage?: string
  readonly defaultListUrl: string
}

export type ChainInfoMap = { readonly [chainId: number]: L1ChainInfo | L2ChainInfo } & {
  readonly [chainId in SupportedL2ChainId]: L2ChainInfo
} &
  { readonly [chainId in SupportedL1ChainId]: L1ChainInfo }

export const CHAIN_INFO: ChainInfoMap = {
  [SupportedChainId.MAINNET]: {
    networkType: NetworkType.L1,
    docs: 'https://ethereum.org/en/developers/docs/',
    explorer: 'https://etherscan.io/',
    infoLink: 'https://ethereum.org',
    label: 'Ethereum',
    logoUrl: ethereumLogoUrl,
    nativeCurrency: { name: 'Ether', symbol: 'ETH', decimals: 18 },
  },
  // [SupportedChainId.RINKEBY]: {
  //   networkType: NetworkType.L1,
  //   docs: 'https://docs.uniswap.org/',
  //   explorer: 'https://rinkeby.etherscan.io/',
  //   infoLink: 'https://info.uniswap.org/#/',
  //   label: 'Rinkeby',
  //   logoUrl: ethereumLogoUrl,
  //   nativeCurrency: { name: 'Rinkeby Ether', symbol: 'rETH', decimals: 18 },
  // },
  // [SupportedChainId.ROPSTEN]: {
  //   networkType: NetworkType.L1,
  //   docs: 'https://docs.uniswap.org/',
  //   explorer: 'https://ropsten.etherscan.io/',
  //   infoLink: 'https://info.uniswap.org/#/',
  //   label: 'Ropsten',
  //   logoUrl: ethereumLogoUrl,
  //   nativeCurrency: { name: 'Ropsten Ether', symbol: 'ropETH', decimals: 18 },
  // },
  // [SupportedChainId.KOVAN]: {
  //   networkType: NetworkType.L1,
  //   docs: 'https://docs.uniswap.org/',
  //   explorer: 'https://kovan.etherscan.io/',
  //   infoLink: 'https://info.uniswap.org/#/',
  //   label: 'Kovan',
  //   logoUrl: ethereumLogoUrl,
  //   nativeCurrency: { name: 'Kovan Ether', symbol: 'kovETH', decimals: 18 },
  // },
  // [SupportedChainId.GOERLI]: {
  //   networkType: NetworkType.L1,
  //   docs: 'https://docs.uniswap.org/',
  //   explorer: 'https://goerli.etherscan.io/',
  //   infoLink: 'https://info.uniswap.org/#/',
  //   label: 'Görli',
  //   logoUrl: ethereumLogoUrl,
  //   nativeCurrency: { name: 'Görli Ether', symbol: 'görETH', decimals: 18 },
  // },
  // [SupportedChainId.OPTIMISM]: {
  //   networkType: NetworkType.L2,
  //   blockWaitMsBeforeWarning: ms`25m`,
  //   bridge: 'https://gateway.optimism.io/?chainId=1',
  //   defaultListUrl: OPTIMISM_LIST,
  //   docs: 'https://optimism.io/',
  //   explorer: 'https://optimistic.etherscan.io/',
  //   infoLink: 'https://info.uniswap.org/#/optimism/',
  //   label: 'Optimism',
  //   logoUrl: optimismLogoUrl,
  //   statusPage: 'https://optimism.io/status',
  //   helpCenterUrl: 'https://help.uniswap.org/en/collections/3137778-uniswap-on-optimistic-ethereum-oξ',
  //   nativeCurrency: { name: 'Ether', symbol: 'ETH', decimals: 18 },
  // },
  // [SupportedChainId.OPTIMISTIC_KOVAN]: {
  //   networkType: NetworkType.L2,
  //   blockWaitMsBeforeWarning: ms`25m`,
  //   bridge: 'https://gateway.optimism.io/',
  //   defaultListUrl: OPTIMISM_LIST,
  //   docs: 'https://optimism.io/',
  //   explorer: 'https://optimistic.etherscan.io/',
  //   infoLink: 'https://info.uniswap.org/#/optimism/',
  //   label: 'Optimistic Kovan',
  //   logoUrl: optimismLogoUrl,
  //   statusPage: 'https://optimism.io/status',
  //   helpCenterUrl: 'https://help.uniswap.org/en/collections/3137778-uniswap-on-optimistic-ethereum-oξ',
  //   nativeCurrency: { name: 'Optimistic Kovan Ether', symbol: 'kovOpETH', decimals: 18 },
  // },
  // [SupportedChainId.ARBITRUM_ONE]: {
  //   networkType: NetworkType.L2,
  //   blockWaitMsBeforeWarning: ms`10m`,
  //   bridge: 'https://bridge.arbitrum.io/',
  //   docs: 'https://offchainlabs.com/',
  //   explorer: 'https://arbiscan.io/',
  //   infoLink: 'https://info.uniswap.org/#/arbitrum',
  //   label: 'Arbitrum',
  //   logoUrl: arbitrumLogoUrl,
  //   defaultListUrl: ARBITRUM_LIST,
  //   helpCenterUrl: 'https://help.uniswap.org/en/collections/3137787-uniswap-on-arbitrum',
  //   nativeCurrency: { name: 'Ether', symbol: 'ETH', decimals: 18 },
  // },
  // [SupportedChainId.ARBITRUM_RINKEBY]: {
  //   networkType: NetworkType.L2,
  //   blockWaitMsBeforeWarning: ms`10m`,
  //   bridge: 'https://bridge.arbitrum.io/',
  //   docs: 'https://offchainlabs.com/',
  //   explorer: 'https://rinkeby-explorer.arbitrum.io/',
  //   infoLink: 'https://info.uniswap.org/#/arbitrum/',
  //   label: 'Arbitrum Rinkeby',
  //   logoUrl: arbitrumLogoUrl,
  //   defaultListUrl: ARBITRUM_LIST,
  //   helpCenterUrl: 'https://help.uniswap.org/en/collections/3137787-uniswap-on-arbitrum',
  //   nativeCurrency: { name: 'Rinkeby Arbitrum Ether', symbol: 'rinkArbETH', decimals: 18 },
  // },
  // [SupportedChainId.POLYGON]: {
  //   networkType: NetworkType.L1,
  //   blockWaitMsBeforeWarning: ms`10m`,
  //   bridge: 'https://wallet.polygon.technology/bridge',
  //   docs: 'https://polygon.io/',
  //   explorer: 'https://polygonscan.com/',
  //   infoLink: 'https://info.uniswap.org/#/polygon/',
  //   label: 'Polygon',
  //   logoUrl: polygonMaticLogo,
  //   nativeCurrency: { name: 'Polygon Matic', symbol: 'MATIC', decimals: 18 },
  // },
  // [SupportedChainId.POLYGON_MUMBAI]: {
  //   networkType: NetworkType.L1,
  //   blockWaitMsBeforeWarning: ms`10m`,
  //   bridge: 'https://wallet.polygon.technology/bridge',
  //   docs: 'https://polygon.io/',
  //   explorer: 'https://mumbai.polygonscan.com/',
  //   infoLink: 'https://info.uniswap.org/#/polygon/',
  //   label: 'Polygon Mumbai',
  //   logoUrl: polygonMaticLogo,
  //   nativeCurrency: { name: 'Polygon Mumbai Matic', symbol: 'mMATIC', decimals: 18 },
  // },
  [SupportedChainId.FUSE_MAINNET]: {
    networkType: NetworkType.L1,
    bridge: 'https://app.voltage.finance/#/bridge',
    docs: 'https://docs.fuse.io/',
    explorer: 'https://explorer.fuse.io',
    infoLink: 'https://fuse.io',
    label: 'Fuse Mainnet',
    logoUrl: fuseLogo, // TODO: change
    nativeCurrency: { name: 'Fuse', symbol: 'FUSE', decimals: 18 },
  },
  // [SupportedChainId.FUSE_SPARK]: {
  //   networkType: NetworkType.L1,
  //   docs: 'https://docs.fuse.io/',
  //   explorer: 'https://explorer.fuse.io',
  //   infoLink: 'https://fuse.io',
  //   label: 'Fuse Spark Testnet',
  //   logoUrl: fuseLogo, // TODO: change
  //   nativeCurrency: { name: 'Fuse Spark', symbol: 'tFUSE', decimals: 18 },
  // },
  [SupportedChainId.BSC_MAINNET]: {
    networkType: NetworkType.L1,
    docs: 'https://docs.binance.org/smart-chain/guides/bsc-intro.html',
    explorer: 'https://bscscan.com/',
    infoLink: 'https://binance.org/',
    label: 'Binance Smart Chain',
    logoUrl: bscLogo, // TODO: change
    nativeCurrency: { name: 'BSC BNB', symbol: 'BNB', decimals: 18 },
  },
  // [SupportedChainId.BSC_TESTNET]: {
  //   networkType: NetworkType.L1,
  //   docs: 'https://docs.binance.org/smart-chain/guides/bsc-intro.html',
  //   explorer: 'https://bscscan.com/',
  //   infoLink: 'https://binance.org/',
  //   label: 'Binance Smart Chain',
  //   logoUrl: bscLogo, // TODO: change
  //   nativeCurrency: { name: 'BSC Testnet BNB', symbol: 'tBNB', decimals: 18 },
  // },
  [SupportedChainId.METIS_ANDROMEDA]: {
    networkType: NetworkType.L2,
    bridge: 'https://bridge.metis.io',
    docs: 'https://docs.metis.io/',
    explorer: 'https://andromeda-explorer.metis.io',
    infoLink: 'https://metis.io',
    label: 'Metis Andromeda',
    logoUrl: metisLogo,
    defaultListUrl: NETWORK_SPECIFIC_LISTS[SupportedChainId.METIS_ANDROMEDA],
    nativeCurrency: { name: 'Metis', symbol: 'METIS', decimals: 18 },
  },
  [SupportedChainId.XDC_APOTHEM]: {
    networkType: NetworkType.L1,
    docs: 'https://docs.xinfin.org/',
    explorer: 'https://explorer.apothem.network',
    infoLink: 'https://xinfin.org',
    label: 'XinFin Apothem Testner',
    logoUrl: xinfinLogo,
    nativeCurrency: { name: 'Test XDC', symbol: 'tXDC', decimals: 18 },
  },
  // [SupportedChainId.METIS_STARDUST]: {
  //   networkType: NetworkType.L2,
  //   bridge: 'https://bridge.metis.io',
  //   docs: 'https://docs.metis.io/',
  //   explorer: 'https://andromeda-explorer.metis.io',
  //   infoLink: 'https://metis.io',
  //   label: 'Metis StarDust',
  //   logoUrl: metisLogo,
  //   defaultListUrl: METIS_BRIDGE_LIST,
  //   nativeCurrency: { name: 'Metis', symbol: 'METIS', decimals: 18 },
  // },
}
