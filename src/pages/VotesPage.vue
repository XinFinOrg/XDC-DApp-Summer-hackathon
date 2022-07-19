<template>
  <div class="file-arae">
    <el-container>
      <el-header style="background-color: #ffffff;">
        <el-tabs v-model="activeName" class="file-tabs" @tab-click="handleClick">
          <el-tab-pane label="All" name="all"></el-tab-pane>
          <el-tab-pane label="Mine" name="mine"></el-tab-pane>
        </el-tabs>     
        <el-button type="primary" size="small" style="float: right;margin-right: 50px;" @click="onAddGreenVote">NEW+
        </el-button>    
        <el-drawer v-model="showAddNewVoteVisiable" direction="rtl" destroy-on-close @opened="onAddNewVoteOpen">
          <template #header>
            <h4>Create A New Green Vote.</h4>   
          </template>
          <template #default>  
            <table style="margin-left: 10px;">
              <tr v-if="voteId > 0">
                <td style="width:120px">Id
                  <el-popover
                    placement="top-start"
                    title="Vote Id"
                    :width="200"
                    trigger="hover"
                    content="The id of the green vote."
                  >
                    <template #reference>
                     <el-icon><QuestionFilled /></el-icon>
                    </template>
                </el-popover>
                </td>
                <td style="width:300px">
                  <el-input v-model="voteId" disabled>
                    <template #append>
                      <el-icon @click="onClickToCopy(voteId)"><document-copy /></el-icon>
                    </template>
                  </el-input>
                </td>
              </tr>
              <tr>
                <td style="width:120px">Dao Id
                  <el-popover
                    placement="top-start"
                    title="Dao Id"
                    :width="200"
                    trigger="hover"
                    content="The dao id of the green dao. The vote must be published through the dao."
                  >
                    <template #reference>
                     <el-icon><QuestionFilled /></el-icon>
                    </template>
                </el-popover>
                </td>
                <td style="width:300px">
                  <el-input v-model="daoId" @change="updateDaoName(daoId)" :disabled="voteId > 0">
                    <template #append>
                      <el-icon @click="onClickToCopy(daoId)"><document-copy /></el-icon>
                    </template>
                  </el-input>
                </td>
              </tr>    
              <tr>
                <td style="width:120px">Dao Name
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
                <td style="width:120px">Title
                  <el-popover
                    placement="top-start"
                    title="Vote Title"
                    :width="200"
                    trigger="hover"
                    content="The title of the green vote."
                  >
                    <template #reference>
                     <el-icon><QuestionFilled /></el-icon>
                    </template>
                </el-popover>
                </td>
                <td style="width:300px">
                  <el-input v-model="voteTitle">
                    <template #append>
                      <el-icon @click="onClickToCopy(voteTitle)"><document-copy /></el-icon>
                    </template>
                  </el-input>
                </td>                   
              </tr>           
              <tr>
                <td style="width:120px">Description
                  <el-popover
                    placement="top-start"
                    title="Vote Description"
                    :width="200"
                    trigger="hover"
                    content="The description of the green vote."
                  >
                    <template #reference>
                     <el-icon><QuestionFilled /></el-icon>
                    </template>
                </el-popover>
                </td>
                <td style="width:300px">
                  <el-input v-model="voteDescription" type="textarea" rows = "5">
                  </el-input>
                </td>                   
              </tr>  
              <tr>
                <td style="width:120px">End Time
                  <el-popover
                    placement="top-start"
                    title="Vote End Time"
                    :width="200"
                    trigger="hover"
                    content="The end time of the green vote."
                  >
                    <template #reference>
                     <el-icon><QuestionFilled /></el-icon>
                    </template>
                </el-popover>
                </td>
                <td style="width:300px">
                  <el-date-picker
                    v-model="voteEndTime"
                    style="width: 100%;"
                    type="datetime"
                    placeholder="Pick a Date"
                    :format="timeFormat"
                  >
                  </el-date-picker>
                </td>                   
              </tr>  
              <tr>
                <td style="width:120px">Pay Token
                  <el-popover
                    placement="top-start"
                    title="Vote Payment Token"
                    :width="200"
                    trigger="hover"
                    content="The token contract for the green vote. You can choose the blockchain native token or the erc20 tokens based on the balance of the dao treassure."
                  >
                    <template #reference>
                     <el-icon><QuestionFilled /></el-icon>
                    </template>
                </el-popover>
                </td>
                <td style="width:300px">
                  <el-input v-model="voteToken" :disabled="voteId > 0">
                    <template #append>
                      <el-icon @click="onClickToCopy(voteToken)"><document-copy /></el-icon>
                    </template>
                  </el-input>
                </td>                   
              </tr>  
              <tr>
                <td style="width:120px">Pay Value
                  <el-popover
                    placement="top-start"
                    title="Vote Pay Value"
                    :width="200"
                    trigger="hover"
                    content="The amount of the token value that the vote request for."
                  >
                    <template #reference>
                     <el-icon><QuestionFilled /></el-icon>
                    </template>
                </el-popover>
                </td>
                <td style="width:300px">
                  <el-input v-model="voteValue" :disabled="voteId > 0">
                     <template #append>
                      <el-icon @click="onClickToCopy(voteValue)"><document-copy /></el-icon>
                    </template>
                  </el-input>
                </td>                   
              </tr>                                            
              <tr>
                <td style="width:120px">Pay To
                  <el-popover
                    placement="top-start"
                    title="Vote Pay To"
                    :width="200"
                    trigger="hover"
                    content="The dest address that the vote payment send to."
                  >
                    <template #reference>
                     <el-icon><QuestionFilled /></el-icon>
                    </template>
                </el-popover>
                </td>
                <td style="width:300px">
                  <el-input v-model="voteTo" :disabled="voteId > 0">
                    <template #append>
                      <el-icon @click="onClickToCopy(voteTo)"><document-copy /></el-icon>
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
              <el-button @click="cancelVoteUpdate">cancel</el-button>
              <el-button type="primary" @click="confirmVoteUpdate">confirm</el-button>
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
          <template v-for="info in greenVoteList" :key="info.voteId">
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
                    <el-popover placement="bottom-start" :width="230" title="Vote Info">
                      <template #reference>
                        <span>
                          <el-link type="success" target="_blank" :href="tokenExplorerUrl(greenvote.getAddress(),info.voteId)">{{info.voteName}}
                          </el-link>
                        </span>
                      </template>
                      <h4>Title: {{info.voteName}}</h4>
                      <h4>Owner: 
                        <el-link type="success" target="_blank" :href="addressExplorerUrl(info.voteOwner)">{{info.voteOwner}}</el-link>
                      </h4>
                      <h4>Receiver: 
                        <el-link type="success" target="_blank" :href="addressExplorerUrl(info.voteTo)">{{info.voteTo}}</el-link>
                      </h4>
                      <h4>Description: {{info.voteDesc}}</h4>
                    </el-popover>
                    <span>
                      <el-button v-if="activeName === 'mine'" type="danger" style="float: right;" size="small" @click="onDeleteGreenVote(info.voteId)"><el-icon><Delete /></el-icon></el-button>
                      <el-button v-if="activeName === 'mine'" type="primary" style="float: right;" size="small" @click="onEditGreenVote(info)"><el-icon><Edit /></el-icon></el-button>
                    </span>  
                  </div>
                </template>
                <el-row>
                  <span>{{info.voteDesc}}</span>
                </el-row>
                <el-row style="float: right;">
                  <el-progress
                    style="width: 220px;float: right;"
                    :text-inside="true"
                    :stroke-width="20"
                    :percentage="(100*info.voteAggree/info.daoMembers)"
                    status="success"
                  >
                    <span>Aggree: {{info.voteAggree + '/' + info.daoMembers}}</span>
                  </el-progress>
                </el-row>
                <el-row style="float: right;">
                  <el-progress
                    style="width: 220px;float: right;"
                    :text-inside="true"
                    :stroke-width="20"
                    :percentage="(100*info.voteAgainst/info.daoMembers)"
                    status="exception"
                  >
                    <span>Against: {{info.voteAgainst + '/' + info.daoMembers}}</span>
                  </el-progress>
                </el-row>
                <el-row style="float: right;">
                  <span style="float: right;">Request Token: {{info.voteValue + ' ' + info.tokenSymbol}}</span>
                </el-row>
                <el-row v-if="info.voteEnded === false && info.votePayed === false" style="float: right;">
                  <span style="float: right;">Endtime: {{(new Date(info.endTime*1000)).toLocaleString()}}</span>
                </el-row>
                <el-row v-if="info.voteEnded === true || info.votePayed === true" style="float: right;">
                  <span style="float: right;">Vote Result: {{info.voteSuccess ? 'pass' : 'fail'}}</span>
                </el-row>
                <el-row style="float: right;">
                  <el-link v-if="info.voteEnded === false && info.votePayed === false" type="primary" style="float: right;" @click="onVote(info.voteId, 'aggree')">Aggree</el-link>
                  <el-link v-if="info.voteEnded === false && info.votePayed === false" type="primary" style="float: right;" @click="onVote(info.voteId, 'against')">Against</el-link>
                  <el-link v-if="info.voteEnded === false && info.votePayed === false" type="primary" style="float: right;" @click="onVote(info.voteId, 'revoke')">Revoke</el-link>
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
  name: 'VotesPage',
  props: {
  }
}
</script>

