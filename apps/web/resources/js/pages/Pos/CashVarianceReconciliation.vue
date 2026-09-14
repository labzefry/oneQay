<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

type ReviewOutcome = 'REVIEW_ACCEPTED' | 'REVIEW_REJECTED';
type VarianceDirection = 'MATCH' | 'OVER' | 'SHORT';

type CaseRow = {
    closing_evidence_id: string;
    shift_id: string;
    device_id: string;
    closing_actor_identity_id: string;
    recorded_at_unix: number;
    has_explanation: boolean;
    has_review: boolean;
    review_outcome: ReviewOutcome | null;
};

type ExplanationEvidence = {
    evidence_id: string;
    actor_identity_id: string;
    text: string;
    recorded_at_unix: number;
};

type ReviewEvidence = {
    review_evidence_id: string;
    reviewer_actor_identity_id: string;
    outcome: ReviewOutcome;
    reviewed_at_unix: number;
};

type SelectedCase = {
    closing_evidence_id: string;
    shift_id: string;
    device_id: string;
    opener_actor_identity_id: string;
    closing_actor_identity_id: string;
    recorded_at_unix: number;
    expected_cash_atomic: string;
    observed_closing_cash_atomic: string;
    variance_atomic: string;
    variance_direction: VarianceDirection;
    currency: string;
    currency_scale: number;
    explanation: ExplanationEvidence | null;
    review: ReviewEvidence | null;
    can_record_explanation: boolean;
    can_record_review: boolean;
    review_ready_for_final_close: boolean;
    review_rejected_terminal: boolean;
    self_review_blocked: boolean;
};

const props = defineProps<{
    scope: { tenant_id: string; organization_id: string; outlet_id: string; actor_id: string };
    permissions: { can_explain: boolean; can_review: boolean };
    cases: CaseRow[];
    selected: SelectedCase | null;
    workspace_base_url: string;
    explanation_endpoint: string;
    review_endpoint: string;
    csrf_token: string;
    correlation_id: string;
}>();

const explanationText = ref('');
const busy = ref(false);
const actionLocked = ref(false);
const statusMessage = ref('');
const errorMessage = ref('');
const reviewConfirm = ref<ReviewOutcome | null>(null);

const utf8Length = computed(() => new TextEncoder().encode(explanationText.value).length);
const explanationValid = computed(() => explanationText.value.trim().length > 0 && utf8Length.value <= 4096);

const formatMoney = (atomic: string, currency: string, scale: number): string => {
    const negative = atomic.startsWith('-');
    const digits = (negative ? atomic.slice(1) : atomic).replace(/^0+(?=\d)/, '') || '0';
    const padded = digits.padStart(scale + 1, '0');
    const whole = scale === 0 ? padded : padded.slice(0, -scale);
    const fraction = scale === 0 ? '' : padded.slice(-scale);
    const grouped = whole.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    return `${negative ? '-' : ''}${currency} ${grouped}${fraction ? `,${fraction}` : ''}`;
};

const timestamp = (unix: number): string => new Intl.DateTimeFormat('id-ID', {
    dateStyle: 'medium',
    timeStyle: 'short',
}).format(new Date(unix * 1000));

const operationId = (prefix: string): string => {
    const uuid = crypto.randomUUID();
    return `${prefix}:${uuid}`;
};

const postJson = async (url: string, body: Record<string, string>): Promise<Record<string, unknown>> => {
    const response = await fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': props.csrf_token,
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify(body),
    });

    const payload = await response.json().catch(() => ({})) as Record<string, unknown>;
    if (!response.ok) {
        throw new Error(typeof payload.error === 'string' ? payload.error : `Request failed (${response.status}).`);
    }
    return payload;
};

const lockForRefresh = (message: string): void => {
    actionLocked.value = true;
    statusMessage.value = message;
};

