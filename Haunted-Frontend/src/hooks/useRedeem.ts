import { useCallback } from 'react';
import useHauntedFinance from './useHauntedFinance';
import { Bank } from '../haunted-finance';
import useHandleTransactionReceipt from './useHandleTransactionReceipt';

const useRedeem = (bank: Bank) => {
  const hauntedFinance = useHauntedFinance();
  const handleTransactionReceipt = useHandleTransactionReceipt();

  const handleRedeem = useCallback(() => {
    handleTransactionReceipt(hauntedFinance.exit(bank.contract, bank.poolId), `Redeem ${bank.contract}`);
  }, [bank, hauntedFinance, handleTransactionReceipt]);

  return { onRedeem: handleRedeem };
};

export default useRedeem;
