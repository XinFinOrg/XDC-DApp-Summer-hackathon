# Licensed to the Apache Software Foundation (ASF) under one
# or more contributor license agreements.  See the NOTICE file
# distributed with this work for additional information
# regarding copyright ownership.  The ASF licenses this file
# to you under the Apache License, Version 2.0 (the
# "License"); you may not use this file except in compliance
# with the License.  You may obtain a copy of the License at
#
#   http://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing,
# software distributed under the License is distributed on an
# "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
# KIND, either express or implied.  See the License for the
# specific language governing permissions and limitations
# under the License.

######## SGX SDK Settings ########

SGX_SDK := /home/$(USER)/workspace/linux-sgx/
SGXSSL_INCLUDE_PATH ?= /opt/intel/sgxssl/include
SGXSSL_CRYPTO_INCLUDE_PATH ?= /opt/intel/sgxssl/include/crypto
SGXSSL_TRUSTED_LIB_PATH ?= /opt/intel/sgxssl/lib64

SGX_MODE := HW
SGX_ARCH := x64
SGX_DEBUG := 1
KS_SGX_SRC := /home/$(USER)/workspace/ks-sgx
TOP_DIR := /home/$(USER)/workspace/incubator-teaclave-sgx-sdk/

include $(TOP_DIR)/buildenv.mk

ifeq ($(shell getconf LONG_BIT), 32)
	SGX_ARCH := x86
else ifeq ($(findstring -m32, $(CXXFLAGS)), -m32)
	SGX_ARCH := x86
endif

ifeq ($(SGX_ARCH), x86)
	SGX_COMMON_CFLAGS := -m32
	SGX_LIBRARY_PATH := $(SGX_SDK)/lib
	SGX_ENCLAVE_SIGNER := $(SGX_SDK)/bin/x86/sgx_sign
	SGX_EDGER8R := $(SGX_SDK)/bin/x86/sgx_edger8r
else
	SGX_COMMON_CFLAGS := -m64
	SGX_LIBRARY_PATH := $(SGX_SDK)/lib64
	SGX_ENCLAVE_SIGNER := $(SGX_SDK)/bin/x64/sgx_sign
	SGX_EDGER8R := $(SGX_SDK)/bin/x64/sgx_edger8r
endif

ifeq ($(SGX_DEBUG), 1)
ifeq ($(SGX_PRERELEASE), 1)
$(error Cannot set SGX_DEBUG and SGX_PRERELEASE at the same time!!)
endif
endif

ifeq ($(SGX_DEBUG), 1)
	SGX_COMMON_CFLAGS += -O0 -g
else
	SGX_COMMON_CFLAGS += -O2
endif

SGX_COMMON_CFLAGS += -fstack-protector

######## CUSTOM Settings ########

CUSTOM_LIBRARY_PATH := ./lib
CUSTOM_BIN_PATH := ./bin
CUSTOM_EDL_PATH := $(TOP_DIR)/edl
CUSTOM_COMMON_PATH := $(TOP_DIR)/common

######## EDL Settings ########

Enclave_EDL_Files := $(KS_SGX_SRC)/Enclave_KS/Enclave_KS_t.c $(KS_SGX_SRC)/Enclave_KS/Enclave_KS_t.h app/Enclave_KS_u.c app/Enclave_KS_u.h

######## APP Settings ########

App_Rust_Flags := --release
App_SRC_Files := $(shell find app/ -type f -name '*.rs') $(shell find app/ -type f -name 'Cargo.toml')
App_Include_Paths := -I ./app -I./include -I$(SGX_SDK)/include -I$(CUSTOM_EDL_PATH) -I$(SGXSSL_INCLUDE_PATH)
App_C_Flags := $(SGX_COMMON_CFLAGS) -fPIC -Wno-attributes $(App_Include_Paths)

App_Rust_Path := ./app/target/release
App_Enclave_u_Object :=lib/libEnclave_u.a
App_Name := bin/app

######## Enclave Settings ########

ifneq ($(SGX_MODE), HW)
	Trts_Library_Name := sgx_trts_sim
	Service_Library_Name := sgx_tservice_sim
