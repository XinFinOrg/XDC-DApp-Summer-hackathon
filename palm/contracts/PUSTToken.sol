// SPDX-License-Identifier: UNLICENSED

pragma solidity 0.6.11;

import "./Interfaces/IPUSTToken.sol";
import "./Dependencies/SafeMath.sol";



/*
 *
 * Based upon OpenZeppelin's ERC20 contract:
 * https://github.com/OpenZeppelin/openzeppelin-contracts/blob/master/contracts/token/ERC20/ERC20.sol
 *
 * and their EIP2612 (ERC20Permit / ERC712) functionality:
 * https://github.com/OpenZeppelin/openzeppelin-contracts/blob/53516bc555a454862470e7860a9b5254db4d00f5/contracts/token/ERC20/ERC20Permit.sol
 *
 *
 * --- Functionality added specific to the PUSTToken ---
 *
 * 1) Transfer protection: blacklist of addresses that are invalid recipients (i.e. core Palm contracts) in external
 * transfer() and transferFrom() calls. The purpose is to protect users from losing tokens by mistakenly sending PUST directly to a Palm
 * core contract, when they should rather call the right function.
 *
 * 2) sendToPool() and returnFromPool(): functions callable only Palm core contracts, which move PUST tokens between Palm <-> user.
 */

