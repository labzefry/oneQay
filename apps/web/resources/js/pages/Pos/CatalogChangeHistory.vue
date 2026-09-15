<script setup lang="ts">
import { computed, ref } from 'vue';

type Scope = {
    tenant_id: string;
    organization_id: string;
    outlet_id: string;
    device_id: string;
};

type CatalogState = {
    display_name: string;
    unit_price_atomic: string;
    currency: string;
    scale: number;
    sellable: boolean;
};

type CatalogChange = {
    mutation_id: string;
    operation_id: string;
    actor_identity_id: string;
    device_id: string;
    product_id: string;
    change_type: 'CREATE' | 'UPDATE';
    before: CatalogState | null;
    after: CatalogState;
    correlation_id: string;
    occurred_at_unix: number;
};

const props = defineProps<{
    scope: Scope;
    changes: CatalogChange[];
    truncated: boolean;
    correlation_id: string;
}>();

const search = ref('');
const normalizedSearch = computed(() => search.value.trim().toLowerCase());
const visibleChanges = computed(() => {
    const query = normalizedSearch.value;
    if (query === '') return props.changes;

    return props.changes.filter((change) => [
        change.product_id,
        change.after.display_name,
        change.before?.display_name ?? '',
        change.actor_identity_id,
        change.device_id,
        change.change_type,
    ].some((value) => value.toLowerCase().includes(query)));
});

const formatEpoch = (unix: number): string => new Date(unix * 1000).toLocaleString();

const money = (state: CatalogState): string => {
    const value = BigInt(state.unit_price_atomic).toString();
    const padded = value.padStart(state.scale + 1, '0');
    const formatted = state.scale === 0
        ? padded
        : `${padded.slice(0, -state.scale) || '0'}.${padded.slice(-state.scale)}`;
    return `${state.currency} ${formatted}`;
};

const sellableLabel = (value: boolean): string => value ? 'Sellable' : 'Not sellable';
</script>

<template>
    <main class="min-h-screen bg-slate-950 px-4 py-6 text-slate-100 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl space-y-6">
            <header class="rounded-2xl border border-slate-800 bg-slate-900/80 p-6 shadow-xl">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-cyan-300">POS · Catalog audit</p>
                        <h1 class="mt-2 text-2xl font-semibold sm:text-3xl">Catalog Change History</h1>
                        <p class="mt-2 max-w-3xl text-sm text-slate-400">Read-only immutable audit of catalog creation, name, price, currency, scale, and sellable-state changes across devices in this outlet.</p>
                    </div>
                    <a href="/pos" class="rounded-xl border border-slate-700 px-4 py-2 text-sm font-medium hover:bg-slate-800">Operations Hub</a>
                </div>
            </header>

            <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <div v-for="item in [['Tenant', scope.tenant_id], ['Organization', scope.organization_id], ['Outlet', scope.outlet_id], ['Requester device', scope.device_id]]" :key="item[0]" class="rounded-xl border border-slate-800 bg-slate-900 p-4">
                    <p class="text-xs uppercase tracking-wider text-slate-500">{{ item[0] }}</p>
                    <p class="mt-1 break-all text-sm font-medium">{{ item[1] }}</p>
                </div>
            </section>

            <section v-if="truncated" class="rounded-xl border border-amber-700/60 bg-amber-950/30 p-4 text-sm text-amber-200">
                Showing the newest 200 immutable catalog changes. Older evidence remains durable but is outside this bounded workspace response.
            </section>

            <section class="rounded-2xl border border-slate-800 bg-slate-900 p-5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div><h2 class="font-semibold">Immutable change evidence</h2><p class="mt-1 text-xs text-slate-500">Newest first · outlet-wide across devices · {{ changes.length }} rows loaded</p></div>
                    <label class="block w-full sm:max-w-sm"><span class="mb-1 block text-xs uppercase tracking-wider text-slate-500">Search</span><input v-model="search" type="search" placeholder="Product, name, actor, device…" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-sm outline-none focus:border-cyan-500" /></label>
                </div>
            </section>

            <section v-if="changes.length === 0" class="rounded-2xl border border-slate-800 bg-slate-900 p-8">
                <h2 class="text-lg font-semibold">No catalog changes yet</h2>
                <p class="mt-2 text-sm text-slate-400">No immutable catalog preparation evidence exists for this organization and outlet.</p>
            </section>

            <section v-else class="space-y-4">
                <article v-for="change in visibleChanges" :key="change.mutation_id" class="rounded-2xl border border-slate-800 bg-slate-900 p-5">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <div class="flex flex-wrap items-center gap-2"><span class="rounded-full px-2.5 py-1 text-xs font-semibold" :class="change.change_type === 'CREATE' ? 'bg-emerald-950 text-emerald-300' : 'bg-cyan-950 text-cyan-300'">{{ change.change_type }}</span><span class="font-mono text-xs text-slate-400">{{ change.product_id }}</span></div>
                            <h2 class="mt-2 text-lg font-semibold">{{ change.after.display_name }}</h2>
                            <p class="mt-1 text-xs text-slate-500">{{ formatEpoch(change.occurred_at_unix) }} · Actor {{ change.actor_identity_id }} · Device {{ change.device_id }}</p>
                        </div>
                        <div class="text-left lg:text-right"><p class="text-xs uppercase tracking-wider text-slate-500">Mutation</p><p class="mt-1 font-mono text-xs text-slate-300">{{ change.mutation_id }}</p></div>
                    </div>

                    <div class="mt-5 grid gap-4 lg:grid-cols-2">
                        <div class="rounded-xl border border-slate-800 bg-slate-950/60 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Before</p>
                            <p v-if="change.before === null" class="mt-3 text-sm text-slate-500">Product did not exist in the outlet catalog.</p>
                            <template v-else><p class="mt-3 font-medium">{{ change.before.display_name }}</p><p class="mt-1 text-sm text-slate-300">{{ money(change.before) }} · {{ sellableLabel(change.before.sellable) }}</p></template>
                        </div>
                        <div class="rounded-xl border border-slate-800 bg-slate-950/60 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">After</p>
                            <p class="mt-3 font-medium">{{ change.after.display_name }}</p><p class="mt-1 text-sm text-slate-300">{{ money(change.after) }} · {{ sellableLabel(change.after.sellable) }}</p>
                        </div>
                    </div>

                    <div class="mt-4 grid gap-2 text-xs text-slate-500 sm:grid-cols-2">
                        <p class="break-all">Operation: <span class="font-mono text-slate-400">{{ change.operation_id }}</span></p>
                        <p class="break-all">Correlation: <span class="font-mono text-slate-400">{{ change.correlation_id }}</span></p>
                    </div>
                </article>

                <div v-if="visibleChanges.length === 0" class="rounded-2xl border border-slate-800 bg-slate-900 p-8 text-center text-sm text-slate-500">No loaded catalog change matches the current search.</div>
            </section>

            <footer class="text-xs text-slate-600">Request correlation: {{ correlation_id }}</footer>
        </div>
    </main>
</template>