else
	Trts_Library_Name := sgx_trts
	Service_Library_Name := sgx_tservice
endif
Crypto_Library_Name := sgx_tcrypto
KeyExchange_Library_Name := sgx_tkey_exchange
ProtectedFs_Library_Name := sgx_tprotected_fs

RustEnclave_C_Files := $(wildcard $(KS_SGX_SRC)/Enclave_KS/*.c)
RustEnclave_C_Objects := $(RustEnclave_C_Files:.c=.o)
RustEnclave_Include_Paths := -I$(CUSTOM_COMMON_PATH)/inc -I$(CUSTOM_EDL_PATH) -I$(SGX_SDK)/include -I$(SGX_SDK)/include/tlibc -I$(SGX_SDK)/include/stlport -I$(SGX_SDK)/include/epid -I ./enclave -I./include -I$(SGXSSL_INCLUDE_PATH)

RustEnclave_Link_Libs := -L$(CUSTOM_LIBRARY_PATH) -lenclave -L$(SGXSSL_TRUSTED_LIB_PATH)
RustEnclave_Compile_Flags := $(SGX_COMMON_CFLAGS) $(ENCLAVE_CFLAGS) $(RustEnclave_Include_Paths)
RustEnclave_Link_Flags := -Wl,--no-undefined -nostdlib -nodefaultlibs -nostartfiles -L$(SGX_LIBRARY_PATH) \
	-Wl,--whole-archive -l$(Trts_Library_Name) -Wl,--no-whole-archive \
	-Wl,--start-group -lsgx_tstdc -l$(Service_Library_Name) -l$(Crypto_Library_Name) $(RustEnclave_Link_Libs) -Wl,--end-group \
	-Wl,--version-script=Enclave_KS/Enclave_KS.lds \
	$(ENCLAVE_LDFLAGS)

RustEnclave_Name := $(KS_SGX_SRC)/libenclave_ks.so
Signed_RustEnclave_Name := bin/libenclave_ks.signed.so

.PHONY: all
all: $(App_Name) $(Signed_RustEnclave_Name)

######## EDL Objects ########

$(Enclave_EDL_Files): $(SGX_EDGER8R) $(KS_SGX_SRC)/Enclave_KS/Enclave_KS.edl
	cp $(KS_SGX_SRC)/App/Enclave_KS_u.c app/
	cp $(KS_SGX_SRC)/APP/Enclave_KS_u.h app/
	# @echo "GEN  =>  $(Enclave_EDL_Files)"

######## App Objects ########

app/Enclave_KS_u.o: $(KS_SGX_SRC)/App/Enclave_KS_u.o
	cp $(KS_SGX_SRC)/App/Enclave_KS_u.o app/Enclave_KS_u.o


# 生成 enclave 的静态链接库.a，需要库.o
$(App_Enclave_u_Object): app/Enclave_KS_u.o
	$(AR) rcsD $@ $^

$(App_Name): $(App_Enclave_u_Object) $(App_SRC_Files)
	@cd app && SGX_SDK=$(SGX_SDK) cargo build $(App_Rust_Flags)
	@echo "Cargo  =>  $@"
	mkdir -p bin
	cp $(App_Rust_Path)/app ./bin
	cp ./app/log4rs.yml ./bin

######## Enclave Objects ########

# $(RustEnclave_Name): Enclave_KS/Enclave_KS_t.o
# 	@$(CXX) Enclave_KS/Enclave_KS_t.o -o $@ $(RustEnclave_Link_Flags)
# 	@echo "LINK =>  $@"

$(Signed_RustEnclave_Name): $(KS_SGX_SRC)/libenclave_ks.signed.so
	mkdir -p bin
	cp $(KS_SGX_SRC)/libenclave_ks.signed.so bin/

# .PHONY: enclave
# enclave:
# 	$(MAKE) -C ./enclave/


.PHONY: clean
clean:
	@rm -f $(App_Name) $(App_Enclave_u_Object) bin/log4rs.yml bin/libenclave_ks.signed.so app/Enclave_KS_u.h app/Enclave_KS_u.c app/Enclave_KS_u.o
	#@cd app && cargo clean && rm -f Cargo.lock
