<template>
  <div class="file-arae">
    <el-container>
      <el-header style="background-color: #ffffff;">
        <el-tabs v-model="activeName" class="file-tabs" @tab-click="handleClick">
          <el-tab-pane label="Upcoming" name="upcoming"></el-tab-pane>
          <el-tab-pane label="Ongoing" name="ongoing"></el-tab-pane>
          <el-tab-pane label="Finished" name="finished"></el-tab-pane>
        </el-tabs>     
        <el-button type="primary" size="small" style="float: right;margin-right: 50px;" @click="showAddNewAuctionVisiable = true;">NEW+
        </el-button>    
        <el-drawer v-model="showAddNewAuctionVisiable" direction="rtl" destroy-on-close @opened="onAddNewAuctionOpen">
          <template #header>
            <h4>Create A New Green Auction.</h4>   
          </template>
          <template #default>  
            <table style="margin-left: 10px;">
              <tr v-if="auctionId > 0">
                <td style="width:135px">Id
                  <el-popover
                    placement="top-start"
                    title="Auction Id"
                    :width="200"
                    trigger="hover"
                    content="The id of the green auction."
                  >
                    <template #reference>
                     <el-icon><QuestionFilled /></el-icon>
                    </template>
                </el-popover>
                </td>
                <td style="width:300px">
                  <el-input v-model="auctionId" disabled>
                    <template #append>
                      <el-icon @click="onClickToCopy(auctionId)"><document-copy /></el-icon>
                    </template>
                  </el-input>
                </td>
              </tr>
              <tr>
                <td style="width:135px">Dao Id
                  <el-popover
                    placement="top-start"
                    title="Dao Id"
                    :width="200"
                    trigger="hover"
                    content="The dao id of the green dao. The auction must be published through the dao."
                  >
                    <template #reference>
                     <el-icon><QuestionFilled /></el-icon>
                    </template>
                </el-popover>
                </td>
                <td style="width:300px">
                  <el-input v-model="daoId" @change="updateDaoName(daoId)" :disabled="auctionId > 0">
                    <template #append>
                      <el-icon @click="onClickToCopy(daoId)"><document-copy /></el-icon>
                    </template>
                  </el-input>
                </td>
              </tr>    
              <tr>
                <td style="width:135px">Dao Name
                  <el-popover
                    placement="top-start"
                    title="Dao Name"
                    :width="200"
                    trigger="hover"
                    content="The name of the green dao."
                  >
                    <template #reference>
                     <el-icon><QuestionFilled /></el-icon>
                    </template>
                </el-popover>
                </td>
                <td style="width:300px">
                  <el-input v-model="daoName" disabled>
                    <template #append>
                      <el-icon @click="onClickToCopy(daoName)"><document-copy /></el-icon>
                    </template>
                  </el-input>
                </td>                   
              </tr>
              <tr>
                <td style="width:135px">Start Time
                  <el-popover
                    placement="top-start"
                    title="Auction Start Time"
                    :width="200"
                    trigger="hover"
                    content="The start time of the green auction."
                  >
                    <template #reference>
                     <el-icon><QuestionFilled /></el-icon>
                    </template>
                </el-popover>
                </td>
                <td style="width:300px">
                  <el-date-picker
                    v-model="auctionStartTime"
                    style="width: 100%;"
                    type="datetime"
                    placeholder="Pick a Date"
                    :format="timeFormat"
                  >
                  </el-date-picker>
                </td>                   
              </tr>  
              <tr>
                <td style="width:135px">End Time
                  <el-popover
                    placement="top-start"
                    title="Auction End Time"
                    :width="200"
                    trigger="hover"
                    content="The end time of the green auction."
                  >
                    <template #reference>
                     <el-icon><QuestionFilled /></el-icon>
                    </template>
                </el-popover>
                </td>
                <td style="width:300px">
                  <el-date-picker
                    v-model="auctionEndTime"
                    style="width: 100%;"
                    type="datetime"
                    placeholder="Pick a Date"
                    :format="timeFormat"
                  >
                  </el-date-picker>
                </td>                   
              </tr>  
              <tr>
                <td style="width:135px">Start Price
                  <el-popover
                    placement="top-start"
                    title="Auction Start Price"
                    :width="200"
                    trigger="hover"
                    content="The start price of the auction."
                  >
                    <template #reference>
                     <el-icon><QuestionFilled /></el-icon>
                    </template>
                </el-popover>
                </td>
                <td style="width:300px">
                  <el-input v-model="auctionStartPrice">
                     <template #append>
                      <el-icon @click="onClickToCopy(auctionStartPrice)"><document-copy /></el-icon>
                    </template>
                  </el-input>
                </td>                   
              </tr>     
              <tr>
                <td style="width:135px">Reverse Price
                  <el-popover
                    placement="top-start"
                    title="Auction Reverse Price"
                    :width="200"
                    trigger="hover"
                    content="The revere price of the auction. The auction will be failed if the last bid price less than the reverse price."
                  >
                    <template #reference>
                     <el-icon><QuestionFilled /></el-icon>
                    </template>
                </el-popover>
                </td>
                <td style="width:300px">
                  <el-input v-model="auctionReversePrice">
                     <template #append>
                      <el-icon @click="onClickToCopy(auctionReversePrice)"><document-copy /></el-icon>
                    </template>
                  </el-input>
                </td>                   
              </tr>    
              <tr>
                <td style="width:135px">Delta Price
                  <el-popover
                    placement="top-start"
                    title="Auction Delta Price"
                    :width="200"
                    trigger="hover"
                    content="For english auction that means the minimum bid increase. For dutch auction that means the price decrease rate per day."
                  >
                    <template #reference>
                     <el-icon><QuestionFilled /></el-icon>
                    </template>
                </el-popover>
                </td>
                <td style="width:300px">
                  <el-input v-model="auctionDeltaPrice">
                     <template #append>
                      <el-icon @click="onClickToCopy(auctionDeltaPrice)"><document-copy /></el-icon>
                    </template>
                  </el-input>
                </td>                   
              </tr>            
              <tr>
                <td style="width:135px">Type
                  <el-popover
                    placement="top-start"
                    title="Auction Type"
                    :width="200"
                    trigger="hover"
                    content="You can choose English Auction or Dutch Auction."
                  >
                    <template #reference>
                     <el-icon><QuestionFilled /></el-icon>
                    </template>
                </el-popover>
                </td>
                <td style="width:300px;margin-left:0px;">
                  <el-select 
                    v-model="auctionType"
                    style="width:100%;margin-left:0px;"
                    placeholder="Select Auction Type"
                    :teleported="false"
                    filterable
                  >
                    <el-option key="english" label="English Auction" :value="0"/>
                    <el-option key="dutch" label="Dutch Auction" :value="1"/>
                  </el-select> 
                </td>                   
              </tr>    
              <tr>
                <td style="width:135px">Nft Id
                  <el-popover
                    placement="top-start"
                    title="Auction Nft Id"
                    :width="200"
                    trigger="hover"
                    content="The nft id of the auction to sell."
                  >
                    <template #reference>
                     <el-icon><QuestionFilled /></el-icon>
                    </template>
                </el-popover>
                </td>
                <td style="width:300px">
                  <el-input v-model="auctionNftId" :disabled="auctionId > 0">
                    <template #append>
                      <el-icon @click="onClickToCopy(auctionNftId)"><document-copy /></el-icon>
                    </template>
                  </el-input>
                </td>                   
              </tr>  
              <tr>
                <td style="width:135px">Nft Contract
                  <el-popover
                    placement="top-start"
                    title="Auction Nft Contract"
                    :width="200"
                    trigger="hover"
                    content="The nft contract of the auction to sell."
                  >
                    <template #reference>
                     <el-icon><QuestionFilled /></el-icon>
                    </template>
                </el-popover>
                </td>
                <td style="width:300px">
                  <el-input v-model="auctionNftContract" :disabled="auctionId > 0">
                    <template #append>
                      <el-icon @click="onClickToCopy(auctionNftContract)"><document-copy /></el-icon>
                    </template>
                  </el-input>
                </td>                   
              </tr>                                             
              <tr>
                <td style="width:135px">Pay Token
                  <el-popover
                    placement="top-start"
                    title="Auction Payment Token"
                    :width="200"
                    trigger="hover"
                    content="The token contract address for the green auction. You can choose the blockchain native token or the erc20 tokens to receive the payment."
                  >
                    <template #reference>
                     <el-icon><QuestionFilled /></el-icon>
                    </template>
                </el-popover>
                </td>
                <td style="width:300px">
                  <el-input v-model="auctionPayToken" :disabled="auctionId > 0">
                    <template #append>
                      <el-icon @click="onClickToCopy(auctionPayToken)"><document-copy /></el-icon>
                    </template>
                  </el-input>
                </td>                   
              </tr>                                        
            </table>
          </template>
          <template #footer>
            <div 
              style="flex: auto"
              v-loading="loadDrawerStatus" 
              element-loading-text="Submitting..."
              :element-loading-spinner="svg"
              element-loading-svg-view-box="-10, -10, 50, 50"
              element-loading-background="#ffffff"
            >
              <el-button @click="cancelAuctionUpdate">cancel</el-button>
              <el-button type="primary" v-if="auctionNftApproved === false" @click="approveNftToken">approve</el-button>
              <el-button type="primary" v-if="auctionNftApproved === true" @click="confirmAuctionUpdate">confirm</el-button>
            </div>
          </template>
        </el-drawer>        
      </el-header>
      <el-main
        style="height: 450px;margin-top: 20px;" 
        v-loading="loadStatus"
        element-loading-text="Loading..."
        :element-loading-spinner="svg"
        element-loading-svg-view-box="-10, -10, 50, 50"
        element-loading-background="#ffffff"
      >
        <el-row :gutter="20">
          <template v-for="info in greenAuctionList" :key="info.aucId">
            <el-col :span="8">
              <el-card class="box-card">
                <template #header>
                  <div class="card-header">
                    <el-popover placement="bottom-start" :width="230" title="Dao Info">
                      <template #reference>
                        <el-link type="success" target="_blank" :href="info.daoWebsite">
                          <el-avatar :src="info.daoAvatar" size="small"></el-avatar>
                        </el-link>
                      </template>
                      <h4>Name: {{info.daoName}}</h4>
                      <h4>Id: 
                        <el-link type="success" target="_blank" :href="tokenExplorerUrl(greendao.getAddress(),info.daoId)">{{info.daoId}}</el-link>
                      </h4>
                      <h4>Owner:
                        <el-link type="success" target="_blank" :href="addressExplorerUrl(info.daoOwner)">{{info.daoOwner}}</el-link>
                      </h4>
                      <h4>Members: {{info.daoMembers}}</h4>
                      <h4>Public: {{info.daoPublic}}</h4>
                      <h4>Description: {{info.daoDesc}}</h4>
                    </el-popover>
                    <el-popover placement="bottom-start" :width="230" title="Auction Info">
                      <template #reference>
                        <span>
                          <el-link type="success" target="_blank" :href="tokenExplorerUrl(info.nftContract,info.nftId)">
                            {{info.nftSymbol + ' : ' + info.nftId}}
                          </el-link>
                        </span>
                      </template>
                      <h4>Auction Id:
                        <el-link type="success" target="_blank" :href="tokenExplorerUrl(greenauction.getAddress(),info.aucId)">{{info.aucId}}</el-link>
                      </h4>
                      <h4>Auction Type: {{info.acuType === 0 ? 'English Auction' : 'Dutch Auction'}}</h4>
                      <h4>Nft Name: {{info.nftName}}</h4>
                      <h4>Nft Symbol: {{info.nftSymbol}}</h4>
                      <h4>Nft Id:
                        <el-link type="success" target="_blank" :href="tokenExplorerUrl(info.nftContract,info.nftId)">{{info.nftId}}</el-link>
                      </h4>
                      <h4>Nft Owner:</h4>
                      <el-link type="success" target="_blank" :href="addressExplorerUrl(info.nftOwner)">{{info.nftOwner}}</el-link>
                      <h4>Bid Address:</h4>
                      <el-link type="success" target="_blank" :href="addressExplorerUrl(info.bidAddress)">{{info.nftOwner}}</el-link>
                      <h4 v-if="info.aucStatus <= 1">Start Price: {{info.startPrice + ' ' + info.tokenSymbol}}</h4>
                      <h4 v-if="info.aucStatus >= 2">Reverse Price: {{info.reversePrice + ' ' + info.tokenSymbol}}</h4>
                      <h4>Bid Price: {{info.bidPrice + ' ' + info.tokenSymbol}}</h4>
                    </el-popover>
                    <span>
                      <el-button v-if="info.isOwner === true && info.aucStatus <= 1" type="danger" style="float: right;" size="small" @click="onCancelGreenAuction(info.aucId)"><el-icon><Delete /></el-icon></el-button>
                    </span>  
                  </div>
                </template>
                <el-row>
                  <iframe frameborder="0" sandbox="allow-scripts allow-same-origin allow-popups" :src="info.nftUrl" style="width: 250px;height: 200px;" />
                </el-row>
                <el-row v-if="info.aucStatus === 0" style="float: right;">
                  <span>Starttime: {{(new Date(info.startTime*1000)).toLocaleString()}}</span>
                </el-row>
                <el-row v-if="info.aucStatus === 1" style="float: right;">
                  <span>Endtime: {{(new Date(info.endTime*1000)).toLocaleString()}}</span>
                </el-row>
                <el-row v-if="info.aucStatus === 2" style="float: right;">
                  <span>Auction Result: fail</span>
                </el-row>
                <el-row v-if="info.aucStatus === 3" style="float: right;">
                  <span>Auction Result: success</span>
                </el-row>
                <el-row v-if="info.aucStatus === 4" style="float: right;">
                  <span>Auction Result: success</span>
                </el-row>
                <el-row v-if="info.aucStatus === 5" style="float: right;">
                  <span>Auction Result: fail</span>
                </el-row>
                <el-row style="float: right;">
                  <el-link type="success" style="float: right;" href="javascript:void(0);">
                    Current Price: {{getCurrentPrice(info).toPrecision(4) + ' ' + info.tokenSymbol}}
                  </el-link>
                  <el-link v-if="info.aucStatus === 0 || info.aucStatus === 1" type="primary" style="float: right;" @click="onBidAuction(info)">Bid</el-link>
                  <el-link v-if="info.aucStatus === 2 || info.aucStatus === 3" type="primary" style="float: right;" @click="onClaimAuction(info.aucId)">Claim</el-link>
                </el-row>
              </el-card>
            </el-col>
          </template>
        </el-row>
      </el-main>
      <el-footer>
        <div>
          <el-button type="primary" style="margin-top: 10px;" @click="onHandlePrev">Prev
          </el-button>
          <el-button type="primary" style="margin-top: 10px;" @click="onHandleNext" :disabled="!hasMore">Next
          </el-button>          
      </div>
      </el-footer>
    </el-container>
  </div>
