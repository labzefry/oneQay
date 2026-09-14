<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

type CatalogItem = {
    product_id: string;
    display_name: string;
    available_quantity: number;
    unit_price: { atomic_units: string; currency: string; scale: number };
};

type SaleReceipt = {
    status: 'completed';
    sale_id: string;
    operation_id: string;
    total: { atomic_units: number; currency: string; scale: number };
    tender_category: string;
    evidence_mode: string;
    change: { atomic_units: number; currency: string; scale: number };
    correlation_id: string;
};

const props = defineProps<{
    scope: { tenant_id: string; organization_id: string; outlet_id: string; device_id: string };
    active_shift_id: string | null;
    catalog: CatalogItem[];
    sale_endpoint: string;
    csrf_token: string;
    correlation_id: string;
}>();

const catalog = ref<CatalogItem[]>(props.catalog.map((item) => ({ ...item, unit_price: { ...item.unit_price } })));
const search = ref('');
const cart = ref<Record<string, number>>({});
const tenderCategory = ref<'CASH' | 'MANUAL_EXTERNAL'>('CASH');
const tendered = ref('');
const submitting = ref(false);
const message = ref('');
const receipt = ref<SaleReceipt | null>(null);

const visibleCatalog = computed(() => {
    const needle = search.value.trim().toLocaleLowerCase('id-ID');
    if (!needle) return catalog.value;
    return catalog.value.filter((item) =>
        item.display_name.toLocaleLowerCase('id-ID').includes(needle)
        || item.product_id.toLocaleLowerCase('id-ID').includes(needle),
    );
});

const cartLines = computed(() => catalog.value
    .filter((item) => (cart.value[item.product_id] ?? 0) > 0)
    .map((item) => ({ item, quantity: cart.value[item.product_id] ?? 0 })));

const cartMoney = computed(() => {
    if (!cartLines.value.length) return null;
    const first = cartLines.value[0].item.unit_price;
    return { currency: first.currency, scale: first.scale };
});

const totalAtomic = computed(() => cartLines.value.reduce(
    (total, line) => total + BigInt(line.item.unit_price.atomic_units) * BigInt(line.quantity),
    0n,
));

const totalUnits = computed(() => cartLines.value.reduce((total, line) => total + line.quantity, 0));

const formatAtomic = (atomic: string | bigint, currency: string, scale: number): string => {
    const value = typeof atomic === 'bigint' ? atomic : BigInt(atomic);
    const base = 10n ** BigInt(scale);
    const whole = value / base;
    const fraction = value % base;
    const wholeText = new Intl.NumberFormat('id-ID').format(whole);
    if (scale === 0) return `${currency} ${wholeText}`;
    return `${currency} ${wholeText},${fraction.toString().padStart(scale, '0')}`;
};

const sameMoney = (item: CatalogItem): boolean => {
    if (!cartMoney.value) return true;
    return cartMoney.value.currency === item.unit_price.currency && cartMoney.value.scale === item.unit_price.scale;
};

const add = (item: CatalogItem): void => {
    message.value = '';
    receipt.value = null;
    if (!sameMoney(item)) {
        message.value = 'Satu transaksi hanya boleh menggunakan satu currency dan scale.';
        return;
    }
    const current = cart.value[item.product_id] ?? 0;
    if (current >= item.available_quantity) {
        message.value = 'Jumlah item telah mencapai stok tersedia.';
        return;
    }
    cart.value = { ...cart.value, [item.product_id]: current + 1 };
};

const decrement = (item: CatalogItem): void => {
    const current = cart.value[item.product_id] ?? 0;
    if (current <= 1) {
        const next = { ...cart.value };
        delete next[item.product_id];
        cart.value = next;
        return;
    }
    cart.value = { ...cart.value, [item.product_id]: current - 1 };
};

const parseTenderedAtomic = (value: string, scale: number): bigint | null => {
    const normalized = value.trim().replace(',', '.');
    const pattern = scale === 0
        ? /^\d+$/
        : new RegExp(`^\\d+(?:\\.\\d{0,${scale}})?$`);
    if (!pattern.test(normalized)) return null;
    const [whole, rawFraction = ''] = normalized.split('.');
    const fraction = rawFraction.padEnd(scale, '0');
    return BigInt(whole) * (10n ** BigInt(scale)) + BigInt(fraction || '0');
};

