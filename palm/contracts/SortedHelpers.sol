// SPDX-License-Identifier: UNLICENSED

pragma solidity 0.6.11;

import "./Interfaces/ISortedAssetPortfolios.sol";
import "./Dependencies/Ownable.sol";


contract SortedHelpers is  Ownable {
    bytes32 constant public NAME = "SortedHelpers";

    ISortedAssetPortfolios internal sortedAssetPortfolios;


    // --- Dependency setters ---

    constructor(
        address _sortedAssetPortfoliosAddress
    ) public
    {
        sortedAssetPortfolios = ISortedAssetPortfolios(_sortedAssetPortfoliosAddress);

//
//        _renounceOwnership();
    }


    // function getPrevs(address _id, int _length) external view override returns (address[]  memory retNodes) {
    //     address curId = _id;

    //     address[]   tempNodes;
    //     for (uint256 i = 0; i < _length; i++) {

    //         if (sortedAssetPortfolios.getPrev(curId) != address(0)) {

    //             curId = sortedAssetPortfolios.getPrev(curId);
    //             tempNodes.push(curId);
    //         }else{
    //             for (int256 j = tempNodes.length; j>=0; j--) {
    //                 if (tempNodes[j] != address(0)) {
    //                     retNodes.push(tempNodes[j]);
    //                 }
    //             }
    //             return retNodes;
    //         }

    //     }
    //     for (int256 j = tempNodes.length; j>=0; j--) {
    //                 if (tempNodes[j] != address(0)) {
    //                     retNodes.push(tempNodes[j]);
    //                 }
    //             }

    //     return retNodes;
    // }
    function getPrevs(address _id, uint _length) external view  returns (address[]  memory ) {
        address curId = _id;

        address[]  memory retNodes = new address[](_length);

        for (uint256 i = 0; i < _length; i++) {

            if (sortedAssetPortfolios.getPrev(curId) != address(0)) {
                curId = sortedAssetPortfolios.getPrev(curId);
                retNodes[i] = curId;
            }else{
                return retNodes;
            }

        }

        return retNodes;
    }
    function getNexts(address _id, uint _length) external view  returns (address[]  memory ) {
        address curId = _id;

        address[]  memory retNodes = new address[](_length);

        for (uint256 i = 0; i < _length; i++) {

        if (sortedAssetPortfolios.getNext(curId) != address(0)) {
                curId = sortedAssetPortfolios.getNext(curId);
                retNodes[i] = curId;
            }else{
                return retNodes;
            }

        }


        return retNodes;
    }
}