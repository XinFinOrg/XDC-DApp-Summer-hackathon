// SPDX-License-Identifier: UNLICENSED

pragma solidity 0.6.11;

/**
 * @notice Interface for use of wrapping and unwrapping vault tokens in the Palm Finance borrowing
 * protocol.
 */
interface IPalmVaultToken {
    function deposit(uint256 _amt) external returns (uint256 receiptTokens);
    function depositFor(address _borrower, address _recipient, uint256 _amt) external returns (uint256 receiptTokens);
    function redeem(address _to, uint256 _amt) external returns (uint256 underlyingTokens);
}