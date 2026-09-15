<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

type PerformanceRow = {
    product_id: string;
    display_name: string;
    currency: string;
    scale: number;
    gross_quantity: string;
    voided_quantity: string;
    net_quantity: string;
    gross_atomic: string;
    voided_atomic: string;
    net_atomic: string;
};

const props = defineProps<{
    scope: { tenant_id: string; organization_id: string; outlet_id: string };
    rows: PerformanceRow[];
    truncated: boolean;
    correlation_id: string;
}>();

const search = ref('');
const filteredRows = computed(() => {
    const needle = search.value.trim().toLocaleLowerCase('id-ID');
    return props.rows.filter((row) => !needle
        || row.product_id.toLocaleLowerCase('id-ID').includes(needle)
        || row.display_name.toLocaleLowerCase('id-ID').includes(needle)
        || row.currency.toLocaleLowerCase('id-ID').includes(needle));
});

const quantity = (value: string): string => new Intl.NumberFormat('id-ID').format(BigInt(value));
const atomic = (value: string, currency: string, scale: number): string => {
    const padded = value.padStart(scale + 1, '0');
    const whole = scale === 0 ? padded : padded.slice(0, -scale);
    const fraction = scale === 0 ? '' : padded.slice(-scale);
    const grouped = new Intl.NumberFormat('id-ID').format(BigInt(whole));
    return `${currency} ${grouped}${fraction ? `,${fraction}` : ''}`;
};
</script>

<template>
    <Head title="Product Sales Performance" />
    <main class="page-shell">
        <header class="hero">
            <div>
                <p class="eyebrow">oneQay · Operational Intelligence</p>
                <h1>Product Sales Performance</h1>
                <p class="subtitle">Read-only product/currency performance from immutable completed-sale lines. Full-sale void is removed once; a later cash refund does not reduce product performance a second time.</p>
            </div>
            <div class="scope-card"><span>Outlet</span><strong>{{ props.scope.outlet_id }}</strong><small>{{ props.scope.organization_id }}</small></div>
        </header>
        <div v-if="props.truncated" class="notice">Hasil dibatasi pada 250 product/currency buckets teratas berdasarkan gross sold quantity.</div>
        <section class="panel">
            <div class="panel-heading">
                <div><p class="eyebrow">Immutable performance</p><h2>Product / currency buckets</h2></div>
                <input v-model="search" placeholder="Cari product ID / nama / currency" />
            </div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Produk</th><th>Currency</th><th class="numeric">Gross units</th><th class="numeric">Voided units</th><th class="numeric">Net active units</th><th class="numeric">Gross recorded</th><th class="numeric">Voided value</th><th class="numeric">Net active value</th></tr></thead>
                    <tbody>
                        <tr v-for="row in filteredRows" :key="`${row.product_id}-${row.currency}-${row.scale}`">
                            <td><strong>{{ row.display_name }}</strong><span class="mono block">{{ row.product_id }}</span></td>
                            <td><span class="currency">{{ row.currency }}</span><small>scale {{ row.scale }}</small></td>
                            <td class="numeric">{{ quantity(row.gross_quantity) }}</td><td class="numeric voided">{{ quantity(row.voided_quantity) }}</td><td class="numeric strong">{{ quantity(row.net_quantity) }}</td>
                            <td class="numeric">{{ atomic(row.gross_atomic, row.currency, row.scale) }}</td><td class="numeric voided">{{ atomic(row.voided_atomic, row.currency, row.scale) }}</td><td class="numeric strong">{{ atomic(row.net_atomic, row.currency, row.scale) }}</td>
                        </tr>
                        <tr v-if="!filteredRows.length"><td colspan="8" class="empty">Tidak ada product sales performance untuk filter ini.</td></tr>
                    </tbody>
                </table>
            </div>
            <p class="helper">Nama produk adalah current catalog label untuk product ID yang sama. Inactive catalog products tetap dipertahankan bila memiliki immutable sales history. Currency/scale historis tidak dicampur menjadi satu bucket.</p>
        </section>
        <footer>Correlation: <span class="mono">{{ props.correlation_id }}</span></footer>
    </main>
</template>

<style scoped>
.page-shell { min-height: 100vh; padding: 32px; background: #f5f7fa; color: #172033; font-family: Inter, ui-sans-serif, system-ui, sans-serif; }
.hero { max-width: 1400px; margin: 0 auto 24px; display: flex; justify-content: space-between; align-items: flex-start; gap: 24px; }
.hero h1 { margin: 4px 0 8px; font-size: clamp(30px, 4vw, 44px); letter-spacing: -.04em; }
.eyebrow { margin: 0; color: #64748b; font-size: 12px; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; }
.subtitle,.helper { color: #64748b; line-height: 1.6; max-width: 850px; }
.scope-card,.panel { background: #fff; border: 1px solid #e2e8f0; border-radius: 18px; box-shadow: 0 14px 35px rgba(15,23,42,.06); }
.scope-card { min-width: 220px; padding: 18px; display: grid; gap: 4px; }.scope-card span,.scope-card small { color: #64748b; }
.panel { max-width: 1352px; margin: 0 auto; padding: 22px; }.panel-heading { display: flex; justify-content: space-between; align-items: center; gap: 16px; margin-bottom: 16px; }.panel-heading h2 { margin: 3px 0 0; font-size: 20px; }
input { min-width: 320px; border: 1px solid #cbd5e1; border-radius: 10px; padding: 11px 12px; color: #172033; background: #fff; }
.notice { max-width: 1352px; box-sizing: border-box; margin: 0 auto 16px; border: 1px solid #fed7aa; background: #fff7ed; color: #9a3412; border-radius: 12px; padding: 12px 14px; }
.table-wrap { overflow-x: auto; } table { width: 100%; border-collapse: collapse; min-width: 1180px; } th,td { padding: 13px 10px; border-bottom: 1px solid #e2e8f0; text-align: left; vertical-align: middle; } th { color: #64748b; font-size: 11px; text-transform: uppercase; letter-spacing: .06em; }
.numeric { text-align: right; font-variant-numeric: tabular-nums; white-space: nowrap; }.strong { font-weight: 800; }.voided { color: #9a3412; }.block { display: block; margin-top: 4px; }.mono { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; color: #64748b; font-size: 12px; }.currency { display: block; font-weight: 800; }.currency + small { color: #94a3b8; }.empty { text-align: center; color: #94a3b8; padding: 28px; }
footer { max-width: 1400px; margin: 18px auto 0; color: #64748b; font-size: 12px; }
@media (max-width: 900px) { .page-shell { padding: 18px; } .hero { flex-direction: column; } .scope-card { width: 100%; box-sizing: border-box; } .panel-heading { align-items: stretch; flex-direction: column; } input { min-width: 0; width: 100%; box-sizing: border-box; } }
</style>
