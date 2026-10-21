<script setup lang="ts">
import { onBeforeUnmount, reactive, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import { t } from '../../i18n';
import type { CurrencyPreferences } from '../../business-money';
import WorkspaceIcon from '../../components/WorkspaceIcon.vue';
import OperationFeedback from '../../components/OperationFeedback.vue';
import type { MutationStatus } from '../../use-business-mutation';
defineProps<{reports:{key:string;title:string}[];preferences:CurrencyPreferences}>();
const today=new Date().toLocaleDateString('en-CA',{timeZone:'Asia/Jakarta'});const filter=reactive({from:today.slice(0,8)+'01',to:today,q:''});
const url=(key:string,format:string)=>`/pos/business/reports/${key}/download?${new URLSearchParams({...filter,format})}`;
const exportState = ref<MutationStatus>('idle'); const exportNotice = ref('');
const exporting = ref(false); let controller: AbortController | null = null;
onBeforeUnmount(() => controller?.abort());
async function download(key: string, format: 'pdf' | 'xlsx'): Promise<void> {
  if (exporting.value) return;
  exporting.value = true; exportState.value = 'running'; exportNotice.value = '';
  const active = new AbortController(); controller = active;
  const timer = window.setTimeout(() => active.abort(), 30000);
  try {
    if (!navigator.onLine) throw new Error('OFFLINE');
    if (!/^[a-z][a-z0-9_-]{0,63}$/i.test(key)) throw new Error('INVALID_REPORT');
    const response = await fetch(url(key, format), { method:'GET', credentials:'same-origin', redirect:'error', signal:active.signal, headers:{ Accept:format==='pdf'?'application/pdf':'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' } });
    if (active.signal.aborted) throw new Error('DOWNLOAD_TIMEOUT');
    if (response.status===401||response.status===419) {
      exportState.value = 'session-expired'; window.location.assign('/business-login?reason=session-expired'); return;
    }
    if (response.status===202) { exportState.value = 'queued'; exportNotice.value = t('Report generation is queued, not completed. Check export status before downloading.'); return; }
    if (!response.ok) throw new Error('DOWNLOAD_REJECTED');
    const blob = await response.blob();
    if (active.signal.aborted) throw new Error('DOWNLOAD_TIMEOUT');
    const expected = format==='pdf'?'application/pdf':'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    if (!blob.size || (!blob.type.includes(expected) && !response.headers.get('Content-Type')?.includes(expected))) throw new Error('INVALID_REPORT_RESPONSE');
    const link = document.createElement('a');
    const objectUrl = URL.createObjectURL(blob);
    try {
      link.href = objectUrl;
      link.download = `oneqay-${key}-${filter.from}-to-${filter.to}.${format}`;
      document.body.appendChild(link); link.click();
      exportState.value = 'succeeded';
      exportNotice.value = t('Browser download initiated. Check the browser for the actual saved file.');
    } finally {
      link.remove();
      window.setTimeout(() => URL.revokeObjectURL(objectUrl), 60000);
    }
  } catch {
    exportState.value = 'failed';
    exportNotice.value = t('Report download was not confirmed. Check connectivity and request again if required.');
  } finally {
    window.clearTimeout(timer); if (controller===active) controller=null; exporting.value=false;
  }
}
</script>
<template><Head :title="t('Reports')"/><main class="merch-page"><OperationFeedback :status="exportState" :message="exportNotice" :success-label="t('Download initiated')" :running-label="t('Preparing report')"/><header class="merch-heading"><div><p class="eyebrow">{{ t('Reports & insights') }}</p><h1>{{ t('Reports') }}</h1><p>{{ t('Download real PDF and Excel files for your authorized business records.') }}</p></div></header><section class="merch-panel report-filters"><label>{{ t('From') }}<input v-model="filter.from" type="date" required :max="filter.to"/></label><label>{{ t('To') }}<input v-model="filter.to" type="date" required :min="filter.from"/></label><label class="merch-search"><input v-model="filter.q" type="search" :placeholder="t('Search records')" maxlength="120"/></label></section><p class="merch-hint">{{ t('Sales and trial balance use the selected date range. Other reports export current records matching the search. Exports include all matching pages, up to 10,000 rows; narrow filters for larger reports.') }}</p><div class="reports-grid"><article v-for="r in reports" :key="r.key" class="merch-panel report-card"><span class="report-icon"><WorkspaceIcon name="chart-column" :size="26"/></span><h2>{{ t(r.title) }}</h2><p>{{ preferences.business_name }}</p><div><a class="secondary" :href="url(r.key,'pdf')" :aria-disabled="exporting" @click.prevent="download(r.key,'pdf')">{{ t('Download PDF') }}</a><a :href="url(r.key,'xlsx')" :aria-disabled="exporting" @click.prevent="download(r.key,'xlsx')">{{ t('Download Excel') }}</a></div></article></div></main></template>
