<script setup lang="ts">
// Author by Lab | zefry. Nonmodal operation feedback: no fabricated percentages or automatic write retries.
import { onBeforeUnmount, ref, watch } from 'vue';
import { locale } from '../i18n';
import WorkspaceIcon from './WorkspaceIcon.vue';
import type { MutationStatus } from '../use-business-mutation';
const props = defineProps<{ status: MutationStatus; message?: string; operationId?: string | null; successLabel?: string; runningLabel?: string }>();
const visible = ref(false);
let timer: ReturnType<typeof setTimeout> | null = null;
let dismissal: ReturnType<typeof setTimeout> | null = null;
watch(() => props.status, next => {
    if (timer !== null) clearTimeout(timer);
    if (dismissal !== null) clearTimeout(dismissal);
    if (next === 'idle') { visible.value = false; return; }
    if (next === 'running') {
        visible.value = false;
        timer = setTimeout(() => { visible.value = true; timer = null; }, 250);
    } else {
        visible.value = true;
        if (next === 'succeeded' || next === 'failed') dismissal = setTimeout(() => { visible.value = false; dismissal = null; }, 6500);
    }
}, { immediate: true });
onBeforeUnmount(() => { if (timer !== null) clearTimeout(timer); if (dismissal !== null) clearTimeout(dismissal); });
const caption = (id: string, en: string) => locale.value === 'id' ? id : en;
</script>
<template>
    <aside v-if="visible" class="oq-operation-feedback" :data-state="status" :role="status === 'failed' || status === 'session-expired' ? 'alert' : 'status'" aria-live="polite" :aria-busy="status === 'running'">
        <button type="button" class="oq-operation-dismiss" :aria-label="caption('Tutup pemberitahuan', 'Dismiss notification')" @click="visible = false"><WorkspaceIcon name="x" :size="16"/></button>
        <strong>
            <span v-if="status === 'running'" class="oq-operation-spinner" aria-hidden="true"/>
            <WorkspaceIcon v-else :name="status === 'succeeded' ? 'check' : status === 'queued' ? 'clock-3' : 'shield-check'" :size="19"/>
            {{ status === 'running' ? (runningLabel ?? caption('Memproses permintaan', 'Processing request'))
                : status === 'succeeded' ? (successLabel ?? caption('Perubahan tersimpan', 'Change saved'))
                : status === 'queued' ? caption('Dalam antrean — belum selesai', 'Queued — not completed')
                : status === 'unknown' ? caption('Hasil belum dapat dipastikan', 'Outcome not confirmed')
                : status === 'session-expired' ? caption('Sesi telah berakhir', 'Session expired')
                : caption('Permintaan tidak berhasil', 'Request was not completed') }}
        </strong>
        <p v-if="message">{{ message }}</p>
        <p v-else-if="status === 'running'">{{ caption('Menunggu respons server. Tidak ada persentase perkiraan.', 'Waiting for the server. No estimated percentage.') }}</p>
        <p v-else-if="status === 'queued'">{{ caption('Periksa status operasi yang sama, jangan kirim permintaan baru.', 'Verify the same operation; do not send a new request.') }}</p>
        <small v-if="operationId && (status === 'queued' || status === 'unknown')">{{ caption('Referensi operasi', 'Operation reference') }}: {{ operationId }}</small>
    </aside>
</template>