const useExactTotal = (): void => {
    if (!cartMoney.value) return;
    const scale = cartMoney.value.scale;
    const base = 10n ** BigInt(scale);
    const whole = totalAtomic.value / base;
    const fraction = totalAtomic.value % base;
    tendered.value = scale === 0 ? whole.toString() : `${whole}.${fraction.toString().padStart(scale, '0')}`;
};

const checkout = async (): Promise<void> => {
    message.value = '';
    receipt.value = null;

    if (!props.active_shift_id) {
        message.value = 'Shift aktif pada device ini diperlukan sebelum transaksi dapat diproses.';
        return;
    }
    if (!cartLines.value.length || !cartMoney.value) {
        message.value = 'Tambahkan minimal satu item ke transaksi.';
        return;
    }

    const tenderAtomic = parseTenderedAtomic(tendered.value, cartMoney.value.scale);
    if (tenderAtomic === null) {
        message.value = 'Nominal pembayaran tidak sesuai scale currency transaksi.';
        return;
    }
    if (tenderAtomic < totalAtomic.value) {
        message.value = 'Nominal pembayaran lebih kecil dari total transaksi.';
        return;
    }
    if (tenderCategory.value === 'MANUAL_EXTERNAL' && tenderAtomic !== totalAtomic.value) {
        message.value = 'Tender MANUAL_EXTERNAL harus sama persis dengan total transaksi.';
        return;
    }
    if (tenderAtomic > BigInt(Number.MAX_SAFE_INTEGER)) {
        message.value = 'Nominal pembayaran melampaui batas aman client; transaksi dibatalkan.';
        return;
    }

    submitting.value = true;
    const submittedLines = cartLines.value.map((line) => ({
        product_id: line.item.product_id,
        quantity: line.quantity,
    }));

    try {
        const response = await fetch(props.sale_endpoint, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                _token: props.csrf_token,
                operation_id: `sale:${crypto.randomUUID()}`,
                lines: submittedLines,
                tender_category: tenderCategory.value,
                tendered_atomic_units: Number(tenderAtomic),
                currency: cartMoney.value.currency,
                currency_scale: cartMoney.value.scale,
            }),
        });

        const payload = await response.json() as SaleReceipt | { error?: { code?: string }; correlation_id?: string };
        if (!response.ok || !('status' in payload) || payload.status !== 'completed') {
            const code = 'error' in payload ? payload.error?.code : undefined;
            message.value = code ? `Transaksi ditolak (${code}).` : 'Transaksi ditolak oleh server.';
            return;
        }

        receipt.value = payload;
        const sold = new Map(submittedLines.map((line) => [line.product_id, line.quantity]));
        catalog.value = catalog.value
            .map((item) => ({
                ...item,
                available_quantity: Math.max(0, item.available_quantity - (sold.get(item.product_id) ?? 0)),
            }))
            .filter((item) => item.available_quantity > 0);
        cart.value = {};
        tendered.value = '';
        message.value = 'Transaksi berhasil diselesaikan.';
    } catch {
        message.value = 'Transaksi tidak dapat dikirim. Tidak ada retry otomatis untuk mencegah duplikasi yang tidak terlihat.';
    } finally {
        submitting.value = false;
    }
};
</script>

