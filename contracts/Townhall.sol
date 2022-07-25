pragma solidity ^0.8.0;

import "@openzeppelin/contracts/token/ERC721/extensions/ERC721URIStorage.sol";
import "@openzeppelin/contracts/utils/Counters.sol";

contract Townhall is ERC721URIStorage {
    using Counters for Counters.Counter;
    Counters.Counter private _tokenIds;
    string tokenuri = "abc";
    address public owner;
    constructor() ERC721("TownHall", "TWH") public {
        owner = tx.origin;
    }
    modifier onlyOwner(){
            require(msg.sender == owner);
            _;
    }
    function startgame(address starter) onlyOwner public  {
        _tokenIds.increment();

        uint256 newItemId = _tokenIds.current();
        _mint(starter, newItemId);
        _setTokenURI(newItemId, tokenuri);

        
    }
     function changeowner(address newowner) onlyOwner public{
        owner = newowner;
    }
}
