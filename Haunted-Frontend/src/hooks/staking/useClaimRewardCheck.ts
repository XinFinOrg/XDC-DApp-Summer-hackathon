import { useEffect, useState } from 'react';
import useRefresh from '../useRefresh';
import useHauntedFinance from '../useHauntedFinance';

const useClaimRewardCheck = () => {
  const  { slowRefresh } = useRefresh();
  const [canClaimReward, setCanClaimReward] = useState(false);
  const hauntedFinance = useHauntedFinance();
  const isUnlocked = hauntedFinance?.isUnlocked;

  useEffect(() => {
    async function canUserClaimReward() {
      try {
        setCanClaimReward(await hauntedFinance.canUserClaimRewardFromStaking());
      } catch(err){
        console.error(err);
      };
    }
    if (isUnlocked) {
      canUserClaimReward();
    }
  }, [isUnlocked, slowRefresh, hauntedFinance]);

  return canClaimReward;
};

export default useClaimRewardCheck;
