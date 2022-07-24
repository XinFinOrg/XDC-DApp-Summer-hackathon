pragma solidity ^0.8.0;

abstract contract DiscountList is AdminControl {

	struct discountInfo {
		uint256 discount;
		uint256 timestamp
    }
	
	bool public _isDiscountListActive = false;
	
    mapping(address => discountInfo) public _discountList;

    function setDiscountListActive() external onlyOwner {
        _isDiscountListActive = !_isDiscountListActive;
    }

    function addDiscountLists(address[] calldata accounts, uint256[] memory discounts, uint256[] memory timestamps) external onlyMinterController {
        for (uint256 i = 0; i < accounts.length; i++) 
		{
			_discountList[accounts[i]] = discountInfo(
			  discounts[i],
			  timestamps[i]
			);
        }
    }
	
	function addDiscountList(address _account, uint256 _discount, uint256 _timestamp) external onlyMinterController {
        _discountList[_account] = discountInfo(
			  _discount,
			  _timestamp
		);
    }
	
	function getDiscount(address account) external view returns (storage) {
		discountInfo storage _discountInfo = _discountList[account];
        return _discountInfo;
    }
	
	function chkInDiscountList(address account) external view returns (bool) {
		discountInfo storage _discountInfo = _discountList[account];
        return _discountInfo.accounts > 0;
    }
}
