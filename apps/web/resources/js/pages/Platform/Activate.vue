<script setup lang="ts">
// Author by Lab | zefry
import { ref } from 'vue';
import { locale } from '../../i18n';
import { consumeInvitationToken } from '../../platform-invitation';
const tr = (id: string, en: string) => locale.value === 'id' ? id : en;
const token = ref(consumeInvitationToken());
const password = ref(''); const confirm = ref(''); const busy = ref(false); const message = ref('');
const context = ref<Record<string, string> | null>(null);
async function activate() {
    if (password.value !== confirm.value) { message.value = tr('Konfirmasi kata sandi tidak sama.', 'Passwords do not match.'); return; }
    busy.value = true; message.value = '';
    try {
        const response = await fetch('/activate-business', { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '' }, body: JSON.stringify({ token: token.value, password: password.value }) });
        if (!response.ok) throw new Error();
        const result = await response.json(); context.value = result.login_context; password.value = ''; confirm.value = ''; token.value = '';
    } catch { message.value = tr('Aktivasi belum terkonfirmasi. Periksa masa berlaku undangan atau coba kembali dengan kata sandi yang sama.', 'Activation is not confirmed. Check invitation expiry or retry with the same password.'); }
    finally { busy.value = false; }
}
function download() {
    const data = new Blob([JSON.stringify({ application: 'oneQay', ...context.value }, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(data); const link = document.createElement('a'); link.href = url; link.download = 'oneqay-login-context.json'; link.click(); URL.revokeObjectURL(url);
}
</script>
<template><main class="activation"><section><span class="eyebrow">ONEQAY BUSINESS</span><h1>{{ context ? tr('Bisnis Anda aktif', 'Your business is active') : tr('Aktifkan akun bisnis', 'Activate your business') }}</h1><template v-if="!context"><p>{{ tr('Buat kata sandi untuk akun pemilik bisnis. Anda akan menyiapkan authenticator saat masuk pertama kali.', 'Create your business owner password. You will set up an authenticator on your first sign-in.') }}</p><form @submit.prevent="activate"><fieldset :disabled="busy || !token"><label>{{ tr('Kata sandi baru', 'New password') }}<input v-model="password" type="password" minlength="12" maxlength="72" required autocomplete="new-password" /></label><label>{{ tr('Konfirmasi kata sandi', 'Confirm password') }}<input v-model="confirm" type="password" minlength="12" maxlength="72" required autocomplete="new-password" /></label><button>{{ busy ? tr('Mengaktifkan…', 'Activating…') : tr('Aktifkan bisnis', 'Activate business') }}</button></fieldset></form><p v-if="!token" role="alert">{{ tr('Buka halaman ini dari tautan undangan email Anda.', 'Open this page from your email invitation link.') }}</p></template><template v-else><p>{{ tr('Simpan identitas login berikut. Kata sandi tidak disimpan dalam berkas ini. Gunakan identitas ini di halaman login.', 'Save the following login identifiers. Your password is not included in this file. Use these identifiers on the sign-in page.') }}</p><dl><template v-for="(value, key) in context" :key="key"><dt>{{ key }}</dt><dd>{{ value }}</dd></template></dl><button @click="download">{{ tr('Simpan identitas login', 'Save login identifiers') }}</button><a href="/business-login">{{ tr('Lanjut masuk', 'Continue to sign in') }} →</a></template><p role="status">{{ message }}</p><footer>Author by Lab | zefry</footer></section></main></template>
<style scoped>
.activation{min-height:85vh;padding:40px 16px;background:#f1f6f4;font-family:system-ui;color:#173f3d;display:grid;place-items:center}.activation section{box-sizing:border-box;width:100%;max-width:570px;padding:36px;background:white;border:1px solid #d8e5e0;border-radius:22px;box-shadow:0 20px 60px #173e3210}.eyebrow{font-size:11px;letter-spacing:2px;color:#40886c;font-weight:800}h1{font-size:30px;letter-spacing:-1px}p{font-size:14px;line-height:1.8;color:#627b76}fieldset{border:0;margin:0;padding:0}label{display:flex;flex-direction:column;gap:9px;margin:20px 0;font-size:13px;font-weight:600}input{padding:13px;border:1px solid #bdd2c8;border-radius:9px;font:inherit}button{padding:14px 20px;border:0;border-radius:9px;background:#17694e;color:white;font-weight:700;cursor:pointer}button:disabled{opacity:.5}a{display:block;margin-top:22px;color:#17694e}dt{font-size:11px;color:#6d8580;margin-top:15px}dd{margin:6px 0;font-size:13px;overflow-wrap:anywhere}footer{font-size:11px;color:#84968f;margin-top:24px}@media(max-width:500px){.activation section{padding:25px}}
</style>
