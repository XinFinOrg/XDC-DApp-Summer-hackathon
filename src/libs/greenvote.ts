// example imports 
import { providers, utils, Contract } from "ethers"

import { greenVoteContractAddress } from "../constant"

import {networkConnect, connectState} from "./connect"

import { ERC20 } from "./erc20"

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
	"function addDaoTreassure(uint256 daoId, address from, address token, uint256 amount) public payable returns (bool)",
	"function mint(string name, string desc, uint256 daoId, uint256 value, address token, address to, uint endTime) public returns (uint256)",
	"function burn(uint256 voteId) public returns (bool)",
	"function updateVote(uint256 voteId, string name, string desc, uint endTime) public returns (bool)",
	"function vote(uint256 voteId, uint8 status) public returns (bool)",
	"function getDaoTreassure(uint256 daoId, address token) public view returns (uint256)",
	"function getVoteTotalCount(bool onlyOwner) public view returns (uint)",
	"function getVoteInfoById(uint256 voteId) public view returns (tuple(string, string, uint256, uint256, address, address, uint, uint, uint, bool, bool))",
	"function getVoteIndexsByPageCount(uint pageSize, uint pageCount, uint256 daoId, bool onlyOwner) public view returns(uint256[])",
];

const zeroAddress = '0x0000000000000000000000000000000000000000';

export class GreenVote {
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
			return (greenVoteContractAddress as any)[connectState.chainId];
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

	public addDaoTreassure = async (daoId:number, from:string, token:string, amount:number) => {
		if(daoId <= 0){
			throw new Error("invalid dao id!");
		}

		if(amount <= 0){
			throw new Error("invalid amount!");
		}

		const contract = await this.getContract();

		const options = {
			value: utils.parseEther('0'),
		}

		let value;

		if(token === zeroAddress){
			value = utils.parseEther(String(amount));
			options.value = value;
		}else{
			const erc20 = new ERC20(token);
			value = utils.parseUnits(String(amount), await erc20.decimals());
		}

		const tx = await contract.addDaoTreassure(daoId, from, token, value, options);

		await tx.wait();

		return tx.hash;			
	}

	public mint = async (name:string, desc:string, daoId:number, amount:number, token:string, to:string, endTime:number) => {
		name = name.trim();
		desc = desc.trim();

		if(name.length <= 0){
			throw new Error("invalid vote name!");
		}

		if(desc.length <= 0){
			throw new Error("invalid vote description!");
		}

		if(daoId <= 0){
			throw new Error("invalid dao id!");
		}

		if(endTime < (new Date()).getTime()/1000){
			throw new Error("invalid grant end time!");
		}		

		if(amount > 0){
			if(amount > await this.getDaoTreassure(daoId, token)){
				throw new Error("invalid amount, large than the dao treassure balance!");
			}
		}

		const contract = await this.getContract();

		let value;

		if(token === zeroAddress){
			value = utils.parseEther(String(amount));
		}else{
			const erc20 = new ERC20(token);
			value = utils.parseUnits(String(amount), await erc20.decimals());
		}

		const tx = await contract.mint(name, desc, daoId, value, token, to, endTime);

		await tx.wait();

		return tx.hash;				
	}

	public burn = async (voteId:number) => {
		if(voteId <= 0){
			throw new Error("invalid vote id!");
		}

		const contract = await this.getContract();

		const tx = await contract.burn(voteId);

		await tx.wait();

		return tx.hash;
	}

	public updateVote = async (voteId:number, name:string, desc:string, endTime:number) => {
		if(voteId <= 0){
			throw new Error("invalid vote id!");
		}

		name = name.trim();
		desc = desc.trim();
		if(name.length <= 0){
			throw new Error("invalid vote name!");
		}		

		if(endTime > 0 && endTime < (new Date()).getTime()/1000){
			throw new Error("invalid grant end time!");
		}else{
			endTime = 0;
		}	

		const contract = await this.getContract();

		const tx = await contract.updateVote(voteId, name, desc, endTime);

		await tx.wait();

		return tx.hash;		
	}

	public vote = async (voteId:number, status:number) => {
		if(voteId <= 0){
			throw new Error("invalid vote id!");
		}

		if(status < 0 || status > 2){
			throw new Error("invalid vote status!");
		}

		const contract = await this.getContract();

		const tx = await contract.vote(voteId, status);

		await tx.wait();

		return tx.hash;		
	}

	public getDaoTreassure = async (daoId:number, token:string) => {
		if(daoId <= 0){
			throw new Error("invalid dao id!");
		}

		const contract = await this.getContract();

		const res = await contract.getDaoTreassure(daoId, token);

		if(token === zeroAddress){
			return Number(utils.formatEther(res));
		}else{
			const erc20 = new ERC20(token);
			return Number(utils.formatUnits(res, await erc20.decimals()));
		}
	}

	public getVoteTotalCount = async (onlyOwner:boolean) => {
		const contract = await this.getContract();

		const res = await contract.getVoteTotalCount(onlyOwner);

		return res.toNumber();
	}

	//todo parse vote info
	public getVoteInfoById = async (voteId:number) => {
		if(voteId <= 0){
			throw new Error("invalid vote id!");
		}

		const contract = await this.getContract();

		const res = await contract.getVoteInfoById(voteId);

		const payContract = res[4];
		let value;
		if(payContract === zeroAddress){
			value = Number(utils.formatEther(res[3]));
		}else{
			const erc20 = new ERC20(payContract);
			const decimals = await erc20.decimals();

			value = Number(utils.formatUnits(res[3], decimals));
		}

		return {
			voteId: voteId,
			voteName: res[0],
			voteDesc: res[1],
			daoId: res[2].toNumber(),
			voteValue: value,
			voteToken: payContract,
			voteTo: res[5],
			voteAggree: res[6].toNumber(),
			voteAgainst: res[7].toNumber(),
			endTime: res[8].toNumber(),
			voteSuccess: res[9],
			votePayed: res[10],
		};
	}

	public getVoteIndexsByPageCount = async (pageSize:number, pageCount:number, daoId:number, onlyOwner:boolean) => {
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

		const res = await contract.getVoteIndexsByPageCount(pageSize, pageCount, daoId, onlyOwner);

		for(const i in res){
			indexList.push(res[i].toNumber());
		}

		return indexList;
	}
}