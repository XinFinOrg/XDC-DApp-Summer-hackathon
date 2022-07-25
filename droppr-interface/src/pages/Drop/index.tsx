import { Trans } from '@lingui/macro'
import { Currency } from '@uniswap/sdk-core'
import { ButtonGray, ButtonPrimary, ButtonYellow } from 'components/Button'
import { AutoColumn } from 'components/Column'
import CurrencyLogo from 'components/CurrencyLogo'
import DropConfirmationsModal from 'components/DropConfirmationModal'
import { BigBold, SmallFaded, StyledTd, StyledTr } from 'components/DropConfirmationModal/styleds'
import Row from 'components/Row'
import useActiveWeb3React from 'hooks/useActiveWeb3React'
import { DecimalInput } from 'lib/components/Input'
import { useDropprIsVip } from 'lib/hooks/useDroppr'
import useNativeCurrency from 'lib/hooks/useNativeCurrency'
import { darken } from 'polished'
import React, { useCallback, useEffect, useMemo, useState } from 'react'
import { FileUploader } from 'react-drag-drop-files'
import Switch from 'react-switch'
import { Text } from 'rebass'
import styled from 'styled-components/macro'
import { ExternalLink } from 'theme'
import { ExplorerDataType, getExplorerLink } from 'utils/getExplorerLink'

import { ReactComponent as DropDown } from '../../assets/images/dropdown.svg'
import DropHeader from '../../components/drop/DropHeader'
import { Wrapper } from '../../components/drop/styleds'
import QuestionHelper from '../../components/QuestionHelper'
import CurrencySearchModal from '../../components/SimpleCurrencySelect/CurrencySearchModal'
import { DROPPR_SUPPORTED_FILE_TYPES } from '../../constants'
import AppBody from '../AppBody'

const CurrencySelect = styled(ButtonGray)<{ visible: boolean; selected: boolean; hideInput?: boolean }>`
  align-items: center;
  background-color: ${({ selected, theme }) => (selected ? theme.bg2 : theme.primary1)};
  box-shadow: ${({ selected }) => (selected ? 'none' : '0px 6px 10px rgba(0, 0, 0, 0.075)')};
  box-shadow: 0px 6px 10px rgba(0, 0, 0, 0.075);
  color: ${({ selected, theme }) => (selected ? theme.text1 : theme.white)};
  cursor: pointer;
  border-radius: 16px;
  outline: none;
  user-select: none;
  border: none;
  font-size: 24px;
  font-weight: 500;
  height: ${({ hideInput }) => (hideInput ? '2.8rem' : '2.4rem')};
  width: ${({ hideInput }) => (hideInput ? '100%' : 'initial')};
  padding: 0 8px;
  justify-content: space-between;
  margin-left: ${({ hideInput }) => (hideInput ? '0' : '12px')};
  :focus,
  :hover {
    background-color: ${({ selected, theme }) => (selected ? theme.bg3 : darken(0.05, theme.primary1))};
  }
  visibility: ${({ visible }) => (visible ? 'visible' : 'hidden')};
`
const Aligner = styled.span`
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
`

const StyledDropDown = styled(DropDown)<{ selected: boolean }>`
  margin: 0 0.25rem 0 0.35rem;
  height: 35%;

  path {
    stroke: ${({ selected, theme }) => (selected ? theme.text1 : theme.white)};
    stroke-width: 1.5px;
  }
`
export const RowFixed = styled(Row)<{ gap?: string; justify?: string }>`
  width: fit-content;
  margin: ${({ gap }) => gap && `-${gap}`};
`
const StyledTokenName = styled.span<{ active?: boolean }>`
  ${({ active }) => (active ? '  margin: 0 0.25rem 0 0.25rem;' : '  margin: 0 0.25rem 0 0.25rem;')}
  font-size:  ${({ active }) => (active ? '18px' : '18px')};
`
const InputRow = styled.div<{ selected: boolean }>`
  ${({ theme }) => theme.flexRowNoWrap}
  align-items: center;
  justify-content: space-between;
  padding: ${({ selected }) => (selected ? ' 1rem 1rem 0.75rem 1rem' : '1rem 1rem 1rem 1rem')};
`
const InputPanel = styled.div<{ hideInput?: boolean }>`
  ${({ theme }) => theme.flexColumnNoWrap}
  position: relative;
  border-radius: ${({ hideInput }) => (hideInput ? '16px' : '20px')};
  background-color: ${({ theme, hideInput }) => (hideInput ? 'transparent' : theme.bg2)};
  z-index: 1;
  width: ${({ hideInput }) => (hideInput ? '100%' : 'initial')};
  transition: height 1s ease;
  will-change: height;
`
const InputLabel = styled.div`
  vertical-align: middle !important;
  padding: 5px;
  margin: 5px;
`
const Container = styled.div<{ hideInput: boolean }>`
  border-radius: ${({ hideInput }) => (hideInput ? '16px' : '20px')};
  border: 1px solid ${({ theme }) => theme.bg0};
  background-color: ${({ theme }) => theme.bg1};
  width: ${({ hideInput }) => (hideInput ? '100%' : 'initial')};
  :focus,
  :hover {
    border: 1px solid ${({ theme, hideInput }) => (hideInput ? ' transparent' : theme.bg3)};
  }
`

