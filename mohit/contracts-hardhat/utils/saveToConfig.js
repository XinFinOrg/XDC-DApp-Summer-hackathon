const path = '../config/contract.json'
const { constants } = require('fs')
const fs = require('fs').promises

function checkFileExists (file) {
  return fs.access(file, constants.F_OK)
    .then(() => true)
    .catch(() => false)
}

async function saveToConfig (contractName, fieldType, value) {
  try {
    if (!await checkFileExists(path)) {
      await fs.writeFile(path, JSON.stringify({}, null, 4))
    }
    const contractConfig = JSON.parse(await fs.readFile(path))
    contractConfig[contractName + '_' + fieldType] = value
    await fs.writeFile(path, JSON.stringify(contractConfig, null, 4))
  } catch (err) {
    console.log(err)
    console.log(`Couldn't Save ${fieldType} for contract ${contractName} to config`)
  }
}

module.exports = saveToConfig
