import { useEffect, useState } from 'react';
import useHauntedFinance from './useHauntedFinance';
import { LPStat } from '../haunted-finance/types';
import useRefresh from './useRefresh';

const useLpStats = (lpTicker: string) => {
  const [stat, setStat] = useState<LPStat>();
  const { slowRefresh } = useRefresh();
  const hauntedFinance = useHauntedFinance();

  useEffect(() => {
    async function fetchLpPrice() {
      try{
        setStat(await hauntedFinance.getLPStat(lpTicker));
      }
      catch(err){
        console.error(err);
      }
    }
    fetchLpPrice();
  }, [setStat, hauntedFinance, slowRefresh, lpTicker]);

  return stat;
};

export default useLpStats;
