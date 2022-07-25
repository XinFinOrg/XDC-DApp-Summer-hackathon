import { ethers } from 'ethers'
import { CONST, ShipToken } from '../const/const'
const spaceshipAbi = require('./abi/Spaceships.json')
const alphaTokenAbi = require('./abi/AlphaToken.json')

declare var window: any
let provider: ethers.providers.Web3Provider
let signer: ethers.providers.JsonRpcSigner
let address: string
let spaceshipContractWithSigner: ethers.Contract
let alphaTokenContractWithSigner: ethers.Contract

export const connectWallet = async () => {
  try {
    provider = new ethers.providers.Web3Provider(window.ethereum)

    await provider.send('eth_requestAccounts', [])

    window.ethereum.request({
      method: 'wallet_addEthereumChain',
      params: [
        {
          chainId: '0x33',
          rpcUrls: ['https://rpc.apothem.network'],
          chainName: 'XDC Testnet',
          nativeCurrency: {
            name: 'XDC',
            symbol: 'XDC',
            decimals: 18,
          },
          blockExplorerUrls: null,
        },
      ],
    })

    signer = provider.getSigner()
    address = await signer.getAddress()

    const spaceshipContract = new ethers.Contract(CONST.SPACESHIP_CONTRACT, spaceshipAbi, provider)
    spaceshipContractWithSigner = spaceshipContract.connect(signer)

    const alphaTokenContract = new ethers.Contract(CONST.ALPHA_TOKEN_CONTRACT, alphaTokenAbi, provider)
    alphaTokenContractWithSigner = alphaTokenContract.connect(signer)

    console.log(address)
  } catch (e: any) {
    window.location.reload()
  }
}

export const getShips = async () => {
  try {
    const shipId1 = await spaceshipContractWithSigner.tokenOfOwnerByIndex(address, 1)
    const shipId2 = await spaceshipContractWithSigner.tokenOfOwnerByIndex(address, 2)
    const shipId3 = await spaceshipContractWithSigner.tokenOfOwnerByIndex(address, 3)
    const shipId4 = await spaceshipContractWithSigner.tokenOfOwnerByIndex(address, 4)

    const shipCode1 = await spaceshipContractWithSigner._tokenToShipCode(shipId1)
    const shipCode2 = await spaceshipContractWithSigner._tokenToShipCode(shipId2)
    const shipCode3 = await spaceshipContractWithSigner._tokenToShipCode(shipId3)
    const shipCode4 = await spaceshipContractWithSigner._tokenToShipCode(shipId4)

    CONST.USER_SHIPS = [
      {
        tokenId: shipId1,
        shipCode: shipCode1,
      },
      {
        tokenId: shipId2,
        shipCode: shipCode2,
      },
      {
        tokenId: shipId3,
        shipCode: shipCode3,
      },
      {
        tokenId: shipId4,
        shipCode: shipCode4,
      },
    ]
    console.log(CONST.USER_SHIPS)
  } catch (e: any) {
    window.location.reload()
  }
}

export const mintShip = async () => {
  const tx = await spaceshipContractWithSigner.mintShip(address)
  const confirmation = await provider.getTransactionReceipt(tx.hash)
  console.log(confirmation)
}

export const upgradeShip = async (ship: ShipToken) => {
  const tx = await spaceshipContractWithSigner.upgradeShip(ship.tokenId, ship.shipCode)
  const confirmation = await provider.getTransactionReceipt(tx.hash)
  console.log(confirmation)
}

export const getAlphaBalance = async () => {
  const alphaBalance = await alphaTokenContractWithSigner.balanceOf(address)
  CONST.ALPHA_BALANCE = alphaBalance.toNumber()
  console.log(alphaBalance.toNumber(), 'ALPHAS')
}

export const mintAlphas = async () => {
  const tx = await alphaTokenContractWithSigner.mint(address, 1000)
  const confirmation = await provider.getTransactionReceipt(tx.hash)
  console.log(confirmation)
}

export const burnAlphas = async () => {
  const tx = await alphaTokenContractWithSigner.burn(1000)
  const confirmation = await provider.getTransactionReceipt(tx.hash)
  console.log(confirmation)
}