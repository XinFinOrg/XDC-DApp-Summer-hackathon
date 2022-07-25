import { useCallback, useEffect, useState } from 'react';
import { BigNumber } from 'ethers';
import ERC20 from '../haunted-finance/ERC20';
import useHauntedFinance from './useHauntedFinance';
import config from '../config';

const useBondsPurchasable = () => {
  const [balance, setBalance] = useState(BigNumber.from(0));
  const hauntedFinance = useHauntedFinance();

  useEffect(() => {
    async function fetchBondsPurchasable() {
        try {
            setBalance(await hauntedFinance.getBondsPurchasable());
        }
        catch(err) {
            console.error(err);
        }
      }
    fetchBondsPurchasable();
  }, [setBalance, hauntedFinance]);

  return balance;
};

export default useBondsPurchasable;
