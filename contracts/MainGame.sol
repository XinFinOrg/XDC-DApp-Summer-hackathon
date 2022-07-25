pragma solidity ^0.8.0;

pragma experimental ABIEncoderV2;

interface IERC1155  {

 function addAsset(string memory assetname,uint256 price)  external returns (uint256);
 function mint(uint256 id,uint256 amount) external;
 function updatelock(uint256 assetid,uint256 amount) external;

}   


interface IERC20 {
  
    function balanceOf(address account) external view returns (uint256);
    function subtract(address owner,uint256 amount)  external;
     function mintnew(uint256 amount,address to)  external;
    }


interface ERC721 /* is ERC165 */ {
    function mint(address starter) external;
    function lock(uint256 minerid,uint256 townhallid)  external;
    function unlock(uint256 minerid,uint256 townhallid)  external;
    function updatelock(uint256 minerid,uint256 townhallid,uint256 amount)  external;
    function collect(uint256 id) external returns(uint256);

}


contract MainGame {

    address public owner;
    address public maincurrency;
    struct Asset{
        string Name;
        uint256 id;
        address contractadd;
        uint256 price;
        bool tokentype ;
    }
    struct User{
        bool gamestarted;
        uint256 townhallid;
    }
    mapping(address => User) public townhalluser;
    uint256 currentcount = 0;
    uint256 townhallcount = 0;
    mapping(uint256 => Asset) public assets;
  
        modifier onlyOwner(){
            require(msg.sender == owner);
            _;
    }
    event addERC721(uint256 id,address contractid,uint256 price,string name,bool typee);
    event addERC1155(uint256 id,address contractid,uint256 price,string name,bool typee,uint256 nftid);
    event startGamev(uint256 townhallid,address own);

    constructor(){
        owner = tx.origin;
    }

    function lockBase(uint256[] memory amount,uint256 minerid) public {
        for(uint i=0;i<amount.length;i++){
            if(assets[i].tokentype == false){
                lockERC721(minerid, i,amount[i]);
            }else{
                lockERC1155(i, amount[i]);
            }
        }
    }

    function addAsset721(string memory name,address contractadd,uint256 price) public onlyOwner {

             Asset memory asset = Asset(name,0,contractadd,price,false);
             assets[currentcount] = asset;
            emit addERC721(currentcount++, contractadd, price, name,false);

    }

    function addAsset1155(string memory name,address contractadd,uint256 price) public onlyOwner{
            
             uint256 id = IERC1155(contractadd).addAsset(name,price);
            Asset memory asset = Asset(name,id,contractadd,price,true);
             assets[currentcount] = asset;
           emit addERC1155(currentcount++, contractadd, price, name, true,id);
    }

    function assetdetails(uint256 id) public view returns (Asset memory) {
        return assets[id];
    }

    function startgame() public {
        require(townhalluser[tx.origin].gamestarted == false);
        if(tx.origin == owner){
            IERC20(maincurrency).mintnew(5000, tx.origin);
        
        }
        townhalluser[tx.origin] = User(true,townhallcount);
        IERC20(maincurrency).mintnew(1000, tx.origin);
        emit startGamev(townhallcount++,tx.origin);
    }

    function endwar(uint256 building) public {
        IERC20(maincurrency).mintnew(building*20, tx.origin);
    }

    function lockERC721(uint256 minerid,uint256 assetid,uint256 amountid) public {
        address contractadd = assets[assetid].contractadd;
     
        ERC721(contractadd).updatelock(minerid,townhalluser[tx.origin].townhallid,amountid);

    }


    function lockERC1155(uint256 assetid,uint256 amount) public {
        address contractadd = assets[assetid].contractadd;
        IERC1155(contractadd).updatelock(assets[assetid].id,amount);
    }
    

    function unlockERC721(uint256 minerid,uint256 assetid) public {
        address contractadd = assets[assetid].contractadd;
        ERC721(contractadd).updatelock(minerid,townhalluser[tx.origin].townhallid,0);

    }
    
    function mintERC721(uint256 assetid) payable public {
        require(msg.value>=assets[assetid].price);
        
        address contractadd = assets[assetid].contractadd;
        ERC721(contractadd).mint(tx.origin);
    }

    function collect(uint256 minerid) public {
        uint256 amount = ERC721(assets[0].contractadd).collect(minerid);
        IERC20(maincurrency).mintnew(amount, tx.origin);

    }

    function mintERC1155(uint256 assetid) public{
        
        require(IERC20(maincurrency).balanceOf(tx.origin) >= assets[assetid].price);
        IERC20(maincurrency).subtract(tx.origin, assets[assetid].price);
        IERC1155(assets[assetid].contractadd).mint(assets[assetid].id,1);
    }


    function setmaincurrency(address scadd) public onlyOwner {
        maincurrency = scadd;
    }



    function withdraw() public payable onlyOwner {
         address payable  own = payable(owner);
       own.transfer(address(this).balance); 
    }

}