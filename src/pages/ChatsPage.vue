<template>
  <div class="file-arae">
    <el-container>
      <el-header style="background-color: #ffffff;">
        <el-tabs v-model="activeName" class="file-tabs" @tab-click="handleClick">
          <el-tab-pane label="Chat" name="chat"></el-tab-pane>
          <el-tab-pane label="Message" name="message"></el-tab-pane>
        </el-tabs>   
        <el-input v-model="searchAddress" :placeholder='userAddr' size="small" style="width: 350px;"></el-input>  
        <el-button type="primary" size="small" @click="handleClick">Search
        </el-button>   
        <el-drawer v-model="showChatShowVisiable" direction="rtl" destroy-on-close @opened="onChatDrawerOpen">
          <template #header>
            <h4>Chat With: {{chatToAddress}}</h4>   
          </template>
          <template #default>
            <template v-for="info in chatToMessageList" :key="info.timestamp">
              <el-card class="box-card" v-if="info.peer === true && info.from === chatToAddress.toLowerCase() && info.to === userAddr.toLowerCase()" style="float: left;width: 200px;color: #409EFF;">
                <span style="float: left;">{{(new Date(info.timestamp)).toLocaleString()}}</span><br/>
                <span style="float: left;">{{info.msg}}</span>
              </el-card>

              <el-card class="box-card" v-if="info.peer === false && info.from === userAddr.toLowerCase() && info.to === chatToAddress.toLowerCase()" style="float: right;width: 200px;color: #67C23A;">
                <span style="float: right;">{{(new Date(info.timestamp)).toLocaleString()}}</span><br/>
                <span style="float: right;">{{info.msg}}</span>
              </el-card>

            </template>
          </template>
          <template #footer>
            <div 
              style="flex: auto"
              v-loading="loadDrawerStatus" 
              element-loading-text="Checking..."
              :element-loading-spinner="svg"
              element-loading-svg-view-box="-10, -10, 50, 50"
              element-loading-background="#ffffff"
            >
              <el-input v-model="chatToMessage" placeholder="hello!" type="textarea" style="float: right;margin-bottom: 5px;" rows="3"></el-input>  
              <el-button v-if="chatOnline === true" type="primary" @click="onClearChatHistory">clear</el-button>
              <el-button v-if="chatOnline === true" type="primary" @click="onSaveChatHistory">save</el-button>
              <el-button v-if="chatOnline === true" type="primary" @click="onSendChatMessage">send</el-button>
              <el-button v-if="chatOnline === false" type="primary" @click="onLoginChat">login</el-button>
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
          <table v-if="activeName === 'chat'">
            <thead>
              <th style="width:400px">Address</th>
              <th style="width:550px">PeerId</th>
              <th style="width:50px">Chat</th>
            </thead>
            <tbody>
              <template v-for="info in greenChatList" :key="info.address">
                <tr>
                  <td style="width:400px">
                    <el-link type="success" target="_blank" :href="addressExplorerUrl(info.address)">{{info.address}}</el-link>
                    <el-icon @click="onClickToCopy(info.address)"><document-copy /></el-icon>
                  </td>
                  <td style="width:550px">{{info.peerId}}
                    <el-icon @click="onClickToCopy(info.peerId)"><document-copy /></el-icon>
                  </td>
                  <td style="width:50px">
                    <el-icon @click="changeChatAddress(info.address, info.peerId)"><ChatLineSquare /></el-icon>
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
          <table v-if="activeName === 'message'">
            <thead>
              <th style="width:400px">From</th>
              <th style="width:350px">Message</th>
              <th style="width:150px">Timestamp</th>
              <th style="width:50px">Chat</th>
            </thead>
            <tbody>
              <template v-for="info in chatToNewMessages" :key="info.from">
                <tr v-if="info.to===userAddr.toLowerCase()&&(searchAddress===''||searchAddress===info.from)">
                  <td style="width:400px">
                    <el-link type="success" target="_blank" :href="addressExplorerUrl(info.from)">{{info.from}}</el-link>
                    <el-icon @click="onClickToCopy(info.from)"><document-copy /></el-icon>
                  </td>
                  <td style="width:350px">{{info.msg}}
                    <el-icon @click="onClickToCopy(info.msg)"><document-copy /></el-icon>
                  </td>
                  <td style="width:150px">{{(new Date(info.timestamp)).toLocaleString()}}
                  </td>
                  <td style="width:50px">
                    <el-icon @click="changeChatAddress(info.from)"><ChatLineSquare /></el-icon>
                  </td>
                </tr>
              </template>
            </tbody>
          </table>          
        </el-row>
      </el-main>
      <el-footer>
        <div>
          <el-button v-if="activeName==='chat'" type="primary" style="margin-top: 10px;" @click="onHandlePrev">Prev
          </el-button>
          <el-button v-if="activeName==='chat'" type="primary" style="margin-top: 10px;" @click="onHandleNext" :disabled="!hasMore">Next
          </el-button>     
          <el-button v-if="activeName==='message'" type="primary" style="margin-top: 10px;" @click="onCleanNewMessages">Clean
          </el-button>
      </div>
      </el-footer>
    </el-container>
  </div>
