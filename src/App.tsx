/* eslint-disable @typescript-eslint/no-explicit-any */
/* eslint-disable @typescript-eslint/ban-ts-comment */
import React, { useState, useEffect } from 'react';
import './app.css';
import { ethers } from 'ethers';
import contractABI from './utils/contractABI.json';

import { useWeb3React } from '@web3-react/core';
import { InjectedConnector } from '@web3-react/injected-connector'

import { XdcConnect, GetWallet } from "xdc-connect";

import ConnectButton from './components/ConnectButton';
import Avatar from './components/Avatar';
import video from './img/plexus.mp4';

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

const App = () => {
  // const injectedConnector = new InjectedConnector({supportedChainIds: [51],})
  // const { activate, active, account, library, chainId } = useWeb3React();
  let xdcprovider = new Web3(Web3.givenProvider || "wss://ws.xinfin.network/");
  const library = new Web3(xdcprovider);
   const [wallet, setwallet] = useState({});
  const [active, setactive] = useState({});
  const [account, setaccount] = useState({});
  const [chainId, setchainId] = useState({});

  const [domain, setDomain] = useState('');
  let domainName = "";
  const [mintPrice, setMintPrice] = useState(0);
  const [record, setRecord] = useState(false);

  const [records, setRecords] = useState<Record | undefined>(undefined);

  const [loading, setLoading] = useState(false);
  const [mints, setMints] = useState<Array<any>>([]);

  useEffect(() => {
    //@ts-ignore
    if(chainId=== 50){
      fetchRecentMints();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [account, chainId]);

  const fetchRecentMints = async () => {
    try {
      setLoading(true);
      if (active) {
        // ethers.js
        // const signer = library.getSigner();
        // const contract = new ethers.Contract(CONTRACT_ADDRESS, contractABI.abi, signer);
        const contract = new library.eth.Contract(contractABI.abi,CONTRACT_ADDRESS);

        // Get all the domain names from our contract
        // ethers.js
        //const names = await contract['getAllNames']();
        const names = await contract.methods.getAllNames().call();

        // For each name, get the record and the address
        //names.slice(-10).reverse().map(async (name: string) => {
        const mintRecords = await Promise.all(
          names.slice(-10).reverse().map(async (name: string) => {
            //const mintRecord = await contract['getRecord'](name, 4);
            const mintRecord = await contract.methods.getRecord(name, 4).call();
            const owner = await contract.methods.getAddress(name).call();
            //const owner = await contract['getAddress'](name);
            return {
              id: names.indexOf(name) + 1,
              name: name,
              record: mintRecord,
              owner: owner
            };
          })
        );
        setLoading(false);
        console.log('MINTS FETCHED ', mintRecords);
        setMints(mintRecords);
      }
    } catch (error) {
      console.log(error);
    }
  };

  const updateDomain = async () => {
    if (!records || !domain) {
      return;
    }
    setLoading(true);
    try {
      if (active) {
        const contract = new library.eth.Contract(contractABI.abi,CONTRACT_ADDRESS);

        // const tx = await contract['setRecords'](
        //   domain,
        //   records.avatar,
        //   records.twitterTag, 
        //   records.website,
        //   records.email,
        //   records.description
        // );
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
        await tx.wait();
        console.log('Record set https://explorer.xinfin.network/txs/' + tx.hash);

        setRecords(undefined);
        setDomain('');
      }
    } catch (error) {
      console.log(error);
    }
    setLoading(false);
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
          setRecords(undefined);
          //setDomain('');
          searchDomain();
        } else {
          alert('Transaction failed! Please try again');
        }
      }
    } catch (error) {
      console.log(error);
    }
  };

  const showDomain = async (_domain = domain) => {
    console.log('showDomain '+_domain);
    setDomain(domainName);
    _domain = domainName;
    
    const contract = new library.eth.Contract(contractABI.abi,CONTRACT_ADDRESS);

    contract.methods.getId(_domain).call()
      .then(async () => {
        const res = await contract.methods.getRecords(_domain).call();
        const newRecords: Record = {
          avatar: res[0][RecordType.AVATAR],
          twitterTag: res[0][RecordType.TWITTER],
          description: res[0][RecordType.DESCRIPTION],
          email: res[0][RecordType.EMAIL],
          website: res[0][RecordType.WEBSITE],
          address: res[1]
        };
        setRecords(newRecords);
        console.log('NEW RECORDS SET');
      })
      .catch(() => {
        
      });

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
    //     setRecords(newRecords);
    //     console.log('NEW RECORDS SET');
    //   })
    //   .catch(() => {
        
    //   });
  };

  const searchDomain = async (_domain = domain) => {
    console.log('searching for '+_domain);
    if (!_domain) {
       _domain = domainName;
       return;
    }
    
    if (_domain.length < 3 || _domain.length > 30) return;

    const contract = new library.eth.Contract(contractABI.abi,CONTRACT_ADDRESS);

    contract.methods.getId(_domain).call()
      .then(async () => {
        const res = await contract.methods.getRecords(_domain).call()
        const newRecords: Record = {
          avatar: res[0][RecordType.AVATAR],
          twitterTag: res[0][RecordType.TWITTER],
          description: res[0][RecordType.DESCRIPTION],
          email: res[0][RecordType.EMAIL],
          website: res[0][RecordType.WEBSITE],
          address: res[1]
        };
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
    //     setRecords(newRecords);
    //     console.log('NEW RECORDS SET');
    //   })
    //   .catch(() => {
    //     if(_domain.length === 3){
    //       setMintPrice(335);
    //     } else if (_domain.length <= 6) {
    //       setMintPrice(170);
    //     } else {
    //       setMintPrice(100);
    //     }
    //   });
  };

  const renderInputForm = () => {
    //@ts-ignore
    //if( networks[chainId?.toString(16)]?.includes('HPB') ){
    if (wallet.connected && chainId !== 50) {
      return (
        <div className="connect-wallet-container">
          <h2>Connected to wrong network</h2>
          {/* This button will call our switch network function */}
          {/*<button className="cta-button mint-button" onClick={switchNetwork }>
          Please switch to HPB Mainnet btn
          </button>*/}
        </div>
      );
    }

    return (
      <div className="form-container">
      <div className="first-row">
        <span id="domain" className="record">
          <input
            type="text"
            value={domain}
            placeholder="domain"
            onChange={e => {
              setRecords(undefined);
              setMintPrice(0);
              setDomain(e.target.value);
            }}
          />
          <p className="tld"> {tld} </p>
          </span>
        </div>
        {records && (
          <>
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
          </>
        )}
        <div className="button-container">
          <button
            className="cta-button mint-button"
            onClick={() => {
              searchDomain();
            }}
          >
            Search
          </button>
          {(records && records.address === account) ? (
            <button className="cta-button mint-button" disabled={loading} onClick={updateDomain}>
              Update
            </button>
          ) : mintPrice > 0 ? (
            <button className="cta-button mint-button" onClick={mintDomain}>
              Mint for {mintPrice} $XDC
            </button>
          ) : null}
        </div>
        {loading ? (
          <p>Loading the last 10 minted domains in the smart contract...</p>
        ): (
          <p>Last 10 Minted Domains</p>
          )}
        <div className="mint-list">
            {mints.map((mint, index) => {
              return (
                <div className="mint-item" key={index}>
                  <div className="mint-row">
                    <a
                      className="link"
                      href={`https://explorer.xinfin.network/nft/${CONTRACT_ADDRESS}/${mint.id}`}
                      target="_blank"
                      rel="noopener noreferrer"
                    >
                      <p className="underlined">
                        {mint.name}
                        {tld}
                      </p>
                    </a>
                    <a href="#"><button className="edit-button" onClick={() => {domainName = mint.name; showDomain();}} >
                      <img
                        className="edit-icon"
                        src="https://img.icons8.com/metro/26/000000/search.png"
                        alt="Edit button"
                      />
                    </button></a>
                  </div>
                  <p> {mint.record} </p>
                </div>
              );
            })}
        </div>
      </div>
    );
  };

  /**
   * <button
                  className="cta-button connect-wallet-button"
                  onClick={() => {
                    activate(MetaMask);
                  }}
                >
                  Connect Wallet
                  </button>
                  
   */
  return (
      <div className="body-container">
        <div className="main-container-wrapper">
          <video autoPlay loop muted>
            <source src={video} type="video/mp4"/>
          </video>
          <div className="main-container flex">
            
            {active==true ? renderInputForm() :
              <>
                <div className="flex-item left">
                  <h1>XDC Name<br/>Service
                  <XdcConnect
                    btnClass="cta-button connect-wallet-button"
                    btnName="Connect Wallet" theme="dark"
                    onConnect={(wallet) => {
                       setwallet(wallet);
                      setactive(wallet.connected);
                      setaccount(wallet.address);
                      setchainId(wallet.chain_id);
                    }} 

                  />
                  </h1>
                </div>
                <ConnectButton/>
                {/*mints && fetchRecentMints()*/}
                </>
              }
          </div>
        </div>
      </div>
  );


};

export default App;
