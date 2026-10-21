<script setup lang="ts">
// Author by Lab | zefry
import { computed, onBeforeUnmount, ref } from 'vue';
import { t, locale } from '../../i18n';
import OperationFeedback from '../../components/OperationFeedback.vue';
import type { MutationStatus } from '../../use-business-mutation';
const props = defineProps<{ identity_id: string; outlet_id: string; templates: Record<string, { title: string; permissions: string[] }>; step_up_url: string | null; initial_role?: string; return_url?: string }>();
const identity = ref(props.identity_id); const role = ref(props.initial_role && props.templates[props.initial_role] ? props.initial_role : 'oneqay-business-manager'); const action = ref('assign');
const returnUrl = computed(() => props.return_url === '/pos/shifts/close' ? '/pos/shifts/close' : '/pos');
const busy = ref(false); const uncertain = ref(false); const message = ref(''); const saved = ref(false);
const password = ref(''); const code = ref(''); const verifying = ref(false);
const pending = ref<{ operation_id: string; identity_id: string; role: string; action: string } | null>(null);
const operationProgress = ref<MutationStatus>('idle');
const operationId = computed(() => pending.value?.operation_id ?? null);
let writeController: AbortController | null = null;
let verifyController: AbortController | null = null;
const groupNames: Record<string, string> = { suppliers: 'Suppliers', purchases: 'Purchasing', customers: 'Customers', finance: 'Finance', people: 'People', audit: 'Activity log', inventory: 'Inventory' };
const groups = computed(() => [...new Set((props.templates[role.value]?.permissions ?? []).map(p => groupNames[p.split('.')[1]] ?? p))]);
function headers(): Record<string, string> {
    return { 'Content-Type': 'application/json', Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '' };
}
async function submit(): Promise<void> {
    if (busy.value) return;
    if (uncertain.value) { message.value = 'Hasil perubahan izin sebelumnya belum pasti. Periksa izin di server sebelum operasi berikutnya.'; return; }
    busy.value = true; saved.value = false; message.value = ''; operationProgress.value = 'running';
    pending.value ??= { operation_id: crypto.randomUUID(), identity_id: identity.value.trim(), role: role.value, action: action.value };
    const controller = new AbortController(); writeController = controller;
    const timer = window.setTimeout(() => controller.abort(), 30000);
    try {
        if (!navigator.onLine) throw new Error('OFFLINE_UNCONFIRMED');
        const response = await fetch('/pos/business/access/roles', {
            method: 'POST', credentials: 'same-origin', redirect: 'error', signal: controller.signal,
            headers: headers(), body: JSON.stringify(pending.value),
        });
        if (controller.signal.aborted) throw new Error('TIMEOUT_UNCONFIRMED');
        if (response.status === 401 || response.status === 419) {
            operationProgress.value = 'session-expired';
            uncertain.value = true;
            window.location.assign('/business-login?reason=session-expired');
            return;
        }
        if (response.status === 202 || response.status >= 500) throw new Error('AMBIGUOUS');
        if (!response.ok) {
            uncertain.value = false; pending.value = null;
            operationProgress.value = 'failed';
            message.value = 'Access change denied. Check identity, administrator authority and security verification.';
            return;
        }
        const result = await response.json();
        if (controller.signal.aborted || result.status !== 'saved') throw new Error('UNCONFIRMED');
        pending.value = null; uncertain.value = false; saved.value = true; operationProgress.value = 'succeeded';
        message.value = 'Business access saved. Open the workspace to refresh available modules.';
    } catch {
        uncertain.value = true; operationProgress.value = 'unknown';
        message.value = 'Access mutation may have committed. Verify authoritative permissions before any new request; no automatic retry.';
    } finally {
        window.clearTimeout(timer);
        if (writeController === controller) writeController = null;
        busy.value = false;
    }
}
async function verify(): Promise<void> {
    if (!props.step_up_url || verifying.value) return;
    verifying.value = true; message.value = '';
    const controller = new AbortController(); verifyController = controller;
    const timer = window.setTimeout(() => controller.abort(), 30000);
    try {
        if (!navigator.onLine) throw new Error('OFFLINE');
        const response = await fetch(props.step_up_url, {
            method:'POST', credentials:'same-origin', redirect:'error', signal:controller.signal,
            headers:headers(), body:JSON.stringify({password:password.value,code:code.value}),
        });
        if (controller.signal.aborted) throw new Error('VERIFY_TIMEOUT');
        if (response.status===401 || response.status===419) {
            operationProgress.value = 'session-expired';
            window.location.assign('/business-login?reason=session-expired');
            return;
        }
        if (!response.ok) {
            message.value = response.status>=500
                ? 'Verification result not confirmed. Check the session before trying again.'
                : 'Security verification rejected. Check password and authenticator code.';
            return;
        }
        // The existing authority endpoint rotates session and CSRF credentials.
        window.location.replace('/pos/business/access');
    } catch { message.value = 'Security verification not confirmed. Check the session before retrying.'; }
    finally {
        window.clearTimeout(timer); if (verifyController===controller) verifyController=null;
        password.value=''; code.value=''; verifying.value=false;
    }
}
function beforeUnload(e: BeforeUnloadEvent): void { if (busy.value || uncertain.value) { e.preventDefault(); e.returnValue = ''; } }
window.addEventListener('beforeunload', beforeUnload);
onBeforeUnmount(() => { window.removeEventListener('beforeunload', beforeUnload); writeController?.abort(); verifyController?.abort(); });
</script>

