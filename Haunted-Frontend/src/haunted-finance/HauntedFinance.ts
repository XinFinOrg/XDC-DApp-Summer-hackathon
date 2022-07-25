// import { Fetcher, Route, Token } from '@uniswap/sdk';
import { Fetcher as FetcherSpirit, Token as TokenSpirit } from '@spiritswap/sdk';
import { Fetcher, Route, Token } from '@spookyswap/sdk';
import { Configuration } from './config';
import { ContractName, TokenStat, AllocationTime, LPStat, Bank, PoolStats, HShareSwapperStat } from './types';
import { BigNumber, Contract, ethers, EventFilter } from 'ethers';
import { decimalToBalance } from './ether-utils';
import { TransactionResponse } from '@ethersproject/providers';
import ERC20 from './ERC20';
import { getFullDisplayBalance, getDisplayBalance } from '../utils/formatBalance';
import { getDefaultProvider } from '../utils/provider';
import IUniswapV2PairABI from './IUniswapV2Pair.abi.json';
import config, { bankDefinitions } from '../config';
import moment from 'moment';
import { parseUnits } from 'ethers/lib/utils';
import { XDC_TICKER, SPOOKY_ROUTER_ADDR, HAUNTED_TICKER } from '../utils/constants';
/**
 * An API module of Haunted Finance contracts.
 * All contract-interacting domain logic should be defined in here.
 */
export class HauntedFinance {
  myAccount: string;
  provider: ethers.providers.Web3Provider;
  signer?: ethers.Signer;
  config: Configuration;
  contracts: { [name: string]: Contract };
  externalTokens: { [name: string]: ERC20 };
  stakingVersionOfUser?: string;

  HAUNTEDWXDC_LP: Contract;
  HAUNTED: ERC20;
  HSHARE: ERC20;
  HBOND: ERC20;
  XDC: ERC20;

  constructor(cfg: Configuration) {
    const { deployments, externalTokens } = cfg;
    const provider = getDefaultProvider();

    // loads contracts from deployments
    this.contracts = {};
    for (const [name, deployment] of Object.entries(deployments)) {
      this.contracts[name] = new Contract(deployment.address, deployment.abi, provider);
    }
    this.externalTokens = {};
    for (const [symbol, [address, decimal]] of Object.entries(externalTokens)) {
      this.externalTokens[symbol] = new ERC20(address, provider, symbol, decimal);
    }
    this.HAUNTED = new ERC20(deployments.haunted.address, provider, 'HAUNTED');
    this.HSHARE = new ERC20(deployments.hShare.address, provider, 'HSHARE');
    this.HBOND = new ERC20(deployments.hBond.address, provider, 'HBOND');
    this.XDC = this.externalTokens['WXDC'];

    // Uniswap V2 Pair
    this.HAUNTEDWXDC_LP = new Contract(externalTokens['HAUNTED-XDC-LP'][0], IUniswapV2PairABI, provider);

    this.config = cfg;
    this.provider = provider;
  }

  /**
   * @param provider From an unlocked wallet. (e.g. Metamask)
   * @param account An address of unlocked wallet account.
   */
  unlockWallet(provider: any, account: string) {
    const newProvider = new ethers.providers.Web3Provider(provider, this.config.chainId);
    this.signer = newProvider.getSigner(0);
    this.myAccount = account;
    for (const [name, contract] of Object.entries(this.contracts)) {
      this.contracts[name] = contract.connect(this.signer);
    }
    const tokens = [this.HAUNTED, this.HSHARE, this.HBOND, ...Object.values(this.externalTokens)];
    for (const token of tokens) {
      token.connect(this.signer);
    }
    this.HAUNTEDWXDC_LP = this.HAUNTEDWXDC_LP.connect(this.signer);
    console.log(`🔓 Wallet is unlocked. Welcome, ${account}!`);
    this.fetchStakingVersionOfUser()
      .then((version) => (this.stakingVersionOfUser = version))
      .catch((err) => {
        console.error(`Failed to fetch staking version: ${err.stack}`);
        this.stakingVersionOfUser = 'latest';
      });
  }

  get isUnlocked(): boolean {
    return !!this.myAccount;
  }

  //===================================================================
  //===================== GET ASSET STATS =============================
  //===================FROM SPOOKY TO DISPLAY =========================
  //=========================IN HOME PAGE==============================
  //===================================================================

