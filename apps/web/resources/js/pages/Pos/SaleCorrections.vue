<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

type SaleState = 'COMPLETED' | 'VOIDED' | 'REFUNDED';
type CorrectionAction = 'void' | 'refund';
type SaleRow = {
    sale_id: string;
    completed_at_unix: number;
    amount_atomic: string;
    currency: string;
    scale: number;
    tender_category: 'CASH' | 'MANUAL_EXTERNAL';
    original_device_id: string;
    state: SaleState;
    shift_active: boolean;
    void_id: string | null;
    voided_at_unix: number | null;
    refund_id: string | null;
    refunded_at_unix: number | null;
    void_eligible: boolean;
    cash_refund_eligible: boolean;
    external_settlement_required: boolean;
};

type MutationResult = {
    status: string;
    sale_id: string;
    void_id?: string;
    refund_id?: string;
    voided_at_unix?: number;
    refunded_at_unix?: number;
};

const props = defineProps<{
    scope: { tenant_id: string; organization_id: string; outlet_id: string; device_id: string };
    permissions: { can_void: boolean; can_cash_refund: boolean };
    sales: SaleRow[];
    void_endpoint: string;
    cash_refund_endpoint: string;
    csrf_token: string;
    correlation_id: string;
}>();

const sales = ref<SaleRow[]>(props.sales.map((sale) => ({ ...sale })));
const search = ref('');
const stateFilter = ref<'ALL' | SaleState>('ALL');
const pending = ref<{ sale: SaleRow; action: CorrectionAction } | null>(null);
const submitting = ref(false);
const message = ref('');
const uncertain = ref(false);

const visibleSales = computed(() => {
    const needle = search.value.trim().toLocaleLowerCase('id-ID');
    return sales.value.filter((sale) => {
        if (stateFilter.value !== 'ALL' && sale.state !== stateFilter.value) return false;
        if (!needle) return true;
        return sale.sale_id.toLocaleLowerCase('id-ID').includes(needle)
            || sale.original_device_id.toLocaleLowerCase('id-ID').includes(needle);
    });
});

const counts = computed(() => ({
    completed: sales.value.filter((sale) => sale.state === 'COMPLETED').length,
    voided: sales.value.filter((sale) => sale.state === 'VOIDED').length,
    refunded: sales.value.filter((sale) => sale.state === 'REFUNDED').length,
}));

const formatAtomic = (atomic: string, currency: string, scale: number): string => {
    const value = BigInt(atomic);
    const base = 10n ** BigInt(scale);
    const whole = value / base;
    const fraction = value % base;
    const wholeText = new Intl.NumberFormat('id-ID').format(whole);
    if (scale === 0) return `${currency} ${wholeText}`;
    return `${currency} ${wholeText},${fraction.toString().padStart(scale, '0')}`;
};

const formatTime = (unix: number | null): string => {
    if (!unix) return '—';
    return new Intl.DateTimeFormat('id-ID', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(unix * 1000));
};

const begin = (sale: SaleRow, action: CorrectionAction): void => {
    if (submitting.value || uncertain.value) return;
    if (action === 'void' && !sale.void_eligible) return;
    if (action === 'refund' && !sale.cash_refund_eligible) return;
    message.value = '';
    pending.value = { sale, action };
};

const cancel = (): void => {
    if (!submitting.value) pending.value = null;
};

const refresh = (): void => window.location.reload();

