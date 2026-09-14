<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

type SetupItem = {
    product_id: string;
    display_name: string;
    unit_price_atomic: string;
    currency: string;
    scale: number;
    available_quantity: string;
    sellable: boolean;
    baseline_established: boolean;
    sale_history_exists: boolean;
    baseline_eligible: boolean;
};

type MutationError = { error?: { code?: string }; correlation_id?: string };

const props = defineProps<{
    scope: { tenant_id: string; organization_id: string; outlet_id: string; device_id: string };
    items: SetupItem[];
    endpoints: { catalog_prepare: string; inventory_baseline: string };
    csrf_token: string;
    correlation_id: string;
}>();

const search = ref('');
const stockFilter = ref<'ALL' | 'READY' | 'ZERO' | 'BASELINE_ELIGIBLE' | 'INACTIVE'>('ALL');
const submitting = ref(false);
const refreshRequired = ref(false);
const message = ref('');

const productId = ref('');
const displayName = ref('');
const unitPriceMajor = ref('');
const currency = ref('IDR');
const scale = ref(0);
const sellable = ref(true);
const openingQuantity = ref('');
const baselineProductId = ref('');

const filteredItems = computed(() => {
    const needle = search.value.trim().toLocaleLowerCase('id-ID');
    return props.items.filter((item) => {
        const matchesSearch = !needle
            || item.product_id.toLocaleLowerCase('id-ID').includes(needle)
            || item.display_name.toLocaleLowerCase('id-ID').includes(needle);
        if (!matchesSearch) return false;
        if (stockFilter.value === 'READY') return item.sellable && BigInt(item.available_quantity) > 0n;
        if (stockFilter.value === 'ZERO') return BigInt(item.available_quantity) === 0n;
        if (stockFilter.value === 'BASELINE_ELIGIBLE') return item.baseline_eligible;
        if (stockFilter.value === 'INACTIVE') return !item.sellable;
        return true;
    });
});

const formatAtomic = (atomic: string, code: string, moneyScale: number): string => {
    const value = BigInt(atomic);
    const base = 10n ** BigInt(moneyScale);
    const whole = value / base;
    const fraction = value % base;
    const wholeText = new Intl.NumberFormat('id-ID').format(whole);
    return moneyScale === 0
        ? `${code} ${wholeText}`
        : `${code} ${wholeText},${fraction.toString().padStart(moneyScale, '0')}`;
};

const majorFromAtomic = (atomic: string, moneyScale: number): string => {
    const value = BigInt(atomic);
    const base = 10n ** BigInt(moneyScale);
    const whole = value / base;
    const fraction = value % base;
    return moneyScale === 0 ? whole.toString() : `${whole}.${fraction.toString().padStart(moneyScale, '0')}`;
};

const parseMajorAtomic = (value: string, moneyScale: number): bigint | null => {
    const normalized = value.trim().replace(',', '.');
    const pattern = moneyScale === 0
        ? /^\d+$/
        : new RegExp(`^\\d+(?:\\.\\d{0,${moneyScale}})?$`);
    if (!pattern.test(normalized)) return null;
    const [whole, rawFraction = ''] = normalized.split('.');
    const fraction = rawFraction.padEnd(moneyScale, '0');
    return BigInt(whole) * (10n ** BigInt(moneyScale)) + BigInt(fraction || '0');
};

const editItem = (item: SetupItem): void => {
    productId.value = item.product_id;
    displayName.value = item.display_name;
    unitPriceMajor.value = majorFromAtomic(item.unit_price_atomic, item.scale);
    currency.value = item.currency;
    scale.value = item.scale;
    sellable.value = item.sellable;
    message.value = 'Catalog form loaded from authoritative workspace snapshot.';
};

const chooseBaseline = (item: SetupItem): void => {
    baselineProductId.value = item.product_id;
    openingQuantity.value = '';
    message.value = item.baseline_eligible
        ? 'Produk dipilih untuk opening inventory baseline.'
        : 'Produk ini tidak memenuhi syarat opening inventory baseline.';
};

const refreshAuthoritative = (): void => window.location.reload();

