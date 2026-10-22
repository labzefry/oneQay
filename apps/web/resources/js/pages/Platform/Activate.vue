<script setup lang="ts">
// Author by Lab | zefry
import { ref } from 'vue';
import { locale } from '../../i18n';
import Icon from '../../components/WorkspaceIcon.vue';
import { consumeInvitationToken } from '../../platform-invitation';
const tr=(id:string,en:string)=>locale.value==='id'?id:en;
const token=ref(consumeInvitationToken()),password=ref(''),confirm=ref(''),busy=ref(false),message=ref(''),destination=ref('');
async function activate(){
 if(busy.value)return;
 if(password.value!==confirm.value){message.value=tr('Konfirmasi kata sandi tidak sama.','Passwords do not match.');return;}
 busy.value=true;message.value='';
 try{
  const response=await fetch('/activate-business',{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json',Accept:'application/json','X-CSRF-TOKEN':document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content??''},body:JSON.stringify({token:token.value,password:password.value}),signal:AbortSignal.timeout(25000)});
  const result=await response.json();
  if(!response.ok||result.status!=='active'||!['/pos','/business-login'].includes(result.redirect))throw Error();
  destination.value=result.redirect;password.value='';confirm.value='';token.value='';window.location.replace(destination.value);
 }catch{message.value=tr('Aktivasi belum terkonfirmasi. Coba kembali dengan kata sandi yang sama. Jika tautan kedaluwarsa, kirim ulang verifikasi.','Activation is not confirmed. Retry with the same password. If the link expired, request another verification email.');}
 finally{busy.value=false;}
}
</script>
<template><main class="activation"><section>
<a class="brand" href="/">one<span>Qay</span></a><div class="activation-icon"><Icon :name="destination?'check':'shield-check'" :size="30"/></div><p class="eyebrow">ONEQAY BUSINESS</p><h1>{{destination?tr('Selamat datang di oneQay.','Welcome to oneQay.'):tr('Satu langkah lagi.','One step to go.')}}</h1>
<template v-if="!destination"><p>{{tr('Buat kata sandi untuk mengaktifkan akun Anda. Setelah selesai, kami akan membawa Anda ke ruang bisnis. Jika diperlukan, selesaikan verifikasi authenticator terlebih dahulu.','Set a password to activate your account. We will take you to your business workspace. Complete authenticator verification first if required.')}}</p><form @submit.prevent="activate"><fieldset :disabled="busy||!token"><label>{{tr('Kata sandi baru','New password')}}<input v-model="password" type="password" minlength="12" maxlength="72" required autocomplete="new-password"/><small>{{tr('Gunakan minimal 12 karakter.','Use at least 12 characters.')}}</small></label><label>{{tr('Ulangi kata sandi','Repeat password')}}<input v-model="confirm" type="password" minlength="12" maxlength="72" required autocomplete="new-password"/></label><button :aria-busy="busy"><Icon :name="busy?'refresh-cw':'arrow-right'" :class="{spin:busy}"/>{{busy?tr('Mengaktifkan akun…','Activating account…'):tr('Aktifkan & lanjutkan','Activate & continue')}}</button></fieldset></form><p v-if="!token" role="alert">{{tr('Buka halaman ini melalui tautan verifikasi di email Anda.','Open this page using the verification link in your email.')}}</p></template>
<template v-else><p>{{tr('Akun bisnis Anda sudah aktif. Membuka ruang kerja…','Your business account is active. Opening your workspace…')}}</p><a :href="destination">{{tr('Lanjutkan','Continue')}} →</a></template><p v-if="message" class="feedback" role="alert">{{message}}</p><div class="links"><a href="/business-login">{{tr('Sudah aktif? Masuk','Already activated? Sign in')}}</a><a href="/register">{{tr('Kirim ulang verifikasi','Resend verification')}}</a></div><footer>© {{new Date().getFullYear()}} oneQay</footer>
</section></main></template>
<style scoped>
.activation{min-height:100vh;box-sizing:border-box;padding:40px 20px;background:#E9EBFF;font-family:system-ui;color:#1B1B39;display:grid;place-items:center;font-size:16px}.activation section{box-sizing:border-box;width:100%;max-width:570px;padding:40px;background:#FFFFFF;border:1px solid #99B7FC;border-radius:24px;box-shadow:14px 14px 40px #1B1B3914,-8px -8px 30px #FFFFFF}.brand{font-size:26px;font-weight:800;text-decoration:none;color:#1B1B39}.brand span{color:#3965FA}.activation-icon{display:grid;place-items:center;background:#E9EBFF;color:#3965FA;width:64px;height:64px;border-radius:18px;margin:32px 0 24px}.eyebrow{font-size:14px;letter-spacing:2px;font-weight:700}h1{font-size:30px;letter-spacing:-.8px;line-height:1.2;margin:12px 0 20px}p{line-height:1.75;color:#1B1B39}fieldset{border:0;margin:0;padding:0}label{display:flex;flex-direction:column;gap:10px;margin:22px 0;font-weight:600}input{padding:14px;border:1px solid #99B7FC;border-radius:10px;font:inherit;color:#202020;background:#FFFFFF;min-height:48px;box-sizing:border-box}small{font-size:14px;font-weight:400}button{display:flex;justify-content:center;align-items:center;gap:10px;width:100%;padding:16px 20px;border:0;border-radius:10px;background:#3965FA;color:#FFFFFF;font:600 16px system-ui;cursor:pointer}fieldset:disabled button{opacity:.65;cursor:wait}a{color:#3965FA}input:focus-visible,button:focus-visible,a:focus-visible{outline:3px solid #3965FA;outline-offset:3px}.links{display:flex;flex-wrap:wrap;gap:16px;margin-top:26px;font-size:14px}.feedback{padding:16px;background:#E9EBFF;border:1px solid #99B7FC;border-radius:10px}footer{font-size:14px;margin-top:32px}.spin{animation:rotate 1s linear infinite}@keyframes rotate{to{transform:rotate(360deg)}}@media(prefers-reduced-motion:reduce){.spin{animation:none}}@media(max-width:500px){.activation section{padding:26px}}
</style>
