<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'

type PreviewProfile = {
  principal_id: string
  label: string
}

const props = defineProps<{
  profiles: PreviewProfile[]
  previewLabel: string
  productionReady: boolean
}>()

// Author by Lab | zefry
const form = useForm({
  principal: props.profiles[0]?.principal_id ?? '',
})

const submit = () => {
  form.post('/technical-preview/sign-in')
}
</script>

<template>
  <main class="preview-shell">
    <section class="preview-card">
      <p class="preview-eyebrow">{{ previewLabel }}</p>
      <h1>Masuk ke oneQay Technical Preview</h1>
      <p class="preview-copy">
        Pilih identitas demo yang sudah di-allowlist server. Ini bukan autentikasi Production dan tidak menggunakan data pengguna nyata.
      </p>

      <form class="preview-form" @submit.prevent="submit">
        <label v-for="profile in profiles" :key="profile.principal_id" class="profile-option">
          <input v-model="form.principal" type="radio" name="principal" :value="profile.principal_id" />
          <span>
            <strong>{{ profile.label }}</strong>
            <small>{{ profile.principal_id }}</small>
          </span>
        </label>

        <p v-if="form.errors.principal" class="preview-error">{{ form.errors.principal }}</p>

        <button type="submit" :disabled="form.processing || !form.principal">
          Lanjutkan dengan identitas synthetic
        </button>
      </form>

      <aside class="preview-warning">
        <strong>Not Production Ready</strong>
        <span>Hanya Local/Test/CI/explicit Preview. Tidak ada credential, payment provider, atau data Production.</span>
      </aside>
    </section>
  </main>
</template>

<style scoped>
.preview-shell { min-height: 100vh; display: grid; place-items: center; padding: 2rem; background: #0b1020; color: #eef2ff; }
.preview-card { width: min(44rem, 100%); padding: 2rem; border: 1px solid #334155; border-radius: 1.25rem; background: #111827; box-shadow: 0 24px 80px rgb(0 0 0 / 35%); }
.preview-eyebrow { margin: 0 0 .5rem; font-size: .78rem; letter-spacing: .12em; text-transform: uppercase; color: #93c5fd; }
h1 { margin: 0; font-size: clamp(1.8rem, 5vw, 2.8rem); }
.preview-copy { color: #cbd5e1; }
.preview-form { display: grid; gap: .8rem; margin-top: 1.5rem; }
.profile-option { display: flex; gap: .8rem; align-items: center; padding: 1rem; border: 1px solid #334155; border-radius: .9rem; cursor: pointer; }
.profile-option:has(input:checked) { border-color: #60a5fa; background: #172554; }
.profile-option span { display: grid; }
.profile-option small { color: #94a3b8; }
button { margin-top: .4rem; border: 0; border-radius: .8rem; padding: .9rem 1rem; font-weight: 700; background: #dbeafe; color: #0f172a; cursor: pointer; }
button:disabled { opacity: .55; cursor: not-allowed; }
.preview-error { margin: 0; color: #fca5a5; }
.preview-warning { display: grid; gap: .25rem; margin-top: 1.5rem; padding: 1rem; border-radius: .8rem; background: #422006; color: #fde68a; }
</style>
