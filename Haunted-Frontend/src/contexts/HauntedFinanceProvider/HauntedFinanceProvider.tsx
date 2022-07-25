import React, { createContext, useEffect, useState } from 'react';
import { useWallet } from 'use-wallet';
import HauntedFinance from '../../haunted-finance';
import config from '../../config';

export interface HauntedFinanceContext {
  hauntedFinance?: HauntedFinance;
}

export const Context = createContext<HauntedFinanceContext>({ hauntedFinance: null });

export const HauntedFinanceProvider: React.FC = ({ children }) => {
  const { ethereum, account } = useWallet();
  const [hauntedFinance, setHauntedFinance] = useState<HauntedFinance>();

  useEffect(() => {
    if (!hauntedFinance) {
      const haunted = new HauntedFinance(config);
      if (account) {
        // wallet was unlocked at initialization
        haunted.unlockWallet(ethereum, account);
      }
      setHauntedFinance(haunted);
    } else if (account) {
      hauntedFinance.unlockWallet(ethereum, account);
    }
  }, [account, ethereum, hauntedFinance]);

  return <Context.Provider value={{ hauntedFinance }}>{children}</Context.Provider>;
};