const prepareCatalog = async (): Promise<void> => {
    message.value = '';
    if (refreshRequired.value) {
        message.value = 'Refresh authoritative state diperlukan sebelum mutation berikutnya.';
        return;
    }

    const canonicalProductId = productId.value.trim().toLocaleLowerCase('en-US');
    if (!/^[a-z0-9](?:[a-z0-9_-]*[a-z0-9])?$/.test(canonicalProductId) || canonicalProductId.length > 64) {
        message.value = 'Product ID tidak valid.';
        return;
    }
    const canonicalName = displayName.value.trim();
    if (!canonicalName || canonicalName.length > 160) {
        message.value = 'Nama produk wajib diisi dan maksimal 160 karakter.';
        return;
    }
    const canonicalCurrency = currency.value.trim().toUpperCase();
    if (!/^[A-Z]{3}$/.test(canonicalCurrency) || !Number.isInteger(scale.value) || scale.value < 0 || scale.value > 6) {
        message.value = 'Currency atau scale tidak valid.';
        return;
    }
    const atomic = parseMajorAtomic(unitPriceMajor.value, scale.value);
    if (atomic === null || atomic > BigInt(Number.MAX_SAFE_INTEGER)) {
        message.value = 'Harga tidak valid atau melampaui batas integer aman pada client.';
        return;
    }

    submitting.value = true;
    try {
        const response = await fetch(props.endpoints.catalog_prepare, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                _token: props.csrf_token,
                operation_id: `catalog:${crypto.randomUUID()}`,
                product_id: canonicalProductId,
                display_name: canonicalName,
                unit_price_atomic: Number(atomic),
                currency: canonicalCurrency,
                currency_scale: scale.value,
                sellable: sellable.value,
            }),
        });
        const payload = await response.json() as { status?: string } & MutationError;
        if (!response.ok || payload.status !== 'prepared') {
            message.value = payload.error?.code
                ? `Catalog preparation ditolak (${payload.error.code}).`
                : 'Catalog preparation ditolak oleh server.';
            return;
        }
        refreshRequired.value = true;
        message.value = 'Catalog preparation berhasil. Refresh authoritative state diwajibkan sebelum langkah berikutnya.';
    } catch {
        refreshRequired.value = true;
        message.value = 'Status mutation tidak dapat dipastikan. Tidak ada retry otomatis; refresh authoritative state diwajibkan.';
    } finally {
        submitting.value = false;
    }
};

const establishBaseline = async (): Promise<void> => {
    message.value = '';
    if (refreshRequired.value) {
        message.value = 'Refresh authoritative state diperlukan sebelum mutation berikutnya.';
        return;
    }
    const item = props.items.find((candidate) => candidate.product_id === baselineProductId.value);
    if (!item || !item.baseline_eligible) {
        message.value = 'Pilih produk yang eligible untuk opening inventory baseline.';
        return;
    }
    if (!/^\d+$/.test(openingQuantity.value.trim())) {
        message.value = 'Opening quantity harus bilangan bulat non-negatif.';
        return;
    }
    const quantity = BigInt(openingQuantity.value.trim());
    if (quantity > BigInt(Number.MAX_SAFE_INTEGER)) {
        message.value = 'Opening quantity melampaui batas integer aman pada client.';
        return;
    }

    submitting.value = true;
    try {
        const response = await fetch(props.endpoints.inventory_baseline, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                _token: props.csrf_token,
                operation_id: `inventory:${crypto.randomUUID()}`,
                product_id: item.product_id,
                opening_quantity: Number(quantity),
            }),
        });
        const payload = await response.json() as { status?: string } & MutationError;
        if (!response.ok || payload.status !== 'established') {
            message.value = payload.error?.code
                ? `Inventory baseline ditolak (${payload.error.code}).`
                : 'Inventory baseline ditolak oleh server.';
            return;
        }
        refreshRequired.value = true;
        message.value = 'Opening inventory berhasil dicatat. Refresh authoritative state diwajibkan sebelum mutation berikutnya.';
    } catch {
        refreshRequired.value = true;
        message.value = 'Status mutation tidak dapat dipastikan. Tidak ada retry otomatis; refresh authoritative state diwajibkan.';
    } finally {
        submitting.value = false;
    }
};
</script>

