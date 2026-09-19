<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

type Destination = {
    key: string;
    title: string;
    description: string;
    url: string;
    category: string;
};

type SecurityCapabilities = {
    can_change_password: boolean;
    can_rotate_password_recovery_codes: boolean;
    can_rotate_totp_recovery_codes: boolean;
    can_logout: boolean;
};

const props = defineProps<{
    scope: {
        tenant_id: string;
        organization_id: string;
        outlet_id: string;
        device_id: string;
    };
    destinations: Destination[];
    security: SecurityCapabilities;
    correlation_id: string;
}>();

const securityOpen = ref(false);
const busy = ref(false);
const notice = ref('');
const error = ref('');
const changeCurrentPassword = ref('');
const changeNewPassword = ref('');
const changeNewPasswordConfirmation = ref('');
const changeTotpCode = ref('');
const passwordRecoveryPassword = ref('');
const totpRecoveryPassword = ref('');
const totpRecoveryTotpCode = ref('');
const passwordRecoveryCodes = ref<string[]>([]);
const totpRecoveryCodes = ref<string[]>([]);

const passwordChangeReady = computed(
    () =>
        changeCurrentPassword.value.length > 0 &&
        changeNewPassword.value.length > 0 &&
        changeNewPassword.value === changeNewPasswordConfirmation.value,
);

const xsrfCookie = (): string => {
    const pair = document.cookie
        .split('; ')
        .find((entry) => entry.startsWith('XSRF-TOKEN='));
    return pair ? decodeURIComponent(pair.slice('XSRF-TOKEN='.length)) : '';
};

async function post(url: string, body: Record<string, string>) {
    const headers: Record<string, string> = {
        Accept: 'application/json',
        'Content-Type': 'application/json',
    };
    const xsrf = xsrfCookie();
    if (xsrf) {
        headers['X-XSRF-TOKEN'] = xsrf;
    } else {
        headers['X-CSRF-TOKEN'] =
            document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
    }

    const response = await fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers,
        body: JSON.stringify(body),
    });
    return { response, data: response.status === 204 ? {} : await response.json().catch(() => ({})) };
}

function normalizeCodes(value: unknown): string[] {
    return Array.isArray(value) && value.every((item) => typeof item === 'string')
        ? value as string[]
        : [];
}

function resetMessages() {
    notice.value = '';
    error.value = '';
}

async function changePassword() {
    if (!props.security.can_change_password || !passwordChangeReady.value || busy.value) return;
    busy.value = true;
    resetMessages();

    try {
        const payload: Record<string, string> = {
            current_password: changeCurrentPassword.value,
            new_password: changeNewPassword.value,
        };
        if (changeTotpCode.value.trim() !== '') payload.totp_code = changeTotpCode.value.trim();

        const { response } = await post('/auth/password/change', payload);
        if (response.ok) {
            changeCurrentPassword.value = '';
            changeNewPassword.value = '';
            changeNewPasswordConfirmation.value = '';
            changeTotpCode.value = '';
            location.assign('/');
            return;
        }
        error.value = 'Password change was not completed. Verify the current password, MFA code, and password policy.';
    } catch {
        error.value = 'Password change could not be completed safely.';
    } finally {
        busy.value = false;
    }
}

async function rotatePasswordRecoveryCodes() {
    if (!props.security.can_rotate_password_recovery_codes || passwordRecoveryPassword.value.length === 0 || busy.value) return;
    busy.value = true;
    resetMessages();
    passwordRecoveryCodes.value = [];

    try {
        const { response, data } = await post('/auth/recovery/codes/rotate', {
            password: passwordRecoveryPassword.value,
        });
        passwordRecoveryPassword.value = '';
        if (response.ok) {
            passwordRecoveryCodes.value = normalizeCodes(data.recovery_codes);
            if (passwordRecoveryCodes.value.length > 0) {
                notice.value = 'New password recovery codes generated. Save them now; they are shown only in this response.';
                return;
            }
        }
        error.value = 'Password recovery codes were not rotated.';
    } catch {
        error.value = 'Password recovery codes could not be rotated safely.';
    } finally {
        busy.value = false;
    }
}

