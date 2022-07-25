import { BigNumber } from '@ethersproject/bignumber'
import { parseUnits } from '@ethersproject/units'
import { Trans } from '@lingui/macro'
import { Currency, CurrencyAmount, NativeCurrency } from '@uniswap/sdk-core'
import { parseAmount } from '@uniswap/smart-order-router'
import { ButtonOutlined, ButtonPrimary, ButtonYellow } from 'components/Button'
import { RowBetween } from 'components/Row'
import useActiveWeb3React from 'hooks/useActiveWeb3React'
import { ApprovalState, useApproveCallback } from 'hooks/useApproveCallback'
import { useDropprContract } from 'hooks/useContract'
import useCurrencyBalance from 'lib/hooks/useCurrencyBalance'
import { useDerivedAirdropInfo, useDropprTxSize } from 'lib/hooks/useDroppr'
import useNativeCurrency from 'lib/hooks/useNativeCurrency'
import { useCallback, useEffect, useMemo, useState } from 'react'
import { Text } from 'rebass'
import { TransactionType } from 'state/transactions/actions'
import { useTransactionAdder } from 'state/transactions/hooks'
import { CloseIcon } from 'theme'

import { DropModalView } from '.'
import { BigBold, PaddedColumn, Separator, SmallFaded, StyledTd, StyledTr, Wrapper } from './styleds'

