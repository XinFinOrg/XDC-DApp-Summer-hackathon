// SPDX-License-Identifier: MIT
pragma solidity ^0.8.0;

import "@openzeppelin/contracts/token/ERC20/IERC20.sol";
import "@openzeppelin/contracts/proxy/utils/Initializable.sol";
import "@openzeppelin/contracts/utils/cryptography/MerkleProof.sol";
import "./interfaces/IMerkleDistributor.sol";

contract MerkleDistributor is IMerkleDistributor, Initializable {
    address public override token;
    bytes32 public override merkleRoot;
    uint256 public deadline;

    address private deployer;

    address constant ZERO_ADDRESS = 0x0000000000000000000000000000000000000000;

    constructor () {
        deployer = msg.sender;
    }

    // This is a packed array of booleans.
    mapping(uint256 => uint256) private claimedBitMap;

    function initialize(address _token, bytes32 _merkleRoot, uint256 _deadline) public initializer{
        require(_deadline >= block.timestamp, "Merkle Distributor: can't start an expired airdrop.");
        token = _token;
        merkleRoot = _merkleRoot;
        deadline = _deadline;
    }

    modifier withinDeadline {
        require(deadline >= block.timestamp, "Merkle Distributor: Deadline passed.");
        _;
    }

    function isClaimed(uint256 index) public view override returns (bool) {
        uint256 claimedWordIndex = index / 256;
        uint256 claimedBitIndex = index % 256;
        uint256 claimedWord = claimedBitMap[claimedWordIndex];
        uint256 mask = (1 << claimedBitIndex);
        return claimedWord & mask == mask;
    }

    function _setClaimed(uint256 index) private {
        uint256 claimedWordIndex = index / 256;
        uint256 claimedBitIndex = index % 256;
        claimedBitMap[claimedWordIndex] = claimedBitMap[claimedWordIndex] | (1 << claimedBitIndex);
    }

    function claim(uint256 index, address account, uint256 amount, bytes32[] calldata merkleProof) external override withinDeadline {
        // TODO: have a type for the airdrop (NFT, Native, ERC20 ...)
        require(!isClaimed(index), "MerkleDistributor: Drop already claimed.");

        // Verify the merkle proof.
        bytes32 node = keccak256(abi.encodePacked(index, account, amount));
        require(MerkleProof.verify(merkleProof, merkleRoot, node), "MerkleDistributor: Invalid proof.");

        // Mark it claimed and send the token.
        _setClaimed(index);
        require(IERC20(token).transfer(account, amount), "MerkleDistributor: Transfer failed.");

        emit Claimed(index, account, amount);
    }
}
