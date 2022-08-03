# XDC Web3 Domains

Nodejs SDK

Npm: https://www.npmjs.com/package/xdcdomainjs

Github: https://github.com/XDCWeb3Domains/xdcdomainjs

Before installing the package you need to check and be sure to install the packages below:

```
npm install web3 
```

Install Package

```
npm install xdcdomainjs
```

Call 
```
const domainjs = require('xdcdomainjs');
```

Set config
```
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

```

Install

```
   // install
	const sdk = domainjs.SDK(config);

	// change your domains
	const _domain = "xdc.xdc";
	
	// change your address
	const _address = "xdc0d29025fa6a82772b5a2ceed27fb8f447e846901";
	
	// resolve domain to get the address of the owner. metadata: true // false default return metadata along with domain information
	const owner = await sdk.getOwner(_domain, false);

	console.log(owner);

	// get total domains
	const balance = await sdk.balanceOf(_address);

	console.log(balance);

	// get a domain default from a user's address, requiring the user to set the default domain name initially.
	const domain = await sdk.getDomain(_address);

	console.log(domain);
	
	// gets all the domains owned by an wallet address.
	const domains = await sdk.getDomains(_address);

	console.log(domains);
	
	//get a value of metadata from the domain name
	const _avatar = await sdk.getMetadata("avatar", _domain);

	console.log(_avatar);
	
	//get values of metadata from the domain name
	const _values = await sdk.getMetadatas(["avatar", "website", "social:twitter"], _domain);

	console.log(_values);
	
	//namehash is a recursive process that can generate a unique hash for any valid domain name.
	const tokenId = await sdk.getTokenId(_domain);

	console.log(tokenId);
```

Pls update test.js for specific instructions

Thanks!