  async getHauntedStat(): Promise<TokenStat> {
    const { HauntedFtmRewardPool, HauntedFtmLpHauntedRewardPool, HauntedFtmLpHauntedRewardPoolOld } = this.contracts;
    const supply = await this.HAUNTED.totalSupply();
    const hauntedRewardPoolSupply = await this.HAUNTED.balanceOf(HauntedFtmRewardPool.address);
    const hauntedRewardPoolSupply2 = await this.HAUNTED.balanceOf(HauntedFtmLpHauntedRewardPool.address);
    const hauntedRewardPoolSupplyOld = await this.HAUNTED.balanceOf(HauntedFtmLpHauntedRewardPoolOld.address);
    const hauntedCirculatingSupply = supply
      .sub(hauntedRewardPoolSupply)
      .sub(hauntedRewardPoolSupply2)
      .sub(hauntedRewardPoolSupplyOld);
    const priceInXDC = await this.getTokenPriceFromPancakeswap(this.HAUNTED);
    const priceOfOneXDC = await this.getWXDCPriceFromPancakeswap();
    const priceOfHauntedInDollars = (Number(priceInXDC) * Number(priceOfOneXDC)).toFixed(2);

    return {
      tokenInFtm: priceInXDC,
      priceInDollars: priceOfHauntedInDollars,
      totalSupply: getDisplayBalance(supply, this.HAUNTED.decimal, 0),
      circulatingSupply: getDisplayBalance(hauntedCirculatingSupply, this.HAUNTED.decimal, 0),
    };
  }

  /**
   * Calculates various stats for the requested LP
   * @param name of the LP token to load stats for
   * @returns
   */
  async getLPStat(name: string): Promise<LPStat> {
    const lpToken = this.externalTokens[name];
    const lpTokenSupplyBN = await lpToken.totalSupply();
    const lpTokenSupply = getDisplayBalance(lpTokenSupplyBN, 18);
    const token0 = name.startsWith('HAUNTED') ? this.HAUNTED : this.HSHARE;
    const isHaunted = name.startsWith('HAUNTED');
    const tokenAmountBN = await token0.balanceOf(lpToken.address);
    const tokenAmount = getDisplayBalance(tokenAmountBN, 18);

    const xdcAmountBN = await this.XDC.balanceOf(lpToken.address);
    const xdcAmount = getDisplayBalance(xdcAmountBN, 18);
    const tokenAmountInOneLP = Number(tokenAmount) / Number(lpTokenSupply);
    const xdcAmountInOneLP = Number(xdcAmount) / Number(lpTokenSupply);
    const lpTokenPrice = await this.getLPTokenPrice(lpToken, token0, isHaunted);
    const lpTokenPriceFixed = Number(lpTokenPrice).toFixed(2).toString();
    const liquidity = (Number(lpTokenSupply) * Number(lpTokenPrice)).toFixed(2).toString();
    return {
      tokenAmount: tokenAmountInOneLP.toFixed(2).toString(),
      xdcAmount: xdcAmountInOneLP.toFixed(2).toString(),
      priceOfOne: lpTokenPriceFixed,
      totalLiquidity: liquidity,
      totalSupply: Number(lpTokenSupply).toFixed(2).toString(),
    };
  }

  /**
   * Use this method to get price for Haunted
   * @returns TokenStat for HBOND
   * priceInXDC
   * priceInDollars
   * TotalSupply
   * CirculatingSupply (always equal to total supply for bonds)
   */
  async getBondStat(): Promise<TokenStat> {
    const { Treasury } = this.contracts;
    const hauntedStat = await this.getHauntedStat();
    const bondHauntedRatioBN = await Treasury.getBondPremiumRate();
    const modifier = bondHauntedRatioBN / 1e18 > 1 ? bondHauntedRatioBN / 1e18 : 1;
    const bondPriceInXDC = (Number(hauntedStat.tokenInFtm) * modifier).toFixed(2);
    const priceOfHBondInDollars = (Number(hauntedStat.priceInDollars) * modifier).toFixed(2);
    const supply = await this.HBOND.displayedTotalSupply();
    return {
      tokenInFtm: bondPriceInXDC,
      priceInDollars: priceOfHBondInDollars,
      totalSupply: supply,
      circulatingSupply: supply,
    };
  }

  /**
   * @returns TokenStat for HSHARE
   * priceInXDC
   * priceInDollars
   * TotalSupply
   * CirculatingSupply (always equal to total supply for bonds)
   */
  async getShareStat(): Promise<TokenStat> {
    const { HauntedFtmLPHShareRewardPool } = this.contracts;

    const supply = await this.HSHARE.totalSupply();

    const priceInXDC = await this.getTokenPriceFromPancakeswap(this.HSHARE);
    const hauntedRewardPoolSupply = await this.HSHARE.balanceOf(HauntedFtmLPHShareRewardPool.address);
    const hShareCirculatingSupply = supply.sub(hauntedRewardPoolSupply);
    const priceOfOneXDC = await this.getWXDCPriceFromPancakeswap();
    const priceOfSharesInDollars = (Number(priceInXDC) * Number(priceOfOneXDC)).toFixed(2);

    return {
      tokenInFtm: priceInXDC,
      priceInDollars: priceOfSharesInDollars,
      totalSupply: getDisplayBalance(supply, this.HSHARE.decimal, 0),
      circulatingSupply: getDisplayBalance(hShareCirculatingSupply, this.HSHARE.decimal, 0),
    };
  }

