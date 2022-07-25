import { useCallback, useEffect, useState } from 'react';
import useHauntedFinance from './useHauntedFinance';
import useStakedBalanceOnStaking from './useStakedBalanceOnStaking';

const useStakingVersion = () => {
  const [stakingVersion, setStakingVersion] = useState('latest');
  const hauntedFinance = useHauntedFinance();
  const stakedBalance = useStakedBalanceOnStaking();

  const updateState = useCallback(async () => {
    setStakingVersion(await hauntedFinance.fetchStakingVersionOfUser());
  }, [hauntedFinance?.isUnlocked, stakedBalance]);

  useEffect(() => {
    if (hauntedFinance?.isUnlocked) {
      updateState().catch((err) => console.error(err.stack));
    }
  }, [hauntedFinance?.isUnlocked, stakedBalance]);

  return stakingVersion;
};

export default useStakingVersion;
