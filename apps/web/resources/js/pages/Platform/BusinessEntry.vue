<script setup lang="ts">
import {onMounted,ref} from 'vue';
const failed=ref(false);
onMounted(async()=>{const ticket=new URLSearchParams(location.hash.slice(1)).get('ticket');history.replaceState(null,'',location.pathname);try{if(!ticket)throw Error();const response=await fetch('/business-entry',{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content??''},body:JSON.stringify({ticket}),signal:AbortSignal.timeout(15000)});if(!response.ok)throw Error();location.replace('/pos');}catch{failed.value=true;}});
</script>
<template><main class="entry"><h1>{{failed?'Tautan masuk berakhir / Sign-in link expired':'Menyiapkan ruang bisnis Anda / Opening your workspace'}}</h1><p role="status">{{failed?'Silakan masuk kembali. Data bisnis Anda tetap tersimpan.':'Sesi aman sedang dipindahkan ke alamat bisnis Anda.'}}</p><a v-if="failed" href="/business-login">Masuk / Sign in</a></main></template>
<style scoped>.entry{max-width:640px;margin:15vh auto;padding:32px;border:1px solid #E9EBFF;border-radius:24px;color:#1B1B39;font:16px/1.6 system-ui;background:#FFFFFF;box-shadow:0 16px 48px #1B1B3914}.entry a{color:#3965FA}</style>