  async getHauntedStatInEstimatedTWAP(): Promise<TokenStat> {
    const { SeigniorageOracle, HauntedFtmRewardPool } = this.contracts;
    const expectedPrice = await SeigniorageOracle.twap(this.HAUNTED.address, ethers.utils.parseEther('1'));

    const supply = await this.HAUNTED.totalSupply();
    const hauntedRewardPoolSupply = await this.HAUNTED.balanceOf(HauntedFtmRewardPool.address);
    const hauntedCirculatingSupply = supply.sub(hauntedRewardPoolSupply);
    return {
      tokenInFtm: getDisplayBalance(expectedPrice),
      priceInDollars: getDisplayBalance(expectedPrice),
      totalSupply: getDisplayBalance(supply, this.HAUNTED.decimal, 0),
      circulatingSupply: getDisplayBalance(hauntedCirculatingSupply, this.HAUNTED.decimal, 0),
    };
  }

  async getHauntedPriceInLastTWAP(): Promise<BigNumber> {
    const { Treasury } = this.contracts;
    return Treasury.getHauntedUpdatedPrice();
  }

  async getBondsPurchasable(): Promise<BigNumber> {
    const { Treasury } = this.contracts;
    return Treasury.getBurnableHauntedLeft();
  }

  /**
   * Calculates the TVL, APR and daily APR of a provided pool/bank
   * @param bank
   * @returns
   */
  async getPoolAPRs(bank: Bank): Promise<PoolStats> {
    if (this.myAccount === undefined) return;
    const depositToken = bank.depositToken;
    const poolContract = this.contracts[bank.contract];
    const depositTokenPrice = await this.getDepositTokenPriceInDollars(bank.depositTokenName, depositToken);
    const stakeInPool = await depositToken.balanceOf(bank.address);
    const TVL = Number(depositTokenPrice) * Number(getDisplayBalance(stakeInPool, depositToken.decimal));
    const stat = bank.earnTokenName === 'HAUNTED' ? await this.getHauntedStat() : await this.getShareStat();
    const tokenPerSecond = await this.getTokenPerSecond(
      bank.earnTokenName,
      bank.contract,
      poolContract,
      bank.depositTokenName,
    );

    const tokenPerHour = tokenPerSecond.mul(60).mul(60);
    const totalRewardPricePerYear =
      Number(stat.priceInDollars) * Number(getDisplayBalance(tokenPerHour.mul(24).mul(365)));
    const totalRewardPricePerDay = Number(stat.priceInDollars) * Number(getDisplayBalance(tokenPerHour.mul(24)));
    const totalStakingTokenInPool =
      Number(depositTokenPrice) * Number(getDisplayBalance(stakeInPool, depositToken.decimal));
    const dailyAPR = (totalRewardPricePerDay / totalStakingTokenInPool) * 100;
    const yearlyAPR = (totalRewardPricePerYear / totalStakingTokenInPool) * 100;
    return {
      dailyAPR: dailyAPR.toFixed(2).toString(),
      yearlyAPR: yearlyAPR.toFixed(2).toString(),
      TVL: TVL.toFixed(2).toString(),
    };
  }

  /**
   * Method to return the amount of tokens the pool yields per second
   * @param earnTokenName the name of the token that the pool is earning
   * @param contractName the contract of the pool/bank
   * @param poolContract the actual contract of the pool
   * @returns
   */
  async getTokenPerSecond(
    earnTokenName: string,
    contractName: string,
    poolContract: Contract,
    depositTokenName: string,
  ) {
    if (earnTokenName === 'HAUNTED') {
      if (!contractName.endsWith('HauntedRewardPool')) {
        const rewardPerSecond = await poolContract.hauntedPerSecond();
        if (depositTokenName === 'WXDC') {
          return rewardPerSecond.mul(6000).div(11000).div(24);
        } else if (depositTokenName === 'BOO') {
          return rewardPerSecond.mul(2500).div(11000).div(24);
        } else if (depositTokenName === 'ZOO') {
          return rewardPerSecond.mul(1000).div(11000).div(24);
        } else if (depositTokenName === 'SHIBA') {
          return rewardPerSecond.mul(1500).div(11000).div(24);
        }
        return rewardPerSecond.div(24);
      }
      const poolStartTime = await poolContract.poolStartTime();
      const startDateTime = new Date(poolStartTime.toNumber() * 1000);
      const FOUR_DAYS = 4 * 24 * 60 * 60 * 1000;
      if (Date.now() - startDateTime.getTime() > FOUR_DAYS) {
        return await poolContract.epochHauntedPerSecond(1);
      }
      return await poolContract.epochHauntedPerSecond(0);
    }
    const rewardPerSecond = await poolContract.hSharePerSecond();
    if (depositTokenName.startsWith('HAUNTED')) {
      return rewardPerSecond.mul(35500).div(59500);
    } else {
      return rewardPerSecond.mul(24000).div(59500);
    }
  }

