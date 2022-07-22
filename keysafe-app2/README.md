# keysafe-sgx
# requirements
## linux-sgx
+ follow https://github.com/intel/linux-sgx#build-and-install-the-intelr-sgx-driver
+ install linux-sgx
+ source environment file in installed sgxsdk directory
+ keysafe relies on intel-sgx to seal the shards of user secrets
## baidu-rust-sgx-sdk
+ follow https://github.com/apache/incubator-teaclave-sgx-sdk
+ to install sgx-sdk
## mysql database
+ install mysql-server 
+ create a database, then import schema.sql into your database
## enterprise email account
+ you will need an email account to send notification mail to users when they register
# build ks-sgx
+ clone ks-sgx in another directory,
+ if you don't have an enclave hardware support
```
make SGX_MODE=SW
```
+ if you have an enclave hardware support
```
make
```
# build keysafe-app
+ download baidu sgx-sdk with the name incubator-teaclave-sgx-sdk
+ copy the code into incubator-teaclave-sgx-sdk/samplecode
+ change Makefile SGX_SDK and TOP_DIR according to your environment
+ if you don't have an enclave hardware support
```
make SGX_MODE=SW
```
+ if you have an enclave hardware support
```
make -f MakeHwFile
```
+ when build failed, change app/build.rs with specific path to your linux-sgx install dir
# execute
+ cd bin
+ cp ../app/conf.toml .
+ add your db config, email config all in this file.
```
node_api_port = 12345 # this is for your app port, might require sudo when this port is less than 1024
```
+ when env = dev, sendmail will not happen.
+ copy your certificate files in certs.
+ ./app
