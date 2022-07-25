// SPDX-License-Identifier: UNLICENSED
pragma solidity >=0.8.4;

import "hardhat/console.sol";
import "@openzeppelin/contracts/token/ERC721/ERC721.sol";
import "@openzeppelin/contracts/utils/Counters.sol";
import "@openzeppelin/contracts/token/ERC721/extensions/ERC721URIStorage.sol";

error SpaceshipsError();

contract Spaceships is ERC721URIStorage {
    using Counters for Counters.Counter;
    Counters.Counter private _tokenIds;
    mapping(uint256 => string) public _tokenToShipCode;
    mapping(address => mapping(uint256 => uint256)) public _ownedTokens;

    constructor(string memory tokenName, string memory symbol) ERC721(tokenName, symbol) {
        console.log("Deploying Spaceships contract");
    }

    function mintShip(address owner) public returns (uint256) {
        _tokenIds.increment();
        uint256 tokenId = _tokenIds.current();

        _safeMint(owner, tokenId);

        _tokenToShipCode[tokenId] = "0000"; // Basic ship

        _addTokenToOwnerEnumeration(owner, tokenId);

        //string(bytes.concat(bytes(a), " ", bytes(b)))
        string memory metadataURI = string(
            abi.encodePacked("https://xdc.waralpha.io/assets/ships/", "0000", ".json")
        );
        _setTokenURI(tokenId, metadataURI);

        return tokenId;
    }

    function upgradeShip(uint256 tokenId, string memory shipCode) public {
        _tokenToShipCode[tokenId] = shipCode;

        string memory metadataURI = string(
            abi.encodePacked("https://xdc.waralpha.io/assets/ships/", shipCode, ".json")
        );

        _setTokenURI(tokenId, metadataURI);
    }

    function _addTokenToOwnerEnumeration(address owner, uint256 tokenId) private {
        uint256 length = balanceOf(owner);
        _ownedTokens[owner][length] = tokenId;
    }

    function tokenOfOwnerByIndex(address owner, uint256 index) public view virtual returns (uint256) {
        //require(index < balanceOf(owner), "ERC721Enumerable: owner index out of bounds");
        return _ownedTokens[owner][index];
    }

    function throwError() external pure {
        revert SpaceshipsError();
    }
}