</template>

<script lang="ts">
export default {
  name: 'AuctionsPage',
  props: {
  }
}
</script>

<script setup lang="ts">
  
import { ref, h } from "vue"
import { connected, connectState } from "../libs/connect"
import * as constant from "../constant"
import * as element from "../libs/element"
import * as tools from "../libs/tools"

import { ERC20 } from "../libs/erc20"
import { ERC721 } from "../libs/erc721";
import { GreenDao } from "../libs/greendao"
import { GreenAuction } from "../libs/greenauction"

const greendao = new GreenDao();
const greenauction = new GreenAuction();

const activeName = connectState.activeName;
const loadStatus = ref(false);
const loadDrawerStatus = ref(false);

const showAddNewAuctionVisiable = ref(false);

const daoId = ref(0);
const daoName = ref('');
const auctionId = ref(0);
const auctionStartTime = ref('');
const auctionEndTime = ref('');
const auctionStartPrice = ref(0);
const auctionReversePrice = ref(0);
const auctionDeltaPrice = ref(0);
const auctionNftId = ref(0);
const auctionNftContract = ref('');
const auctionNftApproved = ref(false);
const auctionPayToken = ref('');
const auctionType = ref(0);

const greenAuctionList = ref(new Array());
const hasMore = ref(false);
const pageSize = ref(6);
const pageCount = ref(0);

