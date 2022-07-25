import { useEffect, useState } from 'react';
import useHauntedFinance from './useHauntedFinance';
import useRefresh from './useRefresh';

const useFetchStakingAPR = () => {
  const [apr, setApr] = useState<number>(0);
  const hauntedFinance = useHauntedFinance();
  const { slowRefresh } = useRefresh(); 

  useEffect(() => {
    async function fetchStakingAPR() {
      try {
        setApr(await hauntedFinance.getStakingAPR());
      } catch(err){
        console.error(err);
      }
    }
   fetchStakingAPR();
  }, [setApr, hauntedFinance, slowRefresh]);

  return apr;
};

export default useFetchStakingAPR;