  /**
   * Method to calculate the tokenPrice of the deposited asset in a pool/bank
   * If the deposited token is an LP it will find the price of its pieces
   * @param tokenName
   * @param pool
   * @param token
   * @returns
   */
  async getDepositTokenPriceInDollars(tokenName: string, token: ERC20) {
    let tokenPrice;
    const priceOfOneFtmInDollars = await this.getWXDCPriceFromPancakeswap();
    if (tokenName === 'WXDC') {
      tokenPrice = priceOfOneFtmInDollars;
    } else {
      if (tokenName === 'HAUNTED-XDC-LP') {
        tokenPrice = await this.getLPTokenPrice(token, this.HAUNTED, true);
      } else if (tokenName === 'HSHARE-XDC-LP') {
        tokenPrice = await this.getLPTokenPrice(token, this.HSHARE, false);
      } else if (tokenName === 'SHIBA') {
        tokenPrice = await this.getTokenPriceFromSpiritswap(token);
      } else {
        tokenPrice = await this.getTokenPriceFromPancakeswap(token);
        tokenPrice = (Number(tokenPrice) * Number(priceOfOneFtmInDollars)).toString();
      }
    }
    return tokenPrice;
  }

  //===================================================================
  //===================== GET ASSET STATS =============================
  //=========================== END ===================================
  //===================================================================

  async getCurrentEpoch(): Promise<BigNumber> {
    const { Treasury } = this.contracts;
    return Treasury.epoch();
  }

  async getBondOraclePriceInLastTWAP(): Promise<BigNumber> {
    const { Treasury } = this.contracts;
    return Treasury.getBondPremiumRate();
  }

  /**
   * Buy bonds with cash.
   * @param amount amount of cash to purchase bonds with.
   */
  async buyBonds(amount: string | number): Promise<TransactionResponse> {
    const { Treasury } = this.contracts;
    const treasuryHauntedPrice = await Treasury.getHauntedPrice();
    return await Treasury.buyBonds(decimalToBalance(amount), treasuryHauntedPrice);
  }

  /**
   * Redeem bonds for cash.
   * @param amount amount of bonds to redeem.
   */
  async redeemBonds(amount: string): Promise<TransactionResponse> {
    const { Treasury } = this.contracts;
    const priceForHaunted = await Treasury.getHauntedPrice();
    return await Treasury.redeemBonds(decimalToBalance(amount), priceForHaunted);
  }

  async getTotalValueLocked(): Promise<Number> {
    let totalValue = 0;
    for (const bankInfo of Object.values(bankDefinitions)) {
      const pool = this.contracts[bankInfo.contract];
      const token = this.externalTokens[bankInfo.depositTokenName];
      const tokenPrice = await this.getDepositTokenPriceInDollars(bankInfo.depositTokenName, token);
      const tokenAmountInPool = await token.balanceOf(pool.address);
      const value = Number(getDisplayBalance(tokenAmountInPool, token.decimal)) * Number(tokenPrice);
      const poolValue = Number.isNaN(value) ? 0 : value;
      totalValue += poolValue;
    }

    const HSHAREPrice = (await this.getShareStat()).priceInDollars;
    const stakinghShareBalanceOf = await this.HSHARE.balanceOf(this.currentStaking().address);
    const stakingTVL = Number(getDisplayBalance(stakinghShareBalanceOf, this.HSHARE.decimal)) * Number(HSHAREPrice);

    return totalValue + stakingTVL;
  }

  /**
   * Calculates the price of an LP token
   * Reference https://github.com/DefiDebauchery/discordpricebot/blob/4da3cdb57016df108ad2d0bb0c91cd8dd5f9d834/pricebot/pricebot.py#L150
   * @param lpToken the token under calculation
   * @param token the token pair used as reference (the other one would be XDC in most cases)
   * @param isHaunted sanity check for usage of haunted token or hShare
   * @returns price of the LP token
   */
  async getLPTokenPrice(lpToken: ERC20, token: ERC20, isHaunted: boolean): Promise<string> {
    const totalSupply = getFullDisplayBalance(await lpToken.totalSupply(), lpToken.decimal);
    //Get amount of tokenA
    const tokenSupply = getFullDisplayBalance(await token.balanceOf(lpToken.address), token.decimal);
    const stat = isHaunted === true ? await this.getHauntedStat() : await this.getShareStat();
    const priceOfToken = stat.priceInDollars;
    const tokenInLP = Number(tokenSupply) / Number(totalSupply);
    const tokenPrice = (Number(priceOfToken) * tokenInLP * 2) //We multiply by 2 since half the price of the lp token is the price of each piece of the pair. So twice gives the total
      .toString();
    return tokenPrice;
  }

  async earnedFromBank(
    poolName: ContractName,
    earnTokenName: String,
    poolId: Number,
    account = this.myAccount,
  ): Promise<BigNumber> {
    const pool = this.contracts[poolName];
    try {
      if (earnTokenName === 'HAUNTED') {
        return await pool.pendingHAUNTED(poolId, account);
      } else {
        return await pool.pendingShare(poolId, account);
      }
    } catch (err) {
      console.error(`Failed to call earned() on pool ${pool.address}: ${err.stack}`);
      return BigNumber.from(0);
    }
  }

  async stakedBalanceOnBank(poolName: ContractName, poolId: Number, account = this.myAccount): Promise<BigNumber> {
    const pool = this.contracts[poolName];
    try {
      let userInfo = await pool.userInfo(poolId, account);
      return await userInfo.amount;
    } catch (err) {
      console.error(`Failed to call balanceOf() on pool ${pool.address}: ${err.stack}`);
      return BigNumber.from(0);
    }
  }

