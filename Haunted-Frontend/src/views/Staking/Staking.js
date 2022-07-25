import React, { useMemo } from 'react';
import { useWallet } from 'use-wallet';
import moment from 'moment';
import styled from 'styled-components';
import Spacer from '../../components/Spacer';
import Harvest from './components/Harvest';
import Stake from './components/Stake';
import { makeStyles } from '@material-ui/core/styles';

import { Box, Card, CardContent, Button, Typography, Grid } from '@material-ui/core';

import { Alert } from '@material-ui/lab';

import UnlockWallet from '../../components/UnlockWallet';
import Page from '../../components/Page';

import useRedeemOnStaking from '../../hooks/useRedeemOnStaking';
import useStakedBalanceOnStaking from '../../hooks/useStakedBalanceOnStaking';
import { getDisplayBalance } from '../../utils/formatBalance';
import useCurrentEpoch from '../../hooks/useCurrentEpoch';
import useFetchStakingAPR from '../../hooks/useFetchStakingAPR';

import useCashPriceInEstimatedTWAP from '../../hooks/useCashPriceInEstimatedTWAP';
import useTreasuryAllocationTimes from '../../hooks/useTreasuryAllocationTimes';
import useTotalStakedOnStaking from '../../hooks/useTotalStakedOnStaking';
import useClaimRewardCheck from '../../hooks/staking/useClaimRewardCheck';
import useWithdrawCheck from '../../hooks/staking/useWithdrawCheck';
import ProgressCountdown from './components/ProgressCountdown';
import StakingImage from '../../assets/img/home.jpg';
import { createGlobalStyle } from 'styled-components';

const BackgroundImage = createGlobalStyle`
  body, html {
    background: url(${StakingImage}) no-repeat !important;
    background-size: cover !important;
  }
`;

const useStyles = makeStyles((theme) => ({
  gridItem: {
    height: '100%',
    [theme.breakpoints.up('md')]: {
      height: '90px',
    },
  },
}));

const Staking = () => {
  const classes = useStyles();
  const { account } = useWallet();
  const { onRedeem } = useRedeemOnStaking();
  const stakedBalance = useStakedBalanceOnStaking();
  const currentEpoch = useCurrentEpoch();
  const cashStat = useCashPriceInEstimatedTWAP();
  const totalStaked = useTotalStakedOnStaking();
  const stakingAPR = useFetchStakingAPR();
  const canClaimReward = useClaimRewardCheck();
  const canWithdraw = useWithdrawCheck();
  const scalingFactor = useMemo(() => (cashStat ? Number(cashStat.priceInDollars).toFixed(4) : null), [cashStat]);
  const { to } = useTreasuryAllocationTimes();

  return (
    <Page>
      <BackgroundImage />
      {!!account ? (
        <>
          <Typography color="textPrimary" align="center" variant="h3" gutterBottom>
            Staking
          </Typography>
          <Box mt={5}>
            <Grid container justify="center" spacing={3}>
              <Grid item xs={12} md={2} lg={2} className={classes.gridItem}>
                <Card className={classes.gridItem}>
                  <CardContent>
                    <Typography style={{ textAlign: 'center' }}>Next Epoch</Typography>
                    <ProgressCountdown base={moment().toDate()} hideBar={true} deadline={to} description="Next Epoch" />
                  </CardContent>
                </Card>
              </Grid>
              <Grid item xs={12} md={2} lg={2} className={classes.gridItem}>
                <Card className={classes.gridItem}>
                  <CardContent align="center">
                    <Typography>Current Epoch</Typography>
                    <Typography>{Number(currentEpoch)}</Typography>
                  </CardContent>
                </Card>
              </Grid>
              <Grid item xs={12} md={2} lg={2} className={classes.gridItem}>
                <Card className={classes.gridItem}>
                  <CardContent align="center">
                    <Typography>
                      HAUNTED Price<small>(TWAP)</small>
                    </Typography>
                    <Typography>{scalingFactor}</Typography>
                  </CardContent>
                </Card>
              </Grid>
              <Grid item xs={12} md={2} lg={2} className={classes.gridItem}>
                <Card className={classes.gridItem}>
                  <CardContent align="center">
                    <Typography>APR</Typography>
                    <Typography>{stakingAPR.toFixed(2)}%</Typography>
                  </CardContent>
                </Card>
              </Grid>
              <Grid item xs={12} md={2} lg={2}>
                <Card className={classes.gridItem}>
                  <CardContent align="center">
                    <Typography>HSHARES Staked</Typography>
                    <Typography>{getDisplayBalance(totalStaked)}</Typography>
                  </CardContent>
                </Card>
              </Grid>
            </Grid>

            <Grid container justify="center">
              <Box mt={3} style={{ width: '600px' }}>
                <Alert variant="filled" severity="warning">
                  Staked HSHAREs can only be withdrawn after 6 epochs since deposit.
                </Alert>
              </Box>
            </Grid>

            <Box mt={4}>
              <StyledBoardroom>
                <StyledCardsWrapper>
                  <StyledCardWrapper>
                    <Harvest />
                  </StyledCardWrapper>
                  <Spacer />
                  <StyledCardWrapper>
                    <Stake />
                  </StyledCardWrapper>
                </StyledCardsWrapper>
              </StyledBoardroom>
            </Box>

            {/* <Grid container justify="center" spacing={3}>
            <Grid item xs={4}>
              <Card>
                <CardContent align="center">
                  <Typography>Rewards</Typography>

                </CardContent>
                <CardActions style={{justifyContent: 'center'}}>
                  <Button color="primary" variant="outlined">Claim Reward</Button>
                </CardActions>
                <CardContent align="center">
                  <Typography>Claim Countdown</Typography>
                  <Typography>00:00:00</Typography>
                </CardContent>
              </Card>
            </Grid>
            <Grid item xs={4}>
              <Card>
                <CardContent align="center">
                  <Typography>Stakings</Typography>
                  <Typography>{getDisplayBalance(stakedBalance)}</Typography>
                </CardContent>
                <CardActions style={{justifyContent: 'center'}}>
                  <Button>+</Button>
                  <Button>-</Button>
                </CardActions>
              </Card>
            </Grid>
          </Grid> */}
          </Box>

          <Box mt={5}>
            <Grid container justify="center" spacing={3} mt={10}>
              <Button
                disabled={stakedBalance.eq(0) || (!canWithdraw && !canClaimReward)}
                onClick={onRedeem}
                color="primary"
                variant="contained"
              >
                Claim and Withdraw
              </Button>
            </Grid>
          </Box>
        </>
      ) : (
        <UnlockWallet />
      )}
    </Page>
  );
};

const StyledBoardroom = styled.div`
  align-items: center;
  display: flex;
  flex-direction: column;
  @media (max-width: 768px) {
    width: 100%;
  }
`;

const StyledCardsWrapper = styled.div`
  display: flex;
  width: 600px;
  @media (max-width: 768px) {
    width: 100%;
    flex-flow: column nowrap;
    align-items: center;
  }
`;

const StyledCardWrapper = styled.div`
  display: flex;
  flex: 1;
  flex-direction: column;
  @media (max-width: 768px) {
    width: 80%;
  }
`;

export default Staking;