<template>
    <Head title="Catalog & Inventory Setup" />
    <main class="setup-shell">
        <header class="hero">
            <div>
                <p class="eyebrow">oneQay · Product Operations</p>
                <h1>Catalog & Inventory Setup</h1>
                <p class="subtitle">Siapkan produk dan opening stock melalui mutation authority canonical. Tidak ada mutation gabungan atau retry otomatis.</p>
            </div>
            <div class="scope-card">
                <span>Outlet</span><strong>{{ props.scope.outlet_id }}</strong>
                <small>Device {{ props.scope.device_id }}</small>
            </div>
        </header>

        <div v-if="message" class="notice" :data-refresh="refreshRequired">{{ message }}</div>
        <button v-if="refreshRequired" class="refresh" type="button" @click="refreshAuthoritative">Refresh authoritative state</button>

        <section class="grid">
            <article class="panel">
                <div class="panel-heading"><div><p class="eyebrow">Step 1</p><h2>Catalog preparation</h2></div><span class="badge">Canonical POST</span></div>
                <div class="form-grid">
                    <label>Product ID<input v-model="productId" maxlength="64" placeholder="kopi-susu" :disabled="submitting || refreshRequired" /></label>
                    <label>Nama produk<input v-model="displayName" maxlength="160" placeholder="Kopi Susu" :disabled="submitting || refreshRequired" /></label>
                    <label>Harga major unit<input v-model="unitPriceMajor" inputmode="decimal" placeholder="15000" :disabled="submitting || refreshRequired" /></label>
                    <label>Currency<input v-model="currency" maxlength="3" placeholder="IDR" :disabled="submitting || refreshRequired" /></label>
                    <label>Scale<input v-model.number="scale" type="number" min="0" max="6" :disabled="submitting || refreshRequired" /></label>
                    <label class="check"><input v-model="sellable" type="checkbox" :disabled="submitting || refreshRequired" /> Sellable</label>
                </div>
                <button class="primary" type="button" :disabled="submitting || refreshRequired" @click="prepareCatalog">Save catalog state</button>
            </article>

            <article class="panel">
                <div class="panel-heading"><div><p class="eyebrow">Step 2</p><h2>Opening inventory</h2></div><span class="badge">One-time baseline</span></div>
                <p class="helper">Hanya tersedia jika stok = 0, baseline belum pernah dicatat, dan produk belum memiliki sale history pada outlet ini.</p>
                <label>Selected product<input v-model="baselineProductId" readonly placeholder="Pilih dari tabel" /></label>
                <label>Opening quantity<input v-model="openingQuantity" inputmode="numeric" placeholder="100" :disabled="submitting || refreshRequired" /></label>
                <button class="primary" type="button" :disabled="submitting || refreshRequired" @click="establishBaseline">Establish opening stock</button>
            </article>
        </section>

        <section class="panel inventory-panel">
            <div class="panel-heading inventory-heading">
                <div><p class="eyebrow">Authoritative snapshot</p><h2>Outlet catalog & stock</h2></div>
                <div class="filters">
                    <input v-model="search" placeholder="Cari product ID / nama" />
                    <select v-model="stockFilter">
                        <option value="ALL">Semua</option>
                        <option value="READY">Ready to sell</option>
                        <option value="ZERO">Stok nol</option>
                        <option value="BASELINE_ELIGIBLE">Baseline eligible</option>
                        <option value="INACTIVE">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Produk</th><th>Status</th><th>Harga</th><th class="numeric">Stock</th><th>Baseline</th><th>Aksi</th></tr></thead>
                    <tbody>
                        <tr v-for="item in filteredItems" :key="item.product_id">
                            <td><strong>{{ item.display_name }}</strong><span class="mono block">{{ item.product_id }}</span></td>
                            <td><span class="status" :data-state="item.sellable ? 'ACTIVE' : 'INACTIVE'">{{ item.sellable ? 'SELLABLE' : 'INACTIVE' }}</span></td>
                            <td>{{ formatAtomic(item.unit_price_atomic, item.currency, item.scale) }}</td>
                            <td class="numeric strong">{{ item.available_quantity }}</td>
                            <td>
                                <span v-if="item.baseline_eligible" class="status" data-state="ELIGIBLE">ELIGIBLE</span>
                                <span v-else-if="item.baseline_established" class="status" data-state="DONE">ESTABLISHED</span>
                                <span v-else-if="item.sale_history_exists" class="status" data-state="LOCKED">SALE HISTORY</span>
                                <span v-else class="status" data-state="LOCKED">NOT ELIGIBLE</span>
                            </td>
                            <td class="actions">
                                <button type="button" :disabled="submitting || refreshRequired" @click="editItem(item)">Edit catalog</button>
                                <button type="button" :disabled="submitting || refreshRequired || !item.baseline_eligible" @click="chooseBaseline(item)">Opening stock</button>
                            </td>
                        </tr>
                        <tr v-if="!filteredItems.length"><td colspan="6" class="empty">Tidak ada item yang sesuai filter.</td></tr>
                    </tbody>
                </table>
            </div>
            <p class="footnote">Maksimum 250 item ditampilkan per authoritative snapshot. Stock setelah transaksi tetap dimutasi oleh sale/void/refund authorities yang sudah ada.</p>
        </section>

        <footer>Correlation: <span class="mono">{{ props.correlation_id }}</span></footer>
    </main>