  /**
   * Deposits token to given pool.
   * @param poolName A name of pool contract.
   * @param amount Number of tokens with decimals applied. (e.g. 1.45 DAI * 10^18)
   * @returns {string} Transaction hash
   */
  async stake(poolName: ContractName, poolId: Number, amount: BigNumber): Promise<TransactionResponse> {
    const pool = this.contracts[poolName];
    return await pool.deposit(poolId, amount);
  }

  /**
   * Withdraws token from given pool.
   * @param poolName A name of pool contract.
   * @param amount Number of tokens with decimals applied. (e.g. 1.45 DAI * 10^18)
   * @returns {string} Transaction hash
   */
  async unstake(poolName: ContractName, poolId: Number, amount: BigNumber): Promise<TransactionResponse> {
    const pool = this.contracts[poolName];
    return await pool.withdraw(poolId, amount);
  }

  /**
   * Transfers earned token reward from given pool to my account.
   */
  async harvest(poolName: ContractName, poolId: Number): Promise<TransactionResponse> {
    const pool = this.contracts[poolName];
    //By passing 0 as the amount, we are asking the contract to only redeem the reward and not the currently staked token
    return await pool.withdraw(poolId, 0);
  }

  /**
   * Harvests and withdraws deposited tokens from the pool.
   */
  async exit(poolName: ContractName, poolId: Number, account = this.myAccount): Promise<TransactionResponse> {
    const pool = this.contracts[poolName];
    let userInfo = await pool.userInfo(poolId, account);
    return await pool.withdraw(poolId, userInfo.amount);
  }

  async fetchStakingVersionOfUser(): Promise<string> {
    return 'latest';
  }

  currentStaking(): Contract {
    if (!this.stakingVersionOfUser) {
      //throw new Error('you must unlock the wallet to continue.');
    }
    return this.contracts.Staking;
  }

  isOldStakingMember(): boolean {
    return this.stakingVersionOfUser !== 'latest';
  }

  async getTokenPriceFromPancakeswap(tokenContract: ERC20): Promise<string> {
    const ready = await this.provider.ready;
    if (!ready) return;
    const { chainId } = this.config;
    const { WXDC } = this.config.externalTokens;

    const wxdc = new Token(chainId, WXDC[0], WXDC[1]);
    const token = new Token(chainId, tokenContract.address, tokenContract.decimal, tokenContract.symbol);
    try {
      const wxdcToToken = await Fetcher.fetchPairData(wxdc, token, this.provider);
      const priceInBUSD = new Route([wxdcToToken], token);

      return priceInBUSD.midPrice.toFixed(4);
    } catch (err) {
      console.error(`Failed to fetch token price of ${tokenContract.symbol}: ${err}`);
    }
  }

  async getTokenPriceFromSpiritswap(tokenContract: ERC20): Promise<string> {
    const ready = await this.provider.ready;
    if (!ready) return;
    const { chainId } = this.config;

    const { WXDC } = this.externalTokens;

    const wxdc = new TokenSpirit(chainId, WXDC.address, WXDC.decimal);
    const token = new TokenSpirit(chainId, tokenContract.address, tokenContract.decimal, tokenContract.symbol);
    try {
      const wxdcToToken = await FetcherSpirit.fetchPairData(wxdc, token, this.provider);
      const liquidityToken = wxdcToToken.liquidityToken;
      let xdcBalanceInLP = await WXDC.balanceOf(liquidityToken.address);
      let xdcAmount = Number(getFullDisplayBalance(xdcBalanceInLP, WXDC.decimal));
      let shibaBalanceInLP = await tokenContract.balanceOf(liquidityToken.address);
      let shibaAmount = Number(getFullDisplayBalance(shibaBalanceInLP, tokenContract.decimal));
      const priceOfOneFtmInDollars = await this.getWXDCPriceFromPancakeswap();
      let priceOfShiba = (xdcAmount / shibaAmount) * Number(priceOfOneFtmInDollars);
      return priceOfShiba.toString();
    } catch (err) {
      console.error(`Failed to fetch token price of ${tokenContract.symbol}: ${err}`);
    }
  }

  async getWXDCPriceFromPancakeswap(): Promise<string> {
    const ready = await this.provider.ready;
    if (!ready) return;
    const { WXDC, FUSDT } = this.externalTokens;
    try {
      const fusdt_wxdc_lp_pair = this.externalTokens['USDT-XDC-LP'];
      let xdc_amount_BN = await WXDC.balanceOf(fusdt_wxdc_lp_pair.address);
      let xdc_amount = Number(getFullDisplayBalance(xdc_amount_BN, WXDC.decimal));
      let fusdt_amount_BN = await FUSDT.balanceOf(fusdt_wxdc_lp_pair.address);
      let fusdt_amount = Number(getFullDisplayBalance(fusdt_amount_BN, FUSDT.decimal));
      return (fusdt_amount / xdc_amount).toString();
    } catch (err) {
      console.error(`Failed to fetch token price of WXDC: ${err}`);
    }
  }