const zeroAddress = '0x0000000000000000000000000000000000000000';
const timeFormat = "YYYY/MM/DD hh:mm:ss";

const svg = `
        <path class="path" d="
          M 30 15
          L 28 17
          M 25.61 25.61
          A 15 15, 0, 0, 1, 15 30
          A 15 15, 0, 1, 1, 27.99 7.5
          L 15 15
        " style="stroke-width: 4px; fill: rgba(0, 0, 0, 0)"/>
      `;

//address explore url
const tokenExplorerUrl = (address:string, tokenId:string = '') => {
  for(const i in constant.chainList){
    if(connectState.chainId === constant.chainList[i].chainId){
      const blockExplorerUrls = constant.chainList[i].blockExplorerUrls;
      if(tokenId != ''){
        return `${blockExplorerUrls}/token/${address}?a=${tokenId}#inventory`
      }
      return `${blockExplorerUrls}/token/${address}`
    }
  }
  return address;
}

//address explore url
const addressExplorerUrl = (address:string) => {
  for(const i in constant.chainList){
    if(connectState.chainId === constant.chainList[i].chainId){
      const blockExplorerUrls = constant.chainList[i].blockExplorerUrls;
      return `${blockExplorerUrls}/address/${address}`
    }
  }
  return address;
}      

