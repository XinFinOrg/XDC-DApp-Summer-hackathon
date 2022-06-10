// SPDX-License-Identifier: UNLICENSED
pragma solidity 0.6.11;

import "../Interfaces/IPriceFeed.sol";
import "../Interfaces/ICurvePool.sol";
//import "../Dependencies/SafeMath.sol";
import "../Dependencies/SafeMathJoe.sol";


/*
 * PriceFeed for mainnet deployment, to be connected to Chainlink's live Collateral:USD aggregator reference
 * contract
 *
 * PriceFeed will just return the Chainlink feed price unless _chainlinkIsBroken() returns true
 * in which case it will just return lastGoodPrice
 */

contract PUSTPriceFeed is IPriceFeed {
    using SafeMathJoe for uint256;
    ICurvePool public curvePool;
    constructor(address _curvePool) public {
        curvePool = ICurvePool(_curvePool);
    }
    function fetchPrice()  external override returns (uint256) {

    }
    function fetchPrice_v() view external override returns (uint256) {
        uint256 price = curvePool.get_dy(0, 1, 1e18) * 1e12;
        return price;
    }
}