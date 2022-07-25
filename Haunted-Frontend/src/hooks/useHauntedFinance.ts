import { useContext } from 'react';
import { Context } from '../contexts/HauntedFinanceProvider';

const useHauntedFinance = () => {
  const { hauntedFinance } = useContext(Context);
  return hauntedFinance;
};

export default useHauntedFinance;
