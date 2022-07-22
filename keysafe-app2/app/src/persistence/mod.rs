use mysql::*;
use mysql::prelude::*;

use std::fs::File;
use std::fs;

use glob::glob;
use std::io::Write;
use serde_derive::{Deserialize, Serialize};
use std::collections::HashMap;

pub struct UserCond {
    pub kid: String,
    pub cond_type: String,
    pub tee_cond_value: String,
    pub tee_cond_size: i32
}

#[derive(Debug, Serialize, Deserialize, Clone)]
pub struct UserOAuth {
    pub kid: String,
    pub org: String,
    pub tee_profile: String,
    pub tee_profile_size: i32
}

pub struct UserSecret {
    pub kid: String,
    pub cond_type: String,
    pub delegate_id: String,
    pub chain: String,
    pub chain_addr: String,
    pub tee_secret: String,
    pub tee_secret_size: i32
}

pub fn insert_user_cond(pool: &Pool, ucond: UserCond) {
    let mut conn = pool.get_conn().unwrap();
    let mut tx = conn.start_transaction(TxOpts::default()).unwrap();
    tx.exec_drop("delete from user_cond where kid = ? and cond_type = ?",
        (ucond.kid.clone(), ucond.cond_type.clone())).unwrap();
    tx.exec_drop("insert into user_cond (kid, cond_type, tee_cond_value, tee_cond_size) values (?, ?, ?, ?)",
        (ucond.kid, ucond.cond_type, ucond.tee_cond_value, ucond.tee_cond_size)).unwrap();
    tx.commit().unwrap();
}

pub fn insert_user_oauth(pool: &Pool, oauth: UserOAuth) {
    let mut conn = pool.get_conn().unwrap();
    let mut tx = conn.start_transaction(TxOpts::default()).unwrap();
    tx.exec_drop("delete from user_oauth where kid = ? and org = ?",
        (oauth.kid.clone(), oauth.org.clone())).unwrap();
    tx.exec_drop("insert into user_oauth (kid, org, tee_profile, tee_profile_size) values (?, ?, ?, ?)",
        (oauth.kid, oauth.org, oauth.tee_profile, oauth.tee_profile_size)).unwrap();
    tx.commit().unwrap();
}

pub fn insert_user_oauth2(pool: &Pool, conf: &HashMap<String, String>, oauth: UserOAuth) {
    let cid = cru_api(conf, "putfile", &oauth.tee_profile);
    let mut oauth2 = oauth.clone();
    oauth2.tee_profile = cid;
    insert_user_oauth(pool, oauth2);
}

pub fn insert_user_secret(pool: &Pool, usecret: UserSecret) {
    let mut conn = pool.get_conn().unwrap();
    let mut tx = conn.start_transaction(TxOpts::default()).unwrap();
    tx.exec_drop(
        "delete from user_secret  where kid = ? and cond_type = ? and  chain = ? and chain_addr = ?",
        (usecret.kid.clone(), usecret.cond_type.clone(), usecret.chain.clone(), usecret.chain_addr.clone())).unwrap();
    tx.exec_drop(
        "insert into user_secret (kid, cond_type, chain, chain_addr, tee_secret, tee_secret_size) values (?, ?, ?, ?, ?, ?)",
        (usecret.kid, usecret.cond_type, usecret.chain, usecret.chain_addr, usecret.tee_secret, usecret.tee_secret_size)).unwrap();
    tx.commit().unwrap();
}

pub fn delete_user_secret(pool: &Pool, usecret: UserSecret) {
    let mut conn = pool.get_conn().unwrap();
    let mut tx = conn.start_transaction(TxOpts::default()).unwrap();
    tx.exec_drop("delete from user_secret where kid = ? and chain = ? and chain_addr = ?",
        (usecret.kid.clone(), usecret.chain.clone(), usecret.chain_addr.clone())).unwrap();
    tx.commit().unwrap();
}

pub fn update_delegate(pool: &Pool, delegate_id: &String, kid: &String) {
    let mut conn = pool.get_conn().unwrap();
    let mut tx = conn.start_transaction(TxOpts::default()).unwrap();
    tx.exec_drop("update user_secret set delegate_id = ? where kid = ? ",
        (delegate_id, kid)).unwrap();
    tx.commit().unwrap();
}

pub fn query_user_oauth2(pool: &Pool, conf: &HashMap<String, String>, stmt: String) -> Vec<UserOAuth> {
    let result = query_user_oauth(pool, stmt);
    let mut result2 = vec![];
    for oauth in result {
        let mut oauth2 = oauth.clone();
        let cid = cru_api(conf, "getfile", &oauth.tee_profile);
        oauth2.tee_profile = cid;
        result2.push(oauth2);
    }
    result2
}

pub fn query_user_oauth(pool: &Pool, stmt: String) -> Vec<UserOAuth>{
    let mut conn = pool.get_conn().unwrap();
    let mut result: Vec<UserOAuth> = Vec::new();
    conn.query_iter(stmt).unwrap().for_each(|row| {
        let r:(std::string::String, std::string::String, 
            std::string::String, i32) = from_row(row.unwrap());
        result.push(UserOAuth {
            kid: r.0,
            org: r.1,
            tee_profile: r.2,
            tee_profile_size: r.3
        });
    });
    result
}

#[derive(Serialize, Deserialize, Debug)]
struct CruApiReq {
    key: String
}

#[derive(Serialize, Deserialize, Debug)]
struct CruApiResp {
    key: String
}

fn cru_api(conf: &HashMap<String, String>, method: &str, key: &str) -> String {
    println!("calling cru api to {} {}", method, key);
    let cru_api_server = conf.get("cru_api_server").unwrap();
    let client =  reqwest::blocking::Client::new();
    let cru_req = CruApiReq {
        key: key.to_string()
    };
    let url = format!("{}/{}", cru_api_server, method);
    let res = client.post(url)
        .json(&cru_req)
        .send().unwrap().json::<CruApiResp>().unwrap();
    println(res.key);
    res.key
}

pub fn query_user_cond(pool: &Pool, stmt: String) -> Vec<UserCond>{
    let mut conn = pool.get_conn().unwrap();
    let mut result: Vec<UserCond> = Vec::new();
    conn.query_iter(stmt).unwrap().for_each(|row| {
        let r:(std::string::String, std::string::String, 
            std::string::String, i32) = from_row(row.unwrap());
        result.push(UserCond {
            kid: r.0,
            cond_type: r.1,
            tee_cond_value: r.2,
            tee_cond_size: r.3
        });
    });
    result
}

pub fn query_user_secret(pool: &Pool, stmt: String) -> Vec<UserSecret> {
    let mut conn = pool.get_conn().unwrap();
    let mut result: Vec<UserSecret> = Vec::new();
    conn.query_iter(stmt).unwrap().for_each(|row| {
        let r:(std::string::String, std::string::String, 
            std::string::String, std::string::String, 
            std::string::String, std::string::String,
            i32
        ) = from_row(row.unwrap());
        result.push(
            UserSecret {
                kid: r.0,
                cond_type: r.1,
                delegate_id: r.2,
                chain: r.3,
                chain_addr: r.4,
                tee_secret: r.5,
                tee_secret_size: r.6
            }
        );
    });
    result
}

