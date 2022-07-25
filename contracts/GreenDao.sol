//SPDX-License-Identifier: MIT
pragma solidity ^0.8.7;

import "@openzeppelin/contracts/token/ERC721/extensions/ERC721Enumerable.sol";
import "@openzeppelin/contracts/token/ERC721/extensions/ERC721URIStorage.sol";
import "@openzeppelin/contracts/token/ERC20/ERC20.sol";
import "@openzeppelin/contracts/utils/Counters.sol";

struct DaoInfo {
    string daoName; //dao name
    string daoDesc; //dao description
    string daoWebsite; //dao website url link
    uint daoMember; //dao members
    bool daoPublic; //dao public or not
}

contract GreenDao is ERC721Enumerable, ERC721URIStorage {   

    using Counters for Counters.Counter;

    //dao id
    Counters.Counter private _daoId;

    //dao info
    mapping(uint256 => DaoInfo) private _daoInfos;

    //dao name
    mapping(string => bool) private _daoNames;    

    //dao member status
    mapping(uint256 => mapping( address => bool)) private _daoMemberStatus;

    constructor() ERC721("Green DAO", "GRDAO") {
    } 

    function _beforeTokenTransfer(address from, address to, uint256 tokenId) internal override (ERC721, ERC721Enumerable) {
        super._beforeTokenTransfer(from, to, tokenId);
    }

    function _burn(uint256 tokenId) internal override (ERC721, ERC721URIStorage) {
        super._burn(tokenId);
    }  

    function supportsInterface(bytes4 interfaceId) public view override (ERC721, ERC721Enumerable) returns (bool) {
        return super.supportsInterface(interfaceId);
    }    

    function tokenURI(uint256 tokenId) public view override (ERC721, ERC721URIStorage) returns (string memory) {
        return super.tokenURI(tokenId);
    }                 

    //mint nft as a new dao
    function mint(string memory name, string memory desc, string memory website, string memory url, bool isPublic) public returns (uint256) {
        require(_daoNames[name] == false, "name exists!");

        _daoId.increment();
        uint256 newId = _daoId.current();
        //mint nft as a new dao
        _mint(msg.sender, newId);
        //update dao member status
        _daoMemberStatus[newId][msg.sender] = true;
        _daoInfos[newId].daoMember = 1;
        _daoInfos[newId].daoPublic = isPublic;
        //update dao infos
        updateDao(newId, name, desc, website, url);
        //return new id
        return newId;
    }

    //burn the dao
    function burn(uint256 daoId) public returns (bool) {
        require(ownerOf(daoId) == msg.sender, "Only owner alowed!");
        //delete dao name
        delete _daoNames[_daoInfos[daoId].daoName];
        //delete dao info
        delete _daoInfos[daoId];
        //burn token
        _burn(daoId);

        return true;
    }

    //set dao to public or private
    function setDaoPublic(uint256 daoId, bool isPublic) public returns (bool){
        require(ownerOf(daoId) == msg.sender, "Only owner alowed!");

        _daoInfos[daoId].daoPublic = isPublic;
        return true;
    }

    //update dao infos
    function updateDao(uint256 daoId, string memory name, string memory desc, string memory website, string memory url) public returns (bool){
        require(ownerOf(daoId) == msg.sender, "Only owner alowed!");

        require(bytes(name).length > 0, "Invalid Dao Name!");

        //change dao name
        if(_daoNames[name] == false){
            delete _daoNames[_daoInfos[daoId].daoName];
            _daoNames[name] = true;
            _daoInfos[daoId].daoName = name;
        }

        if(bytes(desc).length > 0){
            _daoInfos[daoId].daoDesc = desc;
        }

        if(bytes(website).length > 0){
            _daoInfos[daoId].daoWebsite = website;
        } 

        if(bytes(url).length > 0){
            _setTokenURI(daoId, url);
        }

        return true;
    }

    //join a dao, every user can join a public dao, but for private dao only owner can add members
    function joinDao(uint256 daoId, address user) public returns (bool){
       if(_daoInfos[daoId].daoPublic){
           user = msg.sender;
       }else{
           require(ownerOf(daoId) == msg.sender, "Only owner alowed!");
       }

        if(_daoMemberStatus[daoId][user] == false){
            _daoMemberStatus[daoId][user] = true;
            _daoInfos[daoId].daoMember += 1;
        }
        return true;
    }

    //leave dao
    function leaveDao(uint256 daoId, address user) public returns (bool){
        if(_daoInfos[daoId].daoPublic){
            user = msg.sender;
        }else{
            require(ownerOf(daoId) == msg.sender, "Only owner alowed!");
        }

        require(ownerOf(daoId) != user, "Owner can't leave!");

        if(_daoMemberStatus[daoId][user] == true){
            _daoMemberStatus[daoId][user] = false;
            _daoInfos[daoId].daoMember -= 1;
        }
        return true;
    }

    //check a user is in dao or not
    function checkInDao(uint256 daoId, address user) public view returns (bool){
        return _daoMemberStatus[daoId][user];
    }   

    //get dao members count
    function getDaoMembers(uint256 daoId) public view returns (uint){
        return _daoInfos[daoId].daoMember;
    } 

    //get dao total count
    function getDaoTotalCount(bool onlyOwner) public view returns(uint){
        if(onlyOwner){
            return balanceOf(msg.sender);
        }else{
            return totalSupply();
        }        
    }    

    //get dao info by id
    function getDaoInfoById(uint256 daoId) public view returns (string memory, string memory, string memory, string memory, address, bool, uint){
        DaoInfo memory d = _daoInfos[daoId];

        return (d.daoName, d.daoDesc, d.daoWebsite, tokenURI(daoId), ownerOf(daoId), d.daoPublic, d.daoMember);
    }

    //get dao indexs by page 
    function getDaoIndexsByPageCount(uint pageSize, uint pageCount, bool onlyOwner) public view returns(uint256[] memory){
        uint total = getDaoTotalCount(onlyOwner);
        uint256[] memory indexList;
        uint count;

        if(pageSize > 100){
            pageSize = 100;
        }

        uint start = pageSize*pageCount;
        uint end = start+pageSize;

        uint256[] memory tmpList = new uint256[](pageSize);

        for(uint i = start; i < total; i++){
            if(i >= end){
                break;
            }

            if(onlyOwner){
                tmpList[count++] = tokenOfOwnerByIndex(msg.sender, i);
            }else{
                tmpList[count++] = tokenByIndex(i);
            }
        }

        if(count > 0){
            indexList = new uint256[](count);
            for(uint i = 0; i < count; i++){
                indexList[i] = tmpList[i];
            }
        }

        return indexList;
    }
}