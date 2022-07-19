<template>
  <el-row>
    <!-- project logo -->
    <el-col :span="2">
      <div style="float: right;">
        <el-image style="width: 35px;height: 33px; float: right;margin-right: 10px;margin-top: 10px;" :src="logo"/>
      </div>        
    </el-col> 

    <!-- project menus -->
    <el-col :span="14">
      <el-menu
        :default-active="activeIndex"
        class="el-menu-demo"
        mode="horizontal"
        :ellipsis="false"
        :unique-opened="true"
        background-color="#606266"
        style="float: left;width: 100%;"
        text-color="#fff"
        active-text-color="#ffd04b"
        @select="handleSelect"
      >
        <el-menu-item index="1">Daos</el-menu-item>
        <el-menu-item index="2">Learnings</el-menu-item>
        <el-menu-item index="3">Auctions</el-menu-item>
        <el-menu-item index="4">Grants</el-menu-item>
        <el-menu-item index="5">Votes</el-menu-item>
        <el-menu-item index="6">Chats</el-menu-item>
      </el-menu>
    </el-col>
    <!-- notify component -->
    <el-col :span="4">
      <el-popover
        placement="bottom-start"
        title="Transactions"
        :width="200"
        trigger="click"
      >
        <template #reference>
          <el-button 
            circle 
            color="#606266" 
            size="large" 
            style="margin-top: 10px;
            float: right;"
            @click="onClickNotify"
          >
            <el-badge 
              :isDot="transactionCount>0" 
              :max="99" 
              class="item" 
              style="margin-top: 15px;"
              type="primary"
            >
              <el-icon :size="20"><bell /></el-icon>
            </el-badge>
          </el-button>
        </template>
        <el-row :gutter="20">
          <template v-for="tx in transactions" :key="tx">
            <el-col :span="20">
              <el-link type="primary" :href="transactionExplorerUrl(tx)" target="_blank">{{tools.shortString(tx)}}</el-link>
              <el-icon @click="onClickToCopy(tx)" style="margin-left: 10px;"><document-copy /></el-icon>
            </el-col>
          </template>
        </el-row>
        <el-button size="small" type="primary" style="float: right;" @click="onClearTransactions">Clear</el-button>
      </el-popover> 
    </el-col>       
    
    <!-- user addr -->
    <el-col :span="3">
      <el-popover
        placement="bottom-start"
        :title="networkName"
        :width="200"
        trigger="hover"
      >
        <template #reference>
          <a v-if="userName != ''" @click="onClickToCopy(userName)" style="margin-top: 17px;padding-right: 10px;float: right;">{{shortName}}</a>
          <a v-if="userName === ''" @click="onClickToCopy(userAddr)" style="margin-top: 17px;padding-right: 10px;float: right;">{{shortAddr}}</a>
        </template>
        <el-row :gutter="20" v-if="userName != ''">
          <a @click="onClickToCopy(userName)" style="margin-top: 17px">{{userName}}</a><br/>
        </el-row>
        <el-row :gutter="20">
          <a @click="onClickToCopy(userAddr)" style="margin-top: 17px">{{userAddr}}</a>
        </el-row>
      </el-popover>      
    </el-col>

    <!-- connect component -->
    <el-col :span="1">
      <div style="float: right;">
          <el-dropdown button trigger="click" style="width: 35px;height: 33px; float: right;margin-right: 20px;margin-top: 10px;">
            <el-image :src="metamask" />
            <template #dropdown>
              <el-dropdown-menu>
                <el-dropdown-item @click="onConnect" :innerText="connectStatus"></el-dropdown-item>
                <el-dropdown-item @click="onNetworkConfig">Network Config</el-dropdown-item>
              </el-dropdown-menu>
            </template>
          </el-dropdown>
      </div>  
    </el-col>

  </el-row>
  
  <!-- side drawer component-->
  <el-drawer v-model="showSwitchNetwork" direction="rtl" destroy-on-close @open="onDrawerOpen">
      <template #header>
        <h4>Select to config the network</h4>   
      </template>
      <template #default>
        <table style="margin-left: 50px;">
          <tr>
            <td style="width:100px">Network</td>
            <td style="width:200px">
              <el-select 
                v-model="networkSelected"
                style="width:200px" 
                placeholder="Select Network" 
                :teleported="false"
                @change="onNetworkSelected"
                filterable
              >
                <el-option
                  v-for="item in networkOptions"
                  :key="item.chainName"
                  :label="item.chainName"
                  :value="item.chainId"
                />
              </el-select>
            </td>
          </tr>
          <tr>
            <td style="width:100px">Storage</td>
            <td style="width:200px">
              <el-select 
                v-model="storageSelected"
                style="width:200px"
                placeholder="Select Storage"
                :teleported="false"
                filterable
                disabled
              >
               <!--  <el-option key="swarm" label="Swarm Network" value="swarm"/>
                <el-option key="bundlr" label="Bundlr Network" value="bundlr"/> -->
                <el-option key="filcoin" label="Filcoin Network" value="filcoin"/>
              </el-select> 
            </td>
          </tr>
          <tr v-if="storageSelected==='bundlr'">
            <td style="width:100px">Token</td>
            <td style="width:200px">
              <el-select
                v-model="tokenSelected"
                style="width:200px"
                placeholder="Select Network"
                :teleported="false"
                filterable
              >
                <el-option
                  v-for="item in tokenOptions"
                  :key="item"
                  :label="item"
                  :value="item"
                />
              </el-select>               
            </td>
          </tr>
          <tr v-if="storageSelected==='filcoin'">
            <td style="width:100px">ApiToken</td>
            <td style="width:200px;">
              <el-input
                v-model="apiTokenSelected"
                style="width:200px;margin-left: 10px;"
                clearable
              >
              </el-input>
            </td>
          </tr>
        </table>         
      </template>
      <template #footer>
        <div style="flex: auto">
          <el-button @click="cancelSwitchNetwork">cancel</el-button>
          <el-button type="primary" @click="confirmSwitchNetwork">confirm</el-button>
        </div>
      </template>
    </el-drawer>  
