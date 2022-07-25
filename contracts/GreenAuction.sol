//SPDX-License-Identifier: MIT
pragma solidity ^0.8.7;

import "@chainlink/contracts/src/v0.8/KeeperCompatible.sol";
import "@openzeppelin/contracts/security/ReentrancyGuard.sol";
import "@openzeppelin/contracts/token/ERC721/extensions/ERC721Enumerable.sol";
import "@openzeppelin/contracts/token/ERC20/ERC20.sol";
import "@openzeppelin/contracts/utils/Counters.sol";

//auction status: waitting for start; on going; success; failed; canceled; finished(success, failed and canceled etc.);
enum Status {
    UPCOMING, //0
    ONGOING, //1
    FAILED, //2  
    SUCCESS, //3
    CLAIMED, //4
    RETURNBACK, //5
    FINISHED //6
}

enum AucType{
    English, //English Auction
    Dutch //Dutch Auction
}

struct AucInfo {
    uint256 daoId; //dao id for the auction
    uint256 nftId; //nft id to sell
    address nftContract; //nft contract to sell
    address nftOwner; //nft owner
    address payContract; //payment token contract
    address bidAddress; //bid address for now
    uint startTime; //auction start time
    uint endTime; //auction end time
    uint256 startPrice; //start price
    uint256 reversePrice; //reverse price
    uint256 priceDelta; //price delta
    uint256 bidPrice; //bid price for now
    Status status; //auction status
    AucType aucType; //auction type
} 

//green treassure interface
interface GreenTreassure {
    function addDaoTreassure(uint256 daoId, address from, address token, uint256 amount) external payable returns (bool);
}

//green dao interface
interface GreenDao {
    function checkInDao(uint256 daoId, address user) external view returns (bool);
}

