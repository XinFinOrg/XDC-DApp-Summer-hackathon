import React, { useMemo, useState } from 'react';
import Page from '../../components/Page';
import { createGlobalStyle } from 'styled-components';
import HomeImage from '../../assets/img/home.jpg';
import useLpStats from '../../hooks/useLpStats';
import { Box, Button, Grid, Paper, Typography } from '@material-ui/core';
import useHauntedStats from '../../hooks/useHauntedStats';
import TokenInput from '../../components/TokenInput';
import useHauntedFinance from '../../hooks/useHauntedFinance';
import { useWallet } from 'use-wallet';
import useTokenBalance from '../../hooks/useTokenBalance';
import { getDisplayBalance } from '../../utils/formatBalance';
import useApproveTaxOffice from '../../hooks/useApproveTaxOffice';
import { ApprovalState } from '../../hooks/useApprove';
import useProvideHauntedFtmLP from '../../hooks/useProvideHauntedFtmLP';
import { Alert } from '@material-ui/lab';

const BackgroundImage = createGlobalStyle`
  body {
    background: url(${HomeImage}) no-repeat !important;
    background-size: cover !important;
  }
`;
function isNumeric(n) {
  return !isNaN(parseFloat(n)) && isFinite(n);
}

const ProvideLiquidity = () => {
  const [hauntedAmount, setHauntedAmount] = useState(0);
  const [xdcAmount, setFtmAmount] = useState(0);
  const [lpTokensAmount, setLpTokensAmount] = useState(0);
  const { balance } = useWallet();
  const hauntedStats = useHauntedStats();
  const hauntedFinance = useHauntedFinance();
  const [approveTaxOfficeStatus, approveTaxOffice] = useApproveTaxOffice();
  const hauntedBalance = useTokenBalance(hauntedFinance.HAUNTED);
  const xdcBalance = (balance / 1e18).toFixed(4);
  const { onProvideHauntedFtmLP } = useProvideHauntedFtmLP();
  const hauntedFtmLpStats = useLpStats('HAUNTED-XDC-LP');

  const hauntedLPStats = useMemo(() => (hauntedFtmLpStats ? hauntedFtmLpStats : null), [hauntedFtmLpStats]);
  const hauntedPriceInXDC = useMemo(() => (hauntedStats ? Number(hauntedStats.tokenInFtm).toFixed(2) : null), [hauntedStats]);
  const xdcPriceInHAUNTED = useMemo(() => (hauntedStats ? Number(1 / hauntedStats.tokenInFtm).toFixed(2) : null), [hauntedStats]);
  // const classes = useStyles();

  const handleHauntedChange = async (e) => {
    if (e.currentTarget.value === '' || e.currentTarget.value === 0) {
      setHauntedAmount(e.currentTarget.value);
    }
    if (!isNumeric(e.currentTarget.value)) return;
    setHauntedAmount(e.currentTarget.value);
    const quoteFromSpooky = await hauntedFinance.quoteFromSpooky(e.currentTarget.value, 'HAUNTED');
    setFtmAmount(quoteFromSpooky);
    setLpTokensAmount(quoteFromSpooky / hauntedLPStats.xdcAmount);
  };

  const handleFtmChange = async (e) => {
    if (e.currentTarget.value === '' || e.currentTarget.value === 0) {
      setFtmAmount(e.currentTarget.value);
    }
    if (!isNumeric(e.currentTarget.value)) return;
    setFtmAmount(e.currentTarget.value);
    const quoteFromSpooky = await hauntedFinance.quoteFromSpooky(e.currentTarget.value, 'XDC');
    setHauntedAmount(quoteFromSpooky);

    setLpTokensAmount(quoteFromSpooky / hauntedLPStats.tokenAmount);
  };
  const handleHauntedSelectMax = async () => {
    const quoteFromSpooky = await hauntedFinance.quoteFromSpooky(getDisplayBalance(hauntedBalance), 'HAUNTED');
    setHauntedAmount(getDisplayBalance(hauntedBalance));
    setFtmAmount(quoteFromSpooky);
    setLpTokensAmount(quoteFromSpooky / hauntedLPStats.xdcAmount);
  };
  const handleFtmSelectMax = async () => {
    const quoteFromSpooky = await hauntedFinance.quoteFromSpooky(xdcBalance, 'XDC');
    setFtmAmount(xdcBalance);
    setHauntedAmount(quoteFromSpooky);
    setLpTokensAmount(xdcBalance / hauntedLPStats.xdcAmount);
  };
  return (
    <Page>
      <BackgroundImage />
      <Typography color="textPrimary" align="center" variant="h3" gutterBottom>
        Provide Liquidity
      </Typography>

      <Grid container justify="center">
        <Box style={{ width: '600px' }}>
          <Alert variant="filled" severity="warning" style={{ marginBottom: '10px' }}>
            <b>This and <a href=""  rel="noopener noreferrer" target="_blank">XDCSwap</a> are the only ways to provide Liquidity on HAUNTED-XDC pair without paying tax.</b>
          </Alert>
          <Grid item xs={12} sm={12}>
            <Paper>
              <Box mt={4}>
                <Grid item xs={12} sm={12} style={{ borderRadius: 15 }}>
                  <Box p={4}>
                    <Grid container>
                      <Grid item xs={12}>
                        <TokenInput
                          onSelectMax={handleHauntedSelectMax}
                          onChange={handleHauntedChange}
                          value={hauntedAmount}
                          max={getDisplayBalance(hauntedBalance)}
                          symbol={'HAUNTED'}
                        ></TokenInput>
                      </Grid>
                      <Grid item xs={12}>
                        <TokenInput
                          onSelectMax={handleFtmSelectMax}
                          onChange={handleFtmChange}
                          value={xdcAmount}
                          max={xdcBalance}
                          symbol={'XDC'}
                        ></TokenInput>
                      </Grid>
                      <Grid item xs={12}>
                        <p>1 HAUNTED = {hauntedPriceInXDC} XDC</p>
                        <p>1 XDC = {xdcPriceInHAUNTED} HAUNTED</p>
                        <p>LP tokens ≈ {lpTokensAmount.toFixed(2)}</p>
                      </Grid>
                      <Grid xs={12} justifyContent="center" style={{ textAlign: 'center' }}>
                        {approveTaxOfficeStatus === ApprovalState.APPROVED ? (
                          <Button
                            variant="contained"
                            onClick={() => onProvideHauntedFtmLP(xdcAmount.toString(), hauntedAmount.toString())}
                            color="primary"
                            style={{ margin: '0 10px', color: '#fff' }}
                          >
                            Supply
                          </Button>
                        ) : (
                          <Button
                            variant="contained"
                            onClick={() => approveTaxOffice()}
                            color="secondary"
                            style={{ margin: '0 10px' }}
                          >
                            Approve
                          </Button>
                        )}
                      </Grid>
                    </Grid>
                  </Box>
                </Grid>
              </Box>
            </Paper>
          </Grid>
        </Box>
      </Grid>
    </Page>
  );
};

export default ProvideLiquidity;
