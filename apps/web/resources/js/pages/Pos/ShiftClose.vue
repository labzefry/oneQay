<script setup lang="ts">
import axios, { AxiosError } from 'axios'
import { computed, ref } from 'vue'

type DeliveryBoundary = {
  post_url: string
  operation_id_prefix: string
  production_ready: false
  activation_state: 'DORMANT_FAIL_CLOSED'
}

type CloseResult = {
  status: 'closed'
  evidence_id: string
  operation_id: string
  shift_id: string
  reconciliation: {
    expected_cash_atomic: number
    observed_closing_cash_atomic: number
    variance_atomic: number
    variance_direction: 'MATCH' | 'OVER' | 'SHORT'
    currency: string
    currency_scale: number
    review_outcome: string | null
  }
  cutoff_at_unix: number
  closed_at_unix: number
  correlation_id: string
}

// Author by Lab | zefry
const props = defineProps<{ delivery: DeliveryBoundary }>()
const operationId = ref<string | null>(null)
const processing = ref(false)
const result = ref<CloseResult | null>(null)
const errorCode = ref<string | null>(null)

const operation = computed(() => {
  if (operationId.value === null) {
    operationId.value = `${props.delivery.operation_id_prefix}${crypto.randomUUID()}`
  }
  return operationId.value
})

const money = (atomic: number, currency: string): string => new Intl.NumberFormat('id-ID', {
  style: 'currency',
  currency,
  maximumFractionDigits: 0,
}).format(atomic)

const closeShift = async (): Promise<void> => {
  if (processing.value || result.value !== null) return

  processing.value = true
  errorCode.value = null

  try {
    const response = await axios.post<CloseResult>(props.delivery.post_url, {
      operation_id: operation.value,
    }, {
      headers: { Accept: 'application/json' },
    })
    result.value = response.data
  } catch (error) {
    if (error instanceof AxiosError) {
      const code = error.response?.data?.error?.code
      errorCode.value = typeof code === 'string' ? code : 'POS_SHIFT_CLOSE_REJECTED'
    } else {
      errorCode.value = 'POS_SHIFT_CLOSE_REJECTED'
    }
  } finally {
    processing.value = false
  }
}
</script>

<template>
  <main class="shift-close-page">
    <div class="shell">
      <header class="topbar">
        <div>
          <p class="brand">oneQay</p>
          <p class="muted">Final Shift Close</p>
        </div>
        <span class="state">{{ delivery.activation_state }}</span>
      </header>

      <section class="hero">
        <p class="eyebrow">Privileged cash-control action</p>
        <h1>Tutup shift final</h1>
        <p>
          Final Shift Close memakai state kas, reconciliation, review evidence, actor separation,
          dan timestamp yang diturunkan server. UI ini tidak dapat mengirim nominal, variance,
          reviewer, actor identity, shift ID, atau close timestamp.
        </p>
      </section>

      <section class="panel warning">
        <strong>Aksi irreversible secara lifecycle</strong>
        <p>
          Setelah close berhasil, active slot shift dilepas secara atomik. Gunakan tombol ini hanya
          setelah closing-cash evidence dan review variance—jika diperlukan—sudah selesai.
        </p>
      </section>

      <section v-if="result === null" class="panel action-panel">
        <div>
          <p class="eyebrow">Stable operation</p>
          <code>{{ operation }}</code>
          <p class="muted">
            Operation ID dipertahankan untuk retry yang identik. Retry tidak membuat close mutation kedua.
          </p>
        </div>
        <button type="button" :disabled="processing" @click="closeShift">
          {{ processing ? 'Memproses close…' : 'Konfirmasi Final Shift Close' }}
        </button>
      </section>

      <section v-if="errorCode" class="panel error" role="alert">
        <strong>Final Shift Close ditolak aman</strong>
        <p>{{ errorCode }}</p>
        <p class="muted">Periksa prerequisite, authorization, reconciliation, dan separation-of-duties sebelum retry.</p>
      </section>

      <section v-if="result" class="panel success" aria-live="polite">
        <p class="eyebrow">Durable close evidence</p>
        <h2>Shift berhasil ditutup</h2>
        <dl>
          <div><dt>Evidence</dt><dd><code>{{ result.evidence_id }}</code></dd></div>
          <div><dt>Shift</dt><dd><code>{{ result.shift_id }}</code></dd></div>
          <div><dt>Variance</dt><dd>{{ result.reconciliation.variance_direction }} · {{ money(result.reconciliation.variance_atomic, result.reconciliation.currency) }}</dd></div>
          <div><dt>Expected cash</dt><dd>{{ money(result.reconciliation.expected_cash_atomic, result.reconciliation.currency) }}</dd></div>
          <div><dt>Observed cash</dt><dd>{{ money(result.reconciliation.observed_closing_cash_atomic, result.reconciliation.currency) }}</dd></div>
          <div><dt>Review outcome</dt><dd>{{ result.reconciliation.review_outcome ?? 'Not required' }}</dd></div>
        </dl>
      </section>

      <footer>
        <span>Production ready: {{ delivery.production_ready ? 'yes' : 'no' }}</span>
        <span>Author by Lab | zefry</span>
      </footer>
    </div>
  </main>
</template>

<style scoped>
.shift-close-page { min-height: 100vh; background: #07111f; color: #e8eef7; padding: 1.5rem; }
.shell { width: min(64rem, 100%); margin: 0 auto; }
.topbar, .action-panel, footer { display: flex; justify-content: space-between; align-items: center; gap: 1rem; }
.topbar { padding: 1rem 0 2rem; }
.brand { font-size: 1.25rem; font-weight: 800; margin: 0; }
.muted { color: #94a3b8; }
.state { border: 1px solid #334155; border-radius: 999px; padding: .4rem .7rem; font-size: .75rem; }
.hero { padding: 2rem 0; max-width: 48rem; }
.hero h1 { font-size: clamp(2rem, 5vw, 3.5rem); margin: .25rem 0 1rem; }
.eyebrow { color: #7dd3fc; text-transform: uppercase; letter-spacing: .12em; font-size: .75rem; font-weight: 800; }
.panel { background: #0f1c2d; border: 1px solid #22344c; border-radius: 1rem; padding: 1.25rem; margin: 1rem 0; }
.warning { border-color: #7c5d22; }
.error { border-color: #7f1d1d; }
.success { border-color: #166534; }
button { border: 0; border-radius: .75rem; padding: .85rem 1.1rem; font-weight: 800; cursor: pointer; }
button:disabled { cursor: not-allowed; opacity: .6; }
code { word-break: break-all; }
dl { display: grid; grid-template-columns: repeat(auto-fit, minmax(14rem, 1fr)); gap: .75rem; }
dl div { background: #0a1524; border-radius: .75rem; padding: .8rem; }
dt { color: #94a3b8; font-size: .75rem; text-transform: uppercase; }
dd { margin: .25rem 0 0; font-weight: 700; }
footer { color: #64748b; padding: 2rem 0; font-size: .8rem; }
@media (max-width: 640px) { .action-panel, footer { align-items: stretch; flex-direction: column; } button { width: 100%; } }
</style>
