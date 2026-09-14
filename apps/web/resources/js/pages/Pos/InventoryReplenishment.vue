<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

type InventoryItem = {
    product_id: string;
    display_name: string;
    available_quantity: string;
};

type ReplenishmentEvidence = {
    replenishment_id: string;
    product_id: string;
    before_available_quantity: string;
    replenished_quantity: string;
    after_available_quantity: string;
    occurred_at_unix: number;
};

type MutationError = { error?: { code?: string }; correlation_id?: string };

const props = defineProps<{
    scope: { tenant_id: string; organization_id: string; outlet_id: string; device_id: string };
    items: InventoryItem[];
    recent_replenishments: ReplenishmentEvidence[];
    endpoint: string;
    csrf_token: string;
    correlation_id: string;
}>();

const search = ref('');
const selectedProductId = ref('');
const quantity = ref('');
const submitting = ref(false);
const refreshRequired = ref(false);
const message = ref('');

const filteredItems = computed(() => {
    const needle = search.value.trim().toLocaleLowerCase('id-ID');
    return props.items.filter((item) => !needle
        || item.product_id.toLocaleLowerCase('id-ID').includes(needle)
        || item.display_name.toLocaleLowerCase('id-ID').includes(needle));
});

const selectedItem = computed(() => props.items.find((item) => item.product_id === selectedProductId.value) ?? null);

const formatQuantity = (value: string): string => new Intl.NumberFormat('id-ID').format(BigInt(value));

const formatUnix = (value: number): string => new Intl.DateTimeFormat('id-ID', {
    dateStyle: 'medium',
    timeStyle: 'short',
}).format(new Date(value * 1000));

const chooseItem = (item: InventoryItem): void => {
    selectedProductId.value = item.product_id;
    quantity.value = '';
    message.value = `Receiving target dipilih: ${item.display_name}.`;
};

const refreshAuthoritative = (): void => window.location.reload();

const replenish = async (): Promise<void> => {
    message.value = '';
    if (refreshRequired.value) {
        message.value = 'Refresh authoritative state diperlukan sebelum mutation berikutnya.';
        return;
    }

    if (!selectedItem.value) {
        message.value = 'Pilih produk yang sudah memiliki opening inventory baseline.';
        return;
    }

    const raw = quantity.value.trim();
    if (!/^[1-9][0-9]*$/.test(raw)) {
        message.value = 'Received quantity harus bilangan bulat positif.';
        return;
    }

    const received = BigInt(raw);
    if (received > BigInt(Number.MAX_SAFE_INTEGER)) {
        message.value = 'Received quantity melampaui batas integer aman pada client.';
        return;
    }

    submitting.value = true;
    try {
        const response = await fetch(props.endpoint, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                _token: props.csrf_token,
                operation_id: `replenish:${crypto.randomUUID()}`,
                product_id: selectedItem.value.product_id,
                replenished_quantity: Number(received),
            }),
        });

        const payload = await response.json() as {
            status?: string;
            after_available_quantity?: string;
        } & MutationError;

        if (!response.ok || payload.status !== 'replenished') {
            message.value = payload.error?.code
                ? `Inventory replenishment ditolak (${payload.error.code}).`
                : 'Inventory replenishment ditolak oleh server.';
            return;
        }

        refreshRequired.value = true;
        message.value = payload.after_available_quantity
            ? `Stock diterima. Authoritative stock baru: ${formatQuantity(payload.after_available_quantity)}. Refresh diwajibkan sebelum mutation berikutnya.`
            : 'Stock diterima. Refresh authoritative state diwajibkan sebelum mutation berikutnya.';
    } catch {
        refreshRequired.value = true;
        message.value = 'Status mutation tidak dapat dipastikan. Tidak ada retry otomatis; refresh authoritative state diwajibkan.';
    } finally {
        submitting.value = false;
    }
};
</script>

