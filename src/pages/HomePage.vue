<template>
  <DaosPage v-if="activeIndex==='1'"/>  
  <LearningsPage v-if="activeIndex==='2'"/>  
  <AuctionsPage v-if="activeIndex==='3'"/>  
  <GrantsPage v-if="activeIndex==='4'"/>  
  <VotesPage v-if="activeIndex==='5'"/>  
  <ChatsPage v-if="activeIndex==='6'"/>
</template>

<script lang="ts">

import DaosPage from './DaosPage.vue';
import LearningsPage from './LearningsPage.vue';
import AuctionsPage from './AuctionsPage.vue';
import GrantsPage from './GrantsPage.vue';
import VotesPage from './VotesPage.vue';
import ChatsPage from './ChatsPage.vue';

export default {
  name: 'HomePage',
  props: {
    msg: String
  }
}
</script>

<script setup lang="ts">
import { connectState } from "../libs/connect"

const activeIndex = connectState.activeIndex;
</script>