import { Currency, Ether, NativeCurrency, Token, WETH9 } from '@uniswap/sdk-core'
import { USDC_ARBITRUM, USDC_MAINNET, USDC_OPTIMISM, USDC_POLYGON } from '@uniswap/smart-order-router'
import BscLogo from 'assets/images/bsc-logo.png'
import EtherLogo from 'assets/images/ethereum-logo.png'
import FuseLogo from 'assets/images/fuse-logo.png'
import XDCLogo from 'assets/images/xinfin-logo.png'
import MetisLogo from 'assets/svg/metis-logo.svg'
import invariant from 'tiny-invariant'

import { SupportedChainId } from './chains'

export { USDC_ARBITRUM, USDC_MAINNET, USDC_OPTIMISM, USDC_POLYGON }

// export const AMPL = new Token(
//   SupportedChainId.MAINNET,
//   '0xD46bA6D942050d489DBd938a2C909A5d5039A161',
//   9,
//   'AMPL',
//   'Ampleforth'
// )
// export const DAI = new Token(
//   SupportedChainId.MAINNET,
//   '0x6B175474E89094C44Da98b954EedeAC495271d0F',
//   18,
//   'DAI',
//   'Dai Stablecoin'
// )
// export const DAI_ARBITRUM_ONE = new Token(
//   SupportedChainId.ARBITRUM_ONE,
//   '0xDA10009cBd5D07dd0CeCc66161FC93D7c9000da1',
//   18,
//   'DAI',
//   'Dai stable coin'
// )
// export const DAI_OPTIMISM = new Token(
//   SupportedChainId.OPTIMISM,
//   '0xDA10009cBd5D07dd0CeCc66161FC93D7c9000da1',
//   18,
//   'DAI',
//   'Dai stable coin'
// )
// export const USDC: { [chainId in SupportedChainId]: Token } = {
//   [SupportedChainId.MAINNET]: USDC_MAINNET,
//   [SupportedChainId.ARBITRUM_ONE]: USDC_ARBITRUM,
//   [SupportedChainId.OPTIMISM]: USDC_OPTIMISM,
//   [SupportedChainId.ARBITRUM_RINKEBY]: USDC_ARBITRUM_RINKEBY,
//   [SupportedChainId.OPTIMISTIC_KOVAN]: USDC_OPTIMISTIC_KOVAN,
//   [SupportedChainId.POLYGON]: USDC_POLYGON,
//   [SupportedChainId.POLYGON_MUMBAI]: USDC_POLYGON_MUMBAI,
//   [SupportedChainId.GOERLI]: USDC_GÖRLI,
//   [SupportedChainId.RINKEBY]: USDC_RINKEBY,
//   [SupportedChainId.KOVAN]: USDC_KOVAN,
//   [SupportedChainId.ROPSTEN]: USDC_ROPSTEN,
// }
// export const DAI_POLYGON = new Token(
//   SupportedChainId.POLYGON,
//   '0x8f3Cf7ad23Cd3CaDbD9735AFf958023239c6A063',
//   18,
//   'DAI',
//   'Dai Stablecoin'
// )
// export const USDT_POLYGON = new Token(
//   SupportedChainId.POLYGON,
//   '0xc2132d05d31c914a87c6611c10748aeb04b58e8f',
//   6,
//   'USDT',
//   'Tether USD'
// )
// export const WBTC_POLYGON = new Token(
//   SupportedChainId.POLYGON,
//   '0x1bfd67037b42cf73acf2047067bd4f2c47d9bfd6',
//   8,
//   'WBTC',
//   'Wrapped BTC'
// )
// export const USDT = new Token(
//   SupportedChainId.MAINNET,
//   '0xdAC17F958D2ee523a2206206994597C13D831ec7',
//   6,
//   'USDT',
//   'Tether USD'
// )
// export const USDT_ARBITRUM_ONE = new Token(
//   SupportedChainId.ARBITRUM_ONE,
//   '0xFd086bC7CD5C481DCC9C85ebE478A1C0b69FCbb9',
//   6,
//   'USDT',
//   'Tether USD'
// )
// export const USDT_OPTIMISM = new Token(
//   SupportedChainId.OPTIMISM,
//   '0x94b008aA00579c1307B0EF2c499aD98a8ce58e58',
//   6,
//   'USDT',
//   'Tether USD'
// )
// export const WBTC = new Token(
//   SupportedChainId.MAINNET,
//   '0x2260FAC5E5542a773Aa44fBCfeDf7C193bc2C599',
//   8,
//   'WBTC',
//   'Wrapped BTC'
// )
// export const WBTC_ARBITRUM_ONE = new Token(
//   SupportedChainId.ARBITRUM_ONE,
//   '0x2f2a2543B76A4166549F7aaB2e75Bef0aefC5B0f',
//   8,
//   'WBTC',
//   'Wrapped BTC'
// )
// export const WBTC_OPTIMISM = new Token(
//   SupportedChainId.OPTIMISM,
//   '0x68f180fcCe6836688e9084f035309E29Bf0A2095',
//   8,
//   'WBTC',
//   'Wrapped BTC'
// )
// export const FEI = new Token(
//   SupportedChainId.MAINNET,
//   '0x956F47F50A910163D8BF957Cf5846D573E7f87CA',
//   18,
//   'FEI',
//   'Fei USD'
// )
// export const TRIBE = new Token(
//   SupportedChainId.MAINNET,
//   '0xc7283b66Eb1EB5FB86327f08e1B5816b0720212B',
//   18,
//   'TRIBE',
//   'Tribe'
// )
// export const FRAX = new Token(
//   SupportedChainId.MAINNET,
//   '0x853d955aCEf822Db058eb8505911ED77F175b99e',
//   18,
//   'FRAX',
//   'Frax'
// )
// export const FXS = new Token(
//   SupportedChainId.MAINNET,
//   '0x3432B6A60D23Ca0dFCa7761B7ab56459D9C964D0',
//   18,
//   'FXS',
//   'Frax Share'
// )
// export const renBTC = new Token(
//   SupportedChainId.MAINNET,
//   '0xEB4C2781e4ebA804CE9a9803C67d0893436bB27D',
//   8,
//   'renBTC',
//   'renBTC'
// )
// export const ETH2X_FLI = new Token(
//   SupportedChainId.MAINNET,
//   '0xAa6E8127831c9DE45ae56bB1b0d4D4Da6e5665BD',
//   18,
//   'ETH2x-FLI',
//   'ETH 2x Flexible Leverage Index'
// )
// export const sETH2 = new Token(
//   SupportedChainId.MAINNET,
//   '0xFe2e637202056d30016725477c5da089Ab0A043A',
//   18,
//   'sETH2',
//   'StakeWise Staked ETH2'
// )
// export const rETH2 = new Token(
//   SupportedChainId.MAINNET,
//   '0x20BC832ca081b91433ff6c17f85701B6e92486c5',
//   18,
//   'rETH2',
//   'StakeWise Reward ETH2'
// )
// export const SWISE = new Token(
//   SupportedChainId.MAINNET,
//   '0x48C3399719B582dD63eB5AADf12A40B4C3f52FA2',
//   18,
//   'SWISE',
//   'StakeWise'
// )
// export const WETH_POLYGON_MUMBAI = new Token(
//   SupportedChainId.POLYGON_MUMBAI,
//   '0xa6fa4fb5f76172d178d61b04b0ecd319c5d1c0aa',
//   18,
//   'WETH',
//   'Wrapped Ether'
// )

