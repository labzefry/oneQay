<script setup lang="ts">
import { reactive } from 'vue';
import { Head } from '@inertiajs/vue3';
import { t } from '../../i18n';
import type { CurrencyPreferences } from '../../business-money';
import WorkspaceIcon from '../../components/WorkspaceIcon.vue';
defineProps<{reports:{key:string;title:string}[];preferences:CurrencyPreferences}>();
const today=new Date().toLocaleDateString('en-CA',{timeZone:'Asia/Jakarta'});const filter=reactive({from:today.slice(0,8)+'01',to:today,q:''});
const url=(key:string,format:string)=>`/pos/business/reports/${key}/download?${new URLSearchParams({...filter,format})}`;
</script>
<template><Head :title="t('Reports')"/><main class="merch-page"><header class="merch-heading"><div><p class="eyebrow">{{ t('Reports & insights') }}</p><h1>{{ t('Reports') }}</h1><p>{{ t('Download real PDF and Excel files for your authorized business records.') }}</p></div></header><section class="merch-panel report-filters"><label>{{ t('From') }}<input v-model="filter.from" type="date" required :max="filter.to"/></label><label>{{ t('To') }}<input v-model="filter.to" type="date" required :min="filter.from"/></label><label class="merch-search"><input v-model="filter.q" type="search" :placeholder="t('Search records')" maxlength="120"/></label></section><p class="merch-hint">{{ t('Sales and trial balance use the selected date range. Other reports export current records matching the search. Exports include all matching pages, up to 10,000 rows; narrow filters for larger reports.') }}</p><div class="reports-grid"><article v-for="r in reports" :key="r.key" class="merch-panel report-card"><span class="report-icon"><WorkspaceIcon name="chart-column" :size="26"/></span><h2>{{ t(r.title) }}</h2><p>{{ preferences.business_name }}</p><div><a class="secondary" :href="url(r.key,'pdf')">{{ t('Download PDF') }}</a><a :href="url(r.key,'xlsx')">{{ t('Download Excel') }}</a></div></article></div></main></template>
