import React, { useCallback, useEffect, useState } from 'react';
import Context from './context';
import useHauntedFinance from '../../hooks/useHauntedFinance';
import { Bank } from '../../haunted-finance';
import config, { bankDefinitions } from '../../config';

const Banks: React.FC = ({ children }) => {
  const [banks, setBanks] = useState<Bank[]>([]);
  const hauntedFinance = useHauntedFinance();
  const isUnlocked = hauntedFinance?.isUnlocked;

  const fetchPools = useCallback(async () => {
    const banks: Bank[] = [];

    for (const bankInfo of Object.values(bankDefinitions)) {
      if (bankInfo.finished) {
        if (!hauntedFinance.isUnlocked) continue;

        // only show pools staked by user
        const balance = await hauntedFinance.stakedBalanceOnBank(
          bankInfo.contract,
          bankInfo.poolId,
          hauntedFinance.myAccount,
        );
        if (balance.lte(0)) {
          continue;
        }
      }
      banks.push({
        ...bankInfo,
        address: config.deployments[bankInfo.contract].address,
        depositToken: hauntedFinance.externalTokens[bankInfo.depositTokenName],
        earnToken: bankInfo.earnTokenName === 'HAUNTED' ? hauntedFinance.HAUNTED : hauntedFinance.HSHARE,
      });
    }
    banks.sort((a, b) => (a.sort > b.sort ? 1 : -1));
    setBanks(banks);
  }, [hauntedFinance, setBanks]);

  useEffect(() => {
    if (hauntedFinance) {
      fetchPools().catch((err) => console.error(`Failed to fetch pools: ${err.stack}`));
    }
  }, [isUnlocked, hauntedFinance, fetchPools]);

  return <Context.Provider value={{ banks }}>{children}</Context.Provider>;
};

export default Banks;
