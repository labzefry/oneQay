<script setup lang="ts">
// Author by Lab | zefry. Web adapter for the shared cashier workflow.
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import { locale, setLocale } from '../../i18n';
import { formatMoney } from '../../business-money';
import WorkspaceIcon from '../../components/WorkspaceIcon.vue';
import MenuOrderWorkspace from '../../../../../../packages/menu-cashier-ui/MenuOrderWorkspace.vue';
import MemberPicker from '../../../../../../packages/menu-cashier-ui/MemberPicker.vue';
defineProps<{settlement_enabled:boolean;membership_enabled:boolean;directory_only?:boolean}>();
const member=ref<{id:string;name:string;status:'cashier_enrolled'}|null>(null);
async function send(action:string, body:Record<string,unknown>) {
 const token=document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content||'';
 const response=await fetch('/pos/business/menu-orders/'+action,{
  method:'POST',credentials:'same-origin',signal:AbortSignal.timeout(20000),
  headers:{Accept:'application/json','Content-Type':'application/json','X-CSRF-TOKEN':token},
  body:JSON.stringify(body),
 });
 const payload=await response.json();
 if(!response.ok||payload.error)throw new Error(payload.error?.code||('HTTP_'+response.status));
 return payload;
}
</script>
<template>
 <Head v-if="!directory_only" :title="(locale==='id'?'Pesanan QR Kasir':'Cashier QR Orders')+' | oneQay'"/>
 <Head v-else :title="locale==='id'?'Direktori member':'Membership directory'"/>
 <main v-if="directory_only" class="member-page">
  <div class="menu-actions"><a href="/pos">{{locale==='id'?'Kembali ke kasir':'Back to POS'}}</a><button type="button" @click="setLocale(locale==='id'?'en':'id')">{{locale==='id'?'EN':'ID'}}</button></div>
  <h1>{{locale==='id'?'Direktori member':'Membership directory'}}</h1>
  <MemberPicker :language="locale" :icon="WorkspaceIcon" :selected="member" :send="send" @select="member=$event"/>
 </main>
 <MenuOrderWorkspace v-else :settlement_enabled="settlement_enabled" :membership_enabled="membership_enabled" :language="locale" :icon="WorkspaceIcon" :format-money="formatMoney" :send="send">
  <template #actions>
   <div class="menu-actions">
    <a href="/pos">{{locale==='id'?'Kembali ke kasir':'Back to POS'}}</a>
    <button type="button" @click="setLocale(locale==='id'?'en':'id')">{{locale==='id'?'EN':'ID'}}</button>
   </div>
  </template>
 </MenuOrderWorkspace>
</template>
<style scoped>
.member-page{max-width:1080px;margin:auto;padding:1.5rem;background:var(--oq-white);color:var(--oq-dark)}.menu-actions{display:flex;align-items:center;gap:1rem}.menu-actions a{color:var(--oq-primary)}.menu-actions button{min-height:44px;padding:.5rem .8rem;background:var(--oq-white);color:var(--oq-dark);border:1px solid var(--oq-soft);border-radius:9px}
</style>
