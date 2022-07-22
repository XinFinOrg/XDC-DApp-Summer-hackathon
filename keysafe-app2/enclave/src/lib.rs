// Licensed to the Apache Software Foundation (ASF) under one
// or more contributor license agreements.  See the NOTICE file
// distributed with this work for additional information
// regarding copyright ownership.  The ASF licenses this file
// to you under the Apache License, Version 2.0 (the
// "License"); you may not use this file except in compliance
// with the License.  You may obtain a copy of the License at
//
//   http://www.apache.org/licenses/LICENSE-2.0
//
// Unless required by applicable law or agreed to in writing,
// software distributed under the License is distributed on an
// "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
// KIND, either express or implied.  See the License for the
// specific language governing permissions and limitations
// under the License..

#![crate_name = "helloworldsampleenclave"]
#![crate_type = "staticlib"]

#![cfg_attr(not(target_env = "sgx"), no_std)]
#![cfg_attr(target_env = "sgx", feature(rustc_private))]

extern crate sgx_types;
#[cfg(not(target_env = "sgx"))]
#[macro_use]
extern crate sgx_tstd as std;
extern crate serde_json;
extern crate sgx_crypto_helper;
extern crate sgx_tcrypto;

use sgx_tcrypto::*;
use sgx_crypto_helper::*;

use sgx_types::*;
use std::string::String;
use std::vec::Vec;
use std::io::{self, Write};
use std::slice;
use std::str;
use sgx_crypto_helper::RsaKeyPair;
use sgx_crypto_helper::rsa3072::{Rsa3072KeyPair, Rsa3072PubKey};


// generate right after sgx init, destroy after sgx exit
// static KEY: Rsa3072KeyPair = Rsa3072KeyPair::new().unwrap();


#[no_mangle]
pub extern "C" fn exchange_key(
    user_in: *const u8, 
    some_len: usize,
    sgx_out: *mut u8) -> sgx_status_t {
        // input user public key, output sgx public key
        // let exported_pubkey: Rsa3072PubKey = rsa_keypair.export_pubkey().unwrap();
        // let imported_pubkey: Rsa3072PubKey = serde_json::from_str(&serialized_pubkey).unwrap();

    // let str_slice = unsafe { slice::from_raw_parts(some_string, some_len) };
    // let key_str = String::from_utf8(str_slice.to_vec()).unwrap();
    // let key_parts: Vec<&str> = key_str.split("\n").collect();
    // println!("enclave: {}", key_parts[1]);
    // let key: &[u8] = key_parts[1].as_bytes();
    // let n: [u8; 256] = [0; 256];
    // let pubkey = SgxRsaPubKey::new();
    // pubkey.create(256, 4, key, &[0x00, 0x01, 0x00, 0x01]);

    // let text = String::from("Hello, World.");
    // let text_slice = &text.into_bytes();
    // let mut ciphertext: Vec<u8> = vec![0_u8; 256];
    // let mut chipertext_len: usize = ciphertext.len();
    // match pubkey.encrypt_sha256(
    //     ciphertext.as_mut_slice(), 
    //     &mut chipertext_len,
    //     text_slice
    // ) {
    //     Ok(n) => println!("Generated secret with user pub key, {}", ciphertext),
    //     Err(x) => println!("Error occurred during encryption {}", x)
    // }
    return sgx_status_t::SGX_SUCCESS
}

fn clone_into_array(aslice: &[u8]) -> [u8; 256] {
    let mut arr: [u8; 256] = [0; 256];
    let mut i = 0;
    for element in aslice.iter() {
        arr[i] = *element;
        i = i + 1;
    }
    return arr;
}

pub extern "C" fn register_key(user_in: *const u8, 
    some_len: usize,
    sgx_out: *mut u8) -> sgx_status_t {
        // decrypt user input, and seal it.
        return sgx_status_t::SGX_SUCCESS
}
