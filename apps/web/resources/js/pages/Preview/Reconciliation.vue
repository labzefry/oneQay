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

type Reconciliation = {
  tenant_id: string
  organization_id: string
  outlet_id: string
  device_id: string
  shift_id: string
  opening_cash_evidence_id: string
  closing_cash_evidence_id: string
  opening_cash_atomic: number
  cash_sales_atomic: number
  cash_refunds_atomic: number
  sale_count: number
  void_count: number
  refund_count: number
  expected_cash_atomic: number
  observed_closing_atomic: number
  variance_atomic: number
  variance_direction: 'MATCH' | 'OVER' | 'SHORT'
  currency: string
  cutoff_at_unix: number
}

const props = defineProps<{
  profile: PreviewContext
  reconciliation: Reconciliation
  previewLabel: string
  productionReady: boolean
}>()

// Author by Lab | zefry
const money = (value: number) => new Intl.NumberFormat('id-ID', {
  style: 'currency',
  currency: 'IDR',
  maximumFractionDigits: 0,
}).format(value)

const varianceMagnitude = computed(() => Math.abs(props.reconciliation.variance_atomic))
const outcomeLabel = computed(() => {
  if (props.reconciliation.variance_direction === 'MATCH') return 'Kas sesuai'
  if (props.reconciliation.variance_direction === 'OVER') return 'Kas lebih'
  return 'Kas kurang'
})
</script>

<template>
  <main class="preview-shell">
    <section class="reconciliation-card">
      <header>
        <p class="preview-eyebrow">{{ previewLabel }} · Langkah 3</p>
        <h1>Rekonsiliasi Kas Synthetic</h1>
        <p class="muted">{{ profile.label }} · {{ reconciliation.tenant_id }} · {{ reconciliation.outlet_id }}</p>
      </header>

      <div class="outcome" :data-direction="reconciliation.variance_direction">
        <span>{{ reconciliation.variance_direction }}</span>
        <strong>{{ outcomeLabel }}</strong>
        <b>{{ money(varianceMagnitude) }}</b>
      </div>

      <section class="equation">
        <article>
          <small>Opening cash</small>
          <strong>{{ money(reconciliation.opening_cash_atomic) }}</strong>
        </article>
        <span>+</span>
        <article>
          <small>Cash sales · {{ reconciliation.sale_count }} transaksi</small>
          <strong>{{ money(reconciliation.cash_sales_atomic) }}</strong>
        </article>
        <span>−</span>
        <article class="refunds">
          <small>Cash refunds · {{ reconciliation.refund_count }} refund</small>
          <strong>{{ money(reconciliation.cash_refunds_atomic) }}</strong>
        </article>
        <span>=</span>
        <article class="expected">
          <small>Expected cash · server-derived</small>
          <strong>{{ money(reconciliation.expected_cash_atomic) }}</strong>
        </article>
      </section>

      <section class="comparison">
        <span>Void evidence <strong>{{ reconciliation.void_count }}</strong></span>
        <span>Cash refund evidence <strong>{{ reconciliation.refund_count }}</strong></span>
        <span>Expected cash <strong>{{ money(reconciliation.expected_cash_atomic) }}</strong></span>
        <span>Observed closing cash <strong>{{ money(reconciliation.observed_closing_atomic) }}</strong></span>
        <span>Variance <strong>{{ money(reconciliation.variance_atomic) }}</strong></span>
      </section>

      <section class="evidence">
        <span><small>Shift</small><code>{{ reconciliation.shift_id }}</code></span>
        <span><small>Opening evidence</small><code>{{ reconciliation.opening_cash_evidence_id }}</code></span>
        <span><small>Closing evidence</small><code>{{ reconciliation.closing_cash_evidence_id }}</code></span>
        <span><small>Cutoff unix</small><code>{{ reconciliation.cutoff_at_unix }}</code></span>
      </section>

      <aside class="warning">
        <strong>Not Production Ready</strong>
        <span>Expected cash mengikuti lifecycle canonical: void tidak otomatis mengurangi kas; full CASH refund yang terikat ke void menjadi pengurang eksplisit. State shift ini tetap Synthetic Technical Preview session evidence. Tidak ada migration execution, durable production ledger, deployment, atau Production activation.</span>
      </aside>

      <div class="actions">
        <button type="button" @click="router.get('/technical-preview/pos')">Buka shift synthetic baru</button>
        <button type="button" class="secondary" @click="router.post('/technical-preview/logout')">Keluar</button>
      </div>
    </section>
  </main>
</template>

<style scoped>
.preview-shell { min-height: 100vh; display: grid; place-items: center; padding: 2rem; background: #0b1020; color: #eef2ff; }
.reconciliation-card { width: min(68rem, 100%); padding: 2rem; border: 1px solid #334155; border-radius: 1.25rem; background: #111827; }
.preview-eyebrow { margin: 0 0 .35rem; font-size: .75rem; letter-spacing: .12em; text-transform: uppercase; color: #93c5fd; }
h1 { margin: 0; }
.muted, small { color: #94a3b8; }
.outcome { display: grid; grid-template-columns: auto 1fr auto; gap: .8rem; align-items: center; margin: 1.5rem 0; padding: 1rem; border-radius: .8rem; background: #0f172a; }
.outcome > span { padding: .3rem .5rem; border-radius: .45rem; background: #1e293b; font-size: .75rem; font-weight: 800; letter-spacing: .08em; }
.outcome > b { font-size: 1.35rem; }
.outcome[data-direction="MATCH"] { border: 1px solid #166534; }
.outcome[data-direction="OVER"] { border: 1px solid #0369a1; }
.outcome[data-direction="SHORT"] { border: 1px solid #b91c1c; }
.equation { display: grid; grid-template-columns: 1fr auto 1fr auto 1fr auto 1fr; gap: .75rem; align-items: center; }
.equation article { display: grid; gap: .3rem; min-height: 4.6rem; padding: .85rem; border-radius: .7rem; background: #0f172a; }
.equation .refunds { background: #3f1d24; }
.equation .expected { background: #082f49; }
.comparison { display: grid; gap: .6rem; margin: 1.25rem 0; }
.comparison span { display: flex; justify-content: space-between; gap: 1rem; padding-bottom: .55rem; border-bottom: 1px solid #1f2937; }
.evidence { display: grid; grid-template-columns: 1fr 1fr; gap: .6rem; }
.evidence span { display: grid; gap: .2rem; padding: .75rem; border-radius: .65rem; background: #0f172a; overflow-wrap: anywhere; }
.warning { display: grid; gap: .25rem; margin-top: 1rem; padding: .9rem; border-radius: .75rem; background: #422006; color: #fde68a; }
.actions { display: flex; gap: .75rem; margin-top: 1.25rem; flex-wrap: wrap; }
button { border: 0; border-radius: .65rem; padding: .75rem 1rem; background: #dbeafe; color: #0f172a; font-weight: 700; cursor: pointer; }
button.secondary { background: #1e293b; color: #e2e8f0; border: 1px solid #475569; }
@media (max-width: 900px) { .equation { grid-template-columns: 1fr; } .equation > span { display: none; } .evidence { grid-template-columns: 1fr; } .outcome { grid-template-columns: 1fr; } }
</style>
