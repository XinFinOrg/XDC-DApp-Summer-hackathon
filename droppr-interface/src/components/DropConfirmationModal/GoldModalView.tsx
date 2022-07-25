import { parseEther } from '@ethersproject/units'
import { Trans } from '@lingui/macro'
import { ButtonYellow } from 'components/Button'
import { RowBetween } from 'components/Row'
import { CHAIN_IDS_TO_LONG_NAMES, SupportedChainId } from 'constants/chains'
import useActiveWeb3React from 'hooks/useActiveWeb3React'
import { useDropprContract } from 'hooks/useContract'
import useCurrencyBalance from 'lib/hooks/useCurrencyBalance'
import { useDropprFees } from 'lib/hooks/useDroppr'
import useNativeCurrency from 'lib/hooks/useNativeCurrency'
import { useCallback, useMemo, useState } from 'react'
import { ArrowLeft } from 'react-feather'
import { Text } from 'rebass'
import { TransactionType } from 'state/transactions/actions'
import { useTransactionAdder } from 'state/transactions/hooks'
import { CloseIcon } from 'theme'

import { DropModalView } from '.'
import { BigBold, PaddedColumn, Separator, SmallFaded, StyledTd, StyledTr, Wrapper } from './styleds'

export default function GoldModalView({
  onCloseModal,
  setModalView,
}: {
  onCloseModal?: () => void
  setModalView?: (view: DropModalView) => void
}) {
  const { account, chainId } = useActiveWeb3React()
  const { vipFee } = useDropprFees()
  const [buttonMessage, setMessage] = useState<string | undefined>()
  const nativeCurrency = useNativeCurrency()
  const nativeBalance = useCurrencyBalance(account ?? undefined, nativeCurrency)
  const droppr = useDropprContract()
  const networkName = useMemo(
    () => (chainId && chainId in SupportedChainId ? CHAIN_IDS_TO_LONG_NAMES[chainId as SupportedChainId] : undefined),
    [chainId]
  )
  const addTransaction = useTransactionAdder()

  const subscribe = useCallback(async () => {
    if (!account || !nativeBalance || !droppr || !networkName) return setMessage('Not connected')
    if (nativeBalance?.lessThan(vipFee)) return setMessage('Insufficient Balance')
    try {
      setMessage('Getting Gold...')
      const reciept = await droppr.subscribe({ value: parseEther(vipFee.toFixed(nativeCurrency.decimals)) })
      addTransaction(reciept, { type: TransactionType.DROPPR_GOLD, networkName })
      if (setModalView) setModalView(DropModalView.confirmation)
    } catch (e) {
      console.log(e)
      setMessage(undefined)
    }
  }, [account, addTransaction, droppr, nativeBalance, nativeCurrency.decimals, setModalView, vipFee, networkName])

  return (
    <Wrapper>
      <PaddedColumn>
        <RowBetween>
          {setModalView && (
            <ArrowLeft style={{ cursor: 'pointer' }} onClick={() => setModalView(DropModalView.confirmation)} />
          )}
          <Text fontWeight={500} fontSize={20}>
            <Trans>Droppr Gold</Trans>
          </Text>
          {onCloseModal && <CloseIcon onClick={onCloseModal} />}
        </RowBetween>
      </PaddedColumn>
      <Separator />
      <PaddedColumn>
        <table>
          <StyledTr>
            <StyledTd colSpan={2}>Eliminate droppr service fees forever by becoming our Golden partner👑</StyledTd>
          </StyledTr>
          <StyledTr>
            <StyledTd>
              <BigBold>
                {vipFee.toSignificant()} {vipFee.currency.symbol}
              </BigBold>
              <SmallFaded>Droppr Gold one-time fee</SmallFaded>
            </StyledTd>
            <StyledTd>
              <BigBold>
                {nativeBalance?.toSignificant() ?? '0.0'} {nativeCurrency.symbol}
              </BigBold>
              <SmallFaded>Your {nativeCurrency.symbol} balance</SmallFaded>
            </StyledTd>
          </StyledTr>
        </table>
      </PaddedColumn>
      <ButtonYellow disabled={buttonMessage !== undefined} onClick={subscribe}>
        {buttonMessage ?? 'Gold'}
      </ButtonYellow>
    </Wrapper>
  )
}