async function rotateTotpRecoveryCodes() {
    if (!props.security.can_rotate_totp_recovery_codes || totpRecoveryPassword.value.length === 0 || !/^\d{6}$/.test(totpRecoveryTotpCode.value) || busy.value) return;
    busy.value = true;
    resetMessages();
    totpRecoveryCodes.value = [];

    try {
        const { response, data } = await post('/auth/mfa/recovery/codes/rotate', {
            password: totpRecoveryPassword.value,
            totp_code: totpRecoveryTotpCode.value,
        });
        totpRecoveryPassword.value = '';
        totpRecoveryTotpCode.value = '';
        if (response.ok) {
            totpRecoveryCodes.value = normalizeCodes(data.recovery_codes);
            if (totpRecoveryCodes.value.length > 0) {
                notice.value = 'New authenticator recovery codes generated. Save them now; previous codes are no longer valid.';
                return;
            }
        }
        error.value = 'Authenticator recovery codes were not rotated.';
    } catch {
        error.value = 'Authenticator recovery codes could not be rotated safely.';
    } finally {
        busy.value = false;
    }
}

async function logout() {
    if (!props.security.can_logout || busy.value) return;
    busy.value = true;
    resetMessages();

    try {
        const { response } = await post('/auth/logout', {});
        if (response.ok || response.status === 204) {
            location.assign('/');
            return;
        }
        error.value = 'Sign out was not completed.';
    } catch {
        error.value = 'Sign out could not be completed safely.';
    } finally {
        busy.value = false;
    }
}

async function copyCodes(codes: string[]) {
    if (!navigator.clipboard || codes.length === 0) return;
    try {
        await navigator.clipboard.writeText(codes.join('\n'));
        notice.value = 'Recovery codes copied. Store them in a trusted password manager or other protected location.';
    } catch {
        error.value = 'The browser did not allow copying. Save the codes manually before leaving this page.';
    }
}
</script>

