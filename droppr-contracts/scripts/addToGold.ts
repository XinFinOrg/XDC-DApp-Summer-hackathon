const hre = require("hardhat");

const main = async function () {
  const { deployments, getNamedAccounts, ethers } = hre;
  const { execute, getOrNull } = deployments;
  const { libraryDeployer } = await getNamedAccounts();
  const signer = await ethers.getSigner(libraryDeployer);

  const esp = getOrNull("EternalStorageProxy");
  const droppr = getOrNull("AirdropDistributor");
  console.log(esp.address);
  console.log(droppr.abi);
  console.log(signer);
};

main()
  .then(() => process.exit(0))
  .catch((error) => {
    console.error(error);
    process.exitCode = 1;
  });
