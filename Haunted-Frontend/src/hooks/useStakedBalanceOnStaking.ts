import { useEffect, useState } from 'react';
import { BigNumber } from 'ethers';
import useHauntedFinance from './useHauntedFinance';
import useRefresh from './useRefresh';

const useStakedBalanceOnStaking = () => {
  const { slowRefresh } = useRefresh();
  const [balance, setBalance] = useState(BigNumber.from(0));
  const hauntedFinance = useHauntedFinance();
  const isUnlocked = hauntedFinance?.isUnlocked;
  useEffect(() => {
    async function fetchBalance() {
      try {
        setBalance(await hauntedFinance.getStakedSharesOnStaking());
      } catch (e) {
        console.error(e);
      }
    }
    if (isUnlocked) {
      fetchBalance();
    }
  }, [slowRefresh, isUnlocked, hauntedFinance]);
  return balance;
};

export default useStakedBalanceOnStaking;
