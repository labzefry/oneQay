<script setup lang="ts">
// Author by Lab | zefry. Read-only announcements are scoped by the authenticated tenant on the server.
import { ref } from 'vue';
import { locale } from '../i18n';
import WorkspaceIcon from './WorkspaceIcon.vue';
type Communications = { announcements: { id: string; subject: string; body: string }[]; can_manage: boolean; subscribed: boolean };
const props = defineProps<{ communications?: Communications | null }>();
const tr = (id: string, en: string) => locale.value === 'id' ? id : en;
const subscribed = ref(props.communications?.subscribed ?? false), busy = ref(false), message = ref('');
async function save() {
 busy.value = true; message.value = '';
 try { const r = await fetch('/pos/marketing/preferences', { method:'POST', credentials:'same-origin', headers:{ 'Content-Type':'application/json', Accept:'application/json', 'X-CSRF-TOKEN':document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '' }, body:JSON.stringify({ subscribed: subscribed.value }) }); if(!r.ok)throw new Error(); message.value=tr('Preferensi tersimpan.','Preferences saved.'); }
 catch { message.value=tr('Belum tersimpan. Periksa verifikasi keamanan akun dan coba kembali.','Not saved. Check account security verification and retry.'); }
 finally { busy.value=false; }
}
</script>
<template><details v-if="communications && (communications.announcements.length || communications.can_manage)" class="control-inbox no-print"><summary><WorkspaceIcon name="megaphone" :size="17"/>{{ tr('Informasi oneQay','oneQay updates') }}<span>{{ communications.announcements.length }}</span></summary><div class="inbox-content"><article v-for="item in communications.announcements" :key="item.id"><h3>{{ item.subject }}</h3><p>{{ item.body }}</p></article><form v-if="communications.can_manage" @submit.prevent="save"><label><input v-model="subscribed" type="checkbox" :disabled="busy"/>{{ tr('Terima informasi produk dan penawaran melalui email.','Receive product news and offers by email.') }}</label><button :disabled="busy">{{ tr('Simpan preferensi','Save preferences') }}</button><a href="/pos?panel=security#account-security">{{ tr('Verifikasi keamanan','Security verification') }}</a><p role="status">{{ message }}</p></form></div></details></template>
<style scoped>.control-inbox{margin:18px 30px 0;border:1px solid #dbe8df;background:#fff;border-radius:10px;color:#315c49}.control-inbox summary{display:flex;align-items:center;gap:9px;padding:13px 16px;font:600 11px system-ui;cursor:pointer}.control-inbox summary>span{margin-left:auto;background:#edf5ef;border-radius:5px;padding:3px 7px}.inbox-content{padding:0 18px 15px}.inbox-content article{border-top:1px solid #e5ede7;padding:12px 0}.inbox-content h3{font-size:13px}.inbox-content p{white-space:pre-wrap;overflow-wrap:anywhere;font-size:12px;line-height:1.8}.inbox-content form{border-top:1px solid #e5ede7;padding-top:15px}.inbox-content label{display:flex;gap:9px;align-items:center;font-size:12px}.inbox-content button{background:#eaf5ed;border:1px solid #cee2d4;border-radius:7px;padding:8px 12px;font-size:11px;color:#286642;margin:12px 12px 0 0;cursor:pointer}.inbox-content a{font-size:11px;color:#286642}@media(max-width:600px){.control-inbox{margin:15px 16px 0}}</style>
