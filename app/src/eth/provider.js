/* eslint-disable no-unused-vars */
import { ethers } from 'ethers';

const rpcendpoint= "https://rinkeby.infura.io/v3/8d4b9c6cf9a942bd9c0468942a96fce0"

export function createProvider() {  
  return new ethers.providers.JsonRpcProvider(rpcendpoint, 4);
}