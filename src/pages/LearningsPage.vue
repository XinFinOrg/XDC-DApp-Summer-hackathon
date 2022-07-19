<template>
  <div class="file-arae">
    <el-container>
      <el-header style="background-color: #ffffff;">
        <el-tabs v-model="activeName" class="file-tabs" @tab-click="handleClick">
          <el-tab-pane label="All" name="all"></el-tab-pane>
          <el-tab-pane label="Mine" name="mine"></el-tab-pane>
        </el-tabs>     
        <el-button type="primary" size="small" style="float: right;margin-right: 50px;" @click="showAddNewLearningVisiable = true;">NEW+
        </el-button>   
        <el-drawer v-model="showAddNewLearningVisiable" direction="rtl" destroy-on-close @opened="onAddNewLearningOpen">
          <template #header>
            <h4>Create A New Green Learning.</h4>   
          </template>
          <template #default>  
            <table style="margin-left: 10px;">
              <tr v-if="learningId > 0">
                <td style="width:120px">Id
                  <el-popover
                    placement="top-start"
                    title="Learning Id"
                    :width="200"
                    trigger="hover"
                    content="The id of the green learning."
                  >
                    <template #reference>
                     <el-icon><QuestionFilled /></el-icon>
                    </template>
                </el-popover>
                </td>
                <td style="width:300px">
                  <el-input v-model="learningId" disabled>
                    <template #append>
                      <el-icon @click="onClickToCopy(learningId)"><document-copy /></el-icon>
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
                    content="The dao id of the green dao. The learning must be published through the dao."
                  >
                    <template #reference>
                     <el-icon><QuestionFilled /></el-icon>
                    </template>
                </el-popover>
                </td>
                <td style="width:300px">
                  <el-input v-model="daoId" @change="updateDaoName(daoId)" :disabled="learningId > 0">
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
                    title="Learning Title"
                    :width="200"
                    trigger="hover"
                    content="The title of the green learning."
                  >
                    <template #reference>
                     <el-icon><QuestionFilled /></el-icon>
                    </template>
                </el-popover>
                </td>
                <td style="width:300px">
                  <el-input v-model="learningTitle">
                    <template #append>
                      <el-icon @click="onClickToCopy(learningTitle)"><document-copy /></el-icon>
                    </template>
                  </el-input>
                </td>                   
              </tr>           
              <tr>
                <td style="width:120px">Description
                  <el-popover
                    placement="top-start"
                    title="Learning Description"
                    :width="200"
                    trigger="hover"
                    content="The description of the green learning."
                  >
                    <template #reference>
                     <el-icon><QuestionFilled /></el-icon>
                    </template>
                </el-popover>
                </td>
                <td style="width:300px">
                  <el-input v-model="learningDescription" type="textarea" rows = "5">
                  </el-input>
                </td>                   
              </tr>  
              <tr>
                <td style="width:120px">Type
                  <el-popover
                    placement="top-start"
                    title="Learning Resource Type"
                    :width="200"
                    trigger="hover"
                    content="The resource type of the green learning."
                  >
                    <template #reference>
                     <el-icon><QuestionFilled /></el-icon>
                    </template>
                </el-popover>
                </td>
                <td style="width:300px">
                  <el-select 
                    v-model="learningResourceType"
                    style="width:100%;margin-left:0px;"
                    placeholder="Select Resource Type"
                    :teleported="false"
                    @change="onLearningResourceTypeChange"
                    filterable
                  >
                    <el-option key="image" label="Image" value="image"/>
                    <el-option key="audio" label="Audio" value="audio"/>
                    <el-option key="video" label="Video" value="video"/>
                    <el-option key="website" label="Website" value="website"/>
                  </el-select> 
                </td>                   
              </tr>   
              <tr>
                <td style="width:120px">URL
                  <el-popover
                    placement="top-start"
                    title="Learning Resource"
                    :width="200"
                    trigger="hover"
                    content="The resource url link of the green learning. You can input the url link directly or upload the resource files through the upload button."
                  >
                    <template #reference>
                     <el-icon><QuestionFilled /></el-icon>
                    </template>
                </el-popover>
                </td>
                <td style="width:300px">
                  <el-input v-model="learningResource">
                    <template #append>
                      <el-icon @click="onClickToCopy(learningResource)"><document-copy /></el-icon>
                    </template>
                  </el-input>
                </td>                   
              </tr>      
              <tr 
                v-loading="loadResourceStatus"
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
                    :multiple="multiple"
                    class="upload-resource"
                    ref="uploadResource"
                    action=""
                    @change="onChangeSelectResourceFiles"
                    @click="onSelectResourceFiles"
                    :accept="resourceAccept"
                    :limit="limits"
                    :on-exceed="handleResourceExceed"
                    :show-file-list="false"
                    :auto-upload="false"
                  >
                    <template #trigger>
                      <el-button type="primary" style="float: right;margin-right: 10px;width: 100%;">Select Files</el-button>
                    </template>
                  </el-upload>
                  <el-button type="success" style="float: right;" @click="onUploadResourceFiles">Upload</el-button>
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
              <el-button @click="cancelLearningUpdate">cancel</el-button>
              <el-button type="primary" @click="confirmLearningUpdate">confirm</el-button>
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
          <template v-for="info in greenLearningList" :key="info.learningId">
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
                    <el-popover placement="bottom-start" :width="230" title="Learning Info">
                      <template #reference>
                        <span>
                          <el-link type="success" target="_blank" :href="tokenExplorerUrl(greenlearning.getAddress(),info.learningId)">{{info.learningName}}
                          </el-link>
                        </span>
                      </template>
                      <h4>Title: {{info.learningName}}</h4>
                      <h4>Description: {{info.learningDesc}}</h4>
                    </el-popover>
                    <span>
                      <el-button v-if="activeName === 'mine'" type="danger" style="float: right;" size="small" @click="onDeleteGreenLearning(info.learningId)"><el-icon><Delete /></el-icon></el-button>
                    </span>  
                  </div>
                </template>
                <el-row>
                  <img v-if="info.learningType === 0" :src="info.learningUrl" style="width: 250px;height: 200px;" />
                  <audio v-if="info.learningType === 1" :src="info.learningUrl" controls preload style="width: 250px;height: 200px;" />
                  <video v-if="info.learningType === 2" :src="info.learningUrl" controls preload style="width: 250px;height: 200px;" />
                  <iframe frameborder="0" v-if="info.learningType === 3" sandbox="allow-scripts allow-same-origin allow-popups" :src="info.learningUrl" style="width: 250px;height: 200px;" />
                </el-row>
                <el-row style="margin-top: 5px;float: right;">
                  <el-link type="primary" style="float: right;" @click="onLikeLearning(info.learningId)">Likes : {{info.learningLikes}}</el-link>
                  <el-link type="warning" style="float: right;" @click="onHateLearning(info.learningId)">Hates : {{info.learningHates}}</el-link>
                  <el-link type="success" style="float: right;" size="small" :href="info.learningUrl" target="_blank">View</el-link>
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
  name: 'LearningsPage',
  props: {
  }
}
</script>