const recordExplanation = async (): Promise<void> => {
    if (!props.selected || !props.selected.can_record_explanation || !explanationValid.value || busy.value || actionLocked.value) return;
    busy.value = true;
    errorMessage.value = '';
    statusMessage.value = '';
    try {
        await postJson(props.explanation_endpoint, {
            closing_evidence_id: props.selected.closing_evidence_id,
            operation_id: operationId('variance-explanation'),
            explanation_text: explanationText.value,
        });
        lockForRefresh('Explanation recorded. Refresh this workspace before any further reconciliation action.');
    } catch (error) {
        lockForRefresh('The network or server outcome may be uncertain. Refresh authoritatively before trying any action again.');
        errorMessage.value = error instanceof Error ? error.message : 'Variance explanation could not be confirmed.';
    } finally {
        busy.value = false;
    }
};

const requestReview = (outcome: ReviewOutcome): void => {
    if (!props.selected?.can_record_review || busy.value || actionLocked.value) return;
    reviewConfirm.value = outcome;
};

const submitReview = async (): Promise<void> => {
    const outcome = reviewConfirm.value;
    const selected = props.selected;
    if (!outcome || !selected?.can_record_review || !selected.explanation || busy.value || actionLocked.value) return;
    reviewConfirm.value = null;
    busy.value = true;
    errorMessage.value = '';
    statusMessage.value = '';
    try {
        await postJson(props.review_endpoint, {
            closing_evidence_id: selected.closing_evidence_id,
            operation_id: operationId('variance-review'),
            explanation_evidence_id: selected.explanation.evidence_id,
            review_outcome: outcome,
        });
        lockForRefresh(outcome === 'REVIEW_ACCEPTED'
            ? 'Review accepted. Refresh authoritatively before proceeding to final shift close.'
            : 'Review rejected. This decision is terminal for the current explanation and final shift close remains blocked. Refresh authoritatively.');
    } catch (error) {
        lockForRefresh('The network or server outcome may be uncertain. Refresh authoritatively before trying any action again.');
        errorMessage.value = error instanceof Error ? error.message : 'Variance review could not be confirmed.';
    } finally {
        busy.value = false;
    }
};

const refreshPage = (): void => window.location.reload();
</script>

