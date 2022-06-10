// SPDX-License-Identifier: UNLICENSED
pragma solidity >= 0.6.11;

import "../Interfaces/IPriceFeed.sol";
import "../Interfaces/IUniswapV2Pair.sol";
import "../Dependencies/UQ122x122.sol";
import "../Dependencies/SafeMathJoe.sol";


contract PALMPriceFeed is IPriceFeed {
    using UQ112x112 for uint224;
    using SafeMathJoe for uint256;
    IPriceFeed public ETHPriceFeed;
    IUniswapV2Pair public PALMETH;
    address public PALMToken;
    constructor(address LPToken, address _PALMToken, address _ETHPriceFeed) public {
        PALMETH = IUniswapV2Pair(LPToken);
        PALMToken = _PALMToken;
        ETHPriceFeed = IPriceFeed(_ETHPriceFeed);
    }
  function fetchPrice()  external override returns (uint256) {
      
      }
    function fetchPrice_v() view external override returns (uint256) {
        address t0 = PALMETH.token0();
        address t1 = PALMETH.token1();
        (uint112 r0, uint112 r1, ) = PALMETH.getReserves();
        uint256 ETHPrice = ETHPriceFeed.fetchPrice_v();
        if (t0 == PALMToken) {
            return uint256(r1).mul(10 ** 18).div(uint256(r0)).mul(ETHPrice).div(10 ** 18);
        } else {
            return uint256(r0).mul(10 ** 18).div(uint256(r1)).div(ETHPrice).div(10 ** 18);
        }

    }
}