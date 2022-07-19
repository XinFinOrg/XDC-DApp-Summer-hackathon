<template>
  <div class="file-arae">
    <el-container>
      <el-header style="background-color: #ffffff;">
        <el-tabs v-model="activeName" class="file-tabs" @tab-click="handleClick">
          <el-tab-pane label="All" name="all"></el-tab-pane>
          <el-tab-pane label="Mine" name="mine"></el-tab-pane>
        </el-tabs>     
        <el-button type="primary" size="small" style="float: right;margin-right: 50px;" @click="onAddGreenGrant">NEW+
        </el-button>    
        <el-drawer v-model="showAddNewGrantVisiable" direction="rtl" destroy-on-close @opened="onAddNewGrantOpen">
          <template #header>
            <h4>Create A New Green Grant.</h4>   
          </template>
          <template #default>  
            <table style="margin-left: 10px;">
              <tr v-if="grantId > 0">
                <td style="width:120px">Grant Id
                  <el-popover
                    placement="top-start"
                    title="Grant Id"
                    :width="200"
                    trigger="hover"
                    content="The id of the green grant."
                  >
                    <template #reference>
                     <el-icon><QuestionFilled /></el-icon>
                    </template>
                </el-popover>
                </td>
                <td style="width:300px">
                  <el-input v-model="grantId" disabled>
                    <template #append>
                      <el-icon @click="onClickToCopy(grantId)"><document-copy /></el-icon>
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
                    content="The dao id of the green dao. The grant must be published through the dao."
                  >
                    <template #reference>
                     <el-icon><QuestionFilled /></el-icon>
                    </template>
                </el-popover>
                </td>
                <td style="width:300px">
                  <el-input v-model="daoId" @change="updateDaoName(daoId)" :disabled="grantId > 0">
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
                    title="Grant Title"
                    :width="200"
                    trigger="hover"
                    content="The title of the green grant."
                  >
                    <template #reference>
                     <el-icon><QuestionFilled /></el-icon>
                    </template>
                </el-popover>
                </td>
                <td style="width:300px">
                  <el-input v-model="grantTitle">
                    <template #append>
                      <el-icon @click="onClickToCopy(grantTitle)"><document-copy /></el-icon>
                    </template>
                  </el-input>
                </td>                   
              </tr>           
              <tr>
                <td style="width:120px">Description
                  <el-popover
                    placement="top-start"
                    title="Grant Description"
                    :width="200"
                    trigger="hover"
                    content="The description of the green grant."
                  >
                    <template #reference>
                     <el-icon><QuestionFilled /></el-icon>
                    </template>
                </el-popover>
                </td>
                <td style="width:300px">
                  <el-input v-model="grantDescription" type="textarea" rows = "5">
                  </el-input>
                </td>                   
              </tr>  
              <tr>
                <td style="width:120px">End Time
                  <el-popover
                    placement="top-start"
                    title="Grant End Time"
                    :width="200"
                    trigger="hover"
                    content="The end time of the green grant."
                  >
                    <template #reference>
                     <el-icon><QuestionFilled /></el-icon>
                    </template>
                </el-popover>
                </td>
                <td style="width:300px">
                  <el-date-picker
                    v-model="grantEndTime"
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
                    title="Grant Pay Token"
                    :width="200"
                    trigger="hover"
                    content="The payment token contract to support for the green grant. You can choose the blockchain native token or the erc20 tokens to receive the payments."
                  >
                    <template #reference>
                     <el-icon><QuestionFilled /></el-icon>
                    </template>
                </el-popover>
                </td>
                <td style="width:300px">
                  <el-input v-model="grantToken" :disabled="grantId > 0">
                    <template #append>
                      <el-icon @click="onClickToCopy(grantToken)"><document-copy /></el-icon>
                    </template>
                  </el-input>
                </td>                   
              </tr>  
              <tr>
                <td style="width:120px">Github
                  <el-popover
                    placement="top-start"
                    title="Grant Github"
                    :width="200"
                    trigger="hover"
                    content="The github url link of the green grant."
                  >
                    <template #reference>
                     <el-icon><QuestionFilled /></el-icon>
                    </template>
                </el-popover>
                </td>
                <td style="width:300px">
                  <el-input v-model="grantGitUrl">
                     <template #append>
                      <el-icon @click="onClickToCopy(grantGitUrl)"><document-copy /></el-icon>
                    </template>
                  </el-input>
                </td>                   
              </tr>                                            
              <tr>
                <td style="width:120px">Website
                  <el-popover
                    placement="top-start"
                    title="Grant Website"
                    :width="200"
                    trigger="hover"
                    content="The website link of the green grant. You can input the url link directly or upload the website folder through the upload button."
                  >
                    <template #reference>
                     <el-icon><QuestionFilled /></el-icon>
                    </template>
                </el-popover>
                </td>
                <td style="width:300px">
                  <el-input v-model="grantWebsite">
                    <template #append>
                      <el-icon @click="onClickToCopy(grantWebsite)"><document-copy /></el-icon>
                    </template>
                  </el-input>
                </td>                   
              </tr>      
              <tr 
                v-loading="loadWebsiteStatus"
                element-loading-text="Uploading..."
                :element-loading-spinner="svg"
                element-loading-svg-view-box="-10, -10, 50, 50"
                element-loading-background="#ffffff"
              >
                <td style="width:120px"></td>
                <td style="width:300px">
                  <el-upload 
                    style="width: 100px;height: 0px;float: right;margin-right: 100px;"
                    :drag="false"
                    :multiple="true"
                    class="upload-website"
                    ref="uploadWebsite"
                    action=""
                    @change="onChangeSelectWebsiteFolder"
                    @click="onSelectWebsiteFolder"
                    :limit="0"
                    :show-file-list="false"
                    :auto-upload="false"
                  >
                    <template #trigger>
                      <el-button type="primary" style="float: right;margin-right: 10px;width: 100%;">Select Folder</el-button>
                    </template>
                  </el-upload>
                  <el-button type="success" style="float: right;" @click="onUploadWebsiteFolder">Upload</el-button>
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
              <el-button @click="cancelGrantUpdate">cancel</el-button>
              <el-button type="primary" @click="confirmGrantUpdate">confirm</el-button>
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
          <template v-for="info in greenGrantList" :key="info.grantId">
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
                    <el-popover placement="bottom-start" :width="230" title="Grant Info">
                      <template #reference>
                        <span>
                          <el-link type="success" target="_blank" :href="tokenExplorerUrl(greengrant.getAddress(),info.grantId)">{{info.grantName}}
                          </el-link>
                        </span>
                      </template>
                      <h4>Title: {{info.grantName}}</h4>
                      <h4>Owner: 
                        <el-link type="success" target="_blank" :href="addressExplorerUrl(info.grantOwner)">{{info.grantOwner}}</el-link>
                      </h4>
                      <h4>Description: {{info.grantDesc}}</h4>
                    </el-popover>
                    <span>
                      <el-button v-if="activeName === 'mine'" type="danger" style="float: right;" size="small" @click="onDeleteGreenGrant(info.grantId)"><el-icon><Delete /></el-icon></el-button>
                      <el-button v-if="activeName === 'mine'" type="primary" style="float: right;" size="small" @click="onEditGreenGrant(info)"><el-icon><Edit /></el-icon></el-button>
                    </span>  
                  </div>
                </template>
                <el-row>
                  <iframe frameborder="0" sandbox="allow-scripts allow-same-origin allow-popups" :src="info.grantWebsite" style="width: 250px;height: 200px;" />
                </el-row>
                <el-row style="float: right;">
                  <span>Endtime: {{(new Date(info.endTime*1000)).toLocaleString()}}</span>
                </el-row>
                <el-row style="float: right;">
                  <el-link type="primary" style="float: right;" href="javascript:void(0);">Received : {{info.grantTreassure}}</el-link>
                  <el-link v-if="info.grantEnded === false && info.grantPayed === false" type="primary" style="float: right;" @click="onSupportGrant(info)">Support</el-link>
                  <el-link v-if="info.isOwner === true && info.grantEnded === true && info.grantPayed === false" type="primary" style="float: right;" @click="onClaimGrant(info.grantId)">Claim</el-link>
                  <el-link type="primary" style="float: right;" :href="info.grantGitUrl" target="_blank">Github</el-link>
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
  name: 'GrantsPage',
  props: {
  }
}
</script>

