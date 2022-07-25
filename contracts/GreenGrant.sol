//SPDX-License-Identifier: MIT
pragma solidity ^0.8.7;

import "@chainlink/contracts/src/v0.8/KeeperCompatible.sol";
import "@openzeppelin/contracts/security/ReentrancyGuard.sol";
import "@openzeppelin/contracts/token/ERC721/extensions/ERC721Enumerable.sol";
import "@openzeppelin/contracts/token/ERC20/ERC20.sol";
import "@openzeppelin/contracts/utils/Counters.sol";

struct GrantInfo {
    string grantName;
    string grantDesc;
    string grantGitUrl;
    string grantWebsite;
    address grantToken;
    uint256 daoId;
    uint endTime;
    bool grantPayed;
}

//green treassure interface
interface GreenTreassure {
    function addDaoTreassure(uint256 daoId, address from, address token, uint256 amount) external payable returns (bool);
}

//green dao interface
interface GreenDao {
    function checkInDao(uint256 daoId, address user) external view returns (bool);
}

contract GreenGrant is ERC721Enumerable, ReentrancyGuard, KeeperCompatibleInterface {  
    using Counters for Counters.Counter;

    //grant id
    Counters.Counter private _grantId;

    //grant infos
    mapping(uint256 => GrantInfo) private _grantInfos; 

    //owner address
    address private _owner;

    //dao contract
    address private _daoContract;

    //grant treassures
    mapping(uint256 => uint256) private _grantTreassure;

    //address treassures
    mapping(uint256 => mapping(address => uint256)) private _addressTreassure;

    //address list
    mapping(uint256 => address[]) private _addressList;

    //treassure contract
    address private _treassureContract;     

    //chainlink contract
    address private _chainlinkContract;

    constructor() ERC721("Green Grant", "GRANT") {
        _owner = msg.sender;
    } 

    //update contracts address, only owner support
    function updateContracts(address dao, address treassure, address chainlink) public{
        require(msg.sender == _owner);

        _daoContract = dao;
        _treassureContract = treassure;
        _chainlinkContract = chainlink;

    }  

    //check up keep for chainlink
    function checkUpkeep(bytes calldata /* checkData */) external view override returns (bool upkeepNeeded, bytes memory) {
       uint total = totalSupply();
       for(uint i = 0; i < total; i++){
           uint256 grantId = tokenByIndex(i);
           GrantInfo memory  info = _grantInfos[grantId];

           if(info.endTime <= block.timestamp && !info.grantPayed){
               return (true, abi.encode(grantId));
           }
       }

       return (false, abi.encode(0));
    }

    //perform up keep for chainlink
    function performUpkeep(bytes calldata performData) external override {
        uint aucId = abi.decode(performData, (uint));

        claimGrant(aucId);
    }                

    //mint nft as a new grant
    function mint(string memory name, string memory desc, string memory git, string memory website, address token, uint256 daoId, uint endTime) public returns (uint256){
        //msg.sender must in the given dao
        require(GreenDao(_daoContract).checkInDao(daoId, msg.sender) == true, "not in dao!");

        _grantId.increment();

        uint256 newId = _grantId.current();
        //mint nft as a new grant
        _mint(msg.sender, newId);

        //set the dao id of the grant
        _grantInfos[newId].daoId = daoId;
        //set grant token to receive
        _grantInfos[newId].grantToken = token;

        //update grant infos
        updateGrant(newId, name, desc, git, website, endTime);

        return newId;
    }    

    //burn the grant
    function burn(uint256 grantId) public payable nonReentrant returns (bool) {
        require(ownerOf(grantId) == msg.sender, "only owner alowed!");

        require(_grantInfos[grantId].endTime > block.timestamp && _grantInfos[grantId].grantPayed == false, "grant ended!");

        address addr;
        address token = _grantInfos[grantId].grantToken;
        uint256 amount;
        //sent payment back to the user address
        for(uint i = 0; i < _addressList[grantId].length; i++){
            addr = _addressList[grantId][i];
            amount = _addressTreassure[grantId][addr];
            if(amount == 0){
                continue;
            }else{
                _addressTreassure[grantId][addr] = 0;
            }
            
            if(token == address(0x0)){
                payable(addr).transfer(amount);    
            }else{
                ERC20(token).transferFrom(address(this), addr, amount);
            }
        }

        //delete grant info
        delete _grantInfos[grantId];        

        //delete grant treassure
        delete _grantTreassure[grantId];

        //delete address list
        delete _addressList[grantId];

        //burn token
        _burn(grantId);

        return true;
    }       

    //update grant info
    function updateGrant(uint256 grantId, string memory name, string memory desc, string memory git, string memory website, uint endTime) public returns (bool){
        require(ownerOf(grantId) == msg.sender, "only owner alowed!");

        require(bytes(name).length > 0, "invalid grant name!");

        require(endTime >= block.timestamp + 86400, "invalid end time!");

        _grantInfos[grantId].grantName = name;

        if(bytes(desc).length > 0){
            _grantInfos[grantId].grantDesc = desc;
        }

        if(bytes(git).length > 0){
            _grantInfos[grantId].grantGitUrl = git;
        }

        if(bytes(website).length > 0){
            _grantInfos[grantId].grantWebsite = website;
        }

        _grantInfos[grantId].endTime = endTime;

        return true;
    }        

    //support the grant for the token amount
    function supportGrant(uint256 grantId, uint256 amount) public payable nonReentrant returns (bool){
        require(_grantInfos[grantId].endTime > block.timestamp && _grantInfos[grantId].grantPayed == false, "grant ended!");

        address token = _grantInfos[grantId].grantToken;

        if(token == address(0x0)){
            amount = msg.value;
        }else{
            ERC20(token).transferFrom(msg.sender, address(this), amount);
        }

        require(amount > 0, "invalid amount1");

        //add msg sender to list
        if(_addressTreassure[grantId][msg.sender] == 0){
            _addressList[grantId].push(msg.sender);
        }

        //add amount
        _grantTreassure[grantId] += amount;
        _addressTreassure[grantId][msg.sender] += amount;

        return true;
    }

    //claim the grant values to the treassure address
    function claimGrant(uint256 grantId) internal returns (bool){
        require(msg.sender == ownerOf(grantId) || msg.sender == _chainlinkContract, "only owner alowed!");
        require(_grantInfos[grantId].endTime < block.timestamp, "grant not ended!");
        require(_grantInfos[grantId].grantPayed == false, "grant already claimed!");

        _grantInfos[grantId].grantPayed = true;

        address token = _grantInfos[grantId].grantToken;
        uint256 amount = _grantTreassure[grantId];
        uint256 daoId = _grantInfos[grantId].daoId;

        if(token == address(0x0)){
            GreenTreassure(_treassureContract).addDaoTreassure{value: amount}(daoId, address(this), token, amount);
        }else{
            ERC20(token).approve(_treassureContract, amount);
            GreenTreassure(_treassureContract).addDaoTreassure(daoId, address(this), token, amount);
        }

        return true;
    }

    //get grant treassure
    function getGrantTreassure(uint256 grantId, bool onlyOwner) public view returns (uint256){
        if(onlyOwner){
            return _addressTreassure[grantId][msg.sender];
        }else{
            return _grantTreassure[grantId];
        }
    }

    //get grant total count
    function getGrantTotalCount(bool onlyOwner) public view returns(uint){
        if(onlyOwner){
            return balanceOf(msg.sender);
        }else{
            return totalSupply();
        }        
    }      

    //get grant info by id
    function getGrantInfoById(uint256 grantId) public view returns(GrantInfo memory){
        return _grantInfos[grantId];
    }

    //get grant indexs by page
    function getGrantIndexsByPageCount(uint pageSize, uint pageCount, uint256 daoId, bool onlyOwner) public view returns (uint256 []memory){
        uint total = getGrantTotalCount(onlyOwner);
        uint256[] memory indexList;
        uint count;
        uint m;
        uint256 index;

        if(pageSize > 100){
            pageSize = 100;
        }

        uint start = pageSize*pageCount;
        uint end = start+pageSize;

        uint256[] memory tmpList = new uint256[](pageSize);   

        for(uint i = 0; i < total; i++){
            if(onlyOwner){
                index = tokenOfOwnerByIndex(msg.sender, total - i - 1);
            }else{
                index = tokenByIndex(total -i - 1);
            }

            if(daoId > 0 && _grantInfos[index].daoId != daoId){
                continue;
            }else{
                count++;
            }

            if(count < start){
                continue;
            }else if (count > end){
                break;
            }else{
                tmpList[m++] = index;
            }
        }

        if(m > 0){
            indexList = new uint256[](m);
            for(uint i = 0; i < m; i++){
                indexList[i] = tmpList[i];
            }
        }

        return indexList;
    }
}