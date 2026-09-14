<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

type GrossTotal = { currency: string; scale: number; gross_atomic: number };
type RecentSale = {
    sale_id: string;
    completed_at_unix: number;
    total_atomic: number;
    currency: string;
    scale: number;
    tender_category: string;
    state: 'COMPLETED' | 'VOIDED' | 'REFUNDED';
};

const props = defineProps<{
    scope: { tenant_id: string; organization_id: string; outlet_id: string };
    counters: { completed_sales: number; voided_sales: number; cash_refunded_sales: number };
    gross_totals: GrossTotal[];
    recent_sales: RecentSale[];
    correlation_id: string;
}>();

const money = (atomic: number, currency: string, scale: number): string => {
    const divisor = 10 ** scale;
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency,
        minimumFractionDigits: scale,
        maximumFractionDigits: scale,
    }).format(atomic / divisor);
};

const timestamp = (unix: number): string => new Intl.DateTimeFormat('id-ID', {
    dateStyle: 'medium',
    timeStyle: 'short',
}).format(new Date(unix * 1000));
</script>

<template>
    <Head title="Operational Sales Summary" />
    <main class="report-shell">
        <header class="hero">
            <div>
                <p class="eyebrow">oneQay · Operational Intelligence</p>
                <h1>Sales Summary</h1>
                <p class="subtitle">Read-only operational view for the currently authorized tenant and outlet.</p>
            </div>
            <div class="scope-card">
                <span>Outlet</span>
                <strong>{{ props.scope.outlet_id }}</strong>
            </div>
        </header>

        <section class="metrics" aria-label="Sales counters">
            <article><span>Completed sales</span><strong>{{ props.counters.completed_sales }}</strong></article>
            <article><span>Voided sales</span><strong>{{ props.counters.voided_sales }}</strong></article>
            <article><span>Cash refunded</span><strong>{{ props.counters.cash_refunded_sales }}</strong></article>
        </section>

        <section class="panel">
            <div class="panel-heading">
                <div><p class="eyebrow">Gross recorded value</p><h2>By currency</h2></div>
            </div>
            <div v-if="props.gross_totals.length" class="gross-grid">
                <div v-for="total in props.gross_totals" :key="`${total.currency}-${total.scale}`" class="gross-item">
                    <span>{{ total.currency }}</span>
                    <strong>{{ money(total.gross_atomic, total.currency, total.scale) }}</strong>
                </div>
            </div>
            <p v-else class="empty">No completed sale has been recorded for this outlet.</p>
        </section>

        <section class="panel">
            <div class="panel-heading">
                <div><p class="eyebrow">Latest activity</p><h2>Recent sales</h2></div>
                <span class="read-only">Read only</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Sale</th><th>Completed</th><th>Tender</th><th>Status</th><th class="numeric">Amount</th></tr></thead>
                    <tbody>
                        <tr v-for="sale in props.recent_sales" :key="sale.sale_id">
                            <td class="mono">{{ sale.sale_id }}</td>
                            <td>{{ timestamp(sale.completed_at_unix) }}</td>
                            <td>{{ sale.tender_category }}</td>
                            <td><span class="status" :data-state="sale.state">{{ sale.state }}</span></td>
                            <td class="numeric strong">{{ money(sale.total_atomic, sale.currency, sale.scale) }}</td>
                        </tr>
                        <tr v-if="!props.recent_sales.length"><td colspan="5" class="empty">No sales available.</td></tr>
                    </tbody>
                </table>
            </div>
        </section>

        <footer>Correlation: <span class="mono">{{ props.correlation_id }}</span></footer>
    </main>
</template>

<style scoped>
.report-shell { min-height: 100vh; background: #f5f7fa; color: #172033; padding: 32px; font-family: Inter, ui-sans-serif, system-ui, sans-serif; }
.hero { display:flex; justify-content:space-between; gap:24px; align-items:flex-end; max-width:1200px; margin:0 auto 24px; }
h1 { margin:4px 0 8px; font-size:clamp(2rem,4vw,3rem); letter-spacing:-.04em; }
.subtitle { margin:0; color:#667085; max-width:680px; }
.eyebrow { margin:0; color:#526175; font-size:.75rem; font-weight:800; text-transform:uppercase; letter-spacing:.12em; }
.scope-card,.metrics article,.panel { background:#fff; border:1px solid #e4e8ef; border-radius:16px; box-shadow:0 8px 24px rgba(16,24,40,.04); }
.scope-card { padding:16px 20px; min-width:220px; }
.scope-card span,.metrics span { display:block; color:#667085; font-size:.8rem; margin-bottom:6px; }
.metrics { max-width:1200px; margin:0 auto 24px; display:grid; grid-template-columns:repeat(3,1fr); gap:16px; }
.metrics article { padding:22px; }.metrics strong { font-size:2rem; }
.panel { max-width:1152px; margin:0 auto 20px; padding:24px; }
.panel-heading { display:flex; justify-content:space-between; align-items:center; gap:16px; margin-bottom:18px; }
h2 { margin:3px 0 0; font-size:1.15rem; }.read-only { background:#eef2f6; border-radius:999px; padding:7px 11px; font-size:.75rem; font-weight:700; }
.gross-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:12px; }.gross-item { background:#f8fafc; border-radius:12px; padding:16px; }.gross-item span { display:block; color:#667085; font-size:.8rem; }.gross-item strong { display:block; margin-top:4px; font-size:1.25rem; }
.table-wrap { overflow:auto; } table { width:100%; border-collapse:collapse; min-width:760px; } th,td { text-align:left; padding:13px 10px; border-bottom:1px solid #edf0f4; font-size:.86rem; } th { color:#667085; font-size:.72rem; text-transform:uppercase; letter-spacing:.06em; }.numeric{text-align:right}.strong{font-weight:700}.mono{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:.78rem}.status{display:inline-block;padding:5px 8px;border-radius:999px;background:#eef2f6;font-size:.7rem;font-weight:800}.status[data-state="COMPLETED"]{background:#e8f7ef}.status[data-state="VOIDED"]{background:#fff2e5}.status[data-state="REFUNDED"]{background:#fdecec}.empty{color:#667085;text-align:center;padding:24px} footer{max-width:1200px;margin:10px auto;color:#98a2b3;font-size:.72rem}
@media (max-width:760px){.report-shell{padding:20px 14px}.hero{align-items:stretch;flex-direction:column}.metrics{grid-template-columns:1fr}.scope-card{min-width:0}}
</style>