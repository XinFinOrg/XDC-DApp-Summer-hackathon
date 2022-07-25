/* eslint-disable @typescript-eslint/no-explicit-any */
/* eslint-disable @typescript-eslint/ban-ts-comment */
import React, { useState, useEffect } from 'react';
import './app.css';
import { ethers } from 'ethers';
import contractABI from './utils/contractABI.json';

import { useWeb3React } from '@web3-react/core';

import RecentlyMinted from './components/RecentlyMinted';
import ConnectButton from './components/ConnectButton';
import Avatar from './components/Avatar';
import { InjectedConnector } from '@web3-react/injected-connector';
import video from './img/plexus.mp4';

declare var window: any

//const MetaMask = new InjectedConnector({});

const Web3 = require("xdc3");

const tld = '.xdc';

// Constants
const CONTRACT_ADDRESS = 'xdc7f9ca4193d9539ceb5D3D664C3453C8e8Bb6b1Ad';

export type Record = {
  avatar: string;
  twitterTag: string;
  website: string;
  email: string;
  description: string;
  address: string;
};

export enum RecordType {
  AVATAR = 0,
  TWITTER = 1,
  WEBSITE = 2,
  EMAIL = 3,
  DESCRIPTION = 4
}

const MintedDomain = () => {
  //const { activate, active, account, library, chainId } = useWeb3React();
  let xdcprovider = new Web3(Web3.givenProvider || "wss://ws.xinfin.network/");
  const library = new Web3(xdcprovider);
  const [wallet, setwallet] = useState({});
  const [active, setactive] = useState({});
  const [account, setaccount] = useState({});
  const [chainId, setchainId] = useState({});
  const [domain, setDomain] = useState('');
  const [mintPrice, setMintPrice] = useState(0);
  const [balanceQty, setBalanceQty] = useState(0);

  const [records, setRecords] = useState<Record | undefined>(undefined);

  const [loading, setLoading] = useState(true);
  const [mints, setMints] = useState<Array<any>>([]);

  let myBalance = 0;
  
  useEffect(() => {
    
    //@ts-ignore
    //if (networks[chainId?.toString(16)] === 'HPB Mainnet') {
    //if( networks[chainId?.toString(16)]?.includes('HPB') ){
      if (chainId == 50) {
        fetchMints();
      }
  
    //}
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [account, chainId]);

  const switchNetwork = async () => {
    if (account) {
      try {
        // Try to switch to the XDC
        await library.send(
          'wallet_switchEthereumChain',
          [{ chainId: '0x32' }] // Check networks.js for hexadecimal network ids
        );
      } catch (error: any) {
        // This error code means that the chain we want has not been added to MetaMask
        // In this case we ask the user to add it to their MetaMask
        if (error.code === 4902) {
          try {
            await library.send('wallet_addEthereumChain', [
              {
                chainId: '0x32',
                chainName: 'XDC Testnet',
                rpcUrls: ['https://rpc.apothem.network/'],
                nativeCurrency: {
                  name: 'XDC Testnet',
                  symbol: 'XDC',
                  decimals: 18
                },
                blockExplorerUrls: ['https://explorer.apothem.network/']
              }
            ]);
          } catch (error) {
            console.log(error);
          }
        }
        console.log(error);
      }
    }
  };

  const updateDomain = async () => {
    if (!records || !domain) {
      return;
    }
    //setLoading(true);
    try {
      if (active) {
        const contract = new library.eth.Contract(contractABI.abi,CONTRACT_ADDRESS);
        const tx = await contract.methods.setRecords(
          domain,
          records.avatar,
          records.twitterTag,
          records.website,
          records.email,
          records.description
        ).send({
          from: account,
          gasLimit: 3000000
        });
        // const tx = await contract['setRecords'](
        //   domain,
        //   records.avatar,
        //   records.twitterTag,
        //   records.website,
        //   records.email,
        //   records.description
        // );
        await tx.wait();
        console.log('Record set https://explorer.xinfin.network/txs/' + tx.hash);

        fetchMints();
        setRecords(undefined);
        setDomain('');
      }
    } catch (error) {
      console.log(error);
    }
    //setLoading(false);
  };

  const fetchMints = async () => {
    try {
      setLoading(true);
      if (active) {
        const contract = new library.eth.Contract(contractABI.abi,CONTRACT_ADDRESS);
        const myBalance = await contract.methods.balanceOf(account).call();
        //const myBalance = await contract['balanceOf'](account);
        setBalanceQty(myBalance);
        console.log("myBalance "+myBalance);
        if(myBalance!==0){
          // Get all the domain names from our contract
          console.log('get from wallet' + account);
          const names = await contract.methods.walletOfOwner(account).call();
          //const names = await contract['walletOfOwner'](account); //return all token ID (instead of name)
          //const names = await contract['getAllNames']();

          // For each name, get the record and the address
          //names.map(async (name: string) => {
          const mintRecords = await Promise.all(
            names.map(async (tokenId: string) => {
              const name = await contract.methods.names(tokenId).call();
              console.log('name '+name);
              const mintRecord = await contract.methods.getRecord(name, 4).call();
              const owner = await contract.methods.getAddress(name).call();
              return {
                  id: tokenId,
                  name: name,
                  record: mintRecord,
                  owner: owner
                };
            })
          );
          setMints(mintRecords);
          console.log('MINTS FETCHED ', mintRecords);
        }
        setLoading(false);
      }
    } catch (error) {
      console.log(error);
    }
  };

  const mintDomain = async () => {
    // Don't run if the domain is empty
    if (!domain) {
      return;
    }

    // Alert the user if the domain is too short
    if (domain.length < 3) {
      alert('Domain must be at least 3 characters long');
      return;
    }
    // Calculate price based on length of domain (change this to match your contract)
    const price = domain.length === 3 ? '335' : domain.length <= 6 ? '170' : '100';
    console.log('Minting domain', domain, 'with price', price);
    try {
      if (active) {
        // const Xdc3 = require("xdc3");
        // let provider = new Xdc3.providers.HttpProvider('https://rpc.apothem.network');
        // let xdc3 = new Xdc3(provider);
        const contract = new library.eth.Contract(contractABI.abi,CONTRACT_ADDRESS);

        console.log('Going to pop wallet now to pay gas...');
        // const tx = await contract['register'](domain, {
        //   value: ethers.utils.parseEther(price), gasLimit: 3000000
        // });
        const tx = await contract.methods.register(domain).send({
          from: account,
          value: ethers.utils.parseEther(price), 
          gasLimit: 3000000
        });
        // Wait for the transaction to be mined
        const receipt = await tx.wait();

        // Check if the transaction was successfully completed
        if (receipt.status === 1) {
          console.log('Domain minted! https://explorer.xinfin.network/txs/' + tx.hash);

          setTimeout(() => {
            fetchMints();
          }, 2000);

          setRecords(undefined);
          setDomain('');
        } else {
          alert('Transaction failed! Please try again');
        }
      }
    } catch (error) {
      console.log(error);
    }
  };

  const searchDomain = async (_domain = domain) => {
    console.log('searching 227 '+_domain);
    if (!_domain) {
      return;
    }

    if (_domain.length < 3 || _domain.length > 30) return;

    const contract = new library.eth.Contract(contractABI.abi,CONTRACT_ADDRESS);
    contract.methods.getId(_domain).call()
      .then(async () => {
        const res = await contract.methodsgetRecords(_domain);
        const newRecords: Record = {
          avatar: res[0][RecordType.AVATAR],
          twitterTag: res[0][RecordType.TWITTER],
          description: res[0][RecordType.DESCRIPTION],
          email: res[0][RecordType.EMAIL],
          website: res[0][RecordType.WEBSITE],
          address: res[1]
        };
    // contract['getId'](_domain)
    //   .then(async () => {
    //     const res = await contract['getRecords'](_domain);
    //     const newRecords: Record = {
    //       avatar: res[0][RecordType.AVATAR],
    //       twitterTag: res[0][RecordType.TWITTER],
    //       description: res[0][RecordType.DESCRIPTION],
    //       email: res[0][RecordType.EMAIL],
    //       website: res[0][RecordType.WEBSITE],
    //       address: res[1]
    //     };

        setRecords(newRecords);
        console.log('NEW RECORDS SET');
      })
      .catch(() => {
        if(_domain.length === 3){
          setMintPrice(335);
        } else if (_domain.length <= 6) {
          setMintPrice(170);
        } else {
          setMintPrice(100);
        }
      });
  };

  const renderInputForm = () => {

    return (
      <div className="form-container">
        {records && (
          <>
            <span id="domain" className="record">
              <input
                type="text"
                value={domain}
                className="readonly"
                readOnly={true}
                placeholder="domain"
                onChange={e => {
                  setRecords(undefined);
                  setMintPrice(0);
                  setDomain(e.target.value);
                }}
              />
              <p className="tld"> {tld} </p>
            </span>
            <span id="addr" className="record">
              <input
                type="text"
                value={records.address}
                placeholder="enter"
                readOnly={true}
                className="readonly"
              />
            </span>
            <span id="desc" className="record">
              <input
                type="text"
                value={records.description}
                placeholder="enter"
                onChange={e => setRecords({ ...records, description: e.target.value })}
                readOnly={account !== records.address}
                className={account !== records.address ? 'readonly' : ''}
              />
            </span>
            <span id="email" className="record">
              <input
                type="text"
                value={records.email}
                placeholder="enter"
                onChange={e => setRecords({ ...records, email: e.target.value })}
                readOnly={account !== records.address}
                className={account !== records.address ? 'readonly' : ''}
              />
            </span>
            <span id="website" className="record">
              <input
                type="text"
                value={records.website}
                placeholder="enter"
                onChange={e => setRecords({ ...records, website: e.target.value })}
                readOnly={account !== records.address}
                className={account !== records.address ? 'readonly' : ''}
              />
            </span>
            <span id="twitter" className="record">
              <input
                type="text"
                value={records.twitterTag}
                placeholder="enter"
                onChange={e => setRecords({ ...records, twitterTag: e.target.value })}
                readOnly={account !== records.address}
                className={account !== records.address ? 'readonly' : ''}
              />
            </span>
            <span id="avatar" className="record">
              <input
                type="text"
                value={records.avatar}
                placeholder="enter"
                onChange={e => setRecords({ ...records, avatar: e.target.value })}
                readOnly={account !== records.address}
                className={account !== records.address ? 'readonly' : ''}
              />
            </span>
            <Avatar domain={domain+tld} url={records.avatar} />
            <div className="button-container">
              <button
                className="cta-button mint-button"
                disabled={loading}
                onClick={() => {
                  searchDomain();
                }}
              >
                Search
              </button>
              {records ? (
                <button className="cta-button mint-button" disabled={loading} onClick={updateDomain}>
                  Update
                </button>
              ) : mintPrice > 0 ? (
                <button className="cta-button mint-button" disabled={loading} onClick={mintDomain}>
                  Mint for {mintPrice} $XDC
                </button>
              ) : null}
            </div>
          </>
        )}
      </div>
    );
  };

  return (
    <div className="body-container">
      <div className="main-container-wrapper">
        <video autoPlay loop muted>
          <source src={video} type="video/mp4"/>
        </video>
          <div className="recently-minted">
            {renderInputForm()}
            <RecentlyMinted
                minted={mints}
                loading={loading}
                balance={balanceQty}
                onEdit={(name: string) => {
                  setDomain(name);
                  searchDomain(name);
                }}
              />
          </div>
      </div>
    </div>
  );
};

export default MintedDomain;
