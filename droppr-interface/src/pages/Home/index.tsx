import { ButtonOutlined, ButtonYellow } from 'components/Button'
import QuestionHelper from 'components/QuestionHelper'
import UpcomingFeaturesModal from 'components/UpcomingFeaturesModal'
import { CHAIN_IDS_TO_LONG_NAMES } from 'constants/chains'
import ExternalLink from 'lib/components/ExternalLink'
import useInterval from 'lib/hooks/useInterval'
import React, { useMemo, useState } from 'react'
import { GitHub, Mail, MessageCircle, Twitter } from 'react-feather'
import ReactTextTransition from 'react-text-transition'
import { StyledInternalLink } from 'theme'

export default function Home() {
  const [networkIndex, setNetworkIndex] = useState(0)
  const supportedChains = useMemo(() => Object.values(CHAIN_IDS_TO_LONG_NAMES), [])
  const [featuresModalOpen, setFeaturesModalOpen] = useState(false)

  // useEffect(() => {
  //   setInterval(() => {
  //     setNetworkIndex(networkIndex + 1)
  //   }, 9000)
  // })
  useInterval(() => setNetworkIndex(networkIndex + 1), 4000)

  return (
    <>
      <div style={{ width: '75%' }}>
        <div style={{ width: '75%', margin: '50px' }}>
          <span style={{ fontSize: '50px', fontWeight: 600 }}>DROPPR</span>{' '}
          <span style={{ fontSize: '50px', fontWeight: 150 }}>SOLUTIONS</span>
        </div>
        <div style={{ width: '75%', margin: '25px' }}>
          <span style={{ fontSize: '25px', fontWeight: 375 }}>
            Send bulk native coins and ERC-20 tokens on{' '}
            <ReactTextTransition
              inline={true}
              text={supportedChains[networkIndex % supportedChains.length]}
              springConfig={{ tension: 300, friction: 10 }}
            />
            <br />
            And any EVM compatible networks{' '}
            <QuestionHelper text={'Contact us at hello@droppr.one to add your target network'}></QuestionHelper>
          </span>
        </div>
        <div style={{ margin: '25px' }}>
          <ExternalLink href="https://twitter.com/dropprone">
            <Twitter style={{ margin: '7px' }} />
          </ExternalLink>
          <ExternalLink href="https://github.com/dropprone">
            <GitHub style={{ margin: '7px' }} />
          </ExternalLink>
          <ExternalLink href="https://t.me/dropprone">
            <MessageCircle style={{ margin: '7px' }} />
          </ExternalLink>
          <ExternalLink href="mailto: hello@droppr.one">
            <Mail style={{ margin: '7px' }} />
          </ExternalLink>
        </div>
        <div style={{ width: '25%', margin: '50px' }}>
          <StyledInternalLink to={'drop'} style={{ textDecoration: 'none' }}>
            <ButtonYellow style={{ margin: '3px' }}>Send an Airdrop 👑</ButtonYellow>
          </StyledInternalLink>
          <ButtonOutlined onClick={() => setFeaturesModalOpen(true)}>Upcoming features 🚀</ButtonOutlined>
        </div>
      </div>
      <UpcomingFeaturesModal onClose={() => setFeaturesModalOpen(false)} isOpen={featuresModalOpen} />
    </>
  )
}
