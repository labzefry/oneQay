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

type Phase =
  | 'login'
  | 'challenge'
  | 'enrollment'
  | 'reenroll'
  | 'password-recovery-code'
  | 'password-recovery-reset'
  | 'totp-recovery-code'
  | 'totp-recovery-confirm'

const metaEnabled = (name: string) =>
  document.querySelector(`meta[name="${name}"]`)?.getAttribute('content') === 'enabled'

const entryFlag = metaEnabled('oneqay-merchant-entry')
const passwordRecoveryEnabled = metaEnabled('oneqay-password-recovery')
const totpRecoveryEnabled = metaEnabled('oneqay-totp-recovery')

const parseMerchantContext = (): MerchantLoginContext | null => {
  if (!entryFlag) return null
  const node = document.getElementById('oneqay-merchant-login-context')
  if (!node?.textContent) return null

  try {
    const raw = JSON.parse(node.textContent) as Record<string, unknown>
    const keys = ['tenant_id', 'identity_id', 'organization_id', 'outlet_id', 'device_id'] as const
    if (Object.keys(raw).sort().join('|') !== [...keys].sort().join('|')) return null

    for (const key of keys) {
      if (typeof raw[key] !== 'string' || raw[key].trim() === '' || raw[key] !== raw[key].trim()) return null
    }

    return raw as MerchantLoginContext
  } catch {
    return null
  }
}

const merchantContext = parseMerchantContext()
const enabled = entryFlag && merchantContext !== null
const password = ref('')
const code = ref('')
const recoveryCode = ref('')
const recoveredPassword = ref('')
const recoveredPasswordConfirmation = ref('')
const phase = ref<Phase>('login')
const busy = ref(false)
const error = ref('')
const provisioningUri = ref('')
const secret = ref('')

const ready = computed(() => enabled && password.value.length > 0)
const recoveryPasswordReady = computed(
  () =>
    recoveredPassword.value.length > 0 &&
    recoveredPassword.value === recoveredPasswordConfirmation.value,
)

const xsrfCookie = (): string => {
  const pair = document.cookie
    .split('; ')
    .find((entry) => entry.startsWith('XSRF-TOKEN='))
  if (!pair) return ''
  return decodeURIComponent(pair.slice('XSRF-TOKEN='.length))
}

async function post(url: string, body: Record<string, string>) {
  const headers: Record<string, string> = {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  }
  const xsrf = xsrfCookie()
  if (xsrf) {
    headers['X-XSRF-TOKEN'] = xsrf
  } else {
    headers['X-CSRF-TOKEN'] =
      document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? ''
  }

  const response = await fetch(url, {
    method: 'POST',
    credentials: 'same-origin',
    headers,
    body: JSON.stringify(body),
  })
  return { response, data: response.status === 204 ? {} : await response.json().catch(() => ({})) }
}

function clearSensitiveState() {
  password.value = ''
  code.value = ''
  recoveryCode.value = ''
  recoveredPassword.value = ''
  recoveredPasswordConfirmation.value = ''
  provisioningUri.value = ''
  secret.value = ''
  error.value = ''
}

