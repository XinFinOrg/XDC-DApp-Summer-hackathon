pragma solidity ^0.8.0;

import "@openzeppelin/contracts/token/ERC721/extensions/ERC721URIStorage.sol";

import "@openzeppelin/contracts/utils/Counters.sol";

contract Miner is ERC721URIStorage {
    using Counters for Counters.Counter;
    Counters.Counter private _tokenIds;
    string tokenuri = "abc";
    address public owner;
    mapping(uint256=>address) public lockedminers;
    mapping(uint256=>bool) public minerproducing;
    mapping(uint256=>uint256) public lastcollected;
    mapping(uint256=>uint256) public TownhallMiner;
    mapping(uint256=>bool) public townhallconnected;
    uint256 public maxblock = 1200;
    event Amm(uint256 amount);
    constructor() ERC721("Miner", "MNR") public {
        owner = tx.origin;
    }
    modifier onlyOwner(){
            require(msg.sender == owner);
            _;
    }

    function mint(address starter) onlyOwner external {
         _tokenIds.increment();
        require(this.balanceOf(starter)<1,"No more miners");
        uint256 newItemId = _tokenIds.current();
        _mint(starter, newItemId);
        _setTokenURI(newItemId, tokenuri);

    }

     function changeowner(address newowner) onlyOwner public{
        owner = newowner;
    }

    function lock(uint256 minerid,uint256 townhallid) onlyOwner internal{
        require(tx.origin == ownerOf(minerid),"You are not the owner");
        require(townhallconnected[townhallid] == false,"Townhall is already connected");
        _transfer(tx.origin,address(this),minerid);
        lastcollected[minerid] = block.number;
        TownhallMiner[minerid] = townhallid;
        townhallconnected[townhallid] = true;
        lockedminers[minerid] = tx.origin;
        minerproducing[minerid] = true;
    }

    function unlock(uint256 minerid,uint256 townhallid) onlyOwner internal{
        require(lockedminers[minerid] == tx.origin);
        require(townhallconnected[townhallid] == true);
        townhallconnected[townhallid] = false;
        lockedminers[minerid]= address(0);
        _transfer(address(this),tx.origin,minerid);
        minerproducing[minerid] = false;
        

    }

    function collect(uint256 id) onlyOwner public returns(uint256){
        require(minerproducing[id] == true,"the miner is not producing");
        require(lockedminers[id] == tx.origin,"The miner is not your");
        uint256 current = lastcollected[id];
        lastcollected[id] = block.number;
     
        return (block.number -current) % maxblock;

    }

    function updatelock(uint256 minerid,uint256 townhallid,uint256 amount) onlyOwner external{
        if(amount>0 && tx.origin == ownerOf(minerid) && townhallconnected[townhallid] == false){
            lock(minerid,townhallid);

            
        }
        else if(amount == 0 && townhallconnected[townhallid] == true && lockedminers[minerid] == tx.origin ){
            unlock(minerid, townhallid);
        }
    }
}