</template>

<script lang="ts">
export default {
  name: 'ChatsPage',
  props: {
  }
}
</script>

<script setup lang="ts">
import { ref } from 'vue'

import { GreenChat } from "../libs/greenchat"
import * as tools from "../libs/tools"
import { connected, connectState } from "../libs/connect"
import * as constant from "../constant"
import * as element from "../libs/element"
import * as storage from '../libs/storage'
import { checkOnline, sendMessage} from '../libs/fluence'

const greenchat = new GreenChat();

const userAddr = connectState.userAddr;
const showChatShowVisiable = ref(false);
const searchAddress = ref('');

const chatOnline = ref(false);
const chatToAddress = ref('');
const chatToPeerId = ref('');
const chatToMessage = ref('');

const chatToNewMessages = connectState.fluenceChatNewMessages;
const chatToMessageList = connectState.fluenceChatMessages;

const activeName = connectState.activeName;
const loadDrawerStatus = ref(false);
const loadStatus = ref(false);

const greenChatList = ref(new Array());
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

//on click to chat with someone
const changeChatAddress = async (address:string, peerId:string = '') => {
  address = address.trim();
  peerId = peerId.trim();

  const fluenceId = await greenchat.getPeerId(connectState.userAddr.value);

  if(peerId === ''){
    peerId = await greenchat.getPeerId(address);
  }

  chatToAddress.value = address;
  chatToPeerId.value = peerId;

  showChatShowVisiable.value = true;

  if(fluenceId === '' || fluenceId != connectState.fluenceId){
    chatOnline.value = false;
    onLoginChat();
  }else{
    loadDrawerStatus.value = true;
    await onCheckUserOnline();
    loadDrawerStatus.value = false;
  }
  
}

//on click to login chat
const onLoginChat = async () => {
  try{
    loadDrawerStatus.value = true;

    const tx = await greenchat.updatePeerId();

    connectState.transactions.value.unshift(tx);
    connectState.transactionCount.value++;

    await onCheckUserOnline();

    chatOnline.value = true;
  }catch(e){
    element.alertMessage(e);
  }finally{
    loadDrawerStatus.value = false;
  }
}

//check user online or not
const onCheckUserOnline = async () => {
  try{

    checkOnline(chatToPeerId.value);

    for(let i = 0; i < 100; i++){
      await tools.sleep(100);
      if(connectState.fluenceOnline[chatToPeerId.value] === true){
        break;
      }
    }

    if(connectState.fluenceOnline[chatToPeerId.value] === false){
      element.alertMessage("target user is not online now!");
    }else{
      element.elMessage('success', 'login success, you can chat with the user now!', true);
    }    

  }catch(e){
    element.alertMessage("target user is not online now!");
  }

}

//on click to send message
const onSendChatMessage = async () => {
  
  if(connectState.fluenceOnline[chatToPeerId.value] === false){
    element.alertMessage("target user is not online now!");
    return;
  }

  chatToMessage.value = chatToMessage.value.trim();
  if(chatToMessage.value.length < 3){
    element.alertMessage("invalid !");
    return;
  }

  const timestamp = (new Date()).getTime();

  chatToMessageList.value.push({
    from: userAddr.value.toLowerCase(),
    to: chatToAddress.value.toLowerCase(),
    msg: chatToMessage.value,
    timestamp: timestamp,
    peer: false,
  });

  const res = await sendMessage(chatToPeerId.value, userAddr.value, chatToMessage.value, String(timestamp));

  //reset chat message
  chatToMessage.value = '';
}

