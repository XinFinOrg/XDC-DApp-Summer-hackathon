import { AutoColumn } from 'components/Column'
import GoldModalView from 'components/DropConfirmationModal/GoldModalView'
import { useDropprIsVip } from 'lib/hooks/useDroppr'
import AppBody from 'pages/AppBody'
import React from 'react'

import { Wrapper } from '../../components/drop/styleds'

export default function Partner() {
  const isVip = useDropprIsVip()
  return (
    <AppBody>
      <Wrapper id="drop-page">
        <AutoColumn gap={'sm'}>
          <div style={{ display: 'relative' }}>{!isVip ? <GoldModalView /> : <h1>You are already golden!</h1>}</div>
        </AutoColumn>
      </Wrapper>
    </AppBody>
  )
}
