// SPDX-License-Identifier: UNLICENSED

pragma solidity 0.6.11;


interface IAssetPortfolioManagerLiquidations {
    function batchLiquidateAssetPortfolios(address[] memory _assetPortfolioArray, address _liquidator) external;
}