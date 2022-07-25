/* eslint-disable @typescript-eslint/ban-ts-comment */
import React, { useState } from 'react';
import { XdcConnect, GetWallet } from "xdc-connect";
import { createImportSpecifier } from 'typescript';
import hpbLogo from '../img/xdc-logo.png';

const Web3 = require("xdc3");

export default function ConnectionStatus() {
  //const { activate, active, account, library, chainId } = useWeb3React();
  let xdcprovider = new Web3(Web3.givenProvider || "wss://ws.xinfin.network/");
  let library = new Web3(xdcprovider);

  const [wallet, setwallet] = useState({});
  const [active, setactive] = useState({});
  const [account, setaccount] = useState({});
  const [chainId, setchainId] = useState({});

  // let xdcprovider = new Web3("https://rpc.apothem.network");
  // const library = new Web3(xdcprovider);
  //const web3React = useWeb3React();

  const connectWallet = () => {
    return;
  }
      

  return (
    <>{connectWallet()}</>
  );
}
