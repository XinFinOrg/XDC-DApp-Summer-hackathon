//SPDX-License-Identifier: MIT
pragma solidity ^0.8.7;

contract GreenChat {
    //contract owner
    address private _owner;

    //map for address fluence peer id
    mapping(address => string) private _peerIds;

    //map for address chat history ipfs link
    mapping(address => mapping(address => string)) private _chatHistory;

    //address list
    address[] _addressLists;

    constructor() {
        _owner = msg.sender;
    }

    //update address peer id
    function updatePeerId(string memory peerId) public {
        require(bytes(peerId).length > 0, "invalid peer id!");

        if(bytes(_peerIds[msg.sender]).length == 0){
            _addressLists.push(msg.sender);
        }

        _peerIds[msg.sender] = peerId;
    } 

    //update chat history link
    function updateChatHistory(address to, string memory link) public {
        _chatHistory[msg.sender][to] = link;
    }

    //get address peer id
    function getPeerId(address to) public view returns (string memory) {
        return _peerIds[to];
    }

    //get address and peer id list for page size and page count
    function getPeerList(uint pageSize, uint pageCount) public view returns (address[] memory, string[] memory) {
        address[] memory addressList;
        string[] memory peersList;

        if(pageSize == 0){
            return (addressList, peersList);
        }

        if(pageSize > 100) {
            pageSize = 100;
        }

        uint start = pageSize*pageCount;
        uint end = start + pageSize;

        if(start > _addressLists.length){
            return (addressList, peersList);
        }

        if(end > _addressLists.length){
            end = _addressLists.length;
        }

        uint count;
        address[] memory tmp1 = new address[](pageSize);
        string[] memory tmp2 = new string[](pageSize);

        for(uint i = start; i < end; i++){
            tmp1[count] = _addressLists[i];
            tmp2[count] = _peerIds[_addressLists[i]];
            count += 1;
        }

        if(tmp1.length > 0){
            addressList = new address[](count);
            peersList = new string[](count);

            for(uint i = 0; i < count; i++){
                addressList[i] = tmp1[i];
                peersList[i] = tmp2[i];
            }
        }

        return (addressList, peersList);
    }

    //get chat history link
    function getChatHistory(address to) public view returns (string memory) {
        return _chatHistory[msg.sender][to];
    }
}