<script setup lang="ts">
  
import { getCurrentInstance, ComponentInternalInstance, ref } from 'vue'

import { toRaw } from '@vue/reactivity'
import { genFileId } from 'element-plus'
import * as path from "path"
import type { UploadInstance, UploadProps, UploadRawFile, UploadFile, UploadFiles } from 'element-plus'

import { connected, connectState } from "../libs/connect"
import * as constant from "../constant"
import * as element from "../libs/element"
import * as tools from "../libs/tools"
import * as storage from '../libs/storage'
import { GreenDao } from "../libs/greendao"
import { GreenLearning } from "../libs/greenlearning"
 
const { proxy } = getCurrentInstance() as ComponentInternalInstance;

const activeName = connectState.activeName;
const loadStatus = ref(false);
const loadResourceStatus = ref(false);

const greendao = new GreenDao();
const greenlearning = new GreenLearning();

const multiple = ref(false);
const limits = ref(1);
const resourceAccept = ref('');
const uploadResource = ref<UploadInstance>();
const resourceFileList = ref(new Array());

const showAddNewLearningVisiable = ref(false);
const loadDrawerStatus = ref(false);
const daoId = ref(0);
const daoName = ref('');
const learningId = ref(0);
const learningTitle = ref('');
const learningDescription = ref('');
const learningResource = ref('');
const learningResourceType = ref('image');

