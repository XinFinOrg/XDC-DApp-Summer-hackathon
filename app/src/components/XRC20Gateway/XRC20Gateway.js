import { useState, useContext, useEffect } from 'react';
import { executeTxn, showToasts, queryEvents } from '../../service/service';
import { EthereumContext } from "../../eth/context";
import { convertPriceToEth, checkCurrencyBalanceForUser, checkCurrencyBalanceForUserAccount } from "../../eth/transaction";
import './XRC20Gateway.css';
import { log } from '../../service/service'
import { checkConnections, publish, subscribe } from '../../eth/pubsub.redis.pubsub';

function XRC20Gateway() {
  useEffect(async () => {
    await subscribe();
  })

  const [submitting, setSubmitting] = useState(false);
  const { provider, xrc20gateway, pli, srx, wtk, lbt } = useContext(EthereumContext);
  console.log("XRC20Gateway", xrc20gateway)
  console.log("PLI Contract", pli)

  const addSupportedTokens = async (event) => {
    event.preventDefault();
    checkConnections();
    setSubmitting(true);
    let symbol = "PLI";
    var toPass;
    if (symbol = "PLI") {
      toPass = pli;
    }
    let response1 = await executeTxn(xrc20gateway, provider, 'addSupportedToken', [symbol, toPass.address]);
    log("addSupportedTokens", "approve hash", response1.txHash)
    setSubmitting(false);
  }

  const approveTokens = async (event) => {
    event.preventDefault();
    checkConnections();
    setSubmitting(true);
    let _price = 500;
    let symbol = "PLI";
    var toPass;
    let toPrice = await convertPriceToEth(_price.toString(), symbol);
    if (symbol = "PLI") {
      toPass = pli;
    }
    let response1 = await executeTxn(toPass, provider, 'approve', [xrc20gateway.address, toPrice]);
    log("approveTokens", "approve hash", response1.txHash)
    setSubmitting(false);

  }

  const depositTokens = async (event) => {
    event.preventDefault();
    checkConnections();
    setSubmitting(true);
    let _price = 500;
    let symbol = "PLI";
    var toPass;
    let toPrice = await convertPriceToEth(_price.toString(), symbol);
    if (symbol = "PLI") {
      toPass = pli;
    }
    let response1 = await executeTxn(xrc20gateway, provider, 'depositFunds', [toPrice, toPass.address, symbol]);
    log("approveTokens", "approve hash", response1.txHash)
    setSubmitting(false);

  }

  return <div className="Container">
    <div>
      <h1>Add Supported Tokens</h1><br></br>
      <form onSubmit={addSupportedTokens}>
        <button type="submit" disabled={submitting}>{submitting ? 'Submitting..' : 'Support Tokens'}</button>
      </form>
    </div>
    <div></div>
    <div>
      <h1>Approve Tokens</h1><br></br>
      <form onSubmit={approveTokens}>
        <button type="submit" disabled={submitting}>{submitting ? 'Approving..' : 'Approve Tokens'}</button>
      </form>
    </div>
    <div>
      <h1>Deposit Tokens</h1><br></br>
      <form onSubmit={depositTokens}>
        <button type="submit" disabled={submitting}>{submitting ? 'Depositing..' : 'Deposit Tokens'}</button>
      </form>
    </div>
  </div>
}



export default XRC20Gateway;