// export const WETH_POLYGON = new Token(
//   SupportedChainId.POLYGON,
//   '0x7ceb23fd6bc0add59e62ac25578270cff1b9f619',
//   18,
//   'WETH',
//   'Wrapped Ether'
// )
// export const UNI: { [chainId: number]: Token } = {
//   [SupportedChainId.MAINNET]: new Token(SupportedChainId.MAINNET, UNI_ADDRESS[1], 18, 'UNI', 'Uniswap'),
//   [SupportedChainId.RINKEBY]: new Token(SupportedChainId.RINKEBY, UNI_ADDRESS[4], 18, 'UNI', 'Uniswap'),
//   [SupportedChainId.ROPSTEN]: new Token(SupportedChainId.ROPSTEN, UNI_ADDRESS[3], 18, 'UNI', 'Uniswap'),
//   [SupportedChainId.GOERLI]: new Token(SupportedChainId.GOERLI, UNI_ADDRESS[5], 18, 'UNI', 'Uniswap'),
//   [SupportedChainId.KOVAN]: new Token(SupportedChainId.KOVAN, UNI_ADDRESS[42], 18, 'UNI', 'Uniswap'),
// }

export const WRAPPED_NATIVE_CURRENCY: { [chainId: number]: Token | undefined } = {
  ...(WETH9 as Record<SupportedChainId, Token>),
  // [SupportedChainId.OPTIMISM]: new Token(
  //   SupportedChainId.OPTIMISM,
  //   '0x4200000000000000000000000000000000000006',
  //   18,
  //   'WETH',
  //   'Wrapped Ether'
  // ),
  // [SupportedChainId.OPTIMISTIC_KOVAN]: new Token(
  //   SupportedChainId.OPTIMISTIC_KOVAN,
  //   '0x4200000000000000000000000000000000000006',
  //   18,
  //   'WETH',
  //   'Wrapped Ether'
  // ),
  // [SupportedChainId.ARBITRUM_ONE]: new Token(
  //   SupportedChainId.ARBITRUM_ONE,
  //   '0x82aF49447D8a07e3bd95BD0d56f35241523fBab1',
  //   18,
  //   'WETH',
  //   'Wrapped Ether'
  // ),
  // [SupportedChainId.ARBITRUM_RINKEBY]: new Token(
  //   SupportedChainId.ARBITRUM_RINKEBY,
  //   '0xB47e6A5f8b33b3F17603C83a0535A9dcD7E32681',
  //   18,
  //   'WETH',
  //   'Wrapped Ether'
  // ),
  // [SupportedChainId.POLYGON]: new Token(
  //   SupportedChainId.POLYGON,
  //   '0x0d500B1d8E8eF31E21C99d1Db9A6444d3ADf1270',
  //   18,
  //   'WMATIC',
  //   'Wrapped MATIC'
  // ),
  // [SupportedChainId.POLYGON_MUMBAI]: new Token(
  //   SupportedChainId.POLYGON_MUMBAI,
  //   '0x9c3C9283D3e44854697Cd22D3Faa240Cfb032889',
  //   18,
  //   'WMATIC',
  //   'Wrapped MATIC'
  // ),
  [SupportedChainId.MAINNET]: new Token(
    SupportedChainId.MAINNET,
    '0xc02aaa39b223fe8d0a0e5c4f27ead9083c756cc2',
    18,
    'WETH',
    'Wrapped Ether'
  ),
  [SupportedChainId.FUSE_MAINNET]: new Token(
    SupportedChainId.FUSE_MAINNET,
    '0x0BE9e53fd7EDaC9F859882AfdDa116645287C629',
    18,
    'WFUSE',
    'Wrapped Fuse'
  ),
  [SupportedChainId.METIS_ANDROMEDA]: new Token(
    SupportedChainId.METIS_ANDROMEDA,
    '0x75cb093E4D61d2A2e65D8e0BBb01DE8d89b53481',
    18,
    'WMETIS',
    'Wrapped Metis'
  ),
  [SupportedChainId.BSC_MAINNET]: new Token(
    SupportedChainId.BSC_MAINNET,
    '0xbb4CdB9CBd36B01bD1cBaEBF2De08d9173bc095c',
    18,
    'WBNB',
    'Wrapped BNB'
  ),
  [SupportedChainId.XDC_APOTHEM]: new Token(
    SupportedChainId.XDC_APOTHEM,
    '0x2a5c77b016Df1b3b0AE4E79a68F8adF64Ee741ba',
    18,
    'WtXDC',
    'Wrapped tXDC'
  ),
}

