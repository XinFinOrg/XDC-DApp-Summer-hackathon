// SPDX-License-Identifier: UNLICENSED

pragma solidity 0.6.11;

// Common interface for the AssetPortfolio Manager.
interface IBorrowerOperations {

    // --- Functions ---

    function setAddresses(
        address _assetPortfolioManagerAddress,
        address _activePoolAddress,
        address _defaultPoolAddress,
        address _gasPoolAddress,
        address _collSurplusPoolAddress,
        address _sortedAssetPortfoliosAddress,
        address _pustTokenAddress,
        address _controllerAddress
    ) external;

    function openAssetPortfolio(uint _maxFeePercentage, uint _PUSTAmount, address _upperHint,
        address _lowerHint,
        address[] calldata _colls,
        uint[] calldata _amounts) external;

    function openAssetPortfolioLeverUp(
        uint256 _maxFeePercentage,
        uint256 _PUSTAmount,
        address _upperHint,
        address _lowerHint,
        address[] memory _colls,
        uint256[] memory _amounts,
        uint256[] memory _leverages,
        uint256[] memory _maxSlippages
    ) external;

    function closeAssetPortfolioUnlever(
        address[] memory _collsOut,
        uint256[] memory _amountsOut,
        uint256[] memory _maxSlippages
    ) external;

    function closeAssetPortfolio() external;

    function adjustAssetPortfolio(
        address[] calldata _collsIn,
        uint[] calldata _amountsIn,
        address[] calldata _collsOut,
        uint[] calldata _amountsOut,
        uint _PUSTChange,
        bool _isDebtIncrease,
        address _upperHint,
        address _lowerHint,
        uint _maxFeePercentage) external;

    // function addColl(address[] memory _collsIn, uint[] memory _amountsIn, address _upperHint, address _lowerHint, uint _maxFeePercentage) external;

    function addCollLeverUp(
        address[] memory _collsIn,
        uint256[] memory _amountsIn,
        uint256[] memory _leverages,
        uint256[] memory _maxSlippages,
        uint256 _PUSTAmount,
        address _upperHint,
        address _lowerHint,
        uint256 _maxFeePercentage
    ) external;

    function withdrawCollUnleverUp(
        address[] memory _collsOut,
        uint256[] memory _amountsOut,
        uint256[] memory _maxSlippages,
        uint256 _PUSTAmount,
        address _upperHint,
        address _lowerHint
    ) external;
}