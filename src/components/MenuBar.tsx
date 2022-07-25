// @ts-nocheck
import React, { useState, useEffect } from 'react';
import { useWeb3React } from '@web3-react/core';
import { InjectedConnector } from '@web3-react/injected-connector';
import { Navbar, Nav, NavItem, NavDropdown, Container } from 'react-bootstrap';
import { Link } from "react-router-dom";
import ConnectionStatus from '../components/ConnectionStatus';
import ConnectButton from './ConnectButton';
import NavbarCollapse from 'react-bootstrap/esm/NavbarCollapse';
import hpb from '../img/xdc-logo.png';
//import { XdcConnect, GetWallet } from "xdc-connect";
//const Web3 = require("xdc3");

const MenuBar = () => {
  const { account } = useWeb3React();
  // let xdcprovider = new Web3(Web3.givenProvider || "wss://ws.xinfin.network/");
  // const library = new Web3(xdcprovider);
  // const [wallet, setwallet] = useState({});
  // const [active, setactive] = useState({});
  // const [account, setaccount] = useState({});
  // const [chainId, setchainId] = useState({});
  // console.log("wallet1 "+wallet.address);
  // let xdcprovider = new Web3(Web3.givenProvider);
  // const library = new Web3(xdcprovider);
  
  return (
    <Navbar fixed="top" collapseOnSelect expand="lg" bg="white">
        <Container>
        <Link to="/"><Navbar.Brand><img src={hpb} width="50"/>&nbsp; XDC Name Service</Navbar.Brand></Link>
        <Navbar.Toggle aria-controls="responsive-navbar-nav" />
        <Navbar.Collapse id="responsive-navbar-nav">
            <Nav className="me-auto">
            </Nav>
            <Nav>
            {account ? (
          <Link to="/domains">My Domains</Link>
        ): (
          <div/>
          )}            
            <ConnectionStatus />
            </Nav>
        </Navbar.Collapse>
        </Container>
    </Navbar>
  );
}

export default MenuBar;