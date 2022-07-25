# Green-Dao

Green-Dao is a web3 dao paltform for the public goods that make the world more green and sustainable.

There are five parts now for this dapp.

 1. green daos
 
    (1). eveny body can create a green dao to some public good for the world. 
    
    (2). you can create a public dao that means every one can join it.
    
    (3). you can also create a private dao that means only the dao onwer can add dao members.
    
 2. green auctions
 
    (1). dao members can sell their erc721 tokens as an auction to earn some cryptos.
    
    (2). both english auction and dutch auction are supported.
    
    (3). both blockchain native token and erc20 tokens are supported as the pay token.
    
    (4). the dao will receive the payment through the dao treassure contract, then they can create a vote to use the dao treassures.
    
    (5). use chainlink keeper that can auto send the payment to the dao treassure contract when the auction finished.
 
 3. green grants
    
    (1). dao members also can create a grant to build a project for the public good to make the world green.
    
    (2). eveny body can send some crpptos to support the grant.
    
    (3). both blockchain native token and erc20 tokens are supported as the pay token.
    
    (4). the dao will receive the payment through the dao treassure contract, then they can create a vote to use the dao treassures.
    
    (5). use chainlink keeper that can auto send the payment to the dao treassure contract when the grant finished.
 
 4. green votes

    (1). manage the dao treassures. all the cryptos received from the auctions and grants will send to this contract.
    
    (2). the dao members can create a vote to withdraw the dao treassure for the public goods or vote to do something else.

 5. green learnings

    (1). the dao members can publish some learning resources for public goods to make the world green.
    
    (2). the more learning resources will make the dao more reliable for the people.

 6. green chat

    (1). use the Fluence Project to build a browser to browser chat that help the online people comunicate with each other.
    
    (2). people can save the chat history messages to the the filcoin.
 
 ***How to run:***
 
 0. make sure you are using linux system installed with git, node, npm .etc.
 
 1. clone this project
 
        git clone https://github.com/shepherliu/Green-Dao.git
        
 2. cd this project
 
        cd Green-Dao
        
 3. install dependencies
 
        npm install
        
 4. update fluence service if you modify the aqua/greenchat.aqua file

        npm run compile-aqua

        sed -i "" "s/let script =/const script =/g" ./src/\_aqua/greenchat.ts

 5. run it on https://localhost:8080/

        npm run serve
        
 6. build it for production
 
        npm run build
        
 7. see our daemon website: https://green-dao-orcin.vercel.app

       build on blockchains now: 

              rinkeby test network

              goerli test network

              polygon mumbai network

 8. see our daemon video: https://youtu.be/KZevWy_uPI8

 9. see our daemon video for green chat build with fluence project: https://youtu.be/8w2pv0gsNR4
 
 ***Future Plans:***
 
 1. add page to show dao treassure balance and track the event log of the use of dao treassure.
 2. integrated with the gitcoin passport identity platform to verify the dao members identities.
 3. add smart contract marketplace that can auto execute green tasks. for example auto buy and burn the carbon emission tokens.
 4. more functions upgrade to the green chat using fluence project. such as optimize the user check online and add function to send message to offline people.
 5. ui/ux degsign for more user friendly.
 
 ***How it use chainlink:***
 
 1. we use the chainlink keeper functions in our smart contracts, these smart contracts are Keepers-compatible Contracts:
 
        contracts/GreenAuction.sol
      
        contracts/GreenGrant.sol
 
 2. the checkUpkeep function will check if the auction/grant ended or not.
 
 3. the performUpkeep function will automaticly execute the claim functions when the auction/grant is ended. And send the payment to the dao treassure contract, then set the auction/grant status to payed.
 
 ***How it works:***
 
 <img width="627" alt="snapshot" src="https://user-images.githubusercontent.com/84829620/178649437-73d63478-8307-4257-a70d-40226903f724.png">
 
 1. All the functions are based on the dao, user must join one of the dao or create their own dao. Then they can publish auctions, grants, learnings and votes under the dao.
 
 2. The dao can be public that every one can join it. And it also can be private that only the owner can add dao members.

 3. All the payments of the dao will send to the vote contract address as the dao treassure address, the dao must create a vote to use their dao treassures.
 
***Resources:***

*1. Block Chain Resource Docs:*

getting started with Ploygon Network: https://docs.polygon.technology/, https://faucet.polygon.technology/

getting started with Boba Network: https://docs.boba.network/

getting started with Metis Network: https://docs.metis.io/

getting started with Meter Network: https://docs.meter.io/developer-documentation/introduction

*2. Distributed Storage Resource Docs:*

getting started with IPFS & Filecoin: https://bitly.protocol.ai/IPFS_Filecoin_Get_Started

getting started with Web3Storage: https://web3.storage/docs/

*3. Chainlink Resource Docs*

getting started with Chainlink: https://docs.chain.link/

getting started with Chainlink Keeper: https://docs.chain.link/docs/chainlink-keepers/compatible-contracts/

register Chainlink upkeep: https://docs.chain.link/docs/chainlink-keepers/register-upkeep/

*4. Fluence Resource Docs*

getting started with Fluence: https://doc.fluence.dev/docs/

getting started with Fluence Examples: https://github.com/fluencelabs/examples 

*5. Covalent Resource Docs:*

getting started with Covalent: https://www.covalenthq.com/

getting started with Covalent apis: https://www.covalenthq.com/docs/api/#/

*6. Gitcoin Passport Resource Docs:*

getting started with gitcoin passport: https://docs.passport.gitcoin.co/gitcoin-passport-sdk/getting-started

github resources: https://github.com/gitcoinco/passport-sdk

*7. Transak Docs:*

getting started wit Transak: https://integrate.transak.com/

*8. Unstoppable Domains:*

unstoppable Domains Resolution API: https://docs.alchemy.com/alchemy/enhanced-apis/unstoppable-domains-apis

NFT domains registry architecture: https://docs.unstoppabledomains.com/domain-registry-essentials/uns-vs-cns-comparison

domain resolution SDKs: https://docs.unstoppabledomains.com/send-and-receive-crypto-payments/resolution-libraries

how to resolve domains using direct blockchain call (without SDK): https://docs.unstoppabledomains.com/send-and-receive-crypto-payments/direct-blockchain-calls

crypto payments integration guide: https://docs.unstoppabledomains.com/send-and-receive-crypto-payments/crypto-payments

*9. Smart contract Resource Docs:*

online solidity compilier: https://chainide.com/, https://remix.ethereum.org/

getting started with ethers.js: https://docs.ethers.io/v5/

getting started with solidity: https://docs.soliditylang.org/en/latest/

*10. Fronted Resource Docs:*

getting started with Vue3: https://vuejs.org/guide/introduction.html

getting started with Element Ui: https://element-plus.org/en-US/component/menu.html



***Contract Me:***

Email: shepher.liu@gmail.com

Unstoppable Domain Register Email: shepher.liu@gmail.com

Discord: swarmlover#4063
