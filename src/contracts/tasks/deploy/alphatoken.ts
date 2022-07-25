// import { task } from "@nomiclabs/hardhat-ethers/signers";
import { task } from "hardhat/config";
import { TaskArguments } from "hardhat/types";

import { AlphaToken } from "../../src/types/contracts/AlphaToken";
import { AlphaToken__factory } from "../../src/types/factories/contracts/AlphaToken__factory";

task("deploy:AlphaToken")
  // .addParam("greeting", "Say hello, be nice")
  .setAction(async function (taskArguments: TaskArguments, { ethers }) {
    // const signers: SignerWithAddress[] = await ethers.getSigners();
    const alphaTokenFactory: AlphaToken__factory = <AlphaToken__factory>await ethers.getContractFactory("AlphaToken");
    const alphaToken: AlphaToken = <AlphaToken>await alphaTokenFactory.deploy();//, { from: signers[0].address });
    await alphaToken.deployed();
    console.log("AlphaToken deployed to: ", alphaToken.address);
  });
