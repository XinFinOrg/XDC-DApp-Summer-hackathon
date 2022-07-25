export const contractAddress = "0x13953D9cb43d36fAffEE2a25E15A58dEeaE53148"; // NFT smart contract Address


export const abi = [
	{
		"inputs": [
			{
				"internalType": "string",
				"name": "_key",
				"type": "string"
			},
			{
				"internalType": "string",
				"name": "name1",
				"type": "string"
			},
			{
				"internalType": "string",
				"name": "description1",
				"type": "string"
			},
			{
				"internalType": "address",
				"name": "owner1",
				"type": "address"
			},
			{
				"internalType": "uint256",
				"name": "price1",
				"type": "uint256"
			},
			{
				"internalType": "string",
				"name": "path1",
				"type": "string"
			},
			{
				"internalType": "string",
				"name": "path_extension",
				"type": "string"
			},
			{
				"internalType": "string",
				"name": "pathBlur1",
				"type": "string"
			}
		],
		"name": "addDocument",
		"outputs": [],
		"stateMutability": "nonpayable",
		"type": "function"
	},
	{
		"inputs": [
			{
				"internalType": "address payable",
				"name": "_to",
				"type": "address"
			}
		],
		"name": "buyDocument",
		"outputs": [],
		"stateMutability": "payable",
		"type": "function"
	},
	{
		"inputs": [
			{
				"internalType": "uint256",
				"name": "",
				"type": "uint256"
			}
		],
		"name": "_document_key",
		"outputs": [
			{
				"internalType": "string",
				"name": "",
				"type": "string"
			}
		],
		"stateMutability": "view",
		"type": "function"
	},
	{
		"inputs": [
			{
				"internalType": "string",
				"name": "_key",
				"type": "string"
			}
		],
		"name": "getDocumentByKey",
		"outputs": [
			{
				"components": [
					{
						"internalType": "string",
						"name": "name",
						"type": "string"
					},
					{
						"internalType": "string",
						"name": "description",
						"type": "string"
					},
					{
						"internalType": "address",
						"name": "owner",
						"type": "address"
					},
					{
						"internalType": "uint256",
						"name": "price",
						"type": "uint256"
					},
					{
						"internalType": "string",
						"name": "path_extension",
						"type": "string"
					},
					{
						"internalType": "string",
						"name": "path",
						"type": "string"
					}
				],
				"internalType": "struct Contract.Document",
				"name": "",
				"type": "tuple"
			}
		],
		"stateMutability": "view",
		"type": "function"
	},
	{
		"inputs": [],
		"name": "getDocuments",
		"outputs": [
			{
				"internalType": "string[]",
				"name": "",
				"type": "string[]"
			},
			{
				"components": [
					{
						"internalType": "string",
						"name": "name",
						"type": "string"
					},
					{
						"internalType": "string",
						"name": "description",
						"type": "string"
					},
					{
						"internalType": "address",
						"name": "owner",
						"type": "address"
					},
					{
						"internalType": "uint256",
						"name": "price",
						"type": "uint256"
					},
					{
						"internalType": "string",
						"name": "path_extension",
						"type": "string"
					},
					{
						"internalType": "string",
						"name": "path",
						"type": "string"
					}
				],
				"internalType": "struct Contract.Document[]",
				"name": "",
				"type": "tuple[]"
			}
		],
		"stateMutability": "view",
		"type": "function"
	}
];
