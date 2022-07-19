//SPDX-License-Identifier: MIT
pragma solidity ^0.8.7;

import { GreenDao } from "./GreenDao.sol";
import { GreenVote } from "./GreenVote.sol";
import { GreenAuction } from "./GreenAuction.sol";
import { GreenGrant } from "./GreenGrant.sol";
import { GreenLearning } from "./GreenLearning.sol";

contract GreenFactory {
    address private _owner;
    address private _greenDao;
    address private _greenVote;
    address private _greenAuction;
    address private _greenGrant;
    address private _greenLearning;

    constructor (){
        _owner = msg.sender;

        _greenDao = address(new GreenDao());
        _greenVote = address(new GreenVote());
        _greenAuction = address(new GreenAuction());
        _greenGrant = address(new GreenGrant());
        _greenLearning = address(new GreenLearning());
    }

    function updateContracts(address chainlink) public {
        require(msg.sender == _owner);

        GreenVote(_greenVote).updateContracts(_greenDao);
        GreenLearning(_greenLearning).updateContracts(_greenDao);
        GreenAuction(_greenAuction).updateContracts(_greenDao, _greenVote, chainlink);
        GreenGrant(_greenGrant).updateContracts(_greenDao, _greenVote, chainlink);
    
    }

    function getFactoryInfo() public view returns(address,address,address,address,address,address){
        return (_owner, _greenDao, _greenVote, _greenAuction, _greenGrant, _greenLearning);
    }
}