// SPDX-License-Identifier: UNLICENSED
pragma solidity ^0.8.9;

// Import this file to use console.log
import "@openzeppelin/contracts/utils/Counters.sol";

contract Sawti {
    using Counters for Counters.Counter;
    Counters.Counter private _tokenIds;

    uint256 casts = 0;

    event NFTMade(uint256 id);
    event SawtiMade(uint256 id);

    struct Sawt {
        string name;
        string link;
        uint256 timestamp;
        address author;
        string category;
        uint256 id;
    }

    Sawt[] public tech;
    Sawt[] public fiction;
    Sawt[] public history;
    Sawt[] public misc;
    Sawt[] public all;

    mapping(address => Sawt[]) public userToPodcasts;
    mapping(string => address) public podcastToUser;
    mapping(string => Sawt) public linkToPodcast;
    mapping(uint256 => Sawt) public idToPodcast;
  

    constructor()  {}

    function publishPodcast(
        string memory podcastLink,
        string memory name,
        string memory category
    ) public {
        casts = casts + 1;
        userToPodcasts[msg.sender].push(
            Sawt(
                name,
                podcastLink,
                block.timestamp,
                msg.sender,
                category,
                casts
            )
        );
        podcastToUser[podcastLink] = msg.sender;
        linkToPodcast[podcastLink] = Sawt(
            name,
            podcastLink,
            block.timestamp,
            msg.sender,
            category,
            casts
        );

        all.push(
            Sawt(
                name,
                podcastLink,
                block.timestamp,
                msg.sender,
                category,
                casts
            )
        );

        if (keccak256(bytes(category)) == keccak256(bytes("tech"))) {
            tech.push(
                Sawt(
                    name,
                    podcastLink,
                    block.timestamp,
                    msg.sender,
                    category,
                    casts
                )
            );
        } else if (keccak256(bytes(category)) == keccak256(bytes("fiction"))) {
            fiction.push(
                Sawt(
                    name,
                    podcastLink,
                    block.timestamp,
                    msg.sender,
                    category,
                    casts
                )
            );
        } else if (keccak256(bytes(category)) == keccak256(bytes("history"))) {
            history.push(
                Sawt(
                    name,
                    podcastLink,
                    block.timestamp,
                    msg.sender,
                    category,
                    casts
                )
            );
        } else {
            misc.push(
                Sawt(
                    name,
                    podcastLink,
                    block.timestamp,
                    msg.sender,
                    category,
                    casts
                )
            );
        }

        idToPodcast[casts] = Sawt(
            name,
            podcastLink,
            block.timestamp,
            msg.sender,
            category,
            casts
        );

        emit SawtiMade(casts);
    }

    function getUserPodcasts() public view returns (Sawt[] memory) {
        return userToPodcasts[msg.sender];
    }

    function getPodcastsFromId(uint256 id) public view returns (Sawt memory) {
        return idToPodcast[id];
    }

    function getUserOfPodcast(string memory url) public view returns (address) {
        return podcastToUser[url];
    }

    function getPodcastFromLink(string memory url)
        public
        view
        returns (Sawt memory)
    {
        return linkToPodcast[url];
    }

    function getTechPodcasts() public view returns (Sawt[] memory) {
        return tech;
    }

    function getFictionPodcasts() public view returns (Sawt[] memory) {
        return fiction;
    }

    function getHistoryPodcasts() public view returns (Sawt[] memory) {
        return history;
    }

    function getMiscPodcasts() public view returns (Sawt[] memory) {
        return misc;
    }

    function getAllPodcasts() public view returns (Sawt[] memory) {
        return all;
    }
}
