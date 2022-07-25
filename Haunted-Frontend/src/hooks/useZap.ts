import { useCallback } from 'react';
import useHauntedFinance from './useHauntedFinance';
import { Bank } from '../haunted-finance';
import useHandleTransactionReceipt from './useHandleTransactionReceipt';

const useZap = (bank: Bank) => {
  const hauntedFinance = useHauntedFinance();
  const handleTransactionReceipt = useHandleTransactionReceipt();

  const handleZap = useCallback(
    (zappingToken: string, tokenName: string, amount: string) => {
      handleTransactionReceipt(
        hauntedFinance.zapIn(zappingToken, tokenName, amount),
        `Zap ${amount} in ${bank.depositTokenName}.`,
      );
    },
    [bank, hauntedFinance, handleTransactionReceipt],
  );
  return { onZap: handleZap };
};

export default useZap;
