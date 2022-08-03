pragma solidity ^0.8.0;

contract AdminControl is Ownable {

    using Roles for Roles.Role;

    Roles.Role private _controllerRoles;


    modifier onlyMinterController() {
      require (
        hasRole(msg.sender), 
        "AdminControl: sender must has minting role"
      );
      _;
    }

    modifier onlyMinter() {
      require (
        hasRole(msg.sender), 
        "AdminControl: sender must has minting role"
      );
      _;
    }

    constructor() {
      _grantRole(msg.sender);
    }

    function grantMinterRole(address account) public onlyOwner {
      _grantRole(account);
    }

    function revokeMinterRole (address account) public  onlyOwner {
      _revokeRole(account);
    }

    function hasRole(address account) public view returns (bool) {
      return _controllerRoles.has(account);
    }
    
    function _grantRole (address account) internal {
      _controllerRoles.add(account);
    }

    function _revokeRole (address account) internal {
      _controllerRoles.remove(account);
    }

}