contract GreenAuction is ERC721Enumerable, ReentrancyGuard, KeeperCompatibleInterface {   

    using Counters for Counters.Counter;

    //auctionId
    Counters.Counter private _aucId;

    //owner address
    address private _owner;

    //dao contract
    address private _daoContract;

    //treassure contract
    address private _treassureContract;

    //chainlink contract
    address private _chainlinkContract;

    //auctionInfo
    mapping(uint256 => AucInfo) private _aucInfos;      

    constructor() ERC721("Green Auction", "GRAUC") {
        _owner = msg.sender;
    } 

    //update contracts address, only owner support
    function updateContracts(address dao, address treassure, address chainlink) public {
        require(msg.sender == _owner);

        _daoContract = dao;
        _treassureContract = treassure;
        _chainlinkContract = chainlink;
    }  

    //check up keep for chainlink
    function checkUpkeep(bytes calldata /* checkData */) external view override returns (bool upkeepNeeded, bytes memory) {
       uint total = totalSupply();
       for(uint i = 0; i < total; i++){
           uint256 aucId = tokenByIndex(i);
           AucInfo memory auc = _getAucInfoById(aucId);

           if(auc.status == Status.FAILED || auc.status == Status.SUCCESS){
               return (true, abi.encode(aucId));
           }
       }

       return (false, abi.encode(0));
    }

    //perform up keep for chainlink
    function performUpkeep(bytes calldata performData) external override {
        uint aucId = abi.decode(performData, (uint));

        claimAuction(aucId);
    }

    //mint as a nft, and start a new auction
    function mint(
        uint startTime, 
        uint endTime, 
        uint256 startPrice, 
        uint256 reversePrice, 
        uint256 priceDelta,   
        AucType aucType,
        uint256 daoId,
        uint256 nftId,      
        address nftContract,
        address payContract
    ) public returns (uint256) {

        require(startPrice > 0 && reversePrice > 0 && priceDelta > 0, "price invalid");
        //msg.sender must in the given dao
        require(GreenDao(_daoContract).checkInDao(daoId, msg.sender) == true, "not in dao!");

        //use native token as pay token
        if(payContract == msg.sender || payContract == address(this) || payContract == nftContract){
            payContract = address(0x0);
        }        

        //transfer nft to the contract
        ERC721(nftContract).transferFrom(msg.sender, address(this), nftId);     

        //mint auction as a nft
        _aucId.increment();
        uint256 newId = _aucId.current();
        _mint(msg.sender, newId);

        //init auction info
        _aucInfos[newId] = AucInfo({
            daoId: daoId,
            nftId: nftId,
            nftContract: nftContract,
            nftOwner: msg.sender,
            payContract: payContract,
            bidAddress: address(0x0),
            startTime: startTime,
            endTime: endTime,
            startPrice: startPrice,
            reversePrice: reversePrice,
            priceDelta: priceDelta,
            bidPrice: 0,
            status: Status.UPCOMING,
            aucType: aucType
        });

        return newId;
    }     

    //cancel the auction
    function cancelAuction(uint256 aucId) public nonReentrant payable returns(bool){
        AucInfo memory auc = _aucInfos[aucId];     

        //check if already success or not
        require(auc.status == Status.UPCOMING || auc.status == Status.ONGOING, "auction finished!");

        //check the auction owner
        require(msg.sender == auc.nftOwner, "only owner allowed!");         

        //transfer the nft to the owner
        ERC721(auc.nftContract).transferFrom(address(this), auc.nftOwner, auc.nftId);

        //transfer tokens to the bid address
        if(auc.bidPrice > 0){
            if(auc.payContract == address(0x0)){
                payable(auc.bidAddress).transfer(auc.bidPrice);
            }else{
                ERC20(auc.payContract).transferFrom(address(this), auc.bidAddress, auc.bidPrice);
            }
        }        

        //delete auc info;
        delete _aucInfos[aucId];  
        //burn token
        _burn(aucId);

        return true;
    }

    //bid a price for the nft token
    function bidForNft(uint256 aucId, uint256 amount) public nonReentrant payable returns(bool){
        AucInfo memory auc = _getAucInfoById(aucId);

        //check if the acution is finished or not
        require(auc.status == Status.ONGOING, "status not ongoing!");

        //check paytoken is erc20 or not
        if(auc.payContract == address(0x0)){
            amount = msg.value;
        }else{
            //receive tokens
            ERC20(auc.payContract).transferFrom(msg.sender, address(this), amount);
        }       

        if(auc.aucType == AucType.English){
            //check the bid price large than the start price or not
            require(amount >= auc.startPrice, "price not enough!");

            //if no body bid a price yet
            if(auc.bidPrice > 0){
                //must large than the old bid price + min bid increase
                require(amount >= auc.bidPrice + auc.priceDelta, "price not enough!");

                //send back the payment to the old bid address
                if(auc.payContract == address(0x0)){
                    payable(auc.bidAddress).transfer(auc.bidPrice);
                }else{
                    ERC20(auc.payContract).transferFrom(address(this), auc.bidAddress, auc.bidPrice);
                }
            }
        }else{
            //check bid price
            require(amount + (block.timestamp - auc.startTime)*auc.priceDelta/86400 >= auc.startPrice, "bid price not enough!");

            //set the auction status, success if large than the reverse price and exchange tokens directly.
            if(amount >= auc.reversePrice){
                //set status to claimed
                _aucInfos[aucId].status = Status.SUCCESS;
            }else{
                //set status to failed
                _aucInfos[aucId].status = Status.FAILED;
            }

            //set endtime to current time
            _aucInfos[aucId].endTime = block.timestamp;
        }      

        //set new highest bid price and bid address
        _aucInfos[aucId].bidPrice = amount;
        _aucInfos[aucId].bidAddress = msg.sender;        
        
        return true;
    }

    //claim the auction by the nft owner or the bid address
    function claimAuction(uint256 aucId) internal returns(bool){      
        AucInfo memory auc = _getAucInfoById(aucId);

        //only nft owner and bid address can be claimed
        require(msg.sender == _aucInfos[aucId].bidAddress || msg.sender == _aucInfos[aucId].nftOwner || msg.sender == _chainlinkContract, "invalid user!");

        //only success status can be claimed
        require(auc.status == Status.SUCCESS || auc.status == Status.FAILED, "invalid status!");

        if(auc.status == Status.SUCCESS){
            //set auction status to success
            _aucInfos[aucId].status = Status.CLAIMED;  

            //send nft to the bid address
            ERC721(auc.nftContract).transferFrom(address(this), auc.bidAddress, auc.nftId); 

            //send the payment to the treassure address
            if(auc.payContract == address(0x0)){
                //add treassure to the treassure contract
                GreenTreassure(_treassureContract).addDaoTreassure{value: auc.bidPrice}(auc.daoId, auc.bidAddress, auc.payContract, auc.bidPrice);
            }else{
                //approve the amount
                ERC20(auc.payContract).approve(_treassureContract, auc.bidPrice);
                //add treassure to the treassure contract
                GreenTreassure(_treassureContract).addDaoTreassure(auc.daoId, auc.bidAddress, auc.payContract, auc.bidPrice);
            }       
        } else{
            _aucInfos[aucId].status = Status.RETURNBACK;

            //send nft to the bid address
            ERC721(auc.nftContract).transferFrom(address(this), auc.nftOwner, auc.nftId); 

            if(auc.bidPrice > 0){
                //send the payment to the nft owner
                if(auc.payContract == address(0x0)){
                    payable(auc.bidAddress).transfer(auc.bidPrice);
                }else{
                    ERC20(auc.payContract).transferFrom(address(this), auc.bidAddress, auc.bidPrice);
                }       
            }
        }             

        return true;
    }

    //get auction info by id
    function _getAucInfoById(uint256 aucId) internal view returns(AucInfo memory){
        AucInfo memory auc = _aucInfos[aucId];

        //UPCOMING is init status
        if(auc.status == Status.UPCOMING){
            if(block.timestamp < auc.startTime){
                //UPCOMING status
                return auc;
            }else if(block.timestamp <= auc.endTime){
                //on going
                auc.status = Status.ONGOING;
            }else if(auc.bidPrice >= auc.reversePrice){
                //auction success
                auc.status = Status.SUCCESS;
            }else{
                //auction failed
                auc.status = Status.FAILED;
            }       
        }

        return auc;
    }

    //get auction price info
    function getAuctionInfoById(uint256 aucId) public view returns(AucInfo memory){
        AucInfo memory auc = _getAucInfoById(aucId);

        if(auc.status == Status.UPCOMING || auc.status == Status.ONGOING){
            auc.reversePrice = 0;
        }

        return auc;
    }

    //get auction total count
    function getAuctionTotalCount(bool onlyOwner) public view returns(uint){
        if(onlyOwner){
            return balanceOf(msg.sender);
        }else{
            return totalSupply();
        }  
    }

    //get auction ids by Paginations
    function getAuctionIndexsByPage(uint pageSize, uint pageCount, uint256 daoId, Status aucStatus, bool onlyOwner) public view returns(uint256[] memory){
        uint total = getAuctionTotalCount(onlyOwner);  
        uint count;
        uint index;
        uint aucId;
        
        uint256[] memory indexList;

        if(pageSize > 100){
            pageSize = 100;
        }

        uint256[] memory tmpList = new uint256[](pageSize);
        uint start = pageSize * pageCount;
        uint end = start + pageSize;

        for(uint i = 0; i < total; i++){
            //get own auction or not
            if(onlyOwner){
                aucId = tokenOfOwnerByIndex(msg.sender, total - i - 1);
            }else{
                aucId = tokenByIndex(total -i - 1);
            }

            AucInfo memory auc = _getAucInfoById(aucId);
            if(daoId > 0 && auc.daoId != daoId){
                continue;
            }else if(aucStatus == Status.FINISHED && (auc.status == Status.UPCOMING || auc.status == Status.ONGOING)){
                continue;
            }else if(aucStatus != Status.FINISHED && aucStatus != auc.status){
                continue;
            }else{
                count++;
            }          

            if(count < start || count > end){
                continue;
            } else {
                tmpList[index++] = aucId;
            }
        }

        if(index > 0){
            indexList = new uint256[](index);
            for(uint i = 0; i < index; i++){
                indexList[i] = tmpList[i];
            }
        }

        return indexList;
    }
}