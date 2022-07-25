import React, { useState, useEffect } from 'react';
import { contractAddress, abi } from "./config";
import { makeStyles } from "@material-ui/core/styles";
import axios from 'axios'
import fileDownload from 'js-file-download'
import FileViewer from 'react-file-viewer';
import './GalleryPage.css';
//import Snackbar from '@@material-ui/core/Snackbar';

import GridList from "@material-ui/core/GridList";
import Alert from '@material-ui/lab/Alert';
import {
  Button,
  Card,
  Snackbar,
  CardHeader,
  CardContent,
  Typography
} from "@material-ui/core";

const Web3 = require("xdc3");
var Contract = require("xdc3-eth-contract");
const useStyles = makeStyles((theme) => ({
  root: {
    "& > *": {
      margin: "auto"
    }
  },
  gridList: {
    width: "120%",
    height: "auto",
    margin: "auto"
  },
  card: {
    height: "100%",
  }
}));

/*const tileData = [
    {
      path:
        "https://images-na.ssl-images-amazon.com/images/I/71qmF0FHj7L._AC_SX679_.jpg",
      name: "title"
    },
    {
      path:
        "https://images-na.ssl-images-amazon.com/images/I/71qmF0FHj7L._AC_SX679_.jpg",
      name: "title"
    }
  ];*/

function GalleryPage() {
    const [tileData, setTileData] = useState([]);
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


    function handleDownload (url, filename) {
      axios.get(url, {
        responseType: 'blob',
      })
      .then((res) => {
        fileDownload(res.data, filename)
      })
    }
      
    useEffect(() => {
        const fetchData = async () => {
            // get the data from the api
        const web3 = new Web3(Web3.givenProvider);
        const accounts = await web3.eth.getAccounts();
        Contract.setProvider(Web3.givenProvider);
        var contract = new Contract(abi, contractAddress);
        //console.log(inputPrice.current.value)
        let tx = await contract.methods.getDocuments().call({from:accounts[0]}, function(err,data){
            console.log(data);
            });
        
            var val = {};
            var monthsArray = [];
            var keys = tx[0];
            let values = tx[1];
           
            for (let i = 0; i < values.length; i++) {
                val.key = keys[i];
                val.description = values[i].description;
                val.name = values[i].name;
                val.owner = values[i].owner;
                //val.path = [{uri: values[i].path}];
                val.path = values[i].path;
                val.price = values[i].price;
                val.type = values[i].path_extension;
                console.log(val.path)
                monthsArray.push({...val});
            }
         setTileData(monthsArray)   
         
        return tx;
          }
          
        console.log(fetchData());
    
      },[tileData.whenToUpdateProp]);

      async function handleSubmit(index) {
        console.log(index);
        var qty = "1";
        const web3 = new Web3(Web3.givenProvider);
        const accounts = await web3.eth.getAccounts();
        Contract.setProvider(Web3.givenProvider);
        var contract = new Contract(abi, contractAddress);
        let tx1 = await contract.methods.buyDocument(tileData[index].owner).send(
          {
            from: accounts[0],
            value: web3.utils.toHex(web3.utils.toWei(tileData[index].price, "ether"))
          },
          (error, transactionHash) => {
            console.log(transactionHash);
            if (error == null || error.length == 0){
              contract.methods.getDocumentByKey(tileData[index].key).call({from:accounts[0]}, function(err,data){
                if (error == null || error.length == 0){
                  handleDownload(data.path, data.name + '.' + data.path_extension)
                  handleClick();
                  console.log(data);
                  return;
                }else{
                  handleClickErrMsg();
                }
             });
            }else{
              handleClickErrMsg();
            }
          }
        );

      }

      const classes = useStyles();
      return (
        <div className="App">
          <GridList cellHeight={600} spacing={0} cols={4} className={classes.gridList}  >
            {tileData.map((tile, index) => (
              <Card  key={tile.key} className={classes.card} style={{margin: "20px"}}>
                  <CardContent>
                  <div className="App" style={{ backgroundColor: "#3f51b5" }}>
                  <Typography variant="h5" component="h3" noWrap color="#FFFFFF" style={{ color: "#FFFFFF" }} fullWidth={true} >
                      {tile.name}
                    </Typography>
                    </div>
                    <Typography gutterBottom variant="body2" component="h2" noWrap>
                      {tile.description}
                    </Typography>
                    <FileViewer
                      fileType={tile.type}
                      filePath={tile.path}
                     />
                    
                    <Typography gutterBottom variant="body2" component="h2" noWrap>
                      Price: {tile.price} XDC
                    </Typography>
                   <Button variant="contained" color="primary" fullWidth={true} onClick={() => handleSubmit(index)}>Buy</Button>
                   <Snackbar open={open} autoHideDuration={6000} onClose={handleClose}>
                      <Alert onClose={handleClose} severity="success" sx={{ width: '100%' }}>
                          Document purchased successfully
                      </Alert>
                    </Snackbar>
                    <Snackbar open={openErrMsg} autoHideDuration={6000} onClose={handleClose}>
                      <Alert onClose={handleClose} severity="error" sx={{ width: '100%' }}>
                        Error
                      </Alert>
                    </Snackbar>
                  </CardContent>
                
              </Card>
            ))}
          </GridList>
        </div>
      );
}

export default GalleryPage;