<script setup lang="ts">
  
import { getCurrentInstance, ComponentInternalInstance, h, ref } from 'vue'

import { toRaw } from '@vue/reactivity'
import * as path from "path"
import type { UploadInstance, UploadFile, UploadFiles } from 'element-plus'

import { connected, connectState } from "../libs/connect"
import * as constant from "../constant"
import * as element from "../libs/element"
import * as tools from "../libs/tools"
import * as storage from '../libs/storage'

import { ERC20 } from "../libs/erc20"
import { GreenDao } from "../libs/greendao"
import { GreenGrant } from "../libs/greengrant"

const { proxy } = getCurrentInstance() as ComponentInternalInstance;

const uploadWebsite = ref<UploadInstance>();

const greendao = new GreenDao();
const greengrant = new GreenGrant();

const zeroAddress = '0x0000000000000000000000000000000000000000';
const timeFormat = "YYYY/MM/DD hh:mm:ss";

const activeName = connectState.activeName;

const loadStatus = ref(false);
const loadDrawerStatus = ref(false);
const loadWebsiteStatus = ref(false);

const daoId = ref(0);
const daoName = ref('');
const grantId = ref(0);
const grantTitle = ref('');
const grantDescription = ref('');
const grantGitUrl = ref('');
const grantWebsite = ref('');
const grantToken = ref('');
const grantEndTime = ref('');

