<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

type State = 'COMPLETED' | 'VOIDED' | 'REFUNDED';
type SaleSummary = {
    sale_id: string;
    completed_at_unix: number;
    total_atomic: string;
    currency: string;
    scale: number;
    tender_category: string;
    state: State;
    shift_id: string | null;
    device_id: string;
    voided_at_unix: number | null;
    refunded_at_unix: number | null;
};
type ReceiptLine = {
    line_no: number;
    product_id: string;
    quantity: number;
    unit_price_atomic: string;
    line_total_atomic: string;
    currency: string;
    scale: number;
};
type Receipt = SaleSummary & {
    evidence_mode: string;
    applied_atomic: string;
    change_atomic: string;
    void_id: string | null;
    refund_id: string | null;
    lines: ReceiptLine[];
};

const props = defineProps<{
    scope: { tenant_id: string; organization_id: string; outlet_id: string };
    recent_sales: SaleSummary[];
    selected_sale_id: string | null;
    selected_found: boolean;
    selected_receipt: Receipt | null;
    history_base_url: string;
    correlation_id: string;
}>();

const filter = ref<'ALL' | State>('ALL');
const lookup = ref(props.selected_sale_id ?? '');
const salePattern = /^sale-[a-f0-9]{24}$/;

const filteredSales = computed(() => filter.value === 'ALL'
    ? props.recent_sales
    : props.recent_sales.filter((sale) => sale.state === filter.value));

const groupDigits = (value: string): string => value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
const money = (atomic: string, currency: string, scale: number): string => {
    const normalized = atomic.replace(/^0+(?=\d)/, '') || '0';
    if (scale === 0) return `${currency} ${groupDigits(normalized)}`;
    const padded = normalized.padStart(scale + 1, '0');
    const major = padded.slice(0, -scale);
    const minor = padded.slice(-scale);
    return `${currency} ${groupDigits(major)},${minor}`;
};
const timestamp = (unix: number | null): string => unix === null
    ? '—'
    : new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium', timeStyle: 'medium' }).format(new Date(unix * 1000));

const detailUrl = (saleId: string): string => `${props.history_base_url}/${encodeURIComponent(saleId)}`;
const openSale = (saleId: string): void => window.location.assign(detailUrl(saleId));
const submitLookup = (): void => {
    const candidate = lookup.value.trim();
    if (salePattern.test(candidate)) openSale(candidate);
};
const clearSelection = (): void => window.location.assign(props.history_base_url);
</script>

