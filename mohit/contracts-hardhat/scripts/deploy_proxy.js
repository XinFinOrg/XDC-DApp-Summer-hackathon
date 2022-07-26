const { artifacts, ethers, upgrades } = require('hardhat')

const saveToConfig = require('../utils/saveToConfig')

async function main () {
  console.log('Deploying NFT Smart Contract')
  const accounts = await ethers.getSigners()

  const NFT_CONTRACT = await ethers.getContractFactory('EVENT_ON_CHAIN_NFT')

  const name = 'EVENT_ON_CHAIN'
  const symbol = 'EOC'
  const baseURI = ''
  const rootAdmin = accounts[0].address

  const nftABI = (await artifacts.readArtifact('EVENT_ON_CHAIN_NFT')).abi
  await saveToConfig('NFT', 'ABI', nftABI)

  const nftContract = await upgrades.deployProxy(NFT_CONTRACT, [name, symbol, baseURI, rootAdmin], { initializer: 'initialize' })
  await nftContract.deployed()

  await saveToConfig('NFT', 'ADDRESS', nftContract.address)
  console.log('NFT contract deployed to:', nftContract.address)
}

main().catch((error) => {
  console.error(error)
  process.exitCode = 1
})
