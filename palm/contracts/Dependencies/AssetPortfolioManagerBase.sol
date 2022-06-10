// SPDX-License-Identifier: UNLICENSED

pragma solidity 0.6.11;

import "../Interfaces/IAssetPortfolioManager.sol";
import "../Interfaces/IStabilityPool.sol";
import "../Interfaces/ICollSurplusPool.sol";
import "../Interfaces/IPUSTToken.sol";
import "../Interfaces/ISortedAssetPortfolios.sol";
import "../Interfaces/IPALMToken.sol";
import "../Interfaces/IActivePool.sol";
import "../Interfaces/IAssetPortfolioManagerLiquidations.sol";
import "../Interfaces/IAssetPortfolioManagerRedemptions.sol";
import "./LiquityBase.sol";

/**
 * Contains shared functionality of AssetPortfolioManagerLiquidations, AssetPortfolioManagerRedemptions, and AssetPortfolioManager.
 * Keeps addresses to cache, events, structs, status, etc. Also keeps AssetPortfolio struct.
 */

contract AssetPortfolioManagerBase is LiquityBase {

    // --- Connected contract declarations ---

    // A doubly linked list of AssetPortfolios, sorted by their sorted by their individual collateral ratios

    struct ContractsCache {
        IActivePool activePool;
        IDefaultPool defaultPool;
        IPUSTToken pustToken;
        ISortedAssetPortfolios sortedAssetPortfolios;
        ICollSurplusPool collSurplusPool;
        address gasPoolAddress;
        IPalmController controller;
    }

    enum Status {
        nonExistent,
        active,
        closedByOwner,
        closedByLiquidation,
        closedByRedemption
    }

    enum AssetPortfolioManagerOperation {
        applyPendingRewards,
        liquidateInNormalMode,
        liquidateInRecoveryMode,
        redeemCollateral
    }

    // Store the necessary data for a assetPortfolio
    struct AssetPortfolio {
        newColls colls;
        uint debt;
        mapping(address => uint) stakes;
        Status status;
        uint128 arrayIndex;
    }


    event AssetPortfolioUpdated(address indexed _borrower, uint _debt, address[] _tokens, uint[] _amounts, AssetPortfolioManagerOperation operation);
    /**
     * @dev This empty reserved space is put in place to allow future versions to add new
     * variables without shifting down storage in the inheritance chain.
     * See https://docs.openzeppelin.com/contracts/4.x/upgradeable#storage_gaps
     */
    uint256[50] private __gap;
}