<template>
    <Head title="Sales History" />
    <main class="history-shell">
        <header class="hero">
            <div>
                <p class="eyebrow">oneQay · Operational Intelligence</p>
                <h1>Sales History</h1>
                <p class="subtitle">Immutable transaction history and receipt drill-down for the authorized outlet.</p>
            </div>
            <div class="scope-card"><span>Outlet</span><strong>{{ props.scope.outlet_id }}</strong></div>
        </header>

        <section class="toolbar panel" aria-label="Sale lookup">
            <div>
                <p class="eyebrow">Exact receipt lookup</p>
                <h2>Find a canonical sale</h2>
            </div>
            <form class="lookup" @submit.prevent="submitLookup">
                <input v-model="lookup" maxlength="29" autocomplete="off" spellcheck="false" placeholder="sale-xxxxxxxxxxxxxxxxxxxxxxxx" aria-label="Canonical sale ID" />
                <button type="submit" :disabled="!salePattern.test(lookup.trim())">Open receipt</button>
            </form>
        </section>

        <section v-if="props.selected_sale_id" class="panel detail-panel">
            <div class="panel-heading">
                <div><p class="eyebrow">Immutable receipt</p><h2>{{ props.selected_sale_id }}</h2></div>
                <button class="secondary" type="button" @click="clearSelection">Close detail</button>
            </div>

            <div v-if="!props.selected_found" class="notice">
                No sale is available for this identifier in the currently authorized tenant, organization, and outlet scope.
            </div>

            <template v-else-if="props.selected_receipt">
                <div class="receipt-grid">
                    <article><span>Status</span><strong><span class="status" :data-state="props.selected_receipt.state">{{ props.selected_receipt.state }}</span></strong></article>
                    <article><span>Completed</span><strong>{{ timestamp(props.selected_receipt.completed_at_unix) }}</strong></article>
                    <article><span>Tender</span><strong>{{ props.selected_receipt.tender_category }}</strong></article>
                    <article><span>Total</span><strong>{{ money(props.selected_receipt.total_atomic, props.selected_receipt.currency, props.selected_receipt.scale) }}</strong></article>
                    <article><span>Applied</span><strong>{{ money(props.selected_receipt.applied_atomic, props.selected_receipt.currency, props.selected_receipt.scale) }}</strong></article>
                    <article><span>Change</span><strong>{{ money(props.selected_receipt.change_atomic, props.selected_receipt.currency, props.selected_receipt.scale) }}</strong></article>
                    <article><span>Original device</span><strong class="mono">{{ props.selected_receipt.device_id }}</strong></article>
                    <article><span>Shift</span><strong class="mono">{{ props.selected_receipt.shift_id ?? 'Historical / unbound' }}</strong></article>
                </div>

                <div class="evidence-strip">
                    <div><span>Sale evidence</span><strong>{{ props.selected_receipt.evidence_mode }}</strong></div>
                    <div><span>Void evidence</span><strong class="mono">{{ props.selected_receipt.void_id ?? 'None' }}</strong><small>{{ timestamp(props.selected_receipt.voided_at_unix) }}</small></div>
                    <div><span>Cash refund evidence</span><strong class="mono">{{ props.selected_receipt.refund_id ?? 'None' }}</strong><small>{{ timestamp(props.selected_receipt.refunded_at_unix) }}</small></div>
                </div>

                <div class="table-wrap receipt-lines">
                    <table>
                        <thead><tr><th>#</th><th>Product ID</th><th class="numeric">Qty</th><th class="numeric">Unit price</th><th class="numeric">Line total</th></tr></thead>
                        <tbody>
                            <tr v-for="line in props.selected_receipt.lines" :key="line.line_no">
                                <td>{{ line.line_no }}</td>
                                <td class="mono">{{ line.product_id }}</td>
                                <td class="numeric">{{ line.quantity }}</td>
                                <td class="numeric">{{ money(line.unit_price_atomic, line.currency, line.scale) }}</td>
                                <td class="numeric strong">{{ money(line.line_total_atomic, line.currency, line.scale) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </template>
        </section>

        <section class="panel">
            <div class="panel-heading">
                <div><p class="eyebrow">Latest immutable transactions</p><h2>Recent sales</h2></div>
                <div class="filters" aria-label="Status filter">
                    <button v-for="state in ['ALL', 'COMPLETED', 'VOIDED', 'REFUNDED'] as const" :key="state" type="button" :class="{ active: filter === state }" @click="filter = state">{{ state }}</button>
                </div>
            </div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Sale</th><th>Completed</th><th>Tender</th><th>Status</th><th>Shift</th><th class="numeric">Total</th><th></th></tr></thead>
                    <tbody>
                        <tr v-for="sale in filteredSales" :key="sale.sale_id">
                            <td class="mono">{{ sale.sale_id }}</td>
                            <td>{{ timestamp(sale.completed_at_unix) }}</td>
                            <td>{{ sale.tender_category }}</td>
                            <td><span class="status" :data-state="sale.state">{{ sale.state }}</span></td>
                            <td class="mono">{{ sale.shift_id ?? 'Historical / unbound' }}</td>
                            <td class="numeric strong">{{ money(sale.total_atomic, sale.currency, sale.scale) }}</td>
                            <td class="numeric"><button class="link-button" type="button" @click="openSale(sale.sale_id)">Detail</button></td>
                        </tr>
                        <tr v-if="!filteredSales.length"><td colspan="7" class="empty">No sale matches the current status filter.</td></tr>
                    </tbody>
                </table>
            </div>
        </section>

        <footer>
            <span>Read only · max 50 latest sales</span>
            <span>Correlation: <span class="mono">{{ props.correlation_id }}</span></span>
        </footer>
    </main>
</template>

<style scoped>
.history-shell { min-height:100vh; background:#f5f7fa; color:#172033; padding:32px; font-family:Inter,ui-sans-serif,system-ui,sans-serif; }
.hero { display:flex; justify-content:space-between; gap:24px; align-items:flex-start; margin:0 auto 24px; max-width:1440px; }
h1 { margin:4px 0 8px; font-size:34px; letter-spacing:-.03em; } h2 { margin:3px 0 0; font-size:20px; }
.eyebrow { margin:0; text-transform:uppercase; letter-spacing:.12em; font-size:11px; font-weight:800; color:#667085; }
.subtitle { margin:0; color:#667085; max-width:720px; }
.scope-card,.panel { background:#fff; border:1px solid #e4e7ec; border-radius:16px; box-shadow:0 8px 30px rgba(16,24,40,.04); }
.scope-card { padding:14px 18px; min-width:230px; display:grid; gap:4px; } .scope-card span,article span,.evidence-strip span { color:#667085; font-size:12px; }
.panel { max-width:1440px; margin:0 auto 20px; padding:20px; }
.toolbar,.panel-heading { display:flex; align-items:center; justify-content:space-between; gap:20px; }
.lookup { display:flex; gap:10px; min-width:min(100%,520px); } input { flex:1; min-width:260px; border:1px solid #d0d5dd; border-radius:10px; padding:11px 12px; font:inherit; }
button { border:0; border-radius:10px; padding:10px 14px; font-weight:750; cursor:pointer; background:#172033; color:#fff; } button:disabled { opacity:.45; cursor:not-allowed; }
.secondary { background:#eef2f6; color:#344054; } .link-button { background:transparent; color:#175cd3; padding:6px 8px; }
.filters { display:flex; flex-wrap:wrap; gap:6px; } .filters button { background:#f2f4f7; color:#475467; padding:7px 10px; font-size:11px; } .filters button.active { background:#172033; color:#fff; }
.detail-panel { border-top:4px solid #175cd3; }.notice { margin-top:18px; padding:18px; border-radius:12px; background:#fff7ed; color:#9a3412; }
.receipt-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:12px; margin-top:18px; }.receipt-grid article { border:1px solid #eaecf0; border-radius:12px; padding:14px; display:grid; gap:5px; }
.evidence-strip { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px; margin:14px 0; }.evidence-strip div { background:#f8fafc; border-radius:12px; padding:14px; display:grid; gap:4px; }.evidence-strip small { color:#667085; }
.table-wrap { overflow:auto; margin-top:16px; } table { width:100%; border-collapse:collapse; min-width:900px; } th,td { padding:12px 10px; border-bottom:1px solid #eaecf0; text-align:left; white-space:nowrap; } th { color:#667085; font-size:11px; text-transform:uppercase; letter-spacing:.06em; } .numeric { text-align:right; }.strong { font-weight:750; }.mono { font-family:ui-monospace,SFMono-Regular,Menlo,monospace; font-size:12px; }
.status { display:inline-flex; border-radius:999px; padding:4px 8px; font-size:11px; font-weight:800; background:#ecfdf3; color:#027a48; }.status[data-state="VOIDED"] { background:#fff7ed; color:#b54708; }.status[data-state="REFUNDED"] { background:#f2f4f7; color:#344054; }
.empty { text-align:center; color:#667085; padding:28px; } footer { max-width:1440px; margin:10px auto 0; display:flex; justify-content:space-between; gap:16px; color:#667085; font-size:12px; }
@media (max-width:900px) { .history-shell{padding:18px}.hero,.toolbar,.panel-heading{flex-direction:column;align-items:stretch}.scope-card{min-width:0}.lookup{min-width:0}.receipt-grid,.evidence-strip{grid-template-columns:1fr 1fr} }
@media (max-width:560px) { .receipt-grid,.evidence-strip{grid-template-columns:1fr}.lookup{flex-direction:column}input{min-width:0}footer{flex-direction:column} }
</style>
