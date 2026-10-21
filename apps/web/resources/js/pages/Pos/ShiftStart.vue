<script setup lang="ts">
import NumericInput from "../../components/NumericInput.vue";
import { currencyPreferences, formatMoney as displayMoney } from "../../business-money";
import { t, locale } from "../../i18n";
import { Head } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref } from 'vue';
import OperationFeedback from '../../components/OperationFeedback.vue';
import type { MutationStatus } from '../../use-business-mutation';

type OpeningCashEvidence = {
    evidence_id: string;
    amount: { atomic_units: string; currency: string; scale: number };
    evidence_mode: string;
    recorded_at_unix: number;
};

type ActiveShift = {
    shift_id: string;
    opened_at_unix: number;
    opening_cash_evidence: OpeningCashEvidence | null;
};

type ShiftOpenResult = {
    status: 'opened';
    shift_id: string;
    operation_id: string;
    opened_at_unix: number;
};

type OpeningCashResult = {
    status: 'recorded';
    evidence_id: string;
    shift_id: string;
    operation_id: string;
    opening_cash: { atomic: number; currency: string; scale: number };
    evidence_mode: string;
    recorded_at_unix: number;
};

const props = defineProps<{
    scope: { tenant_id: string; organization_id: string; outlet_id: string; device_id: string };
    active_shift: ActiveShift | null;
    ready_for_cashier: boolean;
    open_shift_endpoint: string;
    opening_cash_endpoint: string;
    cashier_url: string | null;
    csrf_token: string;
    correlation_id: string;
}>();

const activeShift = ref<ActiveShift | null>(props.active_shift ? {
    ...props.active_shift,
    opening_cash_evidence: props.active_shift.opening_cash_evidence
        ? { ...props.active_shift.opening_cash_evidence, amount: { ...props.active_shift.opening_cash_evidence.amount } }
        : null,
} : null);
const shiftOperationId = ref(`shift-open:${crypto.randomUUID()}`);
const cashOperationId = ref(`shift-opening-cash:${crypto.randomUUID()}`);
const currency = ref(currencyPreferences.value.currency);
const currencyScale = ref(currencyPreferences.value.scale);
const openingCashInput = ref('0');
const openingShift = ref(false);
const recordingCash = ref(false);
const shiftUnknown = ref(false); const cashUnknown = ref(false);
const operationProgress = ref<MutationStatus>('idle');
const operationId = ref<string | null>(null);
const activeRequests = new Set<AbortController>();
onBeforeUnmount(() => { for (const request of activeRequests) request.abort(); activeRequests.clear(); });
const message = ref('');
const messageKind = ref<'neutral' | 'success' | 'warning' | 'error'>('neutral');

const readyForCashier = computed(() => activeShift.value?.opening_cash_evidence !== null && activeShift.value !== null);
const step = computed(() => {
    if (!activeShift.value) return 1;
    if (!activeShift.value.opening_cash_evidence) return 2;
    return 3;
});

const formatTimestamp = (unix: number): string => new Intl.DateTimeFormat('id-ID', {
    dateStyle: 'medium',
    timeStyle: 'medium',
}).format(new Date(unix * 1000));

const formatAtomic = (atomic: string, code: string, scale: number) => displayMoney(atomic, code, scale);

const parseOpeningCashAtomic = (): number | null => {
    const scale = currencyScale.value;
    if (!Number.isInteger(scale) || scale < 0 || scale > 6) return null;

    const normalized = openingCashInput.value.trim().replace(',', '.');
    const pattern = scale === 0 ? /^\d+$/ : new RegExp(`^\\d+(?:\\.\\d{0,${scale}})?$`);
    if (!pattern.test(normalized)) return null;

    const [whole, rawFraction = ''] = normalized.split('.');
    const fraction = rawFraction.padEnd(scale, '0');
    const atomic = BigInt(whole) * (10n ** BigInt(scale)) + BigInt(fraction || '0');
    if (atomic > BigInt(Number.MAX_SAFE_INTEGER)) return null;

    return Number(atomic);
};

