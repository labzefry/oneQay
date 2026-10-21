// Author by Lab | zefry. Operation identity and uncertain mutations are never discarded implicitly.
import { computed, onBeforeUnmount, ref } from 'vue';

export type MutationStatus = 'idle' | 'running' | 'succeeded' | 'failed' | 'unknown' | 'queued' | 'session-expired';
type Pending = { url: string; body: { operation_id: string; data: unknown }; fingerprint: string };
export function useBusinessMutation() {
    const status = ref<MutationStatus>('idle');
    const busy = computed(() => status.value === 'running');
    const uncertain = computed(() => status.value === 'unknown' || status.value === 'queued');
    const error = ref('');
    const operationId = ref<string | null>(null);
    let pending: Pending | null = null;
    let lastSucceeded: { url: string; fingerprint: string } | null = null;
    let controller: AbortController | null = null;
    let timeout: ReturnType<typeof setTimeout> | null = null;
    let generation = 0;
    let disposed = false;

    function clear(): void {
        if (busy.value || uncertain.value) return;
        pending = null;
        lastSucceeded = null;
        operationId.value = null;
        error.value = '';
        status.value = 'idle';
    }
    function safeCode(value: unknown): string {
        return typeof value === 'string' && /^[A-Z][A-Z0-9_]{1,63}$/.test(value) ? value : '';
    }
    function setOutcome(next: MutationStatus, message = ''): void {
        if (disposed) return;
        status.value = next;
        error.value = message;
    }
    async function submit(url: string, data: unknown): Promise<any | null> {
        if (disposed || busy.value || status.value === 'queued' || status.value === 'session-expired') return null;
        let fingerprint: string;
        try { fingerprint = JSON.stringify(data); } catch {
            setOutcome('failed', 'The form could not be prepared safely.');
            return null;
        }
        if (!pending && lastSucceeded?.url === url && lastSucceeded.fingerprint === fingerprint) {
            setOutcome('succeeded');
            return null; // Duplicate click after authoritative success; wait for a new input.
        }
        if (pending && (pending.url !== url || pending.fingerprint !== fingerprint)) {
            setOutcome('unknown', 'An earlier request is not yet confirmed. Verify its result before editing or sending another request.');
            return null;
        }
        if (!pending) pending = { url, fingerprint, body: { operation_id: `business:${crypto.randomUUID()}`, data: JSON.parse(fingerprint) } };
        operationId.value = pending.body.operation_id;
        setOutcome('running');
        const current = ++generation;
        controller = new AbortController();
        const active = controller;
        timeout = setTimeout(() => active.abort(), 30000);
        try {
            if (!navigator.onLine) {
                setOutcome('unknown', 'Offline. This request was not queued locally. Verify the server result before trying again.');
                return null;
            }
            const token = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content;
            if (!token) {
                setOutcome('session-expired', 'Session verification is unavailable. Sign in again before making changes.');
                return null;
            }
            const response = await fetch(pending.url, {
                method: 'POST', credentials: 'same-origin', redirect: 'error', signal: active.signal,
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': token },
                body: JSON.stringify(pending.body),
            });
            if (disposed || current !== generation) return null;
            if (active.signal.aborted) { setOutcome('unknown', 'Response arrived after the request deadline. Verify the result.'); return null; }
            if (response.status === 401 || response.status === 419) {
                pending = null;
                setOutcome('session-expired', 'Your session has expired. Sign in again.');
                window.location.assign('/business-login?reason=session-expired');
                return null;
            }
            if (response.status >= 500) {
                setOutcome('unknown', 'The server result is not confirmed. Check the record before manually retrying the same operation.');
                return null;
            }
            let value: any;
            try { value = await response.json(); } catch {
                setOutcome('unknown', 'The server response was incomplete. Verify the result; do not create a new operation.');
                return null;
            }
            if (disposed || current !== generation) return null;
            if (active.signal.aborted) { setOutcome('unknown', 'Late response discarded after timeout; verify server state.'); return null; }
            if (response.status === 202 || value?.status === 'queued') {
                setOutcome('queued', 'Accepted into a server queue; completion is not yet confirmed. Check the operation status in the authorized workspace.');
                return null;
            }
            if (!response.ok) {
                const code = safeCode(value?.error?.code ?? value?.code);
                pending = null;
                operationId.value = null;
                setOutcome('failed', code ? `Change rejected (${code}). Check the fields and permissions.` : 'Change rejected. Check the fields and permissions.');
                return null;
            }
            if (typeof value?.record !== 'object' || value.record === null) {
                setOutcome('unknown', 'No confirmed saved record was returned. Verify the server state before proceeding.');
                return null;
            }
            lastSucceeded = { url, fingerprint };
            pending = null;
            setOutcome('succeeded');
            return value.record;
        } catch (reason) {
            if (disposed || current !== generation) return null;
            const timedOut = reason instanceof Error && reason.name === 'AbortError';
            setOutcome('unknown', timedOut
                ? 'Request timed out. The change may have committed. Verify it before manually retrying the same operation.'
                : 'The connection ended without confirmation. Verify the result; do not submit a new operation.');
            return null;
        } finally {
            if (timeout !== null) clearTimeout(timeout);
            timeout = null;
            if (controller === active) controller = null;
        }
    }
    onBeforeUnmount(() => {
        disposed = true;
        generation += 1;
        controller?.abort();
        if (timeout !== null) clearTimeout(timeout);
    });
    return { busy, uncertain, error, status, operationId, submit, clear };
}
