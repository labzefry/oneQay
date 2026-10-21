<script setup lang="ts">
// Author by Lab | zefry. Authentication and MFA are handled only by the canonical endpoints.
import { ref } from 'vue';
import { locale } from '../../i18n';
const tr = (id: string, en: string) => locale.value === 'id' ? id : en;
const props = defineProps<{ mfa_state?: string | null }>();
const expired = new URLSearchParams(location.search).get('reason') === 'session-expired';
const email = ref(''); const password = ref(''); const business = ref(''); const code = ref(''); const secret = ref('');
const busy = ref(false); const message = ref('');
function headers() { return { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '' }; }
async function send(path: string, body: Record<string, string>, kind: string) {
    busy.value = true; message.value = '';
    try {
        const r = await fetch(path, { method: 'POST', credentials: 'same-origin', headers: headers(), body: JSON.stringify(body) });
        if (!r.ok) throw new Error();
        const result = r.status === 204 ? {} : await r.json();
        if (kind === 'enroll') { secret.value = result.secret; return; }
        if (kind === 'confirm' || result.status === 'mfa_required') { window.location.replace('/business-login'); return; }
        window.location.replace('/pos');
    } catch { message.value = tr('Belum berhasil. Periksa identitas, kata sandi atau kode authenticator Anda.', 'Unable to complete. Check your account, password or authenticator code.'); }
    finally { password.value = ''; code.value = ''; busy.value = false; }
}
</script>
<template><main class="login"><section><a class="back-home" href="/">← oneQay</a><span>ONEQAY BUSINESS</span><p v-if="expired" role="status">{{ tr('Sesi Anda sudah berakhir. Silakan masuk kembali untuk melanjutkan.', 'Your session has ended. Sign in again to continue.') }}</p><h1>{{ props.mfa_state ? tr('Verifikasi keamanan', 'Security verification') : tr('Masuk ke bisnis Anda', 'Sign in to your business') }}</h1><template v-if="props.mfa_state === 'enrollment_required'"><p>{{ tr('Tambahkan oneQay ke aplikasi authenticator dengan kunci berikut, lalu masukkan kode enam digit. Setelah konfirmasi, masuk kembali.', 'Add oneQay to your authenticator app using the following key, then enter the six-digit code. Sign in again after confirmation.') }}</p><button v-if="!secret" :disabled="busy" @click="send('/auth/mfa/totp/enrollment/start', {}, 'enroll')">{{ tr('Mulai penyiapan authenticator', 'Start authenticator setup') }}</button><template v-else><p class="secret">{{ secret }}</p><form @submit.prevent="send('/auth/mfa/totp/enrollment/confirm', { code }, 'confirm')"><label>{{ tr('Kode authenticator', 'Authenticator code') }}<input v-model="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required autocomplete="one-time-code" /></label><button :disabled="busy">{{ tr('Konfirmasi authenticator', 'Confirm authenticator') }}</button></form></template></template><form v-else-if="props.mfa_state === 'challenge_required'" @submit.prevent="send('/auth/mfa/totp/challenge', { code }, 'challenge')"><label>{{ tr('Kode authenticator', 'Authenticator code') }}<input v-model="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required autocomplete="one-time-code" /></label><button :disabled="busy">{{ tr('Verifikasi & masuk', 'Verify & sign in') }}</button></form><form v-else @submit.prevent="send('/business-login', { email, password, business_id: business }, 'login')"><fieldset :disabled="busy"><label>{{ tr('Email akun bisnis atau kasir', 'Business or cashier account email') }}<input v-model="email" type="email" required autocomplete="username" /></label><label>{{ tr('Kata sandi', 'Password') }}<input v-model="password" type="password" required autocomplete="current-password" /></label><details><summary>{{ tr('Memiliki lebih dari satu bisnis?', 'Have more than one business?') }}</summary><label>{{ tr('ID bisnis dari pemilik platform', 'Business ID from the platform owner') }}<input v-model="business" maxlength="32" /></label></details><button>{{ tr('Masuk', 'Sign in') }}</button></fieldset></form><p role="status">{{ message }}</p><p v-if="!props.mfa_state">{{ tr('Belum punya akun bisnis?', 'Need a business account?') }} <a href="/register">{{ tr('Daftar bisnis', 'Register your business') }}</a></p><footer>Copyright oneQay</footer></section></main></template>
<style scoped>
.back-home{display:block;margin-bottom:25px;text-decoration:none;font-size:20px;font-weight:800;color:#3965FA}.login a{color:#3965FA}.login{display:grid;place-items:center;min-height:85vh;background:#FFFFFF;padding:32px 16px;font-family:system-ui;color:#1B1B39}.login section{max-width:480px;width:100%;box-sizing:border-box;background:#FFFFFF;padding:36px;border:1px solid #EDEDED;border-radius:20px}.login span{font-size:14px;letter-spacing:2px;color:#3965FA;font-weight:800}h1{font-size:29px;letter-spacing:-1px}p{line-height:1.8;font-size:14px;color:#3965FA}label{display:flex;flex-direction:column;gap:9px;font-size:14px;font-weight:650;margin:22px 0}input{padding:13px;border:1px solid #EDEDED;border-radius:9px;font:inherit}button{padding:14px 22px;background:#3965FA;border:0;border-radius:9px;color:#FFFFFF;font-weight:700;cursor:pointer}button:disabled{opacity:.5}fieldset{border:0;margin:0;padding:0}details{font-size:14px;color:#3965FA;margin-bottom:22px}.secret{font:600 16px monospace;padding:15px;background:#EDEDED;overflow-wrap:anywhere}footer{font-size:14px;color:#1B1B39;margin-top:25px}

.login{background:var(--oq-white);color:var(--oq-navy);font-size:1rem}
.login section{background:var(--oq-white);border-color:var(--oq-light);box-shadow:0 16px 38px #1B1B3914}
.login label,.login p,.login details,.login footer,.login span{font-size:.875rem;color:var(--oq-navy)}
.login input{min-height:44px;font-size:1rem;color:var(--oq-dark);border:1px solid var(--oq-soft)}
.login button{background:var(--oq-primary);color:var(--oq-white);min-height:44px}
.login button:focus-visible,.login input:focus-visible{outline:3px solid var(--oq-primary);outline-offset:3px}

</style>
