import { Web3Storage } from 'web3.storage'

import * as path from "path"

import { connectState } from './connect'
import * as constant from "../constant"

//get web3 storage client
const getClient = () => {
  const apiToken = connectState.web3Storage === '' ? constant.web3StorageAppKey : connectState.web3Storage;
  // Construct with token and endpoint
  const client = new Web3Storage({ token: apiToken, endpoint: new URL(constant.web3StorageHost) });

  return client;
}
//upload file
export const uploadFile = async (file: any) => {
  return await uploadFolder(file.name, [file]);
}

//upload folder
export const uploadFolder = async (dirPath: string, files: any[]) => {
  const client = getClient();

  const data = [];

  if(files.length === 1 && ((files[0].raw) as any).webkitRelativePath === ''){
    data.push(files[0].raw);
  }else{
    for(const i in files){
      const file = files[i].raw;
      const relpath = (file as any).webkitRelativePath.split(path.sep).slice(1,).join(path.sep);
      data.push(new File([file], relpath));
    }
  }

  return await client.put(data, {
    name: dirPath,
    maxRetries: 3,
  });
}

//get files from the filcoin
export const getFiles = async (cid:string) => {
	const client = getClient();

	const res = await client.get(cid);

	const files = await (res as any).files();

	return files;
}