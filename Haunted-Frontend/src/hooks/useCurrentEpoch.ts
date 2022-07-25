import { useEffect, useState } from 'react';
import useHauntedFinance from './useHauntedFinance';
import { BigNumber } from 'ethers';
import useRefresh from './useRefresh';

const useCurrentEpoch = () => {
  const [currentEpoch, setCurrentEpoch] = useState<BigNumber>(BigNumber.from(0));
  const hauntedFinance = useHauntedFinance();
  const { slowRefresh } = useRefresh(); 

  useEffect(() => {
    async function fetchCurrentEpoch () {
      try {
        setCurrentEpoch(await hauntedFinance.getCurrentEpoch());
      } catch(err) {
        console.error(err);
      }
    }
    fetchCurrentEpoch();
  }, [setCurrentEpoch, hauntedFinance, slowRefresh]);

  return currentEpoch;
};

export default useCurrentEpoch;
