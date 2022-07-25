import { useEffect, useState } from 'react';
import useHauntedFinance from './useHauntedFinance';
import { TokenStat } from '../haunted-finance/types';
import useRefresh from './useRefresh';

const useShareStats = () => {
  const [stat, setStat] = useState<TokenStat>();
  const { slowRefresh } = useRefresh();
  const hauntedFinance = useHauntedFinance();

  useEffect(() => {
    async function fetchSharePrice() {
      try {
        setStat(await hauntedFinance.getShareStat());
      } catch(err){
        console.error(err)
      }
    }
    fetchSharePrice();
  }, [setStat, hauntedFinance, slowRefresh]);

  return stat;
};

export default useShareStats;
