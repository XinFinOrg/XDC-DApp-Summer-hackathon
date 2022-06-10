// SPDX-License-Identifier: UNLICENSED

pragma solidity >=0.6.11;

interface ICurvePool {
    function get_dy(int128 i, int128 j, uint256 _dx) view external returns (uint256);
}