<script setup lang="ts">
import { t } from "../../i18n";
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import WorkspaceIcon from '../../components/WorkspaceIcon.vue';

type CatalogItem = {
    product_id: string;
    display_name: string;
    available_quantity: number;
    unit_price: { atomic_units: string; currency: string; scale: number };
};

type ReceiptMoney = { atomic_units: number; currency: string; scale: number };

type ReceiptLine = {
    product_id: string;
    quantity: number;
    unit_price: ReceiptMoney;
    line_total: ReceiptMoney;
};

type SaleReceipt = {
    status: 'completed';
    sale_id: string;
    operation_id: string;
    tenant_id: string;
    organization_id: string;
    outlet_id: string;
    register_context: { device_id: string };
    lines: ReceiptLine[];
    total: ReceiptMoney;
    tender_category: string;
    evidence_mode: string;
    change: ReceiptMoney;
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

const catalogNames = new Map(props.catalog.map((item) => [item.product_id, item.display_name]));

const receiptUnitCount = computed(() => receipt.value?.lines.reduce(
    (total, line) => total + line.quantity,
    0,
) ?? 0);

const receiptProductName = (productId: string): string => catalogNames.get(productId) ?? productId;

const printReceipt = (): void => {
    if (!receipt.value) return;
    window.print();
};

const startNextSale = (): void => {
    receipt.value = null;
    message.value = '';
    search.value = '';
};

const formatAtomic = (atomic: string | number | bigint, currency: string, scale: number): string => {
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
    <Head :title="t(&quot;Cashier&quot;)" />
    <main class="cashier-shell" :data-leave-confirmation="submitting || (cartLines.length > 0 && !receipt)">
        <header class="hero">
            <div>
                <p class="eyebrow">{{ t("oneQay · Point of Sale") }}</p>
                <h1>{{ t("Cashier") }}</h1>
                <p class="subtitle">{{ t("Pilih produk, susun pesanan, dan terima pembayaran.") }}</p>
            </div>
            <div class="shift-card" :data-ready="Boolean(props.active_shift_id)">
                <span>{{ t("Shift status") }}</span>
                <strong>{{ t(props.active_shift_id ? 'ACTIVE' : 'NOT ACTIVE') }}</strong>
                <small v-if="props.active_shift_id" class="mono">{{ props.active_shift_id }}</small>
            </div>
        </header>

        <section class="scope-strip" :aria-label="t(&quot;Ringkasan kasir&quot;)"><div><span>{{ t("Produk tersedia") }}</span><strong>{{ catalog.length }} {{ t("produk") }}</strong></div><div><span>{{ t("Item dalam pesanan") }}</span><strong>{{ totalUnits }} {{ t("item") }}</strong></div><div><span>{{ t("Shift kasir") }}</span><strong>{{ t(props.active_shift_id ? 'Aktif' : 'Belum dimulai') }}</strong></div></section>

        <div class="workspace-grid">
            <section class="panel catalog-panel">
                <div class="panel-heading">
                    <div><p class="eyebrow">{{ t("Sellable catalog") }}</p><h2>{{ t("Pilih produk") }}</h2></div>
                    <input v-model="search" class="search" :aria-label="t(&quot;Cari produk&quot;)" type="search" :placeholder="t(&quot;Cari nama / product ID&quot;)" autocomplete="off">
                </div>
                <div class="catalog-grid">
                    <article v-for="item in visibleCatalog" :key="item.product_id" class="product-card">
                        <div class="product-visual"><WorkspaceIcon name="package" :size="30" /></div>
                        <div>
                            <p class="product-name">{{ item.display_name }}</p>
                            <details class="product-reference"><summary>{{ t("ID produk") }}</summary><small>{{ item.product_id }}</small></details>
                        </div>
                        <div class="product-meta">
                            <strong>{{ formatAtomic(item.unit_price.atomic_units, item.unit_price.currency, item.unit_price.scale) }}</strong>
                            <span>{{ t("Stok") }} {{ item.available_quantity }}</span>
                        </div>
                        <button type="button" :disabled="!sameMoney(item) || (cart[item.product_id] ?? 0) >= item.available_quantity" @click="add(item)">{{ t("Tambah") }}</button>
                    </article>
                    <p v-if="!visibleCatalog.length" class="empty">{{ t("Belum ada produk yang cocok. Periksa pencarian, katalog, dan stok produk Anda.") }}</p>
                </div>
            </section>

            <aside class="panel cart-panel">
                <div class="panel-heading">
                    <div><p class="eyebrow">{{ t("Current transaction") }}</p><h2>{{ t("Keranjang") }}</h2></div>
                    <span class="pill">{{ totalUnits }} {{ t("unit") }}</span>
                </div>

                <div v-if="cartLines.length" class="cart-lines">
                    <div v-for="line in cartLines" :key="line.item.product_id" class="cart-line">
                        <div><strong>{{ line.item.display_name }}</strong><small>{{ formatAtomic(line.item.unit_price.atomic_units, line.item.unit_price.currency, line.item.unit_price.scale) }}</small></div>
                        <div class="qty"><button type="button" @click="decrement(line.item)">−</button><span>{{ line.quantity }}</span><button type="button" :disabled="line.quantity >= line.item.available_quantity" @click="add(line.item)">+</button></div>
                    </div>
                </div>
                <p v-else class="empty">{{ t("Keranjang masih kosong.") }}</p>

                <div class="total-row">
                    <span>{{ t("Total") }}</span>
                    <strong v-if="cartMoney">{{ formatAtomic(totalAtomic, cartMoney.currency, cartMoney.scale) }}</strong>
                    <strong v-else>—</strong>
                </div>

                <label>{{ t("Tender") }} <select v-model="tenderCategory">
                        <option value="CASH">{{ t("Cash") }}</option>
                        <option value="MANUAL_EXTERNAL">{{ t("Manual external") }}</option>
                    </select>
                </label>
                <label>{{ t("Nominal pembayaran") }} <div class="tender-row">
                        <input v-model="tendered" inputmode="decimal" placeholder="0" autocomplete="off">
                        <button type="button" class="secondary" :disabled="!cartMoney" @click="useExactTotal">{{ t("Pas") }}</button>
                    </div>
                </label>

                <button class="checkout" type="button" :disabled="submitting || !props.active_shift_id || !cartLines.length" @click="checkout">
                    {{ t(submitting ? 'Memproses…' : 'Selesaikan transaksi') }}
                </button>
                <p class="guard-note">{{ t("Periksa pesanan dan pembayaran sebelum menyelesaikan transaksi.") }}</p>
            </aside>
        </div>

        <section v-if="message || receipt" class="result" aria-live="polite">
            <strong>{{ t(message) }}</strong>
            <article v-if="receipt" class="receipt-card" :aria-label="t(&quot;Authoritative sale receipt&quot;)">
                <div class="receipt-heading">
                    <div>
                        <p class="eyebrow">{{ t("Authoritative receipt") }}</p>
                        <h2>{{ t("Sale completed") }}</h2>
                        <p class="receipt-subtitle">{{ t("Financial lines below come from the server-completed sale receipt, not from a client-side cart estimate.") }}</p>
                    </div>
                    <span class="receipt-status">{{ t("COMPLETED") }}</span>
                </div>

                <div class="receipt-meta">
                    <span>{{ t("Sale") }} <b class="mono">{{ receipt.sale_id }}</b></span>
                    <span>{{ t("Operation") }} <b class="mono">{{ receipt.operation_id }}</b></span>
                    <span>{{ t("Outlet") }} <b class="mono">{{ receipt.outlet_id }}</b></span>
                    <span>{{ t("Device") }} <b class="mono">{{ receipt.register_context.device_id }}</b></span>
                </div>

                <div class="receipt-lines">
                    <div v-for="line in receipt.lines" :key="line.product_id" class="receipt-line">
                        <div>
                            <strong>{{ receiptProductName(line.product_id) }}</strong>
                            <small class="mono">{{ line.product_id }}</small>
                        </div>
                        <span>{{ line.quantity }} × {{ formatAtomic(line.unit_price.atomic_units, line.unit_price.currency, line.unit_price.scale) }}</span>
                        <b>{{ formatAtomic(line.line_total.atomic_units, line.line_total.currency, line.line_total.scale) }}</b>
                    </div>
                </div>

                <div class="receipt-totals">
                    <span>{{ t("Units") }} <b>{{ receiptUnitCount }}</b></span>
                    <span>{{ t("Tender") }} <b>{{ t(receipt.tender_category) }}</b></span>
                    <span>{{ t("Total") }} <b>{{ formatAtomic(receipt.total.atomic_units, receipt.total.currency, receipt.total.scale) }}</b></span>
                    <span>{{ t("Change") }} <b>{{ formatAtomic(receipt.change.atomic_units, receipt.change.currency, receipt.change.scale) }}</b></span>
                    <span>{{ t("Evidence") }} <b>{{ t(receipt.evidence_mode) }}</b></span>
                </div>

                <div class="receipt-actions no-print">
                    <button type="button" class="secondary" @click="printReceipt">{{ t("Print receipt") }}</button>
                    <button type="button" @click="startNextSale">{{ t("Next sale") }}</button>
                </div>
                <p class="receipt-correlation">{{ t("Receipt correlation:") }} <span class="mono">{{ receipt.correlation_id }}</span></p>
            </article>
        </section>

        <footer>{{ t("Page correlation:") }} <span class="mono">{{ props.correlation_id }}</span></footer>
    </main>
</template>

<style scoped>
.cashier-shell{min-height:100vh;background:#f5f7fa;color:#172033;padding:28px;font-family:Inter,ui-sans-serif,system-ui,sans-serif}.hero{max-width:1380px;margin:0 auto 20px;display:flex;justify-content:space-between;gap:24px;align-items:flex-end}.eyebrow{margin:0;color:#526175;font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.12em}h1{margin:4px 0 7px;font-size:clamp(2rem,4vw,3rem);letter-spacing:-.04em}.subtitle{margin:0;color:#667085}.shift-card,.panel,.scope-strip,.result{background:#fff;border:1px solid #e4e8ef;border-radius:16px;box-shadow:0 8px 24px rgba(16,24,40,.04)}.shift-card{padding:14px 18px;min-width:230px;border-left:4px solid #d92d20}.shift-card[data-ready="true"]{border-left-color:#039855}.shift-card span,.shift-card small{display:block;color:#667085;font-size:.75rem}.shift-card strong{display:block;margin:3px 0}.scope-strip{max-width:1380px;margin:0 auto 18px;padding:14px 18px;display:grid;grid-template-columns:repeat(3,1fr);gap:16px}.scope-strip span{display:block;color:#667085;font-size:.72rem}.scope-strip strong{font-size:.9rem}.workspace-grid{max-width:1380px;margin:0 auto;display:grid;grid-template-columns:minmax(0,1.65fr) minmax(330px,.75fr);gap:18px;align-items:start}.panel{padding:20px}.panel-heading{display:flex;justify-content:space-between;gap:16px;align-items:center;margin-bottom:16px}h2{margin:3px 0 0;font-size:1.18rem}.search,input,select{border:1px solid #d0d5dd;border-radius:10px;background:#fff;padding:10px 12px;font:inherit;color:#172033}.search{width:min(320px,48vw)}.catalog-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:12px}.product-card{border:1px solid #e4e8ef;border-radius:14px;padding:14px;display:flex;flex-direction:column;gap:12px;justify-content:space-between;min-height:155px}.product-name{margin:0;font-weight:750}.muted{color:#98a2b3}.product-meta{display:flex;justify-content:space-between;gap:8px;align-items:end}.product-meta span{font-size:.75rem;color:#667085}button{border:0;border-radius:10px;padding:9px 12px;background:#172033;color:#fff;font-weight:700;cursor:pointer}button:disabled{opacity:.42;cursor:not-allowed}.cart-panel{position:sticky;top:18px}.pill{border-radius:999px;background:#eef2f6;padding:6px 9px;font-size:.75rem;font-weight:800}.cart-lines{display:grid;gap:10px}.cart-line{display:flex;justify-content:space-between;gap:10px;padding:11px 0;border-bottom:1px solid #edf0f4}.cart-line small{display:block;color:#667085;margin-top:3px}.qty{display:flex;align-items:center;gap:8px}.qty button{width:30px;height:30px;padding:0;background:#eef2f6;color:#172033}.total-row{display:flex;justify-content:space-between;align-items:center;padding:18px 0;margin-top:8px;font-size:1.06rem}.total-row strong{font-size:1.35rem}.cart-panel label{display:grid;gap:7px;margin:12px 0;color:#475467;font-size:.8rem;font-weight:700}.tender-row{display:grid;grid-template-columns:1fr auto;gap:8px}.secondary{background:#eef2f6;color:#172033}.checkout{width:100%;padding:13px;margin-top:8px}.guard-note{font-size:.72rem;color:#98a2b3;line-height:1.5}.empty{text-align:center;color:#98a2b3;padding:26px 10px}.result{max-width:1380px;margin:18px auto 0;padding:18px}.receipt-card{display:grid;gap:16px}.receipt-heading{display:flex;justify-content:space-between;gap:18px;align-items:flex-start}.receipt-heading h2{margin:4px 0 6px}.receipt-subtitle{margin:0;color:#667085;font-size:.8rem;line-height:1.5}.receipt-status{border-radius:999px;background:#ecfdf3;color:#067647;padding:7px 10px;font-size:.68rem;font-weight:900;letter-spacing:.08em}.receipt-meta,.receipt-totals{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:8px}.receipt-meta span,.receipt-totals span{background:#f8fafc;border-radius:10px;padding:10px;font-size:.76rem}.receipt-meta b,.receipt-totals b{display:block;margin-top:4px}.receipt-lines{border:1px solid #e4e8ef;border-radius:12px;overflow:hidden}.receipt-line{display:grid;grid-template-columns:minmax(0,1fr) auto auto;gap:16px;align-items:center;padding:12px 14px;border-bottom:1px solid #edf0f4}.receipt-line:last-child{border-bottom:0}.receipt-line small{display:block;margin-top:3px;color:#98a2b3}.receipt-actions{display:flex;justify-content:flex-end;gap:8px}.receipt-correlation{margin:0;color:#98a2b3;font-size:.72rem}.mono{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:.76rem}footer{max-width:1380px;margin:12px auto;color:#98a2b3;font-size:.72rem}@media(max-width:900px){.cashier-shell{padding:18px 12px}.hero{align-items:stretch;flex-direction:column}.shift-card{min-width:0}.scope-strip{grid-template-columns:1fr}.workspace-grid{grid-template-columns:1fr}.cart-panel{position:static}.receipt-meta,.receipt-totals{grid-template-columns:1fr 1fr}}@media(max-width:560px){.panel-heading,.receipt-heading{align-items:stretch;flex-direction:column}.search{width:auto}.receipt-meta,.receipt-totals{grid-template-columns:1fr}.receipt-line{grid-template-columns:1fr}.receipt-actions{justify-content:stretch}.receipt-actions button{flex:1}}@media print{body *{visibility:hidden}.receipt-card,.receipt-card *{visibility:visible}.receipt-card{position:absolute;left:0;top:0;width:100%;box-sizing:border-box;padding:24px;background:#fff}.no-print{display:none!important}}

.cashier-shell{font-family:inherit;padding:30px 28px}.hero h1{margin:6px 0}.subtitle{font-size:12px}.eyebrow{font-size:10px;color:var(--oq-brand);letter-spacing:1.5px}.shift-card{box-shadow:none;border-radius:12px;background:#fff;min-width:190px}.scope-strip{border-radius:12px;box-shadow:none;border-color:var(--oq-line);padding:15px 20px}.scope-strip span{font-size:10px;color:var(--oq-muted)}.scope-strip strong{font-size:12px}.workspace-grid{grid-template-columns:minmax(0,1fr) minmax(295px,.52fr);gap:18px}.panel{border-color:var(--oq-line);border-radius:var(--oq-radius);box-shadow:var(--oq-shadow);padding:22px}.catalog-grid{grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:13px}.product-card{padding:15px;border-color:var(--oq-line);gap:11px;border-radius:12px;min-height:228px;transition:border-color .15s}.product-card:hover{border-color:#a2cbbb}.product-visual{min-height:66px;display:grid;place-items:center;background:#edf5f1;color:#82a799;border-radius:9px}.product-name{font-size:13px;font-weight:650;line-height:1.45}.product-reference{font-size:9px;color:#8d9ca5;margin-top:4px}.product-reference small{overflow-wrap:anywhere}.product-reference summary{cursor:pointer}.product-meta strong{font-size:13px;color:var(--oq-ink)}.product-meta span{font-size:10px}.product-card button{font-size:11px;background:#e9f5ef!important;color:#246951;border:1px solid #daeee3;border-radius:7px;min-height:36px}.product-card button:not(:disabled):hover{background:#d7ece1!important}.panel-heading h2{font-size:16px;letter-spacing:-.35px}.panel-heading .eyebrow{font-size:9px}.search,input,select{font-size:12px;padding:11px 12px;border-color:var(--oq-line);border-radius:8px}.search{width:min(230px,45%)}.cart-panel{top:20px}.cart-line{font-size:12px}.cart-line small{font-size:11px}.qty button{width:34px;height:34px;border-radius:8px}.qty{font-size:12px}.total-row{border-top:1px solid var(--oq-line);font-size:12px}.total-row strong{color:var(--oq-brand);font-size:24px;letter-spacing:-.7px}.checkout{border-radius:9px;min-height:46px;font-size:12px;box-shadow:0 3px 8px #05694d12}.cart-panel label{font-size:10px;line-height:1.7}.guard-note{color:var(--oq-muted);font-size:10px}.empty{padding:42px 16px;background:#f6f8f9;border:1px dashed var(--oq-line);border-radius:12px;font-size:12px;color:var(--oq-muted);grid-column:1/-1}.result{border-color:var(--oq-line);box-shadow:var(--oq-shadow)}.receipt-actions button{font-size:12px}.receipt-correlation{overflow-wrap:anywhere}
@media(max-width:1200px){.workspace-grid{grid-template-columns:minmax(0,1fr) 295px}.catalog-grid{grid-template-columns:repeat(auto-fill,minmax(145px,1fr))}.panel{padding:17px}.product-meta{align-items:flex-start;flex-direction:column;gap:5px}.panel-heading{align-items:stretch;flex-direction:column}.search{width:100%}}@media(max-width:950px){.workspace-grid{grid-template-columns:1fr}.cart-panel{position:static}.catalog-grid{grid-template-columns:repeat(auto-fill,minmax(160px,1fr))}.panel-heading{align-items:center;flex-direction:row}.search{width:min(250px,48%)}}@media(max-width:600px){.cashier-shell{padding:22px 16px}.hero{gap:14px}.scope-strip{grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;padding:13px}.scope-strip strong{font-size:11px}.catalog-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.product-card{padding:12px;min-height:218px}.product-name{font-size:12px}.product-meta strong{font-size:12px}.product-card button{min-height:42px}.search{width:100%}.panel-heading{align-items:stretch;flex-direction:column}.qty button{width:40px;height:40px}}
</style>