  //===================================================================
  //===================================================================
  //===================== STAKING METHODS =============================
  //===================================================================
  //===================================================================

  async getStakingAPR() {
    const Staking = this.currentStaking();
    const latestSnapshotIndex = await Staking.latestSnapshotIndex();
    const lastHistory = await Staking.stakingHistory(latestSnapshotIndex);

    const lastRewardsReceived = lastHistory[1];

    const HSHAREPrice = (await this.getShareStat()).priceInDollars;
    const HAUNTEDPrice = (await this.getHauntedStat()).priceInDollars;
    const epochRewardsPerShare = lastRewardsReceived / 1e18;

    //Mgod formula
    const amountOfRewardsPerDay = epochRewardsPerShare * Number(HAUNTEDPrice) * 4;
    const stakinghShareBalanceOf = await this.HSHARE.balanceOf(Staking.address);
    const stakingTVL = Number(getDisplayBalance(stakinghShareBalanceOf, this.HSHARE.decimal)) * Number(HSHAREPrice);
    const realAPR = ((amountOfRewardsPerDay * 100) / stakingTVL) * 365;
    return realAPR;
  }

  /**
   * Checks if the user is allowed to retrieve their reward from the Staking
   * @returns true if user can withdraw reward, false if they can't
   */
  async canUserClaimRewardFromStaking(): Promise<boolean> {
    const Staking = this.currentStaking();
    return await Staking.canClaimReward(this.myAccount);
  }

  /**
   * Checks if the user is allowed to retrieve their reward from the Staking
   * @returns true if user can withdraw reward, false if they can't
   */
  async canUserUnstakeFromStaking(): Promise<boolean> {
    const Staking = this.currentStaking();
    const canWithdraw = await Staking.canWithdraw(this.myAccount);
    const stakedAmount = await this.getStakedSharesOnStaking();
    const notStaked = Number(getDisplayBalance(stakedAmount, this.HSHARE.decimal)) === 0;
    const result = notStaked ? true : canWithdraw;
    return result;
  }

  async timeUntilClaimRewardFromStaking(): Promise<BigNumber> {
    // const Staking = this.currentStaking();
    // const staker = await Staking.stakers(this.myAccount);
    return BigNumber.from(0);
  }

  async getTotalStakedInStaking(): Promise<BigNumber> {
    const Staking = this.currentStaking();
    return await Staking.totalSupply();
  }

  async stakeShareToStaking(amount: string): Promise<TransactionResponse> {
    if (this.isOldStakingMember()) {
      throw new Error("you're using old staking. please withdraw and deposit the HSHARE again.");
    }
    const Staking = this.currentStaking();
    return await Staking.stake(decimalToBalance(amount));
  }

  async getStakedSharesOnStaking(): Promise<BigNumber> {
    const Staking = this.currentStaking();
    if (this.stakingVersionOfUser === 'v1') {
      return await Staking.getShareOf(this.myAccount);
    }
    return await Staking.balanceOf(this.myAccount);
  }

  async getEarningsOnStaking(): Promise<BigNumber> {
    const Staking = this.currentStaking();
    if (this.stakingVersionOfUser === 'v1') {
      return await Staking.getCashEarningsOf(this.myAccount);
    }
    return await Staking.earned(this.myAccount);
  }

  async withdrawShareFromStaking(amount: string): Promise<TransactionResponse> {
    const Staking = this.currentStaking();
    return await Staking.withdraw(decimalToBalance(amount));
  }

  async harvestCashFromStaking(): Promise<TransactionResponse> {
    const Staking = this.currentStaking();
    if (this.stakingVersionOfUser === 'v1') {
      return await Staking.claimDividends();
    }
    return await Staking.claimReward();
  }

  async exitFromStaking(): Promise<TransactionResponse> {
    const Staking = this.currentStaking();
    return await Staking.exit();
  }

  async getTreasuryNextAllocationTime(): Promise<AllocationTime> {
    const { Treasury } = this.contracts;
    const nextEpochTimestamp: BigNumber = await Treasury.nextEpochPoint();
    const nextAllocation = new Date(nextEpochTimestamp.mul(1000).toNumber());
    const prevAllocation = new Date(Date.now());

    return { from: prevAllocation, to: nextAllocation };
  }
  /**
   * This method calculates and returns in a from to to format
   * the period the user needs to wait before being allowed to claim
   * their reward from the staking
   * @returns Promise<AllocationTime>
   */
  async getUserClaimRewardTime(): Promise<AllocationTime> {
    const { Staking, Treasury } = this.contracts;
    const nextEpochTimestamp = await Staking.nextEpochPoint(); //in unix timestamp
    const currentEpoch = await Staking.epoch();
    const staker = await Staking.stakers(this.myAccount);
    const startTimeEpoch = staker.epochTimerStart;
    const period = await Treasury.PERIOD();
    const periodInHours = period / 60 / 60; // 6 hours, period is displayed in seconds which is 21600
    const rewardLockupEpochs = await Staking.rewardLockupEpochs();
    const targetEpochForClaimUnlock = Number(startTimeEpoch) + Number(rewardLockupEpochs);

    const fromDate = new Date(Date.now());
    if (targetEpochForClaimUnlock - currentEpoch <= 0) {
      return { from: fromDate, to: fromDate };
    } else if (targetEpochForClaimUnlock - currentEpoch === 1) {
      const toDate = new Date(nextEpochTimestamp * 1000);
      return { from: fromDate, to: toDate };
    } else {
      const toDate = new Date(nextEpochTimestamp * 1000);
      const delta = targetEpochForClaimUnlock - currentEpoch - 1;
      const endDate = moment(toDate)
        .add(delta * periodInHours, 'hours')
        .toDate();
      return { from: fromDate, to: endDate };
    }
  }