<script setup lang="ts">
  
import { ref } from "vue"
import { connected, connectState } from "../libs/connect"
import * as constant from "../constant"
import * as element from "../libs/element"
import * as tools from "../libs/tools"

import { ERC20 } from "../libs/erc20"
import { GreenDao } from "../libs/greendao"
import { GreenVote } from "../libs/greenvote"

const greendao = new GreenDao();
const greenvote = new GreenVote();

const activeName = connectState.activeName;
const loadStatus = ref(false);
const loadDrawerStatus = ref(false);

const showAddNewVoteVisiable = ref(false);

const daoId = ref(0);
const daoName = ref('');
const voteId = ref(0);
const voteTitle = ref('');
const voteDescription = ref('');
const voteToken = ref('');
const voteValue = ref(0);
const voteTo = ref('');
const voteEndTime = ref('');

const greenVoteList = ref(new Array());
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

//click to open the drawer to create a new vote
const onAddNewVoteOpen = async () => {
  await updateDaoName(daoId.value);
}

//click to open the drawer to create a new vote
const onAddGreenVote = async () => {
  daoId.value = getDaoId();
  voteId.value = 0;
  voteTitle.value = '';
  voteDescription.value = '';
  voteToken.value = zeroAddress;
  voteValue.value = 0;
  voteTo.value = zeroAddress;

  const now = new Date();
  now.setTime(now.getTime() + 30*24*3600*1000);

  voteEndTime.value = now.toLocaleString();  

  showAddNewVoteVisiable.value = true;
}