const greenLearningList = ref(new Array());
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

//on learning type change
const onLearningResourceTypeChange = async () => {
  learningResource.value = '';
  uploadResource.value!.clearFiles();

  const currentClass = (proxy as any).$el.parentNode.querySelector(".upload-resource");

  if(learningResourceType.value === 'image'){
    resourceAccept.value = 'image/*';
    limits.value = 1;
    multiple.value = false;
    (currentClass.querySelector(".el-upload__input") as any).webkitdirectory = false;  
  }else if(learningResourceType.value === 'audio'){
    resourceAccept.value = 'audio/*';
    limits.value = 1;
    multiple.value = false;
    (currentClass.querySelector(".el-upload__input") as any).webkitdirectory = false;  
  }else if(learningResourceType.value === 'video'){
    resourceAccept.value = 'video/*';
    limits.value = 1;
    multiple.value = false;
    (currentClass.querySelector(".el-upload__input") as any).webkitdirectory = false;  
  }else {
    resourceAccept.value = '';
    limits.value = 0;
    multiple.value = true;
    (currentClass.querySelector(".el-upload__input") as any).webkitdirectory = true;
  }
}

//on learning resource files change
const onChangeSelectResourceFiles = async (uploadFile: UploadFile, uploadFiles: UploadFiles) => {
  resourceFileList.value = toRaw(uploadFiles);

  if(multiple.value){
    learningResource.value = (resourceFileList.value[0].raw as any).webkitRelativePath.split(path.sep)[0];
    if(learningResource.value === ''){
      learningResource.value = (resourceFileList.value[0].raw as any).name;
    }
  }else{
    learningResource.value = (resourceFileList.value[0].raw as any).name;
  }
}

//on click to select learning resource files
const onSelectResourceFiles = async () => {
  uploadResource.value!.clearFiles();
}

//handle file exceed
const handleResourceExceed: UploadProps['onExceed'] = (files:any) => {
  if(multiple.value === false){
    uploadResource.value!.clearFiles();
    const file = files[0] as UploadRawFile;
    file.uid = genFileId();
    uploadResource.value!.handleStart(file);
  }
};

//on click to upload learning resource files
const onUploadResourceFiles = async () => {
  if(multiple.value){
    await opUploadResourceFolder();
  }else{
    await opUploadResourceFile();
  }
}

//upload a sigle file
const opUploadResourceFile = async () => {
  try{

      loadResourceStatus.value = true;

      if(toRaw(resourceFileList.value).length === 0){
        element.elMessage('warning', 'You have not select any file to upload!');
        return;
      }

      learningResource.value = await storage.uploadFile(toRaw(resourceFileList.value)[0]);
    }catch(e){
      element.alertMessage(e);
    }finally{
      loadResourceStatus.value = false;
    }
}

//upload a website folder, must has an index.html in the root path of the folder
const opUploadResourceFolder = async () => {
    try{

      loadResourceStatus.value = true;

      if(toRaw(resourceFileList.value).length === 0){
        element.elMessage('warning', 'You have not select any folder to upload!');
        return;
      }

      let directory = (resourceFileList.value[0].raw as any).webkitRelativePath.split(path.sep)[0];
      if(directory === ''){
        directory = (resourceFileList.value[0].raw as any).name;
      }

      let findIndex = false;

      for(const i in resourceFileList.value){
        const file = resourceFileList.value[i];
        if((file.raw as any).name === 'index.html'){
          findIndex = true;
          break;
        }
      }

      if(findIndex === false){
        element.elMessage('warning', 'Not a valid website folder, index.html not found in the root path!');
        return;
      }

      learningResource.value = await storage.uploadFolder(directory, toRaw(resourceFileList.value));
    }catch(e){
      element.alertMessage(e);
    }finally{
      loadResourceStatus.value = false;
    }
}

