import { useEffect, useState } from 'react';
import useHauntedFinance from './useHauntedFinance';
import useRefresh from './useRefresh';

const useTotalValueLocked = () => {
  const [totalValueLocked, setTotalValueLocked] = useState<Number>(0);
  const { slowRefresh } = useRefresh();
  const hauntedFinance = useHauntedFinance();

  useEffect(() => {
    async function fetchTVL() {
      try {
        setTotalValueLocked(await hauntedFinance.getTotalValueLocked());
      }
      catch(err){
        console.error(err);
      }
    }
    fetchTVL();
  }, [setTotalValueLocked, hauntedFinance, slowRefresh]);

  return totalValueLocked;
};

export default useTotalValueLocked;
