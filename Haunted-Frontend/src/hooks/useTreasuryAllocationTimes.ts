import { useEffect, useState } from 'react';
import useHauntedFinance from './useHauntedFinance';
import { AllocationTime } from '../haunted-finance/types';
import useRefresh from './useRefresh';


const useTreasuryAllocationTimes = () => {
  const { slowRefresh } = useRefresh();
  const [time, setTime] = useState<AllocationTime>({
    from: new Date(),
    to: new Date(),
  });
  const hauntedFinance = useHauntedFinance();
  useEffect(() => {
    if (hauntedFinance) {
      hauntedFinance.getTreasuryNextAllocationTime().then(setTime);
    }
  }, [hauntedFinance, slowRefresh]);
  return time;
};

export default useTreasuryAllocationTimes;
