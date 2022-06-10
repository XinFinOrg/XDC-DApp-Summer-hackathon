// SPDX-License-Identifier: MIT

pragma solidity 0.6.11;

import '../Dependencies/SafeMath.sol';
import '../Interfaces/IPriceFeed.sol';


interface IQIToken {
    function exchangeRateCurrent() external returns (uint);
    function exchangeRateStored() external view returns (uint);
    function underlying() external returns (address);
}

contract QiTokenOracle is IPriceFeed {
  using SafeMath for uint;

  IPriceFeed public immutable underlyingFeed;
  address public immutable qiToken;
  uint public immutable wad;
  string public name;

  constructor(IPriceFeed _underlyingFeed, address _qiToken, uint _underlyingDecimals, string memory _name) public {
    underlyingFeed = _underlyingFeed;
    name = _name;
    qiToken = _qiToken;
    uint wadDecimals =  18 - 8 + _underlyingDecimals; // https://compound.finance/docs/ctokens#exchange-rate
    wad = 10 ** wadDecimals;
  }

  function fetchPrice_v() external override view returns (uint) {
    return IQIToken(qiToken).exchangeRateStored().mul(underlyingFeed.fetchPrice_v()).div(wad);
  }
  function fetchPrice() external override returns (uint) {
    return IQIToken(qiToken).exchangeRateStored().mul(underlyingFeed.fetchPrice_v()).div(wad);
  }
}