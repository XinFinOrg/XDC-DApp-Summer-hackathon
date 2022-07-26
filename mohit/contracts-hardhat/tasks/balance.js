module.exports = task('balance', 'Prints the list of accounts and there balance', async (taskArgs, hre) => {
  const accounts = await hre.ethers.getSigners()
  const provider = hre.ethers.provider

  for (const account of accounts) {
    const balance = await provider.getBalance(account.address)
    console.log(account.address + ' : ' + balance)
  }
})