<template>
    <Head title="Cash Variance Reconciliation" />
    <main class="shell">
        <header class="hero">
            <div>
                <p class="eyebrow">oneQay · Shift Control</p>
                <h1>Cash Variance Reconciliation</h1>
                <p class="subtitle">Explain non-zero closing-cash variance and route it through independent maker-checker review using server-authoritative evidence.</p>
            </div>
            <div class="scope-card">
                <span>Outlet</span><strong>{{ props.scope.outlet_id }}</strong>
                <small>Actor {{ props.scope.actor_id }}</small>
            </div>
        </header>

        <section class="notice">
            <strong>Control boundary</strong>
            <span>The browser never supplies expected cash, observed cash, variance, direction, currency, or scale. Those values are rebuilt from canonical persisted evidence on every action.</span>
        </section>

        <div class="layout">
            <aside class="panel case-list">
                <div class="panel-heading">
                    <div><p class="eyebrow">Active outlet shifts</p><h2>Closing evidence</h2></div>
                    <span class="count">{{ props.cases.length }}</span>
                </div>
                <p v-if="!props.cases.length" class="empty">No active shift has closing-cash evidence available for reconciliation.</p>
                <Link
                    v-for="item in props.cases"
                    :key="item.closing_evidence_id"
                    :href="`${props.workspace_base_url}/${encodeURIComponent(item.closing_evidence_id)}`"
                    class="case-card"
                    :class="{ selected: props.selected?.closing_evidence_id === item.closing_evidence_id }"
                >
                    <div class="case-row"><strong>{{ item.shift_id }}</strong><span>{{ timestamp(item.recorded_at_unix) }}</span></div>
                    <div class="case-row"><span>{{ item.device_id }}</span><span class="state">{{ item.review_outcome ?? (item.has_explanation ? 'WAITING REVIEW' : 'WAITING EXPLANATION') }}</span></div>
                </Link>
            </aside>

            <section class="panel detail">
                <p v-if="!props.selected" class="empty">Select a closing-cash evidence case.</p>
                <template v-else>
                    <div class="panel-heading">
                        <div><p class="eyebrow">Server-authoritative subject</p><h2>{{ props.selected.shift_id }}</h2></div>
                        <span class="direction" :data-direction="props.selected.variance_direction">{{ props.selected.variance_direction }}</span>
                    </div>

                    <div class="metric-grid">
                        <article><span>Expected cash</span><strong>{{ formatMoney(props.selected.expected_cash_atomic, props.selected.currency, props.selected.currency_scale) }}</strong></article>
                        <article><span>Observed closing</span><strong>{{ formatMoney(props.selected.observed_closing_cash_atomic, props.selected.currency, props.selected.currency_scale) }}</strong></article>
                        <article><span>Variance</span><strong>{{ formatMoney(props.selected.variance_atomic, props.selected.currency, props.selected.currency_scale) }}</strong></article>
                    </div>

                    <dl class="evidence-grid">
                        <div><dt>Closing evidence</dt><dd>{{ props.selected.closing_evidence_id }}</dd></div>
                        <div><dt>Device</dt><dd>{{ props.selected.device_id }}</dd></div>
                        <div><dt>Opening actor</dt><dd>{{ props.selected.opener_actor_identity_id }}</dd></div>
                        <div><dt>Closing actor</dt><dd>{{ props.selected.closing_actor_identity_id }}</dd></div>
                    </dl>

                    <div v-if="props.selected.variance_direction === 'MATCH'" class="state-box success">
                        <strong>No reconciliation required</strong>
                        <span>Expected and observed cash match. No explanation or reviewer evidence is required by the variance prerequisite.</span>
                    </div>

                    <div v-else class="workflow">
                        <article class="step-card">
                            <div class="step-title"><span>1</span><div><h3>Explanation</h3><p>Maker evidence for the non-zero variance.</p></div></div>
                            <div v-if="props.selected.explanation" class="evidence-box">
                                <strong>{{ props.selected.explanation.actor_identity_id }}</strong>
                                <p>{{ props.selected.explanation.text }}</p>
                                <small>{{ timestamp(props.selected.explanation.recorded_at_unix) }} · {{ props.selected.explanation.evidence_id }}</small>
                            </div>
                            <template v-else-if="props.selected.can_record_explanation">
                                <textarea v-model="explanationText" rows="5" maxlength="4096" placeholder="Enter a factual explanation for the closing-cash variance." :disabled="busy || actionLocked" />
                                <div class="action-row"><small>{{ utf8Length }} / 4096 UTF-8 bytes</small><button :disabled="!explanationValid || busy || actionLocked" @click="recordExplanation">Record explanation</button></div>
                            </template>
                            <p v-else class="muted">This actor does not have explanation authority, or explanation evidence already exists.</p>
                        </article>

                        <article class="step-card">
                            <div class="step-title"><span>2</span><div><h3>Independent review</h3><p>A different actor reviews the explanation.</p></div></div>
                            <div v-if="props.selected.review" class="evidence-box" :data-review="props.selected.review.outcome">
                                <strong>{{ props.selected.review.outcome }}</strong>
                                <p>Reviewer: {{ props.selected.review.reviewer_actor_identity_id }}</p>
                                <small>{{ timestamp(props.selected.review.reviewed_at_unix) }} · {{ props.selected.review.review_evidence_id }}</small>
                            </div>
                            <div v-else-if="props.selected.self_review_blocked" class="state-box warning">
                                <strong>Maker-checker separation required</strong>
                                <span>The explanation author cannot review their own evidence. A different authorized actor must review it.</span>
                            </div>
                            <div v-else-if="props.selected.can_record_review" class="review-actions">
                                <button class="approve" :disabled="busy || actionLocked" @click="requestReview('REVIEW_ACCEPTED')">Accept review</button>
                                <button class="reject" :disabled="busy || actionLocked" @click="requestReview('REVIEW_REJECTED')">Reject review</button>
                            </div>
                            <p v-else class="muted">Review requires existing explanation evidence and independent reviewer authority.</p>
                        </article>
                    </div>

                    <div v-if="props.selected.review_ready_for_final_close" class="state-box success">
                        <strong>Variance prerequisite ready</strong>
                        <span>The variance prerequisite is satisfied. Final shift close still revalidates all evidence and actor-separation rules independently.</span>
                    </div>
                    <div v-if="props.selected.review_rejected_terminal" class="state-box danger">
                        <strong>Rejected decision is terminal</strong>
                        <span>No second independent decision is supported for this explanation evidence. Final shift close remains blocked for this subject.</span>
                    </div>
                </template>
            </section>
        </div>

        <section v-if="statusMessage || errorMessage" class="result-box" :class="{ error: !!errorMessage }">
            <strong>{{ errorMessage ? 'Authoritative refresh required' : 'Action recorded' }}</strong>
            <p>{{ errorMessage || statusMessage }}</p>
            <button @click="refreshPage">Refresh authoritative state</button>
        </section>

        <footer>Correlation: <span class="mono">{{ props.correlation_id }}</span></footer>

        <div v-if="reviewConfirm" class="modal-backdrop" role="dialog" aria-modal="true">
            <div class="modal">
                <p class="eyebrow">Independent review confirmation</p>
                <h2>{{ reviewConfirm === 'REVIEW_ACCEPTED' ? 'Accept this variance explanation?' : 'Reject this variance explanation?' }}</h2>
                <p v-if="reviewConfirm === 'REVIEW_ACCEPTED'">Acceptance becomes immutable reviewer evidence. Final shift close will still revalidate the complete subject and actor separation.</p>
                <p v-else>Rejection is terminal for this explanation evidence. A second independent decision is not supported and final shift close will remain blocked.</p>
                <div class="modal-actions"><button class="secondary" @click="reviewConfirm = null">Cancel</button><button :class="reviewConfirm === 'REVIEW_REJECTED' ? 'reject' : 'approve'" @click="submitReview">Confirm {{ reviewConfirm === 'REVIEW_ACCEPTED' ? 'acceptance' : 'rejection' }}</button></div>
            </div>
        </div>
    </main>
