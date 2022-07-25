const { ethers } = require('hardhat');
const { writeFileSync } = require('fs');

async function deploy(name, ...params) {
  const Contract = await ethers.getContractFactory(name);
  return await Contract.deploy(...params).then(f => f.deployed());
}

async function main() {

  const erc20Gateway = await deploy('ERC20Gateway');
  console.log("erc20Gateway deployed to:", erc20Gateway.address);

  writeFileSync('deploy.json', JSON.stringify({
    ERC20Gateway: erc20Gateway.address,
    "USDT": "0x3813e82e6f7098b9583fc0f33a962d02018b6803",   //testnet
    "USDC": "0xe11A86849d99F524cAC3E7A0Ec1241828e332C62",   //testnet
    "DAI": "0x5eD8BD53B0c3fa3dEaBd345430B1A3a6A4e8BD7C",    //RInkeby dai
    "PLI": "0xb3db178db835b4dfcb4149b2161644058393267d",
    "SRX": "0xb3db178db835b4dfcb4149b2161644058393267d"
  }, null, 2));

}

if (require.main === module) {
  main().then(() => process.exit(0))
    .catch(error => { console.error(error); process.exit(1); });
}