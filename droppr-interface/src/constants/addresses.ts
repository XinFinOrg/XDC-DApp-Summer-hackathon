import { constructSameAddressMap } from '../utils/constructSameAddressMap'
import { SupportedChainId } from './chains'

type AddressMap = { [chainId: number]: string }

export const UNI_ADDRESS: AddressMap = constructSameAddressMap('0x1f9840a85d5aF5bf1D1762F925BDADdC4201F984')
export const MULTICALL_ADDRESS: AddressMap = {
  [SupportedChainId.MAINNET]: '0x1f98415757620b543a52e61c46b32eb19261f984',
  [SupportedChainId.FUSE_MAINNET]: '0x42Df9a6a4AC762D5B15cF420d72911320670cbe6',
  [SupportedChainId.BSC_MAINNET]: '0xD26De89843e24C3c62B3B78526f9E7185cC440Ac',
  [SupportedChainId.METIS_ANDROMEDA]: '0x1Da839d599A77fbcDF28fF7774d101ca7E9F9c07',
  [SupportedChainId.XDC_APOTHEM]: '0x6FFE6D25342898c6654262817fE30e67F990Fd9d',
}
export const DROPPR_ADDRESS: AddressMap = {
  [SupportedChainId.FUSE_MAINNET]: '0x2578E446F77603bE9b796EAE38B69EAA715fc10f',
  [SupportedChainId.METIS_ANDROMEDA]: '0x8f5456583822bB85523e937efd37edeb3Ce1c6d4', // TODO: deploy droppr contracts
  [SupportedChainId.BSC_MAINNET]: '0x8f5456583822bB85523e937efd37edeb3Ce1c6d4',
  [SupportedChainId.MAINNET]: '0x294938EB354B30B9c237c9CDa8193dDFC5F38E2e',
  [SupportedChainId.XDC_APOTHEM]: '0x6Be1eb0c1ff6Cff98AE9cA6e6eE9b9efBBe4EBA2',
}
/**
 * The latest governor bravo that is currently admin of timelock
 */

export const MERKLE_DISTRIBUTOR_ADDRESS: AddressMap = {
  [SupportedChainId.FUSE_MAINNET]: '0x090D4613473dEE047c3f2706764f49E0821D256e',
}