const submitCorrection = async (): Promise<void> => {
    if (!pending.value || submitting.value || uncertain.value) return;

    const { sale, action } = pending.value;
    const endpoint = action === 'void' ? props.void_endpoint : props.cash_refund_endpoint;
    submitting.value = true;
    message.value = '';

    try {
        const response = await fetch(endpoint, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                _token: props.csrf_token,
                operation_id: `${action}:${crypto.randomUUID()}`,
                sale_id: sale.sale_id,
            }),
        });
        const payload = await response.json() as MutationResult | { error?: { code?: string } };

        if (!response.ok || !('status' in payload) || !('sale_id' in payload) || payload.sale_id !== sale.sale_id) {
            const code = 'error' in payload ? payload.error?.code : undefined;
            message.value = code ? `Koreksi ditolak server (${code}).` : 'Koreksi ditolak server.';
            return;
        }

        const index = sales.value.findIndex((candidate) => candidate.sale_id === sale.sale_id);
        if (index < 0) {
            uncertain.value = true;
            message.value = 'Respons server berhasil, tetapi state lokal tidak dapat dicocokkan. Refresh wajib sebelum tindakan berikutnya.';
            return;
        }

        const current = sales.value[index];
        if (action === 'void' && payload.status === 'voided' && payload.void_id && payload.voided_at_unix) {
            sales.value[index] = {
                ...current,
                state: 'VOIDED',
                void_id: payload.void_id,
                voided_at_unix: payload.voided_at_unix,
                void_eligible: false,
                cash_refund_eligible: props.permissions.can_cash_refund
                    && current.tender_category === 'CASH'
                    && current.shift_active,
                external_settlement_required: current.tender_category === 'MANUAL_EXTERNAL',
            };
            message.value = current.tender_category === 'CASH'
                ? 'Sale berhasil di-void. Untuk pengembalian tunai, lakukan langkah refund terpisah.'
                : 'Sale berhasil di-void. Settlement tender eksternal tetap harus diselesaikan pada kanal eksternal.';
            pending.value = null;
            return;
        }

        if (action === 'refund' && payload.status === 'cash_refund_recorded' && payload.refund_id && payload.refunded_at_unix) {
            sales.value[index] = {
                ...current,
                state: 'REFUNDED',
                refund_id: payload.refund_id,
                refunded_at_unix: payload.refunded_at_unix,
                cash_refund_eligible: false,
                external_settlement_required: false,
            };
            message.value = 'Full CASH refund berhasil dicatat.';
            pending.value = null;
            return;
        }

        uncertain.value = true;
        message.value = 'Respons server tidak sesuai kontrak koreksi yang diharapkan. Refresh wajib sebelum retry.';
    } catch {
        uncertain.value = true;
        message.value = 'Status koreksi tidak dapat dipastikan karena respons jaringan tidak diterima. Jangan retry sebelum refresh dan verifikasi state authoritative.';
    } finally {
        submitting.value = false;
    }
};
</script>

