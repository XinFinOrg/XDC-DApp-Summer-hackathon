import { useCallback } from 'react';
import useHauntedFinance from '../useHauntedFinance';
import useHandleTransactionReceipt from '../useHandleTransactionReceipt';
// import { BigNumber } from "ethers";
import { parseUnits } from 'ethers/lib/utils';


const useSwapHBondToHShare = () => {
  const hauntedFinance = useHauntedFinance();
  const handleTransactionReceipt = useHandleTransactionReceipt();

  const handleSwapHShare = useCallback(
  	(hbondAmount: string) => {
	  	const hbondAmountBn = parseUnits(hbondAmount, 18);
	  	handleTransactionReceipt(
	  		hauntedFinance.swapHBondToHShare(hbondAmountBn),
	  		`Swap ${hbondAmount} HBond to HShare`
	  	);
  	},
  	[hauntedFinance, handleTransactionReceipt]
  );
  return { onSwapHShare: handleSwapHShare };
};

export default useSwapHBondToHShare;