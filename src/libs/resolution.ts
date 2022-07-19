import Resolution from "@unstoppabledomains/resolution";
import {getDefaultProvider} from "ethers";

import { connectState } from "./connect"

const resolution = new Resolution();

export const resolveName = async (name:string) => {

  let address = null;

  try{
  	//resolve by unstoppable
    address = await resolution.addr(name, "ETH");
  }catch(e){

    try{
      //resolve by provider
      address = await connectState.provider.resolveName(name);
    }catch(e){

      try{
      	//resolve by default provider
      	address = await getDefaultProvider().resolveName(name);
      }catch(e){
      	address = null;
      }
    }
  }

  return address;
}