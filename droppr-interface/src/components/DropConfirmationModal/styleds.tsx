import { AutoColumn } from 'components/Column'
import { Text } from 'rebass'
import styled from 'styled-components/macro'

export const Wrapper = styled.div`
  width: 100%;
  position: relative;
  display: flex;
  flex-flow: column;
`
export const PaddedColumn = styled(AutoColumn)`
  padding: 20px;
`
export const Separator = styled.div`
  width: 100%;
  height: 1px;
  background-color: ${({ theme }) => theme.bg2};
`
export const FieldLabel = styled.div`
  vertical-align: middle !important;
  padding: 5px;
  margin: 5px;
`
export const StyledTd = styled.td`
  text-align: center;
  padding: 3px;
  margin: 3px;
`
export const StyledTr = styled.tr`
  padding: 3px;
  margin: 3px;
`
export const BigBold = styled(Text)`
  font-weight: 400px;
  font-size: 18px;
`
export const SmallFaded = styled(Text)`
  font-weight: 250px;
  font-size: 12px;
`
export const CenteredDiv = styled.div`
  text-align: center;
`
