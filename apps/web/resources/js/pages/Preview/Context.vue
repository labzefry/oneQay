<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'

type PreviewContext = {
  label: string
  tenant_id: string
  organization_id: string
  outlet_id: string
  device_id: string
}

const props = defineProps<{
  profile: PreviewContext
  previewLabel: string
}>()

// Author by Lab | zefry
const form = useForm({ selection: 'primary' })
const submit = () => form.post('/technical-preview/context')
</script>

<template>
  <main class="preview-shell">
    <section class="preview-card">
      <p class="preview-eyebrow">{{ previewLabel }}</p>
      <h1>Pilih konteks kerja</h1>
      <p class="preview-copy">
        oneQay menampilkan konteks synthetic yang tersedia untuk {{ props.profile.label }}. Nilai tenant/outlet di bawah ini hanya tampilan; server tetap memverifikasi hubungan identitas sebelum context digunakan.
      </p>

      <form class="context-form" @submit.prevent="submit">
        <button type="submit" :disabled="form.processing">
          <strong>{{ profile.tenant_id }}</strong>
          <span>{{ profile.organization_id }} · {{ profile.outlet_id }} · {{ profile.device_id }}</span>
          <small>Gunakan konteks synthetic ini</small>
        </button>
        <p v-if="form.errors.selection" class="preview-error">{{ form.errors.selection }}</p>
      </form>

      <a href="/technical-preview" class="back-link">Kembali ke pemilihan identitas</a>
    </section>
  </main>
</template>

<style scoped>
.preview-shell { min-height: 100vh; display: grid; place-items: center; padding: 2rem; background: #0b1020; color: #eef2ff; }
.preview-card { width: min(48rem, 100%); padding: 2rem; border: 1px solid #334155; border-radius: 1.25rem; background: #111827; }
.preview-eyebrow { margin: 0 0 .5rem; font-size: .78rem; letter-spacing: .12em; text-transform: uppercase; color: #93c5fd; }
h1 { margin: 0; font-size: clamp(1.8rem, 5vw, 2.6rem); }
.preview-copy { color: #cbd5e1; }
.context-form { display: grid; gap: .8rem; margin: 1.5rem 0; }
button { display: grid; gap: .35rem; width: 100%; text-align: left; padding: 1.25rem; border: 1px solid #60a5fa; border-radius: 1rem; background: #172554; color: #eff6ff; cursor: pointer; }
button span, button small { color: #bfdbfe; }
button:disabled { opacity: .55; cursor: not-allowed; }
.preview-error { color: #fca5a5; }
.back-link { color: #93c5fd; }
</style>
