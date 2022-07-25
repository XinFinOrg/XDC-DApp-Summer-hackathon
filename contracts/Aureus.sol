pragma solidity ^0.8.0;

import "@openzeppelin/contracts/token/ERC20/ERC20.sol";

contract Aureus is ERC20 {
       address public  owner;
      modifier onlyOwner(){
            require(msg.sender == owner);
            _;
    }

    constructor() ERC20("Aureus", "ARS") {
        owner=tx.origin;
    }
    
    function mintnew(uint256 amount,address to) onlyOwner external{
          _mint(to, amount);
    }

    function changeowner(address newowner) onlyOwner public{
        owner = newowner;
    }

    function subtract(address owner,uint256 amount) onlyOwner external{
        _burn(owner,amount);
    }

}
