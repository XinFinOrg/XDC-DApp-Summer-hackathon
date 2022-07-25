import { useCallback } from 'react';
import useHauntedFinance from './useHauntedFinance';
import useHandleTransactionReceipt from './useHandleTransactionReceipt';
import { Bank } from '../haunted-finance';

const useHarvest = (bank: Bank) => {
  const hauntedFinance = useHauntedFinance();
  const handleTransactionReceipt = useHandleTransactionReceipt();

  const handleReward = useCallback(() => {
    handleTransactionReceipt(
      hauntedFinance.harvest(bank.contract, bank.poolId),
      `Claim ${bank.earnTokenName} from ${bank.contract}`,
    );
  }, [bank, hauntedFinance, handleTransactionReceipt]);

  return { onReward: handleReward };
};

export default useHarvest;
