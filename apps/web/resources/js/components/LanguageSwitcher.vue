<script setup lang="ts">
// Author by Lab | zefry.
import { locale, setLocale, t } from '../i18n';
import WorkspaceIcon from './WorkspaceIcon.vue';
function change(event: Event): void { setLocale((event.target as HTMLSelectElement).value); }
</script>
<template><label class="oq-language"><WorkspaceIcon name="globe" :size="16"/><select :value="locale" :aria-label="t('Language')" data-testid="language-switcher" @change="change"><option value="id" lang="id" aria-label="Bahasa Indonesia">ID</option><option value="en" lang="en" aria-label="English">ENG</option></select></label></template>
<style scoped>
.oq-language{display:inline-flex;align-items:center;gap:5px;border:1px solid #dce6e3;border-radius:10px;background:#fff;padding:3px 8px;color:#496963;white-space:nowrap}.oq-language select{min-height:30px;border:0;background:transparent;color:#173e37;font:650 11px system-ui;cursor:pointer}.oq-language select:focus-visible{outline:3px solid #63c5b5;outline-offset:2px}
</style>