<template>
    <Head title="Cashier Workspace" />
    <main class="cashier-shell">
        <header class="hero">
            <div>
                <p class="eyebrow">oneQay · Point of Sale</p>
                <h1>Cashier Workspace</h1>
                <p class="subtitle">Catalog dan checkout menggunakan scope serta transaction contract yang sudah terverifikasi.</p>
            </div>
            <div class="shift-card" :data-ready="Boolean(props.active_shift_id)">
                <span>Shift status</span>
                <strong>{{ props.active_shift_id ? 'ACTIVE' : 'NOT ACTIVE' }}</strong>
                <small v-if="props.active_shift_id" class="mono">{{ props.active_shift_id }}</small>
            </div>
        </header>

        <section class="scope-strip">
            <div><span>Outlet</span><strong>{{ props.scope.outlet_id }}</strong></div>
            <div><span>Device</span><strong>{{ props.scope.device_id }}</strong></div>
            <div><span>Catalog ready</span><strong>{{ catalog.length }} item</strong></div>
        </section>

        <div class="workspace-grid">
            <section class="panel catalog-panel">
                <div class="panel-heading">
                    <div><p class="eyebrow">Sellable catalog</p><h2>Pilih produk</h2></div>
                    <input v-model="search" class="search" type="search" placeholder="Cari nama / product ID" autocomplete="off">
                </div>
                <div class="catalog-grid">
                    <article v-for="item in visibleCatalog" :key="item.product_id" class="product-card">
                        <div>
                            <p class="product-name">{{ item.display_name }}</p>
                            <p class="mono muted">{{ item.product_id }}</p>
                        </div>
                        <div class="product-meta">
                            <strong>{{ formatAtomic(item.unit_price.atomic_units, item.unit_price.currency, item.unit_price.scale) }}</strong>
                            <span>Stok {{ item.available_quantity }}</span>
                        </div>
                        <button type="button" :disabled="!sameMoney(item) || (cart[item.product_id] ?? 0) >= item.available_quantity" @click="add(item)">Tambah</button>
                    </article>
                    <p v-if="!visibleCatalog.length" class="empty">Tidak ada catalog aktif dan berstok dalam scope outlet ini.</p>
                </div>
            </section>

            <aside class="panel cart-panel">
                <div class="panel-heading">
                    <div><p class="eyebrow">Current transaction</p><h2>Keranjang</h2></div>
                    <span class="pill">{{ totalUnits }} unit</span>
                </div>

                <div v-if="cartLines.length" class="cart-lines">
                    <div v-for="line in cartLines" :key="line.item.product_id" class="cart-line">
                        <div><strong>{{ line.item.display_name }}</strong><small>{{ formatAtomic(line.item.unit_price.atomic_units, line.item.unit_price.currency, line.item.unit_price.scale) }}</small></div>
                        <div class="qty"><button type="button" @click="decrement(line.item)">−</button><span>{{ line.quantity }}</span><button type="button" :disabled="line.quantity >= line.item.available_quantity" @click="add(line.item)">+</button></div>
                    </div>
                </div>
                <p v-else class="empty">Keranjang masih kosong.</p>

                <div class="total-row">
                    <span>Total</span>
                    <strong v-if="cartMoney">{{ formatAtomic(totalAtomic, cartMoney.currency, cartMoney.scale) }}</strong>
                    <strong v-else>—</strong>
                </div>

                <label>Tender
                    <select v-model="tenderCategory">
                        <option value="CASH">Cash</option>
                        <option value="MANUAL_EXTERNAL">Manual external</option>
                    </select>
                </label>
                <label>Nominal pembayaran
                    <div class="tender-row">
                        <input v-model="tendered" inputmode="decimal" placeholder="0" autocomplete="off">
                        <button type="button" class="secondary" :disabled="!cartMoney" @click="useExactTotal">Pas</button>
                    </div>
                </label>

                <button class="checkout" type="button" :disabled="submitting || !props.active_shift_id || !cartLines.length" @click="checkout">
                    {{ submitting ? 'Memproses…' : 'Selesaikan transaksi' }}
                </button>
                <p class="guard-note">Checkout tidak melakukan retry otomatis. Server tetap menjadi authority untuk harga, stok, shift, permission, dan idempotency.</p>
            </aside>
        </div>

        <section v-if="message || receipt" class="result" aria-live="polite">
            <strong>{{ message }}</strong>
            <div v-if="receipt" class="receipt-grid">
                <span>Sale <b class="mono">{{ receipt.sale_id }}</b></span>
                <span>Total <b>{{ formatAtomic(BigInt(receipt.total.atomic_units), receipt.total.currency, receipt.total.scale) }}</b></span>
                <span>Change <b>{{ formatAtomic(BigInt(receipt.change.atomic_units), receipt.change.currency, receipt.change.scale) }}</b></span>
                <span>Evidence <b>{{ receipt.evidence_mode }}</b></span>
            </div>
        </section>

        <footer>Page correlation: <span class="mono">{{ props.correlation_id }}</span></footer>
    </main>
</template>

