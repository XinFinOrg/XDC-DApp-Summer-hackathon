<template>
	<el-link type="success" @click="onClickTransak">Buy Cryptos @<el-image :src="transak" style="height: 25px;"/></el-link>
	<el-drawer v-model="buyCrypto" direction="rtl" destroy-on-close @open="onTransakOpen">
      <template #default>
        <iframe frameborder="0" sandbox="allow-scripts allow-same-origin allow-popups" :src="transakUrl" style="height:100%;width: 100%;" />		
      </template>  
	</el-drawer>
	<div class="copyright mt-1" style="padding-bottom: 10px">
    <el-link type="success" href="javascript:void(0);">Build For Hackathons And Learnings</el-link>
  </div>	
</template>

<script lang="ts">
export default {
  name: 'FootPage',
  props: {
  }
}
</script>

<script setup lang="ts">
import { ref } from 'vue'
import * as constant from "../constant"
import { connectState } from "../libs/connect"

const transak = require('@/assets/transak.png');	
const buyCrypto = ref(false);
const transakUrl = ref(constant.transakUrl);

const onClickTransak = async () => {
	buyCrypto.value = true;
}

const onTransakOpen = async () => {
	if (connectState.userAddr.value === ''){
		transakUrl.value = constant.transakUrl;
	} else {
		transakUrl.value = constant.transakUrl + '&disableWalletAddressForm=true&walletAddress=' + connectState.userAddr.value;
	}
}
</script>