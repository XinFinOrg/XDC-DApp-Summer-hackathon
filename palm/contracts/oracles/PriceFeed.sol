// SPDX-License-Identifier: UNLICENSED

pragma solidity 0.6.11;

import "../Interfaces/IPriceFeed.sol";
import "../Dependencies/AggregatorV3Interface.sol";
import "../Dependencies/SafeMath.sol";
import "../Dependencies/PalmMath.sol";



/*
 * PriceFeed for mainnet deployment, to be connected to Chainlink's live Collateral:USD aggregator reference
 * contract
 *
 * PriceFeed will just return the Chainlink feed price unless _chainlinkIsBroken() returns true
 * in which case it will just return lastGoodPrice
 */
contract PriceFeed is IPriceFeed {
    using SafeMath for uint256;

    string public name;

    AggregatorV3Interface public priceAggregator; // Mainnet Chainlink aggregator

    uint256 public constant DECIMAL_PRECISION = 1e18;

    // Use to convert a price answer to an 18-digit precision uint
    uint256 public constant TARGET_DIGITS = 18;

    // The last good price seen from an oracle by Palm
    uint256 public lastGoodPrice;

    struct ChainlinkResponse {
        uint80 roundId;
        int256 answer;
        uint256 timestamp;
        bool success;
        uint8 decimals;
    }

    // --- Dependency setters ---
    bool private addressSet;
    function setAddresses(address _priceAggregatorAddress, string memory _name) external {
        require(addressSet == false, "Addresses already set");
        addressSet = true;

        name = _name;

        priceAggregator = AggregatorV3Interface(_priceAggregatorAddress);

        ChainlinkResponse memory chainlinkResponse = _getCurrentChainlinkResponse();

        require(
            !_chainlinkIsBroken(chainlinkResponse),
            "PriceFeed: Chainlink must be working"
        );

        uint256 scaledChainlinkPrice = _scaleChainlinkPriceByDigits(
            uint256(chainlinkResponse.answer),
            chainlinkResponse.decimals
        );

        // Store an initial price from Chainlink to serve as first reference for lastGoodPrice
        _storePrice(scaledChainlinkPrice);
    }

    // --- Functions ---

    /**
     * @notice Returns the latest price obtained from the Oracle.
     * @dev Callable by anyone externally.
     *
     * Non-view function - it stores the last good price seen by Palm.
     *
     * Uses a Chainlink Oracle and checks it isn't broken.
     * If the Chainlink Oracle is broken, then returns the last good price seen
     * If Chainlink is working, it updates lastGoodPrice to the last Chainlink Response
     * and returns it.
     */
    function fetchPrice() external override returns (uint256) {
        // Get current and previous price data from Chainlink
        ChainlinkResponse memory chainlinkResponse = _getCurrentChainlinkResponse();
        if (
            _chainlinkIsBroken(chainlinkResponse)
        ) {
            // Chainlink has some issue so just return lastGoodPrice
            return lastGoodPrice;
        } else {
            uint256 newPrice = _scaleChainlinkPriceByDigits(
                uint256(chainlinkResponse.answer),
                chainlinkResponse.decimals
            );
            _storePrice(newPrice);
            return newPrice;
        }
    }

    /**
     * @notice Returns the latest price obtained from the Oracle.
     * @dev Called by Palm contracts that require a current price.
     * Also callable by anyone externally.
     *
     * View function
     *
     * Uses a Chainlink Oracle and checks it isn't broken.
     * If the Chainlink Oracle is broken, then returns the last good price seen
     * If Chainlink is working, just returns the latest Chainlink response
     */
    function fetchPrice_v() external view override returns (uint256) {
        // Get current and previous price data from Chainlink
        ChainlinkResponse memory chainlinkResponse = _getCurrentChainlinkResponse();
        if (
            _chainlinkIsBroken(chainlinkResponse)
        ) {
            // Chainlink has some issue so just return lastGoodPrice
            return lastGoodPrice;
        } else {
            return
                _scaleChainlinkPriceByDigits(
                    uint256(chainlinkResponse.answer),
                    chainlinkResponse.decimals
                );
        }
    }

    // --- Helper functions ---

    /**
     *Chainlink is considered broken if its current round data is in any way bad
     */
    function _chainlinkIsBroken(ChainlinkResponse memory _response) internal view returns (bool) {
        // Check for response call reverted
        if (!_response.success) {
            return true;
        }
        // Check for an invalid roundId that is 0
        if (_response.roundId == 0) {
            return true;
        }
        // Check for an invalid timeStamp that is 0, or in the future
        if (_response.timestamp == 0 || _response.timestamp > block.timestamp) {
            return true;
        }
        // Check for non-positive price
        if (_response.answer <= 0) {
            return true;
        }

        return false;
    }


    function _scaleChainlinkPriceByDigits(uint256 _price, uint256 _answerDigits)
        internal
        pure
        returns (uint256)
    {
        /*
         * Convert the price returned by the Chainlink oracle to an 18-digit decimal for use by Palm.
         * At date of Palm launch, Chainlink uses an 8-digit price, but we also handle the possibility of
         * future changes.
         *
         */
        uint256 price;
        if (_answerDigits >= TARGET_DIGITS) {
            // Scale the returned price value down to Palm's target precision
            price = _price.div(10**(_answerDigits - TARGET_DIGITS));
        } else if (_answerDigits < TARGET_DIGITS) {
            // Scale the returned price value up to Palm's target precision
            price = _price.mul(10**(TARGET_DIGITS - _answerDigits));
        }
        return price;
    }

    function _storePrice(uint256 _currentPrice) internal {
        lastGoodPrice = _currentPrice;
        emit LastGoodPriceUpdated(_currentPrice);
    }

    // --- Oracle response wrapper functions ---

    function _getCurrentChainlinkResponse()
        internal
        view
        returns (ChainlinkResponse memory chainlinkResponse)
    {
        // First, try to get current decimal precision:
        try priceAggregator.decimals() returns (uint8 decimals) {
            // If call to Chainlink succeeds, record the current decimal precision
            chainlinkResponse.decimals = decimals;
        } catch {
            // If call to Chainlink aggregator reverts, return a zero response with success = false
            return chainlinkResponse;
        }

        // Secondly, try to get latest price data:
        try priceAggregator.latestRoundData() returns (
            uint80 roundId,
            int256 answer,
            uint256, /* startedAt */
            uint256 timestamp,
            uint80 /* answeredInRound */
        ) {
            // If call to Chainlink succeeds, return the response and success = true
            chainlinkResponse.roundId = roundId;
            chainlinkResponse.answer = answer;
            chainlinkResponse.timestamp = timestamp;
            chainlinkResponse.success = true;
            return chainlinkResponse;
        } catch {
            // If call to Chainlink aggregator reverts, return a zero response with success = false
            return chainlinkResponse;
        }
    }


}