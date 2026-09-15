<script setup lang="ts">
import { computed } from 'vue';

type Scope = {
    tenant_id: string;
    organization_id: string;
    outlet_id: string;
    requester_device_id: string;
};

type Shift = {
    shift_id: string;
    device_id: string;
    opener_actor_identity_id: string;
    closer_actor_identity_id: string;
    opened_at_unix: number;
    closed_at_unix: number;
    duration_seconds: number;
    cutoff_at_unix: number;
    expected_cash_atomic: string;
    observed_closing_cash_atomic: string;
    variance_atomic: string;
    variance_direction: 'MATCH' | 'OVER' | 'SHORT';
    currency: string;
    scale: number;
    review_outcome: string | null;
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
    closed_shifts: Shift[];
    selected_shift: Shift | null;
    buckets: Bucket[];
    correlation_id: string;
}>();

const formatEpoch = (unix: number): string => new Date(unix * 1000).toLocaleString();

const formatDuration = (seconds: number): string => {
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const remainder = seconds % 60;
    return `${hours}h ${minutes}m ${remainder}s`;
};

const money = (atomic: string, currency: string, scale: number): string => {
    const negative = atomic.startsWith('-');
    const raw = negative ? atomic.slice(1) : atomic;
    const value = BigInt(raw || '0').toString();
    const padded = value.padStart(scale + 1, '0');
    const formatted = scale === 0
        ? padded
        : `${padded.slice(0, -scale) || '0'}.${padded.slice(-scale)}`;
    return `${negative ? '-' : ''}${currency} ${formatted}`;
};

const totalCompleted = computed(() => props.buckets.reduce((sum, row) => sum + BigInt(row.completed_sales), 0n).toString());
const totalVoided = computed(() => props.buckets.reduce((sum, row) => sum + BigInt(row.voided_sales), 0n).toString());
const totalActive = computed(() => props.buckets.reduce((sum, row) => sum + BigInt(row.active_sales), 0n).toString());
const totalRefunded = computed(() => props.buckets.reduce((sum, row) => sum + BigInt(row.refunded_sales), 0n).toString());
</script>