export default function ConfirmModalView({
  file,
  fileType,
  infiniteApprove,
  isSameAmounts,
  currency,
  amountPerAddress,
  setModalView,
  onCloseModal,
}: {
  file: string | undefined
  fileType: string | undefined
  infiniteApprove: boolean
  isSameAmounts: boolean
  currency: Currency
  amountPerAddress: string | undefined
  setModalView: (view: DropModalView) => void
  onCloseModal: () => void
}) {
  const { account } = useActiveWeb3React()
  const nativeCurrency = useNativeCurrency()
  const nativeBalance = useCurrencyBalance(account ?? undefined, nativeCurrency)
  const [inputError, setInputError] = useState<string | undefined>()
  const txSize = useDropprTxSize()

  const {
    isVip,
    serviceFee,
    accumulatedServiceFee,
    numTxs,
    currencyBalance,
    addressesWithAmounts,
    totalAmount,
    parsedAmountPerAddress,
  } = useDerivedAirdropInfo(isSameAmounts, currency, file, fileType, amountPerAddress)

  const droppr = useDropprContract()
  const [approval, approve] = useApproveCallback(totalAmount, droppr?.address, !infiniteApprove)
  const [approvalSubmitted, setApprovalSubmitted] = useState(false)

  useEffect(() => {
    if (approval === ApprovalState.APPROVED) setApprovalSubmitted(false)
  }, [approval])

  const onApprove = useCallback(() => {
    setApprovalSubmitted(true)
    approve()
  }, [approve])

  const method = useMemo(() => {
    return currency.isNative
      ? isSameAmounts
        ? 'sendCoinsSingleValue'
        : 'sendCoinsManyValues'
      : isSameAmounts
      ? 'sendTokensSingleValue'
      : 'sendTokensManyValues'
  }, [isSameAmounts, currency.isNative])
  const addTransaction = useTransactionAdder()

  const callsArgs: void | [string[], BigNumber[] | BigNumber, { [value: string]: BigNumber }][] = useMemo(() => {
    if (isSameAmounts && (!amountPerAddress || !parsedAmountPerAddress))
      return setInputError('Amount per address not specified')
    const addressesArrays: string[][] = Array.from(Array(numTxs).keys()).map((i) =>
      Object.keys(addressesWithAmounts).slice(i * txSize, i * txSize + txSize)
    )
    const amountsArrays =
      isSameAmounts && parsedAmountPerAddress !== undefined
        ? addressesArrays.map((_) => parseUnits(parsedAmountPerAddress.toFixed(currency.decimals), currency.decimals))
        : addressesArrays.map((addresses) =>
            addresses.map((address) =>
              parseUnits(addressesWithAmounts[address].toFixed(currency.decimals), currency.decimals)
            )
          )

    const overrides = addressesArrays.map((addressesChunk) => {
      return {
        value: parseUnits(
          serviceFee
            .multiply(isVip ? '0' : '1')
            .add(
              (currency.isNative
                ? addressesChunk.reduce<CurrencyAmount<NativeCurrency>>((mem, address) => {
                    return (addressesWithAmounts[address] as CurrencyAmount<NativeCurrency>).add(mem)
                  }, parseAmount('0', nativeCurrency) as CurrencyAmount<NativeCurrency>)
                : parseAmount('0', nativeCurrency)) as CurrencyAmount<NativeCurrency>
            )
            .toFixed(nativeCurrency.decimals),
          nativeCurrency.decimals
        ),
      }
    })
    return Array.from(Array(numTxs).keys()).map((i) => [addressesArrays[i], amountsArrays[i], overrides[i]])
  }, [
    addressesWithAmounts,
    amountPerAddress,
    currency,
    isSameAmounts,
    isVip,
    nativeCurrency,
    numTxs,
    parsedAmountPerAddress,
    serviceFee,
    txSize,
  ])
  console.log(droppr)

  const drop = useCallback(async () => {
    if (!droppr) return setInputError('Connection Error')
    if (!callsArgs) return
    setInputError('Dropping...')
    callsArgs.forEach(async (args) => {
      try {
        const receipt = await droppr[method](
          ...(currency.isNative ? args : [args[0], args[1], currency.address, args[2]])
        )
        addTransaction(receipt, { type: TransactionType.DROPPR_DROP, numAddresses: args[0].length })
      } catch (e) {
        console.log(e)
      }
    })
    setInputError(undefined)
    onCloseModal()
  }, [addTransaction, callsArgs, currency, droppr, method, onCloseModal])

  return (
    <Wrapper>
      <PaddedColumn>
        <RowBetween>
          <Text fontWeight={500} fontSize={20}>
            <Trans>Airdrop Summary</Trans>
          </Text>
          <CloseIcon onClick={onCloseModal} />
        </RowBetween>
      </PaddedColumn>
      <Separator />
      <PaddedColumn>
        {/* <FieldLabel>Summary</FieldLabel> */}
        <table style={{ padding: 'none' }}>
          <StyledTr>
            <StyledTd>
              <BigBold>{Object.values(addressesWithAmounts).length}</BigBold>
              <SmallFaded>Number of addresses</SmallFaded>
            </StyledTd>
            <StyledTd>
              <BigBold>
                {totalAmount.toSignificant()} {currency.symbol}
              </BigBold>
              <SmallFaded>Total airdrop amount</SmallFaded>
            </StyledTd>
          </StyledTr>
          <StyledTr>
            <StyledTd>
              <BigBold>{numTxs}</BigBold>
              <SmallFaded>Number of transactions required</SmallFaded>
            </StyledTd>
            <StyledTd>
              <BigBold>
                {currencyBalance?.toSignificant() ?? '0.0'} {currency.symbol}
              </BigBold>
              <SmallFaded>Your {currency.symbol} balance</SmallFaded>
            </StyledTd>
          </StyledTr>
          <StyledTr>
            <StyledTd>
              <BigBold>
                {accumulatedServiceFee.toSignificant()} {nativeCurrency.symbol} {isVip && '👑'}
              </BigBold>
              <SmallFaded>droppr service fees</SmallFaded>
            </StyledTd>
            <StyledTd>
              {currency.isNative ? (
                <BigBold>--</BigBold>
              ) : (
                <>
                  <BigBold>
                    {nativeBalance?.toSignificant()} {nativeCurrency.symbol}
                  </BigBold>
                  <SmallFaded>Your {nativeCurrency.symbol} balance</SmallFaded>
                </>
              )}
            </StyledTd>
          </StyledTr>
          {!isVip && (
            <>
              <StyledTr>
                <StyledTd colSpan={2}>
                  <ButtonYellow onClick={() => setModalView(DropModalView.gold)} size={1}>
                    Eliminate Fees 👑
                  </ButtonYellow>
                </StyledTd>
              </StyledTr>
            </>
          )}
        </table>
      </PaddedColumn>
      <Separator />
      {approval !== ApprovalState.APPROVED ? (
        <ButtonOutlined onClick={onApprove} disabled={approvalSubmitted}>
          {approvalSubmitted ? 'Approving...' : `Approve ${currency.symbol}`}
        </ButtonOutlined>
      ) : isVip ? (
        <ButtonYellow disabled={inputError !== undefined} onClick={drop}>
          {inputError ?? 'Drop'}
        </ButtonYellow>
      ) : (
        <ButtonPrimary disabled={inputError !== undefined} onClick={drop}>
          {inputError ?? 'Drop'}
        </ButtonPrimary>
      )}
    </Wrapper>
  )
}
