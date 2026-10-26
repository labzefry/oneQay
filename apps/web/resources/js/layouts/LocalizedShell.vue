<script setup lang="ts">
// Author by Lab | zefry.
import {usePage} from '@inertiajs/vue3';
const page=usePage();
import LanguageSwitcher from '../components/LanguageSwitcher.vue';
</script>
<template>
  <div class="oq-localized-shell">
    <header class="oq-language-bar no-print"><span><img v-if="(page.props.branding as any)?.logo" :src="(page.props.branding as any).logo" alt="oneQay" style="max-width:160px;max-height:44px;object-fit:contain"/><template v-else>oneQay</template></span><LanguageSwitcher /></header>
    <slot />
  </div>
</template>
<style scoped>
.oq-language-bar{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:12px 24px;background:var(--oq-white);border-bottom:1px solid var(--oq-light);color:var(--oq-navy);font:700 1rem/1.5 system-ui,sans-serif}
.oq-language-bar>span{letter-spacing:-.03em}
@media(max-width:600px){.oq-language-bar{padding:12px 16px}}
</style>
