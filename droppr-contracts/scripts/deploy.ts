import { ethers } from "hardhat";

const SERVICE_FEE = ethers.utils.parseEther("0.12").toString();
const PARTNER_FEE = ethers.utils.parseEther("4.82").toString();

async function main() {
  const [deployer] = await ethers.getSigners();

  console.log("Deploying contracts with the account:", deployer.address);
  const AirdropDistributor = await ethers.getContractFactory(
    "AirdropDistributor"
  );

  const droppr = await AirdropDistributor.deploy(SERVICE_FEE, PARTNER_FEE);
  console.log("txHash:", droppr.deployTransaction.hash);
  await droppr.deployed();

  console.log("droppr just dropped at: ", droppr.address);
}

main().catch((error) => {
  console.error(error);
  process.exitCode = 1;
});
