// example imports 
import { providers, utils, Contract } from "ethers"

import { greenAuctionContractAddress } from "../constant"

import { networkConnect, connectState } from "./connect"

import { ERC20 } from "./erc20"
import { ERC721 } from "./erc721"

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
	"function mint(uint startTime, uint endTime, uint256 startPrice, uint256 reversePrice, uint256 priceDelta, uint8 aucType, uint256 daoId, uint256 nftId, address nftContract, address payContract) public returns (uint256)",
	"function cancelAuction(uint256 aucId) public payable returns(bool)",
	"function bidForNft(uint256 aucId, uint256 amount) public payable returns(bool)",
	"function claimAuction(uint256 aucId) public payable returns(bool)",
	"function getAuctionInfoById(uint256 aucId) public view returns(tuple(uint256, uint256, address, address, address, address, uint, uint, uint256, uint256, uint256, uint256, uint8, uint8))",
	"function getAuctionTotalCount(bool onlyOwner) public view returns(uint)",
	"function getAuctionIndexsByPage(uint pageSize, uint pageCount, uint256 daoId, uint8 aucStatus, bool onlyOwner) public view returns(uint256[] memory)",
];

const zeroAddress = '0x0000000000000000000000000000000000000000';

export class GreenAuction {
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
			return (greenAuctionContractAddress as any)[connectState.chainId];
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

	public mint = async (start:number, end:number, startPrice:number, reversePrice:number, priceDelta:number, aucType:number, daoId:number, nftId:number, nftContract:string, payContract:string) => {
		if(start <= 0){
			throw new Error("invalid start time!");
		}

		if(end < (new Date()).getTime()/1000){
			throw new Error("invalid end time!");
		}

		if(start >= end){
			throw new Error("invalid end time!");
		}

		if(startPrice <= 0){
			throw new Error("invalid start price!");
		}

		if(reversePrice <= 0){
			throw new Error("invalid reverse price!");
		}

		if(aucType != 0 && aucType != 1){
			throw new Error("invalid auction type!");
		}

		if(daoId <= 0){
			throw new Error("invalid dao id!");
		}

		if(nftId <= 0){
			throw new Error("invalid nft id!");
		}

		const erc721 = new ERC721(nftContract);
		const owner = (await erc721.ownerOf(nftId)).toLowerCase();

		if(owner != connectState.userAddr.value.toLowerCase()){
			throw new Error("you are not the nft owner!");
		}

		const contract = await this.getContract();

		let sPrice, rPrice, dPrice;

		if(payContract === zeroAddress){
			sPrice = utils.parseEther(String(startPrice));
			rPrice = utils.parseEther(String(reversePrice));
			dPrice = utils.parseEther(String(priceDelta));
		}else{
			const erc20 = new ERC20(payContract);
			const decimals = await erc20.decimals();

			sPrice = utils.parseUnits(String(startPrice), decimals);
			rPrice = utils.parseUnits(String(reversePrice), decimals);
			dPrice = utils.parseUnits(String(priceDelta), decimals);			
		}

		const tx = await contract.mint(start, end, sPrice, rPrice, dPrice, aucType, daoId, nftId, nftContract, payContract);

		await tx.wait();

		return tx.hash;		
	}

	public cancelAuction = async (aucId:number) => {
		if(aucId <= 0){
			throw new Error("invalid auction id!");
		}

		const contract = await this.getContract();

		const tx = await contract.cancelAuction(aucId);

		await tx.wait();

		return tx.hash;				
	}

	public bidForNft = async (aucId:number, amount:number) => {
		if(aucId <= 0){
			throw new Error("invalid auction id!");
		}

		if(amount <= 0){
			throw new Error("invalid bid price!");
		}

		const payContract = (await this.getAuctionInfoById(aucId)).payContract;

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
		const tx = await contract.bidForNft(aucId, value, options);

		await tx.wait();
		return tx.hash;			
	}

	public claimAuction = async (aucId:number) => {
		if(aucId <= 0){
			throw new Error("invalid auction id!");
		}

		const contract = await this.getContract();

		const tx = await contract.claimAuction(aucId);

		await tx.wait();

		return tx.hash;		
	}

	//todo parse auction info
	public getAuctionInfoById = async (aucId:number) => {
		if(aucId <= 0){
			throw new Error("invalid auction id!");
		}

		const contract = await this.getContract();

		const res = await contract.getAuctionInfoById(aucId);

		const payContract = res[4];
		let sPrice, rPrice, dPrice, bPrice;

		if(payContract === zeroAddress){
			sPrice = Number(utils.formatEther(res[8]));
			rPrice = Number(utils.formatEther(res[9]));
			dPrice = Number(utils.formatEther(res[10]));
			bPrice = Number(utils.formatEther(res[11]));
		}else{
			const erc20 = new ERC20(payContract);
			const decimals = await erc20.decimals();

			sPrice = Number(utils.formatUnits(res[8], decimals));
			rPrice = Number(utils.formatUnits(res[9], decimals));
			dPrice = Number(utils.formatUnits(res[10], decimals));		
			bPrice = Number(utils.formatUnits(res[11], decimals));		
		}

		return {
			aucId: aucId,
			daoId: res[0].toNumber(),
			nftId: res[1].toNumber(),
			nftContract: res[2],
			nftOwner: res[3],
			payContract: res[4],
			bidAddress: res[5],
			startTime: res[6].toNumber(),
			endTime: res[7].toNumber(),
			startPrice: sPrice,
			reversePrice: rPrice,
			priceDelta: dPrice,
			bidPrice: bPrice,
			aucStatus: res[12],
			acuType: res[13],
		};
	}

	public getAuctionTotalCount = async (onlyOwner:boolean) => {
		const contract = await this.getContract();

		const res = await contract.getAuctionTotalCount(onlyOwner);

		return res.toNumber();
	}

	public getAuctionIndexsByPage = async (pageSize:number, pageCount:number, daoId:number, aucStatus:number, onlyOwner:boolean) => {
		if(pageSize <= 0 || pageSize > 100){
			throw new Error("invalid page size!");
		}

		if(pageCount < 0){
			throw new Error("invalid page count!");
		}

		if(daoId < 0){
			daoId = 0;
		}

		if(aucStatus < 0){
			aucStatus = 0;
		}

		const contract = await this.getContract();

		const res = await contract.getAuctionIndexsByPage(pageSize, pageCount, daoId, aucStatus, onlyOwner);

		const indexList = [];

		for(const i in res){
			indexList.push(res[i].toNumber());
		}

		return indexList;
	}
}