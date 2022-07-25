//SPDX-License-Identifier: MIT
pragma solidity ^0.8.7;

import "@openzeppelin/contracts/security/ReentrancyGuard.sol";
import "@openzeppelin/contracts/token/ERC721/extensions/ERC721Enumerable.sol";
import "@openzeppelin/contracts/token/ERC20/ERC20.sol";
import "@openzeppelin/contracts/utils/Counters.sol";

//vote status for user
enum VoteStatus {
    None, //not vote
    Aggree, //vote aggree
    Against //vote against
}

//vote info
struct VoteInfo {
    string voteName; //vote name
    string voteDesc; //vote detail
    uint256 daoId; //dao id for the vote
    uint256 voteValue; //vote value if apply for payment
    address voteToken; //vote token contract address if apply for payment
    address voteTo; //address to transfer if apply for payment
    uint voteAggree; //vote aggree count
    uint voteAgainst; //vote against count
    uint endTime; //vote end time
    bool voteSuccess; //vote success or failed
    bool votePayed; //vote payed or not
}

//green dao interface
interface GreenDao {
    function checkInDao(uint256 daoId, address user) external view returns (bool);
    function getDaoMembers(uint256 daoId) external view returns (uint);
}

//green vote contract
contract GreenVote is ERC721Enumerable, ReentrancyGuard {  
    using Counters for Counters.Counter;

    //vote id
    Counters.Counter private _voteId;

    //vote infos
    mapping(uint256 => VoteInfo) private _voteInfos;

    //contract owner
    address private _owner;

    //dao contract
    address private _daoContract;

    //dao treassures
    mapping(uint256 => mapping(address => uint256)) private _daoTreassures;

    //vote status
    mapping(uint256 => mapping(address => VoteStatus)) private _voteStatus;

    //init owner address and dao address
    constructor() ERC721("Green Vote", "GRVote") {
        _owner = msg.sender;
    } 

    //events for receive and send tokens
    event receiveTreassure(uint256 daoId, address from, address token, uint256 amount);
    event sendTreassure(uint256 daoId, uint256 voteId, address to, address token, uint256 amount);

    //send dao treassure when vote success
    function _sendDaoTreassure(uint256 daoId, uint256 voteId, address to, address token, uint256 amount) internal returns (bool){
        require(_voteInfos[voteId].voteSuccess == true, "vote not success!");
        require(_daoTreassures[daoId][token] > amount, "invalid amount!");        

        _daoTreassures[daoId][token] -= amount;

        if(token == address(0x0)){
            payable(to).transfer(amount);
        }else{
            ERC20(token).transferFrom(address(this), to, amount);
        }

        emit sendTreassure(daoId, voteId, to, token, amount);

        return true;
    }

    //update contracts address, only owner support
    function updateContracts(address dao) public {
        require(msg.sender == _owner);

        _daoContract = dao;
    }

    //add dao treassure
    function addDaoTreassure(uint256 daoId, address from, address token, uint256 amount) public payable nonReentrant returns (bool){
        if(token == address(0x0)){
            require(msg.value > 0, "invalid amount!");
            amount = msg.value;
        }else{
            require(amount > 0, "invalid amount!");
            ERC20(token).transferFrom(msg.sender, address(this), amount);
        }

        _daoTreassures[daoId][token] += amount;
        
        emit receiveTreassure(daoId, from, token, amount);

        return true;
    }

    //mint a nft as a new vote
    function mint(string memory name, string memory desc, uint256 daoId, uint256 value, address token, address to, uint endTime) public returns (uint256){
        //msg.sender must in the given dao
        require(GreenDao(_daoContract).checkInDao(daoId, msg.sender) == true, "not in dao!");

        _voteId.increment();

        uint256 newId = _voteId.current();

        //mint a nft as a new vote
        _mint(msg.sender, newId);

        //set the dao id of the vote
        _voteInfos[newId].daoId = daoId;
        //set vote value for transfer after success
        _voteInfos[newId].voteValue = value;
        //set vote token contract for transfer after success
        _voteInfos[newId].voteToken = token;
        //set value transfer to address
        _voteInfos[newId].voteTo = to;

        //update vote info
        updateVote(newId, name, desc, endTime);

        return newId;
    }    

    //burn the vote
    function burn(uint256 voteId) public returns (bool) {
        require(ownerOf(voteId) == msg.sender, "only owner alowed!");

        require(_voteInfos[voteId].endTime > block.timestamp && _voteInfos[voteId].votePayed == false, "vote ended!");

        //delete grant info
        delete _voteInfos[voteId];
        //burn token
        _burn(voteId);

        return true;
    }           

    //update vote infos
    function updateVote(uint256 voteId, string memory name, string memory desc, uint endTime) public returns (bool){
        require(ownerOf(voteId) == msg.sender, "only owner alowed!");

        require(bytes(name).length > 0, "invalid vote name!");

        require(endTime >= block.timestamp + 86400, "invalid end time!");

        _voteInfos[voteId].voteName = name;

        if(bytes(desc).length > 0){
            _voteInfos[voteId].voteDesc = desc;
        }

        _voteInfos[voteId].endTime = endTime;

        return true;
    }

    //vote for the dao members
    function vote(uint256 voteId, VoteStatus status) public nonReentrant returns (bool){
        require(_voteInfos[voteId].endTime >= block.timestamp && _voteInfos[voteId].votePayed == false, "vote ended!");

        uint256 daoId = _voteInfos[voteId].daoId;
        //msg.sender must in the given dao
        require(GreenDao(_daoContract).checkInDao(daoId, msg.sender) == true, "not in dao!");

        VoteStatus old = _voteStatus[voteId][msg.sender];

        //set new status
        _voteStatus[voteId][msg.sender] = status;

        //update aggree and against count
        if(old == VoteStatus.Aggree){
            if(status == VoteStatus.Aggree){
                return true;
            }else if (status == VoteStatus.Against){
                _voteInfos[voteId].voteAggree -= 1;
                _voteInfos[voteId].voteAgainst += 1;
            }else{
                _voteInfos[voteId].voteAggree -=1;
            }
        }else if(old == VoteStatus.Against){
            if(status == VoteStatus.Aggree){
                _voteInfos[voteId].voteAggree += 1;
                _voteInfos[voteId].voteAgainst -= 1;
            }else if (status == VoteStatus.Against){
                return true;
            }else{
                _voteInfos[voteId].voteAgainst -= 1;
            }
        }else{
            //default None
            if(status == VoteStatus.Aggree){
                _voteInfos[voteId].voteAggree += 1;
            }else if (status == VoteStatus.Against){
                _voteInfos[voteId].voteAgainst += 1;
            }else{
                return true;
            }            
        }

        //get total member for dao
        uint total = GreenDao(_daoContract).getDaoMembers(daoId);

        //failed if against > 40% of total member
        if(_voteInfos[voteId].voteAgainst > total * 4 / 10){
            _voteInfos[voteId].voteSuccess = false;
            return true;
        }

        //success if aggree > 50% of total member
        if(_voteInfos[voteId].voteAggree > total / 2){
            uint256 amount = _voteInfos[voteId].voteValue;
            _voteInfos[voteId].votePayed = true;
            _voteInfos[voteId].voteSuccess = true;
            if(amount > 0){
                _sendDaoTreassure(daoId, voteId, _voteInfos[voteId].voteTo, _voteInfos[voteId].voteToken, amount);
            }
        }

        return true;
    }

    //get dao treassure
    function getDaoTreassure(uint256 daoId, address token) public view returns (uint256){
        return _daoTreassures[daoId][token];
    }

    //get vote total count
    function getVoteTotalCount(bool onlyOwner) public view returns(uint){
        if(onlyOwner){
            return balanceOf(msg.sender);
        }else{
            return totalSupply();
        }        
    }      

    //get vote info by id
    function getVoteInfoById(uint256 voteId) public view returns (VoteInfo memory){
        return _voteInfos[voteId];
    }

    //get vote indexs by page
    function getVoteIndexsByPageCount(uint pageSize, uint pageCount, uint256 daoId, bool onlyOwner) public view returns(uint256[] memory){
        uint total = getVoteTotalCount(onlyOwner);
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

            if(daoId > 0 && _voteInfos[index].daoId != daoId){
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