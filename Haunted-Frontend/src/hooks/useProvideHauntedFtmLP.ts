import { useCallback } from 'react';
import useHauntedFinance from './useHauntedFinance';
import useHandleTransactionReceipt from './useHandleTransactionReceipt';
import { parseUnits } from 'ethers/lib/utils';
import { TAX_OFFICE_ADDR } from '../utils/constants'

const useProvideHauntedFtmLP = () => {
  const hauntedFinance = useHauntedFinance();
  const handleTransactionReceipt = useHandleTransactionReceipt();

  const handleProvideHauntedFtmLP = useCallback(
    (xdcAmount: string, hauntedAmount: string) => {
      const hauntedAmountBn = parseUnits(hauntedAmount);
      handleTransactionReceipt(
        hauntedFinance.provideHauntedFtmLP(xdcAmount, hauntedAmountBn),
        `Provide Haunted-XDC LP ${hauntedAmount} ${xdcAmount} using ${TAX_OFFICE_ADDR}`,
      );
    },
    [hauntedFinance, handleTransactionReceipt],
  );
  return { onProvideHauntedFtmLP: handleProvideHauntedFtmLP };
};

export default useProvideHauntedFtmLP;