const FileLink = styled(ExternalLink)`
  border-radius: 6px;
  border: 3px solid ${({ theme }) => theme.bg0};
  background-color: ${({ theme }) => theme.bg1};
  align: center;
  text-decoration: none;
  width: initial;
  :focus,
  :hover {
    border: 1px solid transparent;
  }
`

export default function Drop() {
  const { chainId } = useActiveWeb3React()
  const nativeCurrency = useNativeCurrency()
  const [currency, setCurrency] = useState<Currency>(nativeCurrency)
  const [sameAmounts, setSameAmounts] = useState(false)
  const [amountPerAddress, setAmountPerAddress] = useState<string | undefined>(undefined)
  const [infiniteApprove, setInfiniteApprove] = useState(true)
  const [isConfirmationModalOpen, setIsConfirmationModalOpen] = useState(false)

  useEffect(() => setCurrency(nativeCurrency), [nativeCurrency])

  const [file, setFile] = useState<string | undefined>()
  const [fileType, setFileType] = useState<string | undefined>()

  const handleFileChange = (file: File) => {
    setFileType(file.type)
    file
      .text()
      .then((text: any) => setFile(text))
      .catch(console.log)
  }

  const [modalOpen, setModalOpen] = useState<boolean>(false)
  const isVip = useDropprIsVip()
  const { isNative } = currency
  const tokenAddress = currency.wrapped.address
  const tokenLink = useMemo(() => {
    if (!chainId) return
    return getExplorerLink(chainId ?? 0, tokenAddress, ExplorerDataType.TOKEN)
  }, [tokenAddress, chainId])

  const onCurrencySelect = setCurrency
  const handleDismissSearch = useCallback(() => {
    setModalOpen(false)
  }, [setModalOpen])

  return (
    <AppBody>
      <DropHeader />
      <Wrapper id="drop-page">
        <div style={{ margin: '5px', padding: '3px' }}>
          <Text fontWeight={400} fontSize={15}>
            Send bulk native coins and ERC-20 tokens and save your time and gas
          </Text>
        </div>
        <AutoColumn gap={'sm'}>
          <div style={{ display: 'relative' }}>
            <InputPanel id="drop-panel" hideInput={true} {...{ label: <Trans>Select asset</Trans> }}>
              <div style={{ margin: '5px', padding: '3px' }}>Select asset:</div>
              <Container hideInput={true}>
                <InputRow style={{ padding: '0', borderRadius: '8px' }} selected={!onCurrencySelect}>
                  <CurrencySelect
                    visible={currency !== undefined}
                    selected={!!currency}
                    hideInput={true}
                    className="open-currency-select-button"
                    onClick={() => {
                      setModalOpen(true)
                    }}
                  >
                    <Aligner>
                      <RowFixed>
                        {currency ? (
                          <CurrencyLogo style={{ marginRight: '0.5rem' }} currency={currency} size={'24px'} />
                        ) : null}
                        <StyledTokenName
                          className="token-symbol-container"
                          active={Boolean(currency && currency.symbol)}
                        >
                          {(currency && currency.symbol && currency.symbol.length > 20
                            ? currency.symbol.slice(0, 4) +
                              '...' +
                              currency.symbol.slice(currency.symbol.length - 5, currency.symbol.length)
                            : currency?.symbol) || <Trans>Select a token</Trans>}
                        </StyledTokenName>
                      </RowFixed>
                      <StyledDropDown selected={!!currency} />
                    </Aligner>
                  </CurrencySelect>
                </InputRow>
              </Container>
              {!isNative && tokenLink && (
                <>
                  <div style={{ padding: '5px', margin: '5px' }}>
                    Token Address: <br />
                  </div>
                  <Container hideInput={true}>
                    <InputRow style={{ padding: '7px', borderRadius: '8px' }} selected={!onCurrencySelect}>
                      <RowFixed>
                        <div>
                          <ExternalLink href={tokenLink}>{tokenAddress}</ExternalLink>
                        </div>
                      </RowFixed>
                    </InputRow>
                  </Container>
                </>
              )}
              <div>
                <table style={{ width: '100%', padding: '3px' }}>
                  <StyledTr>
                    <StyledTd colSpan={isNative ? 2 : 1}>
                      <BigBold>
                        Same Amounts <QuestionHelper text={'check this if sending the same amount to all addresses'} />{' '}
                      </BigBold>
                      <SmallFaded>
                        <Switch
                          height={15}
                          width={40}
                          onChange={() => {
                            setSameAmounts(!sameAmounts)
                            setFile(undefined)
                          }}
                          checked={sameAmounts}
                        />
                      </SmallFaded>
                    </StyledTd>
                    {!isNative && (
                      <StyledTd>
                        <BigBold>Infinite approve </BigBold>
                        <SmallFaded>
                          <Switch
                            height={15}
                            width={40}
                            onChange={() => setInfiniteApprove(!infiniteApprove)}
                            checked={infiniteApprove}
                          />
                        </SmallFaded>
                      </StyledTd>
                    )}
                  </StyledTr>
                </table>

                {sameAmounts && (
                  <>
                    <InputLabel style={{ textAlign: 'center' }}>Amount per address: </InputLabel>
                    <Container hideInput={false}>
                      <DecimalInput
                        style={{ padding: '0 2.5em 0 0.5em', margin: '4px 4px', textAlign: 'center' }}
                        value={amountPerAddress ?? ''}
                        placeholder="0.0"
                        onChange={(val) => setAmountPerAddress(val)}
                      />
                    </Container>
                  </>
                )}
              </div>
              <InputLabel>
                Airdrop file: <QuestionHelper text={'See the file examples below for each file format'} />
              </InputLabel>{' '}
              {/* TODO: select between file and text field */}
              <Container hideInput={true}>
                <FileUploader
                  handleChange={handleFileChange}
                  name={'Airdrop File'}
                  types={DROPPR_SUPPORTED_FILE_TYPES}
                />
              </Container>
              <InputLabel>
                Examples:
                <FileLink href={sameAmounts ? 'examples/same-amounts.txt' : 'examples/different-amounts.txt'}>
                  txt
                </FileLink>{' '}
                <FileLink href={sameAmounts ? 'examples/same-amounts.csv' : 'examples/different-amounts.csv'}>
                  csv
                </FileLink>{' '}
              </InputLabel>
              <CurrencySearchModal
                isOpen={modalOpen}
                onDismiss={handleDismissSearch}
                onCurrencySelect={onCurrencySelect}
                selectedCurrency={currency}
              />
              <DropConfirmationsModal
                isModalOpen={isConfirmationModalOpen}
                onCloseModal={() => setIsConfirmationModalOpen(false)}
                currency={currency}
                infiniteApprove={infiniteApprove}
                isSameAmounts={sameAmounts}
                file={file}
                fileType={fileType}
                amountPerAddress={amountPerAddress}
              />
              {isVip ? (
                <ButtonYellow onClick={() => setIsConfirmationModalOpen(true)}>Drop</ButtonYellow>
              ) : (
                <ButtonPrimary onClick={() => setIsConfirmationModalOpen(true)}>Drop</ButtonPrimary>
              )}
            </InputPanel>
          </div>
        </AutoColumn>
      </Wrapper>
    </AppBody>
  )
}
