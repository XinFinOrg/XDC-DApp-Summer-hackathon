import type { SignerWithAddress } from "@nomiclabs/hardhat-ethers/dist/src/signer-with-address";
import { artifacts, ethers, waffle } from "hardhat";
import type { Artifact } from "hardhat/types";

import type { Spaceships } from "../../src/types/contracts/Spaceships";
import { Signers } from "../types";
import { shouldBehaveLikeSpaceships } from "./Spaceships.behavior";

describe("Unit tests", function () {
  before(async function () {
    this.signers = {} as Signers;

    const signers: SignerWithAddress[] = await ethers.getSigners();
    this.signers.admin = signers[0];
    this.signers.alice = signers[1];
    this.signers.bob = signers[2];

  });

  describe("Spaceships", function () {
    beforeEach(async function () {
      const spaceshipsArtifact: Artifact = await artifacts.readArtifact("Spaceships");
      this.spaceships = <Spaceships>await waffle.deployContract(this.signers.admin, spaceshipsArtifact, ['Spaceship', 'WARALPHA']);
    });

    shouldBehaveLikeSpaceships();
  });
});
