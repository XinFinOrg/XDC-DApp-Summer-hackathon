// example imports 
import { providers, utils, Contract } from "ethers"

import { greenLearningContractAddress } from "../constant"

import {networkConnect, connectState} from "./connect"

const abi = [
	"function isApprovedForAll(address owner, address operator) public view returns (bool)",
	"function name() public view returns (string)",
	"function symbol() public view returns (string)",
	"function ownerOf(uint256 tokenId) public view returns (address)",
	"function tokenByIndex(uint256 index) public view returns (uint256)",
	"function tokenOfOwnerByIndex(address owner, uint256 index) public view returns (uint256)",
	"function tokenURI(uint256 tokenId) public view returns (string)",
	"function totalSupply() public view returns (uint256)",
	"function balanceOf(address owner) public view returns (uint256)",
	"function approve(address to, uint256 tokenId) public returns (bool)",
	"function getApproved(uint256 tokenId) public view returns (address)",
	"function safeTransferFrom(address from, address to, uint256 tokenId) public returns (bool)",
	"function transferFrom(address from, address to, uint256 tokenId) public returns (bool)",
	"function setApprovalForAll(address operator, bool approved) public returns (bool)",
	"function updateContracts(address dao) public",
	"function mint(string name, string desc, string url, uint256 daoId, uint8 learningType) public returns (uint256)",
	"function burn(uint256 learningId) public returns (bool)",
	"function likeTheLearning(uint256 learningId) public returns (bool)",
	"function hateTheLearning(uint256 learningId) public returns (bool)",
	"function getLearningTotalCount(bool onlyOwner) public view returns(uint)",
	"function getLearningInfoById(uint256 learningId) public view returns (tuple(string,string,string,uint8,uint256,uint,uint))",
	"function getLearningIndexsByPageCount(uint pageSize, uint pageCount, uint256 daoId, bool onlyOwner) public view returns(uint256[])",
];

const zeroAddress = '0x0000000000000000000000000000000000000000';

