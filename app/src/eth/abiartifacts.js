import { ethers } from 'ethers';

import {
  ERC20Gateway as erc20gatewayadders,
  USDT as usdtaddress,
  USDC as usdcaddress,
  DAI as daiaddress,
  PLI as pliaddress,
  XRC20Gateway as xrc20gatewayAddress,
} from '../deploy.json';

import { abi as erc20Gatewayabi } from '../artifacts/contracts/ERC20Gateway.sol/ERC20Gateway.json';
import { abi as usdtabi } from '../currencyabi/usdt.json';
import { abi as usdcabi } from '../currencyabi/usdc.json';
import { abi as daiabi } from '../currencyabi/dai.json';
import { abi as pliabi } from '../currencyabi/pli.json';
import { abi as xrc20Gatewayabi } from '../artifacts/contracts/XRC20Gateway.sol/XRC20Gateway.json';

export function createInstance(provider, name) {
  if (name === "ERC20Gateway") {
    return new ethers.Contract(erc20gatewayadders, erc20Gatewayabi, provider);
  }
  if (name === "USDT") {
    return new ethers.Contract(usdtaddress, usdtabi, provider);
  }
  if (name === "USDC") {
    return new ethers.Contract(usdcaddress, usdcabi, provider);
  }
  if (name === "DAI") {
    return new ethers.Contract(daiaddress, daiabi, provider);
  }
  if (name === "PLI") {
    return new ethers.Contract(pliaddress, pliabi, provider);
  }
  if (name === "XRC20Gateway") {
    return new ethers.Contract(xrc20gatewayAddress, xrc20Gatewayabi, provider);
  }
}
