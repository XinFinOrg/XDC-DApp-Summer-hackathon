import { useEffect, useState } from 'react';
import useHauntedFinance from '../useHauntedFinance';
import { AllocationTime } from '../../haunted-finance/types';

const useUnstakeTimerStaking = () => {
  const [time, setTime] = useState<AllocationTime>({
    from: new Date(),
    to: new Date(),
  });
  const hauntedFinance = useHauntedFinance();

  useEffect(() => {
    if (hauntedFinance) {
      hauntedFinance.getUserUnstakeTime().then(setTime);
    }
  }, [hauntedFinance]);
  return time;
};

export default useUnstakeTimerStaking;
