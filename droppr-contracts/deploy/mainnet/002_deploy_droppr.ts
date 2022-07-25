/* eslint-disable node/no-unpublished-import */
import { HardhatRuntimeEnvironment } from "hardhat/types";
// eslint-disable-next-line node/no-missing-import
import { DeployFunction } from "hardhat-deploy/types";

const func: DeployFunction = async function (hre: HardhatRuntimeEnvironment) {
  const { deployments, getNamedAccounts, ethers } = hre;
  const { deploy, execute, getOrNull, log } = deployments;
  const { libraryDeployer } = await getNamedAccounts();
  const signer = await ethers.getSigner(libraryDeployer);

  const serviceFee = ethers.utils.parseEther("0.017");
  const goldFee = ethers.utils.parseEther("1");

  const result = await deploy("AirdropDistributor", {
    from: libraryDeployer,
    log: true,
    skipIfAlreadyDeployed: false,
  });

  const dropprVersion = "1.0.0";
  const esp = await getOrNull("EternalStorageProxy");
  if (!esp) {
    console.error("EternalStorageProxy not found, deploy again");
  } else {
    await execute(
      "EternalStorageProxy",
      { from: libraryDeployer, log: true },
      "upgradeTo",
      dropprVersion,
      result.address
    );
    const dropprESP = new ethers.Contract(esp.address, result.abi, signer);
    try {
      const ownTx = await dropprESP.initialize(serviceFee, goldFee);
      log(`Initializing droppr`);
    } catch (e) {
      console.log(e);
    }
  }
  log(`Upgraded Droppr to ${dropprVersion}`);
};
export default func;
func.dependencies = ["EternalStorageProxy"];
func.tags = ["Droppr"];
