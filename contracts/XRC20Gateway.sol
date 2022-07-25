// SPDX-License-Identifier: MIT
pragma solidity ^0.8.0;

import "@goplugin/contracts/src/v0.8/PluginClient.sol";
import "@openzeppelin/contracts/utils/Counters.sol";
import "@openzeppelin/contracts/token/ERC20/IERC20.sol";

contract XRC20Gateway is PluginClient {
    using Plugin for Plugin.Request;
    using Counters for Counters.Counter;
    Counters.Counter private _tokenIds;
    Counters.Counter private _settlementIds;

    //Initialize Oracle Payment
    uint256 private constant ORACLE_PAYMENT = 0.1 * 10**18;
    uint256 public currentPrice;

    address public contractowner;

    struct TokenSupported {
        uint256 tokenId;
        string symbol;
        address tokenAddress;
    }
    mapping(uint256 => TokenSupported) tokenSupportedId;

    struct Settlement {
        uint256 id;
        uint256 depositedValue;
        uint256 transferredValue;
        uint256 transferredOn;
        address buyer;
        address tokenAddress;
        bool settled;
    }
    mapping(uint256 => Settlement) settlementId;

    mapping(bytes32 => Settlement) settlementRequestIds;

    mapping(string => mapping(address => bool)) symbolAddress;

    //Initialize event RequestPriceFulfilled
    event RequestPriceFulfilled(
        bytes32 indexed requestId,
        uint256 indexed price
    );

    //Initialize event requestCreated
    event requestCreated(
        address indexed requester,
        bytes32 indexed jobId,
        bytes32 indexed requestId
    );

    //Constructor to pass Pli Token Address during deployment
    constructor(address _pli) {
        setPluginToken(_pli);
        contractowner = msg.sender;
        _tokenIds.increment();
        _settlementIds.increment();
    }

    function addSupportedToken(string memory _symbol, address _tokenaddress)
        public
    {
        require(
            msg.sender == contractowner,
            "Only Contract Owner Can deposit Funds"
        );
        require(
            symbolAddress[_symbol][_tokenaddress] == false,
            "Symbol & Address already added"
        );

        uint256 currentId = _tokenIds.current();
        tokenSupportedId[currentId] = TokenSupported(
            currentId,
            _symbol,
            _tokenaddress
        );
        symbolAddress[_symbol][_tokenaddress] = true;
    }

    function depositFunds(
        uint256 _amount,
        address _tokenAddress,
        string memory _symbol
    ) public {
        require(
            msg.sender == contractowner,
            "Only Contract Owner Can deposit Funds"
        );
        require(_amount > 0, "Deposit funds cannot be 0");
        require(
            symbolAddress[_symbol][_tokenAddress] == true,
            "Symbol and/or Address not supported, please initiate addSupportedToken first"
        );
        XRC20Balance(msg.sender, _tokenAddress, _amount);
        XRC20Allowance(msg.sender, _tokenAddress, _amount);
        transferFundsToContract(_amount, _tokenAddress);
    }

    function XRC20Balance(
        address _addrToCheck,
        address _currency,
        uint256 _AmountToCheckAgainst
    ) internal view {
        require(
            IERC20(_currency).balanceOf(_addrToCheck) >= _AmountToCheckAgainst,
            "XRC20Gateway: insufficient currency balance"
        );
    }

    function XRC20Allowance(
        address _addrToCheck,
        address _currency,
        uint256 _AmountToCheckAgainst
    ) internal view {
        require(
            IERC20(_currency).allowance(_addrToCheck, address(this)) >=
                _AmountToCheckAgainst,
            "XRC20Gateway: insufficient allowance."
        );
    }

    //internal function for transferpayment
    function transferFundsToContract(uint256 _amount, address _tokenaddress)
        internal
    {
        IERC20(_tokenaddress).transferFrom(msg.sender, address(this), _amount);
    }

    //internal function for transferpayment
    function transferFundsToBuyer(
        address _buyer,
        address _tokenaddress,
        uint256 _amount
    ) internal {
        IERC20(_tokenaddress).transfer(_buyer, _amount);
    }

    //requestPrice function will initate the request to Oracle to get the price from Vinter API
    function requestPrice(
        address _oracle,
        string memory _jobId,
        string memory _fsymbol,
        string memory _tsymbol,
        uint256 _amountPaid,
        address _buyer,
        address _tokenaddress
    ) public returns (bytes32 requestId) {
        uint256 _settleId = _settlementIds.current();
        settlementId[_settleId] = Settlement(
            _settleId,
            _amountPaid,
            0,
            block.timestamp,
            _buyer,
            _tokenaddress,
            false
        );
        Plugin.Request memory request = buildPluginRequest(
            stringToBytes32(_jobId),
            address(this),
            this.fulfillPrice.selector
        );
        request.add("fsymbol", _fsymbol);
        request.add("tsymbol", _tsymbol);
        request.addUint("amounPaid", _amountPaid);
        request.addInt("times", 1000000000000000000);
        requestId = sendPluginRequestTo(_oracle, request, ORACLE_PAYMENT);
        settlementRequestIds[requestId] = settlementId[_settleId];
        emit requestCreated(msg.sender, stringToBytes32(_jobId), requestId);
    }

    //callBack function
    function fulfillPrice(bytes32 _requestId, uint256 _price)
        public
        recordPluginFulfillment(_requestId)
    {
        Settlement memory settle = settlementRequestIds[_requestId];
        transferFundsToBuyer(settle.buyer, settle.tokenAddress, _price);
        settle.settled = true;
        settle.transferredValue = _price;
        emit RequestPriceFulfilled(_requestId, _price);
    }

    function getPluginToken() public view returns (address) {
        return pluginTokenAddress();
    }

    //With draw pli can be invoked only by owner
    function withdrawPli() public {
        PliTokenInterface pli = PliTokenInterface(pluginTokenAddress());
        require(
            pli.transfer(msg.sender, pli.balanceOf(address(this))),
            "Unable to transfer"
        );
    }

    //Cancel the existing request
    function cancelRequest(
        bytes32 _requestId,
        uint256 _payment,
        bytes4 _callbackFunctionId,
        uint256 _expiration
    ) public {
        cancelPluginRequest(
            _requestId,
            _payment,
            _callbackFunctionId,
            _expiration
        );
    }

    //String to bytes to convert jobid to bytest32
    function stringToBytes32(string memory source)
        private
        pure
        returns (bytes32 result)
    {
        bytes memory tempEmptyStringTest = bytes(source);
        if (tempEmptyStringTest.length == 0) {
            return 0x0;
        }
        assembly {
            result := mload(add(source, 32))
        }
    }
}