<style scoped>
.cashier-shell{min-height:100vh;background:#f5f7fa;color:#172033;padding:28px;font-family:Inter,ui-sans-serif,system-ui,sans-serif}.hero{max-width:1380px;margin:0 auto 20px;display:flex;justify-content:space-between;gap:24px;align-items:flex-end}.eyebrow{margin:0;color:#526175;font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.12em}h1{margin:4px 0 7px;font-size:clamp(2rem,4vw,3rem);letter-spacing:-.04em}.subtitle{margin:0;color:#667085}.shift-card,.panel,.scope-strip,.result{background:#fff;border:1px solid #e4e8ef;border-radius:16px;box-shadow:0 8px 24px rgba(16,24,40,.04)}.shift-card{padding:14px 18px;min-width:230px;border-left:4px solid #d92d20}.shift-card[data-ready="true"]{border-left-color:#039855}.shift-card span,.shift-card small{display:block;color:#667085;font-size:.75rem}.shift-card strong{display:block;margin:3px 0}.scope-strip{max-width:1380px;margin:0 auto 18px;padding:14px 18px;display:grid;grid-template-columns:repeat(3,1fr);gap:16px}.scope-strip span{display:block;color:#667085;font-size:.72rem}.scope-strip strong{font-size:.9rem}.workspace-grid{max-width:1380px;margin:0 auto;display:grid;grid-template-columns:minmax(0,1.65fr) minmax(330px,.75fr);gap:18px;align-items:start}.panel{padding:20px}.panel-heading{display:flex;justify-content:space-between;gap:16px;align-items:center;margin-bottom:16px}h2{margin:3px 0 0;font-size:1.18rem}.search,input,select{border:1px solid #d0d5dd;border-radius:10px;background:#fff;padding:10px 12px;font:inherit;color:#172033}.search{width:min(320px,48vw)}.catalog-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:12px}.product-card{border:1px solid #e4e8ef;border-radius:14px;padding:14px;display:flex;flex-direction:column;gap:12px;justify-content:space-between;min-height:155px}.product-name{margin:0;font-weight:750}.muted{color:#98a2b3}.product-meta{display:flex;justify-content:space-between;gap:8px;align-items:end}.product-meta span{font-size:.75rem;color:#667085}button{border:0;border-radius:10px;padding:9px 12px;background:#172033;color:#fff;font-weight:700;cursor:pointer}button:disabled{opacity:.42;cursor:not-allowed}.cart-panel{position:sticky;top:18px}.pill{border-radius:999px;background:#eef2f6;padding:6px 9px;font-size:.75rem;font-weight:800}.cart-lines{display:grid;gap:10px}.cart-line{display:flex;justify-content:space-between;gap:10px;padding:11px 0;border-bottom:1px solid #edf0f4}.cart-line small{display:block;color:#667085;margin-top:3px}.qty{display:flex;align-items:center;gap:8px}.qty button{width:30px;height:30px;padding:0;background:#eef2f6;color:#172033}.total-row{display:flex;justify-content:space-between;align-items:center;padding:18px 0;margin-top:8px;font-size:1.06rem}.total-row strong{font-size:1.35rem}.cart-panel label{display:grid;gap:7px;margin:12px 0;color:#475467;font-size:.8rem;font-weight:700}.tender-row{display:grid;grid-template-columns:1fr auto;gap:8px}.secondary{background:#eef2f6;color:#172033}.checkout{width:100%;padding:13px;margin-top:8px}.guard-note{font-size:.72rem;color:#98a2b3;line-height:1.5}.empty{text-align:center;color:#98a2b3;padding:26px 10px}.result{max-width:1380px;margin:18px auto 0;padding:18px}.receipt-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-top:10px}.receipt-grid span{background:#f8fafc;border-radius:10px;padding:10px;font-size:.8rem}.mono{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:.76rem}footer{max-width:1380px;margin:12px auto;color:#98a2b3;font-size:.72rem}@media(max-width:900px){.cashier-shell{padding:18px 12px}.hero{align-items:stretch;flex-direction:column}.shift-card{min-width:0}.scope-strip{grid-template-columns:1fr}.workspace-grid{grid-template-columns:1fr}.cart-panel{position:static}.receipt-grid{grid-template-columns:1fr 1fr}}@media(max-width:560px){.panel-heading{align-items:stretch;flex-direction:column}.search{width:auto}.receipt-grid{grid-template-columns:1fr}}
</style>