  /**
   * This method calculates and returns in a from to to format
   * the period the user needs to wait before being allowed to unstake
   * from the staking
   * @returns Promise<AllocationTime>
   */
  async getUserUnstakeTime(): Promise<AllocationTime> {
    const { Staking, Treasury } = this.contracts;
    const nextEpochTimestamp = await Staking.nextEpochPoint();
    const currentEpoch = await Staking.epoch();
    const staker = await Staking.stakers(this.myAccount);
    const startTimeEpoch = staker.epochTimerStart;
    const period = await Treasury.PERIOD();
    const PeriodInHours = period / 60 / 60;
    const withdrawLockupEpochs = await Staking.withdrawLockupEpochs();
    const fromDate = new Date(Date.now());
    const targetEpochForClaimUnlock = Number(startTimeEpoch) + Number(withdrawLockupEpochs);
    const stakedAmount = await this.getStakedSharesOnStaking();
    if (currentEpoch <= targetEpochForClaimUnlock && Number(stakedAmount) === 0) {
      return { from: fromDate, to: fromDate };
    } else if (targetEpochForClaimUnlock - currentEpoch === 1) {
      const toDate = new Date(nextEpochTimestamp * 1000);
      return { from: fromDate, to: toDate };
    } else {
      const toDate = new Date(nextEpochTimestamp * 1000);
      const delta = targetEpochForClaimUnlock - Number(currentEpoch) - 1;
      const endDate = moment(toDate)
        .add(delta * PeriodInHours, 'hours')
        .toDate();
      return { from: fromDate, to: endDate };
    }
  }

  async watchAssetInMetamask(assetName: string): Promise<boolean> {
    const { ethereum } = window as any;
    if (ethereum && ethereum.networkVersion === config.chainId.toString()) {
      let asset;
      let assetUrl;
      if (assetName === 'HAUNTED') {
        asset = this.HAUNTED;
        assetUrl = 'https://haunted.finance/presskit/haunted_icon_noBG.png';
      } else if (assetName === 'HSHARE') {
        asset = this.HSHARE;
        assetUrl = 'https://haunted.finance/presskit/hshare_icon_noBG.png';
      } else if (assetName === 'HBOND') {
        asset = this.HBOND;
        assetUrl = 'https://haunted.finance/presskit/hbond_icon_noBG.png';
      }
      await ethereum.request({
        method: 'wallet_watchAsset',
        params: {
          type: 'ERC20',
          options: {
            address: asset.address,
            symbol: asset.symbol,
            decimals: 18,
            image: assetUrl,
          },
        },
      });
    }
    return true;
  }

  async provideHauntedFtmLP(xdcAmount: string, hauntedAmount: BigNumber): Promise<TransactionResponse> {
    const { TaxOffice } = this.contracts;
    let overrides = {
      value: parseUnits(xdcAmount, 18),
    };
    return await TaxOffice.addLiquidityETHTaxFree(hauntedAmount, hauntedAmount.mul(992).div(1000), parseUnits(xdcAmount, 18).mul(992).div(1000), overrides);
  }

  async quoteFromSpooky(tokenAmount: string, tokenName: string): Promise<string> {
    const { SpookyRouter } = this.contracts;
    const { _reserve0, _reserve1 } = await this.HAUNTEDWXDC_LP.getReserves();
    let quote;
    if (tokenName === 'HAUNTED') {
      quote = await SpookyRouter.quote(parseUnits(tokenAmount), _reserve1, _reserve0);
    } else {
      quote = await SpookyRouter.quote(parseUnits(tokenAmount), _reserve0, _reserve1);
    }
    return (quote / 1e18).toString();
  }

