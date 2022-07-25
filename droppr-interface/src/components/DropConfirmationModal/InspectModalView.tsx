import { Trans } from '@lingui/macro'
import { RowBetween } from 'components/Row'
import { ArrowLeft } from 'react-feather'
import { Text } from 'rebass'
import { CloseIcon } from 'theme'

import { DropModalView } from '.'
import { PaddedColumn, Separator, Wrapper } from './styleds'

export default function ManageModalView({
  onCloseModal,
  setModalView,
}: {
  onCloseModal: () => void
  setModalView: (view: DropModalView) => void
}) {
  return (
    <Wrapper>
      <PaddedColumn>
        <RowBetween>
          <ArrowLeft style={{ cursor: 'pointer' }} onClick={() => setModalView(DropModalView.confirmation)} />
          <Text fontWeight={500} fontSize={20}>
            <Trans>Manage Airdrop</Trans>
          </Text>
          <CloseIcon onClick={onCloseModal} />
        </RowBetween>
      </PaddedColumn>
      <Separator />
      <PaddedColumn></PaddedColumn>
    </Wrapper>
  )
}
