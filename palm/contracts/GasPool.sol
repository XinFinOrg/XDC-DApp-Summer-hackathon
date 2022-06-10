// SPDX-License-Identifier: UNLICENSED

pragma solidity 0.6.11;

/**
 * The purpose of this contract is to hold PUST tokens for gas compensation:
 * https://github.com/liquity/dev#gas-compensation
 * When a borrower opens a assetPortfolio, an additional 200 PUST debt is issued,
 * and 200 PUST is minted and sent to this contract.
 * When a borrower closes their active assetPortfolio, this gas compensation is refunded:
 * 200 PUST is burned from the this contract's balance, and the corresponding
 * 200 PUST debt on the assetPortfolio is cancelled.
 * See this issue for more context: https://github.com/liquity/dev/issues/186
 */
contract GasPool {
    // do nothing, as the core contracts have permission to send to and burn PUST from this address
}