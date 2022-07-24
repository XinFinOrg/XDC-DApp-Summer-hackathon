abstract contract BookingList is AdminControl {

    mapping(bytes => string) public _bookingList;
	
	bool public _isBookingListActive = false;

    function setBookingListActive() external onlyOwner {
        _isBookingListActive = !_isBookingListActive;
    }

    function addBookingLists(string[] calldata names) external onlyMinterController {
        for (uint256 i = 0; i < names.length; i++) 
		{
            _bookingList[bytes(names[i])] = names[i];
        }
    }
	
	function addBookingList(string calldata name) external onlyMinterController {
        _bookingList[bytes(name)] = name;
    }
	
	function removeBookingList(string calldata name) external onlyMinterController {
		delete _bookingList[bytes(name)];
    }
	
	function chkInBookingList(string calldata name) external view returns (bool) {
		string memory _name = _bookingList[bytes(name)];
        return bytes(_name).length > 0;
    }
}