<template>
    <Head title="Inventory Replenishment" />
    <main class="page-shell">
        <header class="hero">
            <div>
                <p class="eyebrow">oneQay · Inventory Operations</p>
                <h1>Inventory Replenishment</h1>
                <p class="subtitle">Receive positive stock against a product that already has canonical opening inventory. This workspace does not provide arbitrary stock correction or negative adjustment.</p>
            </div>
            <div class="scope-card">
                <span>Outlet</span><strong>{{ props.scope.outlet_id }}</strong>
                <small>Device {{ props.scope.device_id }}</small>
            </div>
        </header>

        <div v-if="message" class="notice" :data-refresh="refreshRequired">{{ message }}</div>
        <button v-if="refreshRequired" class="refresh" type="button" @click="refreshAuthoritative">Refresh authoritative state</button>

        <section class="grid">
            <article class="panel receive-panel">
                <div class="panel-heading">
                    <div><p class="eyebrow">Positive receiving</p><h2>Record received stock</h2></div>
                    <span class="badge">Immutable evidence</span>
                </div>
                <p class="helper">Only active catalog items with an established opening baseline are eligible. The server locks current stock, records before/received/after evidence, and rejects unsafe overflow or replay mismatch.</p>
                <label>Selected product<input :value="selectedItem ? `${selectedItem.display_name} · ${selectedItem.product_id}` : ''" readonly placeholder="Pilih produk dari authoritative snapshot" /></label>
                <label>Current authoritative stock<input :value="selectedItem ? formatQuantity(selectedItem.available_quantity) : ''" readonly placeholder="—" /></label>
                <label>Received quantity<input v-model="quantity" inputmode="numeric" placeholder="100" :disabled="submitting || refreshRequired || !selectedItem" /></label>
                <button class="primary" type="button" :disabled="submitting || refreshRequired || !selectedItem" @click="replenish">Record replenishment</button>
            </article>

            <article class="panel evidence-panel">
                <div class="panel-heading">
                    <div><p class="eyebrow">Audit evidence</p><h2>Recent replenishments</h2></div>
                    <span class="badge">Latest 50</span>
                </div>
                <div class="evidence-list">
                    <div v-for="row in props.recent_replenishments" :key="row.replenishment_id" class="evidence-row">
                        <div><strong>{{ row.product_id }}</strong><span class="mono">{{ row.replenishment_id }}</span></div>
                        <div class="flow"><span>{{ formatQuantity(row.before_available_quantity) }}</span><b>+{{ formatQuantity(row.replenished_quantity) }}</b><span>→ {{ formatQuantity(row.after_available_quantity) }}</span></div>
                        <time>{{ formatUnix(row.occurred_at_unix) }}</time>
                    </div>
                    <p v-if="!props.recent_replenishments.length" class="empty">Belum ada replenishment evidence pada outlet ini.</p>
                </div>
            </article>
        </section>

        <section class="panel inventory-panel">
            <div class="panel-heading inventory-heading">
                <div><p class="eyebrow">Authoritative snapshot</p><h2>Baseline-established active products</h2></div>
                <input v-model="search" placeholder="Cari product ID / nama" />
            </div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Produk</th><th class="numeric">Current stock</th><th>Aksi</th></tr></thead>
                    <tbody>
                        <tr v-for="item in filteredItems" :key="item.product_id">
                            <td><strong>{{ item.display_name }}</strong><span class="mono block">{{ item.product_id }}</span></td>
                            <td class="numeric strong">{{ formatQuantity(item.available_quantity) }}</td>
                            <td><button type="button" :disabled="submitting || refreshRequired" @click="chooseItem(item)">Receive stock</button></td>
                        </tr>
                        <tr v-if="!filteredItems.length"><td colspan="3" class="empty">Tidak ada active product dengan baseline yang sesuai filter.</td></tr>
                    </tbody>
                </table>
            </div>
            <p class="helper">Sale completion decrements stock; full-sale void restores stock from immutable sale lines. Cash refund follows an existing void and does not restore stock a second time.</p>
        </section>

        <footer>Correlation: <span class="mono">{{ props.correlation_id }}</span></footer>
    </main>
