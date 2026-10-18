<script setup lang="ts">
// Author by Lab | zefry. The opt-out bearer stays out of query strings and referrers.
import { ref } from 'vue';
import { locale } from '../../i18n';
const tr = (id: string, en: string) => locale.value === 'id' ? id : en;
const token = new URLSearchParams(window.location.hash.slice(1)).get('token') ?? '';
history.replaceState(null, '', '/marketing/preferences');
const busy = ref(false), done = ref(false), error = ref(false);
async function unsubscribe() {
  busy.value = true; error.value = false;
  try { const r = await fetch('/marketing/unsubscribe', { method:'POST', credentials:'same-origin', headers:{ 'Content-Type':'application/json', Accept:'application/json', 'X-CSRF-TOKEN': document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '' }, body: JSON.stringify({ token }) }); if (!r.ok) throw new Error(); done.value = true; }
  catch { error.value = true; } finally { busy.value = false; }
}
</script>
<template><main class="preferences"><a href="/">oneQay</a><h1>{{ done ? tr('Berhasil berhenti berlangganan','You are unsubscribed') : tr('Preferensi email marketing','Marketing email preferences') }}</h1><p>{{ done ? tr('Email marketing berikutnya dihentikan. Pesan keamanan dan layanan tetap dikirim.','Future marketing emails are stopped. Security and service messages continue.') : tr('Berhenti menerima penawaran dan informasi marketing oneQay melalui email.','Stop receiving oneQay offers and marketing updates by email.') }}</p><button v-if="!done" :disabled="busy || !token" @click="unsubscribe">{{ busy ? tr('Menyimpan…','Saving…') : tr('Berhenti berlangganan','Unsubscribe') }}</button><p v-if="!token && !done" role="alert">{{ tr('Buka halaman ini dari tautan email marketing Anda.','Open this page from your marketing email link.') }}</p><p v-if="error" role="alert">{{ tr('Tautan tidak valid atau layanan belum tersedia. Coba kembali.','The link is invalid or the service is unavailable. Please retry.') }}</p><footer>© oneQay</footer></main></template>
<style scoped>.preferences{max-width:540px;margin:80px auto;padding:30px;color:#204c3d;font-family:system-ui}.preferences>a{font-size:24px;font-weight:750;color:#176d50;text-decoration:none}.preferences h1{margin-top:35px;font-size:30px;letter-spacing:-.8px}.preferences p{line-height:1.8;color:#688475}.preferences button{padding:13px 20px;border:0;border-radius:9px;background:#176d50;color:white;font-weight:600;cursor:pointer}.preferences button:disabled{opacity:.5}.preferences footer{margin-top:45px;font-size:11px;color:#81968b}</style>