<template>
    <Head title="Sale Corrections" />
    <main class="shell">
        <header class="hero">
            <div>
                <p class="eyebrow">oneQay · Point of Sale</p>
                <h1>Sale Correction Workspace</h1>
                <p class="subtitle">Void dan full CASH refund tetap memakai mutation authority canonical, idempotent, dan scoped.</p>
            </div>
            <button type="button" class="secondary refresh" @click="refresh">Refresh authoritative state</button>
        </header>

        <section class="scope-strip">
            <div><span>Outlet</span><strong>{{ props.scope.outlet_id }}</strong></div>
            <div><span>Operator device</span><strong>{{ props.scope.device_id }}</strong></div>
            <div><span>Void permission</span><strong>{{ props.permissions.can_void ? 'ALLOWED' : 'DENIED' }}</strong></div>
            <div><span>Cash refund permission</span><strong>{{ props.permissions.can_cash_refund ? 'ALLOWED' : 'DENIED' }}</strong></div>
        </section>

        <section class="summary-grid">
            <article><span>Completed</span><strong>{{ counts.completed }}</strong></article>
            <article><span>Voided</span><strong>{{ counts.voided }}</strong></article>
            <article><span>Refunded</span><strong>{{ counts.refunded }}</strong></article>
        </section>

        <section v-if="message" class="notice" :data-uncertain="uncertain" aria-live="polite">
            <strong>{{ uncertain ? 'State verification required' : 'Workspace update' }}</strong>
            <p>{{ message }}</p>
            <button v-if="uncertain" type="button" class="secondary" @click="refresh">Refresh sekarang</button>
        </section>

        <section class="panel">
            <div class="toolbar">
                <div>
                    <p class="eyebrow">Recent scoped sales</p>
                    <h2>Koreksi transaksi</h2>
                </div>
                <div class="filters">
                    <input v-model="search" type="search" placeholder="Cari sale ID / device" autocomplete="off">
                    <select v-model="stateFilter">
                        <option value="ALL">Semua status</option>
                        <option value="COMPLETED">Completed</option>
                        <option value="VOIDED">Voided</option>
                        <option value="REFUNDED">Refunded</option>
                    </select>
                </div>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Sale</th><th>Waktu</th><th>Nilai</th><th>Tender</th><th>Original device</th><th>Shift</th><th>Status</th><th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="sale in visibleSales" :key="sale.sale_id">
                            <td><strong class="mono">{{ sale.sale_id }}</strong></td>
                            <td>{{ formatTime(sale.completed_at_unix) }}</td>
                            <td><strong>{{ formatAtomic(sale.amount_atomic, sale.currency, sale.scale) }}</strong></td>
                            <td>{{ sale.tender_category }}</td>
                            <td class="mono">{{ sale.original_device_id }}</td>
                            <td><span class="shift" :data-active="sale.shift_active">{{ sale.shift_active ? 'ACTIVE' : 'CLOSED' }}</span></td>
                            <td>
                                <span class="state" :data-state="sale.state">{{ sale.state }}</span>
                                <small v-if="sale.external_settlement_required" class="external">External settlement required</small>
                            </td>
                            <td class="actions">
                                <button v-if="sale.void_eligible" type="button" :disabled="submitting || uncertain" @click="begin(sale, 'void')">Void sale</button>
                                <button v-else-if="sale.cash_refund_eligible" type="button" :disabled="submitting || uncertain" @click="begin(sale, 'refund')">Record cash refund</button>
                                <span v-else class="muted">No action</span>
                            </td>
                        </tr>
                        <tr v-if="!visibleSales.length"><td colspan="8" class="empty">Tidak ada sale yang cocok dengan filter.</td></tr>
                    </tbody>
                </table>
            </div>
        </section>

        <div v-if="pending" class="modal-backdrop" @click.self="cancel">
            <section class="modal" role="dialog" aria-modal="true" aria-labelledby="correction-title">
                <p class="eyebrow">Irreversible operational step</p>
                <h2 id="correction-title">{{ pending.action === 'void' ? 'Konfirmasi full sale void' : 'Konfirmasi full CASH refund' }}</h2>
                <p v-if="pending.action === 'void'">Void akan membalik seluruh sale dan mengembalikan stok sesuai business authority canonical. Untuk CASH, refund tunai tetap merupakan langkah kedua yang terpisah.</p>
                <p v-else>Refund ini hanya mencatat full CASH refund setelah full sale void yang valid. Nominal berasal dari sale authoritative dan tidak dapat diubah dari UI.</p>
                <dl>
                    <div><dt>Sale</dt><dd class="mono">{{ pending.sale.sale_id }}</dd></div>
                    <div><dt>Nilai</dt><dd>{{ formatAtomic(pending.sale.amount_atomic, pending.sale.currency, pending.sale.scale) }}</dd></div>
                    <div><dt>Tender</dt><dd>{{ pending.sale.tender_category }}</dd></div>
                </dl>
                <div class="modal-actions">
                    <button type="button" class="secondary" :disabled="submitting" @click="cancel">Batal</button>
                    <button type="button" class="danger" :disabled="submitting" @click="submitCorrection">{{ submitting ? 'Memproses…' : 'Konfirmasi' }}</button>
                </div>
                <small>Tidak ada automatic retry. Bila respons jaringan tidak pasti, refresh state authoritative sebelum tindakan berikutnya.</small>
            </section>
        </div>

        <footer>Page correlation: <span class="mono">{{ props.correlation_id }}</span></footer>
    </main>
</template>

