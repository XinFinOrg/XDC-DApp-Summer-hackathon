// SPDX-License-Identifier: MIT OR Apache-2.0
pragma solidity ^0.8.2;

import "../CustomTokens/contracts/utils/Counters.sol";
import "../CustomTokens/contracts/security/ReentrancyGuard.sol";
import "../CustomTokens/contracts/utils/math/SafeMath.sol";
import "../CustomTokens/contracts/token/ERC20/IERC20.sol";
import "../CustomTokens/contracts/access/AccessControlEnumerable.sol";
import "hardhat/console.sol";

contract ERC20Gateway is ReentrancyGuard {
    using Counters for Counters.Counter;
    using SafeMath for uint256;

    Counters.Counter private _merchantIds;
    Counters.Counter private _paymentIds;

    address marketBeneficiary;

    enum TokenType {
        USDT,
        USDC,
        DAI,
        USDZ
    }
    struct Payment {
        uint256 id;
        uint256 amount;
        uint256 depositedOn;
        address buyer;
        address seller;
        TokenType tokentype;
    }

    struct Merchant {
        uint256 merchantId;
        address merchantAddress;
        bytes32 apikey;
        TokenType tokentype;
    }

    mapping(uint256 => Merchant) public idToMerchants;

    mapping(address => bytes32) public apikey;

    mapping(bytes32 => bool) public apiRegistered;

    mapping(address => mapping(address => uint256)) public balanceToPay;

    mapping(bytes32 => address) public merchantAddress;

    mapping(address => bool) public registered;

    mapping(uint256 => Payment) public idToPayments;

    constructor() {
        marketBeneficiary = msg.sender;
        _merchantIds.increment();
        _paymentIds.increment();
    }

    event ERC20Transferred(
        uint256 indexed purchaseid,
        address indexed sender,
        address indexed beneficiary,
        uint256 amount,
        TokenType _tokenType
    );

    event APIRegistered(
        uint256 indexed merchantId,
        address indexed merchantWallet,
        bytes32 indexed api,
        TokenType _tokenType
    );

    /* Function to register a merchange and get API Keys */
    function registerMerchant(
        address _erc20Wallet,
        string memory _code,
        TokenType _tokenType
    ) external returns (bytes32) {
        require(registered[_erc20Wallet] == false, "Already Registered");
        //generate api key
        bytes32 api = keccak256(abi.encode(_erc20Wallet, _code));

        //store necessary mappings
        apikey[_erc20Wallet] = api;
        apiRegistered[api] = true;
        registered[_erc20Wallet] = true;
        merchantAddress[api] = _erc20Wallet;

        //store Merchant Details
        uint256 _merchantid = _merchantIds.current();
        idToMerchants[_merchantid] = Merchant(
            _merchantid,
            _erc20Wallet,
            api,
            TokenType(_tokenType)
        );

        emit APIRegistered(
            _merchantid,
            _erc20Wallet,
            api,
            TokenType(_tokenType)
        );

        return api;
    }

    /* Function to get API Keys */
    function fetchAPIKey(address _erc20Wallet) external view returns (bytes32) {
        require(registered[_erc20Wallet] == true, "Not Registered Yet");
        return apikey[_erc20Wallet];
    }

    /* Creates the sale of a marketplace item */
    /* Transfers ownership of the item, as well as funds between parties */
    function makePayment(
        uint256 _amount,
        address _tokenAddress,
        TokenType _token,
        bytes32 _api
    ) public nonReentrant returns (bool) {
        require(_amount > 0, "Amount cannot be 0");
        require(
            apiRegistered[_api] == true,
            "APIKey Not registered, please check with Merchant"
        );
        bool flag = false;

        if (
            (TokenType.USDT == TokenType(_token)) ||
            (TokenType.USDC == TokenType(_token)) ||
            (TokenType.DAI == TokenType(_token)) ||
            (TokenType.USDZ == TokenType(_token))
        ) {
            flag = true;
            uint256 _paymentId = _paymentIds.current();
            ERC20Balance(msg.sender, _tokenAddress, _amount);
            ERC20Allowance(msg.sender, _tokenAddress, _amount);
            idToPayments[_paymentId] = Payment(
                _paymentId,
                _amount,
                block.timestamp,
                msg.sender,
                merchantAddress[_api],
                TokenType(_token)
            );
            transferPayment(_amount, _tokenAddress, merchantAddress[_api]);
            emit ERC20Transferred(
                _paymentId,
                msg.sender,
                marketBeneficiary,
                _amount,
                TokenType(_token)
            );
            return flag;
        } else {
            require(flag, "Token Type is not supported");
            return flag;
        }
    }

    function ERC20Balance(
        address _addrToCheck,
        address _currency,
        uint256 _AmountToCheckAgainst
    ) internal view {
        require(
            IERC20(_currency).balanceOf(_addrToCheck) >= _AmountToCheckAgainst,
            "ERC20Payment: insufficient currency balance"
        );
    }

    function ERC20Allowance(
        address _addrToCheck,
        address _currency,
        uint256 _AmountToCheckAgainst
    ) internal view {
        require(
            IERC20(_currency).allowance(_addrToCheck, address(this)) >=
                _AmountToCheckAgainst,
            "ERC20Payment: insufficient allowance."
        );
    }

    //internal function for transferpayment
    function transferPayment(
        uint256 _amount,
        address _tokenaddress,
        address _seller
    ) internal {
        balanceToPay[_seller][_tokenaddress] = balanceToPay[_seller][
            _tokenaddress
        ].add(_amount);
        IERC20(_tokenaddress).transferFrom(msg.sender, address(this), _amount);
    }

    //Function to return total PLI balance available in the contract
    function getTokenBalance(address _tokenAddress)
        public
        view
        returns (uint256 _balance)
    {
        return IERC20(_tokenAddress).balanceOf(address(this));
    }

    //Function to return total PLI balance available in the contract
    function withdrawBalance(bytes32 _api, address _currencyToWithdraw)
        public
        returns (bool)
    {
        require(
            merchantAddress[_api] == msg.sender,
            "Only merchant can call this function"
        );
        require(
            balanceToPay[msg.sender][_currencyToWithdraw] > 0,
            "No Amount to withdraw"
        );

        uint256 _contractBalance = IERC20(_currencyToWithdraw).balanceOf(
            address(this)
        );

        require(_contractBalance > 0, "Contract Balance for this token is 0");

        uint256 _merchantBalance = balanceToPay[msg.sender][
            _currencyToWithdraw
        ];

        require(_merchantBalance > 0, "Merchant Balance for this token is 0");

        uint256 toWithdraw = _contractBalance.sub(_merchantBalance);

        IERC20(_currencyToWithdraw).transfer(msg.sender, toWithdraw);

        balanceToPay[msg.sender][_currencyToWithdraw] = balanceToPay[
            msg.sender
        ][_currencyToWithdraw].sub(toWithdraw);

        return true;
    }
}