const requestJson = async (endpoint: string, body: Record<string, unknown>): Promise<{ response: Response; payload: unknown }> => {
    const controller = new AbortController(); activeRequests.add(controller);
    const timer = window.setTimeout(() => controller.abort(), 30000);
    try {
        if (!navigator.onLine) throw new Error('OFFLINE_UNCONFIRMED');
        const response = await fetch(endpoint, {
            method: 'POST', credentials: 'same-origin', signal: controller.signal, redirect: 'error',
            headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ _token: props.csrf_token, ...body }),
        });
        if (controller.signal.aborted) throw new Error('TIMEOUT_UNCONFIRMED');
        if (response.status === 401 || response.status === 419) {
            operationProgress.value = 'session-expired';
            window.location.assign('/business-login?reason=session-expired');
            throw new Error('SESSION_EXPIRED');
        }
        if (response.status === 202 || response.status >= 500) {
            operationProgress.value = response.status === 202 ? 'queued' : 'unknown';
            throw new Error('WRITE_UNCONFIRMED');
        }
        const payload: unknown = await response.json();
        if (controller.signal.aborted) throw new Error('TIMEOUT_UNCONFIRMED');
        return { response, payload };
    } finally { window.clearTimeout(timer); activeRequests.delete(controller); }
};

const openShift = async (): Promise<void> => {
    if (activeShift.value || openingShift.value || shiftUnknown.value) return;

    openingShift.value = true;
    operationId.value = shiftOperationId.value;
    operationProgress.value = 'running';
    message.value = '';
    messageKind.value = 'neutral';

    try {
        const { response, payload: raw } = await requestJson(props.open_shift_endpoint, { operation_id: shiftOperationId.value });
        const payload = raw as ShiftOpenResult | { error?: { code?: string } };
        if (!response.ok || !('status' in payload) || payload.status !== 'opened') {
            const code = 'error' in payload ? payload.error?.code : undefined;
            message.value = code ? `Pembukaan shift ditolak (${code}).` : 'Pembukaan shift ditolak oleh server.';
            messageKind.value = 'error';
            operationProgress.value = 'failed';
            return;
        }

        activeShift.value = {
            shift_id: payload.shift_id,
            opened_at_unix: payload.opened_at_unix,
            opening_cash_evidence: null,
        };
        message.value = 'Shift aktif berhasil dibuka. Lanjutkan pencatatan kas awal; jangan membuka shift kedua.';
        messageKind.value = 'success';
        operationProgress.value = 'succeeded';
    } catch {
        shiftUnknown.value = true;
        if (operationProgress.value !== 'queued' && operationProgress.value !== 'session-expired') operationProgress.value = 'unknown';
        message.value = 'Status pembukaan shift belum dapat dipastikan. Tidak ada retry otomatis. Refresh halaman untuk membaca status authoritative sebelum mencoba lagi.';
        messageKind.value = 'warning';
    } finally {
        openingShift.value = false;
    }
};

const recordOpeningCash = async (): Promise<void> => {
    if (!activeShift.value || activeShift.value.opening_cash_evidence || recordingCash.value || cashUnknown.value) return;

    const canonicalCurrency = currency.value.trim().toUpperCase();
    if (!/^[A-Z]{3}$/.test(canonicalCurrency)) {
        message.value = 'Currency harus menggunakan kode tiga huruf.';
        messageKind.value = 'error';
        return;
    }

    const atomic = parseOpeningCashAtomic();
    if (atomic === null) {
        message.value = 'Nominal kas awal tidak valid untuk currency scale yang dipilih atau melampaui batas aman client.';
        messageKind.value = 'error';
        return;
    }

    recordingCash.value = true;
    operationId.value = cashOperationId.value;
    operationProgress.value = 'running';
    message.value = '';
    messageKind.value = 'neutral';

    try {
        const { response, payload: raw } = await requestJson(props.opening_cash_endpoint, {
            operation_id: cashOperationId.value,
            opening_cash_atomic: atomic,
            currency: canonicalCurrency,
            currency_scale: currencyScale.value,
        });
        const payload = raw as OpeningCashResult | { error?: { code?: string } };
        if (!response.ok || !('status' in payload) || payload.status !== 'recorded') {
            const code = 'error' in payload ? payload.error?.code : undefined;
            message.value = code
                ? `Kas awal belum tercatat (${code}). Shift tetap aktif; jangan membuka shift baru.`
                : 'Kas awal belum tercatat. Shift tetap aktif; jangan membuka shift baru.';
            messageKind.value = 'error';
            operationProgress.value = 'failed';
            return;
        }

        activeShift.value = {
            ...activeShift.value,
            opening_cash_evidence: {
                evidence_id: payload.evidence_id,
                amount: {
                    atomic_units: String(payload.opening_cash.atomic),
                    currency: payload.opening_cash.currency,
                    scale: payload.opening_cash.scale,
                },
                evidence_mode: payload.evidence_mode,
                recorded_at_unix: payload.recorded_at_unix,
            },
        };
        message.value = 'Kas awal tercatat. Shift siap digunakan untuk transaksi kasir.';
        messageKind.value = 'success';
        operationProgress.value = 'succeeded';
    } catch {
        cashUnknown.value = true;
        if (operationProgress.value !== 'queued' && operationProgress.value !== 'session-expired') operationProgress.value = 'unknown';
        message.value = 'Status pencatatan kas awal belum dapat dipastikan. Tidak ada retry otomatis. Refresh halaman untuk membaca evidence authoritative sebelum mencoba lagi.';
        messageKind.value = 'warning';
    } finally {
        recordingCash.value = false;
    }
};
</script>

