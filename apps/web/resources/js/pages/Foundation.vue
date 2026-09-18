<script setup lang="ts">
import { computed, ref } from 'vue'

defineProps<{ headline: string }>()

type MerchantLoginContext = {
  tenant_id: string
  identity_id: string
  organization_id: string
  outlet_id: string
  device_id: string
}

const enabled = document.querySelector('meta[name="oneqay-merchant-entry"]')?.getAttribute('content') === 'enabled'
const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? ''

const parseMerchantContext = (): MerchantLoginContext | null => {
  if (!enabled) return null
  const node = document.getElementById('oneqay-merchant-login-context')
  if (!node?.textContent) return null

  try {
    const raw = JSON.parse(node.textContent) as Record<string, unknown>
    const keys = ['tenant_id', 'identity_id', 'organization_id', 'outlet_id', 'device_id'] as const
    if (Object.keys(raw).sort().join('|') !== [...keys].sort().join('|')) return null

    for (const key of keys) {
      if (typeof raw[key] !== 'string' || raw[key].trim() === '') return null
    }

    return {
      tenant_id: raw.tenant_id as string,
      identity_id: raw.identity_id as string,
      organization_id: raw.organization_id as string,
      outlet_id: raw.outlet_id as string,
      device_id: raw.device_id as string,
    }
  } catch {
    return null
  }
}

const merchantContext = parseMerchantContext()
const contextReady = enabled && merchantContext !== null
const password = ref('')
const code = ref('')
const phase = ref<'login'|'challenge'|'enrollment'|'reenroll'>('login')
const busy = ref(false)
const error = ref('')
const provisioningUri = ref('')
const secret = ref('')
const ready = computed(() => contextReady && password.value.length > 0)

async function post(url: string, body: Record<string, string>) {
  const response = await fetch(url, {
    method: 'POST',
    credentials: 'same-origin',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrf,
    },
    body: JSON.stringify(body),
  })
  return { response, data: response.status === 204 ? {} : await response.json().catch(() => ({})) }
}

async function login() {
  if (!ready.value || busy.value || merchantContext === null) return
  busy.value = true
  error.value = ''

  try {
    const { response, data } = await post('/auth/login', {
      ...merchantContext,
      password: password.value,
    })
    password.value = ''

    if (response.ok && data.status === 'ok') {
      location.assign('/pos')
      return
    }
    if (response.status === 202 && data.code === 'MFA_CHALLENGE_REQUIRED') {
      phase.value = 'challenge'
      return
    }
    if (response.status === 202 && data.code === 'MFA_ENROLLMENT_REQUIRED') {
      const enrollment = await post('/auth/mfa/totp/enrollment/start', {})
      if (enrollment.response.ok) {
        provisioningUri.value = String(enrollment.data.provisioning_uri ?? '')
        secret.value = String(enrollment.data.secret ?? '')
        phase.value = 'enrollment'
        return
      }
    }

    error.value = 'Sign-in could not be completed. Verify the password and try again.'
  } catch {
    error.value = 'Sign-in could not be completed safely.'
  } finally {
    busy.value = false
  }
}

async function verify(enrollment = false) {
  if (!/^\d{6}$/.test(code.value) || busy.value) return
  busy.value = true
  error.value = ''

  try {
    const { response, data } = await post(
      enrollment ? '/auth/mfa/totp/enrollment/confirm' : '/auth/mfa/totp/challenge',
      { code: code.value },
    )
    code.value = ''

    if (response.ok) {
      if (enrollment) {
        phase.value = 'reenroll'
        return
      }
      if (data.status === 'ok') {
        location.assign('/pos')
        return
      }
    }

    error.value = 'Verification failed. No application access was granted.'
  } catch {
    error.value = 'Verification could not be completed safely.'
  } finally {
    busy.value = false
  }
}

function reset() {
  phase.value = 'login'
  code.value = ''
  provisioningUri.value = ''
  secret.value = ''
  error.value = ''
}
</script>