function isFuse(chainId: number): chainId is SupportedChainId.FUSE_MAINNET {
  return chainId === SupportedChainId.FUSE_MAINNET
}
function isApothem(chainId: number): chainId is SupportedChainId.XDC_APOTHEM {
  return chainId === SupportedChainId.XDC_APOTHEM
}
function isBSC(chainId: number): chainId is SupportedChainId.BSC_MAINNET {
  return chainId === SupportedChainId.BSC_MAINNET
}
function isMetis(chainId: number): chainId is SupportedChainId.METIS_ANDROMEDA {
  return chainId === SupportedChainId.METIS_ANDROMEDA
}
function isMainnet(chainId: number): chainId is SupportedChainId.MAINNET {
  return chainId === SupportedChainId.MAINNET
}

// class MaticNativeCurrency extends NativeCurrency {
//   equals(other: Currency): boolean {
//     return other.isNative && other.chainId === this.chainId
//   }

//   get wrapped(): Token {
//     if (!isMatic(this.chainId)) throw new Error('Not matic')
//     const wrapped = WRAPPED_NATIVE_CURRENCY[this.chainId]
//     invariant(wrapped instanceof Token)
//     return wrapped
//   }

//   public constructor(chainId: number) {
//     if (!isMatic(chainId)) throw new Error('Not matic')
//     super(chainId, 18, 'MATIC', 'Polygon Matic')
//   }
// }

