drop table if exists teestore;
create table teestore (
    ks_id varchar(40) not null,
    chain varchar(40),
    chain_addr varchar(100),
    cond_type varchar(20),
    d_cond_type varchar(20),
    tee_cond_type varbinary(100),
    tee_cond_value varbinary(100),
    tee_d_cond_type varbinary(100),
    tee_d_cond_value varbinary(100),
    tee_content varbinary(8192),
    INDEX ks_id using hash(ks_id),
    INDEX ks_addr using hash(chain_addr),
    PRIMARY KEY(ks_id, chain, chain_addr)
)
ROW_FORMAT=COMPRESSED
CHARACTER set = utf8mb4;

drop table if exists user_cond;
create table user_cond (
    kid varchar(40) not null,
    cond_type varchar(20) default '' not null,
    tee_cond_value varchar(1000) default '' not null,
    tee_cond_size int default 0,
    PRIMARY KEY(kid, cond_type)
);

drop table if exists user_secret;
create table user_secret (
    kid varchar(40) not null,
    cond_type varchar(20) default '' not null,
    delegate_id varchar(40) default '' not null,
    chain varchar(40) default '' not null,
    chain_addr varchar(100) default '' not null,
    tee_secret varchar(8192) default '' not null,
    tee_secret_size int default 0,
    INDEX uid using hash(kid),
    INDEX did using hash(delegate_id),
    PRIMARY KEY(kid, cond_type)
);

