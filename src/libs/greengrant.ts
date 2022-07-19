// example imports 
import { providers, utils, Contract } from "ethers"

import { greenGrantContractAddress } from "../constant"

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
	"function updateContracts(address dao, address treassure, address chainlink) public",
	"function mint(string name, string desc, string git, string website, address token, uint256 daoId, uint endTime) public returns (uint256)",
	"function burn(uint256 grantId) public payable returns (bool)",
	"function updateGrant(uint256 grantId, string name, string desc, string git, string website, uint endTime) public returns (bool)",
	"function supportGrant(uint256 grantId, uint256 amount)public payable returns (bool)",
	"function claimGrant(uint256 grantId) public payable returns (bool)",
	"function getGrantTreassure(uint256 grantId, bool onlyOwner) public view returns (uint256)",
	"function getGrantTotalCount(bool onlyOwner) public view returns(uint)",
	"function getGrantInfoById(uint256 grantId) public view returns(tuple(string, string, string, string, address, uint256, uint, bool))",
	"function getGrantIndexsByPageCount(uint pageSize, uint pageCount, uint256 daoId, bool onlyOwner) public view returns (uint256 []memory)",
];

const zeroAddress = '0x0000000000000000000000000000000000000000';

export class GreenGrant {
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
			return (greenGrantContractAddress as any)[connectState.chainId];
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

	public updateContracts = async (dao:string, treassure:string, chainlink:string) => {
		if(dao === zeroAddress || dao === this.contractAddress){
			throw new Error("invalid dao address!");
		}

		if(treassure === zeroAddress || dao === this.contractAddress){
			throw new Error("invalid treassure address!");
		}
				
		const contract = await this.getContract();

		const tx = await contract.updateContracts(dao, treassure, chainlink);

		await tx.wait();

		return tx.hash;			
	}

	public mint = async (name:string, desc:string, git:string, website:string, token:string, daoId:number, endTime:number) => {
		name = name.trim();
		if(name.length <= 0){
			throw new Error("invalid grant name!");
		}

		desc = desc.trim();
		if(desc.length <= 0){
			throw new Error("invalid grant description!");
		}

		git = git.trim();
		if(git.length <= 0){
			throw new Error("invalid github url link!");
		}

		if(git.search("https://") != 0){
			throw new Error("github url must started with 'https://'!");
		}

		website = website.trim();
		if(website.length <= 0){
			throw new Error("invalid grant website!");
		}

		if(website.search("https://") != 0){
			throw new Error("website url must started with 'https://'!");
		}

		if(daoId <= 0){
			throw new Error("invalid dao id!");
		}

		if(endTime < (new Date()).getTime()/1000){
			throw new Error("invalid grant end time!");
		}

		const contract = await this.getContract();

		const tx = await contract.mint(name, desc, git, website, token, daoId, endTime);

		await tx.wait();

		return tx.hash;		
	}

	public burn = async (grantId:number) => {
		if(grantId <= 0){
			throw new Error("invalid grant id!");
		}

		const contract = await this.getContract();

		const tx = await contract.burn(grantId);

		await tx.wait();

		return tx.hash;			
	}

	public updateGrant = async (grantId:number, name:string, desc:string, git:string, website:string, endTime:number) => {
		if(grantId <= 0){
			throw new Error("invalid grant id!");
		}

		name = name.trim();
		desc = desc.trim();
		git = git.trim();
		website = website.trim();

		if(name.length <= 0){
			throw new Error("invalid grant name!");
		}

		if(git.length > 0 && git.search("https://") != 0){
			throw new Error("github url must started with 'https://'!");
		}		

		if(website.length > 0 && website.search("https://") != 0){
			throw new Error("website url must started with 'https://'!");
		}

		if(endTime > 0 && endTime < (new Date()).getTime()/1000){
			throw new Error("invalid grant end time!");
		}else{
			endTime = 0;
		}		

		const contract = await this.getContract();

		const tx = await contract.updateGrant(grantId, name, desc, git, website, endTime);

		await tx.wait();

		return tx.hash;			
	}

	public supportGrant = async (grantId:number, amount:number) => {
		if(grantId <= 0){
			throw new Error("invalid grant id!");
		}

		if(amount <= 0){
			throw new Error("invalid amount!");
		}

		const payContract = (await this.getGrantInfoById(grantId)).grantToken;

		const options = {
			value: utils.parseEther('0'),
		}

		let value;

		if(payContract === zeroAddress){
			value = utils.parseEther(String(amount));
			options.value = value;
		}else{
			const erc20 = new ERC20(payContract);
			value = utils.parseUnits(String(amount), await erc20.decimals());	
		}	

		const contract = await this.getContract();

		const tx = await contract.supportGrant(grantId, value, options);

		await tx.wait();

		return tx.hash;			
	}

	public claimGrant = async (grantId:number) => {
		if(grantId <= 0){
			throw new Error("invalid grant id!");
		}

		const contract = await this.getContract();

		const tx = await contract.claimGrant(grantId);

		await tx.wait();

		return tx.hash;
	}

	public getGrantTreassure = async (grantId:number, onlyOwner:boolean) => {
		if(grantId <= 0){
			throw new Error("invalid grant id!");
		}

		const payContract = (await this.getGrantInfoById(grantId)).grantToken;

		const contract = await this.getContract();

		const res = await contract.getGrantTreassure(grantId, onlyOwner);

		if(payContract === zeroAddress){
			return Number(utils.formatEther(res));
		}else{
			const erc20 = new ERC20(payContract);

			return Number(utils.formatUnits(res, await erc20.decimals()));
		}
	}

	public getGrantTotalCount = async (onlyOwner:boolean) => {
		const contract = await this.getContract();

		const res = await contract.getGrantTotalCount(onlyOwner);

		return res.toNumber();
	}

	//todo parse grant info
	public getGrantInfoById = async (grantId:number) => {
		if(grantId <= 0){
			throw new Error("invalid grant id!");
		}

		const contract = await this.getContract();

		const res = await contract.getGrantInfoById(grantId);

		return {
			grantId: grantId,
			grantName: res[0],
			grantDesc: res[1],
			grantGitUrl: res[2],
			grantWebsite: res[3],
			grantToken: res[4],
			daoId: res[5].toNumber(),
			endTime: res[6].toNumber(),
			grantPayed: res[7],
		};
	}

	public getGrantIndexsByPageCount = async (pageSize:number, pageCount:number, daoId:number, onlyOwner:boolean) => {
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

		const res = await contract.getGrantIndexsByPageCount(pageSize, pageCount, daoId, onlyOwner);

		for(const i in  res){
			indexList.push(res[i].toNumber());
		}

		return indexList;
	}
}