class EthereumNativeCurrency extends NativeCurrency {
  equals(other: Currency): boolean {
    return other.isNative && other.chainId === this.chainId
  }

  get wrapped(): Token {
    if (!isMainnet(this.chainId)) throw new Error('Not fuse')
    const wrapped = WRAPPED_NATIVE_CURRENCY[this.chainId]
    invariant(wrapped instanceof Token)
    return wrapped
  }

  public constructor(chainId: number) {
    if (!isMainnet(chainId)) throw new Error('Not mainnet')
    super(chainId, 18, 'ETH', 'Ether')
  }
}
class FuseNativeCurrency extends NativeCurrency {
  equals(other: Currency): boolean {
    return other.isNative && other.chainId === this.chainId
  }

  get wrapped(): Token {
    if (!isFuse(this.chainId)) throw new Error('Not fuse')
    const wrapped = WRAPPED_NATIVE_CURRENCY[this.chainId]
    invariant(wrapped instanceof Token)
    return wrapped
  }

  public constructor(chainId: number) {
    if (!isFuse(chainId)) throw new Error('Not fuse')
    super(chainId, 18, 'FUSE', 'Fuse')
  }
}
class ApothemNativeCurrency extends NativeCurrency {
  equals(other: Currency): boolean {
    return other.isNative && other.chainId === this.chainId
  }

  get wrapped(): Token {
    if (!isApothem(this.chainId)) throw new Error('Not apothem')
    const wrapped = WRAPPED_NATIVE_CURRENCY[this.chainId]
    invariant(wrapped instanceof Token)
    return wrapped
  }

  public constructor(chainId: number) {
    if (!isApothem(chainId)) throw new Error('Not apothem')
    super(chainId, 18, 'tXDC', 'Test XDC')
  }
}
class MetisNativeCurrency extends NativeCurrency {
  equals(other: Currency): boolean {
    return other.isNative && other.chainId === this.chainId
  }

  get wrapped(): Token {
    if (!isMetis(this.chainId)) throw new Error('Not metis')
    const wrapped = WRAPPED_NATIVE_CURRENCY[this.chainId]
    invariant(wrapped instanceof Token)
    return wrapped
  }

  public constructor(chainId: number) {
    if (!isMetis(chainId)) throw new Error('Not metis')
    super(chainId, 18, 'METIS', 'Metis')
  }
}
class BSCNativeCurrency extends NativeCurrency {
  equals(other: Currency): boolean {
    return other.isNative && other.chainId === this.chainId
  }