const showAddNewGrantVisiable = ref(false);
const websiteFileList = ref(new Array());

const greenGrantList = ref(new Array());
const hasMore = ref(false);
const pageSize = ref(6);
const pageCount = ref(0);

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

//on website folder change
const onChangeSelectWebsiteFolder = async (uploadFile: UploadFile, uploadFiles: UploadFiles) => {
  websiteFileList.value = toRaw(uploadFiles);
  
  grantWebsite.value = (websiteFileList.value[0].raw as any).webkitRelativePath.split(path.sep)[0];
  if(grantWebsite.value === ''){
    grantWebsite.value = (websiteFileList.value[0].raw as any).name;
  }
}

//on click to select website folder
const onSelectWebsiteFolder = async () => {
  uploadWebsite.value!.clearFiles();
}

//on click to upload website folder
const onUploadWebsiteFolder = async () => {
    try{

      loadWebsiteStatus.value = true;

      if(toRaw(websiteFileList.value).length === 0){
        element.elMessage('warning', 'You have not select any folder to upload!');
        return;
      }

      let directory = (websiteFileList.value[0].raw as any).webkitRelativePath.split(path.sep)[0];
      if(directory === ''){
        directory = (websiteFileList.value[0].raw as any).name;
      }

      let findIndex = false;

      for(const i in websiteFileList.value){
        const file = websiteFileList.value[i];
        if((file.raw as any).name === 'index.html'){
          findIndex = true;
          break;
        }
      }

      if(findIndex === false){
        element.elMessage('warning', 'Not a valid website folder, index.html not found in the root path!');
        return;
      }

      grantWebsite.value = await storage.uploadFolder(directory, toRaw(websiteFileList.value));
    }catch(e){
      element.alertMessage(e);
    }finally{
      loadWebsiteStatus.value = false;
    }
}      

//click to open the drawer to create a new grant
const onAddNewGrantOpen = async () => {
  const currentClass = (proxy as any).$el.parentNode.querySelector(".upload-website");

  (currentClass.querySelector(".el-upload__input") as any).webkitdirectory = true;  

  await updateDaoName(daoId.value);
}

