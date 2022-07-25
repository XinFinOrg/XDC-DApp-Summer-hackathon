// import { task } from "@nomiclabs/hardhat-ethers/signers";
import { task } from "hardhat/config";
import { TaskArguments } from "hardhat/types";

import { Spaceships__factory } from "../../src/types/factories/contracts/Spaceships__factory";
import { Spaceships } from "../../src/types/contracts/Spaceships";

task("deploy:Spaceships")
  // .addParam("greeting", "Say hello, be nice")
  .setAction(async function (taskArguments: TaskArguments, { ethers }) {
    // const signers: SignerWithAddress[] = await ethers.getSigners();
    const spaceshipsFactory: Spaceships__factory = <Spaceships__factory>await ethers.getContractFactory("Spaceships");
    const spaceships: Spaceships = <Spaceships>await spaceshipsFactory.deploy('Spaceship', 'WARALPHA');//, { from: signers[0].address });
    await spaceships.deployed();
    console.log("Spaceships deployed to: ", spaceships.address);
  });
