import { useCallback, useState, useEffect } from 'react';
import useHauntedFinance from './useHauntedFinance';
import { Bank } from '../haunted-finance';
import { PoolStats } from '../haunted-finance/types';
import config from '../config';

const useStatsForPool = (bank: Bank) => {
  const hauntedFinance = useHauntedFinance();

  const [poolAPRs, setPoolAPRs] = useState<PoolStats>();

  const fetchAPRsForPool = useCallback(async () => {
    setPoolAPRs(await hauntedFinance.getPoolAPRs(bank));
  }, [hauntedFinance, bank]);

  useEffect(() => {
    fetchAPRsForPool().catch((err) => console.error(`Failed to fetch HBOND price: ${err.stack}`));
    const refreshInterval = setInterval(fetchAPRsForPool, config.refreshInterval);
    return () => clearInterval(refreshInterval);
  }, [setPoolAPRs, hauntedFinance, fetchAPRsForPool]);

  return poolAPRs;
};

export default useStatsForPool;