<template>
    <main class="min-h-screen bg-slate-950 px-4 py-6 text-slate-100 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl space-y-6">
            <header class="rounded-2xl border border-slate-800 bg-slate-900/80 p-6 shadow-xl">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-cyan-300">POS · Historical control</p>
                        <h1 class="mt-2 text-2xl font-semibold sm:text-3xl">Shift History Performance</h1>
                        <p class="mt-2 max-w-3xl text-sm text-slate-400">Read-only closed-shift accountability from immutable final-close, sale, full-void, and cash-refund evidence across devices in this outlet.</p>
                    </div>
                    <a href="/pos" class="rounded-xl border border-slate-700 px-4 py-2 text-sm font-medium hover:bg-slate-800">Operations Hub</a>
                </div>
            </header>

            <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <div v-for="item in [['Tenant', scope.tenant_id], ['Organization', scope.organization_id], ['Outlet', scope.outlet_id], ['Requester device', scope.requester_device_id]]" :key="item[0]" class="rounded-xl border border-slate-800 bg-slate-900 p-4">
                    <p class="text-xs uppercase tracking-wider text-slate-500">{{ item[0] }}</p>
                    <p class="mt-1 break-all text-sm font-medium">{{ item[1] }}</p>
                </div>
            </section>

            <section v-if="closed_shifts.length === 0" class="rounded-2xl border border-slate-800 bg-slate-900 p-8">
                <h2 class="text-lg font-semibold">No closed shifts yet</h2>
                <p class="mt-2 text-sm text-slate-400">This outlet has no shift with canonical Final Shift Close evidence in the bounded history.</p>
            </section>

            <div v-else class="grid gap-6 lg:grid-cols-[22rem_minmax(0,1fr)]">
                <aside class="rounded-2xl border border-slate-800 bg-slate-900 p-4">
                    <div class="mb-4"><h2 class="font-semibold">Recent closed shifts</h2><p class="mt-1 text-xs text-slate-500">Newest 50 canonical closed shifts.</p></div>
                    <div class="max-h-[44rem] space-y-2 overflow-y-auto pr-1">
                        <a v-for="shift in closed_shifts" :key="shift.shift_id" :href="`/pos/reporting/shift-history/${shift.shift_id}`" class="block rounded-xl border p-3 transition" :class="selected_shift?.shift_id === shift.shift_id ? 'border-cyan-500/60 bg-cyan-950/20' : 'border-slate-800 hover:bg-slate-800/60'">
                            <div class="flex items-center justify-between gap-3"><span class="font-mono text-xs text-slate-300">{{ shift.shift_id.slice(0, 12) }}…</span><span class="rounded-full px-2 py-1 text-[10px] font-semibold" :class="shift.variance_direction === 'MATCH' ? 'bg-emerald-950 text-emerald-300' : 'bg-amber-950 text-amber-300'">{{ shift.variance_direction }}</span></div>
                            <p class="mt-2 text-sm">{{ formatEpoch(shift.closed_at_unix) }}</p>
                            <p class="mt-1 text-xs text-slate-500">Device {{ shift.device_id }} · {{ formatDuration(shift.duration_seconds) }}</p>
                        </a>
                    </div>
                </aside>

                <div v-if="selected_shift" class="space-y-6">
                    <section class="rounded-2xl border border-slate-800 bg-slate-900 p-5">
                        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                            <div><p class="text-xs uppercase tracking-wider text-slate-500">Opened</p><p class="mt-1 text-sm">{{ formatEpoch(selected_shift.opened_at_unix) }}</p></div>
                            <div><p class="text-xs uppercase tracking-wider text-slate-500">Closed</p><p class="mt-1 text-sm">{{ formatEpoch(selected_shift.closed_at_unix) }}</p></div>
                            <div><p class="text-xs uppercase tracking-wider text-slate-500">Duration</p><p class="mt-1 text-sm">{{ formatDuration(selected_shift.duration_seconds) }}</p></div>
                            <div><p class="text-xs uppercase tracking-wider text-slate-500">Device</p><p class="mt-1 break-all text-sm">{{ selected_shift.device_id }}</p></div>
                            <div><p class="text-xs uppercase tracking-wider text-slate-500">Opening actor</p><p class="mt-1 break-all text-sm">{{ selected_shift.opener_actor_identity_id }}</p></div>
                            <div><p class="text-xs uppercase tracking-wider text-slate-500">Closing actor</p><p class="mt-1 break-all text-sm">{{ selected_shift.closer_actor_identity_id }}</p></div>
                            <div><p class="text-xs uppercase tracking-wider text-slate-500">Cutoff</p><p class="mt-1 text-sm">{{ formatEpoch(selected_shift.cutoff_at_unix) }}</p></div>
                            <div><p class="text-xs uppercase tracking-wider text-slate-500">Review</p><p class="mt-1 text-sm">{{ selected_shift.review_outcome ?? 'Not required' }}</p></div>
                        </div>
                    </section>

                    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        <div class="rounded-2xl border border-slate-800 bg-slate-900 p-5"><p class="text-xs uppercase tracking-wider text-slate-500">Expected cash</p><p class="mt-2 text-xl font-semibold">{{ money(selected_shift.expected_cash_atomic, selected_shift.currency, selected_shift.scale) }}</p></div>
                        <div class="rounded-2xl border border-slate-800 bg-slate-900 p-5"><p class="text-xs uppercase tracking-wider text-slate-500">Observed cash</p><p class="mt-2 text-xl font-semibold">{{ money(selected_shift.observed_closing_cash_atomic, selected_shift.currency, selected_shift.scale) }}</p></div>
                        <div class="rounded-2xl border border-slate-800 bg-slate-900 p-5"><p class="text-xs uppercase tracking-wider text-slate-500">Variance</p><p class="mt-2 text-xl font-semibold">{{ money(selected_shift.variance_atomic, selected_shift.currency, selected_shift.scale) }}</p></div>
                        <div class="rounded-2xl border border-slate-800 bg-slate-900 p-5"><p class="text-xs uppercase tracking-wider text-slate-500">Direction</p><p class="mt-2 text-xl font-semibold">{{ selected_shift.variance_direction }}</p></div>
                    </section>

                    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        <div v-for="card in [['Completed sales', totalCompleted], ['Active sales', totalActive], ['Full voids', totalVoided], ['Cash refunds', totalRefunded]]" :key="card[0]" class="rounded-2xl border border-slate-800 bg-slate-900 p-5"><p class="text-xs uppercase tracking-wider text-slate-500">{{ card[0] }}</p><p class="mt-2 text-3xl font-semibold">{{ card[1] }}</p></div>
                    </section>

                    <section class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900">
                        <div class="border-b border-slate-800 px-5 py-4"><h2 class="font-semibold">Tender & currency performance</h2><p class="mt-1 text-xs text-slate-500">Active net = gross − full-sale void. CASH refund is displayed separately and is not subtracted twice.</p></div>
                        <div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-800 text-sm"><thead class="bg-slate-950/50 text-left text-xs uppercase tracking-wider text-slate-500"><tr><th class="px-5 py-3">Tender</th><th class="px-5 py-3">Currency</th><th class="px-5 py-3">Completed</th><th class="px-5 py-3">Voided</th><th class="px-5 py-3">Active</th><th class="px-5 py-3">Refunded</th><th class="px-5 py-3">Gross</th><th class="px-5 py-3">Void value</th><th class="px-5 py-3">Active net</th><th class="px-5 py-3">Cash refunded</th></tr></thead><tbody class="divide-y divide-slate-800"><tr v-for="row in buckets" :key="`${row.tender_category}|${row.currency}|${row.scale}`"><td class="px-5 py-4 font-medium">{{ row.tender_category }}</td><td class="px-5 py-4">{{ row.currency }} · scale {{ row.scale }}</td><td class="px-5 py-4">{{ row.completed_sales }}</td><td class="px-5 py-4">{{ row.voided_sales }}</td><td class="px-5 py-4">{{ row.active_sales }}</td><td class="px-5 py-4">{{ row.refunded_sales }}</td><td class="px-5 py-4">{{ money(row.gross_atomic, row.currency, row.scale) }}</td><td class="px-5 py-4">{{ money(row.voided_atomic, row.currency, row.scale) }}</td><td class="px-5 py-4 font-semibold text-emerald-300">{{ money(row.active_net_atomic, row.currency, row.scale) }}</td><td class="px-5 py-4">{{ money(row.refunded_cash_atomic, row.currency, row.scale) }}</td></tr><tr v-if="buckets.length === 0"><td colspan="10" class="px-5 py-8 text-center text-slate-500">No completed sales are bound to this closed shift.</td></tr></tbody></table></div>
                    </section>
                </div>
            </div>

            <footer class="text-xs text-slate-600">Correlation: {{ correlation_id }}</footer>
        </div>
    </main>
</template>
