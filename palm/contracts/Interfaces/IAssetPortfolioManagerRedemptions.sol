// SPDX-License-Identifier: UNLICENSED

pragma solidity 0.6.11;

interface IAssetPortfolioManagerRedemptions {
    function redeemCollateral(
        uint _PUSTamount,
        uint _PUSTMaxFee,
        address _firstRedemptionHint,
        address _upperPartialRedemptionHint,
        address _lowerPartialRedemptionHint,
        uint _partialRedemptionHintNICR,
        uint _maxIterations,
        address _redeemSender
    )
    external;

    function redeemCollateralSingle(
        uint256 _PUSTamount,
        uint256 _PUSTMaxFee,
        address _target,
        address _upperHint,
        address _lowerHint,
        uint256 _hintAICR,
        address _collToRedeem,
        address _redeemer
    ) external;

    function updateRedemptionsEnabled(bool _enabled) external;
}