</template>

<style scoped>
.page-shell { min-height: 100vh; padding: 32px; background: #f5f7fa; color: #172033; font-family: Inter, ui-sans-serif, system-ui, sans-serif; }
.hero { display: flex; justify-content: space-between; gap: 24px; align-items: flex-start; margin-bottom: 24px; }
.hero h1 { margin: 4px 0 8px; font-size: clamp(28px, 4vw, 42px); letter-spacing: -.04em; }
.eyebrow { margin: 0; font-size: 12px; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; color: #64748b; }
.subtitle, .helper { color: #64748b; line-height: 1.6; }
.scope-card, .panel { background: #fff; border: 1px solid #e2e8f0; border-radius: 18px; box-shadow: 0 14px 35px rgba(15, 23, 42, .06); }
.scope-card { min-width: 220px; padding: 18px; display: grid; gap: 4px; }
.scope-card span, .scope-card small { color: #64748b; }
.grid { display: grid; grid-template-columns: minmax(0, .9fr) minmax(0, 1.1fr); gap: 20px; margin-bottom: 20px; }
.panel { padding: 22px; }
.panel-heading { display: flex; justify-content: space-between; gap: 16px; align-items: flex-start; margin-bottom: 16px; }
.panel-heading h2 { margin: 3px 0 0; font-size: 20px; }
.badge { border-radius: 999px; padding: 6px 10px; background: #eef2ff; color: #3730a3; font-size: 11px; font-weight: 800; }
label { display: grid; gap: 7px; margin: 14px 0; font-size: 13px; font-weight: 700; color: #475569; }
input { width: 100%; box-sizing: border-box; border: 1px solid #cbd5e1; border-radius: 10px; padding: 11px 12px; background: #fff; color: #172033; }
input:read-only { background: #f8fafc; }
button { border: 1px solid #cbd5e1; border-radius: 10px; padding: 9px 12px; background: #fff; font-weight: 750; cursor: pointer; color: #172033; }
button:disabled { opacity: .45; cursor: not-allowed; }
.primary { width: 100%; margin-top: 8px; background: #172033; color: #fff; border-color: #172033; padding: 12px; }
.notice { margin-bottom: 12px; border: 1px solid #bfdbfe; background: #eff6ff; border-radius: 12px; padding: 12px 14px; color: #1e3a8a; }
.notice[data-refresh="true"] { border-color: #fed7aa; background: #fff7ed; color: #9a3412; }
.refresh { margin-bottom: 18px; }
.inventory-heading { align-items: center; }
.inventory-heading input { max-width: 320px; }
.table-wrap { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; }
th, td { padding: 13px 10px; border-bottom: 1px solid #e2e8f0; text-align: left; vertical-align: middle; }
th { font-size: 11px; text-transform: uppercase; letter-spacing: .07em; color: #64748b; }
.numeric { text-align: right; font-variant-numeric: tabular-nums; }
.strong { font-weight: 800; }
.block { display: block; margin-top: 4px; }
.mono { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; color: #64748b; font-size: 12px; }
.evidence-list { display: grid; gap: 10px; max-height: 390px; overflow-y: auto; }
.evidence-row { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 7px 14px; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px; }
.evidence-row > div:first-child { display: grid; gap: 3px; min-width: 0; }
.flow { display: flex; align-items: center; gap: 7px; font-variant-numeric: tabular-nums; }
.flow b { color: #166534; }
time { grid-column: 1 / -1; color: #64748b; font-size: 12px; }
.empty { text-align: center; color: #94a3b8; padding: 22px; }
footer { margin-top: 18px; color: #64748b; font-size: 12px; }
@media (max-width: 900px) { .page-shell { padding: 18px; } .hero { flex-direction: column; } .scope-card { width: 100%; box-sizing: border-box; } .grid { grid-template-columns: 1fr; } .inventory-heading { align-items: stretch; flex-direction: column; } .inventory-heading input { max-width: none; } }
</style>
