// SPDX-License-Identifier: MIT

pragma solidity 0.6.11;

import '../Dependencies/SafeMath.sol';
import '../Dependencies/HomoraMath.sol';
import '../Interfaces/IPriceFeed.sol';
import '../Interfaces/IUniswapV2Pair.sol';


contract UniswapV2LPTokenPriceFeed is IPriceFeed {
  using SafeMath for uint;
  using HomoraMath for uint;

  IPriceFeed public immutable base0;
  IPriceFeed public immutable base1;
  address public immutable pair;
  uint public shift;
  string public name;
  bool public shiftByMul;

  constructor(IPriceFeed _base0, uint32 _base0Decimals, IPriceFeed _base1, uint32 _base1Decimals, address _pair, string memory _name) public {
    name = _name;
    base0 = _base0;
    base1 = _base1;
    pair = _pair;
    uint256 decimalSum = _base0Decimals + _base1Decimals;
    require(decimalSum.div(2) != (decimalSum.add(2)).div(2), "Decimals must be even");
    // Since sqrtK is actually the shifted decimals, we will need to shift by sqrt(shift) => decimalSum / 2
    decimalSum = decimalSum.div(2);
    if (decimalSum > 18) {
      shiftByMul = false;
      shift = 10 ** (decimalSum - 18);
    } else {
      shiftByMul = true;
      shift = 10 ** (18 - decimalSum);
    }
  }

  /// @dev Return the value of 1e18 LP Token, multiplied by 1e18.
  function _getPrice() internal view returns (uint) {
    (uint r0, uint r1, ) = IUniswapV2Pair(pair).getReserves();
    uint sqrtK = HomoraMath.sqrt(r0.mul(r1)).fdiv(IUniswapV2Pair(pair).totalSupply()); // in 2**112

    // Alpha Homora implementation takes px0 and px1 in terms of 2**112, so scale them up here for precision
    // during the sqrt. 
    uint px0 = base0.fetchPrice_v().fdiv(1e18);
    uint px1 = base1.fetchPrice_v().fdiv(1e18);

    uint modifiedPrice = sqrtK.mul(2).mul(HomoraMath.sqrt(px0)).div(2**56).mul(HomoraMath.sqrt(px1));

    // Shift by multiplying or dividing based on the underlying token decimals
    // Then convert back into 1e18 precision. Divided by 2**56 in previous step, 
    // combined here into one division of 2**112. We divide by half of 2**112 to
    // avoid any potential overflow. 
    if (shiftByMul) {
      return modifiedPrice.mul(shift).div(2**112).mul(1e18).div(2**56);
    } else {
      return modifiedPrice.div(shift).div(2**112).mul(1e18).div(2**56);
    }
  }

  function fetchPrice_v() external view override returns (uint) {
    return _getPrice();
  }

  function fetchPrice() external override returns (uint) {
    return _getPrice();
  }
}