function returnToLogin() {
  clearSensitiveState()
  phase.value = 'login'
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

async function provePasswordRecovery() {
  if (!passwordRecoveryEnabled || recoveryCode.value.trim() === '' || busy.value) return
  busy.value = true
  error.value = ''

  try {
    const { response, data } = await post('/auth/recovery/proof', {
      recovery_code: recoveryCode.value.trim(),
    })
    recoveryCode.value = ''
    if (response.ok && data.state === 'password_reset_required') {
      phase.value = 'password-recovery-reset'
      return
    }
    error.value = 'Recovery proof was not accepted.'
  } catch {
    error.value = 'Recovery proof could not be completed safely.'
  } finally {
    busy.value = false
  }
}

async function resetRecoveredPassword() {
  if (!recoveryPasswordReady.value || busy.value) return
  busy.value = true
  error.value = ''

  try {
    const { response } = await post('/auth/recovery/password-reset', {
      password: recoveredPassword.value,
    })
    if (response.ok) {
      returnToLogin()
      return
    }
    error.value = 'Password reset was not completed.'
  } catch {
    error.value = 'Password reset could not be completed safely.'
  } finally {
    busy.value = false
  }
}

async function proveTotpRecovery() {
  if (!totpRecoveryEnabled || recoveryCode.value.trim() === '' || busy.value) return
  busy.value = true
  error.value = ''

  try {
    const proof = await post('/auth/mfa/recovery/proof', {
      recovery_code: recoveryCode.value.trim(),
    })
    recoveryCode.value = ''
    if (!proof.response.ok || proof.data.state !== 'totp_factor_replacement_required') {
      error.value = 'Authenticator recovery proof was not accepted.'
      return
    }

    const replacement = await post('/auth/mfa/recovery/totp/replace/start', {})
    if (!replacement.response.ok) {
      error.value = 'Authenticator replacement could not be started.'
      return
    }

    provisioningUri.value = String(replacement.data.provisioning_uri ?? '')
    secret.value = String(replacement.data.secret ?? '')
    phase.value = 'totp-recovery-confirm'
  } catch {
    error.value = 'Authenticator recovery could not be completed safely.'
  } finally {
    busy.value = false
  }
}

async function confirmTotpRecovery() {
  if (!/^\d{6}$/.test(code.value) || busy.value) return
  busy.value = true
  error.value = ''

  try {
    const { response, data } = await post('/auth/mfa/recovery/totp/replace/confirm', {
      totp_code: code.value,
    })
    code.value = ''
    if (response.ok && data.requires_fresh_login === true) {
      returnToLogin()
      return
    }
    error.value = 'Authenticator replacement was not completed.'
  } catch {
    error.value = 'Authenticator replacement could not be completed safely.'
  } finally {
    busy.value = false
  }
}
</script>

<template>
  <main v-if="enabled" class="entry">
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
        <div v-if="passwordRecoveryEnabled || totpRecoveryEnabled" class="recovery-actions">
          <button v-if="passwordRecoveryEnabled" class="secondary" type="button" @click="phase = 'password-recovery-code'; error = ''">Recover password</button>
          <button v-if="totpRecoveryEnabled" class="secondary" type="button" @click="phase = 'totp-recovery-code'; error = ''">Lost authenticator?</button>
        </div>
      </template>

      <template v-else-if="phase === 'challenge'">
        <p class="eyebrow">Additional verification</p>
        <h2>Authenticator code</h2>
        <p class="muted">Application access remains locked until MFA succeeds.</p>
        <form @submit.prevent="verify(false)">
          <label>6-digit code<input v-model="code" inputmode="numeric" maxlength="6" autocomplete="one-time-code"></label>
          <button :disabled="busy || !/^\d{6}$/.test(code)">Verify and open workspace</button>
        </form>
        <button v-if="totpRecoveryEnabled" class="text-button" type="button" @click="phase = 'totp-recovery-code'; code = ''; error = ''">Use an authenticator recovery code</button>
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

      <template v-else-if="phase === 'reenroll'">
        <p class="eyebrow">Enrollment complete</p>
        <h2>Sign in again</h2>
        <p class="muted">The enrollment session was invalidated by design. Start a fresh sign-in to establish full session authority.</p>
        <button @click="returnToLogin">Return to sign in</button>
      </template>

      <template v-else-if="phase === 'password-recovery-code'">
        <p class="eyebrow">Account recovery</p>
        <h2>Use a password recovery code</h2>
        <p class="muted">Recovery codes are single-use. No tenant or identity identifier is accepted from the browser.</p>
        <form @submit.prevent="provePasswordRecovery">
          <label>Recovery code<input v-model="recoveryCode" autocomplete="one-time-code" spellcheck="false"></label>
          <button :disabled="busy || recoveryCode.trim() === ''">Verify recovery code</button>
        </form>
        <button class="text-button" type="button" @click="returnToLogin">Back to sign in</button>
      </template>

      <template v-else-if="phase === 'password-recovery-reset'">
        <p class="eyebrow">Restricted recovery session</p>
        <h2>Set a new password</h2>
        <p class="muted">The server has bound this short-lived session to the verified recovery proof. The password must satisfy the canonical server policy.</p>
        <form @submit.prevent="resetRecoveredPassword">
          <label>New password<input v-model="recoveredPassword" type="password" autocomplete="new-password"></label>
          <label>Confirm new password<input v-model="recoveredPasswordConfirmation" type="password" autocomplete="new-password"></label>
          <button :disabled="busy || !recoveryPasswordReady">Reset password</button>
        </form>
      </template>

      <template v-else-if="phase === 'totp-recovery-code'">
        <p class="eyebrow">Authenticator recovery</p>
        <h2>Use an authenticator recovery code</h2>
        <p class="muted">A valid single-use code opens only the bounded factor-replacement session.</p>
        <form @submit.prevent="proveTotpRecovery">
          <label>Recovery code<input v-model="recoveryCode" autocomplete="one-time-code" spellcheck="false"></label>
          <button :disabled="busy || recoveryCode.trim() === ''">Start secure replacement</button>
        </form>
        <button class="text-button" type="button" @click="returnToLogin">Back to sign in</button>
      </template>

      <template v-else>
        <p class="eyebrow">Authenticator replacement</p>
        <h2>Enroll the replacement factor</h2>
        <p class="muted">Add this one-time material to your authenticator, then confirm the new 6-digit code. Completion requires a fresh login.</p>
        <div class="secret">
          <small>Provisioning URI</small><code>{{ provisioningUri }}</code>
          <small>Secret</small><code>{{ secret }}</code>
        </div>
        <form @submit.prevent="confirmTotpRecovery">
          <label>6-digit code<input v-model="code" inputmode="numeric" maxlength="6" autocomplete="one-time-code"></label>
          <button :disabled="busy || !/^\d{6}$/.test(code)">Confirm replacement</button>
        </form>
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
.entry{min-height:100vh;display:grid;grid-template-columns:1.05fr .95fr;background:#f4f6f8;color:#172033;font-family:Inter,ui-sans-serif,system-ui,sans-serif}.brand{padding:clamp(40px,8vw,110px);display:flex;flex-direction:column;justify-content:center;background:#101828;color:#fff}.brand h1{font-size:clamp(2.5rem,5vw,4.8rem);line-height:1.02;letter-spacing:-.055em;margin:0}.brand p{max-width:650px;color:#cbd5e1;line-height:1.7}.eyebrow{font-size:.72rem;font-weight:850;letter-spacing:.13em;text-transform:uppercase;opacity:.7}.trust{display:flex;gap:8px;flex-wrap:wrap;margin-top:14px}.trust span{border:1px solid #344054;border-radius:999px;padding:7px 10px;font-size:.7rem}.card{align-self:center;justify-self:center;width:min(500px,calc(100% - 40px));background:#fff;border:1px solid #e4e7ec;border-radius:22px;padding:32px;box-shadow:0 22px 55px rgba(16,24,40,.09)}h2{font-size:1.8rem;margin:0}.muted{color:#667085;line-height:1.55}.context-ready{display:grid;gap:3px;margin:18px 0;padding:12px 14px;border:1px solid #d1fadf;border-radius:12px;background:#ecfdf3;color:#05603a}.context-ready span{font-size:.74rem;color:#087443}form{display:grid;gap:14px}label{display:grid;gap:6px;font-size:.76rem;font-weight:750;color:#475467}input{border:1px solid #d0d5dd;border-radius:10px;padding:11px 12px;font:inherit}button{border:0;border-radius:11px;padding:12px 15px;background:#101828;color:#fff;font-weight:800;cursor:pointer}button:disabled{opacity:.45;cursor:not-allowed}.secondary{background:#eef2f6;color:#344054}.text-button{margin-top:14px;background:transparent;color:#475467;padding:8px 0;text-align:left}.recovery-actions{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:14px}.error{background:#fff1f0;color:#912018;border-radius:10px;padding:11px}.secret{display:grid;gap:7px;background:#f8fafc;padding:13px;border-radius:12px}.secret code{overflow-wrap:anywhere;font-size:.72rem}footer{margin-top:22px;color:#98a2b3;font-size:.68rem;text-align:center}.foundation-shell{min-height:100vh;display:grid;place-items:center;background:#f5f7fa;padding:24px}.foundation-card{max-width:720px;background:#fff;border:1px solid #e4e7ec;border-radius:18px;padding:30px;box-shadow:0 12px 32px rgba(16,24,40,.06)}@media(max-width:850px){.entry{grid-template-columns:1fr}.brand{padding:42px 24px}.card{margin:28px auto}}@media(max-width:520px){.recovery-actions{grid-template-columns:1fr}}
</style>
