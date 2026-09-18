<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps<{ csrf_token: string; pos_url: string }>();

const tenantId = ref('');
const identityId = ref('');
const password = ref('');
const organizationId = ref('');
const outletId = ref('');
const deviceId = ref('');
const code = ref('');
const phase = ref<'login'|'challenge'|'enrollment'|'enrollment-confirmed'>('login');
const provisioningUri = ref('');
const enrollmentSecret = ref('');
const busy = ref(false);
const error = ref('');

const canSubmit = computed(() => tenantId.value.trim() && identityId.value.trim() && password.value && organizationId.value.trim());

async function post(url: string, body: Record<string,string>) {
  const response = await fetch(url, {
    method: 'POST',
    credentials: 'same-origin',
    headers: {'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':props.csrf_token},
    body: JSON.stringify(body),
  });
  const data = response.status === 204 ? {} : await response.json().catch(() => ({}));
  return { response, data };
}

async function login() {
  if (!canSubmit.value || busy.value) return;
  busy.value = true; error.value = '';
  const body: Record<string,string> = {
    tenant_id: tenantId.value.trim(), identity_id: identityId.value.trim(), password: password.value,
    organization_id: organizationId.value.trim(),
  };
  if (outletId.value.trim()) body.outlet_id = outletId.value.trim();
  if (deviceId.value.trim()) body.device_id = deviceId.value.trim();
  try {
    const {response,data} = await post('/auth/login', body);
    password.value = '';
    if (response.ok && data.status === 'ok') { window.location.assign(props.pos_url); return; }
    if (response.status === 202 && data.code === 'MFA_CHALLENGE_REQUIRED') { phase.value='challenge'; return; }
    if (response.status === 202 && data.code === 'MFA_ENROLLMENT_REQUIRED') {
      const started = await post('/auth/mfa/totp/enrollment/start', {});
      if (started.response.ok && started.data.status === 'enrollment_pending') {
        provisioningUri.value = String(started.data.provisioning_uri ?? '');
        enrollmentSecret.value = String(started.data.secret ?? '');
        phase.value='enrollment'; return;
      }
    }
    error.value = 'Sign-in could not be completed. Verify the authorized merchant context and try again.';
  } catch { error.value = 'Sign-in could not be completed safely. Try again.'; }
  finally { busy.value = false; }
}

async function verifyMfa(enrollment = false) {
  if (!/^\d{6}$/.test(code.value) || busy.value) return;
  busy.value=true; error.value='';
  try {
    const target = enrollment ? '/auth/mfa/totp/enrollment/confirm' : '/auth/mfa/totp/challenge';
    const {response,data} = await post(target,{code:code.value});
    code.value='';
    if (response.ok) {
      if (enrollment) { phase.value='enrollment-confirmed'; return; }
      if (data.status === 'ok') { window.location.assign(props.pos_url); return; }
    }
    error.value='Verification failed. No application access was granted.';
  } catch { error.value='Verification could not be completed safely.'; }
  finally { busy.value=false; }
}

function resetLogin(){ phase.value='login'; code.value=''; provisioningUri.value=''; enrollmentSecret.value=''; error.value=''; }
</script>

