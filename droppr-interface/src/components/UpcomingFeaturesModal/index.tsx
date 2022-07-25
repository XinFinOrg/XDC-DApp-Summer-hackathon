import { Trans } from '@lingui/macro'
import Modal from 'components/Modal'
import { RowBetween } from 'components/Row'
import ExternalLink from 'lib/components/ExternalLink'
import React from 'react'
import { Text } from 'rebass'
import { CloseIcon } from 'theme'

import { PaddedColumn, Separator, Wrapper } from '../DropConfirmationModal/styleds'

export default function UpcomingFeaturesModal({ isOpen, onClose }: { isOpen: boolean; onClose: () => void }) {
  const upcomingFeatures = [
    {
      title: 'NFT Airdrops',
      description: 'Soon you will be able to bulk send erc721 and erc1155 NFT tokens using droppr.one',
    },
    {
      title: 'Droppr Infinity ♾',
      description:
        'Send to an INFINITE number of recipients in a single transaction, save tons on gas costs, and reclaim unclaimed amounts. Users will be able to claim their tokens via the droppr platform',
    },
  ]
  return (
    <>
      <Modal isOpen={isOpen} onDismiss={onClose}>
        <Wrapper>
          <PaddedColumn>
            <RowBetween>
              <Text fontWeight={500} fontSize={20}>
                <Trans>Upcoming Features 🚀</Trans>
              </Text>
              <CloseIcon onClick={onClose} />
            </RowBetween>
          </PaddedColumn>
          <Separator />
          {upcomingFeatures.map((feature, i) => (
            <div key={i.toString()} style={{ margin: '7px' }}>
              <Text style={{ margin: '1px', padding: '3px' }} fontWeight={400} fontSize={18}>
                {feature.title}
              </Text>
              <Text style={{ padding: '7px' }} fontWeight={250} fontSize={16}>
                {feature.description}
              </Text>
            </div>
          ))}
          <Separator />
          <div style={{ margin: '7px' }}>
            <Text style={{ margin: '1px', padding: '3px' }} fontWeight={400} fontSize={18}>
              <ExternalLink style={{ textDecoration: 'none' }} href="https://twitter.com/dropprone">
                Tweet
              </ExternalLink>{' '}
              about us, or{' '}
              <ExternalLink style={{ textDecoration: 'none' }} href="mailto:hello@droppr.one">
                Email
              </ExternalLink>{' '}
              us to let us know about your favorite feature.
            </Text>
          </div>
        </Wrapper>
      </Modal>
    </>
  )
}