<template>
    <Head :title="t('Shift Start Workspace')" />
    <main class="shell"><OperationFeedback :status="operationProgress" :message="message" :operation-id="operationId" :success-label="locale==='id'?'Operasi shift terkonfirmasi':'Shift operation confirmed'" :running-label="locale==='id'?'Memproses operasi shift':'Processing shift operation'"/>
        <header class="hero">
            <div>
                <p class="eyebrow">{{ t("oneQay · Point of Sale") }}</p>
                <h1>{{ t("Shift Start Workspace") }}</h1>
                <p class="subtitle">{{ t("Pembukaan register yang resumable, exact-device scoped, dan menggunakan mutation contract canonical.") }}</p>
            </div>
            <div class="status-card" :data-ready="readyForCashier">
                <span>{{ t("Operational readiness") }}</span>
                <strong>{{ t(readyForCashier ? 'READY FOR CASHIER' : activeShift ? 'OPENING CASH REQUIRED' : 'SHIFT REQUIRED') }}</strong>
                <small>{{ t("Step") }} {{ t(step) }} / 3</small>
            </div>
        </header>

        <section class="scope-grid">
            <div><span>{{ t("Tenant") }}</span><strong>{{ props.scope.tenant_id }}</strong></div>
            <div><span>{{ t("Organization") }}</span><strong>{{ props.scope.organization_id }}</strong></div>
            <div><span>{{ t("Outlet") }}</span><strong>{{ props.scope.outlet_id }}</strong></div>
            <div><span>{{ t("Device") }}</span><strong>{{ props.scope.device_id }}</strong></div>
        </section>

        <section class="workflow">
            <article class="panel" :data-active="step === 1" :data-complete="Boolean(activeShift)">
                <div class="panel-head"><span class="step">01</span><div><p class="eyebrow">{{ t("Register lifecycle") }}</p><h2>{{ t("Buka shift") }}</h2></div></div>
                <template v-if="!activeShift">
                    <p>{{ t("Server akan mengikat shift aktif ke tenant, organization, outlet, device, dan actor yang sedang terverifikasi.") }}</p>
                    <p class="operation">{{ t("Operation ID") }} <span>{{ shiftOperationId }}</span></p>
                    <button type="button" :disabled="openingShift" @click="openShift">{{ t(openingShift ? 'Membuka shift…' : 'Buka shift sekarang') }}</button>
                    <small class="guard">{{ t("Tidak ada retry otomatis. Jika koneksi terputus setelah submit, refresh halaman sebelum mengulang.") }}</small>
                </template>
                <template v-else>
                    <div class="success-box"><strong>{{ t("Shift aktif") }}</strong><span class="mono">{{ activeShift.shift_id }}</span><small>{{ t("Dibuka") }} {{ formatTimestamp(activeShift.opened_at_unix) }}</small></div>
                </template>
            </article>

            <article class="panel" :data-active="step === 2" :data-complete="Boolean(activeShift?.opening_cash_evidence)">
                <div class="panel-head"><span class="step">02</span><div><p class="eyebrow">{{ t("Cash control") }}</p><h2>{{ t("Catat kas awal") }}</h2></div></div>
                <p v-if="!activeShift" class="muted">{{ t("Tahap ini tersedia setelah shift aktif pada device ini.") }}</p>
                <template v-else-if="!activeShift.opening_cash_evidence">
                    <div class="money-grid">
                        <label>{{ t("Currency") }}<input v-model="currency" maxlength="3" autocomplete="off" @input="currency = currency.toUpperCase()"></label>
                        <label>{{ t("Scale") }}<input v-model.number="currencyScale" type="number" min="0" max="6" step="1"></label>
                        <label class="amount">{{ t("Kas awal") }}<NumericInput :decimals="currencyScale" v-model="openingCashInput" inputmode="decimal" autocomplete="off" placeholder="0"/></label>
                    </div>
                    <p class="operation">{{ t("Operation ID") }} <span>{{ cashOperationId }}</span></p>
                    <button type="button" :disabled="recordingCash" @click="recordOpeningCash">{{ t(recordingCash ? 'Mencatat kas awal…' : 'Catat kas awal') }}</button>
                    <small class="guard">{{ t("Jika pencatatan gagal, shift tetap aktif. Jangan membuka shift baru; selesaikan evidence kas awal pada shift yang sama.") }}</small>
                </template>
                <template v-else>
                    <div class="success-box"><strong>{{ t("Opening cash recorded") }}</strong><span>{{ formatAtomic(activeShift.opening_cash_evidence.amount.atomic_units, activeShift.opening_cash_evidence.amount.currency, activeShift.opening_cash_evidence.amount.scale) }}</span><small>{{ t(activeShift.opening_cash_evidence.evidence_mode) }} · {{ formatTimestamp(activeShift.opening_cash_evidence.recorded_at_unix) }}</small></div>
                </template>
            </article>

            <article class="panel" :data-active="step === 3" :data-complete="readyForCashier">
                <div class="panel-head"><span class="step">03</span><div><p class="eyebrow">{{ t("Cashier readiness") }}</p><h2>{{ t("Mulai transaksi") }}</h2></div></div>
                <p v-if="!readyForCashier" class="muted">{{ t("Cashier baru dinyatakan siap setelah active shift dan opening-cash evidence authoritative tersedia.") }}</p>
                <template v-else>
                    <p>{{ t("Register siap. Harga, stok, authorization, idempotency, dan receipt tetap ditentukan server pada sale contract canonical.") }}</p>
                    <a v-if="props.cashier_url" class="primary-link" :href="props.cashier_url">{{ t("Buka Cashier Workspace") }}</a>
                    <p v-else class="muted">{{ t("Cashier Workspace belum diaktifkan pada runtime ini.") }}</p>
                </template>
            </article>
        </section>

        <section v-if="message" class="message" :data-kind="messageKind" aria-live="polite">{{ t(message) }}</section>

        <section class="policy-note">
            <strong>{{ t("Fail-closed operating rule") }}</strong>
            <p>{{ t("Page ini tidak menggabungkan dua mutation menjadi transaksi baru dan tidak melakukan hidden retry. Setiap tahap memakai endpoint, permission, idempotency, dan persistence authority yang sudah canonical.") }}</p>
        </section>

        <footer>{{ t("Page correlation:") }} <span class="mono">{{ props.correlation_id }}</span></footer>
    </main>
