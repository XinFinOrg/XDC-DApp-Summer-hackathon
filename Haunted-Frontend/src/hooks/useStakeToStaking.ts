import { useCallback } from 'react';
import useHauntedFinance from './useHauntedFinance';
import useHandleTransactionReceipt from './useHandleTransactionReceipt';

const useStakeToStaking = () => {
  const hauntedFinance = useHauntedFinance();
  const handleTransactionReceipt = useHandleTransactionReceipt();

  const handleStake = useCallback(
    (amount: string) => {
      handleTransactionReceipt(hauntedFinance.stakeShareToStaking(amount), `Stake ${amount} HSHARE to the staking`);
    },
    [hauntedFinance, handleTransactionReceipt],
  );
  return { onStake: handleStake };
};

export default useStakeToStaking;
