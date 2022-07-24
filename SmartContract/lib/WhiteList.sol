pragma solidity ^0.8.0;

abstract contract WhiteList is AdminControl {

    mapping(address => uint256) public _whiteList;
	
	bool public isWhiteListActive = false;

    function setWhiteListActive(bool _isWhiteListActive) external onlyOwner {
        isWhiteListActive = _isWhiteListActive;
    }

    function addWhiteLists(address[] calldata accounts, uint256 numbers) external onlyMinterController {
        for (uint256 i = 0; i < accounts.length; i++) 
		{
            _whiteList[accounts[i]] = numbers;
        }
    }
	
	function addWhiteList(address account, uint256 numbers) external onlyMinterController {
        _whiteList[account] = numbers;
    }
	
	function numberInWhiteList(address addr) external view returns (uint256) {
        return _whiteList[addr];
    }
	
	function chkInWhiteList(address addr) external view returns (bool) {
        return _whiteList[addr] > 0;
    }
}
