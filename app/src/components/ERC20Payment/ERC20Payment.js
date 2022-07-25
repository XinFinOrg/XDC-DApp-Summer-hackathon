import { useState, useContext, useEffect } from 'react';
import { executeTxn, showToasts, queryEvents, queryData } from '../../service/service';
import { EthereumContext } from "../../eth/context";
import { convertPriceToEth, checkCurrencyBalanceForUser, checkCurrencyBalanceForUserAccount, convertPricefromEth } from "../../eth/transaction";
import './ERC20Payment.css';
import { log } from '../../service/service'
import { checkConnections, publish, subscribe } from '../../eth/pubsub.redis.pubsub';

function ERC20Payment() {
  useEffect(async () => {
    await subscribe();
  })

  const [submitting, setSubmitting] = useState(false);
  const { provider, erc20gateway, usdt, usdc, dai, account } = useContext(EthereumContext);

  const registerMerchant = async (event) => {
    event.preventDefault();
    checkConnections();
    let _code = "PLIPay";
    let _tokenType = 2;
    log("makePayment", "erc20Gateway", erc20gateway)
    setSubmitting(true);
    let response1 = await executeTxn(erc20gateway, provider, 'registerMerchant', [account, _code, _tokenType]);
    log("registerMerchant", "Registered hash", response1.txHash)
    console.log("response1 value is", response1);
    showToasts(response1.txHash)
    const returnvalue = await queryEvents(erc20gateway, provider, "APIRegistered", response1.blockNumber);
    log("registerMerchant", "APIRegistered Event", returnvalue)
    setSubmitting(false);
  }

  const makePayment = async (event) => {
    event.preventDefault();
    checkConnections();
    let _price = 1;
    let _tokenAddress = dai.address;
    let _symbol = "DAI";

    // 0 -> USDT,
    // 1 -> USDC,
    // 2 -> DAI,
    // 3 -> USDZ
    let _tokenEnum = 2;

    let _apiKey = "0x5e974b3ba815642c0750c82011754e2c5a3817dd8702adab5fcd4953e6775474";

    log("makePayment", "erc20Gateway", erc20gateway)
    setSubmitting(true);
    let isHavingSuffcientBalance = await checkCurrencyBalanceForUser(dai, provider, account, _price, _symbol);

    if (isHavingSuffcientBalance) {
      let toPrice = await convertPriceToEth(_price.toString(), _symbol);
      let response1 = await executeTxn(dai, provider, 'approve', [erc20gateway.address, toPrice]);
      log("makePayment", "approve hash", response1.txHash)
      showToasts(response1.txHash)
      log("makePayment", "Price Value", toPrice)
      let response = await executeTxn(erc20gateway, provider, 'makePayment', [toPrice, _tokenAddress, _tokenEnum, _apiKey]);
      log("makePayment", "Hash value ", response.txHash, process.env.REACT_APP_EXPLORER)
      var returnvalue = await queryEvents(erc20gateway, provider, "ERC20Transferred", response.blockNumber);
      log("makePayment", "ERC20Transferred Event", returnvalue)
      showToasts(response.txHash)
      var transferpayload = JSON.stringify(returnvalue);
      console.log("JSON.stringify(returnvalue)", JSON.stringify(returnvalue))

      fetch('http://localhost:5001/api/transferCrypto', {
        method: 'post',
        headers: { 'Content-Type': 'application/json' },
        body: transferpayload
      })
        .then((response) => console.log(response));
      setSubmitting(false);

    }
  }



  const usdcBalance = async (event) => {
    event.preventDefault();
    let userBalance = await checkCurrencyBalanceForUserAccount(usdc, provider, account, "USDC");
    console.log("USDC userBalance", userBalance)
  }

  const usdtBalance = async (event) => {
    event.preventDefault();
    let userBalance = await checkCurrencyBalanceForUserAccount(usdt, provider, account, "USDT");
    console.log("USDT userBalance", userBalance)
  }

  const daiBalance = async (event) => {
    event.preventDefault();
    let userBalance = await checkCurrencyBalanceForUserAccount(dai, provider, account, "DAI");
    console.log("DAI userBalance", userBalance)
  }

  const fetchApiKey = async (event) => {
    event.preventDefault();
    let merchantWallet = "0x02244683B7CFb156C2f8F7E08513CA8FAfAA8E05";
    let apikey = await queryData(erc20gateway, provider, 'fetchAPIKey', [merchantWallet]);
    console.log("APIKey is", apikey)
  }

  const validAPI = async (event) => {
    event.preventDefault();
    let _apiKey = "0x5e974b3ba815642c0750c82011754e2c5a3817dd8702adab5fcd4953e6775474";
    let result = await queryData(erc20gateway, provider, 'apiRegistered', [_apiKey]);
    console.log("result is", result)
  }



  const daiEarnings = async (event) => {
    event.preventDefault();
    let tokenAddress = dai.address;
    let balance = await queryData(erc20gateway, provider, 'balanceToPay', [account, tokenAddress]);
    let convertedBalance = await convertPricefromEth(balance);
    console.log("balance is", convertedBalance)
  }

  const usdtEarnings = async (event) => {
    event.preventDefault();
    let tokenAddress = usdt.address;
    let balance = await queryData(erc20gateway, provider, 'balanceToPay', [account, tokenAddress]);
    let convertedBalance = await convertPricefromEth(balance);
    console.log("balance is", convertedBalance)
  }


  const usdcEarnings = async (event) => {
    event.preventDefault();
    let tokenAddress = usdc.address;
    let balance = await queryData(erc20gateway, provider, 'balanceToPay', [account, tokenAddress]);
    let convertedBalance = await convertPricefromEth(balance);
    console.log("balance is", convertedBalance)
  }

  const withdrawEarnings = async (event) => {
    event.preventDefault();
    let _currencyAddres = dai.address;
    let _apiKey = "0x5e974b3ba815642c0750c82011754e2c5a3817dd8702adab5fcd4953e6775474";
    setSubmitting(true);
    let response1 = await executeTxn(erc20gateway, provider, 'withdrawBalance', [_apiKey, _currencyAddres]);
    log("withdrawBalance", "Withdraw Balance hash", response1.txHash)
    console.log("response1 value is", response1);
    showToasts(response1.txHash)
    setSubmitting(false);

  }

  return <div className="Container">
    <div>
      <h1>Register Merchant</h1><br></br>
      <form onSubmit={registerMerchant}>
        <button type="submit" disabled={submitting}>{submitting ? 'Registering Merchant..' : 'Register Merchant'}</button>
      </form>
    </div>
    <div>
      <h1>Fetch APIKey</h1><br></br>
      <form onSubmit={fetchApiKey}>
        <button type="submit" disabled={submitting}>{submitting ? 'Fetch API Key..' : 'Fetch API'}</button>
      </form>
    </div>
    <div>
      <h1>Make Payment</h1><br></br>
      <form onSubmit={makePayment}>
        <button type="submit" disabled={submitting}>{submitting ? 'Making Payment..' : 'Make Payment'}</button>
      </form>
    </div>
    <div>
      <h1>USDT Balance</h1><br></br>
      <form onSubmit={usdtBalance}>
        <button type="submit" disabled={submitting}>{submitting ? 'USDT Balance .....' : 'USDT'}</button>
      </form>
    </div>
    <div>
      <h1>USDC Balance</h1><br></br>
      <form onSubmit={usdcBalance}>
        <button type="submit" disabled={submitting}>{submitting ? 'USDT Balance .....' : 'USDC'}</button>
      </form>
    </div>
    <div>
      <h1>Dai balance </h1><br></br>
      <form onSubmit={daiBalance}>
        <button type="submit" disabled={submitting}>{submitting ? 'DAI Balance .....' : 'DAI'}</button>
      </form>
    </div>
    <div>
      <h1>Merchant Earnings in Dai </h1><br></br>
      <form onSubmit={daiEarnings}>
        <button type="submit" disabled={submitting}>{submitting ? 'Merchant Earnings .....' : 'Earnings'}</button>
      </form>
    </div>
    <div>
      <h1>Merchant Earnings in USDT </h1><br></br>
      <form onSubmit={usdtEarnings}>
        <button type="submit" disabled={submitting}>{submitting ? 'Merchant Earnings .....' : 'Earnings'}</button>
      </form>
    </div>
    <div>
      <h1>Merchant Earnings in USDC </h1><br></br>
      <form onSubmit={usdcEarnings}>
        <button type="submit" disabled={submitting}>{submitting ? 'Merchant Earnings .....' : 'Earnings'}</button>
      </form>
    </div>
    <div>
      <h1>Withdraw Earnings </h1><br></br>
      <form onSubmit={withdrawEarnings}>
        <button type="submit" disabled={submitting}>{submitting ? 'Withdrawing Earnings .....' : 'Withdraw'}</button>
      </form>
    </div>
    <div>
      <h1>is Valid API </h1><br></br>
      <form onSubmit={validAPI}>
        <button type="submit" disabled={submitting}>{submitting ? 'Valid API .....' : 'validAPI'}</button>
      </form>
    </div>
  </div>
}

export default ERC20Payment;