<script setup lang="ts">
// Author by Lab | zefry.
import { locale, setLocale, t } from '../i18n';
function change(event: Event): void { setLocale((event.target as HTMLSelectElement).value); }
</script>
<template>
  <label class="oq-language">
    <span>{{ t('Language') }}</span>
    <select :value="locale" :aria-label="t('Language')" data-testid="language-switcher" @change="change">
      <option value="id" lang="id">Bahasa Indonesia</option>
      <option value="en" lang="en">English</option>
    </select>
  </label>
</template>
<style scoped>
.oq-language{display:flex;align-items:center;gap:8px;color:#52656f;font-size:11px;white-space:nowrap}.oq-language select{max-width:152px;min-height:36px;border:1px solid #d6e1e5;border-radius:8px;background:#fff;color:#173c43;padding:6px 8px;font:inherit;font-weight:650;cursor:pointer}.oq-language select:focus-visible{outline:3px solid #63c5b5;outline-offset:2px}@media(max-width:600px){.oq-language>span{position:absolute;width:1px;height:1px;overflow:hidden;clip-path:inset(50%)}.oq-language select{max-width:160px}}
</style>