//transaction explore url
const transactionExplorerUrl = (transaction:string) => {
  for(const i in constant.chainList){
    if(connectState.chainId === constant.chainList[i].chainId){
      const blockExplorerUrls = constant.chainList[i].blockExplorerUrls;
      return blockExplorerUrls + '/tx/' + transaction;
    }
  }
  return transaction;
}

//get block chain native currency
const getTokenCurencyName = async (token:string) => {
  if(token === zeroAddress){
    for(const i in constant.chainList){
      if(constant.chainList[i].chainId === connectState.chainId){
        return constant.chainList[i].nativeCurrency;
      }
    }
  }else{
    const erc20 = new ERC20(token);
    return await erc20.symbol();
  }
}

//on click to copy address
const onClickToCopy = async (content:string) => {
  tools.clickToCopy(content);
  
  element.elMessage('success', 'Copy ' + content + ' to clipboard success.');     
};

//get dao id
const getDaoId = () => {
  const daoId = tools.getUrlParamter('daoId');
  try{
    return Number(daoId);
  }catch(e){
    return 0;
  }
}

//update dao name by dao id
const updateDaoName = async (daoId:Number) => {
  const daoInfo = await greendao.getDaoInfoById(daoId);
  daoName.value = daoInfo.daoName;
}

//click to open the drawer to create a new auction
const onAddNewAuctionOpen = async () => {
  daoId.value = getDaoId();
  auctionId.value = 0;
  auctionStartPrice.value = 0;
  auctionReversePrice.value = 0;
  auctionDeltaPrice.value = 0;
  auctionType.value = 0;
  auctionNftId.value = 0;
  auctionNftContract.value = zeroAddress;
  auctionNftApproved.value = false;
  auctionPayToken.value = zeroAddress;

  const now = new Date();
  auctionStartTime.value = now.toLocaleString();

  now.setTime(now.getTime() + 30*24*3600*1000);
  auctionEndTime.value = now.toLocaleString();

  await updateDaoName(daoId.value);
}