//click to open the drawer to edit the vote
const onEditGreenVote = async (voteInfo:any) => {
  daoId.value = voteInfo.daoId;
  voteId.value = voteInfo.voteId;
  voteTitle.value = voteInfo.voteName;
  voteDescription.value = voteInfo.voteDesc;
  voteToken.value = voteInfo.voteToken;
  voteValue.value = voteInfo.voteValue;
  voteTo.value = voteInfo.voteTo;

  voteEndTime.value = (new Date(voteInfo.endTime*1000)).toLocaleString();

  showAddNewVoteVisiable.value = true; 
}

//click to cancle vote update or create
const cancelVoteUpdate = async () => {
  showAddNewVoteVisiable.value = false;
}

//click to confirm to update or create the vote
const confirmVoteUpdate = async () => {
  try{
    loadDrawerStatus.value = true;

    const endTime = new Date(voteEndTime.value).getTime()/1000;

    if(voteId.value > 0){
      const tx = await greenvote.updateVote(voteId.value, voteTitle.value, voteDescription.value, endTime);
      connectState.transactions.value.unshift(tx);
      connectState.transactionCount.value++;
      const msg = `<div><span>Update vote success! Transaction: </span><a href="${transactionExplorerUrl(tx)}" target="_blank">${tx}</a></div>`;
      element.elMessage('success', msg, true);   
    }else{
      const tx = await greenvote.mint(voteTitle.value, voteDescription.value, daoId.value, voteValue.value, voteToken.value, voteTo.value, endTime);
      connectState.transactions.value.unshift(tx);
      connectState.transactionCount.value++;
      const msg = `<div><span>Create vote success! Transaction: </span><a href="${transactionExplorerUrl(tx)}" target="_blank">${tx}</a></div>`;
      element.elMessage('success', msg, true);    
    }

    showAddNewVoteVisiable.value = false;

    handleClick();
  }catch(e){
    element.alertMessage(e);
  }finally{
    loadDrawerStatus.value = false;
  }
}