//click to edit the green grant
const onEditGreenGrant = async(grantInfo:any) => {
  daoId.value = grantInfo.daoId;
  grantId.value = grantInfo.grantId;
  grantTitle.value = grantInfo.grantName;
  grantDescription.value = grantInfo.grantDesc;
  grantGitUrl.value = grantInfo.grantGitUrl;
  grantWebsite.value = grantInfo.grantWebsite;
  grantToken.value = grantInfo.grantToken;
  grantEndTime.value = (new Date(grantInfo.endTime*1000)).toLocaleString();

  showAddNewGrantVisiable.value = true;
}

//click to add a new green grant
const onAddGreenGrant = async () => {
  daoId.value = getDaoId();
  grantId.value = 0;
  grantTitle.value = '';
  grantDescription.value = '';
  grantGitUrl.value = '';
  grantWebsite.value = '';
  grantToken.value = zeroAddress;

  const now = new Date();
  now.setTime(now.getTime() + 30*24*3600*1000);
  grantEndTime.value = now.toLocaleString();

  showAddNewGrantVisiable.value = true;
}

//click to cancel grant update
const cancelGrantUpdate = async () => {
  showAddNewGrantVisiable.value = false;
}

//click to confirm to update or create the grant
const confirmGrantUpdate = async () => {

  try{
    loadDrawerStatus.value = true;

    const endTime = new Date(grantEndTime.value).getTime()/1000;

    if(grantId.value > 0){
      const tx = await greengrant.updateGrant(grantId.value, grantTitle.value, grantDescription.value, grantGitUrl.value, grantWebsite.value, endTime);
      connectState.transactions.value.unshift(tx);
      connectState.transactionCount.value++;
      const msg = `<div><span>Update grant success! Transaction: </span><a href="${transactionExplorerUrl(tx)}" target="_blank">${tx}</a></div>`;
      element.elMessage('success', msg, true);        
    }else{
      const tx = await greengrant.mint(grantTitle.value, grantDescription.value, grantGitUrl.value, grantWebsite.value, grantToken.value, daoId.value, endTime);
      connectState.transactions.value.unshift(tx);
      connectState.transactionCount.value++;
      const msg = `<div><span>Create grant success! Transaction: </span><a href="${transactionExplorerUrl(tx)}" target="_blank">${tx}</a></div>`;
      element.elMessage('success', msg, true);      
    }    

    showAddNewGrantVisiable.value = false;

    handleClick();     

  }catch(e){
    element.alertMessage(e);
  }finally{
    loadDrawerStatus.value = false;
  }  
}

//click to delete a green grant
const onDeleteGreenGrant = async (grantId:number) => {
  try{
    const tx = await greengrant.burn(grantId);
    connectState.transactions.value.unshift(tx);
    connectState.transactionCount.value++;
    const msg = `<div><span>Delete grant success! Transaction: </span><a href="${transactionExplorerUrl(tx)}" target="_blank">${tx}</a></div>`;
    element.elMessage('success', msg, true);       

    handleClick();
  }catch(e){
    element.alertMessage(e);
  }
}

