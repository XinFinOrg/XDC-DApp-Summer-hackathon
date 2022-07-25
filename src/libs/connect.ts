// example imports 
import { providers, utils } from "ethers"

import web3modal from './web3modal'

import * as constant from '../constant'

import { shortString } from "./tools"

import { ref } from "vue"

export const connectState = {
  chainId: 50,
  chainName: '',
  userName: ref(''),
  shortName: ref(''),
  userAddr: ref(''),
  shortAddr: ref(''),
  currency: 'ETH',
  signed: false,
  provider: Object(),
  connected: false,
  inited:false,
  signer: Object(),
  storage: 'filcoin',
  web3Storage: '',
  bundlrProvider: Object(),
  activeIndex: ref('1'),
  activeName: ref(''),
  search: '',
  searchCallback: async () => {},
  connectCallback: async () => {},
  accountsChanged: async (accounts: string[]) => {},
  chainChanged: async () => {},
  transactions: ref(new Array()),
  transactionCount: ref(0),
  fluenceId: '',
  fluenceRelayId: '',
  fluenceOnline: Object(),
  fluenceChatMessages: ref(new Array()),
  fluenceChatNewMessages: ref(new Array()),
};

//detect currency
export const detectCurrency = async (chainId: number) => {
    const tokenList = (constant.tokenList as any)[chainId];
    for (const i in tokenList){
      if(connectState.currency === tokenList[i]){
        return;
      }
    }

    connectState.currency = tokenList[0];
}

//connect to metamask wallet
export const networkConnect = async () => {

  if(!connectState.connected){

    try{
      const provider = await web3modal.connect();

      connectState.provider = new providers.Web3Provider(provider);  

      //only first time to bind the events
      if(!connectState.inited){
        connectState.inited = true;
        // Subscribe to accounts change
        provider.on("accountsChanged", (accounts: string[]) => {
          connectState.accountsChanged(accounts);
        });

        // Subscribe to chainId change
        provider.on("chainChanged", (chainId: number) => {
          connectState.chainChanged();
        });  
      } 

    }catch(e){
      cancelConnect();
      return false;
    }
  }

  try{
    const userName = (window as any).localStorage.getItem('uauth-default-username');
    if(userName != undefined && userName != null && userName != ""){
      connectState.userName.value = userName;
    }    
    if(await detectNetwork()){
      return true;
    }
  }catch(e){
    cancelConnect();
    return false;
  }

  cancelConnect();
  return false;
}

//deteck network
export const detectNetwork = async () => {
  //detect block chain
  const res = await connectState.provider.detectNetwork();

  if(res.chainId == null || res.chainId == undefined){
    return false;
  }

  const accounts = await connectState.provider.send("eth_accounts", []);
  if(accounts.length === 0){
    return false;
  }

  const selectedAddress = accounts[0];

  if(selectedAddress === undefined || selectedAddress === null || selectedAddress === ''){
    return false;
  }

  if(connectState.userAddr.value != selectedAddress){
    connectState.bundlrProvider = null;

    //user address
    connectState.userAddr.value = selectedAddress;
  }  

  connectState.provider = new providers.Web3Provider(connectState.provider.provider);

  //signer
  connectState.signer = connectState.provider.getSigner();

  //chain id changed
  if(connectState.chainId !== res.chainId){

    connectState.bundlrProvider = null;
  }

  //chain id
  connectState.chainId = res.chainId;
  
  //chain name
  connectState.chainName = res.name;

  //chain token
  detectCurrency(res.chainId);

  //set connected status
  connectState.connected = true;

  //connect call back
  connectState.connectCallback();  

  return true;  
}

//disconnect to metamask wallet
export const cancelConnect = async () => {
  connectState.userAddr.value = "";
  connectState.connected = false;

  try{
    connectState.provider.provider.close();
  }catch(e){
    console.log('');
  }
  
  web3modal.clearCachedProvider();
  connectState.signer = null;
  connectState.bundlrProvider = null;
}

//check if wallet is connected or not
export const connected = async () => {
  if(!connectState.connected){
    return false;
  }

  try{
    const res = await connectState.provider.send("eth_accounts", []);

    if(res.length > 0){
      return true;
    }    
  }catch(e){
    console.log('');
  }

  connectState.connected = false;
  return false;  
}