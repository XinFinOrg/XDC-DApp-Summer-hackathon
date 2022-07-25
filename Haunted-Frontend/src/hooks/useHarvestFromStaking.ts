import { useCallback } from 'react';
import useHauntedFinance from './useHauntedFinance';
import useHandleTransactionReceipt from './useHandleTransactionReceipt';

const useHarvestFromStaking = () => {
  const hauntedFinance = useHauntedFinance();
  const handleTransactionReceipt = useHandleTransactionReceipt();

  const handleReward = useCallback(() => {
    handleTransactionReceipt(hauntedFinance.harvestCashFromStaking(), 'Claim HAUNTED from Staking');
  }, [hauntedFinance, handleTransactionReceipt]);

  return { onReward: handleReward };
};

export default useHarvestFromStaking;
