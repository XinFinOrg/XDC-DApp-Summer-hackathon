import { useEffect, useState } from 'react';
import { BigNumber } from 'ethers';
import useHauntedFinance from './useHauntedFinance';

const useTreasuryAmount = () => {
  const [amount, setAmount] = useState(BigNumber.from(0));
  const hauntedFinance = useHauntedFinance();

  useEffect(() => {
    if (hauntedFinance) {
      const { Treasury } = hauntedFinance.contracts;
      hauntedFinance.HAUNTED.balanceOf(Treasury.address).then(setAmount);
    }
  }, [hauntedFinance]);
  return amount;
};

export default useTreasuryAmount;
