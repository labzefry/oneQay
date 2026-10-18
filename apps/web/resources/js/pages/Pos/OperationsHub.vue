<!-- Sprint204 durable staging readiness attestation compatibility anchor — Lab | zefry -->
<!-- Sprint203 bounded staging bridge compatibility anchor — Lab | zefry -->
<script setup lang="ts">
import { formatMoney as displayMoney } from "../../business-money";
import { t } from "../../i18n";
import { Head } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import WorkspaceIcon from '../../components/WorkspaceIcon.vue';
import SalesTrend from '../../components/SalesTrend.vue';


type SaleFact = {
    sale_id: string;
    completed_at_unix: number;
    total_atomic: number;
    currency: string;
    scale: number;
    state: 'COMPLETED' | 'VOIDED' | 'REFUNDED';
};
type Intelligence = {
    status: 'available' | 'restricted' | 'unavailable';
    counters: null | { completed_sales: number; voided_sales: number; cash_refunded_sales: number };
    gross_totals: Array<{ currency: string; scale: number; gross_atomic: number }>;
    recent_sales: SaleFact[];
};
type BusinessCapability = {
    key: string; title: string; description: string; status: 'available' | 'not_available'; url: string | null; group: string;
};

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

type MerchantOperationsReadiness = {
    state: 'guidance_unavailable' | 'setup_required' | 'shift_required' | 'cashier_ready' | 'review_available' | 'routes_only';
    recommended_key: string | null;
    catalog_item_count: number | null;
    sellable_item_count: number | null;
    shift_active: boolean | null;
    opening_cash_ready: boolean | null;
};

const props = defineProps<{
    scope: {
        tenant_id: string;
        organization_id: string;
        outlet_id: string;
        device_id: string;
    };
    destinations: Destination[];
    intelligence: Intelligence;
    capabilities: BusinessCapability[];
    readiness: MerchantOperationsReadiness;
    security: SecurityCapabilities;
    correlation_id: string;
}>();

const securityOpen = ref(false);
onMounted(() => { if (new URLSearchParams(window.location.search).get('panel') === 'security') securityOpen.value = true; });
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

const technicalContextOpen = ref(false);

const categoryMeta: Record<string, { label: string; description: string; order: number }> = {
    SETUP: { label: 'Get ready', description: 'Manage products and prepare stock for your next sale.', order: 10 },
    SHIFT: { label: 'Shift operations', description: 'Manage opening cash and keep each shift organized.', order: 20 },
    SELL: { label: 'Sell', description: 'Create an order and complete a sale.', order: 30 },
    STOCK: { label: 'Stock movement', description: 'Keep inventory up to date as stock arrives.', order: 40 },
    REVIEW: { label: 'Performance & history', description: 'See sales, shifts, products, and stock in one place.', order: 50 },
    CONTROL: { label: 'Corrections & controls', description: 'Review corrections and reconcile cash at shift close.', order: 60 },
};

const primaryDestination = computed<Destination | null>(() => {
    if (props.readiness.recommended_key === null) return null;

    return props.destinations.find(
        (candidate) => candidate.key === props.readiness.recommended_key,
    ) ?? null;
});

const readinessLabel = computed(() => {
    const labels: Record<MerchantOperationsReadiness['state'], string> = {
        guidance_unavailable: 'Guidance unavailable',
        setup_required: 'Setup required',
        shift_required: 'Shift preparation required',
        cashier_ready: 'Ready to sell',
        review_available: 'Review available',
        routes_only: 'Authorized routes available',
    };

    return labels[props.readiness.state];
});

const readinessMessage = computed(() => {
    const messages: Record<MerchantOperationsReadiness['state'], string> = {
        guidance_unavailable: 'We could not check your current setup. Open a workspace below to review it.',
        setup_required: 'Add your products and opening stock to prepare your first sale.',
        shift_required: 'Start your shift and record opening cash before using the cashier.',
        cashier_ready: 'Your catalog, stock, and shift are ready. Open the cashier to begin your next sale.',
        review_available: 'Explore your reports and review recent business activity.',
        routes_only: 'Choose an available workspace below to continue your work.',
    };

    return messages[props.readiness.state];
});

function readinessValue(value: number | boolean | null, booleanTrue: string, booleanFalse: string): string {
    if (value === null) return 'Not verified';
    if (typeof value === 'number') return String(value);
    return value ? booleanTrue : booleanFalse;
}

const groupedDestinations = computed(() => {
    const groups = new Map<string, Destination[]>();

    for (const destination of props.destinations) {
        const existing = groups.get(destination.category) ?? [];
        existing.push(destination);
        groups.set(destination.category, existing);
    }

    return Array.from(groups.entries())
        .map(([category, destinations]) => {
            const meta = categoryMeta[category] ?? {
                label: category,
                description: 'Authorized workspace group for the current context.',
                order: 999,
            };

            return { category, destinations, ...meta };
        })
        .sort((left, right) => left.order - right.order || left.label.localeCompare(right.label));
});