</template>

<style scoped>
.shell{min-height:100vh;background:#FFFFFF;color:#1B1B39;padding:28px;font-family:Inter,ui-sans-serif,system-ui,sans-serif}.hero{max-width:1240px;margin:0 auto 20px;display:flex;justify-content:space-between;align-items:flex-end;gap:24px}.eyebrow{margin:0;color:#3965FA;font-size:.72rem;font-weight:800;letter-spacing:.12em;text-transform:uppercase}h1{margin:4px 0 7px;font-size:clamp(2rem,4vw,3rem);letter-spacing:-.04em}h2{margin:3px 0 0;font-size:1.18rem}.subtitle,.muted,.guard{color:#3965FA}.status-card,.scope-grid,.panel,.message,.policy-note{background:#FFFFFF;border:1px solid #E9EBFF;border-radius:16px;box-shadow:0 8px 24px #1B1B390A}.status-card{min-width:240px;padding:14px 18px;border-left:4px solid #3965FA}.status-card[data-ready="true"]{border-left-color:#3965FA}.status-card span,.status-card small{display:block;color:#3965FA;font-size:.75rem}.status-card strong{display:block;margin:4px 0}.scope-grid{max-width:1240px;margin:0 auto 18px;padding:14px 18px;display:grid;grid-template-columns:repeat(4,1fr);gap:16px}.scope-grid span{display:block;color:#3965FA;font-size:.72rem}.scope-grid strong{font-size:.88rem;word-break:break-all}.workflow{max-width:1240px;margin:0 auto;display:grid;grid-template-columns:repeat(3,1fr);gap:16px}.panel{padding:20px;border-top:3px solid transparent}.panel[data-active="true"]{border-top-color:#3965FA}.panel[data-complete="true"]{border-top-color:#3965FA}.panel-head{display:flex;align-items:center;gap:12px;margin-bottom:14px}.step{display:grid;place-items:center;width:38px;height:38px;border-radius:12px;background:#E9EBFF;color:#3965FA;font-weight:800}.operation{font-size:.74rem;color:#3965FA}.operation span,.mono{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;word-break:break-all}.money-grid{display:grid;grid-template-columns:1fr 90px;gap:10px}.money-grid .amount{grid-column:1/-1}label{font-size:.78rem;font-weight:700;color:#3965FA}input{display:block;width:100%;box-sizing:border-box;margin-top:6px;border:1px solid #EDEDED;border-radius:10px;padding:10px 12px;font:inherit;color:#1B1B39}button,.primary-link{display:inline-flex;justify-content:center;align-items:center;border:0;border-radius:10px;padding:11px 15px;background:#3965FA;color:#FFFFFF;font:inherit;font-weight:800;text-decoration:none;cursor:pointer}button:disabled{opacity:.55;cursor:not-allowed}.guard{display:block;margin-top:10px;font-size:.72rem;line-height:1.45}.success-box{padding:14px;border-radius:12px;background:#E9EBFF;border:1px solid #99B7FC}.success-box strong,.success-box span,.success-box small{display:block}.success-box span{margin:5px 0;font-weight:800}.success-box small{color:#3965FA}.message,.policy-note{max-width:1204px;margin:16px auto 0;padding:16px 18px}.message[data-kind="success"]{border-left:4px solid #3965FA}.message[data-kind="warning"]{border-left:4px solid #3965FA}.message[data-kind="error"]{border-left:4px solid #3965FA}.policy-note p{margin:5px 0 0;color:#3965FA}footer{max-width:1240px;margin:18px auto 0;color:#3965FA;font-size:.72rem}@media(max-width:900px){.hero{align-items:stretch;flex-direction:column}.status-card{min-width:0}.scope-grid{grid-template-columns:repeat(2,1fr)}.workflow{grid-template-columns:1fr}}@media(max-width:520px){.shell{padding:18px}.scope-grid{grid-template-columns:1fr}}

/* Minimal-depth, legible opening shift surfaces. */
.shell{background:var(--oq-white);color:var(--oq-dark);font-size:1rem}
h1,h2,.scope-grid strong{color:var(--oq-navy)}
.status-card,.scope-grid,.panel,.message,.policy-note{background:var(--oq-white);border-color:var(--oq-light);box-shadow:0 8px 24px #1B1B3914}
.status-card,.status-card[data-ready="true"],.panel[data-active="true"],.panel[data-complete="true"]{border-left-color:var(--oq-primary);border-top-color:var(--oq-primary)}
.eyebrow,.status-card span,.status-card small,.scope-grid span,.operation,.muted,.subtitle{font-size:.875rem;color:var(--oq-navy)}
input,select{font-size:1rem;min-height:44px;color:var(--oq-dark);border-color:var(--oq-soft)}
button:focus-visible,input:focus-visible,select:focus-visible{outline:3px solid var(--oq-primary);outline-offset:3px}

</style>