//click to delete a green vote
const onDeleteGreenVote = async (voteId:number) => {
  try{
    const tx = await greenvote.burn(voteId);
    connectState.transactions.value.unshift(tx);
    connectState.transactionCount.value++;
    const msg = `<div><span>Delete vote success! Transaction: </span><a href="${transactionExplorerUrl(tx)}" target="_blank">${tx}</a></div>`;
    element.elMessage('success', msg, true);       

    handleClick();
  }catch(e){
    element.alertMessage(e);
  }
}

//click to cast a vote
const onVote = async (voteId:number, voteType:string) => {
  let status = 0;

  if(voteType === 'aggree'){
    status = 1;
  }else if(voteType === 'against'){
    status = 2;
  }else{
    status = 0;
  }

  try{
    const tx = await greenvote.vote(voteId, status);
    connectState.transactions.value.unshift(tx);
    connectState.transactionCount.value++;
    const msg = `<div><span>Cast the vote success! Transaction: </span><a href="${transactionExplorerUrl(tx)}" target="_blank">${tx}</a></div>`;
    element.elMessage('success', msg, true);       

    handleClick();
  }catch(e){
    element.alertMessage(e);
  }  
}

//get green vote infos by page size and page count
const getGreenVoteCount = async (onlyOwner:boolean) => {
  daoId.value = getDaoId();

  const indexs = await greenvote.getVoteIndexsByPageCount(pageSize.value, pageCount.value, daoId.value, onlyOwner);

  if(indexs.length < pageSize.value){
    hasMore.value = false;
  }else{
    hasMore.value = true;
  }

  const infoList = new Array();
  for(const i in indexs){
    const res = await greenvote.getVoteInfoById(indexs[i]);

    res.voteEnded = res.endTime < (new Date().getTime()/1000);
    res.voteOwner = await greenvote.ownerOf(indexs[i]);
    res.tokenSymbol = await getTokenCurencyName(res.voteToken);
    res.isOwner = res.voteOwner.toLowerCase() === connectState.userAddr.value.toLowerCase();

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

  greenVoteList.value = infoList;  
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

//handle page refresh
const handleClick = async () => {
  //wait for the active name change
  await tools.sleep(100);
    
  connectState.activeName.value = activeName.value;
  tools.setUrlParamter('activeName', activeName.value);
  try{
    loadStatus.value = true;
    if (!(await connected())){
      greenVoteList.value = new Array();
      return;
    }

    if(pageCount.value < 0){
      pageCount.value = 0;
    }

    const onlyOwner = activeName.value === 'mine';

    await getGreenVoteCount(onlyOwner);

  }catch(e){
    greenVoteList.value = new Array();
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
  if(activeName.value != 'all' && 
    activeName.value != 'mine'){
    activeName.value = 'all';
  }
}catch(e){
  activeName.value = 'all';
}
//update page size
handleClick();
</script>