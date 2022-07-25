import { useEffect, useState } from 'react';
import useHauntedFinance from './useHauntedFinance';
import { TokenStat } from '../haunted-finance/types';
import useRefresh from './useRefresh';

const useCashPriceInEstimatedTWAP = () => {
  const [stat, setStat] = useState<TokenStat>();
  const hauntedFinance = useHauntedFinance();
  const { slowRefresh } = useRefresh(); 

  useEffect(() => {
    async function fetcHAUNTEDPrice() {
      try {
        setStat(await hauntedFinance.getHauntedStatInEstimatedTWAP());
      }catch(err) {
        console.error(err);
      }
    }
    fetcHAUNTEDPrice();
  }, [setStat, hauntedFinance, slowRefresh]);

  return stat;
};

export default useCashPriceInEstimatedTWAP;