//on click to create a new learning 
const onAddNewLearningOpen = async () => {
  daoId.value = getDaoId();
  learningId.value = 0;
  learningTitle.value = '';
  learningDescription.value = '';
  learningResource.value = '';
  learningResourceType.value = 'image';

  await onLearningResourceTypeChange();

  await updateDaoName(daoId.value);
}

//cancle create the learning
const cancelLearningUpdate = async () => {
  showAddNewLearningVisiable.value = false;
}

//confirm to create the new learning
const confirmLearningUpdate = async () => {
  try{
    loadDrawerStatus.value = true;

    //learning type baseod the resources
    let learningType;
    if (learningResourceType.value === 'image'){
      learningType = 0;
    }else if(learningResourceType.value === 'audio'){
      learningType = 1;
    }else if(learningResourceType.value === 'video'){
      learningType = 2;
    }else {
      learningType = 3;
    }

    const tx = await greenlearning.mint(learningTitle.value, learningDescription.value, learningResource.value, daoId.value, learningType);
    connectState.transactions.value.unshift(tx);
    connectState.transactionCount.value++;
    const msg = `<div><span>Create new learning success! Transaction: </span><a href="${transactionExplorerUrl(tx)}" target="_blank">${tx}</a></div>`;
    element.elMessage('success', msg, true);       

    showAddNewLearningVisiable.value = false;

    handleClick();
  }catch(e){
    element.alertMessage(e);
  }finally{
    loadDrawerStatus.value = false;
  }
}

//click to delete a green learning
const onDeleteGreenLearning = async (learningId:number) => {
  try{
    const tx = await greenlearning.burn(learningId);
    connectState.transactions.value.unshift(tx);
    connectState.transactionCount.value++;
    const msg = `<div><span>Delete learning success! Transaction: </span><a href="${transactionExplorerUrl(tx)}" target="_blank">${tx}</a></div>`;
    element.elMessage('success', msg, true);       

    handleClick();
  }catch(e){
    element.alertMessage(e);
  }
}

//click to like a green learning
const onLikeLearning = async (learningId:number) => {
  try{
    const tx = await greenlearning.likeTheLearning(learningId);
    connectState.transactions.value.unshift(tx);
    connectState.transactionCount.value++;
    const msg = `<div><span>Like the learning success! Transaction: </span><a href="${transactionExplorerUrl(tx)}" target="_blank">${tx}</a></div>`;
    element.elMessage('success', msg, true);       

    handleClick();
  }catch(e){
    element.alertMessage(e);
  }  
}

//click to hate a green learning
const onHateLearning = async (learningId:number) => {
  try{
    const tx = await greenlearning.hateTheLearning(learningId);
    connectState.transactions.value.unshift(tx);
    connectState.transactionCount.value++;
    const msg = `<div><span>Hate the learning success! Transaction: </span><a href="${transactionExplorerUrl(tx)}" target="_blank">${tx}</a></div>`;
    element.elMessage('success', msg, true);       

    handleClick();
  }catch(e){
    element.alertMessage(e);
  }  
}

//get green learning infos by page size and page count
const getGreenLearningCount = async (onlyOwner:boolean) => {
  daoId.value = getDaoId();

  const indexs = await greenlearning.getLearningIndexsByPageCount(pageSize.value, pageCount.value, daoId.value, onlyOwner);

  if(indexs.length < pageSize.value){
    hasMore.value = false;
  }else{
    hasMore.value = true;
  }

  const infoList = new Array();
  for(const i in indexs){
    const res = await greenlearning.getLearningInfoById(indexs[i]);

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

  greenLearningList.value = infoList;
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
      greenLearningList.value = new Array();
      return;
    }

    if(pageCount.value < 0){
      pageCount.value = 0;
    }

    const onlyOwner = activeName.value === 'mine';

    await getGreenLearningCount(onlyOwner);

  }catch(e){
    greenLearningList.value = new Array();
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