//when chat drawer open
const onChatDrawerOpen = async () => {
  const chatlink = await greenchat.getChatHistory(chatToAddress.value);
  const chatList = new Array();

  for(const i in chatToMessageList.value){
    if(chatToMessageList.value[i].from === userAddr.value.toLowerCase() ||
      chatToMessageList.value[i].to === userAddr.value.toLowerCase()){

      chatList.push(chatToMessageList.value[i]);
    }
  }

  if(chatList.length === 0 && chatlink != ''){
    let res = await fetch(chatlink, {
      "referrer": (window as any).location.href,
      "referrerPolicy": "no-referrer-when-downgrade",
      "method": "GET",
      "credentials": "omit",
      "redirect": "follow",
    });
  
    if (res.status >= 200 && res.status <= 299){
      res = await res.json();

      for(const i in res){
        try{
          if(res[i].from === userAddr.value.toLowerCase() || res[i].to === userAddr.value.toLowerCase()){
            chatToMessageList.value.push(res[i]);
          }
        }catch(e){
          continue;
        }
      }
    }
  }
}

//click to clean new messages
const onCleanNewMessages = async () => {
  searchAddress.value = '';
  chatToMessageList.value = new Array();
}

//click to clean chat history
const onClearChatHistory = async () => {
  const chatList = new Array();
  for(const i in chatToMessageList.value){
    if(chatToMessageList.value[i].from != userAddr.value.toLowerCase() &&
      chatToMessageList.value[i].to != userAddr.value.toLowerCase()){

      chatList.push(chatToMessageList.value[i]);
    }
  }

  chatToMessageList.value = chatList;

  try{
    loadDrawerStatus.value = true;
    const tx = await greenchat.updateChatHistory(chatToAddress.value, '');
    connectState.transactions.value.unshift(tx);
    connectState.transactionCount.value++;
    const msg = `<div><span>Clear chat success! Transaction: </span><a href="${transactionExplorerUrl(tx)}" target="_blank">${tx}</a></div>`;
    element.elMessage('success', msg, true);       

  }catch(e){
    element.alertMessage(e);
  }finally{
    loadDrawerStatus.value = false;
  }
}

//click to save chat history
const onSaveChatHistory = async () => {
  const chatList = new Array();
  for(const i in chatToMessageList.value){
    if(chatToMessageList.value[i].from === userAddr.value.toLowerCase() ||
      chatToMessageList.value[i].to === userAddr.value.toLowerCase()){
      chatList.push(chatToMessageList.value[i]);
    }
  }

  if(chatList.length === 0){
    try{
      loadDrawerStatus.value = true;
      const tx = await greenchat.updateChatHistory(chatToAddress.value, '');
      connectState.transactions.value.unshift(tx);
      connectState.transactionCount.value++;
      const msg = `<div><span>Save chat success! Transaction: </span><a href="${transactionExplorerUrl(tx)}" target="_blank">${tx}</a></div>`;
      element.elMessage('success', msg, true);
    }catch(e){
      element.alertMessage(e);
    }finally{
      loadDrawerStatus.value = false;
    }

    return; 
  }

  const name = userAddr.value.toLowerCase() + chatToAddress.value.toLowerCase();
  const content = JSON.stringify(chatList);
  const contentType = 'application/json';

  const file = tools.makeFileObject(name, contentType, content);

  const reference = await storage.uploadFile(file);

  if(reference === ''){
    element.alertMessage('save chat history failed!');
    return;
  }

  try{
    loadDrawerStatus.value = true;
    const tx = await greenchat.updateChatHistory(chatToAddress.value, reference);
    connectState.transactions.value.unshift(tx);
    connectState.transactionCount.value++;
    const msg = `<div><span>Save chat success! Transaction: </span><a href="${transactionExplorerUrl(tx)}" target="_blank">${tx}</a></div>`;
    element.elMessage('success', msg, true);
  }catch(e){
    element.alertMessage(e);
  }finally{
    loadDrawerStatus.value = false;
  }
}

//get green chat infos by page size and page count
const getGreenChatCount = async (address:string) => {
  const infoList = new Array();

  address = address.trim();

  if(address != ''){
    const peerId = await greenchat.getPeerId(address);
    infoList.push({
      address: address,
      peerId: peerId,
    });
  }else{
    const res = await greenchat.getPeerList(pageSize.value, pageCount.value);

    if(res.length < pageSize.value){
      hasMore.value = false;
    }else{
      hasMore.value = true;
    }

    for(const i in res){
      if(res[i].isOwner || res[i].isOffline){
        continue;
      }

      infoList.push(res[i]);
    }
  }

  greenChatList.value = infoList;
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
      greenChatList.value = new Array();
      return;
    }

    if(pageCount.value < 0){
      pageCount.value = 0;
    }

    if(activeName.value === 'chat'){
      await getGreenChatCount(searchAddress.value);
    }

  }catch(e){
    greenChatList.value = new Array();
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
  if(activeName.value != 'chat' &&
    activeName.value != 'message'){

    activeName.value = 'chat';
  }
}catch(e){
  activeName.value = 'chat';
}

//update page size
handleClick();  
</script>