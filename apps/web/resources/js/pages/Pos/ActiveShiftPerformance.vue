<script setup lang="ts">
import { computed } from 'vue';

type Scope = {
    tenant_id: string;
    organization_id: string;
    outlet_id: string;
    device_id: string;
};

type ActiveShift = {
    shift_id: string;
    opener_actor_identity_id: string;
    opened_at_unix: number;
};

type Bucket = {
    tender_category: 'CASH' | 'MANUAL_EXTERNAL';
    currency: string;
    scale: number;
    completed_sales: string;
    voided_sales: string;
    active_sales: string;
    refunded_sales: string;
    gross_atomic: string;
    voided_atomic: string;
    active_net_atomic: string;
    refunded_cash_atomic: string;
};

const props = defineProps<{
    scope: Scope;
    active_shift: ActiveShift | null;
    buckets: Bucket[];
    correlation_id: string;
}>();

const totalCompleted = computed(() => props.buckets.reduce((sum, row) => sum + BigInt(row.completed_sales), 0n).toString());
const totalVoided = computed(() => props.buckets.reduce((sum, row) => sum + BigInt(row.voided_sales), 0n).toString());
const totalActive = computed(() => props.buckets.reduce((sum, row) => sum + BigInt(row.active_sales), 0n).toString());
const totalRefunded = computed(() => props.buckets.reduce((sum, row) => sum + BigInt(row.refunded_sales), 0n).toString());

const money = (atomic: string, currency: string, scale: number): string => {
    const padded = atomic.padStart(scale + 1, '0');
    if (scale === 0) return `${currency} ${padded}`;
    const whole = padded.slice(0, -scale) || '0';
    const fraction = padded.slice(-scale);
    return `${currency} ${whole}.${fraction}`;
};

const openedAt = computed(() => {
    if (props.active_shift === null) return '—';
    return new Date(props.active_shift.opened_at_unix * 1000).toLocaleString();
});
</script>

<template>
    <main class="min-h-screen bg-slate-950 px-4 py-6 text-slate-100 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl space-y-6">
            <header class="rounded-2xl border border-slate-800 bg-slate-900/80 p-6 shadow-xl">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-cyan-300">POS · Live operations</p>
                        <h1 class="mt-2 text-2xl font-semibold sm:text-3xl">Active Shift Performance</h1>
                        <p class="mt-2 max-w-3xl text-sm text-slate-400">Read-only exact-device performance from immutable shift-bound sale, full-void, and cash-refund evidence.</p>
                    </div>
                    <a href="/pos" class="rounded-xl border border-slate-700 px-4 py-2 text-sm font-medium hover:bg-slate-800">Operations Hub</a>
                </div>
            </header>

            <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <div v-for="item in [['Tenant', scope.tenant_id], ['Organization', scope.organization_id], ['Outlet', scope.outlet_id], ['Device', scope.device_id]]" :key="item[0]" class="rounded-xl border border-slate-800 bg-slate-900 p-4">
                    <p class="text-xs uppercase tracking-wider text-slate-500">{{ item[0] }}</p>
                    <p class="mt-1 break-all text-sm font-medium">{{ item[1] }}</p>
                </div>
            </section>

            <section v-if="active_shift === null" class="rounded-2xl border border-amber-700/40 bg-amber-950/20 p-8">
                <h2 class="text-lg font-semibold text-amber-200">No active shift on this device</h2>
                <p class="mt-2 text-sm text-amber-100/70">This is a valid empty state. Start a shift before expecting live shift-bound sales performance.</p>
            </section>

            <template v-else>
                <section class="rounded-2xl border border-slate-800 bg-slate-900 p-5">
                    <div class="grid gap-4 md:grid-cols-3">
                        <div><p class="text-xs uppercase tracking-wider text-slate-500">Shift ID</p><p class="mt-1 break-all font-mono text-sm">{{ active_shift.shift_id }}</p></div>
                        <div><p class="text-xs uppercase tracking-wider text-slate-500">Opened</p><p class="mt-1 text-sm">{{ openedAt }}</p></div>
                        <div><p class="text-xs uppercase tracking-wider text-slate-500">Opening actor</p><p class="mt-1 break-all text-sm">{{ active_shift.opener_actor_identity_id }}</p></div>
                    </div>
                </section>

                <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div v-for="card in [['Completed sales', totalCompleted], ['Active sales', totalActive], ['Full voids', totalVoided], ['Cash refunds', totalRefunded]]" :key="card[0]" class="rounded-2xl border border-slate-800 bg-slate-900 p-5">
                        <p class="text-xs uppercase tracking-wider text-slate-500">{{ card[0] }}</p>
                        <p class="mt-2 text-3xl font-semibold">{{ card[1] }}</p>
                    </div>
                </section>

                <section class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900">
                    <div class="border-b border-slate-800 px-5 py-4"><h2 class="font-semibold">Tender & currency performance</h2><p class="mt-1 text-xs text-slate-500">Active net value = gross − full-sale void. Cash refund is shown separately and is not subtracted twice.</p></div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-800 text-sm">
                            <thead class="bg-slate-950/50 text-left text-xs uppercase tracking-wider text-slate-500"><tr><th class="px-5 py-3">Tender</th><th class="px-5 py-3">Currency</th><th class="px-5 py-3">Completed</th><th class="px-5 py-3">Voided</th><th class="px-5 py-3">Active</th><th class="px-5 py-3">Refunded</th><th class="px-5 py-3">Gross</th><th class="px-5 py-3">Void value</th><th class="px-5 py-3">Active net</th><th class="px-5 py-3">Cash refunded</th></tr></thead>
                            <tbody class="divide-y divide-slate-800">
                                <tr v-for="row in buckets" :key="`${row.tender_category}|${row.currency}|${row.scale}`"><td class="px-5 py-4 font-medium">{{ row.tender_category }}</td><td class="px-5 py-4">{{ row.currency }} · scale {{ row.scale }}</td><td class="px-5 py-4">{{ row.completed_sales }}</td><td class="px-5 py-4">{{ row.voided_sales }}</td><td class="px-5 py-4">{{ row.active_sales }}</td><td class="px-5 py-4">{{ row.refunded_sales }}</td><td class="px-5 py-4">{{ money(row.gross_atomic, row.currency, row.scale) }}</td><td class="px-5 py-4">{{ money(row.voided_atomic, row.currency, row.scale) }}</td><td class="px-5 py-4 font-semibold text-emerald-300">{{ money(row.active_net_atomic, row.currency, row.scale) }}</td><td class="px-5 py-4">{{ money(row.refunded_cash_atomic, row.currency, row.scale) }}</td></tr>
                                <tr v-if="buckets.length === 0"><td colspan="10" class="px-5 py-8 text-center text-slate-500">No completed sales are bound to the active shift yet.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </template>

            <footer class="text-xs text-slate-600">Correlation: {{ correlation_id }}</footer>
        </div>
    </main>
</template>