</template>

<script lang="ts">  
export default {
  name: 'NavBar',
  props: {
  },
}
</script>

<script setup lang="ts">
import { ref } from "vue"

// import { Resolution } from '@unstoppabledomains/resolution';

import * as tools from "../libs/tools"
import * as connect from "../libs/connect"
import * as network from "../libs/network"
import * as element from "../libs/element"

import * as constant from "../constant"

const logo = require('@/assets/logo.png');
const metamask = require('@/assets/metamask.svg');
// const resolution = new Resolution();
const userName = connect.connectState.userName;
const shortName = connect.connectState.shortName;
const userAddr = connect.connectState.userAddr;
const shortAddr = connect.connectState.shortAddr;
const networkName = ref("");
const activeIndex = connect.connectState.activeIndex;
const connectStatus = ref("Connect Wallet");
const showSwitchNetwork = ref(false);
const transactions = connect.connectState.transactions;
const transactionCount = connect.connectState.transactionCount;
const networkSelected = ref(connect.connectState.chainId);
const storageSelected = ref(connect.connectState.storage);
const tokenSelected = ref(connect.connectState.currency);
const apiTokenSelected = ref(connect.connectState.web3Storage==='' ? constant.web3StorageAppKey : connect.connectState.web3Storage);
const networkOptions = constant.chainList;
const tokenOptions = ref((constant.tokenList as any)[connect.connectState.chainId]);

//transaction explore url
const transactionExplorerUrl = (transaction:string) => {
  for(const i in constant.chainList){
    if(connect.connectState.chainId === constant.chainList[i].chainId){
      const blockExplorerUrls = constant.chainList[i].blockExplorerUrls;
      return blockExplorerUrls + '/tx/' + transaction;
    }
  }

  return transaction;
}

//connect to metamask
const connectNetwork = async () => {
  await connect.networkConnect().then(async (res) => {
    if(res){
      element.elMessage('success', 'You have connected to the wallet.');
    }else{
      await connect.cancelConnect();
      userAddr.value = "";
      shortName.value = "";
      shortAddr.value = "";
      networkName.value = "";
      connectStatus.value = "Connect Wallet";

      element.elMessage('error', 'Connect to the wallet failed.');  
    }
         
  });
}

//set connect callback function
connect.connectState.connectCallback = async () => {
    userName.value = connect.connectState.userName.value;
    shortName.value = tools.shortString(userName.value);
    userAddr.value = connect.connectState.userAddr.value;
    shortAddr.value = tools.shortString(userAddr.value);

    networkName.value = network.getChainName(connect.connectState.chainId);
    connectStatus.value = "Cancel Connect";  
};

//disconnect from metamask
const disConnectNetwork = async () => {
    await connect.cancelConnect();

    connectStatus.value = "Connect Wallet";
    userName.value = "";
    shortName.value = "";
    userAddr.value = "";
    shortAddr.value = "";
    networkName.value = "";

    element.elMessage('warning', 'You have disconnected to the wallet.');                     
}

