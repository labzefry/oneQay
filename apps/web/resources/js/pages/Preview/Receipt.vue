<script setup lang="ts">
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'

type PreviewContext = {
  label: string
  tenant_id: string
  organization_id: string
  outlet_id: string
  device_id: string
}

type ReceiptLine = {
  product_id: string
  name: string
  quantity: number
  unit_price_atomic: number
  line_total_atomic: number
}

type SaleAdjustment = {
  sale_id: string
  status: 'COMPLETED' | 'VOIDED' | 'REFUNDED'
  void_operation_id: string | null
  refund_operation_id: string | null
  refund_amount_atomic: number
  tender_category: string
  idempotent_replay: boolean
}

type Receipt = {
  sale_id: string
  operation_id: string
  tenant_id: string
  actor_id: string
  outlet_id: string
  device_id: string
  lines: ReceiptLine[]
  total_atomic: number
  currency: string
  tender_category: string
  evidence_mode: string
  change_atomic: number
  correlation_id: string
  adjustment: SaleAdjustment
}

const props = defineProps<{
  profile: PreviewContext
  receipt: Receipt
  previewLabel: string
  productionReady: boolean
}>()

// Author by Lab | zefry
const money = (value: number) => new Intl.NumberFormat('id-ID', {
  style: 'currency',
  currency: 'IDR',
  maximumFractionDigits: 0,
}).format(value)

const canVoid = computed(() => props.receipt.adjustment.status === 'COMPLETED')
const canRefund = computed(() => (
  props.receipt.adjustment.status === 'VOIDED'
  && props.receipt.tender_category === 'CASH'
))
</script>

<template>
  <main class="preview-shell">
    <section class="receipt-card">
      <p class="preview-eyebrow">{{ previewLabel }}</p>
      <h1>Receipt Preview</h1>
      <p class="muted">{{ profile.label }} · {{ receipt.tenant_id }} · {{ receipt.outlet_id }}</p>

      <div class="receipt-meta">
        <span><b>Sale</b>{{ receipt.sale_id }}</span>
        <span><b>Operation</b>{{ receipt.operation_id }}</span>
        <span><b>Actor</b>{{ receipt.actor_id }}</span>
        <span><b>Device</b>{{ receipt.device_id }}</span>
      </div>

      <div class="lines">
        <div v-for="line in receipt.lines" :key="line.product_id" class="line">
          <div>
            <strong>{{ line.name }}</strong>
            <small>{{ line.product_id }} · {{ line.quantity }} × {{ money(line.unit_price_atomic) }}</small>
          </div>
          <b>{{ money(line.line_total_atomic) }}</b>
        </div>
      </div>

      <div class="summary">
        <span>Total <strong>{{ money(receipt.total_atomic) }}</strong></span>
        <span>Tender <strong>{{ receipt.tender_category }}</strong></span>
        <span>Evidence <strong>{{ receipt.evidence_mode }}</strong></span>
        <span>Change <strong>{{ money(receipt.change_atomic) }}</strong></span>
      </div>

      <section class="adjustment" :data-status="receipt.adjustment.status">
        <div>
          <small>Sale lifecycle</small>
          <strong>{{ receipt.adjustment.status }}</strong>
        </div>
        <div v-if="receipt.adjustment.void_operation_id">
          <small>Void evidence</small>
          <code>{{ receipt.adjustment.void_operation_id }}</code>
        </div>
        <div v-if="receipt.adjustment.refund_operation_id">
          <small>Refund evidence</small>
          <code>{{ receipt.adjustment.refund_operation_id }}</code>
        </div>
        <div v-if="receipt.adjustment.refund_amount_atomic > 0">
          <small>Cash refund</small>
          <strong>{{ money(receipt.adjustment.refund_amount_atomic) }}</strong>
        </div>
      </section>

      <div class="correlation">
        <small>Safe correlation reference</small>
        <code>{{ receipt.correlation_id }}</code>
      </div>

      <aside class="warning">
        <strong>Not Production Ready</strong>
        <span>Void dan refund di halaman ini adalah synthetic Preview evidence. Void tidak mengurangi expected cash; hanya full CASH refund yang terikat ke void meng-offset cash sale. Tidak ada durable persistence, payment-provider verification, deployment, atau Production readiness.</span>
      </aside>

      <div class="actions">
        <button v-if="canVoid" type="button" @click="router.post('/technical-preview/sale/void')">Void sale synthetic</button>
        <button v-if="canRefund" type="button" @click="router.post('/technical-preview/sale/refund')">Refund CASH penuh</button>
        <button type="button" class="secondary" @click="router.get('/technical-preview/pos')">Kembali ke POS</button>
        <button type="button" class="secondary" @click="router.post('/technical-preview/logout')">Keluar</button>
      </div>
    </section>
  </main>
</template>

<style scoped>
.preview-shell { min-height: 100vh; display: grid; place-items: center; padding: 2rem; background: #0b1020; color: #eef2ff; }
.receipt-card { width: min(50rem, 100%); padding: 2rem; border: 1px solid #334155; border-radius: 1.25rem; background: #111827; }
.preview-eyebrow { margin: 0 0 .35rem; font-size: .75rem; letter-spacing: .12em; text-transform: uppercase; color: #93c5fd; }
h1 { margin: 0; }
.muted, small { color: #94a3b8; }
.receipt-meta { display: grid; grid-template-columns: 1fr 1fr; gap: .6rem; margin: 1.5rem 0; }
.receipt-meta span { display: grid; padding: .75rem; border-radius: .65rem; background: #0f172a; overflow-wrap: anywhere; }
.lines { border-top: 1px solid #334155; border-bottom: 1px solid #334155; }
.line { display: flex; justify-content: space-between; gap: 1rem; padding: 1rem 0; }
.line > div { display: grid; }
.summary { display: grid; gap: .5rem; margin: 1rem 0; }
.summary span { display: flex; justify-content: space-between; gap: 1rem; }
.adjustment { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .6rem; margin: 1rem 0; padding: .9rem; border: 1px solid #334155; border-radius: .75rem; background: #0f172a; }
.adjustment[data-status="VOIDED"] { border-color: #a16207; }
.adjustment[data-status="REFUNDED"] { border-color: #166534; }
.adjustment > div { display: grid; gap: .2rem; overflow-wrap: anywhere; }
.correlation { display: grid; gap: .3rem; padding: .8rem; border-radius: .65rem; background: #0f172a; overflow-wrap: anywhere; }
.warning { display: grid; gap: .25rem; margin-top: 1rem; padding: .9rem; border-radius: .75rem; background: #422006; color: #fde68a; }
.actions { display: flex; gap: .75rem; margin-top: 1.25rem; flex-wrap: wrap; }
button { border: 0; border-radius: .65rem; padding: .75rem 1rem; background: #dbeafe; color: #0f172a; font-weight: 700; cursor: pointer; }
button.secondary { background: #1e293b; color: #e2e8f0; border: 1px solid #475569; }
@media (max-width: 640px) { .receipt-meta, .adjustment { grid-template-columns: 1fr; } }
</style>
