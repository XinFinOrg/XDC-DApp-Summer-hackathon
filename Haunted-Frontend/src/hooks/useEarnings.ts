import { useCallback, useEffect, useState } from 'react';
import { BigNumber } from 'ethers';
import useHauntedFinance from './useHauntedFinance';
import { ContractName } from '../haunted-finance';
import config from '../config';

const useEarnings = (poolName: ContractName, earnTokenName: String, poolId: Number) => {
  const [balance, setBalance] = useState(BigNumber.from(0));
  const hauntedFinance = useHauntedFinance();
  const isUnlocked = hauntedFinance?.isUnlocked;

  const fetchBalance = useCallback(async () => {
    const balance = await hauntedFinance.earnedFromBank(poolName, earnTokenName, poolId, hauntedFinance.myAccount);
    setBalance(balance);
  }, [poolName, earnTokenName, poolId, hauntedFinance]);

  useEffect(() => {
    if (isUnlocked) {
      fetchBalance().catch((err) => console.error(err.stack));

      const refreshBalance = setInterval(fetchBalance, config.refreshInterval);
      return () => clearInterval(refreshBalance);
    }
  }, [isUnlocked, poolName, hauntedFinance, fetchBalance]);

  return balance;
};

export default useEarnings;
