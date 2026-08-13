<script setup lang="ts">
import { computed, reactive, ref } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

type PreviewContext = {
  label: string
  tenant_id: string
  organization_id: string
  outlet_id: string
  device_id: string
}

type CatalogItem = {
  product_id: string
  name: string
  unit_price_atomic: number
  currency: string
}

const props = defineProps<{
  profile: PreviewContext
  catalog: CatalogItem[]
  previewLabel: string
  productionReady: boolean
}>()

// Author by Lab | zefry
const quantities = reactive<Record<string, number>>({})
const tenderCategory = ref<'CASH' | 'MANUAL_EXTERNAL'>('CASH')
const tenderedAtomicUnits = ref(0)
const processing = ref(false)
const operationId = `preview-op-${crypto.randomUUID()}`
const page = usePage()

const money = (value: number) => new Intl.NumberFormat('id-ID', {
  style: 'currency',
  currency: 'IDR',
  maximumFractionDigits: 0,
}).format(value)

const cartLines = computed(() => props.catalog
  .map((item) => ({ product_id: item.product_id, quantity: quantities[item.product_id] ?? 0 }))
  .filter((line) => line.quantity > 0))

const total = computed(() => props.catalog.reduce((sum, item) => (
  sum + item.unit_price_atomic * (quantities[item.product_id] ?? 0)
), 0))

const saleError = computed(() => {
  const errors = (page.props.errors ?? {}) as Record<string, string>
  return errors.sale ?? ''
})

const add = (productId: string) => {
  quantities[productId] = Math.min(99, (quantities[productId] ?? 0) + 1)
  if (tenderCategory.value === 'CASH') tenderedAtomicUnits.value = total.value
}

const remove = (productId: string) => {
  quantities[productId] = Math.max(0, (quantities[productId] ?? 0) - 1)
  if (tenderCategory.value === 'CASH') tenderedAtomicUnits.value = total.value
}

const submit = () => {
  if (cartLines.value.length === 0 || processing.value) return
  processing.value = true
  router.post('/technical-preview/sale', {
    lines: cartLines.value,
    tender_category: tenderCategory.value,
    tendered_atomic_units: tenderedAtomicUnits.value,
    operation_id: operationId,
  }, {
    preserveScroll: true,
    onFinish: () => { processing.value = false },
  })
}
</script>

<template>
  <main class="preview-shell">
    <header class="preview-topbar">
      <div>
        <p class="preview-eyebrow">{{ previewLabel }}</p>
        <h1>Kasir Synthetic</h1>
        <p>{{ profile.label }} · {{ profile.tenant_id }} · {{ profile.outlet_id }}</p>
      </div>
      <button class="secondary" type="button" @click="router.post('/technical-preview/logout')">Keluar</button>
    </header>

    <section class="preview-grid">
      <div class="panel">
        <h2>Katalog</h2>
        <article v-for="item in catalog" :key="item.product_id" class="catalog-item">
          <div>
            <strong>{{ item.name }}</strong>
            <small>{{ item.product_id }}</small>
            <span>{{ money(item.unit_price_atomic) }}</span>
          </div>
          <div class="qty-controls">
            <button type="button" class="secondary" @click="remove(item.product_id)">−</button>
            <b>{{ quantities[item.product_id] ?? 0 }}</b>
            <button type="button" @click="add(item.product_id)">+</button>
          </div>
        </article>
      </div>

      <aside class="panel checkout">
        <div>
          <p class="preview-eyebrow">Server-authoritative total</p>
          <strong class="total">{{ money(total) }}</strong>
          <p class="muted">Server M7.4 menghitung ulang harga dan total dari catalog fixture terverifikasi.</p>
        </div>

        <label>
          Tender
          <select v-model="tenderCategory" @change="tenderedAtomicUnits = total">
            <option value="CASH">Cash</option>
            <option value="MANUAL_EXTERNAL">Manual external (operator recorded)</option>
          </select>
        </label>

        <label>
          Nominal diterima (IDR)
          <input v-model.number="tenderedAtomicUnits" type="number" min="0" step="1" />
        </label>

        <p v-if="saleError" class="preview-error">{{ saleError }}</p>
        <button type="button" :disabled="processing || cartLines.length === 0" @click="submit">
          {{ processing ? 'Memproses synthetic sale…' : 'Selesaikan transaksi' }}
        </button>

        <div class="warning">
          <strong>Not Production Ready</strong>
          <span>Tidak ada payment provider atau transaksi uang nyata. Manual external tidak pernah dianggap provider verified.</span>
        </div>
      </aside>
    </section>
  </main>
</template>

<style scoped>
.preview-shell { min-height: 100vh; padding: 1.5rem; background: #0b1020; color: #eef2ff; }
.preview-topbar { max-width: 76rem; margin: 0 auto 1.25rem; display: flex; justify-content: space-between; gap: 1rem; align-items: center; }
.preview-topbar h1 { margin: 0; }
.preview-topbar p { margin: .25rem 0; color: #cbd5e1; }
.preview-eyebrow { margin: 0 0 .25rem; font-size: .75rem; letter-spacing: .12em; text-transform: uppercase; color: #93c5fd; }
.preview-grid { max-width: 76rem; margin: 0 auto; display: grid; grid-template-columns: minmax(0, 1.6fr) minmax(18rem, .8fr); gap: 1rem; }
.panel { padding: 1.25rem; border: 1px solid #334155; border-radius: 1rem; background: #111827; }
.catalog-item { display: flex; justify-content: space-between; gap: 1rem; align-items: center; padding: 1rem 0; border-bottom: 1px solid #1f2937; }
.catalog-item > div:first-child { display: grid; gap: .2rem; }
.catalog-item small, .muted { color: #94a3b8; }
.qty-controls { display: flex; align-items: center; gap: .65rem; }
button { border: 0; border-radius: .65rem; padding: .7rem .9rem; background: #dbeafe; color: #0f172a; font-weight: 700; cursor: pointer; }
button.secondary { background: #1e293b; color: #e2e8f0; border: 1px solid #475569; }
button:disabled { opacity: .5; cursor: not-allowed; }
.checkout { display: grid; gap: 1rem; align-content: start; }
.total { display: block; font-size: 2rem; }
label { display: grid; gap: .4rem; color: #cbd5e1; }
input, select { width: 100%; border: 1px solid #475569; border-radius: .65rem; padding: .75rem; background: #0f172a; color: #f8fafc; }
.preview-error { color: #fca5a5; }
.warning { display: grid; gap: .25rem; padding: .9rem; border-radius: .75rem; background: #422006; color: #fde68a; }
@media (max-width: 780px) { .preview-grid { grid-template-columns: 1fr; } .preview-topbar { align-items: flex-start; } }
</style>
