import { Trans } from '@lingui/macro'

import { useToken } from '../../hooks/Tokens'
import {
  ApproveTransactionInfo,
  DropprAirdropTransactionInfo,
  DropprSubscribeTransactionInfo,
  TransactionInfo,
  TransactionType,
} from '../../state/transactions/actions'

function ApprovalSummary({ info }: { info: ApproveTransactionInfo }) {
  const token = useToken(info.tokenAddress)

  return <Trans>Approve {token?.symbol}</Trans>
}

function DropprGoldSummary({ info }: { info: DropprSubscribeTransactionInfo }) {
  return <Trans>Purchased Droppr Gold on {info.networkName}</Trans>
}

function DropprAirdropSummary({ info }: { info: DropprAirdropTransactionInfo }) {
  // TODO: add amount
  return <Trans>Drop to {info.numAddresses} addresses</Trans>
}

export function TransactionSummary({ info }: { info: TransactionInfo }) {
  switch (info.type) {
    case TransactionType.APPROVAL:
      return <ApprovalSummary info={info} />
    case TransactionType.DROPPR_GOLD:
      return <DropprGoldSummary info={info} />
    case TransactionType.DROPPR_DROP:
      return <DropprAirdropSummary info={info} />
  }
}
