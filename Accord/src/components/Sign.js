import React, { useState, useEffect, useMemo } from "react";
import { Result, Spin } from "antd";
import Packet from "./Packet";
import { useParams } from "react-router-dom";
import { createSignatureNFT, getMintedNFT } from "../util/nftport";
import { fetchMetadata, retrieveFiles } from "../util/stor";
import { getExplorerUrl, xdcAddress } from "../util";
import {
  markContractCompleted,
} from "../contract/deploy";

function Sign({ account }) {
  const { signId } = useParams(); // cid
  const [data, setData] = useState({});
  const [loading, setLoading] = useState(false);
  const [result, setResult] = useState();

  const fetchData = async () => {
    console.log("fetch", signId);
    if (!signId) {
      return;
    }

    setLoading(true);

    try {
      const res = await fetchMetadata(signId);
      setData(res.data);
      console.log("esignature request", res.data);
    } catch (e) {
      console.error(e);
      alert("error getting signdata" + e);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchData();
  }, [signId]);

  const authed = useMemo(() => {
    return data && (xdcAddress(data.signerAddress) || '').toLowerCase() === xdcAddress(account).toLowerCase()
  } ,[data, account])

  const { description, title, signerAddress, address } = data;

  const contractAddress = xdcAddress(address);

  const sign = async (signatureData) => {
    let nftResults = {};

    setLoading(true);

    let res;

    try {
      //   https://docs.nftport.xyz/docs/nftport/b3A6MjE2NjM5MDM-easy-minting-w-url
      res = await createSignatureNFT(
        title,
        description,
        signerAddress,
        signatureData
      );
      nftResults["signatureNft"] = res.data;
      const url = nftResults["transaction_external_url"] || ""
      res = await markContractCompleted(address, url || signId);
      nftResults = { nftResults, ...res };
      try {
        res = await getMintedNFT(res["hash"]);
        nftResults = { nftResults, ...res };
      } catch (e) {
        // soft error for token id fetch.
        console.error(e);
      }
      setResult(nftResults);
    } catch (e) {
      console.error("error signing", e);
      alert("Error completing esignature: " + JSON.stringify(e));
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return (
      <div className="container">
        <Spin size="large" />
      </div>
    );
  }

  if (result) {
    return (
      <div className="container">
        {/* <img src={logo} className="header-logo" /> */}
        <br />
        <br />
        <Result
    status="success"
    title="Transaction complete!"
    subTitle="Access your completed xdc contract and signature packet below"></Result>

        <a href={getExplorerUrl(contractAddress)} target="_blank">
          View Contract
        </a>
        <p>Full response below:</p>
        <pre>{JSON.stringify(result, null, "\t")}</pre>
      </div>
    );
  }

  return (
    <div className="container boxed white">
      <h2 className="centered">Sign Documents</h2>
      <br />
      <Packet {...data} contractAddress={contractAddress} authed={authed} sign={sign} signId={signId} />
    </div>
  );
}

Sign.propTypes = {};

export default Sign;
