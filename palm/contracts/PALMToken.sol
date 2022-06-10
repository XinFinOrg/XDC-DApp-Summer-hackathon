// SPDX-License-Identifier: MIT

pragma solidity 0.6.11;

import "./Dependencies/SafeMath.sol";
import "./Interfaces/IPALMToken.sol";


/*
* Brought to you by @PalmFinance
*
* Based upon OpenZeppelin's ERC20 contract:
* https://github.com/OpenZeppelin/openzeppelin-contracts/blob/master/contracts/token/ERC20/ERC20.sol
*
* and their EIP2612 (ERC20Permit / ERC712) functionality:
* https://github.com/OpenZeppelin/openzeppelin-contracts/blob/53516bc555a454862470e7860a9b5254db4d00f5/contracts/token/ERC20/ERC20Permit.sol
*
*
*  --- Functionality added specific to the PALMToken ---
*
* 1) Transfer protection: Prevent accidentally sending PALM to directly to this address
*
* 2) sendToSPALM(): Only callable by the SPALM contract to transfer PALM for staking.
*
* 3) Supply hard-capped at 500 million
*
* 4) Palm Finance Treasury and Palm Finance Team addresses set at deployment
*
* 5) 365 million tokens are minted at deployment to the Palm Finance Treasury
*
* 6) 135 million tokens are minted at deployment to the Palm Finance Team
*
*/
contract PALMToken is IPALMToken {
    using SafeMath for uint256;

    // --- ERC20 Data ---

    string constant internal _NAME = "Palm Finance";
    string constant internal _SYMBOL = "PALM";
    string constant internal _VERSION = "1";
    uint8 constant internal  _DECIMALS = 18;

    mapping (address => uint256) private _balances;
    mapping (address => mapping (address => uint256)) private _allowances;
    uint private _totalSupply;

    // --- EIP 2612 Data ---

    bytes32 private constant _PERMIT_TYPEHASH = keccak256("Permit(address owner,address spender,uint256 value,uint256 nonce,uint256 deadline)");
    bytes32 private constant _TYPE_HASH = keccak256("EIP712Domain(string name,string version,uint256 chainId,address verifyingContract)");

    // Cache the domain separator as an immutable value, but also store the chain id that it corresponds to, in order to
    // invalidate the cached domain separator if the chain id changes.
    bytes32 private immutable _CACHED_DOMAIN_SEPARATOR;
    uint256 private immutable _CACHED_CHAIN_ID;

    bytes32 private immutable _HASHED_NAME;
    bytes32 private immutable _HASHED_VERSION;

    mapping (address => uint256) private _nonces;

    // --- PALMToken specific data ---

    // uint for use with SafeMath
    uint internal _1_MILLION = 1e24;    // 1e6 * 1e18 = 1e24

    uint internal immutable deploymentStartTime;

    address public immutable sPALMAddress;


    // --- Functions ---

    constructor
    (
        address _sPALMAddress,  // PalmFinance
        address _treasuryAddress,
        address _teamAddress
    )
    public
    {
        deploymentStartTime  = block.timestamp;

        sPALMAddress = _sPALMAddress;

        bytes32 hashedName = keccak256(bytes(_NAME));
        bytes32 hashedVersion = keccak256(bytes(_VERSION));

        _HASHED_NAME = hashedName;
        _HASHED_VERSION = hashedVersion;
        _CACHED_CHAIN_ID = _chainID();
        _CACHED_DOMAIN_SEPARATOR = _buildDomainSeparator(_TYPE_HASH, hashedName, hashedVersion);

        // --- Initial PALM allocations ---

        // Allocate 365 million for Palm Finance Treasury
        uint treasuryEntitlement = _1_MILLION.mul(365);
        _totalSupply = _totalSupply.add(treasuryEntitlement);
        _balances[_treasuryAddress] = _balances[_treasuryAddress].add(treasuryEntitlement);

        // Allocate 135 million for Palm Finance Team
        uint teamEntitlement = _1_MILLION.mul(135);
        _totalSupply = _totalSupply.add(teamEntitlement);
        _balances[_teamAddress] = _balances[_teamAddress].add(teamEntitlement);
    }


    // --- External functions ---

    function transfer(address recipient, uint256 amount) external override returns (bool) {
        _requireValidRecipient(recipient);

        // Otherwise, standard transfer functionality
        _transfer(msg.sender, recipient, amount);
        return true;
    }

    function approve(address spender, uint256 amount) external override returns (bool) {
        _approve(msg.sender, spender, amount);
        return true;
    }

    function transferFrom(address sender, address recipient, uint256 amount) external override returns (bool) {
        _requireValidRecipient(recipient);
        _transfer(sender, recipient, amount);
        _approve(sender, msg.sender, _allowances[sender][msg.sender].sub(amount, "PALM: transfer amount exceeds allowance"));
        return true;
    }

    function increaseAllowance(address spender, uint256 addedValue) external override returns (bool) {
        _approve(msg.sender, spender, _allowances[msg.sender][spender].add(addedValue));
        return true;
    }

    function decreaseAllowance(address spender, uint256 subtractedValue) external override returns (bool) {
        _approve(msg.sender, spender, _allowances[msg.sender][spender].sub(subtractedValue, "PALM: decreased allowance below zero"));
        return true;
    }

    function sendToSPALM(address _sender, uint256 _amount) external override {
        _requireCallerIsSPALM();
        _transfer(_sender, sPALMAddress, _amount);
    }


    // --- EIP 2612 functionality ---

    function domainSeparator() public view override returns (bytes32) {
        if (_chainID() == _CACHED_CHAIN_ID) {
            return _CACHED_DOMAIN_SEPARATOR;
        } else {
            return _buildDomainSeparator(_TYPE_HASH, _HASHED_NAME, _HASHED_VERSION);
        }
    }

    function permit
    (
        address owner,
        address spender,
        uint amount,
        uint deadline,
        uint8 v,
        bytes32 r,
        bytes32 s
    )
    external
    override
    {
        require(deadline >= now, 'PALM: expired deadline');
        bytes32 digest = keccak256(abi.encodePacked('\x19\x01',
            domainSeparator(), keccak256(abi.encode(
                _PERMIT_TYPEHASH, owner, spender, amount,
                _nonces[owner]++, deadline))));
        address recoveredAddress = ecrecover(digest, v, r, s);
        require(recoveredAddress == owner, 'PALM: invalid signature');
        _approve(owner, spender, amount);
    }

    function nonces(address owner) external view override returns (uint256) { // FOR EIP 2612
        return _nonces[owner];
    }


    // --- Internal functions ---

    function _chainID() private pure returns (uint256 chainID) {
        assembly {
            chainID := chainid()
        }
    }

    function _buildDomainSeparator(bytes32 typeHash, bytes32 name, bytes32 version) private view returns (bytes32) {
        return keccak256(abi.encode(typeHash, name, version, _chainID(), address(this)));
    }

    function _transfer(address sender, address recipient, uint256 amount) internal {
        require(sender != address(0), "PALM: transfer from the zero address");

        _balances[sender] = _balances[sender].sub(amount, "PALM: transfer amount exceeds balance");
        _balances[recipient] = _balances[recipient].add(amount);
        emit Transfer(sender, recipient, amount);
    }


    function _approve(address owner, address spender, uint256 amount) internal {
        _allowances[owner][spender] = amount;
        emit Approval(owner, spender, amount);
    }


    // --- 'require' functions ---

    function _requireValidRecipient(address _recipient) internal view {
        require(
            _recipient != address(this),
            "PALM: Cannot transfer tokens directly to the PALM token contract"
        );
    }

    function _requireCallerIsSPALM() internal view {
        require(msg.sender == sPALMAddress, "PALM: caller must be the SPALM contract");
    }


    // --- External View functions ---

    function balanceOf(address account) external view override returns (uint256) {
        return _balances[account];
    }

    function allowance(address owner, address spender) external view override returns (uint256) {
        return _allowances[owner][spender];
    }

    function totalSupply() external view override returns (uint256) {
        return _totalSupply;
    }

    function getDeploymentStartTime() external view override returns (uint256) {
        return deploymentStartTime;
    }

    function name() external view override returns (string memory) {
        return _NAME;
    }

    function symbol() external view override returns (string memory) {
        return _SYMBOL;
    }

    function decimals() external view override returns (uint8) {
        return _DECIMALS;
    }

    function version() external view override returns (string memory) {
        return _VERSION;
    }

    function permitTypeHash() external view override returns (bytes32) {
        return _PERMIT_TYPEHASH;
    }

}