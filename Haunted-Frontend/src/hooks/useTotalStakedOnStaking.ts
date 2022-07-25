import { useEffect, useState } from 'react';
import { BigNumber } from 'ethers';
import useHauntedFinance from './useHauntedFinance';
import useRefresh from './useRefresh';

const useTotalStakedOnStaking = () => {
  const [totalStaked, setTotalStaked] = useState(BigNumber.from(0));
  const hauntedFinance = useHauntedFinance();
  const { slowRefresh } = useRefresh();
  const isUnlocked = hauntedFinance?.isUnlocked;

  useEffect(() => {
    async function fetchTotalStaked() {
      try {
        setTotalStaked(await hauntedFinance.getTotalStakedInStaking());
      } catch(err) {
        console.error(err);
      }
    }
    if (isUnlocked) {
     fetchTotalStaked();
    }
  }, [isUnlocked, slowRefresh, hauntedFinance]);

  return totalStaked;
};

export default useTotalStakedOnStaking;
