import { Currency } from '@uniswap/sdk-core'
import Modal from 'components/Modal'
import { useState } from 'react'

import ConfirmModalView from './ConfirmModalView'
import GoldModalView from './GoldModalView'

export enum DropModalView {
  confirmation,
  management,
  gold,
}

export default function DropConfirmationsModal({
  file,
  fileType,
  infiniteApprove,
  isSameAmounts,
  currency,
  amountPerAddress,
  isModalOpen,
  onCloseModal,
}: {
  file: string | undefined
  fileType: string | undefined
  infiniteApprove: boolean
  isSameAmounts: boolean
  currency: Currency
  amountPerAddress: string | undefined
  isModalOpen: boolean
  onCloseModal: () => void
}) {
  const [modalView, setModalView] = useState(DropModalView.confirmation)
  return (
    <Modal maxHeight={80} minHeight={30} isOpen={isModalOpen} onDismiss={onCloseModal}>
      {modalView === DropModalView.confirmation && (
        <ConfirmModalView
          file={file}
          fileType={fileType}
          isSameAmounts={isSameAmounts}
          currency={currency}
          amountPerAddress={amountPerAddress}
          onCloseModal={onCloseModal}
          setModalView={setModalView}
          infiniteApprove={infiniteApprove}
        />
      )}
      {modalView === DropModalView.gold && <GoldModalView onCloseModal={onCloseModal} setModalView={setModalView} />}
    </Modal>
  )
}