const laneCount = computed(() => new Set(props.destinations.map((destination) => destination.category)).size);

function compactRef(value: string): string {
    return value.length <= 14 ? value : `${value.slice(0, 7)}…${value.slice(-5)}`;
}

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


const commandFacts = computed(() => props.intelligence.status === 'available' ? props.intelligence.counters : null);
const recentBusinessActivity = computed(() => props.intelligence.status === 'available' ? props.intelligence.recent_sales.slice(0, 5) : []);
const businessGroups = computed(() => {
    const order = ['Operations', 'Management', 'Intelligence', 'Platform'];
    return order.map(group => ({ group, items: props.capabilities.filter(module => module.group === group) }));
});
const businessSignals = computed(() => {
    const signals: string[] = [];
    if (props.readiness.catalog_item_count === 0 || props.readiness.sellable_item_count === 0) signals.push('Complete your catalog and stock setup before selling.');
    if (props.readiness.shift_active === false || props.readiness.opening_cash_ready === false) signals.push('Verify the active shift and opening cash before starting a sale.');
    if (commandFacts.value && commandFacts.value.cash_refunded_sales > 0) signals.push('Cash refunds have been recorded. Review corrections and their audit trail.');
    if (commandFacts.value && commandFacts.value.voided_sales > 0) signals.push('Voided sales exist. Review the sale correction history.');
    if (props.intelligence.status === 'restricted') signals.push('Sales intelligence is only available to an authorized reporting role.');
    if (props.intelligence.status === 'unavailable') signals.push('Sales intelligence is temporarily unavailable. Figures have not been estimated.');
    if (signals.length === 0) signals.push('Your live operational context is available. Explore the authorized business workspaces.');
    return signals;
});
function commandMoney(atomic: number, currency: string, scale: number): string { return displayMoney(atomic, currency, scale); }
function commandDate(unix: number): string {
    return Number.isSafeInteger(unix) ? new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(unix * 1000)) : '—';
}

const workspaceDescriptions: Record<string, string> = {
    catalog_inventory: 'Add products and set opening inventory.',
    inventory_replenishment: 'Receive stock and update quantities.',
    inventory_accountability: 'Check stock movements and available inventory.',
    shift_start: 'Start a shift and record opening cash.',
    cashier: 'Add items, take payment, and issue a receipt.',
    sales_summary: 'Review sales totals and daily activity.',
    active_shift_performance: 'Track sales and returns in the current shift.',
    shift_history_performance: 'Review closed shifts and closing cash.',
    product_sales_performance: 'Explore products sold and returned.',
    sale_history: 'Find receipts and view sale details.',
    corrections: 'Review available sale corrections and refunds.',
    cash_variance_reconciliation: 'Review and explain closing-cash differences.',
    shift_close: 'Reconcile and complete your shift close.',
};
</script>