//click to cancel auction create
const cancelAuctionUpdate = async () => {
  showAddNewAuctionVisiable.value = false;
}

//click to approve the nft token to the auction contract
const approveNftToken = async () => {
  if(auctionNftApproved.value === true){
    return true;
  }

  try{
    loadDrawerStatus.value = true;

    const erc721 = new ERC721(auctionNftContract.value);

    const contractAddress = (constant.greenAuctionContractAddress as any)[connectState.chainId].toLowerCase();

    const approvedAddress = (await erc721.getApproved(auctionNftId.value)).toLowerCase();

    if(approvedAddress === contractAddress){
      auctionNftApproved.value = true;
    }else{
      const tx = await erc721.approve(contractAddress, auctionNftId.value);
      auctionNftApproved.value = true;
      connectState.transactions.value.unshift(tx);
      connectState.transactionCount.value++;
      const msg = `<div><span>Approve nft token success! Transaction: </span><a href="${transactionExplorerUrl(tx)}" target="_blank">${tx}</a></div>`;
      element.elMessage('success', msg, true);    
    }

  }catch(e){
    element.alertMessage(e);
  }finally{
    loadDrawerStatus.value = false;
  }
}

//click to confirm to create a new auction
const confirmAuctionUpdate = async () => {
  try{
    loadDrawerStatus.value = true;

    const startTime = new Date(auctionStartTime.value).getTime()/1000;
    const endTime = new Date(auctionEndTime.value).getTime()/1000;

    const tx = await greenauction.mint(startTime, endTime, auctionStartPrice.value, auctionReversePrice.value, auctionDeltaPrice.value, auctionType.value, daoId.value, auctionNftId.value, auctionNftContract.value, auctionPayToken.value);
    connectState.transactions.value.unshift(tx);
    connectState.transactionCount.value++;
    const msg = `<div><span>Create auction success! Transaction: </span><a href="${transactionExplorerUrl(tx)}" target="_blank">${tx}</a></div>`;
    element.elMessage('success', msg, true);    

    showAddNewAuctionVisiable.value = false;

    handleClick();
  }catch(e){
    element.alertMessage(e);
  }finally{
    loadDrawerStatus.value = false;
  }
}

