// example imports 
import { providers, utils } from "ethers"

import { connectState } from "./connect"

import * as constant from "../constant"

const chainList = constant.chainList;

//sleep a few ms 
const sleep = (time: number) => {
  return new Promise((resolve:any) => setTimeout(resolve, time));
};

//get chain ifo by chain id
export const getChainInfo = (chainId: number) => {
  for (const i in chainList){
    if(chainList[i].chainId === chainId){
      return {
        chainId: utils.hexValue(chainId),
        chainName: chainList[i].chainName,
        nativeCurrency: {
          name: chainList[i].nativeCurrency,
          symbol: chainList[i].nativeCurrency,
          decimals: 18,
        },
        rpcUrls: [chainList[i].rpcUrls],
        blockExplorerUrls: [chainList[i].blockExplorerUrls],
      };
    }
  }

  return null;
}

//get chain name
export const getChainName = (chainId: number) => {
  for (const i in chainList){
    if(chainList[i].chainId === chainId){
      return chainList[i].chainName;
    }
  }

  return connectState.chainName;
}

//switch network
export const switchNetwork = async (chainId: number) => {

  const res = await connectState.provider.detectNetwork();
  if(res.chainId == null || res.chainId == undefined){
    return false;
  }
  
  if(res.chainId === chainId){
    return true;
  }

  const chainInfo = getChainInfo(chainId);

  if (chainInfo === null || chainInfo === undefined){
    return false;
  }

  try {
    await connectState.provider.send(
      "wallet_switchEthereumChain", 
      [{ chainId: utils.hexValue(chainId) },],
    );
  } catch (err: any) {

    if (err.code === 4902 || err.code === -32603) {
      await connectState.provider.send(
        "wallet_addEthereumChain", 
        [chainInfo],
      );
    }
  }

  //wait for at most 5s to network refresh
  for(let i = 0; i < 10; i++){
    const res = await connectState.provider.detectNetwork();
    if(res.chainId == null || res.chainId == undefined){
      continue;
    }
    
    if(res.chainId === chainId){
      return true;
    }    

    await sleep(500);
  }

  return false;
}