import { useCallback, useEffect, useState } from 'react';
import useHauntedFinance from '../useHauntedFinance';
import { useWallet } from 'use-wallet';
import { BigNumber } from 'ethers';
import { parseUnits } from 'ethers/lib/utils';

const useEstimateHShare = (hbondAmount: string) => {
  const [estimateAmount, setEstimateAmount] = useState<string>('');
  const { account } = useWallet();
  const hauntedFinance = useHauntedFinance();

  const estimateAmountOfHShare = useCallback(async () => {
    const hbondAmountBn = parseUnits(hbondAmount);
    const amount = await hauntedFinance.estimateAmountOfHShare(hbondAmountBn.toString());
    setEstimateAmount(amount);
  }, [account]);

  useEffect(() => {
    if (account) {
      estimateAmountOfHShare().catch((err) => console.error(`Failed to get estimateAmountOfHShare: ${err.stack}`));
    }
  }, [account, estimateAmountOfHShare]);

  return estimateAmount;
};

export default useEstimateHShare;