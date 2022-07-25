//SPDX-License-Identifier: MIT
pragma solidity ^0.8.7;

import "@openzeppelin/contracts/token/ERC721/extensions/ERC721Enumerable.sol";
import "@openzeppelin/contracts/token/ERC721/extensions/ERC721URIStorage.sol";
import "@openzeppelin/contracts/token/ERC20/ERC20.sol";
import "@openzeppelin/contracts/utils/Counters.sol";

enum LearningType {
    IMAGE,
    AUDIO,
    VIDEO,
    WEBSITE
}

struct LearningInfo {
    string learningName; //learning name
    string learningDesc; //learning description
    string learningUrl; //learning url link
    LearningType learningType; //learning type
    uint256 daoId; //dao id
    uint learningLikes; //learning likes
    uint learningHates; //learning hates
}

//green dao interface
interface GreenDao {
    function checkInDao(uint256 daoId, address user) external view returns (bool);
}

contract GreenLearning is ERC721Enumerable, ERC721URIStorage {   

    using Counters for Counters.Counter;

    //learning id
    Counters.Counter private _learningId;

    //learning info
    mapping(uint256 => LearningInfo) private _learningInfos;  

    //learning likes
    mapping(uint256 => mapping(address => bool)) private _learningLikes;

    //learning hates
    mapping(uint256 => mapping(address => bool)) private _learningHates;

    //owner
    address private _owner;

    //dao contract address
    address private _daoContract;

    constructor() ERC721("Green Learning", "GRLearn") {
        _owner = msg.sender;
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

    //update contracts address, only owner support
    function updateContracts(address dao) public {
        require(msg.sender == _owner);

        _daoContract = dao;
    }        

    //mint nft as a new learning
    function mint(string memory name, string memory desc, string memory url, uint256 daoId, LearningType learningType) public returns (uint256) {
        //msg.sender must in the given dao
        require(GreenDao(_daoContract).checkInDao(daoId, msg.sender) == true, "not in dao!");

        _learningId.increment();
        uint256 newId = _learningId.current();
        //mint nft as a new learning
        _mint(msg.sender, newId);
    
        //update learning info
        _learningInfos[newId] = LearningInfo({
            learningName: name,
            learningDesc: desc,
            learningUrl: url,
            learningType: learningType,
            daoId: daoId,
            learningLikes: 0,
            learningHates: 0
        });
        
        //set token uri
        _setTokenURI(newId, url);

        //return new id
        return newId;
    }

    //burn the learning
    function burn(uint256 learningId) public returns (bool) {
        require(ownerOf(learningId) == msg.sender, "Only owner alowed!");

        //delete learning info
        delete _learningInfos[learningId];
        //burn token
        _burn(learningId);

        return true;
    }

    //like the learning
    function likeTheLearning(uint256 learningId) public returns (bool){
        if(_learningLikes[learningId][msg.sender]){
            return true;
        }

        if(_learningHates[learningId][msg.sender]){
            _learningHates[learningId][msg.sender] = false;
            _learningInfos[learningId].learningHates -= 1;
        }

        _learningLikes[learningId][msg.sender] = true;
        _learningInfos[learningId].learningLikes += 1;

        return true;
    }

    //hate the learning
    function hateTheLearning(uint256 learningId) public returns (bool){
        if(_learningHates[learningId][msg.sender]){
            return true;
        }

        if(_learningLikes[learningId][msg.sender]){
            _learningLikes[learningId][msg.sender] = false;
            _learningInfos[learningId].learningLikes -= 1;
        }

        _learningHates[learningId][msg.sender] = true;
        _learningInfos[learningId].learningHates += 1;

        return true;
    }

    //get learning total count
    function getLearningTotalCount(bool onlyOwner) public view returns(uint){
        if(onlyOwner){
            return balanceOf(msg.sender);
        }else{
            return totalSupply();
        }        
    }    

    //get learning info by id
    function getLearningInfoById(uint256 learningId) public view returns (LearningInfo memory){
        return _learningInfos[learningId];
    }

    //get learning indexs by page 
    function getLearningIndexsByPageCount(uint pageSize, uint pageCount, uint256 daoId, bool onlyOwner) public view returns(uint256[] memory){
        uint total = getLearningTotalCount(onlyOwner);
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

            if(daoId > 0 && _learningInfos[index].daoId != daoId){
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