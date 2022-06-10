// SPDX-License-Identifier: UNLICENSED

pragma solidity 0.6.11;

import "./Interfaces/ISortedAssetPortfolios.sol";
import "./Dependencies/SafeMath.sol";
import "./Dependencies/PalmMath.sol";



/**
 * Some notes from Liquity:
 * @notice A sorted doubly linked list with nodes sorted in descending order.
 *
 * Nodes map to active AssetPortfolios in the system - the ID property is the address of a AssetPortfolio owner.
 * Nodes are ordered according to their current individual collateral ratio (ICR),
 *
 * The list optionally accepts insert position hints.
 *
 * The list relies on the fact that liquidation events preserve ordering: a liquidation decreases the ICRs of all active AssetPortfolios,
 * but maintains their order. A node inserted based on current ICR will maintain the correct position,
 * relative to it's peers, as rewards accumulate, as long as it's raw collateral and debt have not changed.
 * Thus, Nodes remain sorted by current ICR.
 *
 * Nodes need only be re-inserted upon a AssetPortfolio operation - when the owner adds or removes collateral or debt
 * to their position.
 *
 * The list is a modification of the following audited SortedDoublyLinkedList:
 * https://github.com/livepeer/protocol/blob/master/contracts/libraries/SortedDoublyLL.sol
 *
 *
 * Changes made compared to the Liquity implementation:
 *
 * - Keys have been removed from nodes
 *
 * - Ordering checks for insertion are performed by comparing an boostedAICR argument to the current boostedAICR, calculated at runtime.
 *   The list relies on the property that ordering by boostedAICR is maintained as the Coll:USD price varies.
 *
 * - Public functions with parameters have been made internal to save gas, and given an external wrapper function for external access
 *
 * Changes made in Palm Finance implementation:
 * Since the nodes are no longer just reliant on the nominal ICR which is just amount of ETH / debt, we now have to use the boostedAICR based
 * on the RVC + boost value of the node. This changes with any price change, as the composition of any assetPortfolio does not stay constant. Therefore
 * the list can easily become stale. This is a compromise that we had to make due to it being too expensive gas wise to keep the list
 * actually sorted by current boostedAICR, as this can change each block. Instead, we keep it ordered by oldBoostedAICR, and it is instead updated through
 * an external function in AssetPortfolioManager.sol, updateAssetPortfolios(), and can be called by anyone. This will essentially just update the oldBoostedAICR and re-insert it
 * into the list. It always remains sorted by oldBoostedAICR. To then perform redemptions properly, we just allow redemptions to occur for any
 * assetPortfolio in order of the stale list. However, the redemption amount is in dollar terms so people will always still keep their value, just
 * will lose exposure to the asset.
 *
 * AICR is defined as the Recovery ICR, which is the sum(collaterals * recovery ratio) / total debt
 * Boosted AICR is defined as the AICR + Boost. (Boost defined below)
 * This list is sorted by boostedAICR so that redemptions take from assetPortfolios which have a relatively lower recovery ratio adjusted ratio. If we sorted
 * by ICR, then the redemptions would always take from the lowest but actually relatively safe assetPortfolios, such as the ones with purely
 * stablecoin collateral. Since more resistant assetPortfolios will have higher boostedAICR, this will make them less likely to be redeemed against.
 *
 * Boost is defined as the extra factor added to the AICR. In order to avoid users paying large fees due to extra leverage and then immediately
 * getting redeemed, they gain an additional factor which is added to the AICR. Depending on the fee % * leverage, and the global boost factor,
 * they will have a decayed additional boost. This decays according to the boostMinuteDecayFactor, which by default has a half life of 5 days.
 *
 * SortedAssetPortfolios is also used to check if there is a assetPortfolio eligible for liquidation for SP Withdrawal. Technically it can be the case
 * that there is an undercollateralized assetPortfolio which has boostedAICR > 110%, and since it is sorted by boostedAICR it may not be at the bottom.
 * However, this is inherently because these assets are deemed safer, so it is unlikely that there will be an undercollateralized assetPortfolio with
 * boostedAICR > 110% and no assetPortfolios without a high boostedAICR which are also not undercollateralized. If the collateral dropped in value while being
 * hedged with some stablecoins in the assetPortfolio as well, it is likely that there is another undercollateralized assetPortfolio.
 *
 * As an additional countermeasure, we are adding a under-collateralized assetPortfolios list. This list is intended to keep track of if there are any
 * under-collateralized assetPortfolios in the event of a large usage and gas spike. Since the list is sorted by boostedAICR, it is possible that there are
 * under-collateralized assetPortfolios which are not at the bottom, while the bottom of the list is a assetPortfolio which has a boostedAICR > 110%. So, this list exists
 * to not break the invariant for knowing if there is a under-collateralized assetPortfolio in order to perform a SP withdrawal. It will be updated by
 * external callers and if the ICR calculated is < 110%, then it will be added to the list. There will be another external function to
 * remove it from the list. Palm Finance bots will be performing the updating, and since SP withdrawal is the only action that is dependant
 * on this, it is not a problem if it is slow or lagged to clear the list entirely. The SP Withdrawal function will just check the length
 * of the UnderCollateralizedAssetPortfolios list and see if it is more than 0.
 */

