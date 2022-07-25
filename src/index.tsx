import { StrictMode } from 'react';
import * as ReactDOM from 'react-dom';
import App from './App';
import { Web3ReactProvider } from '@web3-react/core';
import { Web3Provider } from '@ethersproject/providers';
import 'bootstrap/dist/css/bootstrap.min.css';
import {
  BrowserRouter,
  Routes,
  Route,
} from "react-router-dom";
import './app.css';
import MintedDomain from './mintedDomain';
import MenuBar from './components/MenuBar';
import Footer from './components/Footer';
import ScrollToTop from './components/ScrollToTop';
import { XdcConnect, GetWallet } from "xdc-connect";

const Web3 = require("xdc3");

function getLibrary(provider: any, connector: any) {
  //return new Web3Provider(provider); // this will vary according to whether you use e.g. ethers or web3.js
  //return new Web3(Web3.currentProvider);
  //let xdcprovider = new Web3.providers.WebsocketProvider('wss://ws.xinfin.network/ws');
  let xdcprovider = new Web3(Web3.givenProvider || "wss://ws.xinfin.network/");
  return new Web3(xdcprovider);
}
//  <StrictMode>
//  </StrictMode>,

ReactDOM.render(
    <Web3ReactProvider getLibrary={getLibrary}>
      <BrowserRouter>
      <div className="App">
        <ScrollToTop />
        <MenuBar/>
        <Routes>
          <Route path="/" element={<App />} />
          <Route path="domains" element={<MintedDomain />} />
        </Routes>
        <Footer/>
      </div>
      </BrowserRouter>
    </Web3ReactProvider>,

  document.getElementById('root')
);
