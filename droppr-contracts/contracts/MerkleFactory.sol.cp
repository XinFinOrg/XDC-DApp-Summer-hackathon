// SPDX-License-Identifier: MIT
pragma solidity ^0.8.0;
import "@openzeppelin/contracts/proxy/Clones.sol";
import "./upgradeability/EternalStorage.sol";
import "@openzeppelin/contracts/token/ERC20/IERC20.sol";
import "./interfaces/IMerkleDistributor.sol";

contract MerkleFactory is EternalStorage{
    using Clones for address;

    // event NewAirdrop (address indexed contractAddress);
    event NewAirdrop(
        address indexed asset,
        address merkleAddress, 
        bytes32 merkleRoot, 
        bytes merkleUri, 
        uint256 amount, 
        uint256 deadline
    );
    
    function master() public view returns (address) {
        return addressStorage[keccak256(abi.encode("merkleMaster"))];
    }

    function setMaster(address _master) internal {
        addressStorage[keccak256(abi.encode("merkleMaster"))] = _master;
    }


    constructor(address _master) {
        setMaster(_master);
    }

    function createMerkle() internal returns (address){
        return master().clone();
    }

    function startAirdrop(bytes32 merkleRoot, bytes memory merkleUri, uint256 deadline, address asset, uint256 amount) internal returns (address) {
        // TODO: worry about asset type
        
        // IMerkleDistributor memory md = createMerkle();
        // IERC20 memory token = IERC20(asset);
        // require(token.transferFrom(msg.sender, address(md), amount), "MerkleFactory: Sending tokenAmount failed.");
        // require(md.initialize(asset, merkleRoot, deadline), "MerkleFactory: Merkle.initialize() failed.");
        // emit NewAirdrop() TODO: stff
    }


}