  /**
   * @returns an array of the regulation events till the most up to date epoch
   */
  async listenForRegulationsEvents(): Promise<any> {
    const { Treasury } = this.contracts;

    const treasuryDaoFundedFilter = Treasury.filters.DaoFundFunded();
    const treasuryDevFundedFilter = Treasury.filters.DevFundFunded();
    const treasuryStakingFundedFilter = Treasury.filters.StakingFunded();
    const boughhBondsFilter = Treasury.filters.BoughhBonds();
    const redeemBondsFilter = Treasury.filters.RedeemedBonds();

    let epochBlocksRanges: any[] = [];
    let stakingFundEvents = await Treasury.queryFilter(treasuryStakingFundedFilter);
    var events: any[] = [];
    stakingFundEvents.forEach(function callback(value, index) {
      events.push({ epoch: index + 1 });
      events[index].stakingFund = getDisplayBalance(value.args[1]);
      if (index === 0) {
        epochBlocksRanges.push({
          index: index,
          startBlock: value.blockNumber,
          boughBonds: 0,
          redeemedBonds: 0,
        });
      }
      if (index > 0) {
        epochBlocksRanges.push({
          index: index,
          startBlock: value.blockNumber,
          boughBonds: 0,
          redeemedBonds: 0,
        });
        epochBlocksRanges[index - 1].endBlock = value.blockNumber;
      }
    });

    epochBlocksRanges.forEach(async (value, index) => {
      events[index].bondsBought = await this.getBondsWithFilterForPeriod(
        boughhBondsFilter,
        value.startBlock,
        value.endBlock,
      );
      events[index].bondsRedeemed = await this.getBondsWithFilterForPeriod(
        redeemBondsFilter,
        value.startBlock,
        value.endBlock,
      );
    });
    let DEVFundEvents = await Treasury.queryFilter(treasuryDevFundedFilter);
    DEVFundEvents.forEach(function callback(value, index) {
      events[index].devFund = getDisplayBalance(value.args[1]);
    });
    let DAOFundEvents = await Treasury.queryFilter(treasuryDaoFundedFilter);
    DAOFundEvents.forEach(function callback(value, index) {
      events[index].daoFund = getDisplayBalance(value.args[1]);
    });
    return events;
  }

  /**
   * Helper method
   * @param filter applied on the query to the treasury events
   * @param from block number
   * @param to block number
   * @returns the amount of bonds events emitted based on the filter provided during a specific period
   */
  async getBondsWithFilterForPeriod(filter: EventFilter, from: number, to: number): Promise<number> {
    const { Treasury } = this.contracts;
    const bondsAmount = await Treasury.queryFilter(filter, from, to);
    return bondsAmount.length;
  }

  async estimateZapIn(tokenName: string, lpName: string, amount: string): Promise<number[]> {
    const { zapper } = this.contracts;
    const lpToken = this.externalTokens[lpName];
    let estimate;
    if (tokenName === XDC_TICKER) {
      estimate = await zapper.estimateZapIn(lpToken.address, SPOOKY_ROUTER_ADDR, parseUnits(amount, 18));
    } else {
      const token = tokenName === HAUNTED_TICKER ? this.HAUNTED : this.HSHARE;
      estimate = await zapper.estimateZapInToken(
        token.address,
        lpToken.address,
        SPOOKY_ROUTER_ADDR,
        parseUnits(amount, 18),
      );
    }
    return [estimate[0] / 1e18, estimate[1] / 1e18];
  }
  async zapIn(tokenName: string, lpName: string, amount: string): Promise<TransactionResponse> {
    const { zapper } = this.contracts;
    const lpToken = this.externalTokens[lpName];
    if (tokenName === XDC_TICKER) {
      let overrides = {
        value: parseUnits(amount, 18),
      };
      return await zapper.zapIn(lpToken.address, SPOOKY_ROUTER_ADDR, this.myAccount, overrides);
    } else {
      const token = tokenName === HAUNTED_TICKER ? this.HAUNTED : this.HSHARE;
      return await zapper.zapInToken(
        token.address,
        parseUnits(amount, 18),
        lpToken.address,
        SPOOKY_ROUTER_ADDR,
        this.myAccount,
      );
    }
  }
  async swapHBondToHShare(hbondAmount: BigNumber): Promise<TransactionResponse> {
    const { HShareSwapper } = this.contracts;
    return await HShareSwapper.swapHBondToHShare(hbondAmount);
  }
  async estimateAmountOfHShare(hbondAmount: string): Promise<string> {
    const { HShareSwapper } = this.contracts;
    try {
      const estimateBN = await HShareSwapper.estimateAmountOfHShare(parseUnits(hbondAmount, 18));
      return getDisplayBalance(estimateBN, 18, 6);
    } catch (err) {
      console.error(`Failed to fetch estimate hshare amount: ${err}`);
    }
  }

  async getHShareSwapperStat(address: string): Promise<HShareSwapperStat> {
    const { HShareSwapper } = this.contracts;
    const hshareBalanceBN = await HShareSwapper.getHShareBalance();
    const hbondBalanceBN = await HShareSwapper.getHBondBalance(address);
    // const hauntedPriceBN = await HShareSwapper.getHauntedPrice();
    // const hsharePriceBN = await HShareSwapper.getHSharePrice();
    const rateHSharePerHauntedBN = await HShareSwapper.getHShareAmountPerHaunted();
    const hshareBalance = getDisplayBalance(hshareBalanceBN, 18, 5);
    const hbondBalance = getDisplayBalance(hbondBalanceBN, 18, 5);
    return {
      hshareBalance: hshareBalance.toString(),
      hbondBalance: hbondBalance.toString(),
      // hauntedPrice: hauntedPriceBN.toString(),
      // hsharePrice: hsharePriceBN.toString(),
      rateHSharePerHaunted: rateHSharePerHauntedBN.toString(),
    };
  }
}
