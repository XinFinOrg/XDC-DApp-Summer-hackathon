// SPDX-License-Identifier: MIT
pragma solidity ^0.8.4;

contract Keysafe {
    // The keyword "public" makes variables
    // accessible from other contracts
    address public minter;
    mapping (address => uint) public balances;
    // Initial: 0; 
    struct Node {
      address nodeAddress;
      string nodePubKey;
    }

    struct User{
      address userAddress;
      string userID;
      uint condition1Type;
      address nodeAddress1;
      uint condition2Type;
      address nodeAddress2;
      uint condition3Type;
      address nodeAddress3;
    }

    struct RecoverHistory{
      address userAddress;
      string userId;
      uint status;
      uint serialNumber;
      string recoverProof1;
      uint nodeConfirm1;
      string recoverProof2;
      uint nodeConfirm2;
      string recoverProof3;
      uint nodeConfirm3;
    }

    struct SignHistory{
      address userAddress;
      string userId;
      uint status;
      uint serialNumber;
      string signProof1;
      uint nodeConfirm1;
      string signProof2;
      uint nodeConfirm2;
      string signProof3;
      uint nodeConfirm3;
    }

    Node[] nodeList;
    mapping(string => User) public userList;
    mapping(string => RecoverHistory) public recoverHistoryList;
    mapping(string => SignHistory) public signHistoryList;

    // Events allow clients to react to specific
    // contract changes you declare
    event Sent(address from, address to, uint amount);

    // Constructor code is only run when the contract
    // is created
    constructor() {
      minter = msg.sender;
    }

    // Sends an amount of newly created coins to an address
    // Can only be called by the contract creator
    function mint(address receiver, uint amount) public {
      require(msg.sender == minter);
      balances[receiver] += amount;
    }

    // Errors allow you to provide information about
    // why an operation failed. They are returned
    // to the caller of the function.
    error InsufficientBalance(uint requested, uint available);

    // Sends an amount of existing coins
    // from any caller to an address
    function send(address receiver, uint amount) public {
      if (amount > balances[msg.sender])
        revert InsufficientBalance({
          requested: amount,
            available: balances[msg.sender]
        });

      balances[msg.sender] -= amount;
      balances[receiver] += amount;
      emit Sent(msg.sender, receiver, amount);
    }
    
    function registerNode(string memory _nodePubKey) public {
      uint len = nodeList.length;
      for (uint i; i < len; i++) {
        if (nodeList[i].nodeAddress == msg.sender) {
          nodeList[i].nodePubKey = _nodePubKey;
          return;
        }
      }
      nodeList.push( Node(msg.sender, _nodePubKey));
    }

    function getNodes() public view returns(Node[] memory nodes) {
      return nodeList;
    }

    function registerUser(string memory userId, uint condition1Type, address nodeAddress1, 
      uint condition2Type, address nodeAddress2, uint condition3Type,
      address nodeAddress3) public {
      
      if(userList[userId].userAddress == address(0)) {

        if(balances[msg.sender] < 6) return;
        send(minter, 3);
        send(nodeAddress1, 1);
        send(nodeAddress2, 1);
        send(nodeAddress3, 1);
      }
      //if(userList[userId].userAddress != address(0)) return;

      userList[userId] = User(msg.sender, userId, condition1Type,
        nodeAddress1, condition2Type, nodeAddress2, condition3Type,
        nodeAddress3);
      
      recoverHistoryList[userId] = RecoverHistory(msg.sender, userId, 0, 0, "", 0,
        "", 0, "", 0);

      signHistoryList[userId] = SignHistory(msg.sender, userId, 0, 0, "", 0,
        "", 0, "", 0);


    }

    function recoverRequest(string memory userId) public {
      if(userList[userId].userAddress == address(0)) return;
      recoverHistoryList[userId].status = 1;
    }

    function recoverDone(string memory userId, 
      string memory recoverProof) public {

      if(balances[userList[userId].userAddress] < 3) return;
      if(userList[userId].userAddress == address(0)) return;
      if(recoverHistoryList[userId].status != 1) return;

      if(msg.sender == userList[userId].nodeAddress1) {
        recoverHistoryList[userId].recoverProof1 = recoverProof;
        recoverHistoryList[userId].nodeConfirm1 = 
          recoverHistoryList[userId].serialNumber + 1;
      } else if(msg.sender == userList[userId].nodeAddress2) {
        recoverHistoryList[userId].recoverProof2 = recoverProof;
        recoverHistoryList[userId].nodeConfirm2 = 
          recoverHistoryList[userId].serialNumber + 1;
      } else if(msg.sender == userList[userId].nodeAddress3) {
        recoverHistoryList[userId].recoverProof3 = recoverProof;
        recoverHistoryList[userId].nodeConfirm3 = 
          recoverHistoryList[userId].serialNumber + 1;
      } else {
        return;
      }
      uint nodeConfirms = recoverHistoryList[userId].nodeConfirm1 + 
        recoverHistoryList[userId].nodeConfirm2 + 
        recoverHistoryList[userId].nodeConfirm3;

      if (nodeConfirms - 3 * recoverHistoryList[userId].serialNumber >= 2) {
        recoverHistoryList[userId].status = 2;
        recoverHistoryList[userId].serialNumber += 1;

        balances[userList[userId].userAddress] -= 3;
        balances[userList[userId].nodeAddress1] += 1;
        balances[userList[userId].nodeAddress2] += 1;
        balances[userList[userId].nodeAddress3] += 1;
      }
    }

    function signRequest(string memory userId) public {
      if(userList[userId].userAddress == address(0)) return;
      signHistoryList[userId].status = 1;
    }

    function signDone(string memory userId, 
      string memory signProof) public {

      if(balances[userList[userId].userAddress] < 3) return;
      if(userList[userId].userAddress == address(0)) return;
      if(signHistoryList[userId].status != 1) return;

      if(msg.sender == userList[userId].nodeAddress1) {
        signHistoryList[userId].signProof1 = signProof;
        signHistoryList[userId].nodeConfirm1 = 
          signHistoryList[userId].serialNumber + 1;
      } else if(msg.sender == userList[userId].nodeAddress2) {
        signHistoryList[userId].signProof2 = signProof;
        signHistoryList[userId].nodeConfirm2 = 
          signHistoryList[userId].serialNumber + 1;
      } else if(msg.sender == userList[userId].nodeAddress3) {
        signHistoryList[userId].signProof3 = signProof;
        signHistoryList[userId].nodeConfirm3 = 
          signHistoryList[userId].serialNumber + 1;
      } else {
        return;
      }
      uint nodeConfirms = signHistoryList[userId].nodeConfirm1 + 
        signHistoryList[userId].nodeConfirm2 + 
        signHistoryList[userId].nodeConfirm3;

      if (nodeConfirms - 3 * signHistoryList[userId].serialNumber >= 2) {
        signHistoryList[userId].status = 2;
        signHistoryList[userId].serialNumber += 1;

        balances[userList[userId].userAddress] -= 3;
        balances[userList[userId].nodeAddress1] += 1;
        balances[userList[userId].nodeAddress2] += 1;
        balances[userList[userId].nodeAddress3] += 1;
      }
    }

}