//on wallet address changed
const accountsChanged = async (accounts: string[]) => {
  if(accounts.length === 0){
    return await disConnectNetwork();
  }

  if(connectStatus.value === "Cancel Connect"){
    await connectNetwork();
    connect.connectState.searchCallback();
  }
}

connect.connectState.accountsChanged = accountsChanged;

// connect.accountsChanged(accountsChanged);

//on wallet network changed
const chainChanged = async () => {
  //clear transactions when network changed
  connect.connectState.transactions.value = new Array();
  connect.connectState.transactionCount.value = 0;

  if(!(await connect.connected())){
    return;
  }

  if(connectStatus.value === "Cancel Connect"){
    await connect.connectState.connectCallback();
    connect.connectState.searchCallback();
  } 
}

connect.connectState.chainChanged = chainChanged;

// connect.chainChanged(chainChanged); 

//on drawer open to switch network
const onDrawerOpen = async () => {
  networkSelected.value = connect.connectState.chainId;
  storageSelected.value = connect.connectState.storage;
  tokenSelected.value = connect.connectState.currency;
  tokenOptions.value = (constant.tokenList as any)[connect.connectState.chainId];

  //make sure token is avaiable in selected network
  for(const i in tokenOptions.value){
    if(tokenOptions.value[i] === tokenSelected.value){
      return;
    }
  }
  tokenSelected.value = tokenOptions.value[0];      
}

//on select the network
const onNetworkSelected = async () => {
  tokenOptions.value = (constant.tokenList as any)[networkSelected.value];
  for(const i in tokenOptions.value){
    if(tokenOptions.value[i] === tokenSelected.value){
      return;
    }
  }

  tokenSelected.value = tokenOptions.value[0];
}

//on cancel switch network clicked
const cancelSwitchNetwork = async () => {
  showSwitchNetwork.value = false;
}

//on confirm swithc network clicked
const confirmSwitchNetwork = async () => {
  try{

    showSwitchNetwork.value = false;

    if(Number(networkSelected.value) <= 0){
      element.elMessage('warning', 'Invalid network selected!');
      return;
    }

    const res = await network.switchNetwork(Number(networkSelected.value));
    if(!res){
      element.elMessage('warning', 'Switch network chain failed!');
      return;      
    }

    if(!(await connect.connected())){
      await connectNetwork();
    }

    connect.connectState.storage = storageSelected.value;

    if (storageSelected.value === 'bundlr'){
      connect.connectState.currency = tokenSelected.value;
    }

    if (storageSelected.value === 'filcoin'){
      if (apiTokenSelected.value === ''){
        apiTokenSelected.value = constant.web3StorageAppKey;
      }

      connect.connectState.web3Storage = apiTokenSelected.value;
    }

    element.elMessage('success', 'Config network success!');
  }catch(e){
    element.alertMessage(e);
  }
}

//on click to clear transtractions
const onClearTransactions = async () => {
  connect.connectState.transactions.value = new Array();
  connect.connectState.transactionCount.value = 0; 
}
//on click to copy address
const onClickToCopy = async (content:string) => {
  tools.clickToCopy(content);
  
  element.elMessage('success', 'Copy ' + content + ' to clipboard success.');     
};

//on click notify
const onClickNotify = async () => {
  transactionCount.value = 0;
}

//on connect clicked
const onConnect = async () => {
  if(connectStatus.value === "Cancel Connect"){      
    return await disConnectNetwork();
  } else {
    return await connectNetwork();
  }
};

//on switch network clicked
const onNetworkConfig = async () => {
  showSwitchNetwork.value = true;
}

//on menus selected
const handleSelect = (key: string, keyPath: string[]) => {
  activeIndex.value = key;

  tools.setUrlParamter('activeIndex', activeIndex.value);
  tools.setUrlParamter('daoId', 0);
};    

//login to wallet and switch to the target chain.
const login = async () => {
  await connectNetwork();
  await confirmSwitchNetwork();
  connect.connectState.searchCallback();
};

//try get activeIndex from the url paramter
try{
  activeIndex.value = String(tools.getUrlParamter('activeIndex'));
  if(activeIndex.value != '1' && 
    activeIndex.value != '2' && 
    activeIndex.value != '3' &&
    activeIndex.value != '4' &&
    activeIndex.value != '5' &&
    activeIndex.value != '6'){
    activeIndex.value = '1';
  }
}catch(e){
  activeIndex.value = '1';
}

//set activeIndex to connectState and location.href
connect.connectState.activeIndex.value = activeIndex.value;
tools.setUrlParamter('activeIndex', activeIndex.value);

//try connect to metamask
login();
</script>