contract SortedAssetPortfolios is ISortedAssetPortfolios {
    using SafeMath for uint256;

    bytes32 public constant NAME = "SortedAssetPortfolios";
    uint256 internal constant DECIMAL_PRECISION = 1e18;

    event NodeAdded(address _id, uint256 _AICR);
    event NodeRemoved(address _id);
    event UnderCollateralizedAssetPortfolioAdded(address _id);
    event UnderCollateralizedAssetPortfolioRemoved(address _id);

    address internal borrowerOperationsAddress;
    address internal assetPortfolioManagerRedemptionsAddress;
    address internal assetPortfolioManagerAddress;
    address internal controllerAddress;

    // Initiallly 0 and can be set further through controller.
    // Multiplied by passed in fee factors to scale the fee percentage.
    uint256 public globalBoostFactor;

    /*
     * Half-life of 5d = 120h. 120h = 7200 min
     * (1/2) = d^7200 => d = (1/2)^(1/7200)
     * d is equal to boostMinuteDecayFactor
     */
    uint256 public boostMinuteDecayFactor;

    // Information for a node in the list
    struct Node {
        bool exists;
        address nextId; // Id of next node (smaller boostedAICR) in the list
        address prevId; // Id of previous node (larger boostedAICR) in the list
        uint256 oldBoostedAICR; // boostedAICR of the node last time it was updated. List is always in order
        // in terms of oldBoostedAICR .
        uint256 boost; // Boost factor which was previously added to the boostedAICR when inserted
        uint256 timeSinceBoostUpdated; // Time since the boost factor was last updated
    }

    // Information for the list
    struct Data {
        address head; // Head of the list. Also the node in the list with the largest boostedAICR
        address tail; // Tail of the list. Also the node in the list with the smallest boostedAICR
        uint256 maxSize; // Maximum size of the list
        uint256 size; // Current size of the list
        mapping(address => Node) nodes; // Track the corresponding ids for each node in the list
    }

    Data public data;

    mapping(address => bool) public underCollateralizedAssetPortfolios;
    uint256 public underCollateralizedAssetPortfoliosSize;

    // --- Dependency setters ---
    bool private addressSet;
    function setParams(
        uint256 _size,
        address _assetPortfolioManagerAddress,
        address _borrowerOperationsAddress,
        address _assetPortfolioManagerRedemptionsAddress,
        address _palmControllerAddress
    ) external override {
        require(addressSet == false, "Addresses already set");
        addressSet = true;
        require(_size != 0, "SortedAssetPortfolios: Size can't be zero");

        data.maxSize = _size;
        boostMinuteDecayFactor = 999903734192105837;
        assetPortfolioManagerAddress = _assetPortfolioManagerAddress;
        borrowerOperationsAddress = _borrowerOperationsAddress;
        assetPortfolioManagerRedemptionsAddress = _assetPortfolioManagerRedemptionsAddress;
        controllerAddress = _palmControllerAddress;
    }

    // --- Functions relating to insertion, deletion, reinsertion ---

    /**
     * @notice Add a node to the list
     * @param _id Node's id
     * @param _AICR Node's _AICR at time of inserting
     * @param _prevId Id of previous node for the insert position
     * @param _nextId Id of next node for the insert position
     * @param _feeAsPercentOfTotal The fee as a percentage of the total VC in when inserting a new assetPortfolio.
     */
    function insert(
        address _id,
        uint256 _AICR,
        address _prevId,
        address _nextId,
        uint256 _feeAsPercentOfTotal
    ) external override {
        _requireCallerIsBO();
        // Calculate new boost amount using fee as percent of total, with global boost factor.
        uint256 newBoostAmount = (
            _feeAsPercentOfTotal.mul(globalBoostFactor).div(DECIMAL_PRECISION)
        );
        _insert(_id, _AICR, _prevId, _nextId, newBoostAmount);
    }

    /**
     * @notice Add a node to the list, which may or may not have just been removed.
     * @param _id Node's id
     * @param _AICR Node's _AICR at time of inserting
     * @param _prevId Id of previous node for the insert position
     * @param _nextId Id of next node for the insert position
     * @param _newBoostAmount Boost amount which has been calculated with previous data or is
     *   completely new, depending on whether it is a reinsert or not. It will be used as the boost
     *   param for the node reinsertion.
     */
    function _insert(
        address _id,
        uint256 _AICR,
        address _prevId,
        address _nextId,
        uint256 _newBoostAmount
    ) internal {
        // List must not be full
        require(!isFull(), "SortedAssetPortfolios: List is full");
        // List must not already contain node
        require(!contains(_id), "SortedAssetPortfolios: duplicate node");
        // Node id must not be null
        require(_id != address(0), "SortedAssetPortfolios: Id cannot be zero");
        // AICR must be non-zero
        require(_AICR != 0, "SortedAssetPortfolios: AICR must be (+)");

        // Calculate boostedAICR as AICR + decayed boost
        uint256 boostedAICR = _AICR.add(_newBoostAmount);
        address prevId = _prevId;
        address nextId = _nextId;
        if (!_validInsertPosition(boostedAICR, prevId, nextId)) {
            // Sender's hint was not a valid insert position
            // Use sender's hint to find a valid insert position
            (prevId, nextId) = _findInsertPosition(boostedAICR, prevId, nextId);
        }

        data.nodes[_id].exists = true;
        if (prevId == address(0) && nextId == address(0)) {
            // Insert as head and tail
            data.head = _id;
            data.tail = _id;
        } else if (prevId == address(0)) {
            // Insert before `prevId` as the head
            data.nodes[_id].nextId = data.head;
            data.nodes[data.head].prevId = _id;
            data.head = _id;
        } else if (nextId == address(0)) {
            // Insert after `nextId` as the tail
            data.nodes[_id].prevId = data.tail;
            data.nodes[data.tail].nextId = _id;
            data.tail = _id;
        } else {
            // Insert at insert position between `prevId` and `nextId`
            data.nodes[_id].nextId = nextId;
            data.nodes[_id].prevId = prevId;
            data.nodes[prevId].nextId = _id;
            data.nodes[nextId].prevId = _id;
        }

        // Update node's boostedAICR
        data.nodes[_id].oldBoostedAICR = boostedAICR;
        data.nodes[_id].boost = _newBoostAmount;
        data.nodes[_id].timeSinceBoostUpdated = block.timestamp;

        data.size = data.size.add(1);
        emit NodeAdded(_id, boostedAICR);
    }

    /**
     * @notice Remove a node to the list. Used when purely removing or when reinserting.
     * @param _id Node's id
     */
    function remove(address _id) external override {
        _requireCallerIsAssetPortfolioManager();
        _remove(_id);
    }

    /**
     * @notice Remove a node from the list. Used when purely removing or when reinserting.
     * @param _id Node's id
     */
    function _remove(address _id) internal {
        // List must contain the node
        require(contains(_id), "SortedAssetPortfolios: Id not found");

        if (data.size > 1) {
            // List contains more than a single node
            if (_id == data.head) {
                // The removed node is the head
                // Set head to next node
                data.head = data.nodes[_id].nextId;
                // Set prev pointer of new head to null
                data.nodes[data.head].prevId = address(0);
            } else if (_id == data.tail) {
                // The removed node is the tail
                // Set tail to previous node
                data.tail = data.nodes[_id].prevId;
                // Set next pointer of new tail to null
                data.nodes[data.tail].nextId = address(0);
            } else {
                // The removed node is neither the head nor the tail
                // Set next pointer of previous node to the next node
                data.nodes[data.nodes[_id].prevId].nextId = data.nodes[_id].nextId;
                // Set prev pointer of next node to the previous node
                data.nodes[data.nodes[_id].nextId].prevId = data.nodes[_id].prevId;
            }
        } else {
            // List contains a single node
            // Set the head and tail to null
            data.head = address(0);
            data.tail = address(0);
        }

        delete data.nodes[_id];
        data.size = data.size.sub(1);
        emit NodeRemoved(_id);
    }

    /**
     * @notice Re-insert the node at a new position, based on its new boostedAICR
     * @dev Does not add additional boost and is called by redemption reinsertion. Only decays the existing boost.
     * @param _id Node's id
     * @param _newAICR Node's new AICR
     * @param _prevId Id of previous node for the new insert position
     * @param _nextId Id of next node for the new insert position
     */
    function reInsert(
        address _id,
        uint256 _newAICR,
        address _prevId,
        address _nextId
    ) external override {
        _requireCallerIsTM();
        _reInsert(_id, _newAICR, _prevId, _nextId);
    }

    /**
     * @notice Re-insert the node at a new position, based on its new boostedAICR
     * @dev Does not add additional boost and is called by redemption reinsertion, or TM manual reinsertion.
     *   Only decays the existing boost.
     * @param _id Node's id
     * @param _newAICR Node's new AICR
     * @param _prevId Id of previous node for the new insert position
     * @param _nextId Id of next node for the new insert position
     */
    function _reInsert(
        address _id,
        uint256 _newAICR,
        address _prevId,
        address _nextId
    ) internal {
        // List must contain the node
        require(contains(_id), "SortedAssetPortfolios: Id not found");
        // AICR must be non-zero
        require(_newAICR != 0, "SortedAssetPortfolios: AICR != 0");

        // Does not add additional boost and is called by redemption reinsertion. Only decays the existing boost.
        uint256 decayedLastBoost = _calculateDecayedBoost(
            data.nodes[_id].boost,
            data.nodes[_id].timeSinceBoostUpdated
        );
        // Remove node from the list
        _remove(_id);

        _insert(_id, _newAICR, _prevId, _nextId, decayedLastBoost);
    }

    /**
     * @notice Reinserts the assetPortfolio in adjustAssetPortfolio with and weight the new boost factor with the old boost and VC calculation
     * @param _id Node's id
     * @param _newAICR Node's new AICR with old VC + new VC In - new VC out
     * @param _prevId Id of previous node for the new insert position
     * @param _nextId Id of next node for the new insert position
     * @param _feeAsPercentOfAddedVC Fee as percent of the VC added in this tx
     * @param _addedVCIn amount VC added in this tx
     * @param _VCBeforeAdjustment amount VC before this tx, what to scale the old decayed boost by
     */
    function reInsertWithNewBoost(
        address _id,
        uint256 _newAICR,
        address _prevId,
        address _nextId,
        uint256 _feeAsPercentOfAddedVC,
        uint256 _addedVCIn,
        uint256 _VCBeforeAdjustment
    ) external override {
        _requireCallerIsBO();
        // List must contain the node
        require(contains(_id), "SortedAssetPortfolios: Id not found");
        // AICR must be non-zero
        require(_newAICR != 0, "SortedAssetPortfolios: AICR != 0");

        // Calculate decayed last boost based on previous assetPortfolio information.
        uint256 decayedLastBoost = _calculateDecayedBoost(
            data.nodes[_id].boost,
            data.nodes[_id].timeSinceBoostUpdated
        );
        // Remove node from the list
        _remove(_id);

        // Weight new deposit compared to old boost deposit amount.
        // (OldBoost * Previous VC) + (NewBoost * Added VC)
        // divided by new VC
        uint256 newBoostFactor = _feeAsPercentOfAddedVC.mul(globalBoostFactor).div(
            DECIMAL_PRECISION
        );
        uint256 newBoostAmount = (
            decayedLastBoost.mul(_VCBeforeAdjustment).add(newBoostFactor.mul(_addedVCIn))
        ).div(_VCBeforeAdjustment.add(_addedVCIn));

        _insert(_id, _newAICR, _prevId, _nextId, newBoostAmount);
    }

    /**
     * @notice Re-insert the node at a new position, based on its new boostedAICR
     * @param _ids IDs to reinsert
     * @param _newAICRs new AICRs for all IDs
     * @param _prevIds Ids of previous node for the new insert position
     * @param _nextIds Ids of next node for the new insert position
     */
    function reInsertMany(
        address[] memory _ids,
        uint256[] memory _newAICRs,
        address[] memory _prevIds,
        address[] memory _nextIds
    ) external override {
        _requireCallerIsTM();
        uint256 _idsLength = _ids.length;
        for (uint256 i; i < _idsLength; ++i) {
            _reInsert(_ids[i], _newAICRs[i], _prevIds[i], _nextIds[i]);
        }
    }

    /**
     * @notice Decays the boost based on last time updated, based on boost minute decay factor
     * @param _originalBoost Boost which has not been decayed stored at last time of update
     * @param _timeSinceBoostUpdated Time since last time boost was updated
     */
    function _calculateDecayedBoost(uint256 _originalBoost, uint256 _timeSinceBoostUpdated)
        internal
        view
        returns (uint256)
    {
        uint256 minutesPassed = (block.timestamp.sub(_timeSinceBoostUpdated)).div(60); // Div by 60 to convert to minutes
        uint256 decayFactor = PalmMath._decPow(boostMinuteDecayFactor, minutesPassed);
        return _originalBoost.mul(decayFactor).div(DECIMAL_PRECISION);
    }

    // --- Under-Collateralized AssetPortfolios Functions ---

    /**
     * @notice Update a particular assetPortfolio address in the underCollateralizedAssetPortfolios list
     * @dev This function is called by the UpdateAssetPortfolios bot and if there are many
     * under-collateralized assetPortfolios this will add it to the
     * list so that no SP withdrawal can happen.
     * If the assetPortfolio is no longer under-collateralized then this function can be used to
     * remove it from the list.
     * @param _id AssetPortfolio's id
     * @param _isUnderCollateralized True if the assetPortfolio is under-collateralized,
     *          using ICR calculated from the call from TM
     */
    function updateUnderCollateralizedAssetPortfolio(address _id, bool _isUnderCollateralized) external override {
        _requireCallerIsAssetPortfolioManager();
        require(contains(_id), "SortedAssetPortfolios: Id not found");
        if (_isUnderCollateralized) {
            // If under-collateralized and marked not under-collateralized,
            // add to list
            if (!underCollateralizedAssetPortfolios[_id]) {
                _insertUnderCollateralizedAssetPortfolio(_id);
            }
        } else {
            // If not under-collateralized and marked under-collateralized,
            // remove from the list
            if (underCollateralizedAssetPortfolios[_id]) {
                _removeUnderCollateralizedAssetPortfolio(_id);
            }
        }
    }

    /**
     * @notice Add a node to the under-collateralized assetPortfolios list and increase the size
     */
    function _insertUnderCollateralizedAssetPortfolio(address _id) internal {
        underCollateralizedAssetPortfoliosSize = underCollateralizedAssetPortfoliosSize.add(1);
        underCollateralizedAssetPortfolios[_id] = true;
        emit UnderCollateralizedAssetPortfolioAdded(_id);
    }

    /**
     * @notice Remove a node from the under-collateralized assetPortfolios list and decrease the size
     */
    function _removeUnderCollateralizedAssetPortfolio(address _id) internal {
        underCollateralizedAssetPortfoliosSize = underCollateralizedAssetPortfoliosSize.sub(1);
        underCollateralizedAssetPortfolios[_id] = false;
        emit UnderCollateralizedAssetPortfolioRemoved(_id);
    }

    // --- Functions relating to finding insert position ---

    /**
     * @notice Check if a pair of nodes is a valid insertion point for a new node with the given boostedAICR
     * @param _boostedAICR Node's boostedAICR
     * @param _prevId Id of previous node for the insert position
     * @param _nextId Id of next node for the insert position
     * @return True if insert positon is valid, False if insert position is not valid
     */
    function validInsertPosition(
        uint256 _boostedAICR,
        address _prevId,
        address _nextId
    ) external view override returns (bool) {
        return _validInsertPosition(_boostedAICR, _prevId, _nextId);
    }

    /**
     * @notice Check if a pair of nodes is a valid insertion point for a new node with the given boosted AICR
     * @dev Instead of calculating current boosted AICR using assetPortfolio manager, we use oldBoostedAICR values.
     * @param _prevId Id of previous node for the insert position
     * @param _nextId Id of next node for the insert position
     */
    function _validInsertPosition(
        uint256 _boostedAICR,
        address _prevId,
        address _nextId
    ) internal view returns (bool) {
        if (_prevId == address(0) && _nextId == address(0)) {
            // `(null, null)` is a valid insert position if the list is empty
            return isEmpty();
        } else if (_prevId == address(0)) {
            // `(null, _nextId)` is a valid insert position if `_nextId` is the head of the list
            return data.head == _nextId && _boostedAICR >= data.nodes[_nextId].oldBoostedAICR;
        } else if (_nextId == address(0)) {
            // `(_prevId, null)` is a valid insert position if `_prevId` is the tail of the list
            return data.tail == _prevId && _boostedAICR <= data.nodes[_prevId].oldBoostedAICR;
        } else {
            // `(_prevId, _nextId)` is a valid insert position if they are adjacent nodes and `_boostedAICR` falls between the two nodes' AICRs
            return
                data.nodes[_prevId].nextId == _nextId &&
                data.nodes[_prevId].oldBoostedAICR >= _boostedAICR &&
                _boostedAICR >= data.nodes[_nextId].oldBoostedAICR;
        }
    }

    /**
     * @notice Descend the list (larger AICRs to smaller AICRs) to find a valid insert position
     * @param _boostedAICR Node's boostedAICR
     * @param _startId Id of node to start descending the list from
     */
    function _descendList(uint256 _boostedAICR, address _startId)
        internal
        view
        returns (address, address)
    {
        // If `_startId` is the head, check if the insert position is before the head
        if (data.head == _startId && _boostedAICR >= data.nodes[_startId].oldBoostedAICR) {
            return (address(0), _startId);
        }

        address prevId = _startId;
        address nextId = data.nodes[prevId].nextId;

        // Descend the list until we reach the end or until we find a valid insert position
        while (prevId != address(0) && !_validInsertPosition(_boostedAICR, prevId, nextId)) {
            prevId = data.nodes[prevId].nextId;
            nextId = data.nodes[prevId].nextId;
        }

        return (prevId, nextId);
    }

    /**
     * @notice Ascend the list (smaller AICRs to larger AICRs) to find a valid insert position
     * @param _boostedAICR Node's boosted AICR
     * @param _startId Id of node to start ascending the list from
     */
    function _ascendList(uint256 _boostedAICR, address _startId)
        internal
        view
        returns (address, address)
    {
        // If `_startId` is the tail, check if the insert position is after the tail
        if (data.tail == _startId && _boostedAICR <= data.nodes[_startId].oldBoostedAICR) {
            return (_startId, address(0));
        }

        address nextId = _startId;
        address prevId = data.nodes[nextId].prevId;

        // Ascend the list until we reach the end or until we find a valid insertion point
        while (nextId != address(0) && !_validInsertPosition(_boostedAICR, prevId, nextId)) {
            nextId = data.nodes[nextId].prevId;
            prevId = data.nodes[nextId].prevId;
        }

        return (prevId, nextId);
    }

    /**
     * @notice Find the insert position for a new node with the given boosted AICR
     * @param _boostedAICR Node's boostedAICR
     * @param _prevId Id of previous node for the insert position
     * @param _nextId Id of next node for the insert position
     */
    function findInsertPosition(
        uint256 _boostedAICR,
        address _prevId,
        address _nextId
    ) external view override returns (address, address) {
        return _findInsertPosition(_boostedAICR, _prevId, _nextId);
    }

    function _findInsertPosition(
        uint256 _boostedAICR,
        address _prevId,
        address _nextId
    ) internal view returns (address, address) {
        address prevId = _prevId;
        address nextId = _nextId;

        if (prevId != address(0)) {
            if (!contains(prevId) || _boostedAICR > data.nodes[prevId].oldBoostedAICR) {
                // `prevId` does not exist anymore or now has a smaller boosted AICR than the given boosted AICR
                prevId = address(0);
            }
        }

        if (nextId != address(0)) {
            if (!contains(nextId) || _boostedAICR < data.nodes[nextId].oldBoostedAICR) {
                // `nextId` does not exist anymore or now has a larger boosted AICR than the given boosted AICR
                nextId = address(0);
            }
        }

        if (prevId == address(0) && nextId == address(0)) {
            // No hint - descend list starting from head
            return _descendList(_boostedAICR, data.head);
        } else if (prevId == address(0)) {
            // No `prevId` for hint - ascend list starting from `nextId`
            return _ascendList(_boostedAICR, nextId);
        } else if (nextId == address(0)) {
            // No `nextId` for hint - descend list starting from `prevId`
            return _descendList(_boostedAICR, prevId);
        } else {
            // Descend list starting from `prevId`
            return _descendList(_boostedAICR, prevId);
        }
    }

    /**
     * @notice change the boost minute decay factor from the controller timelock.
     *   Half-life of 5d = 120h. 120h = 7200 min
     *   (1/2) = d^7200 => d = (1/2)^(1/7200)
     *   d is equal to boostMinuteDecayFactor
     */
    function changeBoostMinuteDecayFactor(uint256 _newBoostMinuteDecayFactor) external override {
        _requireCallerIsPalmController();
        boostMinuteDecayFactor = _newBoostMinuteDecayFactor;
    }

    /**
     * @notice change the global boost multiplier from the controller timelock.
     *   Initiallly 0 and can be set further through controller.
     *   Multiplied by passed in fee factors to scale the fee percentage
     */
    function changeGlobalBoostMultiplier(uint256 _newGlobalBoostMultiplier) external override {
        _requireCallerIsPalmController();
        globalBoostFactor = _newGlobalBoostMultiplier;
    }

    // --- Getter functions ---

    /**
     * @notice Checks if the list contains a node
     */
    function contains(address _id) public view override returns (bool) {
        return data.nodes[_id].exists;
    }

    /**
     * @notice Checks if list is full
     */
    function isFull() public view override returns (bool) {
        return data.size == data.maxSize;
    }

    /**
     * @notice Checks if list is empty
     */
    function isEmpty() public view override returns (bool) {
        return data.size == 0;
    }

    /**
     * @notice Returns the current size of the list
     */
    function getSize() external view override returns (uint256) {
        return data.size;
    }

    /**
     * @notice Returns the maximum size of the list
     */
    function getMaxSize() external view override returns (uint256) {
        return data.maxSize;
    }

    /**
     * @notice Returns the node data in the list
     * @dev First node is node with the largest boostedAICR
     */
    function getNode(address _id) external view override returns (bool, address, address, uint256, uint256, uint256) {
        Node memory node = data.nodes[_id];
        return (node.exists, node.nextId, node.prevId, node.oldBoostedAICR, node.boost, node.timeSinceBoostUpdated);
    }

    /**
     * @notice Returns the first node in the list
     * @dev First node is node with the largest boostedAICR
     */
    function getFirst() external view override returns (address) {
        return data.head;
    }

    /**
     * @notice Returns the last node in the list
     * @dev First node is node with the smallest boostedAICR
     */
    function getLast() external view override returns (address) {
        return data.tail;
    }

    /**
     * @notice Returns the next node (with a smaller boostedAICR) in the list for a given node
     * @param _id Node's id
     */
    function getNext(address _id) external view override returns (address) {
        return data.nodes[_id].nextId;
    }

    /**
     * @notice Returns the previous node (with a larger boostedAICR) in the list for a given node
     * @param _id Node's id
     */
    function getPrev(address _id) external view override returns (address) {
        return data.nodes[_id].prevId;
    }

    /**
     * @notice Get the stale boostedAICR of a node
     * @param _id Node's id
     */
    function getOldBoostedAICR(address _id) external view override returns (uint256) {
        return data.nodes[_id].oldBoostedAICR;
    }

    /**
     * @notice Get the timeSinceBoostUpdated of a node
     * @param _id Node's id
     */
    function getTimeSinceBoostUpdated(address _id) external view override returns (uint256) {
        return data.nodes[_id].timeSinceBoostUpdated;
    }

    /**
     * @notice Get the current boost of a node
     * @param _id Node's id
     */
    function getBoost(address _id) external view override returns (uint256) {
        return data.nodes[_id].boost;
    }

    /**
     * @notice Get the decayed boost of a node since time last updated
     * @param _id Node's id
     */
    function getDecayedBoost(address _id) external view override returns (uint256) {
        return _calculateDecayedBoost(data.nodes[_id].boost, data.nodes[_id].timeSinceBoostUpdated);
    }

    /**
     * @notice get the size of under-collateralized assetPortfolios list.
     * @dev if != 0 then not allowed to withdraw from SP.
     */
    function getUnderCollateralizedAssetPortfoliosSize() external view override returns (uint256) {
        return underCollateralizedAssetPortfoliosSize;
    }

    // --- 'require' functions ---

    function _requireCallerIsAssetPortfolioManager() internal view {
        if (msg.sender != assetPortfolioManagerAddress) {
            _revertWrongFuncCaller();
        }
    }

    function _requireCallerIsPalmController() internal view {
        if (msg.sender != controllerAddress) {
            _revertWrongFuncCaller();
        }
    }

    function _requireCallerIsBO() internal view {
        if (msg.sender != borrowerOperationsAddress) {
            _revertWrongFuncCaller();
        }
    }

    function _requireCallerIsTM() internal view {
        if (msg.sender != assetPortfolioManagerAddress && msg.sender != assetPortfolioManagerRedemptionsAddress) {
            _revertWrongFuncCaller();
        }
    }

    function _revertWrongFuncCaller() internal pure {
        revert("ST: External caller not allowed");
    }
}