<template>
    <Head title="POS Operations" />
    <main class="hub-shell">
        <header class="hero">
            <div>
                <p class="eyebrow">oneQay · Point of Sale</p>
                <h1>POS Operations</h1>
                <p class="subtitle">
                    Satu pintu untuk workspace POS yang sedang delivered dan diizinkan pada konteks ini.
                    Setiap workspace tetap melakukan pemeriksaan otorisasi sendiri.
                </p>
            </div>
            <div class="hero-actions">
                <button class="security-trigger" type="button" @click="securityOpen = !securityOpen">
                    {{ securityOpen ? 'Close security' : 'Account & security' }}
                </button>
                <button v-if="props.security.can_logout" class="logout-trigger" type="button" :disabled="busy" @click="logout">
                    Sign out
                </button>
                <div class="scope-card">
                    <span>Outlet aktif</span>
                    <strong>{{ props.scope.outlet_id }}</strong>
                    <small>Device · {{ props.scope.device_id }}</small>
                </div>
            </div>
        </header>

        <section class="context-strip" aria-label="Current POS context">
            <div><span>Tenant</span><strong>{{ props.scope.tenant_id }}</strong></div>
            <div><span>Organization</span><strong>{{ props.scope.organization_id }}</strong></div>
            <div><span>Outlet</span><strong>{{ props.scope.outlet_id }}</strong></div>
            <div><span>Device</span><strong>{{ props.scope.device_id }}</strong></div>
        </section>

        <section v-if="securityOpen" class="security-panel" aria-label="Account and security">
            <div class="security-heading">
                <div>
                    <p class="eyebrow">Self-service security</p>
                    <h2>Account & security</h2>
                    <p>These controls reuse the existing first-party identity endpoints. No role or POS permission is granted here.</p>
                </div>
                <span class="secure-badge">Session protected</span>
            </div>

            <div class="security-grid">
                <form v-if="props.security.can_change_password" class="security-card" @submit.prevent="changePassword">
                    <span class="security-kicker">Credential</span>
                    <h3>Change password</h3>
                    <p>A successful change invalidates the current session and requires a fresh sign-in.</p>
                    <label>Current password<input v-model="changeCurrentPassword" type="password" autocomplete="current-password"></label>
                    <label>New password<input v-model="changeNewPassword" type="password" autocomplete="new-password"></label>
                    <label>Confirm new password<input v-model="changeNewPasswordConfirmation" type="password" autocomplete="new-password"></label>
                    <label>MFA code when required<input v-model="changeTotpCode" inputmode="numeric" maxlength="6" autocomplete="one-time-code"></label>
                    <button :disabled="busy || !passwordChangeReady">Change password</button>
                </form>

                <form v-if="props.security.can_rotate_password_recovery_codes" class="security-card" @submit.prevent="rotatePasswordRecoveryCodes">
                    <span class="security-kicker">Recovery</span>
                    <h3>Password recovery codes</h3>
                    <p>Rotate the single-use codes used to recover a forgotten password. Existing codes are replaced.</p>
                    <label>Current password<input v-model="passwordRecoveryPassword" type="password" autocomplete="current-password"></label>
                    <button :disabled="busy || passwordRecoveryPassword.length === 0">Generate new codes</button>
                    <div v-if="passwordRecoveryCodes.length" class="codes">
                        <code v-for="item in passwordRecoveryCodes" :key="item">{{ item }}</code>
                        <button class="secondary" type="button" @click="copyCodes(passwordRecoveryCodes)">Copy codes</button>
                    </div>
                </form>

                <form v-if="props.security.can_rotate_totp_recovery_codes" class="security-card" @submit.prevent="rotateTotpRecoveryCodes">
                    <span class="security-kicker">MFA recovery</span>
                    <h3>Authenticator recovery codes</h3>
                    <p>Generate replacement single-use codes for secure authenticator-factor recovery.</p>
                    <label>Current password<input v-model="totpRecoveryPassword" type="password" autocomplete="current-password"></label>
                    <label>Current 6-digit MFA code<input v-model="totpRecoveryTotpCode" inputmode="numeric" maxlength="6" autocomplete="one-time-code"></label>
                    <button :disabled="busy || totpRecoveryPassword.length === 0 || !/^\d{6}$/.test(totpRecoveryTotpCode)">Generate authenticator codes</button>
                    <div v-if="totpRecoveryCodes.length" class="codes">
                        <code v-for="item in totpRecoveryCodes" :key="item">{{ item }}</code>
                        <button class="secondary" type="button" @click="copyCodes(totpRecoveryCodes)">Copy codes</button>
                    </div>
                </form>
            </div>

            <p v-if="notice" class="notice" role="status">{{ notice }}</p>
            <p v-if="error" class="error" role="alert">{{ error }}</p>
        </section>

        <section class="workspace-panel">
            <div class="panel-heading">
                <div>
                    <p class="eyebrow">Authorized destinations</p>
                    <h2>Operational workspaces</h2>
                </div>
                <span class="count">{{ props.destinations.length }} available</span>
            </div>

            <div class="workspace-grid">
                <a
                    v-for="destination in props.destinations"
                    :key="destination.key"
                    class="workspace-card"
                    :href="destination.url"
                >
                    <div class="card-topline">
                        <span class="category">{{ destination.category }}</span>
                        <span class="arrow" aria-hidden="true">→</span>
                    </div>
                    <h3>{{ destination.title }}</h3>
                    <p>{{ destination.description }}</p>
                    <span class="open-label">Open workspace</span>
                </a>
            </div>
        </section>

        <aside class="safety-note">
            <strong>Fail-closed navigation</strong>
            <p>
                Hub ini tidak memberikan permission dan tidak mengaktifkan capability. Tautan hanya muncul bila
                route target sedang terdaftar dan permission current context memenuhi requirement workspace tersebut.
            </p>
        </aside>

        <footer>Correlation: <span class="mono">{{ props.correlation_id }}</span></footer>
    </main>
</template>

