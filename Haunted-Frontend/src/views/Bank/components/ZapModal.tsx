import React, { useState, useMemo } from 'react';

import { Button, Select, MenuItem, InputLabel, Typography, withStyles } from '@material-ui/core';
// import Button from '../../../components/Button'
import Modal, { ModalProps } from '../../../components/Modal';
import ModalActions from '../../../components/ModalActions';
import ModalTitle from '../../../components/ModalTitle';
import TokenInput from '../../../components/TokenInput';
import styled from 'styled-components';

import { getDisplayBalance } from '../../../utils/formatBalance';
import Label from '../../../components/Label';
import useLpStats from '../../../hooks/useLpStats';
import useTokenBalance from '../../../hooks/useTokenBalance';
import useHauntedFinance from '../../../hooks/useHauntedFinance';
import { useWallet } from 'use-wallet';
import useApproveZapper, { ApprovalState } from '../../../hooks/useApproveZapper';
import { HAUNTED_TICKER, HSHARE_TICKER, XDC_TICKER } from '../../../utils/constants';
import { Alert } from '@material-ui/lab';

interface ZapProps extends ModalProps {
  onConfirm: (zapAsset: string, lpName: string, amount: string) => void;
  tokenName?: string;
  decimals?: number;
}

const ZapModal: React.FC<ZapProps> = ({ onConfirm, onDismiss, tokenName = '', decimals = 18 }) => {
  const hauntedFinance = useHauntedFinance();
  const { balance } = useWallet();
  const xdcBalance = (Number(balance) / 1e18).toFixed(4).toString();
  const hauntedBalance = useTokenBalance(hauntedFinance.HAUNTED);
  const hshareBalance = useTokenBalance(hauntedFinance.HSHARE);
  const [val, setVal] = useState('');
  const [zappingToken, setZappingToken] = useState(XDC_TICKER);
  const [zappingTokenBalance, setZappingTokenBalance] = useState(xdcBalance);
  const [estimate, setEstimate] = useState({ token0: '0', token1: '0' }); // token0 will always be XDC in this case
  const [approveZapperStatus, approveZapper] = useApproveZapper(zappingToken);
  const hauntedFtmLpStats = useLpStats('HAUNTED-XDC-LP');
  const hShareFtmLpStats = useLpStats('HSHARE-XDC-LP');
  const hauntedLPStats = useMemo(() => (hauntedFtmLpStats ? hauntedFtmLpStats : null), [hauntedFtmLpStats]);
  const hshareLPStats = useMemo(() => (hShareFtmLpStats ? hShareFtmLpStats : null), [hShareFtmLpStats]);
  const xdcAmountPerLP = tokenName.startsWith(HAUNTED_TICKER) ? hauntedLPStats?.xdcAmount : hshareLPStats?.xdcAmount;
  /**
   * Checks if a value is a valid number or not
   * @param n is the value to be evaluated for a number
   * @returns
   */
  function isNumeric(n: any) {
    return !isNaN(parseFloat(n)) && isFinite(n);
  }
  const handleChangeAsset = (event: any) => {
    const value = event.target.value;
    setZappingToken(value);
    setZappingTokenBalance(xdcBalance);
    if (event.target.value === HSHARE_TICKER) {
      setZappingTokenBalance(getDisplayBalance(hshareBalance, decimals));
    }
    if (event.target.value === HAUNTED_TICKER) {
      setZappingTokenBalance(getDisplayBalance(hauntedBalance, decimals));
    }
  };

  const handleChange = async (e: any) => {
    if (e.currentTarget.value === '' || e.currentTarget.value === 0) {
      setVal(e.currentTarget.value);
      setEstimate({ token0: '0', token1: '0' });
    }
    if (!isNumeric(e.currentTarget.value)) return;
    setVal(e.currentTarget.value);
    const estimateZap = await hauntedFinance.estimateZapIn(zappingToken, tokenName, String(e.currentTarget.value));
    setEstimate({ token0: estimateZap[0].toString(), token1: estimateZap[1].toString() });
  };

  const handleSelectMax = async () => {
    setVal(zappingTokenBalance);
    const estimateZap = await hauntedFinance.estimateZapIn(zappingToken, tokenName, String(zappingTokenBalance));
    setEstimate({ token0: estimateZap[0].toString(), token1: estimateZap[1].toString() });
  };

  return (
    <Modal>
      <ModalTitle text={`Zap in ${tokenName}`} />
      <Typography variant="h6" align="center">
        Powered by{' '}
        <a target="_blank" rel="noopener noreferrer" href="https://mlnl.finance">
          mlnl.finance
        </a>
      </Typography>

      <StyledActionSpacer />
      <InputLabel style={{ color: '#121212' }} id="label">
        Select asset to zap with
      </InputLabel>
      <Select
        onChange={handleChangeAsset}
        style={{ color: '#121212' }}
        labelId="label"
        id="select"
        value={zappingToken}
      >
        <StyledMenuItem value={XDC_TICKER}>XDC</StyledMenuItem>
        <StyledMenuItem value={HSHARE_TICKER}>HSHARE</StyledMenuItem>
        {/* Haunted as an input for zapping will be disabled due to issues occuring with the Gatekeeper system */}
        {/* <StyledMenuItem value={HAUNTED_TICKER}>HAUNTED</StyledMenuItem> */}
      </Select>
      <TokenInput
        onSelectMax={handleSelectMax}
        onChange={handleChange}
        value={val}
        max={zappingTokenBalance}
        symbol={zappingToken}
      />
      <Label text="Zap Estimations" />
      <StyledDescriptionText>
        {' '}
        {tokenName}: {Number(estimate.token0) / Number(xdcAmountPerLP)}
      </StyledDescriptionText>
      <StyledDescriptionText>
        {' '}
        ({Number(estimate.token0)} {XDC_TICKER} / {Number(estimate.token1)}{' '}
        {tokenName.startsWith(HAUNTED_TICKER) ? HAUNTED_TICKER : HSHARE_TICKER}){' '}
      </StyledDescriptionText>
      <ModalActions>
        <Button
          color="primary"
          variant="contained"
          onClick={() =>
            approveZapperStatus !== ApprovalState.APPROVED ? approveZapper() : onConfirm(zappingToken, tokenName, val)
          }
        >
          {approveZapperStatus !== ApprovalState.APPROVED ? 'Approve' : "Let's go"}
        </Button>
      </ModalActions>

      <StyledActionSpacer />
      <Alert variant="filled" severity="warning">
        Beta feature. Use at your own risk!
      </Alert>
    </Modal>
  );
};

const StyledActionSpacer = styled.div`
  height: ${(props) => props.theme.spacing[4]}px;
  width: ${(props) => props.theme.spacing[4]}px;
`;

const StyledDescriptionText = styled.div`
  align-items: center;
  color: ${(props) => props.theme.color.grey[400]};
  display: flex;
  font-size: 14px;
  font-weight: 700;
  height: 22px;
  justify-content: flex-start;
`;
const StyledMenuItem = withStyles({
  root: {
    backgroundColor: 'white',
    color: '#121212',
    '&:hover': {
      backgroundColor: 'grey',
      color: '#121212',
    },
    selected: {
      backgroundColor: 'black',
    },
  },
})(MenuItem);

export default ZapModal;
