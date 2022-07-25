import { sendTx, sendMetaTx, signAndBalance, qryEvents, queryReports } from '../eth/transaction';
import { toast } from 'react-toastify';


//ExecuteTxn function to post the data onto blockchain
//It calls normal transation as well as meta transaction
export async function executeTxn(contract, provider, functionname, input) {
  log("executeTxn","Input Value",functionname, input)

  if (!input) throw new Error(`Input cannot be empty`);

  const { balance, signer } = await signAndBalance(provider);
  const canSendTx = balance.gt(1e15);

  if (canSendTx) return sendTx(contract['connect'](signer), functionname, [...input]);
  else return sendMetaTx(contract, provider, signer, functionname, [...input]);
}

//queryEvents to pull the event details and bring the return value from smart contract
export async function queryEvents(contract, provider, eventname, blocknumber) {
  const { signer } = await signAndBalance(provider);
  return qryEvents(contract['connect'](signer), eventname, blocknumber);
}

//showToasts to just show the toast message with Txn Hash
export async function showToasts(txHash) {
  const onClick = txHash
    ? () => window.open(`${process.env.REACT_APP_EXPLORER}${txHash}`)
    : undefined;
  toast('Transaction Result!', { type: 'info', onClick });
}

//showToasts to just show the toast message with Txn Hash
export async function showError(result) {
  toast('Transaction Error!', { type: 'error',result  });
}

//queryEvents to pull the event details and bring the return value from smart contract
export async function queryData(contract, provider, functionname, input) {
  const { signer } = await signAndBalance(provider);
  return queryReports(contract['connect'](signer), functionname, [...input]);
}

export function log(functioname,content,value){
  console.log(`${functioname} :::: ${content} ::::`, value);
}