<!-- State-aware guidance is read-only. Suggested from verified POS state and the currently delivered route set. -->
<template>
    <Head :title="t('Overview')" />
    <main class="operations-shell">
        <header class="overview-header"><div><p class="eyebrow">{{ t("YOUR BUSINESS, AT A GLANCE") }}</p><h1>{{ t("Business overview") }}</h1><p class="subtitle">{{ t("Everything you need to prepare, sell, and keep your business moving.") }}</p></div><div class="overview-actions"><button class="security-trigger" type="button" @click="securityOpen = !securityOpen"><WorkspaceIcon name="shield-check" :size="17" />{{ t(securityOpen ? 'Close security' : 'Account & security') }}</button><button v-if="props.security.can_logout" class="logout-trigger" type="button" :disabled="busy" @click="logout"><WorkspaceIcon name="log-out" :size="17" />{{ t("Sign out") }}</button></div></header>
        <section class="metric-grid" :aria-label="t('Merchant operations summary')">
            <article class="metric-card"><div class="metric-top"><span>{{ t("Catalog items") }}</span><span class="metric-icon"><WorkspaceIcon name="package" /></span></div><strong>{{ t(readinessValue(props.readiness.catalog_item_count, '', '')) }}</strong><small>{{ t("Products in your catalog") }}</small></article>
            <article class="metric-card"><div class="metric-top"><span>{{ t("Sellable items") }}</span><span class="metric-icon blue"><WorkspaceIcon name="boxes" /></span></div><strong>{{ t(readinessValue(props.readiness.sellable_item_count, '', '')) }}</strong><small>{{ t("Available for your next sale") }}</small></article>
            <article class="metric-card"><div class="metric-top"><span>{{ t("Current shift") }}</span><span class="metric-icon amber"><WorkspaceIcon name="clock-3" /></span></div><strong class="status-value">{{ t(readinessValue(props.readiness.shift_active, 'Active', 'Not started')) }}</strong><small>{{ t("For your current device") }}</small></article>
            <article class="metric-card"><div class="metric-top"><span>{{ t("Opening cash") }}</span><span class="metric-icon violet"><WorkspaceIcon name="wallet" /></span></div><strong class="status-value">{{ t(readinessValue(props.readiness.opening_cash_ready, 'Recorded', 'Not ready')) }}</strong><small>{{ t("Cash readiness for this shift") }}</small></article>
        </section>

        <section class="os-command" aria-label="Business intelligence command center">
            <div class="os-section-heading">
                <div>
                    <p class="eyebrow">{{ t("INTELLIGENT BUSINESS MANAGEMENT") }}</p>
                    <h2>{{ t("Business Command Center") }}</h2>
                    <p>{{ t("Verified operational intelligence for the active outlet. No invented revenue, profit or AI predictions.") }}</p>
                </div>
                <span class="os-data-tag"><WorkspaceIcon name="shield-check" :size="15" />{{ t("Scoped business data") }}</span>
            </div>
            <div class="os-summary">
                <article class="os-stat">
                    <span>{{ t("Recorded transactions") }}</span>
                    <strong>{{ commandFacts?.completed_sales ?? '—' }}</strong>
                    <small>{{ t("All recorded completed sales, including later corrections") }}</small>
                </article>
                <article class="os-stat">
                    <span>{{ t("Voided sales") }}</span>
                    <strong>{{ commandFacts?.voided_sales ?? '—' }}</strong>
                    <small>{{ t("Recorded correction count") }}</small>
                </article>
                <article class="os-stat">
                    <span>{{ t("Cash refunds") }}</span>
                    <strong>{{ commandFacts?.cash_refunded_sales ?? '—' }}</strong>
                    <small>{{ t("Recorded refund count") }}</small>
                </article>
                <article class="os-stat">
                    <span>{{ t("Reporting access") }}</span>
                    <strong class="os-stat-state">{{ t(props.intelligence.status === 'available' ? 'Available' : props.intelligence.status === 'restricted' ? 'Restricted' : 'Unavailable') }}</strong>
                    <small>{{ t("Data shown only when reporting is authorized") }}</small>
                </article>
            </div>
            <div class="os-duo">
                <section class="os-pane">
                    <div class="os-pane-heading"><div><p class="eyebrow">{{ t("FINANCIAL VISIBILITY") }}</p><h3>{{ t("Recorded gross by currency") }}</h3></div><WorkspaceIcon name="chart-column" :size="20" /></div>
                    <p class="os-footnote">{{ t("Historical gross before full voids and cash refunds; not net revenue or profit.") }}</p>
                    <div v-if="props.intelligence.status === 'available' && props.intelligence.gross_totals.length" class="os-gross-list">
                        <div v-for="gross in props.intelligence.gross_totals" :key="gross.currency + ':' + gross.scale" class="os-gross-item">
                            <span>{{ gross.currency }}</span><strong>{{ commandMoney(gross.gross_atomic, gross.currency, gross.scale) }}</strong>
                        </div>
                    </div>
                    <p v-else class="os-empty">{{ t(props.intelligence.status === 'available' ? 'No completed sales recorded.' : 'No authorized financial data to display.') }}</p>
                    <a v-if="props.destinations.some(x => x.key === 'sales_summary')" class="os-more" :href="props.destinations.find(x => x.key === 'sales_summary')?.url">{{ t("Open sales reporting") }} <WorkspaceIcon name="arrow-up-right" :size="15" /></a>
                </section>
                <section class="os-pane">
                    <div class="os-pane-heading"><div><p class="eyebrow">{{ t("BUSINESS SIGNALS") }}</p><h3>{{ t("Operational attention") }}</h3></div><WorkspaceIcon name="activity" :size="20" /></div>
                    <p class="os-footnote">{{ t("Deterministic signals from existing records, not AI-generated advice.") }}</p>
                    <ul class="os-signals"><li v-for="signal in businessSignals" :key="signal"><WorkspaceIcon name="check-circle-2" :size="17" />{{ t(signal) }}</li></ul>
                </section>
            </div>
            <section class="os-pane os-activity" :aria-label="t('Recent business activity')">
                <div class="os-pane-heading"><div><p class="eyebrow">{{ t("TRANSACTION ACTIVITY") }}</p><h3>{{ t("Recent business activity") }}</h3></div><span class="os-data-tag">{{ t("Last five from the authorized recent-sales read model") }}</span></div>
                <div v-if="recentBusinessActivity.length" class="os-activity-list">
                    <div v-for="sale in recentBusinessActivity" :key="sale.sale_id" class="os-activity-row">
                        <div class="os-activity-icon"><WorkspaceIcon name="receipt-text" :size="19" /></div>
                        <div class="os-activity-copy"><strong>{{ sale.sale_id }}</strong><small>{{ commandDate(sale.completed_at_unix) }}</small></div>
                        <span class="os-status" :data-state="sale.state">{{ t(sale.state) }}</span>
                        <strong class="os-amount">{{ commandMoney(sale.total_atomic, sale.currency, sale.scale) }}</strong>
                    </div>
                </div>
                <p v-else class="os-empty">{{ t("There are no authorized recent sales to display.") }}</p>
            </section>
        </section>
        <SalesTrend v-if="props.intelligence.status==='available'"/>
        <section class="overview-guide" :aria-label="t('Operating readiness')" :data-state="props.readiness.state">
            <div class="guide-emblem"><WorkspaceIcon :name="props.readiness.state === 'cashier_ready' ? 'circle-check' : 'sparkles'" :size="32" /></div><div class="guide-copy"><p class="eyebrow">{{ t("YOUR NEXT STEP") }}</p><h2>{{ t(readinessLabel) }}</h2><p>{{ t(readinessMessage) }}</p></div><a v-if="primaryDestination" class="primary-action" :href="primaryDestination.url">{{ t(primaryDestination.title) }}<WorkspaceIcon name="arrow-right" :size="17" /></a><span v-else class="guide-state">{{ t("Review your workspaces") }}</span>
        </section>
        <section id="account-security" v-if="securityOpen" class="security-panel" :aria-label="t('Account and security')">
            <div class="security-heading">
                <div>
                    <p class="eyebrow">{{ t("Self-service security") }}</p>
                    <h2>{{ t("Account & security") }}</h2>
                    <p>{{ t("Manage your password, authenticator, and recovery codes.") }}</p>
                </div>
                <span class="secure-badge">{{ t("Session protected") }}</span>
            </div>

            <div class="security-grid">
                <form v-if="props.security.can_change_password" class="security-card" @submit.prevent="changePassword">
                    <span class="security-kicker">{{ t("Credential") }}</span>
                    <h3>{{ t("Change password") }}</h3>
                    <p>{{ t("A successful change invalidates the current session and requires a fresh sign-in.") }}</p>
                    <label>{{ t("Current password") }}<input v-model="changeCurrentPassword" type="password" autocomplete="current-password"></label>
                    <label>{{ t("New password") }}<input v-model="changeNewPassword" type="password" autocomplete="new-password"></label>
                    <label>{{ t("Confirm new password") }}<input v-model="changeNewPasswordConfirmation" type="password" autocomplete="new-password"></label>
                    <label>{{ t("MFA code when required") }}<input v-model="changeTotpCode" inputmode="numeric" maxlength="6" autocomplete="one-time-code"></label>
                    <button :disabled="busy || !passwordChangeReady">{{ t("Change password") }}</button>
                </form>

                <form v-if="props.security.can_rotate_password_recovery_codes" class="security-card" @submit.prevent="rotatePasswordRecoveryCodes">
                    <span class="security-kicker">{{ t("Recovery") }}</span>
                    <h3>{{ t("Password recovery codes") }}</h3>
                    <p>{{ t("Rotate the single-use codes used to recover a forgotten password. Existing codes are replaced.") }}</p>
                    <label>{{ t("Current password") }}<input v-model="passwordRecoveryPassword" type="password" autocomplete="current-password"></label>
                    <button :disabled="busy || passwordRecoveryPassword.length === 0">{{ t("Generate new codes") }}</button>
                    <div v-if="passwordRecoveryCodes.length" class="codes">
                        <code v-for="item in passwordRecoveryCodes" :key="item">{{ item }}</code>
                        <button class="secondary" type="button" @click="copyCodes(passwordRecoveryCodes)">{{ t("Copy codes") }}</button>
                    </div>
                </form>

                <form v-if="props.security.can_rotate_totp_recovery_codes" class="security-card" @submit.prevent="rotateTotpRecoveryCodes">
                    <span class="security-kicker">{{ t("MFA recovery") }}</span>
                    <h3>{{ t("Authenticator recovery codes") }}</h3>
                    <p>{{ t("Generate replacement single-use codes for secure authenticator-factor recovery.") }}</p>
                    <label>{{ t("Current password") }}<input v-model="totpRecoveryPassword" type="password" autocomplete="current-password"></label>
                    <label>{{ t("Current 6-digit MFA code") }}<input v-model="totpRecoveryTotpCode" inputmode="numeric" maxlength="6" autocomplete="one-time-code"></label>
                    <button :disabled="busy || totpRecoveryPassword.length === 0 || !/^\d{6}$/.test(totpRecoveryTotpCode)">{{ t("Generate authenticator codes") }}</button>
                    <div v-if="totpRecoveryCodes.length" class="codes">
                        <code v-for="item in totpRecoveryCodes" :key="item">{{ item }}</code>
                        <button class="secondary" type="button" @click="copyCodes(totpRecoveryCodes)">{{ t("Copy codes") }}</button>
                    </div>
                </form>
            </div>

            <p v-if="notice" class="notice" role="status">{{ t(notice) }}</p>
            <p v-if="error" class="error" role="alert">{{ t(error) }}</p>
        </section>


        <section class="workspace-panel"><div class="panel-heading"><div><p class="eyebrow">{{ t("WORK THE WAY YOUR BUSINESS DOES") }}</p><h2>{{ t("Your workspaces") }}</h2><p>{{ t("Pick a task and get straight to work.") }}</p></div><span class="count">{{ props.destinations.length }} {{ t("available ·") }} {{ laneCount }} {{ t("groups") }}</span></div><div class="workspace-groups"><section v-for="group in groupedDestinations" :key="group.category" class="workspace-group"><div class="group-heading"><div><h3>{{ t(group.label) }}</h3><p>{{ t(group.description) }}</p></div><span class="group-count">{{ group.destinations.length }}</span></div><div class="workspace-grid"><a v-for="destination in group.destinations" :key="destination.key" class="workspace-card" :href="destination.url"><span class="workspace-symbol"><WorkspaceIcon :name="({ SETUP: 'package', SHIFT: 'clock-3', SELL: 'shopping-cart', STOCK: 'truck', REVIEW: 'chart-column', CONTROL: 'shield-check' } as Record<string,string>)[destination.category] ?? 'clipboard-list'" :size="23" /></span><div class="workspace-copy"><h3>{{ t(destination.title) }}</h3><p>{{ t(workspaceDescriptions[destination.key] ?? destination.description) }}</p></div><WorkspaceIcon class="workspace-arrow" name="arrow-up-right" :size="18" /></a></div></section></div></section>
        <details class="workspace-details"><summary>{{ t("Session & support details") }}</summary><p v-if="!primaryDestination">{{ t("No guarded next action is being recommended.") }} <span v-if="primaryDestination">{{ t("The current next step is supplied by verified operating readiness.") }}</span></p><button class="context-toggle" type="button" @click="technicalContextOpen = !technicalContextOpen">{{ t(technicalContextOpen ? 'Hide technical context' : 'Technical context') }}</button>
        <section v-if="technicalContextOpen" class="context-strip" :aria-label="t('Current POS technical context')">
            <div><span>{{ t("Tenant") }}</span><strong>{{ props.scope.tenant_id }}</strong></div>
            <div><span>{{ t("Organization") }}</span><strong>{{ props.scope.organization_id }}</strong></div>
            <div><span>{{ t("Outlet") }}</span><strong>{{ props.scope.outlet_id }}</strong></div>
            <div><span>{{ t("Device") }}</span><strong>{{ props.scope.device_id }}</strong></div>
        </section> {{ t("Correlation:") }} <span class="mono">{{ props.correlation_id }}</span>
        </details>
    </main>
