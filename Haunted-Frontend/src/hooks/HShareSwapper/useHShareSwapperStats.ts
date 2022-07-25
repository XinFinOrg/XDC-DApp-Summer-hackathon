import { useEffect, useState } from 'react';
import useHauntedFinance from '../useHauntedFinance';
import { HShareSwapperStat } from '../../haunted-finance/types';
import useRefresh from '../useRefresh';

const useHShareSwapperStats = (account: string) => {
  const [stat, setStat] = useState<HShareSwapperStat>();
  const { fastRefresh/*, slowRefresh*/ } = useRefresh();
  const hauntedFinance = useHauntedFinance();

  useEffect(() => {
    async function fetchHShareSwapperStat() {
      try{
        if(hauntedFinance.myAccount) {
          setStat(await hauntedFinance.getHShareSwapperStat(account));
        }
      }
      catch(err){
        console.error(err);
      }
    }
    fetchHShareSwapperStat();
  }, [setStat, hauntedFinance, fastRefresh, account]);

  return stat;
};

export default useHShareSwapperStats;