
const hre = require('hardhat')
const saveToConfig = require('../utils/saveToConfig')

async function main () {
  const Greeter = await hre.ethers.getContractFactory('Greeter')
  const greeterABI = (await hre.artifacts.readArtifact('Greeter')).abi
  await saveToConfig('GREETER', 'ABI', greeterABI)
  const greeter = await Greeter.deploy('Hello, Hardhat!')
  await greeter.deployed()
  await saveToConfig('GREETER', 'ADDRESS', greeter.address)
  console.log('Greeter deployed to:', greeter.address)
}

main().catch((error) => {
  console.error(error)
  process.exitCode = 1
})