<template>
  <Head title="Merchant Sign In" />
  <main class="shell">
    <section class="brand">
      <p class="eyebrow">oneQay · Merchant Workspace</p>
      <h1>Run your business from one secure workspace.</h1>
      <p class="lead">Sign in with the merchant context provisioned for this installation. Access remains tenant-scoped, device-aware, and deny-by-default.</p>
      <div class="trust"><span>Tenant isolated</span><span>Session controlled</span><span>Permission filtered</span></div>
    </section>
    <section class="card">
      <div v-if="phase==='login'">
        <p class="eyebrow">First-party access</p><h2>Sign in</h2>
        <p class="muted">Use the exact context assigned by your administrator.</p>
        <form @submit.prevent="login" autocomplete="on">
          <label>Tenant ID<input v-model="tenantId" required autocomplete="organization" /></label>
          <label>Identity ID<input v-model="identityId" required autocomplete="username" /></label>
          <label>Password<input v-model="password" required type="password" autocomplete="current-password" /></label>
          <label>Organization ID<input v-model="organizationId" required /></label>
          <div class="pair"><label>Outlet ID<input v-model="outletId" /></label><label>Device ID<input v-model="deviceId" /></label></div>
          <button :disabled="!canSubmit || busy">{{ busy ? 'Verifying…' : 'Continue securely' }}</button>
        </form>
      </div>
      <div v-else-if="phase==='challenge'">
        <p class="eyebrow">Additional verification</p><h2>Enter authenticator code</h2>
        <p class="muted">Your password was accepted, but application access remains locked until MFA succeeds.</p>
        <form @submit.prevent="verifyMfa(false)"><label>6-digit code<input v-model="code" inputmode="numeric" maxlength="6" autocomplete="one-time-code" /></label><button :disabled="busy || !/^\d{6}$/.test(code)">Verify and open workspace</button></form>
      </div>
      <div v-else-if="phase==='enrollment'">
        <p class="eyebrow">MFA enrollment required</p><h2>Protect this account</h2>
        <p class="muted">Add the provisioning URI to your authenticator, then confirm a 6-digit code. The enrollment material is shown only for this guarded flow.</p>
        <div class="secret"><small>Provisioning URI</small><code>{{ provisioningUri }}</code><small>Secret</small><code>{{ enrollmentSecret }}</code></div>
        <form @submit.prevent="verifyMfa(true)"><label>6-digit code<input v-model="code" inputmode="numeric" maxlength="6" autocomplete="one-time-code" /></label><button :disabled="busy || !/^\d{6}$/.test(code)">Confirm enrollment</button></form>
      </div>
      <div v-else>
        <p class="eyebrow">Enrollment complete</p><h2>Sign in again</h2>
        <p class="muted">The enrollment session was invalidated by design. Start a fresh sign-in to establish full session authority.</p>
        <button @click="resetLogin">Return to sign in</button>
      </div>
      <p v-if="error" class="error" role="alert">{{ error }}</p>
      <footer>No public registration · No implicit permission grant · Lab | zefry</footer>
    </section>
  </main>
</template>

<style scoped>
.shell{min-height:100vh;display:grid;grid-template-columns:1.05fr .95fr;background:#f4f6f8;color:#172033;font-family:Inter,ui-sans-serif,system-ui,sans-serif}.brand{padding:clamp(40px,8vw,110px);display:flex;flex-direction:column;justify-content:center;background:#101828;color:#fff}.eyebrow{font-size:.72rem;font-weight:850;letter-spacing:.13em;text-transform:uppercase;opacity:.7;margin:0 0 10px}.brand h1{font-size:clamp(2.5rem,5vw,4.8rem);line-height:1.02;letter-spacing:-.055em;max-width:780px;margin:0}.lead{max-width:620px;color:#cbd5e1;line-height:1.7;margin:25px 0}.trust{display:flex;gap:9px;flex-wrap:wrap}.trust span{border:1px solid #344054;border-radius:999px;padding:8px 11px;font-size:.72rem}.card{align-self:center;justify-self:center;width:min(480px,calc(100% - 40px));background:#fff;border:1px solid #e4e7ec;border-radius:22px;padding:32px;box-shadow:0 22px 55px rgba(16,24,40,.09)}h2{font-size:1.8rem;letter-spacing:-.035em;margin:0}.muted{color:#667085;line-height:1.55;margin:8px 0 22px}form{display:grid;gap:14px}label{display:grid;gap:6px;font-size:.76rem;font-weight:750;color:#475467}input{border:1px solid #d0d5dd;border-radius:10px;padding:11px 12px;font:inherit;color:#101828;background:#fff;outline:none}input:focus{border-color:#667085;box-shadow:0 0 0 3px #f2f4f7}.pair{display:grid;grid-template-columns:1fr 1fr;gap:12px}button{border:0;border-radius:11px;padding:12px 15px;background:#101828;color:#fff;font-weight:800;cursor:pointer}button:disabled{opacity:.45;cursor:not-allowed}.error{background:#fff1f0;color:#912018;border-radius:10px;padding:11px 12px;font-size:.8rem}.secret{display:grid;gap:7px;background:#f8fafc;border:1px solid #e4e7ec;border-radius:12px;padding:13px;margin:0 0 16px}.secret code{overflow-wrap:anywhere;font-size:.72rem}.secret small{color:#667085;font-weight:800}footer{margin-top:22px;color:#98a2b3;font-size:.68rem;text-align:center}@media(max-width:850px){.shell{grid-template-columns:1fr}.brand{padding:42px 24px}.brand h1{font-size:2.4rem}.card{margin:28px auto}.pair{grid-template-columns:1fr}}
</style>
