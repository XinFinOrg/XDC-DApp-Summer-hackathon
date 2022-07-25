pragma solidity >=0.4.22 <0.9.0;


import "@openzeppelin/contracts/token/ERC1155/ERC1155.sol";


contract Assets is ERC1155 {
    uint256 public currentcount = 0;
    struct Asset{
        string Name;
        uint256 price;
        uint256 id;
    }
    mapping (uint256 => Asset) Assets;
    
    address public owner;
    struct Locked{
       mapping(uint256=>uint256) tokenledger;

    }
    mapping(address=>Locked) totalrecord;
    modifier onlyOwner(){
            require(msg.sender == owner);
            
            _;
    }
    event unLock(address from,address to,uint256 id,uint256 value);
    constructor() public ERC1155("https://game.example/api/item/{id}.json") {
             owner = tx.origin;
    }

    function addAsset(string memory assetname,uint256 price) onlyOwner external returns (uint256)  {
        Asset memory asset = Asset(assetname,price,currentcount);
        Assets[currentcount] = asset;
        return currentcount++;
    }

    function mint(uint256 id,uint256 amount) external onlyOwner {
        
        _mint(tx.origin,id,amount,"");

    }

    function withdraw() public payable onlyOwner {
         address payable  own = payable(owner);
       own.transfer(address(this).balance); 
    }
      function changeowner(address newowner) onlyOwner public{
        owner = newowner;
    }

    function lock(uint256 assetid,uint256 amount) public onlyOwner{
        require(balanceOf(tx.origin,assetid) >= amount);
        _burn(tx.origin,assetid,amount);
        totalrecord[tx.origin].tokenledger[assetid]+=amount;

    }

    function unlock(uint256 assetid,uint256 amount) public onlyOwner{
        require(totalrecord[tx.origin].tokenledger[assetid] >= amount);
        totalrecord[tx.origin].tokenledger[assetid]-=amount;
        emit unLock(address(this), tx.origin, assetid, amount);
        _mint(tx.origin,assetid,amount,"");

    }

    function updatelock(uint256 assetid,uint256 amount) external onlyOwner{
        uint256 currentlock = totalrecord[tx.origin].tokenledger[assetid];
        if(amount >currentlock ){
            lock(assetid,amount-currentlock);
        }
        else if(amount < currentlock) {
            unlock(assetid, currentlock-amount);
        }

    }


}