<style scoped>
.hub-shell { min-height:100vh; background:#f5f7fa; color:#172033; padding:32px; font-family:Inter,ui-sans-serif,system-ui,sans-serif; }
.hero { max-width:1200px; margin:0 auto 22px; display:flex; justify-content:space-between; gap:28px; align-items:flex-end; }
.hero-actions{display:grid;grid-template-columns:auto auto;gap:9px;align-items:end}.scope-card{grid-column:1/-1}
button{border:0;border-radius:11px;padding:10px 14px;font:inherit;font-size:.76rem;font-weight:800;cursor:pointer}.security-trigger{background:#172033;color:#fff}.logout-trigger,.secondary{background:#eef2f6;color:#344054}button:disabled{opacity:.45;cursor:not-allowed}
h1 { margin:5px 0 9px; font-size:clamp(2.1rem,4vw,3.2rem); letter-spacing:-.045em; }
.subtitle { max-width:720px; margin:0; color:#667085; line-height:1.65; }
.eyebrow { margin:0; color:#526175; font-size:.74rem; font-weight:800; text-transform:uppercase; letter-spacing:.12em; }
.scope-card,.context-strip,.workspace-panel,.safety-note,.security-panel { background:#fff; border:1px solid #e4e8ef; border-radius:18px; box-shadow:0 8px 24px rgba(16,24,40,.04); }
.scope-card { padding:17px 20px; min-width:260px; }.scope-card span,.scope-card small{display:block;color:#667085;font-size:.76rem}.scope-card strong{display:block;margin:5px 0;font-size:1.05rem;word-break:break-word}
.context-strip { max-width:1152px; margin:0 auto 20px; padding:16px 24px; display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:18px; }.context-strip span{display:block;color:#98a2b3;font-size:.7rem;text-transform:uppercase;letter-spacing:.08em;font-weight:700}.context-strip strong{display:block;margin-top:5px;font-size:.82rem;word-break:break-all}
.security-panel{max-width:1152px;margin:0 auto 20px;padding:24px}.security-heading{display:flex;justify-content:space-between;gap:18px;align-items:flex-start;margin-bottom:18px}.security-heading h2{margin:4px 0 5px}.security-heading p{margin:0;color:#667085;max-width:720px;line-height:1.55;font-size:.82rem}.secure-badge{background:#ecfdf3;color:#067647;border:1px solid #abefc6;border-radius:999px;padding:7px 10px;font-size:.7rem;font-weight:850}.security-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}.security-card{border:1px solid #e4e8ef;border-radius:15px;padding:18px;background:#fbfcfd;display:grid;gap:11px}.security-card h3{margin:0}.security-card p{margin:0 0 3px;color:#667085;font-size:.79rem;line-height:1.5}.security-kicker{font-size:.65rem;font-weight:900;letter-spacing:.1em;text-transform:uppercase;color:#667085}.security-card label{display:grid;gap:5px;font-size:.72rem;font-weight:750;color:#475467}.security-card input{border:1px solid #d0d5dd;border-radius:9px;padding:9px 10px;font:inherit}.security-card button:not(.secondary){background:#172033;color:#fff}.codes{display:grid;gap:6px;background:#f2f4f7;border-radius:10px;padding:10px}.codes code{font-size:.72rem;overflow-wrap:anywhere}.notice,.error{margin:14px 0 0;border-radius:10px;padding:10px 12px;font-size:.78rem}.notice{background:#ecfdf3;color:#067647}.error{background:#fff1f0;color:#b42318}
.workspace-panel { max-width:1152px; margin:0 auto 20px; padding:26px; }.panel-heading{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:20px}h2{margin:4px 0 0;font-size:1.25rem}.count{padding:7px 11px;border-radius:999px;background:#eef2f6;font-size:.74rem;font-weight:800}
.workspace-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:14px; }.workspace-card{display:flex;min-height:170px;flex-direction:column;text-decoration:none;color:inherit;border:1px solid #e4e8ef;border-radius:15px;padding:19px;background:#fbfcfd;transition:transform .15s ease,box-shadow .15s ease,border-color .15s ease}.workspace-card:hover,.workspace-card:focus-visible{transform:translateY(-2px);border-color:#c8d1de;box-shadow:0 12px 28px rgba(16,24,40,.08);outline:none}.card-topline{display:flex;justify-content:space-between;align-items:center}.category{font-size:.67rem;font-weight:900;letter-spacing:.11em;color:#667085}.arrow{font-size:1.25rem}.workspace-card h3{margin:20px 0 7px;font-size:1.05rem}.workspace-card p{margin:0;color:#667085;font-size:.83rem;line-height:1.55;flex:1}.open-label{margin-top:18px;font-size:.74rem;font-weight:800}
.safety-note { max-width:1152px; margin:0 auto; padding:18px 22px; display:grid; grid-template-columns:190px 1fr; gap:18px; align-items:start }.safety-note strong{font-size:.84rem}.safety-note p{margin:0;color:#667085;font-size:.8rem;line-height:1.55}footer{max-width:1200px;margin:14px auto 0;color:#98a2b3;font-size:.72rem}.mono{font-family:ui-monospace,SFMono-Regular,Menlo,monospace}
@media (max-width:980px){.security-grid{grid-template-columns:1fr}.workspace-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.context-strip{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media (max-width:640px){.hub-shell{padding:20px 14px}.hero{align-items:stretch;flex-direction:column}.hero-actions{grid-template-columns:1fr 1fr}.scope-card{min-width:0}.context-strip,.workspace-grid{grid-template-columns:1fr}.workspace-panel,.security-panel{padding:18px}.panel-heading,.security-heading{align-items:flex-start;flex-direction:column}.safety-note{grid-template-columns:1fr}}
</style>
