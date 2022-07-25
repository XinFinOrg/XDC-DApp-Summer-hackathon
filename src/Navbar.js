import React from "react";
import { BrowserRouter, Route, Link } from "react-router-dom";
import { useNavigate  } from 'react-router-dom';
import { makeStyles } from '@material-ui/core/styles';
import AppBar from '@material-ui/core/AppBar';
import Toolbar from '@material-ui/core/Toolbar';
import Typography from '@material-ui/core/Typography';
import Button from '@material-ui/core/Button';
import IconButton from '@material-ui/core/IconButton';

function Navbar() {
    const navigate = useNavigate();
    const redirect_upload = () => {
        navigate('/upload')
      }
      const redirect_home = () => {
        navigate('/')
      }
  return (

    <AppBar position="static">
  <Toolbar>
    <Button color="inherit" onClick={redirect_home}>Home</Button>
    <Button color="inherit" onClick={redirect_upload}>Upload</Button>
  </Toolbar>
</AppBar>

   
  );
}

export default Navbar;