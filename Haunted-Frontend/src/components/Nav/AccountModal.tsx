import React, { useMemo } from 'react';
import styled from 'styled-components';
import useTokenBalance from '../../hooks/useTokenBalance';
import { getDisplayBalance } from '../../utils/formatBalance';

import Label from '../Label';
import Modal, { ModalProps } from '../Modal';
import ModalTitle from '../ModalTitle';
import useHauntedFinance from '../../hooks/useHauntedFinance';
import TokenSymbol from '../TokenSymbol';

const AccountModal: React.FC<ModalProps> = ({ onDismiss }) => {
  const hauntedFinance = useHauntedFinance();

  const hauntedBalance = useTokenBalance(hauntedFinance.HAUNTED);
  const displayHauntedBalance = useMemo(() => getDisplayBalance(hauntedBalance), [hauntedBalance]);

  const hshareBalance = useTokenBalance(hauntedFinance.HSHARE);
  const displayHshareBalance = useMemo(() => getDisplayBalance(hshareBalance), [hshareBalance]);

  const hbondBalance = useTokenBalance(hauntedFinance.HBOND);
  const displayHbondBalance = useMemo(() => getDisplayBalance(hbondBalance), [hbondBalance]);

  return (
    <Modal>
      <ModalTitle text="My Wallet" />

      <Balances>
        <StyledBalanceWrapper>
          <TokenSymbol symbol="HAUNTED" />
          <StyledBalance>
            <StyledValue>{displayHauntedBalance}</StyledValue>
            <Label text="HAUNTED Available" />
          </StyledBalance>
        </StyledBalanceWrapper>

        <StyledBalanceWrapper>
          <TokenSymbol symbol="HSHARE" />
          <StyledBalance>
            <StyledValue>{displayHshareBalance}</StyledValue>
            <Label text="HSHARE Available" />
          </StyledBalance>
        </StyledBalanceWrapper>

        <StyledBalanceWrapper>
          <TokenSymbol symbol="HBOND" />
          <StyledBalance>
            <StyledValue>{displayHbondBalance}</StyledValue>
            <Label text="HBOND Available" />
          </StyledBalance>
        </StyledBalanceWrapper>
      </Balances>
    </Modal>
  );
};

const StyledValue = styled.div`
  //color: ${(props) => props.theme.color.grey[300]};
  font-size: 30px;
  font-weight: 700;
`;

const StyledBalance = styled.div`
  align-items: center;
  display: flex;
  flex-direction: column;
`;

const Balances = styled.div`
  display: flex;
  flex-direction: row;
  justify-content: center;
  margin-bottom: ${(props) => props.theme.spacing[4]}px;
`;

const StyledBalanceWrapper = styled.div`
  align-items: center;
  display: flex;
  flex-direction: column;
  margin: 0 ${(props) => props.theme.spacing[3]}px;
`;

export default AccountModal;
