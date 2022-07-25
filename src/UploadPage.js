import React, { useRef }  from 'react';
import './UploadPage.css';
import TextField from '@material-ui/core/TextField';
import InputLabel from '@material-ui/core/InputLabel';
import FilledInput from '@material-ui/core/FilledInput';
import InputAdornment from '@material-ui/core/InputAdornment';
import { useState } from 'react'
import { create } from 'ipfs-http-client'
import { contractAddress, abi } from "./config";
import Alert from '@material-ui/lab/Alert';
import FormControl from '@material-ui/core/FormControl';
import {
  Button,
  Snackbar,
  Card,
  CardContent,
  Typography
} from "@material-ui/core";

const client = create('https://ipfs.infura.io:5001/api/v0')
const Web3 = require("xdc3");
var Contract = require("xdc3-eth-contract");

export default function Upload(props) {
  const [fileUrl, updateFileUrl] = useState('')
  const [fileUrlBlur, updateFileUrlBlur] = useState('')
  const inputName = useRef(null);
  const inputDescription = useRef(null);
  const inputPrice = useRef(null);
  const [open, setOpen] = React.useState(false);
  const [openErrMsg, setOpenErrMsg] = React.useState(false);
  
  const handleClick = () => {
    handleClose();
    setOpen(true);
  };

  const handleClickErrMsg = () => {
    handleClose();
    setOpenErrMsg(true);
  };

  const handleClose = (event, reason) => {
    if (reason === 'clickaway') {
      return;
    }

    setOpen(false);
    setOpenErrMsg(false);
  };

  function makeid(length) {
    var result           = '';
    var characters       = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    var charactersLength = characters.length;
    for ( var i = 0; i < length; i++ ) {
      result += characters.charAt(Math.floor(Math.random() * charactersLength));
   }
   return result + new Date().valueOf();
  }

  function readFileAsync(file) {
    return new Promise((resolve, reject) => {
      let reader = new FileReader();
  
      reader.onload = () => {
        resolve(reader.result);
      };
  
      reader.onerror = reject;
  
      reader.readAsArrayBuffer(file);
    })
  }
  
  async function onChange(e) {
    var temp_ext = fileUrlBlur.name.split('.');
    var extention = "";
    if (temp_ext.length > 0){
      extention = temp_ext[temp_ext.length - 1];
    }
    let contentBuffer = await readFileAsync(fileUrl);
    console.log(contentBuffer)
    const added = await client.add(contentBuffer);
    const url = `https://ipfs.infura.io/ipfs/${added.path}`
    console.log(url)
    //updateFileUrl(url)

   // const file1 = acceptedFiles1[0]
    let contentBuffer1 = await readFileAsync(fileUrlBlur);
    console.log(contentBuffer1)
    const added1 = await client.add(contentBuffer1);
    const urlBlur = `https://ipfs.infura.io/ipfs/${added1.path}`
    console.log(urlBlur)
   // updateFileUrlBlur(urlBlur)


    var qty = "1";
    /*if (this.state.qty === "") {
      qty = "1";
    } else {
      qty = this.state.qty;
    }

    console.log(this.state.qty);*/
    const web3 = new Web3(Web3.givenProvider);
    const accounts = await web3.eth.getAccounts();
    Contract.setProvider(Web3.givenProvider);
    var contract = new Contract(abi, contractAddress);
    console.log(inputPrice.current.value)
    contract.methods.addDocument(String(makeid(5)), String(inputName.current.value), String(inputDescription.current.value), accounts[0], 
    parseInt(inputPrice.current.value), url, extention, urlBlur).send(
      {
        from: accounts[0],
        gas: 800000,
        /*gasPrice: web3.utils.toHex(web3.utils.toWei("10", "gwei")),
        value: web3.utils.toHex(web3.utils.toWei(qty, "ether"))*/
      },
      (error, transactionHash) => {
        if (error == null || error.length == 0){
          handleClick();
        }else{
          handleClickErrMsg();
        }
        //console.log(transactionHash);
        //this.setState({ transactionHash: transactionHash });
      }
    );
    
  }

  function uploadFile(e) {
    let file = e.target.files[0];
    updateFileUrl(file);
    console.log(file)
  }

  function uploadFileBlur(e) {
    let file = e.target.files[0];
    updateFileUrlBlur(file);
  }

  return (
    <Card  style={{margin: "80px auto", maxWidth: '700px', height: '400px'}} >
      <CardContent>
        <div className="App" style={{ backgroundColor: "#3f51b5"}} >
          <Typography variant="h5" component="h3" noWrap color="#FFFFFF" style={{ color: "#FFFFFF" }} fullWidth={true} >
            File Info
          </Typography>
        </div>
        <TextField id="outlined-basic" fullWidth={true}  style={{ margin: "10px auto" }} label="File name:" inputRef={inputName} variant="outlined" />
        <TextField id="outlined-basic" fullWidth={true}  style={{ margin: "10px auto" }} label="Description:" inputRef={inputDescription} variant="outlined" />
        <FormControl fullWidth={true}  sx={{ m: 1 }} style={{ margin: "20px auto" }} variant="filled">
            <InputLabel htmlFor="filled-adornment-amount">Price</InputLabel>
            <FilledInput inputRef={inputPrice}
              id="filled-adornment-amount"
              startAdornment={<InputAdornment position="start">XCD</InputAdornment>}
            />
        </FormControl>
        <div style={{ margin: "10px auto" }} >
          <label for="myFile">File: </label>
          <input type="file" name="myFile" onChange={uploadFile} />
          <label for="myFile2">Preview file: </label>
          <input type="file" name="myFile2" onChange={uploadFileBlur} />
        </div>
        <Button variant="contained" style={{ margin: "10px auto" }} color="primary" fullWidth={true} onClick={onChange}>Upload</Button>
        <Snackbar open={open} autoHideDuration={6000} onClose={handleClose}>
                      <Alert onClose={handleClose} severity="success" sx={{ width: '100%' }}>
                        Document uploaded successfully
                      </Alert>
                    </Snackbar>
                    <Snackbar open={openErrMsg} autoHideDuration={6000} onClose={handleClose}>
                      <Alert onClose={handleClose} severity="error" sx={{ width: '100%' }}>
                        Error
                      </Alert>
        </Snackbar>
      </CardContent>
    </Card>
    );
}