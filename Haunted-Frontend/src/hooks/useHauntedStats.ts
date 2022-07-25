import { useEffect, useState } from 'react';
import useHauntedFinance from './useHauntedFinance';
import { TokenStat } from '../haunted-finance/types';
import useRefresh from './useRefresh';

const useHauntedStats = () => {
  const [stat, setStat] = useState<TokenStat>();
  const { fastRefresh } = useRefresh();
  const hauntedFinance = useHauntedFinance();

  useEffect(() => {
    async function fetchHauntedPrice(){
      try {
        setStat(await hauntedFinance.getHauntedStat());
      }
      catch(err){
        console.error(err)
      }
    }
    fetchHauntedPrice();
  }, [setStat, hauntedFinance, fastRefresh]);

  return stat;
};

export default useHauntedStats;
