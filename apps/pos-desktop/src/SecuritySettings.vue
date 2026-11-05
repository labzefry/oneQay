<script setup lang="ts">
import {computed,ref,watch} from 'vue'
import {invoke} from '@tauri-apps/api/core'
import Icon from './Icon.vue'
import type {Security} from './types'
const props=defineProps<{security:Security;language:string}>()
const emit=defineEmits<{update:[Security];error:[unknown];lock:[];logout:[]}>()
const tr=(id:string,en:string)=>props.language==='id'?id:en
const seconds=ref(props.security.auto_lock_seconds??300),busy=ref(false),saved=ref(false)
watch(()=>props.security.auto_lock_seconds,v=>{seconds.value=v??300})
const choices=[0,60,120,300,600,900,1800]
const changed=computed(()=>seconds.value!==(props.security.auto_lock_seconds??300))
async function save(){
 if(busy.value||!changed.value)return
 busy.value=true;saved.value=false
 try{emit('update',await invoke<Security>('save_security_settings',{seconds:seconds.value}));saved.value=true}
 catch(e){emit('error',e)}finally{busy.value=false}
}
</script>
<template>
<main class="page-content security-page">
 <div class="page-intro"><div><span class="eyebrow">{{tr('KENYAMANAN & KEAMANAN','COMFORT & SECURITY')}}</span><h2>{{tr('Akun tetap masuk. Kendali tetap di Anda.','Stay signed in. Stay in control.')}}</h2><p>{{tr('Atur kunci layar untuk akun kasir ini pada komputer ini.','Set screen locking for this cashier on this computer.')}}</p></div><span class="session-status"><Icon name="shield-check"/>{{tr('Akun tersimpan di perangkat','Account remembered on this device')}}</span></div>
 <div class="security-layout">
  <section class="settings-card security-primary"><span class="section-icon"><Icon name="shield-check"/></span><h3>{{tr('Kunci otomatis','Automatic lock')}}</h3><p>{{tr('Layar terkunci saat tidak digunakan. Buka kembali dengan PIN, tanpa login ulang.','Lock the screen when idle. Unlock with your PIN without signing in again.')}}</p>
   <label for="auto-lock-duration">{{tr('Kunci setelah tidak ada aktivitas','Lock after inactivity')}}</label>
   <select id="auto-lock-duration" v-model.number="seconds" :disabled="busy"><option v-for="n in choices" :key="n" :value="n">{{n===0?tr('Nonaktif — kunci manual saja','Off — manual locking only'):`${n/60} ${tr('menit','minutes')}`}}</option></select>
   <div class="security-note"><Icon name="circle-alert"/><p>{{seconds===0?tr('Kunci otomatis dinonaktifkan. Anda tetap dapat menekan Kunci POS kapan saja.','Automatic locking is off. You can still select Lock POS at any time.'):tr('Pengaturan berlaku untuk kasir ini dan tetap tersimpan setelah aplikasi ditutup.','This setting belongs to this cashier and survives closing the app.')}}</p></div>
   <div class="button-row"><button class="primary" :disabled="busy||!changed" @click="save"><Icon :name="busy?'refresh-cw':'check'" :class="{spin:busy}"/>{{busy?tr('Menyimpan…','Saving…'):tr('Simpan kunci otomatis','Save automatic lock')}}</button><span v-if="saved&&!changed" role="status">{{tr('Pengaturan tersimpan.','Settings saved.')}}</span></div>
  </section>
  <div class="settings-stack"><section class="settings-card"><span class="section-icon"><Icon name="monitor"/></span><h3>{{tr('Login tanpa hitung mundur','Sign-in without a countdown')}}</h3><p>{{tr('Menutup atau membuka ulang aplikasi tidak mengeluarkan akun. Login berakhir saat Anda logout atau akses dicabut oleh pemilik bisnis.','Closing and reopening the app keeps your account signed in. Sign-in ends when you log out or your business owner revokes access.')}}</p><dl class="security-details"><div><dt>{{tr('Akun aktif','Active account')}}</dt><dd>{{security.login}}</dd></div><div><dt>{{tr('Saat aplikasi dibuka kembali','When reopening')}}</dt><dd>{{tr('Lanjutkan akun; PIN bila terkunci','Resume account; PIN if locked')}}</dd></div></dl></section>
  <section class="settings-card"><h3>{{tr('Kunci atau keluar akun','Lock or sign out')}}</h3><p>{{tr('Kunci mempertahankan akun. Logout mengakhiri login tersimpan; struk dan antrean transaksi tetap disimpan.','Lock keeps your account signed in. Logout ends remembered sign-in; receipts and queued transactions remain stored.')}}</p><div class="button-row"><button class="secondary" @click="emit('lock')"><Icon name="shield-check"/>{{tr('Kunci sekarang','Lock now')}}</button><button class="secondary" @click="emit('logout')"><Icon name="chevron-right"/>{{tr('Keluar akun','Sign out')}}</button></div></section></div>
 </div><p class="security-footnote">{{tr('Izin transaksi offline tetap diperiksa secara terpisah. Saat izin perlu diperbarui, sambungkan internet; akun tersimpan tidak perlu diganti.','Offline transaction authorization is checked separately. Reconnect when it needs refreshing; keep using your remembered account.')}}</p>
</main>
</template>
<style scoped>
.security-page{color:#202020}.security-page p,.security-page label,.security-page dt,.security-page dd{color:#202020}.security-layout{display:grid;grid-template-columns:minmax(0,1.1fr) minmax(0,1fr);gap:24px}.security-page h2{font-size:26px;line-height:1.3}.security-page h3{font-size:20px;margin:12px 0}.security-page p{font-size:15px;line-height:1.7}.security-primary{align-self:start}.security-primary select{width:100%;min-height:48px;font-size:16px;color:#202020;background:#FFFFFF}.security-note{display:flex;align-items:flex-start;gap:12px;border:1px solid #EDEDED;border-radius:12px;padding:16px;margin:20px 0;background:#FFFFFF}.security-note svg{color:#3965FA;margin-top:4px}.security-note p{margin:0}.session-status{display:flex;align-items:center;gap:8px;border:1px solid #EDEDED;border-radius:999px;padding:10px 16px;color:#202020;background:#FFFFFF;font-size:14px}.security-details{margin:20px 0 0}.security-details>div{display:grid;gap:5px;margin-top:14px}.security-details dt{font-size:14px}.security-details dd{font-weight:600;font-size:15px;margin:0;overflow-wrap:anywhere}.security-footnote{margin-top:24px!important}.security-page .primary{background:#3965FA;color:#FFFFFF}.security-page .secondary{background:#FFFFFF;color:#202020}.security-page .eyebrow{color:#202020}.security-page .section-icon{background:#1B1B39;color:#FFFFFF}.security-page .button-row{flex-wrap:wrap;gap:12px}.security-page button{min-height:44px}.security-page .settings-card{box-shadow:0 12px 32px #1B1B3910;border-radius:20px}@media(max-width:1150px){.security-layout{grid-template-columns:1fr}.session-status{display:none}}@media(prefers-reduced-motion:reduce){.security-page *{animation:none!important;transition:none!important}}
</style>
