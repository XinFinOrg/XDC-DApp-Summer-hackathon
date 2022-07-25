// import { createInstance } from './forwarder';
import { signMetaTxRequest } from './signer';
import { ethers } from 'ethers';
import { create } from 'ipfs-http-client';
import axios from 'axios'
import { log, queryData } from '../service/service'

export async function sendTx(contract, functionname, input) {
  log("sendTx", "Sending tx to set", input);
  // const tx = await contract[functionname](...input);
  const tx = await contract[functionname](...input, { gasLimit: 3000000 });

  const txresponse = await tx.wait();
  const blkno = txresponse.blockNumber ? txresponse.blockNumber : 0;
  const txnevnts = txresponse.events ? txresponse.events : "";
  let obj = { txHash: txresponse.transactionHash, blockNumber: blkno, event: txnevnts }
  log("sendTx", "Responses", obj);
  return obj;
}

export async function queryReports(contract, functionname, input) {
  const result = await contract[functionname](...input);
  return result;
}

export async function qryEvents(contract, eventname, blockNumber) {
  log("qryEvents", "inputs are", contract, eventname, blockNumber);
  const events = await contract.queryFilter(contract.filters[`${eventname}`](), blockNumber, blockNumber);
  log("qryEvents", "event value", events, events[0].args)
  return events[0].args;
}

export async function sendMetaTx(contract, provider, signer, functionname, input) {
  log("sendMetaTx", "Sending meta-tx to set", input);

  let url = '';
  if (process.env.REACT_APP_DEBUGMODE === "INTEGRATION") {
    url = process.env.REACT_APP_WEBHOOK_URL_INTEGRATION;
  } else {
    url = process.env.REACT_APP_WEBHOOK_URL_TEST;
  }
  log("sendMetaTx", "Defender URL", url);

  if (!url) throw new Error(`Missing relayer url`);
  // const forwarder = createInstance(provider);
  let forwarder = "";
  const from = await signer.getAddress();
  const data = contract.interface.encodeFunctionData(functionname, [...input]);
  const to = contract.address;

  const request = await signMetaTxRequest(signer.provider, forwarder, { to, from, data });
  const responses = await axios.post(url, request)
  log("sendMetaTx", "responses", responses);
  const finalresult = JSON.parse(responses.data.result);
  let obj = { txHash: finalresult.txHash, blockNumber: finalresult.blockNumber, event: "" }
  log("sendMetaTx", "Responses", obj);
  return obj;
}

export async function signAndBalance(provider) {
  const userProvider = provider;
  const userNetwork = await userProvider.getNetwork();
  log("signAndBalance", "userNetwork", userNetwork)
  // if (userNetwork.chainId !== 80001) throw new Error(`Please switch to Mumbai Polygon for signing`);
  const signer = userProvider.getSigner();
  const from = await signer.getAddress();
  const balance = await provider.getBalance(from);
  return { balance, signer };
};

export async function convertPriceToEth(n, tokenType) {
  var convertedprice;
  if (tokenType === 'USDT' || tokenType === 'USDC') {
    convertedprice = ethers.utils.parseUnits(n, 6);
  } else {
    convertedprice = ethers.utils.parseUnits(n, 'ether');
  }
  return convertedprice;
}

export async function convertPricefromEth(n) {
  const convertedprice = ethers.utils.formatUnits(n, 'ether');
  return convertedprice;
}

export async function convertListingFee(n) {
  const convertedprice = ethers.utils.formatUnits(n, 4);
  return convertedprice;
}

export async function upload(n, from) {
  const ipfs = create({ host: 'ipfs.infura.io', port: 5001, protocol: 'https' })
  log("IPFS", "Object", ipfs)
  try {
    const added = await ipfs.add(n)
    const url = `https://ipfs.infura.io/ipfs/${added.path}`
    log("IPFS", `Upload initiated for ${from}`, url)
    return url;
  } catch (error) {
    log('IPFS', 'Error', error)
  }
}

export async function checkCurrencyBalanceForUser(token, provider, account, actualprice, _symbol) {
  let data = await queryData(token, provider, 'balanceOf', [account]);
  var actualBalance;
  console.log("data,", data)
  if (_symbol === 'USDT' || _symbol === 'USDC') {
    actualBalance = parseInt(ethers.utils.formatUnits(data, 6));
  } else {
    actualBalance = await convertPricefromEth(data);
  }

  log("checkUserBalance", "actualBalance", actualBalance)
  log("checkUserBalance", "actualprice", actualprice)

  if ((actualBalance === 0) || (actualBalance < actualprice)) {
    log("buyNFT", "Balance of required currency is either 0 or lower than expected price", actualBalance);
    return false;
  } else {
    return true;
  }
}

export async function checkCurrencyBalanceForUserAccount(token, provider, account, _symbol) {
  let data = await queryData(token, provider, 'balanceOf', [account]);
  var actualBalance;
  console.log("data,", data)
  if (_symbol === 'USDT') {
    actualBalance = (ethers.utils.formatUnits(data, 6));
  } else {
    actualBalance = await convertPricefromEth(data);
  }

  log("checkUserBalance", "actualBalance", actualBalance)
  return actualBalance;
}

export async function getTransactionReceiptMined(txHash, interval, provider) {
  console.log("txhahs is", txHash)
  const self = this;
  const transactionReceiptAsync = async function (resolve, reject) {
    await provider.getTransactionReceipt(txHash, (error, receipt) => {
      if (error) {
        reject(error);
      } else if (receipt == null) {
        console.log("receipt is", receipt)
        setTimeout(
          () => transactionReceiptAsync(resolve, reject),
          interval ? interval : 100);
      } else {
        console.log("receipt is", receipt)

        resolve(receipt);
      }
    });
  };
  if (Array.isArray(txHash)) {
    console.log("i am here in Array check", txHash)
    return Promise.all(txHash.map(
      oneTxHash => self.getTransactionReceiptMined(oneTxHash, interval)));
  } else if (typeof txHash === "string") {
    console.log("i am here in string check", txHash)
    return new Promise(await transactionReceiptAsync);
  } else {
    throw new Error("Invalid Type: " + txHash);
  }
};