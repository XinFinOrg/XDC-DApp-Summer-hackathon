// SPDX-License-Identifier: MIT

pragma solidity 0.6.11;

import '../Dependencies/SafeMath.sol';
import '../Interfaces/IPriceFeed.sol';

interface IVault {
  function receiptPerUnderlying() external view returns (uint);
  function underlyingPerReceipt() external view returns (uint);
  function underlyingDecimal() external view returns (uint);
}

contract VaultOracle is IPriceFeed {
  using SafeMath for uint;

  IPriceFeed public immutable underlyingFeed;
  IVault public immutable vault;
  uint256 public immutable wad;
  string public name;
  
  constructor(address _underlyingFeed, address _vault, string memory _name) public {
    name = _name;
    vault = IVault(_vault);
    underlyingFeed = IPriceFeed(_underlyingFeed);
    wad = 10 ** IVault(_vault).underlyingDecimal();
  }

  function fetchPrice_v() external view override returns (uint) {
    return (vault.underlyingPerReceipt()).mul(underlyingFeed.fetchPrice_v()).div(wad);
  }

  function fetchPrice() external override returns (uint) {
    return (vault.underlyingPerReceipt()).mul(underlyingFeed.fetchPrice()).div(wad);
  }
}