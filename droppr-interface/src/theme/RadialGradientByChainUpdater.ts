import { SupportedChainId } from 'constants/chains'
import useActiveWeb3React from 'hooks/useActiveWeb3React'
import { useEffect } from 'react'
import { useDarkModeManager } from 'state/user/hooks'

// import { SupportedChainId } from '../constants/chains'

const initialStyles = {
  width: '200vw',
  height: '200vh',
  transform: 'translate(-50vw, -100vh)',
  backgroundBlendMode: '',
}
const backgroundResetStyles = {
  width: '100vw',
  height: '100vh',
  transform: 'unset',
  backgroundBlendMode: '',
}

type TargetBackgroundStyles = typeof initialStyles | typeof backgroundResetStyles

const backgroundRadialGradientElement = document.getElementById('background-radial-gradient')
const setBackground = (newValues: TargetBackgroundStyles) =>
  Object.entries(newValues).forEach(([key, value]) => {
    if (backgroundRadialGradientElement) {
      backgroundRadialGradientElement.style[key as keyof typeof backgroundResetStyles] = value
    }
  })
export default function RadialGradientByChainUpdater(): null {
  const { chainId } = useActiveWeb3React()
  const [darkMode] = useDarkModeManager()
  // manage background color
  useEffect(() => {
    if (!backgroundRadialGradientElement) {
      return
    }

    switch (chainId) {
      case SupportedChainId.FUSE_MAINNET:
        setBackground(backgroundResetStyles)
        const fuseLightGradient = 'radial-gradient(150% 100% at 50% 0%, #e4f5ab 0%, #defaf1 50%, #FFFFFF 100%)'
        const fuseDarkGradient = 'radial-gradient(150% 100% at 50% 0%, #0A294B 0%, #221E30 50%, #1F2128 100%)'
        backgroundRadialGradientElement.style.background = darkMode ? fuseDarkGradient : fuseLightGradient
        break
      case SupportedChainId.BSC_MAINNET:
        setBackground(backgroundResetStyles)
        const bscLightGradient = 'radial-gradient(150% 100% at 50% 0%, #ffc891 0%, #fffca1 50%, #FFFFFF 100%)'
        const bscDarkGradient = 'radial-gradient(150% 100% at 50% 0%,  #1F2128 0%, #2e2b28 50%, #2b221a 100%)'
        backgroundRadialGradientElement.style.background = darkMode ? bscDarkGradient : bscLightGradient
        break
      case SupportedChainId.METIS_ANDROMEDA:
        setBackground(backgroundResetStyles)
        const metisLightGradient = 'radial-gradient(150% 100% at 50% 0%, #c375ff 0%, #d2f1f7 50%, #FFFFFF 100%)'
        const metisDarkGradient = 'radial-gradient(150% 100% at 50% 0%, #2e0e36 0%, #180d29 50%, #231a29 100%)'
        backgroundRadialGradientElement.style.background = darkMode ? metisDarkGradient : metisLightGradient
        break
      case SupportedChainId.MAINNET:
        setBackground(backgroundResetStyles)
        const mainnetLightGradient = 'radial-gradient(150% 100% at 50% 0%, #96cbff 0%, #dbbdff 50%, #f0e3ff 100%)'
        const mainnetDarkGradient = 'radial-gradient(150% 100% at 50% 0%, #2e3863 0%, #474169 50%, #200429 100%)'
        backgroundRadialGradientElement.style.background = darkMode ? mainnetDarkGradient : mainnetLightGradient
        break
      case SupportedChainId.XDC_APOTHEM:
        setBackground(backgroundResetStyles)
        const xdcLightGradient = 'radial-gradient(150% 100% at 50% 0%, #DAA520 0%, #b36678 50%, #e8b7c3 100%)'
        const xdcDarkGradient = 'radial-gradient(150% 100% at 50% 0%, #031f1b 0%, #033136 50%, #2d074a 100%)'
        backgroundRadialGradientElement.style.background = darkMode ? xdcDarkGradient : xdcLightGradient
        break
      // TODO: something
      // case SupportedChainId.ARBITRUM_ONE:
      // case SupportedChainId.ARBITRUM_RINKEBY:
      //   setBackground(backgroundResetStyles)
      //   const arbitrumLightGradient = 'radial-gradient(150% 100% at 50% 0%, #CDE8FB 0%, #FCF3F9 50%, #FFFFFF 100%)'
      //   const arbitrumDarkGradient = 'radial-gradient(150% 100% at 50% 0%, #0A294B 0%, #221E30 50%, #1F2128 100%)'
      //   backgroundRadialGradientElement.style.background = darkMode ? arbitrumDarkGradient : arbitrumLightGradient
      //   break
      // case SupportedChainId.OPTIMISM:
      // case SupportedChainId.OPTIMISTIC_KOVAN:
      //   setBackground(backgroundResetStyles)
      //   const optimismLightGradient = 'radial-gradient(150% 100% at 50% 0%, #FFFBF2 2%, #FFF4F9 53%, #FFFFFF 100%)'
      //   const optimismDarkGradient = 'radial-gradient(150% 100% at 50% 0%, #3E2E38 2%, #2C1F2D 53%, #1F2128 100%)'
      //   backgroundRadialGradientElement.style.background = darkMode ? optimismDarkGradient : optimismLightGradient
      //   break
      // case SupportedChainId.POLYGON:
      // case SupportedChainId.POLYGON_MUMBAI:
      //   setBackground(backgroundResetStyles)
      //   const polygonLightGradient =
      //     'radial-gradient(153.32% 100% at 47.26% 0%, rgba(130, 71, 229, 0.0864) 0%, rgba(0, 41, 255, 0.06) 48.19%, rgba(0, 41, 255, 0.012) 100%), #FFFFFF'
      //   const polygonDarkGradient =
      //     'radial-gradient(150.6% 98.22% at 48.06% 0%, rgba(130, 71, 229, 0.6) 0%, rgba(200, 168, 255, 0) 100%), #1F2128'
      //   backgroundRadialGradientElement.style.background = darkMode ? polygonDarkGradient : polygonLightGradient
      //   backgroundRadialGradientElement.style.backgroundBlendMode = darkMode ? 'overlay,normal' : 'multiply,normal'
      //   break
      default:
        setBackground(initialStyles)
        backgroundRadialGradientElement.style.background = ''
    }
  }, [darkMode, chainId])
  return null
}