</template>

<style scoped>
.setup-shell { min-height: 100vh; padding: 32px; background: #f5f7fa; color: #172033; font-family: Inter, ui-sans-serif, system-ui, sans-serif; }
.hero { display: flex; justify-content: space-between; gap: 24px; align-items: flex-start; margin-bottom: 24px; }
.hero h1 { margin: 4px 0 8px; font-size: clamp(28px, 4vw, 42px); letter-spacing: -0.04em; }
.eyebrow { margin: 0; font-size: 12px; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; color: #64748b; }
.subtitle, .helper, .footnote { color: #64748b; }
.scope-card, .panel { background: #fff; border: 1px solid #e2e8f0; border-radius: 18px; box-shadow: 0 10px 30px rgba(15,23,42,.05); }
.scope-card { min-width: 230px; padding: 16px 18px; display: grid; gap: 4px; }
.scope-card span, .scope-card small { color: #64748b; }
.grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; margin-bottom: 18px; }
.panel { padding: 22px; }
.panel-heading { display: flex; justify-content: space-between; gap: 16px; align-items: center; margin-bottom: 18px; }
.panel-heading h2 { margin: 4px 0 0; font-size: 20px; }
.badge, .status { display: inline-flex; align-items: center; border-radius: 999px; padding: 5px 9px; font-size: 11px; font-weight: 800; background: #eef2ff; }
.form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
label { display: grid; gap: 6px; margin-bottom: 12px; font-size: 13px; font-weight: 700; }
input, select { width: 100%; box-sizing: border-box; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 10px; background: #fff; color: inherit; }
.check { display: flex; align-items: center; gap: 8px; }
.check input { width: auto; }
button { border: 1px solid #cbd5e1; background: #fff; border-radius: 10px; padding: 9px 12px; font-weight: 750; cursor: pointer; }
button:disabled { opacity: .45; cursor: not-allowed; }
.primary { background: #172033; border-color: #172033; color: #fff; }
.notice { margin-bottom: 12px; padding: 12px 14px; border-radius: 12px; background: #eef2ff; font-weight: 700; }
.notice[data-refresh="true"] { background: #fff7ed; }
.refresh { margin-bottom: 18px; }
.inventory-heading { align-items: flex-end; }
.filters { display: flex; gap: 8px; min-width: min(520px, 100%); }
.table-wrap { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; }
th, td { padding: 13px 10px; border-bottom: 1px solid #e2e8f0; text-align: left; vertical-align: middle; }
th { color: #64748b; font-size: 12px; text-transform: uppercase; letter-spacing: .05em; }
.numeric { text-align: right; }.strong { font-weight: 800; }.mono { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; }.block { display: block; margin-top: 4px; color: #64748b; font-size: 12px; }
.actions { display: flex; gap: 6px; white-space: nowrap; }
.status[data-state="ELIGIBLE"], .status[data-state="DONE"], .status[data-state="ACTIVE"] { background: #ecfdf5; }
.status[data-state="LOCKED"], .status[data-state="INACTIVE"] { background: #f1f5f9; color: #64748b; }
.empty { text-align: center; padding: 28px; color: #64748b; }.footnote { margin: 14px 0 0; font-size: 12px; }
footer { margin-top: 18px; color: #64748b; font-size: 12px; }
@media (max-width: 900px) { .hero, .panel-heading, .inventory-heading { flex-direction: column; align-items: stretch; }.grid { grid-template-columns: 1fr; }.form-grid { grid-template-columns: 1fr; }.filters { min-width: 0; flex-direction: column; }.setup-shell { padding: 18px; }.scope-card { min-width: 0; } }
</style>
