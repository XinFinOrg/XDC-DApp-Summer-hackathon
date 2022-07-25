import * as path from "path"

import * as web3storage from './web3storage'
import { connectState } from './connect'
import * as tools from "./tools"
import * as constant from "../constant"

//get real file url link based on the storage type
const getFileLink = (filename:string, filetype:string, fileid:string) => {
  if(fileid === undefined || fileid === null || fileid === ''){
    return '';
  }
  
  switch(connectState.storage){
    case 'filcoin':
      if(filetype==='folder'){
        return `https://${fileid}.ipfs.dweb.link`;
        // return constant.web3Gateway + fileid;
      } else if (filetype==='website'){
        return `https://${fileid}.ipfs.dweb.link/index.html`;
        // return constant.web3Gateway + fileid + '/index.html';
      } else {
        return `https://${fileid}.ipfs.dweb.link/${filename}`;
        // return constant.web3Gateway + fileid + '/' + filename;
      }
  }

  return fileid;
}

//upload file
export const uploadFile = async (file: any) => {
  return await uploadFolder(file.name, [file]);
}

//upload folder
export const uploadFolder = async (dirPath: string, files: any[]) => {
  if(files.length===0){
    throw new Error("no files selected to upload!");
  }

  let size = 0;
  for(const i in files){
    size += (files[i].raw as any).size;
  }

  let filetype = '';
  if(files.length === 1 && ((files[0].raw) as any).webkitRelativePath === ''){
    filetype = tools.fileType(files[0].name).split('/')[0];
    if(filetype!='image'&&filetype!='audio'&&filetype!='video'){
      filetype = 'docs';
    }
  }else{
    filetype = 'folder';
    for(const i in files){
      const relpath = ((files[i].raw) as any).webkitRelativePath.split(path.sep).slice(1,).join(path.sep);
      if(relpath==='index.html'){
        filetype = 'website';
        break;
      }
    }
  }

  //upload to the remote storage
  let fileid = '';
  switch (connectState.storage){
    case 'filcoin':
      fileid = await web3storage.uploadFolder(dirPath, files);
      break;
    default:
      fileid = await web3storage.uploadFolder(dirPath, files);
      break;
  }   

  fileid = getFileLink(dirPath, filetype, fileid);

  return fileid;
}