  get wrapped(): Token {
    if (!isBSC(this.chainId)) throw new Error('Not bsc')
    const wrapped = WRAPPED_NATIVE_CURRENCY[this.chainId]
    invariant(wrapped instanceof Token)
    return wrapped
  }

  public constructor(chainId: number) {
    if (!isBSC(chainId)) throw new Error('Not bsc')
    super(chainId, 18, 'BNB', 'Binance Smart Chain BNB')
  }
}

export class ExtendedEther extends Ether {
  public get wrapped(): Token {
    const wrapped = WRAPPED_NATIVE_CURRENCY[this.chainId]
    if (wrapped) return wrapped
    throw new Error('Unsupported chain ID')
  }

  private static _cachedExtendedEther: { [chainId: number]: NativeCurrency } = {}

  public static onChain(chainId: number): ExtendedEther {
    return this._cachedExtendedEther[chainId] ?? (this._cachedExtendedEther[chainId] = new ExtendedEther(chainId))
  }
}

const cachedNativeCurrency: { [chainId: number]: NativeCurrency } = {
  [SupportedChainId.MAINNET]: new EthereumNativeCurrency(SupportedChainId.MAINNET),
  [SupportedChainId.FUSE_MAINNET]: new FuseNativeCurrency(SupportedChainId.FUSE_MAINNET),
  // [SupportedChainId.FUSE_SPARK]: new FuseNativeCurrency(SupportedChainId.FUSE_SPARK),
  [SupportedChainId.METIS_ANDROMEDA]: new MetisNativeCurrency(SupportedChainId.METIS_ANDROMEDA),
  // [SupportedChainId.METIS_STARDUST]: new MetisNativeCurrency(SupportedChainId.METIS_STARDUST),
  [SupportedChainId.BSC_MAINNET]: new BSCNativeCurrency(SupportedChainId.BSC_MAINNET),
  [SupportedChainId.XDC_APOTHEM]: new ApothemNativeCurrency(SupportedChainId.XDC_APOTHEM),
  // [SupportedChainId.BSC_TESTNET]: new BSCNativeCurrency(SupportedChainId.BSC_TESTNET),
}
export function nativeOnChain(chainId: number): NativeCurrency {
  return cachedNativeCurrency[chainId] ?? ExtendedEther.onChain(chainId)
}

// export const TOKEN_SHORTHANDS: { [shorthand: string]: { [chainId in SupportedChainId]?: string } } = {
//   USDC: {
//     [SupportedChainId.MAINNET]: USDC_MAINNET.address,
//     [SupportedChainId.ARBITRUM_ONE]: USDC_ARBITRUM.address,
//     [SupportedChainId.OPTIMISM]: USDC_OPTIMISM.address,
//     [SupportedChainId.ARBITRUM_RINKEBY]: USDC_ARBITRUM_RINKEBY.address,
//     [SupportedChainId.OPTIMISTIC_KOVAN]: USDC_OPTIMISTIC_KOVAN.address,
//     [SupportedChainId.POLYGON]: USDC_POLYGON.address,
//     [SupportedChainId.POLYGON_MUMBAI]: USDC_POLYGON_MUMBAI.address,
//     [SupportedChainId.GOERLI]: USDC_GÖRLI.address,
//     [SupportedChainId.RINKEBY]: USDC_RINKEBY.address,
//     [SupportedChainId.KOVAN]: USDC_KOVAN.address,
//     [SupportedChainId.ROPSTEN]: USDC_ROPSTEN.address,
//   },
// }

export const NATIVE_LOGOS: { [chainId: number]: string } = {
  [SupportedChainId.BSC_MAINNET]: BscLogo,
  [SupportedChainId.FUSE_MAINNET]: FuseLogo,
  [SupportedChainId.METIS_ANDROMEDA]: MetisLogo,
  [SupportedChainId.MAINNET]: EtherLogo,
  [SupportedChainId.XDC_APOTHEM]: XDCLogo,
}
