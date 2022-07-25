import { useEffect, useState } from 'react';
import useHauntedFinance from './useHauntedFinance';
import { TokenStat } from '../haunted-finance/types';
import useRefresh from './useRefresh';

const useBondStats = () => {
  const [stat, setStat] = useState<TokenStat>();
  const { slowRefresh } = useRefresh();
  const hauntedFinance = useHauntedFinance();

  useEffect(() => {
    async function fetchBondPrice() {
      try {
        setStat(await hauntedFinance.getBondStat());
      }
      catch(err){
        console.error(err);
      }
    }
    fetchBondPrice();
  }, [setStat, hauntedFinance, slowRefresh]);

  return stat;
};

export default useBondStats;
