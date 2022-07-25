// SPDX-License-Identifier: MIT
pragma solidity ^0.8.0;

import "@openzeppelin/contracts/token/ERC20/IERC20.sol";
import "@openzeppelin/contracts/utils/math/SafeMath.sol";
import "@openzeppelin/contracts/token/ERC721/IERC721.sol";
import "@openzeppelin/contracts/token/ERC1155/IERC1155.sol";
import "./FeeManager.sol";
// import "./MerkleFactory.sol";


contract AirdropDistributor is FeeManager {
    using SafeMath for uint256;

    function withdraw () public onlyOwner {
        payable(msg.sender).transfer(address(this).balance);
    }

    function withdraw20 (address asset) public onlyOwner {
        uint256 tokenBalance = IERC20(asset).balanceOf(address(this));
        require(IERC20(asset).transfer(msg.sender, tokenBalance), "");
    }

    function withdraw721 (address asset, uint256 tokenId) public onlyOwner {
        IERC721(asset).transferFrom(address(this), msg.sender, tokenId);
    }

    function withdraw1155 (address asset, uint256 tokenId, uint256 amount) public onlyOwner {
        IERC1155(asset).safeTransferFrom(address(this), msg.sender, tokenId, amount, "");
    }


    function sendCoinsSingleValue (address[] memory recipients, uint256 amount) public payable {
        uint256 totalAmount = recipients.length.mul(amount);
        if(isSubscribed(msg.sender)){
            require(msg.value >= totalAmount, "Insufficient amount");
        } else {
            require(msg.value >= totalAmount.add(serviceFee()), "Insufficient amount");
        }
        require(recipients.length <= 256, "Recipients array too big");
        for(uint16 i=0 ; i<recipients.length ; i++){
            // solhint-disable-next-line
            require(payable(recipients[i]).send(amount), "");
        }
    }

    function sum (uint256[] memory arr) public pure returns (uint) {
        uint256 ans = 0;
        for(uint i=0 ; i<arr.length ; i++)
            ans = ans.add(arr[i]);
        return ans;
    }

    function sendCoinsManyValues (address[] memory recipients, uint256[] memory amounts) public payable {
        uint256 totalAmount = sum(amounts);
        require (recipients.length == amounts.length, "invalid Arguments");
        if(isSubscribed(msg.sender)){
            require(msg.value >= totalAmount, "Insufficient amount");
        } else {
            require(msg.value >= totalAmount.add(serviceFee()), "Insufficient amount");
        }
        require(recipients.length <= 256, "Recipients array too big");
        for(uint16 i=0 ; i<recipients.length ; i++){
            // solhint-disable-next-line
            require(payable(recipients[i]).send(amounts[i]), "");
        }
    }

    function sendTokensSingleValue (address[] memory recipients, uint256 amount, address asset) public payable {
        uint256 totalAmount = recipients.length.mul(amount);

        require(
            IERC20(asset).allowance(msg.sender, address(this)) >= 
            totalAmount, 
            "Insufficient allowance"
        );
        require(
            IERC20(asset).balanceOf(msg.sender) >= totalAmount,
            "Insufficient balance"
        );
        require(isSubscribed(msg.sender) || msg.value >= serviceFee(), "Insufficient fees");
        require(recipients.length <= 256, "Recipients array too big");

        for(uint16 i=0 ; i<recipients.length ; i++){
            require(IERC20(asset).transferFrom(msg.sender, recipients[i], amount), "");
        }
    }

    function sendTokensManyValues (address[] memory recipients, uint256[] memory amounts, address asset) public payable {
        uint256 totalAmount = sum(amounts);

        require(IERC20(asset).allowance(msg.sender, address(this)) >= totalAmount, "Insufficient allowance");
        require(IERC20(asset).balanceOf(msg.sender) >= totalAmount, "Insufficient balance");
        require(isSubscribed(msg.sender) || msg.value >= serviceFee(), "Insufficient fees");
        require(recipients.length <= 256, "Recipients array too big");

        for(uint16 i=0 ; i<recipients.length ; i++){
            require(IERC20(asset).transferFrom(msg.sender, recipients[i], amounts[i]), "");
        }
    }

    function sendERC721 (address[] memory recipients, uint256[] memory tokenIds, address asset) public payable {
        IERC721 token = IERC721(asset);
        
        require(token.isApprovedForAll(msg.sender, address(this)), "");
        require(isSubscribed(msg.sender) || msg.value >= serviceFee(), "Insufficient fees");
        require(recipients.length <= 256, "Recipients array too big");


        for(uint16 i=0 ; i<recipients.length ; i++){
            token.transferFrom(msg.sender, recipients[i], tokenIds[i]);
        }
    }

    function sendERC1155 (address[] memory recipients, uint256[] memory tokenIds, uint256[] memory amounts,  address asset) public payable {
        IERC1155 token = IERC1155(asset);
        
        require(token.isApprovedForAll(msg.sender, address(this)), "");
        require(isSubscribed(msg.sender) || msg.value >= serviceFee(), "Insufficient fees");
        require(recipients.length <= 256, "Recipients array too big");

        for(uint16 i=0 ; i<recipients.length ; i++){
            token.safeTransferFrom(msg.sender, recipients[i], tokenIds[i], amounts[i], "");
        }
    }

    function sendERC1155SameAmount (address[] memory recipients, uint256[] memory tokenIds, uint256 amount,  address asset) public payable {
        IERC1155 token = IERC1155(asset);
        
        require(token.isApprovedForAll(msg.sender, address(this)), "");
        require(isSubscribed(msg.sender) || msg.value >= serviceFee(), "Insufficient fees");
        require(recipients.length <= 256, "Recipients array too big");

        for(uint16 i=0 ; i<recipients.length ; i++){
            token.safeTransferFrom(msg.sender, recipients[i], tokenIds[i], amount, "");
        }
    }

}
