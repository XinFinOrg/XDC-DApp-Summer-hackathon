import React, { /*useCallback, useEffect, */useMemo, useState } from 'react';
import Page from '../../components/Page';
import HomeImage from '../../assets/img/home.jpg';
import { createGlobalStyle } from 'styled-components';
import { Route, Switch, useRouteMatch } from 'react-router-dom';
import { useWallet } from 'use-wallet';
import UnlockWallet from '../../components/UnlockWallet';
import PageHeader from '../../components/PageHeader';
import { Box,/* Paper, Typography,*/ Button, Grid } from '@material-ui/core';
import styled from 'styled-components';
import Spacer from '../../components/Spacer';
import useHauntedFinance from '../../hooks/useHauntedFinance';
import { getDisplayBalance/*, getBalance*/ } from '../../utils/formatBalance';
import { BigNumber/*, ethers*/ } from 'ethers';
import useSwapHBondToHShare from '../../hooks/HShareSwapper/useSwapHBondToHShare';
import useApprove, { ApprovalState } from '../../hooks/useApprove';
import useHShareSwapperStats from '../../hooks/HShareSwapper/useHShareSwapperStats';
import TokenInput from '../../components/TokenInput';
import Card from '../../components/Card';
import CardContent from '../../components/CardContent';
import TokenSymbol from '../../components/TokenSymbol';

const BackgroundImage = createGlobalStyle`
  body {
    background: url(${HomeImage}) no-repeat !important;
    background-size: cover !important;
  }
`;

function isNumeric(n: any) {
  return !isNaN(parseFloat(n)) && isFinite(n);
}

