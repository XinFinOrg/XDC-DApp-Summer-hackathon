extern crate sgx_types;
extern crate sgx_urts;
use sgx_types::*;


extern {

    pub fn ec_gen_key(
        eid: sgx_enclave_id_t, 
        retval: *mut sgx_status_t
    ) -> sgx_status_t;

    pub fn ec_ks_exchange(
        eid: sgx_enclave_id_t, 
        retval: *mut sgx_status_t,
        user_pub_key: *const c_char,
        strval: *mut c_char,
        strval2: *mut c_char
    ) -> sgx_status_t;

    pub fn ec_auth(
        eid: sgx_enclave_id_t,
        retval: *mut u32,
        account: *const c_char,
        user_pub_key: *const c_char
    ) -> sgx_status_t;

    pub fn ec_auth_confirm(
        eid: sgx_enclave_id_t,
        retval: *mut sgx_status_t,
        account: *const c_char,
        cipher_code: *const c_char,
        cipher_size: u32
    ) -> sgx_status_t;

    pub fn ec_gen_register_mail_code(
        eid :sgx_enclave_id_t,
        retval: *mut u32,
        account: *const c_char,
        cipher_code: *const c_char,
        cipher_size: u32
    ) -> sgx_status_t;

    pub fn ec_register_mail(
        eid: sgx_enclave_id_t,
        retval: *mut sgx_status_t,
        account: *const c_char,
        cipher_code: *const c_char,
        cipher_size: u32,
        sealed_mail: *mut c_void,
        sealed_size: u32
    ) -> sgx_status_t;

    pub fn ec_register_password(
        eid: sgx_enclave_id_t,
        retval: *mut sgx_status_t,
        account: *const c_char,
        cipher_code: *const c_char,
        cipher_size: u32,
        sealed_password: *mut c_void,
        sealed_size: u32
    ) -> sgx_status_t;

    pub fn ec_register_gauth(
        eid: sgx_enclave_id_t,
        retval: *mut sgx_status_t,
        account: *const c_char,
        cipher_gauth: *mut c_void,
        cipher_size: u32,
        sealed_gauth: *mut c_void,
        sealed_size: u32,
    ) -> sgx_status_t;

    pub fn ec_gen_gauth_secret(
        eid: sgx_enclave_id_t,
        retval: *mut sgx_status_t,
        sealed_gauth: *mut c_char,
        sealed_size: u32,
        cipher_gauth: *mut c_char
    ) -> sgx_status_t;

    pub fn ec_verify_gauth_code(
        eid: sgx_enclave_id_t,
        retval: *mut sgx_status_t,
        code: i32,
        gauth_secret: *const c_char,
        time: u64
    ) -> sgx_status_t;

    pub fn ec_ks_seal(
        eid: sgx_enclave_id_t, 
        retval: *mut sgx_status_t,
        account: *const c_char,
        cipher_secret: *const c_char,
        cipher_size: u32,
        sealed_secret: *mut c_void,
        sealed_size: u32
    ) -> sgx_status_t;

    pub fn ec_ks_unseal2(
        eid: sgx_enclave_id_t, 
        retval: *mut u32,
        account: *const c_char,
        cipher_cond: *const c_char, // user encrypted password or confirm code or etc
        cipher_cond_size: u32,
        cond_type: *const c_char,
        sealed_cond: *const c_char,
        sealed_cond_size: u32,
        sealed_secret: *const c_char,
        sealed_secret_size: u32,
        unsealed_secret: *mut c_void,
        unsealed_secret_size: u32
    ) -> sgx_status_t;

    pub fn ec_ks_unseal(
        eid: sgx_enclave_id_t, 
        retval: *mut u32,
        user_pub_key: *const c_char,
        sealed: *const c_char,
        len3: u32
    ) -> sgx_status_t;

    pub fn ec_prove_me(
        eid: sgx_enclave_id_t, 
        retval: *mut u32,
        code: *const c_char,
        code_len: u32,
        unsealed: *mut c_void
    ) -> sgx_status_t;

    pub fn ec_calc_sealed_size(
        eid: sgx_enclave_id_t, 
        retval: *mut u32,
        len1: u32
    ) -> sgx_status_t;

    pub fn ec_check_code(
        eid: sgx_enclave_id_t, 
        retval: *mut u32,
        secret: *const c_char,
        secret_len: u32,
        tm: u64,
        code: *const c_char,
        code_len: u32,
        data: *const c_char,
        data_len: u32,
        unsealed: *mut c_void
    ) -> sgx_status_t;

}

