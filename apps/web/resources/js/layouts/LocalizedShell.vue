<script setup lang="ts">
// Author by Lab | zefry.
import LanguageSwitcher from '../components/LanguageSwitcher.vue';
</script>
<template>
  <div class="oq-localized-shell">
    <header class="oq-language-bar no-print"><span>oneQay</span><LanguageSwitcher /></header>
    <slot />
  </div>
</template>
<style scoped>
.oq-language-bar{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:12px 24px;background:#fff;border-bottom:1px solid #dce6ea;color:#163a42;font:700 14px system-ui,sans-serif}.oq-language-bar>span{letter-spacing:-.04em}@media(max-width:600px){.oq-language-bar{padding:10px 16px}}
</style>