<style scoped>
.shell{min-height:100vh;background:#f5f7fa;color:#172033;padding:28px;font-family:Inter,ui-sans-serif,system-ui,sans-serif}.hero{max-width:1440px;margin:0 auto 20px;display:flex;justify-content:space-between;gap:20px;align-items:flex-end}.eyebrow{margin:0;color:#667085;font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.12em}h1{margin:5px 0 8px;font-size:clamp(2rem,4vw,3rem);letter-spacing:-.04em}.subtitle,.muted{color:#667085}.scope-strip,.summary-grid,.panel,.notice{max-width:1440px;margin-left:auto;margin-right:auto}.scope-strip{display:grid;grid-template-columns:repeat(4,1fr);gap:1px;background:#e4e7ec;border:1px solid #e4e7ec;border-radius:14px;overflow:hidden;margin-bottom:16px}.scope-strip div{background:#fff;padding:13px 16px}.scope-strip span,.summary-grid span{display:block;color:#667085;font-size:.72rem}.scope-strip strong{font-size:.86rem}.summary-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:16px}.summary-grid article,.panel,.notice{background:#fff;border:1px solid #e4e7ec;border-radius:16px;box-shadow:0 8px 24px rgba(16,24,40,.04)}.summary-grid article{padding:16px}.summary-grid strong{display:block;font-size:1.8rem;margin-top:2px}.notice{padding:16px 18px;margin-bottom:16px;border-left:4px solid #1570ef}.notice[data-uncertain="true"]{border-left-color:#b42318}.notice p{margin:5px 0 10px}.panel{padding:20px}.toolbar{display:flex;justify-content:space-between;gap:16px;align-items:center;margin-bottom:16px}.toolbar h2{margin:3px 0 0}.filters{display:flex;gap:10px}input,select{border:1px solid #d0d5dd;border-radius:10px;background:#fff;padding:10px 12px;font:inherit;color:#172033}.table-wrap{overflow:auto}table{width:100%;border-collapse:collapse;min-width:1120px}th,td{text-align:left;padding:13px 10px;border-bottom:1px solid #eaecf0;vertical-align:middle}th{font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:#667085}td{font-size:.88rem}.mono{font-family:ui-monospace,SFMono-Regular,Menlo,monospace}.state,.shift{display:inline-flex;border-radius:999px;padding:4px 8px;font-size:.68rem;font-weight:800;background:#f2f4f7;color:#344054}.state[data-state="COMPLETED"],.shift[data-active="true"]{background:#ecfdf3;color:#027a48}.state[data-state="VOIDED"]{background:#fff4ed;color:#b54708}.state[data-state="REFUNDED"]{background:#eef4ff;color:#3538cd}.shift[data-active="false"]{background:#fef3f2;color:#b42318}.external{display:block;color:#b54708;margin-top:5px}.actions button,button{border:0;border-radius:10px;padding:9px 12px;font:inherit;font-weight:750;cursor:pointer;background:#172033;color:#fff}.secondary{background:#fff;color:#344054;border:1px solid #d0d5dd}.danger{background:#b42318}button:disabled{opacity:.5;cursor:not-allowed}.empty{text-align:center;color:#667085;padding:28px}.modal-backdrop{position:fixed;inset:0;background:rgba(16,24,40,.55);display:grid;place-items:center;padding:20px;z-index:50}.modal{width:min(560px,100%);background:#fff;border-radius:18px;padding:24px;box-shadow:0 24px 64px rgba(16,24,40,.25)}.modal h2{margin:5px 0 10px}.modal p{color:#475467;line-height:1.55}.modal dl{border:1px solid #eaecf0;border-radius:12px;overflow:hidden}.modal dl div{display:flex;justify-content:space-between;gap:16px;padding:10px 12px;border-bottom:1px solid #eaecf0}.modal dl div:last-child{border-bottom:0}.modal dt{color:#667085}.modal dd{margin:0;font-weight:700}.modal-actions{display:flex;justify-content:flex-end;gap:10px;margin:18px 0 10px}footer{max-width:1440px;margin:16px auto 0;color:#667085;font-size:.75rem}@media(max-width:850px){.shell{padding:18px}.hero,.toolbar{align-items:stretch;flex-direction:column}.scope-strip{grid-template-columns:1fr 1fr}.summary-grid{grid-template-columns:1fr}.filters{flex-direction:column}.refresh{width:100%}}
</style>