//click to cancel a green auction
const onCancelGreenAuction = async (auctionId:number) => {
  try{
    const tx = await greenauction.cancelAuction(auctionId);
    connectState.transactions.value.unshift(tx);
    connectState.transactionCount.value++;
    const msg = `<div><span>Cancel auction success! Transaction: </span><a href="${transactionExplorerUrl(tx)}" target="_blank">${tx}</a></div>`;
    element.elMessage('success', msg, true);       

    handleClick();
  }catch(e){
    element.alertMessage(e);
  }
}

const getCurrentPrice = (auctionInfo:any) => {
  let currentPrice;

  if(auctionInfo.acuType === 0){
    if(auctionInfo.bidPrice === 0){
      currentPrice = auctionInfo.startPrice;
    }else{
      currentPrice = auctionInfo.bidPrice + auctionInfo.priceDelta;
    }
  }else{
    const timeDelta = (new Date()).getTime()/1000 - auctionInfo.startTime;
    currentPrice = auctionInfo.startPrice - (parseInt(timeDelta/86400))*auctionInfo.priceDelta;
  }

  return currentPrice;
}

//click to bid a price for the auction
const onBidAuction = async (auctionInfo:any) => {
  const currentPrice = getCurrentPrice(auctionInfo);

  const opts = {
    message: '',
    confirmButtonText: 'Send',
    cancelButtonText: 'Cancel',
    inputType: 'number',
    inputValue: String(currentPrice),
    inputValidator: (value:number) => {return value >= currentPrice},
    inputErrorMessage: 'bid price must large than current price',
  };

  const erc20 = new ERC20(auctionInfo.payContract);

  const tokenBalance = await erc20.balanceOf(connectState.userAddr.value);

  if(auctionInfo.payContract === zeroAddress){
    opts.message =  h('p', null, [
      h('p', null, 'Please enter the token amount for the auction:'),
      h('p', { style: 'color: teal' }, `dao id: ${auctionInfo.daoId}`),
      h('p', { style: 'color: teal' }, `dao name: ${auctionInfo.daoName}`),
      h('p', { style: 'color: teal' }, `auction id: ${auctionInfo.aucId}`),
      h('p', { style: 'color: teal' }, `token name: ${auctionInfo.tokenSymbol}`),
      h('p', { style: 'color: teal' }, `token balance: ${tokenBalance}`),
    ]);
  }else{
    opts.message =  h('p', null, [
      h('p', null, 'Please enter the token amount for the auction:'),
      h('p', { style: 'color: teal' }, `dao id: ${auctionInfo.daoId}`),
      h('p', { style: 'color: teal' }, `dao name: ${auctionInfo.daoName}`),
      h('p', { style: 'color: teal' }, `auction id: ${auctionInfo.aucId}`),
      h('p', { style: 'color: teal' }, `token name: ${auctionInfo.tokenSymbol}`),
      h('p', { style: 'color: teal' }, `token contract: ${auctionInfo.payContract}`),
      h('p', { style: 'color: teal' }, `token balance: ${tokenBalance}`),
    ]);
  }

  element.elMessageBox('Please enter the token amount for the auction:', 'Send Token', opts, async (value:number) => {
    if(value < currentPrice){
      element.alertMessage(`bid price must large than current price: ${currentPrice}!`);
    }

    try{
      if(auctionInfo.payContract != zeroAddress){
        const tx = await erc20.approve(greenauction.getAddress(), value);
        connectState.transactions.value.unshift(tx);
        connectState.transactionCount.value++;
        const msg = `<div><span>Approve token success! Transaction: </span><a href="${transactionExplorerUrl(tx)}" target="_blank">${tx}</a></div>`;
      }

      const tx = await greenauction.bidForNft(auctionInfo.aucId, value);
      connectState.transactions.value.unshift(tx);
      connectState.transactionCount.value++;
      const msg = `<div><span>Bid price for the auction success! Transaction: </span><a href="${transactionExplorerUrl(tx)}" target="_blank">${tx}</a></div>`;

      handleClick();
    }catch(e){
      element.alertMessage(e);
    }

  });    
}