</template>

<style scoped>
.shell{min-height:100vh;background:#f5f7fa;color:#172033;padding:32px;font-family:Inter,ui-sans-serif,system-ui,sans-serif}.hero{max-width:1280px;margin:0 auto 20px;display:flex;justify-content:space-between;gap:24px;align-items:flex-end}h1{margin:4px 0 8px;font-size:clamp(2rem,4vw,3rem);letter-spacing:-.04em}.subtitle{margin:0;color:#667085;max-width:760px}.eyebrow{margin:0;color:#526175;font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.12em}.scope-card,.panel,.notice,.result-box{background:#fff;border:1px solid #e4e8ef;border-radius:16px;box-shadow:0 8px 24px rgba(16,24,40,.04)}.scope-card{padding:14px 18px;min-width:240px}.scope-card span,.scope-card small{display:block;color:#667085;font-size:.75rem}.scope-card strong{display:block;margin:4px 0}.notice{max-width:1240px;margin:0 auto 20px;padding:14px 18px;display:flex;gap:12px;align-items:flex-start}.notice span{color:#667085;font-size:.85rem}.layout{max-width:1280px;margin:0 auto;display:grid;grid-template-columns:360px minmax(0,1fr);gap:20px}.panel{padding:22px}.panel-heading{display:flex;justify-content:space-between;gap:16px;align-items:center;margin-bottom:18px}.panel-heading h2{margin:3px 0 0}.count,.direction,.state{font-size:.7rem;font-weight:800;border-radius:999px;padding:6px 9px;background:#eef2f6}.case-list{align-self:start}.case-card{display:block;color:inherit;text-decoration:none;border:1px solid #e8ebf0;border-radius:12px;padding:13px;margin-bottom:10px;background:#fbfcfd}.case-card.selected{border-color:#98a2b3;background:#f2f4f7}.case-row{display:flex;justify-content:space-between;gap:10px;font-size:.78rem}.case-row+ .case-row{margin-top:8px;color:#667085}.metric-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}.metric-grid article{border:1px solid #e8ebf0;border-radius:12px;padding:16px}.metric-grid span{display:block;color:#667085;font-size:.75rem}.metric-grid strong{display:block;margin-top:5px;font-size:1.05rem}.evidence-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin:18px 0}.evidence-grid div{background:#f8fafc;border-radius:10px;padding:12px}.evidence-grid dt{color:#667085;font-size:.7rem}.evidence-grid dd{margin:4px 0 0;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:.74rem;overflow-wrap:anywhere}.workflow{display:grid;grid-template-columns:1fr 1fr;gap:14px}.step-card{border:1px solid #e4e8ef;border-radius:14px;padding:18px}.step-title{display:flex;gap:10px}.step-title>span{display:flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:50%;background:#172033;color:#fff;font-weight:800}.step-title h3{margin:0}.step-title p{margin:3px 0 14px;color:#667085;font-size:.8rem}.evidence-box,.state-box{border-radius:12px;padding:14px;background:#f8fafc}.evidence-box p{white-space:pre-wrap}.evidence-box small{color:#667085;overflow-wrap:anywhere}.state-box{margin-top:16px;display:flex;flex-direction:column;gap:4px;font-size:.84rem}.state-box.success{background:#eaf7ef}.state-box.warning{background:#fff5e8}.state-box.danger,.evidence-box[data-review="REVIEW_REJECTED"]{background:#fdecec}textarea{box-sizing:border-box;width:100%;border:1px solid #d0d5dd;border-radius:10px;padding:12px;resize:vertical;font:inherit}.action-row,.review-actions,.modal-actions{display:flex;justify-content:space-between;gap:10px;align-items:center;margin-top:10px}button{border:0;border-radius:10px;background:#172033;color:#fff;padding:10px 14px;font-weight:750;cursor:pointer}button:disabled{opacity:.45;cursor:not-allowed}.approve{background:#1e6f45}.reject{background:#a33030}.secondary{background:#eef2f6;color:#172033}.muted,.empty{color:#667085;font-size:.84rem}.result-box{max-width:1240px;margin:20px auto 0;padding:16px}.result-box.error{border-color:#f0b7b7}.result-box p{margin:5px 0 12px;color:#667085}footer{max-width:1280px;margin:16px auto;color:#98a2b3;font-size:.72rem}.mono{font-family:ui-monospace,SFMono-Regular,Menlo,monospace}.modal-backdrop{position:fixed;inset:0;background:rgba(16,24,40,.55);display:flex;align-items:center;justify-content:center;padding:20px;z-index:50}.modal{width:min(520px,100%);background:#fff;border-radius:16px;padding:24px;box-shadow:0 24px 64px rgba(16,24,40,.24)}.modal h2{margin:5px 0 10px}.modal p{color:#667085}.modal-actions{justify-content:flex-end}@media(max-width:900px){.shell{padding:20px 14px}.hero{flex-direction:column;align-items:stretch}.scope-card{min-width:0}.layout{grid-template-columns:1fr}.metric-grid,.workflow{grid-template-columns:1fr}.evidence-grid{grid-template-columns:1fr}.notice{flex-direction:column}}
</style>
