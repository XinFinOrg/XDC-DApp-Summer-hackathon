// example imports 
import { providers, utils, Contract } from "ethers"

import { greenChatContractAddress } from "../constant"

import {networkConnect, connectState} from "./connect"

import { connectFluence } from "./fluence"

const abi = [
	"function updatePeerId(string peerId) public",
	"function updateChatHistory(address to, string link) public",
	"function getPeerId(address to) public view returns (string)",
	"function getPeerList(uint pageSize, uint pageCount) public view returns (address[], string[])",
	"function getChatHistory(address to) public view returns (string)",
];

export class GreenChat {
	private contractAddress: string;

	constructor(contractAddress:string = ''){
		this.contractAddress = contractAddress;
	}

	private getContract = async () => {
		await networkConnect();

		return new Contract(this.getAddress(), abi, connectState.signer);		
	}	

	public getAddress = () => {
		if(this.contractAddress != ''){
			return this.contractAddress;
		}else{
			return (greenChatContractAddress as any)[connectState.chainId];
		}
	}	

	public updatePeerId = async () => {

		if(connectState.fluenceId === ''){
			const fluenceId = await connectFluence();

			if(fluenceId === undefined || fluenceId === null || fluenceId === ''){
				throw new Error("connect to fluence failed!");
			}else{
				connectState.fluenceId = fluenceId;
			}
		}

		const contract = await this.getContract();

		const tx = await contract.updatePeerId(connectState.fluenceId);

		await tx.wait();

		return tx.hash;
	}

	public updateChatHistory = async (to:string, link:string) => {
		to = to.trim();
		if(to.length === 0){
			throw new Error("invalid address to!");
		}

		link = link.trim();

		const contract = await this.getContract();

		const tx = await contract.updateChatHistory(to, link);

		await tx.wait();

		return tx.hash;
	}

	public getPeerId = async (to:string) => {
		to = to.trim();
		if(to.length === 0){
			throw new Error("invalid address to!");
		}

		const contract = await this.getContract();

		return await contract.getPeerId(to);
	}

	public getPeerList = async (pageSize:number, pageCount:number) => {
		if(pageSize <= 0 || pageSize > 100){
			throw new Error("invalid page size!");
		}

		if(pageCount < 0){
			throw new Error("invalid page count!");
		}

		const contract = await this.getContract();

		const res = await contract.getPeerList(pageSize, pageCount);

		const peerList = new Array();

		for(const i in res[0]){
			const address = res[0][i].toLowerCase();
			const peerId = res[1][i];

			peerList.push({
				address: address,
				peerId: peerId,
				isOwner: address === connectState.userAddr.value.toLowerCase(),
				isOffline: peerId === '',
			});
		}

		return peerList;
	}

	public getChatHistory = async (to:string) => {
		to = to.trim();
		if(to.length === 0){
			throw new Error("invalid address to!");
		}

		const contract = await this.getContract();

		return await contract.getChatHistory(to);
	}
}