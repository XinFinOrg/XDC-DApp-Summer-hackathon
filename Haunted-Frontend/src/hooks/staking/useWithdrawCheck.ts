import { useEffect, useState } from 'react';
import useHauntedFinance from '../useHauntedFinance';
import useRefresh from '../useRefresh';

const useWithdrawCheck = () => {
  const [canWithdraw, setCanWithdraw] = useState(false);
  const hauntedFinance = useHauntedFinance();
  const { slowRefresh } = useRefresh();
  const isUnlocked = hauntedFinance?.isUnlocked;

  useEffect(() => {
    async function canUserWithdraw() {
      try {
        setCanWithdraw(await hauntedFinance.canUserUnstakeFromStaking());
      } catch (err) {
        console.error(err);
      }
    }
    if (isUnlocked) {
      canUserWithdraw();
    }
  }, [isUnlocked, hauntedFinance, slowRefresh]);

  return canWithdraw;
};

export default useWithdrawCheck;
