/* eslint-disable node/no-unpublished-import */
/* eslint-disable node/no-missing-import */
import { HardhatRuntimeEnvironment } from "hardhat/types";
import { DeployFunction } from "hardhat-deploy/types";

const func: DeployFunction = async function (hre: HardhatRuntimeEnvironment) {
  const { deployments, getNamedAccounts } = hre;
  const { deploy, getOrNull, log } = deployments;
  const { libraryDeployer } = await getNamedAccounts();

  const esp = await getOrNull("EternalStorageProxy");
  if (esp) {
    log(`reusing "EternalStorageProxy" at ${esp.address}`);
  } else {
    await deploy("EternalStorageProxy", {
      from: libraryDeployer,
      log: true,
      skipIfAlreadyDeployed: true,
    });
  }
};
export default func;
func.tags = ["EternalStorageProxy"];