//click to support the grant
const onSupportGrant = async (grantInfo:any) => {
  const opts = {
    message: '',
    confirmButtonText: 'Send',
    cancelButtonText: 'Cancel',
    inputType: 'number',
    inputValue: '1',
    inputPlaceholder: '0',
    inputErrorMessage: 'Invalid value',
  };

  const erc20 = new ERC20(grantInfo.grantToken);

  const tokenSymbol = await getTokenCurencyName(grantInfo.grantToken);
  const tokenBalance = await erc20.balanceOf(connectState.userAddr.value);

  if(grantInfo.grantToken === zeroAddress){
    opts.message =  h('p', null, [
      h('p', null, 'Please enter the token amount to support the grant:'),
      h('p', { style: 'color: teal' }, `dao id: ${grantInfo.daoId}`),
      h('p', { style: 'color: teal' }, `dao name: ${grantInfo.daoName}`),
      h('p', { style: 'color: teal' }, `grant id: ${grantInfo.grantId}`),
      h('p', { style: 'color: teal' }, `grant name: ${grantInfo.grantName}`),
      h('p', { style: 'color: teal' }, `token name: ${tokenSymbol}`),
      h('p', { style: 'color: teal' }, `token balance: ${tokenBalance}`),
    ]);
  }else{
    opts.message =  h('p', null, [
      h('p', null, 'Please enter the token amount to support the grant:'),
      h('p', { style: 'color: teal' }, `dao id: ${grantInfo.daoId}`),
      h('p', { style: 'color: teal' }, `dao name: ${grantInfo.daoName}`),
      h('p', { style: 'color: teal' }, `grant id: ${grantInfo.grantId}`),
      h('p', { style: 'color: teal' }, `grant name: ${grantInfo.grantName}`),
      h('p', { style: 'color: teal' }, `token name: ${tokenSymbol}`),
      h('p', { style: 'color: teal' }, `token contract: ${grantInfo.grantToken}`),
      h('p', { style: 'color: teal' }, `token balance: ${tokenBalance}`),
    ]);
  }

  element.elMessageBox('Please enter the token amount to support the grant:', 'Send Token', opts, async (value:number) => {
    if(value <= 0){
      element.alertMessage("support token value must large than zero!");
      return;
    }

    try{
      if(grantInfo.grantToken != zeroAddress){
        const tx = await erc20.approve(greengrant.getAddress(), value);
        connectState.transactions.value.unshift(tx);
        connectState.transactionCount.value++;
        const msg = `<div><span>Approve token success! Transaction: </span><a href="${transactionExplorerUrl(tx)}" target="_blank">${tx}</a></div>`;
      }

      const tx = await greengrant.supportGrant(grantInfo.grantId, value);
      connectState.transactions.value.unshift(tx);
      connectState.transactionCount.value++;
      const msg = `<div><span>Support the grant success! Transaction: </span><a href="${transactionExplorerUrl(tx)}" target="_blank">${tx}</a></div>`;

      handleClick();
    }catch(e){
      element.alertMessage(e);
    }

  });  
}

//click to claim the grant treassure
const onClaimGrant = async (grantId: number) => {
  try{
    const tx = await greengrant.claimGrant(grantId);
    connectState.transactions.value.unshift(tx);
    connectState.transactionCount.value++;
    const msg = `<div><span>Claim grant success! Transaction: </span><a href="${transactionExplorerUrl(tx)}" target="_blank">${tx}</a></div>`;
    element.elMessage('success', msg, true);       

    handleClick();
  }catch(e){
    element.alertMessage(e);
  }
}

//get green grant infos by page size and page count
const getGreenGrantCount = async (onlyOwner:boolean) => {
  daoId.value = getDaoId();

  const indexs = await greengrant.getGrantIndexsByPageCount(pageSize.value, pageCount.value, daoId.value, onlyOwner);

  if(indexs.length < pageSize.value){
    hasMore.value = false;
  }else{
    hasMore.value = true;
  }

  const infoList = new Array();
  for(const i in indexs){
    const res = await greengrant.getGrantInfoById(indexs[i]);

    res.grantEnded = res.endTime < (new Date().getTime()/1000);
    res.grantOwner = await greengrant.ownerOf(indexs[i]);
    res.grantTreassure = (await greengrant.getGrantTreassure(indexs[i], false)).toPrecision(4);
    res.isOwner = res.grantOwner.toLowerCase() === connectState.userAddr.value.toLowerCase();

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

  greenGrantList.value = infoList;
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
      greenGrantList.value = new Array();
      return;
    }

    if(pageCount.value < 0){
      pageCount.value = 0;
    }

    const onlyOwner = activeName.value === 'mine';

    await getGreenGrantCount(onlyOwner);

  }catch(e){
    greenGrantList.value = new Array();
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