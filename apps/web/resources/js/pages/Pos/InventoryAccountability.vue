<script setup lang="ts">
type Scope = {
    tenant_id: string;
    organization_id: string;
    outlet_id: string;
    device_id: string;
};

type Item = {
    product_id: string;
    display_name: string;
    active: boolean;
    current_available_quantity: string;
    expected_available_quantity: string;
    opening_quantity: string;
    replenished_quantity: string;
    sold_quantity: string;
    restored_quantity: string;
};

type Movement = {
    movement_type: string;
    evidence_id: string;
    product_id: string;
    display_name: string;
    direction: 'IN' | 'OUT';
    quantity: string;
    occurred_at_unix: number;
    reference_id: string;
};

const props = defineProps<{
    scope: Scope;
    items: Item[];
    recent_movements: Movement[];
    correlation_id: string;
}>();

const formatTime = (unix: number): string =>
    new Intl.DateTimeFormat('id-ID', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(unix * 1000));

const movementLabel = (type: string): string => ({
    OPENING_BASELINE: 'Opening baseline',
    REPLENISHMENT: 'Replenishment',
    SALE_DECREMENT: 'Sale',
    FULL_SALE_VOID_RESTORATION: 'Sale void restoration',
}[type] ?? type);
</script>

<template>
    <main class="min-h-screen bg-slate-950 px-4 py-6 text-slate-100 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl space-y-6">
            <header class="rounded-2xl border border-slate-800 bg-slate-900/80 p-6 shadow-xl">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-emerald-300">Inventory control</p>
                        <h1 class="mt-2 text-2xl font-semibold sm:text-3xl">Inventory Accountability</h1>
                        <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-300">
                            Read-only reconciliation of canonical opening stock, replenishment, sale decrement,
                            and full-sale void restoration. Cash refunds never create a second stock movement.
                        </p>
                    </div>
                    <a href="/pos" class="inline-flex rounded-xl border border-slate-700 px-4 py-2 text-sm font-medium hover:bg-slate-800">
                        Back to POS Operations
                    </a>
                </div>
                <dl class="mt-5 grid gap-3 text-xs text-slate-300 sm:grid-cols-2 lg:grid-cols-4">
                    <div><dt class="text-slate-500">Tenant</dt><dd class="mt-1 break-all">{{ props.scope.tenant_id }}</dd></div>
                    <div><dt class="text-slate-500">Organization</dt><dd class="mt-1 break-all">{{ props.scope.organization_id }}</dd></div>
                    <div><dt class="text-slate-500">Outlet</dt><dd class="mt-1 break-all">{{ props.scope.outlet_id }}</dd></div>
                    <div><dt class="text-slate-500">Device context</dt><dd class="mt-1 break-all">{{ props.scope.device_id }}</dd></div>
                </dl>
            </header>

            <section class="rounded-2xl border border-emerald-900/60 bg-emerald-950/30 p-4 text-sm text-emerald-100">
                Every product shown has passed an exact integrity equation:
                <strong>opening + replenishment + void restoration − sold = current stock</strong>.
                Any mismatch fails closed at the server instead of presenting a misleading balance.
            </section>

            <section class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/80">
                <div class="border-b border-slate-800 px-5 py-4">
                    <h2 class="font-semibold">Stock accountability by product</h2>
                    <p class="mt-1 text-sm text-slate-400">{{ props.items.length }} baselined product(s)</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-950/70 text-left text-xs uppercase tracking-wide text-slate-400">
                            <tr>
                                <th class="px-4 py-3">Product</th>
                                <th class="px-4 py-3 text-right">Opening</th>
                                <th class="px-4 py-3 text-right">Received</th>
                                <th class="px-4 py-3 text-right">Sold</th>
                                <th class="px-4 py-3 text-right">Restored</th>
                                <th class="px-4 py-3 text-right">Expected</th>
                                <th class="px-4 py-3 text-right">Current</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            <tr v-for="item in props.items" :key="item.product_id" class="hover:bg-slate-800/40">
                                <td class="px-4 py-3">
                                    <div class="font-medium">{{ item.display_name }}</div>
                                    <div class="mt-1 text-xs text-slate-500">{{ item.product_id }} · {{ item.active ? 'ACTIVE' : 'INACTIVE' }}</div>
                                </td>
                                <td class="px-4 py-3 text-right font-mono">{{ item.opening_quantity }}</td>
                                <td class="px-4 py-3 text-right font-mono">{{ item.replenished_quantity }}</td>
                                <td class="px-4 py-3 text-right font-mono">{{ item.sold_quantity }}</td>
                                <td class="px-4 py-3 text-right font-mono">{{ item.restored_quantity }}</td>
                                <td class="px-4 py-3 text-right font-mono">{{ item.expected_available_quantity }}</td>
                                <td class="px-4 py-3 text-right font-mono font-semibold text-emerald-300">{{ item.current_available_quantity }}</td>
                            </tr>
                            <tr v-if="props.items.length === 0">
                                <td colspan="7" class="px-4 py-10 text-center text-slate-500">No baselined inventory is available in this scope.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/80">
                <div class="border-b border-slate-800 px-5 py-4">
                    <h2 class="font-semibold">Recent immutable stock evidence</h2>
                    <p class="mt-1 text-sm text-slate-400">
                        Up to 200 latest evidence movements. Ordering ties are display-only; reconciliation does not depend on timestamp order.
                    </p>
                </div>
                <div class="divide-y divide-slate-800">
                    <article v-for="movement in props.recent_movements" :key="movement.evidence_id" class="grid gap-3 px-5 py-4 sm:grid-cols-[1fr_auto]">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-medium">{{ movement.display_name }}</span>
                                <span class="rounded-full bg-slate-800 px-2 py-1 text-[11px] text-slate-300">{{ movementLabel(movement.movement_type) }}</span>
                            </div>
                            <p class="mt-1 text-xs text-slate-500">
                                {{ movement.reference_id }} · {{ formatTime(movement.occurred_at_unix) }}
                            </p>
                        </div>
                        <div class="self-center text-right font-mono text-lg font-semibold" :class="movement.direction === 'OUT' ? 'text-rose-300' : 'text-emerald-300'">
                            {{ movement.direction === 'OUT' ? '−' : '+' }}{{ movement.quantity }}
                        </div>
                    </article>
                    <div v-if="props.recent_movements.length === 0" class="px-5 py-10 text-center text-sm text-slate-500">
                        No immutable inventory movement evidence is available.
                    </div>
                </div>
            </section>

            <footer class="pb-4 text-xs text-slate-600">Correlation: {{ props.correlation_id }}</footer>
        </div>
    </main>
</template>