<template>
  <main v-if="contextReady" class="entry">
    <section class="brand">
      <p class="eyebrow">oneQay · Merchant Workspace</p>
      <h1>Run your business from one secure workspace.</h1>
      <p>Your installation context is already bound by the server. Sign in without copying internal tenant, organization, outlet, or device identifiers.</p>
      <div class="trust"><span>Context bound</span><span>Tenant isolated</span><span>Session controlled</span><span>Permission filtered</span></div>
    </section>

    <section class="card">
      <template v-if="phase === 'login'">
        <p class="eyebrow">First-party access</p>
        <h2>Sign in</h2>
        <p class="muted">The authorized merchant context is selected automatically for this installation.</p>
        <div class="context-ready"><strong>Installation context ready</strong><span>No internal IDs are required.</span></div>
        <form @submit.prevent="login">
          <label>Password<input v-model="password" type="password" required autocomplete="current-password"></label>
          <button :disabled="!ready || busy">{{ busy ? 'Verifying…' : 'Continue securely' }}</button>
        </form>
      </template>

      <template v-else-if="phase === 'challenge'">
        <p class="eyebrow">Additional verification</p>
        <h2>Authenticator code</h2>
        <p class="muted">Application access remains locked until MFA succeeds.</p>
        <form @submit.prevent="verify(false)">
          <label>6-digit code<input v-model="code" inputmode="numeric" maxlength="6" autocomplete="one-time-code"></label>
          <button :disabled="busy || !/^\d{6}$/.test(code)">Verify and open workspace</button>
        </form>
      </template>

      <template v-else-if="phase === 'enrollment'">
        <p class="eyebrow">MFA enrollment</p>
        <h2>Protect this account</h2>
        <p class="muted">Add this one-time enrollment material to your authenticator, then confirm a 6-digit code.</p>
        <div class="secret">
          <small>Provisioning URI</small><code>{{ provisioningUri }}</code>
          <small>Secret</small><code>{{ secret }}</code>
        </div>
        <form @submit.prevent="verify(true)">
          <label>6-digit code<input v-model="code" inputmode="numeric" maxlength="6" autocomplete="one-time-code"></label>
          <button :disabled="busy || !/^\d{6}$/.test(code)">Confirm enrollment</button>
        </form>
      </template>

      <template v-else>
        <p class="eyebrow">Enrollment complete</p>
        <h2>Sign in again</h2>
        <p class="muted">The enrollment session was invalidated by design. Start a fresh sign-in to establish full session authority.</p>
        <button @click="reset">Return to sign in</button>
      </template>

      <p v-if="error" class="error" role="alert">{{ error }}</p>
      <footer>No public registration · No implicit permission grant · Lab | zefry</footer>
    </section>
  </main>

  <main v-else class="foundation-shell">
    <section class="foundation-card" aria-labelledby="foundation-title">
      <p class="eyebrow">Lab | zefry · M7.1</p>
      <h1 id="foundation-title">{{ headline }}</h1>
      <p>Merchant sign-in remains unavailable until the exact installation context and required Local/Test/CI security gates are valid.</p>
    </section>
  </main>
</template>

<style scoped>
.entry{min-height:100vh;display:grid;grid-template-columns:1.05fr .95fr;background:#f4f6f8;color:#172033;font-family:Inter,ui-sans-serif,system-ui,sans-serif}.brand{padding:clamp(40px,8vw,110px);display:flex;flex-direction:column;justify-content:center;background:#101828;color:#fff}.brand h1{font-size:clamp(2.5rem,5vw,4.8rem);line-height:1.02;letter-spacing:-.055em;margin:0}.brand p{max-width:650px;color:#cbd5e1;line-height:1.7}.eyebrow{font-size:.72rem;font-weight:850;letter-spacing:.13em;text-transform:uppercase;opacity:.7}.trust{display:flex;gap:8px;flex-wrap:wrap;margin-top:14px}.trust span{border:1px solid #344054;border-radius:999px;padding:7px 10px;font-size:.7rem}.card{align-self:center;justify-self:center;width:min(480px,calc(100% - 40px));background:#fff;border:1px solid #e4e7ec;border-radius:22px;padding:32px;box-shadow:0 22px 55px rgba(16,24,40,.09)}h2{font-size:1.8rem;margin:0}.muted{color:#667085;line-height:1.55}.context-ready{display:grid;gap:3px;margin:18px 0;padding:12px 14px;border:1px solid #d1fadf;border-radius:12px;background:#ecfdf3;color:#05603a}.context-ready span{font-size:.74rem;color:#087443}form{display:grid;gap:14px}label{display:grid;gap:6px;font-size:.76rem;font-weight:750;color:#475467}input{border:1px solid #d0d5dd;border-radius:10px;padding:11px 12px;font:inherit}button{border:0;border-radius:11px;padding:12px 15px;background:#101828;color:#fff;font-weight:800}button:disabled{opacity:.45}.error{background:#fff1f0;color:#912018;border-radius:10px;padding:11px}.secret{display:grid;gap:7px;background:#f8fafc;padding:13px;border-radius:12px}.secret code{overflow-wrap:anywhere;font-size:.72rem}footer{margin-top:22px;color:#98a2b3;font-size:.68rem;text-align:center}.foundation-shell{min-height:100vh;display:grid;place-items:center;background:#f5f7fa;padding:24px}.foundation-card{max-width:720px;background:#fff;border:1px solid #e4e7ec;border-radius:18px;padding:30px;box-shadow:0 12px 32px rgba(16,24,40,.06)}@media(max-width:850px){.entry{grid-template-columns:1fr}.brand{padding:42px 24px}.card{margin:28px auto}}
</style>
