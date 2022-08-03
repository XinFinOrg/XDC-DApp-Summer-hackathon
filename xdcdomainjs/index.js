const Web3 = require('web3');

const contract = require('./contracts/abi.json');

const abi = contract.abi;

const config = 
{
	testnet:{
		rpcUrl: "",
		contractAddress: ""
	},
	mainnet:{ 
		rpcUrl: "https://rpc.xinfin.network/",
		contractAddress: "xdc295a7ab79368187a6cd03c464cfaab04d799784e"
	},
	defaultNetwork: "mainnet"
}

const defaultKeys = ["avatar","cover","website","email","social:twitter","social:facebook","social:telegram","social:discord","social:instagram"];

var exports=module.exports={};

exports.SDK = function (options) {
	
	var _config = config;
	if (options){
		_config  = options;
	}
	
	var rpcUrl = config.mainnet.rpcUrl;
	var contractAddress = config.mainnet.contractAddress;
	
	if (_config.defaultNetwork == 'testnet'){
		rpcUrl = _config.testnet.rpcUrl;
		contractAddress = _config.testnet.contractAddress;
		if (typeof contractAddress == 'undefined'){
			contractAddress = _config.testnet.contactAddress;
		}
	}
	if (_config.defaultNetwork == 'mainnet'){
		rpcUrl = _config.mainnet.rpcUrl;
		contractAddress = _config.mainnet.contractAddress;
		if (typeof contractAddress == 'undefined'){
			contractAddress = _config.mainnet.contactAddress;
		}
	}
	
	contractAddress = contractAddress.replace(/^(xdc|XDC)/i,'0x'); 
	
	const web3 = new Web3(new Web3.providers.HttpProvider(rpcUrl));
	
	const contractFirst = new web3.eth.Contract(abi, contractAddress);
	
	const func = new Object();
	
	func.balanceOf = async (address) => 
	{
		const _address = address.replace(/^(xdc|XDC)/i,'0x');
		const balance = await contractFirst.methods.balanceOf(_address).call();
		return balance;
	}
	
	
	func.getOwner = async (domain, metadata = false) => 
	{
		const ownerAddress = await contractFirst.methods.getOwner(domain).call();
		const obj = new Object();
		obj.owner = ownerAddress.replace(/^(0x|0X)/i,'xdc');
		obj.owner0x = ownerAddress;
		var arg = [];
		if (metadata == true){
			
			const tokenId = await contractFirst.methods._tokenIdMaps(domain).call();
			
			const values = await contractFirst.methods.getMany(defaultKeys, tokenId).call();

			for (let i = 0; i < defaultKeys.length; ++i) {
				const _obj = new Object();
				_obj.key = defaultKeys[i];
				_obj.value = values[i];
				arg.push(_obj)
			}
		}
		obj.metadata = arg;
		return obj;
	}
	
	func.getDomain = async (_address) => 
	{
		const address = _address.replace(/^(xdc|XDC)/i,'0x');
		try{
			const defaultDomain = await contractFirst.methods.reverseOf(address).call();
			return defaultDomain
		}catch{}
		return "";
	}
  
    func.getDomains = async (_address) => 
	{
		const address = _address.replace(/^(xdc|XDC)/i,'0x');
		const domains = [];
		try{
			const arg = await contractFirst.methods.getDomainbyAddress(address).call();
			return arg.domains;
		}catch{}
		return domains;
	}
	
	func.getMetadata = async (key, domain) => 
	{
		const tokenId = await contractFirst.methods._tokenIdMaps(domain).call();
		var value = await contractFirst.methods.get(key, tokenId).call();
		var obj = new Object();
		obj.key = key
		obj.value = value
		return obj;
	}
	
	func.getMetadatas = async (keys, domain) => 
	{
		const tokenId = await contractFirst.methods._tokenIdMaps(domain).call();
		const values = await contractFirst.methods.getMany(keys, tokenId).call();
		var arg = [];
		for (let i = 0; i < keys.length; ++i) {
			var obj = new Object();
			obj.key = keys[i];
			obj.value = values[i];
			arg.push(obj)
		}
		return arg;
	}
	
	func.getTokenId = async (domain) => {
		const tokenId = await contractFirst.methods._tokenIdMaps(domain).call();
		return tokenId;
	}
	
	return func;	
}


