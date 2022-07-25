import { isAddress } from '@ethersproject/address'
import { Currency, CurrencyAmount, NativeCurrency } from '@uniswap/sdk-core'
import useActiveWeb3React from 'hooks/useActiveWeb3React'
import { useDropprContract } from 'hooks/useContract'
import tryParseCurrencyAmount from 'lib/utils/tryParseCurrencyAmount'
import { tryParseFile } from 'lib/utils/tryParseFile'
import { useEffect, useMemo, useState } from 'react'

import { useSingleCallResult } from './multicall'
import useCurrencyBalance from './useCurrencyBalance'
import useNativeCurrency from './useNativeCurrency'

export type AddressesToAmounts = { [address: string]: CurrencyAmount<Currency> }

export interface AirdropInfo {
  isNative: boolean
  isSameAmounts: boolean
  isVip: boolean
  serviceFee: CurrencyAmount<NativeCurrency>
  vipFee: CurrencyAmount<NativeCurrency>
  accumulatedServiceFee: CurrencyAmount<NativeCurrency>
  numTxs: number
  currencyBalance?: CurrencyAmount<Currency>
  addressesWithAmounts: AddressesToAmounts
  totalAmount: CurrencyAmount<Currency>
  parsedAmountPerAddress?: CurrencyAmount<Currency>
}

export function useDropprIsVip() {
  const { account } = useActiveWeb3React()
  const droppr = useDropprContract()
  const isVip = useSingleCallResult(droppr, 'isSubscribed', [account ?? undefined])
  return useMemo(() => (isVip.result?.length ? isVip.result[0] : false), [isVip.result])
}

export function useDropprTxSize() {
  return 256
}

export function useDropprFees() {
  const droppr = useDropprContract()
  const rawServiceFee = useSingleCallResult(droppr, 'serviceFee')
  const rawVipFee = useSingleCallResult(droppr, 'subscribtionFee')
  const nativeCurrency = useNativeCurrency()
  return useMemo(() => {
    return {
      serviceFee: rawServiceFee.result?.length
        ? CurrencyAmount.fromRawAmount(nativeCurrency, rawServiceFee.result[0])
        : (tryParseCurrencyAmount('0', nativeCurrency) as CurrencyAmount<NativeCurrency>),
      vipFee: rawVipFee.result?.length
        ? CurrencyAmount.fromRawAmount(nativeCurrency, rawVipFee.result[0])
        : (tryParseCurrencyAmount('0', nativeCurrency) as CurrencyAmount<NativeCurrency>),
    }
  }, [rawServiceFee, rawVipFee, nativeCurrency])
}

export function useDerivedAirdropInfo(
  isSameAmounts: boolean,
  currency: Currency,
  file?: string,
  fileType?: string,
  typedAmount?: string
): AirdropInfo {
  const { isNative } = currency
  const { account } = useActiveWeb3React()
  const isVip = useDropprIsVip()
  const [parsedData, setParsedData] = useState<Array<Array<string>> | undefined>()
  const { serviceFee, vipFee } = useDropprFees()

  useEffect(() => {
    if (!file || !fileType) return
    tryParseFile(file, fileType, setParsedData)
  }, [file, fileType])

  const parsedAmount = useMemo(() => tryParseCurrencyAmount(typedAmount, currency), [typedAmount, currency])

  const addressesWithAmounts: AddressesToAmounts = useMemo(() => {
    if (!parsedData) return {}
    return (
      parsedData
        // .filter((arr) => arr.length === (isSameAmounts ? 1 : 2))
        .reduce<AddressesToAmounts>((mem, curr) => {
          const amount = isSameAmounts ? parsedAmount : tryParseCurrencyAmount(curr[1], currency)
          const address = curr[0]
          if (!isAddress(address) || !amount) return mem
          return { ...mem, [address]: amount }
        }, {})
    )
  }, [parsedData, isSameAmounts, parsedAmount, currency])

  const _ZERO = useMemo(() => tryParseCurrencyAmount('0', currency) as CurrencyAmount<Currency>, [currency])

  const totalAmount = useMemo(
    () => Object.values(addressesWithAmounts).reduce((mem, amount) => mem.add(amount), _ZERO),
    [addressesWithAmounts, _ZERO]
  )
  const currencyBalance = useCurrencyBalance(account ?? undefined, currency)
  const numTxs = useMemo(() => Math.ceil(Object.keys(addressesWithAmounts).length / 256), [addressesWithAmounts])
  const accumulatedServiceFee = useMemo(() => serviceFee.multiply(isVip ? 0 : numTxs), [serviceFee, isVip, numTxs])

  return {
    isNative,
    isSameAmounts,
    isVip,
    vipFee,
    serviceFee,
    accumulatedServiceFee,
    numTxs,
    currencyBalance,
    addressesWithAmounts,
    totalAmount,
    parsedAmountPerAddress: parsedAmount,
  }
}