</template>

<style scoped>

.operations-shell{padding:30px 32px;color:var(--oq-ink);font-family:inherit}.overview-header,.metric-grid,.overview-guide,.workspace-panel,.workspace-details,.security-panel{max-width:1280px;margin-left:auto;margin-right:auto}.overview-header{display:flex;align-items:center;justify-content:space-between;gap:22px;margin-bottom:24px}.overview-header .eyebrow{font-size:9px;letter-spacing:1.6px;color:var(--oq-brand);font-weight:750;margin:0}.overview-header h1{margin:8px 0 6px}.subtitle{font-size:12px;color:var(--oq-muted);margin:0}.overview-actions{display:flex;gap:8px;flex-shrink:0}.overview-actions button{display:flex;gap:7px;align-items:center;padding:10px 12px;border:1px solid var(--oq-line);border-radius:9px;background:#fff;color:var(--oq-muted);font-size:11px;font-weight:600;cursor:pointer}.overview-actions .logout-trigger{border:0;background:transparent}.metric-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px;margin-bottom:23px}.metric-card{background:var(--oq-panel);border:1px solid var(--oq-line);border-radius:var(--oq-radius);padding:19px 20px;box-shadow:var(--oq-shadow);min-width:0}.metric-top{display:flex;align-items:center;justify-content:space-between;gap:8px;color:var(--oq-muted);font-size:11px;font-weight:550}.metric-icon{width:36px;height:36px;display:grid;place-items:center;border-radius:11px;background:#e9f7f2;color:#08816f}.metric-icon.blue{background:#ebf3fc;color:#5381b6}.metric-icon.amber{background:#fdf4e6;color:#b5893e}.metric-icon.violet{background:#f2effa;color:#8c71b8}.metric-card>strong{font-size:29px;font-weight:700;letter-spacing:-1px;display:block;margin:6px 0 2px;line-height:1.35}.metric-card>.status-value{font-size:19px;letter-spacing:-.5px;min-height:39px;display:flex;align-items:center}.metric-card>small{display:block;font-size:10px;color:#82939b}.overview-guide{display:flex;align-items:center;gap:19px;background:linear-gradient(105deg,#e4f3ed,#f4fbf7);border:1px solid #cee5d9;border-radius:var(--oq-radius);padding:25px;margin-bottom:30px}.guide-emblem{height:60px;width:60px;flex-shrink:0;border-radius:16px;display:grid;place-items:center;background:#d3e9df;color:#377e68}.guide-copy{flex:1;min-width:0}.guide-copy .eyebrow{font-size:9px;margin:0 0 5px;letter-spacing:1.4px;color:#57927c;font-weight:750}.guide-copy h2{margin:0 0 6px;font-size:18px;letter-spacing:-.35px}.guide-copy>p:last-child{margin:0;color:#638072;font-size:12px;line-height:1.6}.primary-action{background:var(--oq-brand);color:#fff;display:flex;align-items:center;justify-content:center;gap:13px;padding:12px 16px;border-radius:9px;font-size:11px;font-weight:650;text-decoration:none;box-shadow:0 3px 7px #07685613;flex-shrink:0}.primary-action:hover{background:var(--oq-brand-dark)}.guide-state{font-size:11px;color:#4d7865}.workspace-panel{padding:0;background:none;border:0;box-shadow:none}.panel-heading{display:flex;justify-content:space-between;align-items:center;gap:15px;margin-bottom:25px}.panel-heading .eyebrow{font-size:9px;letter-spacing:1.4px;font-weight:650;color:#88999e;margin:0 0 4px}.panel-heading h2{margin:0;font-size:19px;letter-spacing:-.3px}.panel-heading p:last-child{margin:6px 0 0;color:var(--oq-muted);font-size:11px}.count{font-size:10px;color:var(--oq-muted);white-space:nowrap;padding:7px 10px;background:#edf2f4;border-radius:8px}.workspace-groups{display:grid;gap:23px}.workspace-group{display:grid;gap:10px}.group-heading{display:flex;align-items:center;gap:10px;justify-content:space-between}.group-heading h3{margin:0;font-size:12px;color:#506874;font-weight:650}.group-heading p{margin:3px 0 0;font-size:10px;color:#8b9ca4}.group-count{font-size:10px;color:#748993;padding:3px 8px;background:#e9eef0;border-radius:6px}.workspace-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:13px}.workspace-card{display:flex;align-items:flex-start;gap:12px;min-height:127px;border-radius:13px;background:#fff;border:1px solid var(--oq-line);padding:19px 16px;text-decoration:none;box-shadow:var(--oq-shadow);transition:border-color .15s,transform .15s}.workspace-card:hover{border-color:#91c3b5;transform:translateY(-2px)}.workspace-symbol{width:38px;height:38px;border-radius:11px;background:#f0f5f4;display:grid;place-items:center;color:#43786d;flex-shrink:0}.workspace-copy{min-width:0;flex:1}.workspace-copy h3{font-size:13px;line-height:1.4;font-weight:650;margin:2px 0 7px;letter-spacing:-.15px}.workspace-copy p{font-size:11px;line-height:1.6;color:#6e838e;margin:0}.workspace-arrow{color:#a0b1b8;margin-top:4px}.workspace-details{padding-top:26px;color:#7f929a;font-size:10px}.workspace-details summary{cursor:pointer;padding:8px 0}.workspace-details p{font-size:11px}.context-strip{padding:15px;display:grid;grid-template-columns:repeat(2,1fr);gap:12px}.context-strip span,.context-strip strong{display:block;overflow-wrap:anywhere}.context-strip span{font-size:10px}.context-strip strong{font-size:11px;color:var(--oq-ink)}.security-panel{margin-bottom:26px}.security-card button{cursor:pointer}
@media(max-width:1250px){.workspace-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.metric-grid{gap:11px}.metric-card{padding:15px}.metric-top{font-size:10px}}@media(max-width:1000px){.overview-header{align-items:flex-start}.overview-actions{flex-direction:column}.overview-guide{flex-wrap:wrap}.primary-action{margin-left:79px}.metric-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.security-grid{grid-template-columns:1fr}.security-heading{flex-wrap:wrap}}@media(max-width:600px){.operations-shell{padding:23px 16px}.overview-header{flex-direction:column;gap:14px}.overview-actions{flex-direction:row}.metric-grid{gap:10px}.metric-card{padding:15px 13px}.metric-top{font-size:10px}.metric-icon{width:30px;height:30px}.metric-card>small{font-size:9px}.overview-guide{padding:18px;gap:12px}.guide-emblem{height:43px;width:43px;border-radius:12px}.guide-copy{flex-basis:calc(100% - 56px)}.primary-action{margin-left:0;width:100%}.panel-heading{align-items:flex-start}.count{font-size:9px}.workspace-grid{grid-template-columns:1fr}.workspace-card{min-height:106px;padding:17px}.context-strip{grid-template-columns:1fr}.workspace-copy h3{font-size:13px}.workspace-copy p{font-size:11px}}

.security-panel{background:#fff;border:1px solid var(--oq-line);border-radius:16px;padding:24px;min-width:0}.security-heading{display:flex;justify-content:space-between;gap:16px;align-items:flex-start}.security-heading h2{margin:0 0 8px}.security-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px;margin:22px 0}.security-card{min-width:0;border:1px solid var(--oq-line);border-radius:12px;padding:20px;background:#fafcfb}.security-card h3{margin:0 0 10px}.security-card p,.security-panel p{font-size:12px;line-height:1.7;color:var(--oq-muted)}.security-card label{display:flex;flex-direction:column;gap:8px;margin:14px 0;font-size:12px;font-weight:600}.security-card input{width:100%;box-sizing:border-box;padding:12px;border:1px solid #cadbd5;border-radius:8px;background:white;font:inherit}.security-panel button{border:1px solid #bad8ce;background:#edf7f2;color:#175e4d;border-radius:8px;padding:10px 14px;font:600 12px system-ui}.security-panel pre,.security-panel code{white-space:pre-wrap;overflow-wrap:anywhere}.security-panel .table-wrap{overflow:auto}.security-panel table{width:100%;text-align:left;border-collapse:collapse}.security-panel th,.security-panel td{padding:12px;font-size:12px;border-bottom:1px solid var(--oq-line)}@media(max-width:900px){.security-grid{grid-template-columns:1fr}}@media(max-width:600px){.security-panel{padding:17px}.security-card{padding:16px}}
</style>


<style scoped>
.os-command,.os-suite{max-width:1320px;margin:20px auto 28px;padding:0 28px}
.os-section-heading{display:flex;justify-content:space-between;align-items:flex-start;gap:18px;margin-bottom:20px}
.os-section-heading h2{font-size:clamp(1.4rem,2.5vw,2.05rem);letter-spacing:-.045em;margin:4px 0 6px;color:#15313a}
.os-section-heading p:not(.eyebrow){font-size:.9rem;max-width:730px;color:#637987;margin:0}
.os-data-tag{display:inline-flex;align-items:center;gap:6px;background:#eaf6f2;border:1px solid #cce7db;border-radius:100px;padding:8px 11px;color:#136e5c;font-size:.7rem;font-weight:700;white-space:normal}
.os-summary{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:17px}
.os-stat,.os-pane,.os-module{border:1px solid #e0e8eb;border-radius:17px;background:#fff;box-shadow:0 5px 22px #162b3509}
.os-stat{padding:22px;min-height:144px}.os-stat span{display:block;color:#617885;font-size:.78rem;font-weight:650}.os-stat strong{display:block;margin:10px 0;font-size:2rem;line-height:1.1;letter-spacing:-.04em;color:#16313d}.os-stat .os-stat-state{font-size:1.18rem;letter-spacing:0;margin:14px 0}.os-stat small{font-size:.7rem;color:#8093a0;line-height:1.5}
.os-duo{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px}.os-pane{padding:24px;min-width:0}.os-pane-heading{display:flex;align-items:center;justify-content:space-between;gap:12px;color:#138274}.os-pane-heading h3{font-size:1.1rem;color:#15313a;letter-spacing:-.025em;margin:4px 0 0}.os-pane .eyebrow{font-size:.65rem;color:#6a8590}.os-footnote{margin:10px 0 19px;font-size:.76rem;line-height:1.55;color:#6a7e89}.os-gross-list{display:grid;gap:10px}.os-gross-item{display:flex;justify-content:space-between;align-items:center;gap:10px;padding:14px;background:#f5faf9;border:1px solid #e1ede9;border-radius:10px}.os-gross-item span{font-size:.73rem;font-weight:700;color:#547870}.os-gross-item strong{font-size:1.12rem;color:#14584f}.os-more{display:inline-flex;align-items:center;gap:6px;margin-top:20px;font-size:.78rem;font-weight:750;color:#087f6f}.os-empty{margin:18px 0;color:#8697a1;font-size:.85rem}.os-signals{list-style:none;padding:0;display:grid;gap:12px;margin:20px 0 0}.os-signals li{display:flex;align-items:flex-start;gap:11px;background:#f5f9fa;border-radius:10px;padding:12px;font-size:.8rem;color:#516a77;line-height:1.6}.os-signals svg{color:#138674;flex-shrink:0;margin-top:2px}
.os-activity{padding:22px 24px}.os-activity-list{margin-top:18px}.os-activity-row{display:flex;gap:13px;align-items:center;padding:13px 0;border-bottom:1px solid #edf2f4}.os-activity-row:last-child{border-bottom:0}.os-activity-icon{display:grid;place-items:center;width:36px;height:36px;border-radius:9px;background:#ebf7f1;color:#117d6c}.os-activity-copy{display:flex;flex-direction:column;min-width:0;flex:1;gap:3px}.os-activity-copy strong{font-size:.74rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.os-activity-copy small{font-size:.7rem;color:#8596a0}.os-status{font-size:.67rem;font-weight:750;border-radius:999px;background:#eef1f3;padding:6px 9px}.os-status[data-state="COMPLETED"]{background:#e6f5eb;color:#19734e}.os-status[data-state="VOIDED"]{background:#fff1dc;color:#96600c}.os-status[data-state="REFUNDED"]{background:#fbe9e9;color:#9f4141}.os-amount{min-width:130px;text-align:right;font-size:.85rem}
.os-suite{margin-top:35px}.os-suite-group{margin-top:24px}.os-suite-group>h3{font-size:.76rem;color:#58727b;text-transform:uppercase;letter-spacing:.11em;margin:0 0 13px}.os-module-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:13px}.os-module{display:flex;align-items:center;gap:13px;min-height:102px;padding:17px;text-decoration:none;transition:transform .15s,border-color .15s}.os-module[href]:hover{border-color:#52b69e;transform:translateY(-2px)}.os-module-symbol{display:grid;place-items:center;width:43px;height:43px;flex:0 0 43px;background:#eaf7f2;color:#07866f;border-radius:12px}.os-module-info{flex:1;min-width:0}.os-module-info strong{display:block;font-size:.88rem;color:#213a46}.os-module-info small{display:block;font-size:.72rem;color:#7e909a;line-height:1.4;margin-top:4px}.os-module-state{font-size:.65rem;color:#7b8991;background:#f0f3f4;border-radius:20px;padding:7px 9px;white-space:nowrap}.os-module-state[data-enabled=true]{background:#e7f5ed;color:#147651}.os-module-disabled{opacity:.72}
@media(max-width:1100px){.os-summary{grid-template-columns:repeat(2,minmax(0,1fr))}.os-module-grid{grid-template-columns:1fr}}
@media(max-width:700px){.os-command,.os-suite{padding:0 14px}.os-section-heading{flex-direction:column}.os-duo{grid-template-columns:1fr}.os-summary{gap:10px}.os-stat{padding:16px;min-height:120px}.os-stat strong{font-size:1.5rem}.os-module{flex-wrap:wrap}.os-module-state{margin-left:56px}.os-activity-row{flex-wrap:wrap}.os-amount{margin-left:auto}.os-activity .os-data-tag{display:none}}
@media(max-width:410px){.os-summary{grid-template-columns:1fr}.os-pane{padding:17px}}
.security-panel{background:#fff;border:1px solid var(--oq-line);border-radius:16px;padding:24px;min-width:0}.security-heading{display:flex;justify-content:space-between;gap:16px;align-items:flex-start}.security-heading h2{margin:0 0 8px}.security-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px;margin:22px 0}.security-card{min-width:0;border:1px solid var(--oq-line);border-radius:12px;padding:20px;background:#fafcfb}.security-card h3{margin:0 0 10px}.security-card p,.security-panel p{font-size:12px;line-height:1.7;color:var(--oq-muted)}.security-card label{display:flex;flex-direction:column;gap:8px;margin:14px 0;font-size:12px;font-weight:600}.security-card input{width:100%;box-sizing:border-box;padding:12px;border:1px solid #cadbd5;border-radius:8px;background:white;font:inherit}.security-panel button{border:1px solid #bad8ce;background:#edf7f2;color:#175e4d;border-radius:8px;padding:10px 14px;font:600 12px system-ui}.security-panel pre,.security-panel code{white-space:pre-wrap;overflow-wrap:anywhere}.security-panel .table-wrap{overflow:auto}.security-panel table{width:100%;text-align:left;border-collapse:collapse}.security-panel th,.security-panel td{padding:12px;font-size:12px;border-bottom:1px solid var(--oq-line)}@media(max-width:900px){.security-grid{grid-template-columns:1fr}}@media(max-width:600px){.security-panel{padding:17px}.security-card{padding:16px}}
</style>