//click to claim the auction treassure
const onClaimAuction = async (auctionId: number) => {
  try{
    const tx = await greenauction.claimAuction(auctionId);
    connectState.transactions.value.unshift(tx);
    connectState.transactionCount.value++;
    const msg = `<div><span>Claim auction success! Transaction: </span><a href="${transactionExplorerUrl(tx)}" target="_blank">${tx}</a></div>`;
    element.elMessage('success', msg, true);       

    handleClick();
  }catch(e){
    element.alertMessage(e);
  }
}

//on click for prev page
const onHandlePrev = async () => {
  if(pageCount.value > 0){
    pageCount.value--;
  }
  handleClick();
}

//on click for next page
const onHandleNext = async () => {
  if(hasMore.value){
    pageCount.value++;
  }
  handleClick();
}

//get green auctions infos by page size and page count
const getGreenAuctionCount = async (aucStatus:number, onlyOwner:boolean) => {
  daoId.value = getDaoId();

  const indexs = await greenauction.getAuctionIndexsByPage(pageSize.value, pageCount.value, daoId.value, aucStatus, onlyOwner);

  if(indexs.length < pageSize.value){
    hasMore.value = false;
  }else{
    hasMore.value = true;
  }

  const infoList = new Array();
  for(const i in indexs){
    const res = await greenauction.getAuctionInfoById(indexs[i]);
    const erc721 = new ERC721(res.nftContract);

    res.tokenSymbol = await getTokenCurencyName(res.payContract);
    try{
      res.nftUrl = await erc721.tokenURI(res.nftId);
    }catch(e){
      res.nftUrl = '';
    }
    
    res.nftName = await erc721.name();
    res.nftSymbol = await erc721.symbol();
    res.isOwner = res.nftOwner.toLowerCase() === connectState.userAddr.value.toLowerCase();

    //if dao not exists, skip
    try{
      const daoInfo = await greendao.getDaoInfoById(res.daoId);
      res.daoName = daoInfo.daoName;
      res.daoAvatar = daoInfo.daoAvatar;
      res.daoWebsite = daoInfo.daoWebsite;
      res.daoDesc = daoInfo.daoDesc;
      res.daoOwner = daoInfo.daoOwner;
      res.daoPublic = daoInfo.daoPublic;
      res.daoMembers = daoInfo.daoMembers;
    }catch(e){
      continue;
    }

    infoList.push(res);
  }

  greenAuctionList.value = infoList;  
}

//handle page refresh
const handleClick = async () => {
  //wait for the active name change
  await tools.sleep(100);
    
  connectState.activeName.value = activeName.value;
  tools.setUrlParamter('activeName', activeName.value);
  try{
    loadStatus.value = true;
    if (!(await connected())){
      greenAuctionList.value = new Array();
      return;
    }

    if(pageCount.value < 0){
      pageCount.value = 0;
    }

    const onlyOwner = false;

    let aucStatus = 0;
    if(activeName.value === 'upcoming'){
      aucStatus = 0;
    }else if(activeName.value === 'ongoing'){
      aucStatus = 1;
    }else{
      aucStatus = 6;
    }

    await getGreenAuctionCount(aucStatus, onlyOwner);

  }catch(e){
    greenAuctionList.value = new Array();
  }finally{
    loadStatus.value = false;
  }
}

//clean search content and bind search callback function
connectState.search = '';
connectState.searchCallback = handleClick;
//try get activeName from the url paramter
try{
  activeName.value = tools.getUrlParamter('activeName');
  if(activeName.value != 'upcoming' && 
    activeName.value != 'ongoing' &&
    activeName.value != 'finished'){
    activeName.value = 'ongoing';
  }
}catch(e){
  activeName.value = 'ongoing';
}
//update page size
handleClick();
</script>