contract PUSTToken is IPUSTToken {
    using SafeMath for uint256;

    uint256 private _totalSupply;
    string internal constant _NAME = "PUST Stablecoin";
    string internal constant _SYMBOL = "PUST";
    string internal constant _VERSION = "1";
    uint8 internal constant _DECIMALS = 18;
    bool canMint = true;

    // --- Data for EIP2612 ---

    // keccak256("Permit(address owner,address spender,uint256 value,uint256 nonce,uint256 deadline)");
    bytes32 private constant _PERMIT_TYPEHASH =
        0x6e71edae12b1b97f4d1f60370fef10105fa2faae0126114a169c64845d6126c9;
    //     keccak256("EIP712Domain(string name,string version,uint256 chainId,address verifyingContract)");
    bytes32 private constant _TYPE_HASH =
        0x8b73c3c69bb8fe3d512ecc4cf759cc79239f7b179b0ffacaa9a75d522b39400f;

    // Cache the domain separator as an immutable value, but also store the chain id that it corresponds to, in order to
    // invalidate the cached domain separator if the chain id changes.
    bytes32 private immutable _CACHED_DOMAIN_SEPARATOR;
    uint256 private immutable _CACHED_CHAIN_ID;

    bytes32 private immutable _HASHED_NAME;
    bytes32 private immutable _HASHED_VERSION;

    mapping(address => uint256) private _nonces;

    // User data for PUST token
    mapping(address => uint256) private _balances;
    mapping(address => mapping(address => uint256)) private _allowances;

    // --- Addresses ---
    address internal immutable assetPortfolioManagerAddress;
    address internal immutable assetPortfolioManagerLiquidationsAddress;
    address internal immutable assetPortfolioManagerRedemptionsAddress;
    address internal immutable stabilityPoolAddress;
    address internal immutable borrowerOperationsAddress;
    address internal immutable controllerAddress;
    mapping(address => bool) validMinters;

    modifier onlyController() {
        require(msg.sender == controllerAddress, "PUSTToken: Caller is not PalmController");
        _;
    }

    constructor(
        address _assetPortfolioManagerAddress,
        address _assetPortfolioManagerLiquidationsAddress,
        address _assetPortfolioManagerRedemptionsAddress,
        address _stabilityPoolAddress,
        address _borrowerOperationsAddress,
        address _controllerAddress
    ) public {
        assetPortfolioManagerAddress = _assetPortfolioManagerAddress;
        assetPortfolioManagerLiquidationsAddress = _assetPortfolioManagerLiquidationsAddress;
        assetPortfolioManagerRedemptionsAddress = _assetPortfolioManagerRedemptionsAddress;
        stabilityPoolAddress = _stabilityPoolAddress;
        borrowerOperationsAddress = _borrowerOperationsAddress;
        controllerAddress = _controllerAddress;

        validMinters[_borrowerOperationsAddress] = true;

        validMinters[msg.sender] = true;  // TODO FOR TEST

        bytes32 hashedName = keccak256(bytes(_NAME));
        bytes32 hashedVersion = keccak256(bytes(_VERSION));

        _HASHED_NAME = hashedName;
        _HASHED_VERSION = hashedVersion;
        _CACHED_CHAIN_ID = _chainID();
        _CACHED_DOMAIN_SEPARATOR = _buildDomainSeparator(_TYPE_HASH, hashedName, hashedVersion);
    }

    // --- Functions for intra-Palm calls ---

    function mint(address _account, uint256 _amount) external override {
        _requireCanMint();
        _requireValidMinter();
        _mint(_account, _amount);
    }

    function burn(address _account, uint256 _amount) external override {
        _requireCallerIsBOorAssetPortfolioMorSP();
        _burn(_account, _amount);
    }

    /** 
     * Function special to Palm which sends PUST directly to SP without approve
     */
    function sendToPool(
        address _sender,
        address _poolAddress,
        uint256 _amount
    ) external override {
        _requireCallerIsStabilityPool();
        _transfer(_sender, _poolAddress, _amount);
    }

    /** 
     * Function special to Palm which sends PUST directly from pool back to a user
     */
    function returnFromPool(
        address _poolAddress,
        address _receiver,
        uint256 _amount
    ) external override {
        _requireCallerIsTMLorSP();
        _transfer(_poolAddress, _receiver, _amount);
    }

    // --- External functions ---

    function totalSupply() external view override returns (uint256) {
        return _totalSupply;
    }

    function balanceOf(address account) external view override returns (uint256) {
        return _balances[account];
    }

    function transfer(address recipient, uint256 amount) external override returns (bool) {
        _requireValidRecipient(recipient);
        _transfer(msg.sender, recipient, amount);
        return true;
    }

    function allowance(address owner, address spender) external view override returns (uint256) {
        return _allowances[owner][spender];
    }

    function approve(address spender, uint256 amount) external override returns (bool) {
        _approve(msg.sender, spender, amount);
        return true;
    }

    function transferFrom(
        address sender,
        address recipient,
        uint256 amount
    ) external override returns (bool) {
        _requireValidRecipient(recipient);

        _transfer(sender, recipient, amount);
        _approve(
            sender,
            msg.sender,
            _allowances[sender][msg.sender].sub(amount, "ERC20: transfer amount exceeds allowance")
        );
        return true;
    }

    function increaseAllowance(address spender, uint256 addedValue)
        external
        override
        returns (bool)
    {
        _approve(msg.sender, spender, _allowances[msg.sender][spender].add(addedValue));
        return true;
    }

    function decreaseAllowance(address spender, uint256 subtractedValue)
        external
        override
        returns (bool)
    {
        _approve(
            msg.sender,
            spender,
            _allowances[msg.sender][spender].sub(
                subtractedValue,
                "ERC20: decreased allowance below zero"
            )
        );
        return true;
    }

    // --- EIP 2612 Functionality ---

    function domainSeparator() public view override returns (bytes32) {
        if (_chainID() == _CACHED_CHAIN_ID) {
            return _CACHED_DOMAIN_SEPARATOR;
        } else {
            return _buildDomainSeparator(_TYPE_HASH, _HASHED_NAME, _HASHED_VERSION);
        }
    }

    function permit(
        address owner,
        address spender,
        uint256 amount,
        uint256 deadline,
        uint8 v,
        bytes32 r,
        bytes32 s
    ) external override {
        require(deadline >= block.timestamp, "PUST: expired deadline");
        bytes32 digest = keccak256(
            abi.encodePacked(
                "\x19\x01",
                domainSeparator(),
                keccak256(
                    abi.encode(_PERMIT_TYPEHASH, owner, spender, amount, _nonces[owner]++, deadline)
                )
            )
        );
        address recoveredAddress = ecrecover(digest, v, r, s);
        require(recoveredAddress == owner, "PUST: invalid signature");
        _approve(owner, spender, amount);
    }

    function nonces(address owner) external view override returns (uint256) {
        // FOR EIP 2612
        return _nonces[owner];
    }

    // --- Internal operations ---

    function _chainID() private pure returns (uint256 chainID) {
        assembly {
            chainID := chainid()
        }
    }

    function _buildDomainSeparator(
        bytes32 typeHash,
        bytes32 name,
        bytes32 version
    ) private view returns (bytes32) {
        return keccak256(abi.encode(typeHash, name, version, _chainID(), address(this)));
    }

    // --- Internal operations ---
    // Warning: sanity checks (for sender and recipient) should have been done before calling these internal functions

    function _transfer(
        address sender,
        address recipient,
        uint256 amount
    ) internal {
        require(sender != address(0), "_transfer: sender is address(0)");
        require(recipient != address(0), "_transfer: recipient is 0address");

        _balances[sender] = _balances[sender].sub(amount, "ERC20: transfer amount > balance");
        _balances[recipient] = _balances[recipient].add(amount);
        emit Transfer(sender, recipient, amount);
    }

    function _mint(address account, uint256 amount) internal {
        require(account != address(0), "_mint: account is address(0)");

        _totalSupply = _totalSupply.add(amount);
        _balances[account] = _balances[account] + amount;
        emit Transfer(address(0), account, amount);
    }

    function _burn(address account, uint256 amount) internal {
        require(account != address(0), "_burn: account is address(0)");

        _balances[account] = _balances[account].sub(amount, "ERC20: burn amount > balance");
        _totalSupply = _totalSupply - amount; // can't underflow since indiv balance didn't
        emit Transfer(account, address(0), amount);
    }

    function _approve(
        address owner,
        address spender,
        uint256 amount
    ) internal {
        require(owner != address(0), "_approve: owner is address(0)");
        require(spender != address(0), "_approve: spender is address(0)");

        _allowances[owner][spender] = amount;
        emit Approval(owner, spender, amount);
    }

    function addValidMinter(address _newMinter) external override onlyController {
        validMinters[_newMinter] = true;
    }

    function removeValidMinter(address _minter) external override onlyController {
        validMinters[_minter] = false;
    }

    function updateMinting(bool _canMint) external override onlyController {
        canMint = _canMint;
    }

    function _requireCanMint() internal view {
        require(canMint);
    }

    // --- 'require' functions ---

    function _requireValidRecipient(address _recipient) internal view {
        require(
            _recipient != address(this),
            "PUST: Cannot transfer tokens directly to the PUST token contract"
        );
    }

    function _requireValidMinter() internal view {
        require(validMinters[msg.sender], "PUSTToken: Caller is not Valid Minter");
    }

    function _requireCallerIsBOorAssetPortfolioMorSP() internal view {
        require(
            msg.sender == borrowerOperationsAddress ||
                msg.sender == assetPortfolioManagerAddress ||
                msg.sender == stabilityPoolAddress ||
                msg.sender == assetPortfolioManagerRedemptionsAddress,
            "PUST: Caller is neither BorrowerOperations nor AssetPortfolioManager nor StabilityPool"
        );
    }

    function _requireCallerIsStabilityPool() internal view {
        require(msg.sender == stabilityPoolAddress, "PUST: Caller is not the StabilityPool");
    }

    function _requireCallerIsTMLorSP() internal view {
        require(
            msg.sender == stabilityPoolAddress || msg.sender == assetPortfolioManagerLiquidationsAddress,
            "PUST: Caller is neither AssetPortfolioManagerLiquidator nor StabilityPool"
        );
    }

    // --- Optional functions ---

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