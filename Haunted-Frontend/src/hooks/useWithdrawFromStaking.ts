import { useCallback } from 'react';
import useHauntedFinance from './useHauntedFinance';
import useHandleTransactionReceipt from './useHandleTransactionReceipt';

const useWithdrawFromStaking = () => {
  const hauntedFinance = useHauntedFinance();
  const handleTransactionReceipt = useHandleTransactionReceipt();

  const handleWithdraw = useCallback(
    (amount: string) => {
      handleTransactionReceipt(
        hauntedFinance.withdrawShareFromStaking(amount),
        `Withdraw ${amount} HSHARE from the staking`,
      );
    },
    [hauntedFinance, handleTransactionReceipt],
  );
  return { onWithdraw: handleWithdraw };
};

export default useWithdrawFromStaking;