<template>
    <main class="access-page"><OperationFeedback :status="operationProgress" :message="message" :operation-id="operationId" :success-label="locale==='id'?'Perubahan akses berhasil':'Access change confirmed'"/>
        <a href="/pos" class="back">← {{ t('Back to workspace') }}</a>
        <header><span class="eyebrow">{{ t('Administration') }}</span><h1>{{ t('Business access') }}</h1><p>{{ t('Give existing team members access to business modules for this outlet.') }}</p></header>
        <p class="scope">{{ t('Outlet') }} <strong>{{ outlet_id }}</strong></p>
        <div class="access-grid">
            <section class="card">
                <h2>{{ t('Team permissions') }}</h2>
                <form @submit.prevent="submit">
                    <fieldset :disabled="busy || uncertain || verifying">
                        <label>{{ t('Team member identity ID') }}<input v-model="identity" required maxlength="96" autocomplete="off" /></label>
                        <p class="hint">{{ t('The account must already belong to this organization and outlet.') }}</p>
                        <label>{{ t('Business role') }}<select v-model="role"><option v-for="(preset, key) in templates" :key="key" :value="key">{{ t(preset.title) }}</option></select></label>
                        <ul class="groups"><li v-for="group in groups" :key="group">{{ t(group) }}</li></ul>
                        <label>{{ t('Access change') }}<select v-model="action"><option value="assign">{{ t('Grant role') }}</option><option value="revoke">{{ t('Revoke role') }}</option></select></label>
                    </fieldset>
                    <button type="submit" :disabled="busy || verifying">{{ t(busy ? 'Saving…' : uncertain ? 'Retry same request' : 'Save access') }}</button>
                </form>
                <p class="hint">{{ t('Revoking this role preserves permissions granted by other roles.') }}</p>
            </section>
            <aside class="card" v-if="step_up_url">
                <h2>{{ t('Security verification') }}</h2>
                <p>{{ t('Verify your administrator session before changing team permissions.') }}</p>
                <form @submit.prevent="verify">
                    <fieldset :disabled="verifying || busy || uncertain">
                        <label>{{ t('Current password') }}<input v-model="password" type="password" required autocomplete="current-password" /></label>
                        <label>{{ t('Authenticator code') }}<input v-model="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required autocomplete="one-time-code" /></label>
                        <button type="submit">{{ t(verifying ? 'Verifying…' : 'Verify session') }}</button>
                    </fieldset>
                </form>
            </aside>
        </div>
        <div v-if="message" class="message" :class="{ success: saved }" role="status">{{ t(message) }} <a v-if="saved" :href="returnUrl">{{ t('Open workspace') }} →</a></div>
    </main>
</template>

<style scoped>
.access-page{max-width:1100px;margin:auto;padding:32px;color:#1B1B39}.back{font-size:14px;color:#3965FA;text-decoration:none}.eyebrow{display:block;margin-top:28px;text-transform:uppercase;letter-spacing:.16em;color:#3965FA;font-size:14px;font-weight:700}h1{font-size:clamp(28px,4vw,42px);line-height:1.2;margin:10px 0}header p,.card p{line-height:1.6;color:#1B1B39}.scope{background:#EDEDED;border-radius:12px;padding:12px 18px;overflow-wrap:anywhere}.scope strong{margin-left:10px}.access-grid{display:grid;grid-template-columns:1.4fr 1fr;gap:24px;margin-top:24px}.card{background:var(--oq-white);border:1px solid #EDEDED;border-radius:20px;padding:26px;box-shadow:0 6px 28px #1B1B3906;align-self:start}h2{font-size:20px;margin:0 0 20px}fieldset{border:0;padding:0;margin:0;min-width:0}label{display:block;font-size:14px;font-weight:600;margin:16px 0}input,select{display:block;width:100%;min-height:46px;border:1px solid #1B1B39;border-radius:9px;background:var(--oq-white);padding:10px 12px;margin-top:7px;box-sizing:border-box;color:#1B1B39;font:inherit}input:focus,select:focus{outline:3px solid #3965FA80}button{background:#1B1B39;color:var(--oq-white);border:0;border-radius:10px;padding:13px 20px;font-weight:700;cursor:pointer;min-height:46px}button:disabled{opacity:.55;cursor:wait}.hint{font-size:14px;margin:12px 0}.groups{list-style:none;display:flex;flex-wrap:wrap;gap:8px;padding:0}.groups li{background:#EDEDED;color:#1B1B39;padding:6px 10px;border-radius:8px;font-size:14px}.message{padding:18px;margin-top:24px;background:#EDEDED;border-radius:12px;line-height:1.6;overflow-wrap:anywhere}.message.success{background:#E9EBFF}.message a{display:inline-block;font-weight:700;color:#1B1B39;margin-left:8px}@media(max-width:760px){.access-page{padding:18px}.access-grid{grid-template-columns:1fr;gap:16px}.card{padding:20px}}

.access-page{color:var(--oq-dark);font-size:1rem}
.scope,.groups li,.message,.message.success{background:var(--oq-pale);color:var(--oq-navy);border:1px solid var(--oq-soft)}
.card{background:var(--oq-white);border-color:var(--oq-light);box-shadow:0 10px 24px #1B1B3914}
.card label,.hint,.groups li,.eyebrow{font-size:.875rem;color:var(--oq-navy)}
input,select{font-size:1rem;color:var(--oq-dark);background:var(--oq-white);border:1px solid var(--oq-soft)}
button{background:var(--oq-primary);color:var(--oq-white)}
button:focus-visible,input:focus-visible,select:focus-visible{outline:3px solid var(--oq-primary);outline-offset:3px}

</style>
