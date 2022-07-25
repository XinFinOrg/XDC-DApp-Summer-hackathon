const { ethers } = require('hardhat');
const { writeFileSync } = require('fs');

async function deploy(name, ...params) {
  const Contract = await ethers.getContractFactory(name);
  return await Contract.deploy(...params).then(f => f.deployed());
}

async function main() {

  const xrc20Gateway = await deploy('XRC20Gateway', "0xb3db178db835b4dfcb4149b2161644058393267d");
  console.log("xrc20Gateway deployed to:", xrc20Gateway.address);

  writeFileSync('deployxdc.json', JSON.stringify({
    XRC20Gateway: xrc20Gateway.address
  }, null, 2));

}

if (require.main === module) {
  main().then(() => process.exit(0))
    .catch(error => { console.error(error); process.exit(1); });
}