const Sbs: React.FC = () => {
  const { path } = useRouteMatch();
  const { account } = useWallet();
  const hauntedFinance = useHauntedFinance();
  const [hbondAmount, setHbondAmount] = useState('');
  const [hshareAmount, setHshareAmount] = useState('');

  const [approveStatus, approve] = useApprove(hauntedFinance.HBOND, hauntedFinance.contracts.HShareSwapper.address);
  const { onSwapHShare } = useSwapHBondToHShare();
  const hshareSwapperStat = useHShareSwapperStats(account);

  const hshareBalance = useMemo(() => (hshareSwapperStat ? Number(hshareSwapperStat.hshareBalance) : 0), [hshareSwapperStat]);
  const bondBalance = useMemo(() => (hshareSwapperStat ? Number(hshareSwapperStat.hbondBalance) : 0), [hshareSwapperStat]);

  const handleHBondChange = async (e: any) => {
    if (e.currentTarget.value === '') {
      setHbondAmount('');
      setHshareAmount('');
      return
    }
    if (!isNumeric(e.currentTarget.value)) return;
    setHbondAmount(e.currentTarget.value);
    const updateHShareAmount = await hauntedFinance.estimateAmountOfHShare(e.currentTarget.value);
    setHshareAmount(updateHShareAmount);  
  };

  const handleHBondSelectMax = async () => {
    setHbondAmount(String(bondBalance));
    const updateHShareAmount = await hauntedFinance.estimateAmountOfHShare(String(bondBalance));
    setHshareAmount(updateHShareAmount); 
  };

  const handleHShareSelectMax = async () => {
    setHshareAmount(String(hshareBalance));
    const rateHSharePerHaunted = (await hauntedFinance.getHShareSwapperStat(account)).rateHSharePerHaunted;
    const updateHBondAmount = ((BigNumber.from(10).pow(30)).div(BigNumber.from(rateHSharePerHaunted))).mul(Number(hshareBalance) * 1e6);
    setHbondAmount(getDisplayBalance(updateHBondAmount, 18, 6));
  };

  const handleHShareChange = async (e: any) => {
    const inputData = e.currentTarget.value;
    if (inputData === '') {
      setHshareAmount('');
      setHbondAmount('');
      return
    }
    if (!isNumeric(inputData)) return;
    setHshareAmount(inputData);
    const rateHSharePerHaunted = (await hauntedFinance.getHShareSwapperStat(account)).rateHSharePerHaunted;
    const updateHBondAmount = ((BigNumber.from(10).pow(30)).div(BigNumber.from(rateHSharePerHaunted))).mul(Number(inputData) * 1e6);
    setHbondAmount(getDisplayBalance(updateHBondAmount, 18, 6));
  }

  return (
    <Switch>
      <Page>
        <BackgroundImage />
        {!!account ? (
          <>
            <Route exact path={path}>
              <PageHeader icon={'🏦'} title="HBond -> HShare Swap" subtitle="Swap HBond to HShare" />
            </Route>
            <Box mt={5}>
              <Grid container justify="center" spacing={6}>
                <StyledBoardroom>
                  <StyledCardsWrapper>
                    <StyledCardWrapper>
                      <Card>
                        <CardContent>
                          <StyledCardContentInner>
                            <StyledCardTitle>HBonds</StyledCardTitle>
                            <StyledExchanger>
                              <StyledToken>
                                <StyledCardIcon>
                                  <TokenSymbol symbol={hauntedFinance.HBOND.symbol} size={54} />
                                </StyledCardIcon>
                              </StyledToken>
                            </StyledExchanger>
                            <Grid item xs={12}>
                              <TokenInput
                                onSelectMax={handleHBondSelectMax}
                                onChange={handleHBondChange}
                                value={hbondAmount}
                                max={bondBalance}
                                symbol="HBond"
                              ></TokenInput>
                            </Grid>
                            <StyledDesc>{`${bondBalance} HBOND Available in Wallet`}</StyledDesc>
                          </StyledCardContentInner>
                        </CardContent>
                      </Card>
                    </StyledCardWrapper>
                    <Spacer size="lg"/>
                    <StyledCardWrapper>
                      <Card>
                        <CardContent>
                          <StyledCardContentInner>
                            <StyledCardTitle>HShare</StyledCardTitle>
                            <StyledExchanger>
                              <StyledToken>
                                <StyledCardIcon>
                                  <TokenSymbol symbol={hauntedFinance.HSHARE.symbol} size={54} />
                                </StyledCardIcon>
                              </StyledToken>
                            </StyledExchanger>
                            <Grid item xs={12}>
                              <TokenInput
                                onSelectMax={handleHShareSelectMax}
                                onChange={handleHShareChange}
                                value={hshareAmount}
                                max={hshareBalance}
                                symbol="HShare"
                              ></TokenInput>
                            </Grid>
                            <StyledDesc>{`${hshareBalance} HSHARE Available in Swapper`}</StyledDesc>
                          </StyledCardContentInner>
                        </CardContent>
                      </Card>
              
                    </StyledCardWrapper>
                  </StyledCardsWrapper>
                </StyledBoardroom>
              </Grid>
            </Box>

            <Box mt={5}>
              <Grid container justify="center">
                <Grid item xs={8}>
                  <Card>
                    <CardContent>
                      <StyledApproveWrapper>
                      {approveStatus !== ApprovalState.APPROVED ? (
                        <Button
                          disabled={approveStatus !== ApprovalState.NOT_APPROVED}
                          color="primary"
                          variant="contained"
                          onClick={approve}
                          size="medium"
                        >
                          Approve HBOND
                        </Button>
                      ) : (
                        <Button
                          color="primary"
                          variant="contained"
                          onClick={() => onSwapHShare(hbondAmount.toString())}
                          size="medium"
                        >
                          Swap
                        </Button>
                      )}
                      </StyledApproveWrapper>
                    </CardContent>
                  </Card>
                </Grid>
              </Grid>
            </Box>
          </>
        ) : (
          <UnlockWallet />
        )}
      </Page>
    </Switch>
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
    width: 100%;
  }
`;

const StyledApproveWrapper = styled.div`
  margin-left: auto;
  margin-right: auto;
`;
const StyledCardTitle = styled.div`
  align-items: center;
  display: flex;
  font-size: 20px;
  font-weight: 700;
  height: 64px;
  justify-content: center;
  margin-top: ${(props) => -props.theme.spacing[3]}px;
`;

const StyledCardIcon = styled.div`
  background-color: ${(props) => props.theme.color.grey[900]};
  width: 72px;
  height: 72px;
  border-radius: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: ${(props) => props.theme.spacing[2]}px;
`;

const StyledExchanger = styled.div`
  align-items: center;
  display: flex;
  margin-bottom: ${(props) => props.theme.spacing[5]}px;
`;

const StyledToken = styled.div`
  align-items: center;
  display: flex;
  flex-direction: column;
  font-weight: 600;
`;

const StyledCardContentInner = styled.div`
  align-items: center;
  display: flex;
  flex: 1;
  flex-direction: column;
  justify-content: space-between;
`;

const StyledDesc = styled.span``;

export default Sbs;
