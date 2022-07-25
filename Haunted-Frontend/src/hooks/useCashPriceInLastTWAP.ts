import { useCallback, useEffect, useState } from 'react';
import useHauntedFinance from './useHauntedFinance';
import config from '../config';
import { BigNumber } from 'ethers';

const useCashPriceInLastTWAP = () => {
  const [price, setPrice] = useState<BigNumber>(BigNumber.from(0));
  const hauntedFinance = useHauntedFinance();

  const fetcHAUNTEDPrice = useCallback(async () => {
    setPrice(await hauntedFinance.getHauntedPriceInLastTWAP());
  }, [hauntedFinance]);

  useEffect(() => {
    fetcHAUNTEDPrice().catch((err) => console.error(`Failed to fetch HAUNTED price: ${err.stack}`));
    const refreshInterval = setInterval(fetcHAUNTEDPrice, config.refreshInterval);
    return () => clearInterval(refreshInterval);
  }, [setPrice, hauntedFinance, fetcHAUNTEDPrice]);

  return price;
};

export default useCashPriceInLastTWAP;
