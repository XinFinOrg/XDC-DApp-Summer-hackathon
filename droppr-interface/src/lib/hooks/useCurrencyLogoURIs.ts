import { Currency } from '@uniswap/sdk-core'
import { SupportedChainId } from 'constants/chains'
import { NATIVE_LOGOS } from 'constants/tokens'
import useHttpLocations from 'hooks/useHttpLocations'
// import useHttpLocations from 'hooks/useHttpLocations'
import { useMemo } from 'react'
import { WrappedTokenInfo } from 'state/lists/wrappedTokenInfo'

// function chainIdToNetworkName(networkId: SupportedChainId): string {
//   return CHAIN_IDS_TO_NAMES[networkId] ?? CHAIN_IDS_TO_NAMES[SupportedChainId.BSC_MAINNET]
// }

function getNativeLogoURI(chainId: SupportedChainId = SupportedChainId.BSC_MAINNET): string {
  return NATIVE_LOGOS[chainId] ?? NATIVE_LOGOS[SupportedChainId.BSC_MAINNET]
}

// function getTokenLogoURI(address: string, chainId: SupportedChainId = SupportedChainId.MAINNET): string | void {
//   const networkName = chainIdToNetworkName(chainId)
//   const networksWithUrls = [SupportedChainId.ARBITRUM_ONE, SupportedChainId.MAINNET, SupportedChainId.OPTIMISM]
//   if (networksWithUrls.includes(chainId)) {
//     return `https://raw.githubusercontent.com/Uniswap/assets/master/blockchains/${networkName}/assets/${address}/logo.png`
//   }
// }

export default function useCurrencyLogoURIs(currency?: Currency | null): string[] {
  const locations = useHttpLocations(currency instanceof WrappedTokenInfo ? currency.logoURI : undefined)
  return useMemo(() => {
    const logoURIs = [...locations]

    if (currency) {
      if (currency.isNative) {
        logoURIs.push(getNativeLogoURI(currency.chainId))
      } else if (currency.isToken) {
        // const logoURI = getTokenLogoURI(currency.address, currency.chainId)
        // if (logoURI) {
        //   logoURIs.push(logoURI)
        // }
      }
    }
    return logoURIs
  }, [currency, locations])
}