export class GreenLearning {
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
			return (greenLearningContractAddress as any)[connectState.chainId];
		}
	}		

	public isApprovedForAll = async (owner:string, operator:string) => {
		const contract = await this.getContract();

		return await contract.isApprovedForAll(owner, operator);
	}

	public name = async () => {
		const contract = await this.getContract();

		return await contract.name();
	}

	public symbol = async () => {
		const contract = await this.getContract();

		return await contract.symbol();
	}

	public totalSupply = async () => {
		const contract = await this.getContract();

		const res = await contract.totalSupply();

		return res.toNumber();
	}

	public balanceOf = async (address:string) => {
		const contract = await this.getContract();

		const res = await contract.balanceOf(address);

		return res.toNumber();
	}

	public ownerOf = async (tokenId:number) => {
		if(tokenId <= 0){
			throw new Error("invalid token id!");
		}

		const contract = await this.getContract();

		return await contract.ownerOf(tokenId);
	}

	public tokenByIndex = async (index:number) => {
		if(index < 0){
			throw new Error("invalid token index!");
		}

		const contract = await this.getContract();

		const res = await contract.tokenByIndex(index);

		return res.toNumber();
	}

	public tokenOfOwnerByIndex = async (owner:string, index:number) => {
		if(index < 0){
			throw new Error("invalid token index!");
		}

		const contract = await this.getContract();

		const res = await contract.tokenOfOwnerByIndex(owner, index);

		return res.toNumber();
	}

	public tokenURI = async (tokenId:number) => {
		if(tokenId <= 0){
			throw new Error("invalid token id!");
		}

		const contract = await this.getContract();

		return await contract.tokenURI(tokenId);
	}

	public approve = async (to:string, tokenId:number) => {
		if(tokenId <= 0){
			throw new Error("invalid token id!");
		}

		const contract = await this.getContract();

		const tx = await contract.approve(to, tokenId);

		await tx.wait();

		return tx.hash;
	}

	public getApproved = async (tokenId:number) => {
		if(tokenId <= 0){
			throw new Error("invalid token id!");
		}

		const contract = await this.getContract();

		return await contract.getApproved(tokenId);
	}

	public safeTransferFrom = async (from:string, to:string, tokenId:number) => {
		if(tokenId <= 0){
			throw new Error("invalid token id!");
		}

		const contract = await this.getContract();

		const tx = await contract.safeTransferFrom(from, to, tokenId);

		await tx.wait();

		return tx.hash;		
	}

	public transferFrom = async (from:string, to:string, tokenId:number) => {
		if(tokenId <= 0){
			throw new Error("invalid token id!");
		}
				
		const contract = await this.getContract();

		const tx = await contract.transferFrom(from, to, tokenId);

		await tx.wait();

		return tx.hash;		
	}

	public setApprovalForAll = async (operator:string, approved:boolean) => {
		const contract = await this.getContract();

		const tx = await contract.setApprovalForAll(operator, approved);

		await tx.wait();

		return tx.hash;		
	}

	public updateContracts = async (dao:string) => {
		if(dao === zeroAddress || dao === this.contractAddress){
			throw new Error("invalid dao address!");
		}

		const contract = await this.getContract();

		const tx = await contract.updateContracts(dao);

		await tx.wait();

		return tx.hash;		
	}

	public mint = async (name:string, desc:string, url:string, daoId:number, learningType:number) => {
		name = name.trim();
		desc = desc.trim();
		url = url.trim();

		if(name.length <= 0){
			throw new Error("invalid learning name!");
		}

		if(desc.length <= 0){
			throw new Error("invalid learning description!");
		}

		if(url.length <= 0){
			throw new Error("invalid learning url link!");
		}

		if(url.search("https://") != 0){
			throw new Error("learning url link must started with 'https://'!");
		}

		if(daoId <= 0){
			throw new Error("invalid dao id!");
		}

		if(learningType < 0 || learningType > 3){
			throw new Error("invalid learning type!");
		}

		const contract = await this.getContract();

		const tx = await contract.mint(name, desc, url, daoId, learningType);

		await tx.wait();

		return tx.hash;			
	}

	public burn = async (learningId:number) => {
		if(learningId <= 0){
			throw new Error("invalid learning id!");
		}

		const contract = await this.getContract();

		const tx = await contract.burn(learningId);

		await tx.wait();

		return tx.hash;
	}

	public likeTheLearning = async (learningId:number) => {
		if(learningId <= 0){
			throw new Error("invalid learning id!");
		}

		const contract = await this.getContract();

		const tx = await contract.likeTheLearning(learningId);

		await tx.wait();

		return tx.hash;		
	}

	public hateTheLearning = async (learningId:number) => {
		if(learningId <= 0){
			throw new Error("invalid learning id!");
		}

		const contract = await this.getContract();

		const tx = await contract.hateTheLearning(learningId);

		await tx.wait();

		return tx.hash;		
	}

	public getLearningTotalCount = async (onlyOwner:boolean) => {
		const contract = await this.getContract();

		const res = await contract.getLearningTotalCount(onlyOwner);

		return res.toNumber();
	}

	//todo parse learning info
	public getLearningInfoById = async (learningId:number) => {
		if(learningId <= 0){
			throw new Error("invalid learning id!");
		}

		const contract = await this.getContract();

		const res = await contract.getLearningInfoById(learningId);

		return {
			learningId: learningId,
			learningName: res[0],
			learningDesc: res[1],
			learningUrl: res[2],
			learningType: res[3],
			daoId: res[4].toNumber(),
			learningLikes: res[5].toNumber(),
			learningHates: res[6].toNumber(),
		};
	}

	public getLearningIndexsByPageCount = async (pageSize:number, pageCount:number, daoId:number, onlyOwner:boolean) => {
		if(pageSize <= 0 || pageSize > 100){
			throw new Error("invalid page size!");
		}

		if(pageCount < 0){
			throw new Error("invalid page count!");
		}

		if(daoId < 0){
			daoId = 0;
		}

		const contract = await this.getContract();

		const indexList = [];

		const res = await contract.getLearningIndexsByPageCount(pageSize, pageCount, daoId, onlyOwner);

		for(const i in res){
			indexList.push(res[i].toNumber());
		}

		return indexList;
	}
}