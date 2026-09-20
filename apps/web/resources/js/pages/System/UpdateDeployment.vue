<script setup lang="ts">
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
type ReleaseCheck = {
  status: 'NOT_CHECKED' | 'UNAVAILABLE' | 'AVAILABLE'
  release_id: string | null
  version: string | null
  channel: string | null
  source_commit: string | null
}

type UpdateStatus = {
  control_plane: 'ENABLED' | 'DISABLED'
  install: 'ENABLED' | 'DISABLED'
  state: string
  active_operation: boolean
  operation_id: string | null
  release_check: ReleaseCheck
  schema_change_supported: boolean
  activation_supported: boolean
  deployment_authorized: boolean
  attribution: string
}

type UiBoundary = {
  mode: 'READ_ONLY'
  install_action_exposed: false
  check_action_exposed: false
  production_ready: false
}

type InstallationCheck = {
  ready: boolean
  reason: string
}

type InstallationPreflight = {
  mode: 'READ_ONLY'
  ready: boolean
  actions_exposed: false
  checks: Record<string, InstallationCheck>
  evidence: {
    release_manifest: 'MISSING' | 'OBSERVED'
    release_artifact: 'MISSING' | 'OBSERVED'
    database: 'NOT_CONFIGURED' | 'UNAVAILABLE' | 'OBSERVED'
    host_platform: 'SERVER_OBSERVED_PARTIAL'
  }
}

type DevelopmentUpdaterResult = {
  state: string | null
  release_id: string | null
  source_commit: string | null
  completed_at_unix: number | null
  safe_code: string | null
}

type DevelopmentUpdaterStatus = {
  enabled: boolean
  mode: 'GOVERNED_DEVELOPMENT_STAGING_ONLY'
  request_pending: boolean
  request_id: string | null
  request_expires_at_unix: number | null
  last_result: DevelopmentUpdaterResult | null
  production_allowed: false
  migration_execution_allowed: false
  repository: 'labzefry/oneQay'
  channel: 'STAGING'
  attribution: string
}

type DevelopmentUpdateFeedback = {
  state: 'REQUEST_ACCEPTED' | 'REQUEST_DENIED'
  request_id: string | null
  message: string
} | null

// Author by Lab | zefry
const props = defineProps<{
  status: UpdateStatus
  ui: UiBoundary
  installation_preflight: InstallationPreflight
  development_updater: DevelopmentUpdaterStatus
  development_update_feedback: DevelopmentUpdateFeedback
}>()

const displayValue = (value: string | null): string => value ?? '—'
const compactCommit = (value: string | null): string => value ? `${value.slice(0, 12)}…` : '—'
const yesNo = (value: boolean): string => value ? 'Ya' : 'Tidak'
const readinessLabel = (ready: boolean): string => ready ? 'READY' : 'BLOCKED'

const installationSteps = [
  { key: 'runtime', title: 'Runtime & host', checks: ['php_version', 'php_extensions', 'host_platform'] },
  { key: 'configuration', title: 'Secure configuration', checks: ['environment', 'application_key', 'production_debug', 'production_https'] },
  { key: 'database', title: 'Database compatibility', checks: ['database_compatibility'] },
  { key: 'filesystem', title: 'Filesystem', checks: ['filesystem_write'] },
  { key: 'release', title: 'Governed release', checks: ['release_manifest', 'artifact_integrity'] },
] as const

const stepReady = (checks: readonly string[]): boolean =>
  checks.every((key) => props.installation_preflight.checks[key]?.ready === true)

const developmentConfirmation = ref(false)
const developmentUpdateForm = useForm({
  operator_token: '',
  totp_code: '',
  confirmation: '',
})

const submitDevelopmentUpdate = (): void => {
  if (!developmentConfirmation.value || developmentUpdateForm.processing || props.development_updater.request_pending) {
    return
  }

  developmentUpdateForm.confirmation = 'SYNC_GOVERNED_DEVELOPMENT_RELEASE'
  developmentUpdateForm.post('/system/update/development/request', {
    preserveScroll: true,
    onFinish: () => {
      developmentUpdateForm.reset('operator_token', 'totp_code', 'confirmation')
      developmentConfirmation.value = false
    },
  })
}
</script>

<template>
  <main class="update-page">
    <div class="update-shell">
      <header class="topbar">
        <div>
          <p class="brand">oneQay</p>
          <p class="brand-subtitle">Platform Operations</p>
        </div>
        <span class="mode-chip">{{ ui.mode }}</span>
      </header>

      <nav class="breadcrumb" aria-label="Breadcrumb">
        <span>System</span>
        <span aria-hidden="true">/</span>
        <strong>Update &amp; Deployment</strong>
      </nav>

      <section class="hero">
        <div>
          <p class="eyebrow">Governed updater control plane</p>
          <h1>Update &amp; Deployment</h1>
          <p class="hero-copy">
            Halaman ini hanya menampilkan status operasional yang aman. Pemeriksaan release, instalasi,
            aktivasi, rollback, dan deployment belum tersedia dari antarmuka ini.
          </p>
        </div>
        <div class="hero-lock">
          <span class="lock-label">Installation</span>
          <strong>{{ status.install }}</strong>
          <small>Hard-disabled by source policy</small>
        </div>
      </section>

      <section class="status-grid" aria-label="Updater status summary">
        <article class="status-card">
          <span class="status-label">Control plane</span>
          <strong>{{ status.control_plane }}</strong>
          <p>Backend status surface tersedia, tetapi tetap mengikuti feature gate.</p>
        </article>

        <article class="status-card">
          <span class="status-label">Operation state</span>
          <strong>{{ status.state }}</strong>
          <p>Active operation: {{ yesNo(status.active_operation) }}</p>
        </article>

        <article class="status-card">
          <span class="status-label">Release availability</span>
          <strong>{{ status.release_check.status }}</strong>
          <p>Tidak ada download atau staging yang dijalankan oleh halaman ini.</p>
        </article>

        <article class="status-card status-card--locked">
          <span class="status-label">Deployment authority</span>
          <strong>{{ status.deployment_authorized ? 'AUTHORIZED' : 'NOT AUTHORIZED' }}</strong>
          <p>UI tidak dapat menciptakan lifecycle authority.</p>
        </article>
      </section>

      <section class="content-grid">
        <article class="panel">
          <div class="panel-heading">
            <div>
              <p class="panel-kicker">Release status</p>
              <h2>Governed release visibility</h2>
            </div>
            <span class="state-pill">{{ status.release_check.status }}</span>
          </div>

          <dl class="detail-list">
            <div>
              <dt>Release ID</dt>
              <dd>{{ displayValue(status.release_check.release_id) }}</dd>
            </div>
            <div>
              <dt>Version</dt>
              <dd>{{ displayValue(status.release_check.version) }}</dd>
            </div>
            <div>
              <dt>Channel</dt>
              <dd>{{ displayValue(status.release_check.channel) }}</dd>
            </div>
            <div>
              <dt>Source commit</dt>
              <dd class="mono">{{ compactCommit(status.release_check.source_commit) }}</dd>
            </div>
          </dl>

          <div class="notice">
            Release check action belum diekspos di UI read-only ini. Tidak ada network release client,
            artifact download, atau package verification yang dipicu dari halaman ini.
          </div>
        </article>

        <article class="panel">
          <div class="panel-heading">
            <div>
              <p class="panel-kicker">Safety boundary</p>
              <h2>Deployment safeguards</h2>
            </div>
          </div>

          <dl class="boundary-list">
            <div>
              <dt>Schema change support</dt>
              <dd>{{ yesNo(status.schema_change_supported) }}</dd>
            </div>
            <div>
              <dt>Activation support</dt>
              <dd>{{ yesNo(status.activation_supported) }}</dd>
            </div>
            <div>
              <dt>Deployment authorized</dt>
              <dd>{{ yesNo(status.deployment_authorized) }}</dd>
            </div>
            <div>
              <dt>Operation ID</dt>
              <dd class="mono">{{ displayValue(status.operation_id) }}</dd>
            </div>
          </dl>

          <aside class="security-note">
            <strong>Privileged security remains mandatory</strong>
            <p>
              Instalasi masa depan tetap membutuhkan platform-superadmin capability, fresh privileged
              session, explicit re-authentication, TOTP step-up, CSRF, rate limiting, dan sanitized audit.
            </p>
          </aside>
        </article>
      </section>

      <section class="installation-wizard" aria-label="Installation readiness wizard">
        <div class="wizard-heading">
          <div>
            <p class="panel-kicker">Installation readiness wizard</p>
            <h2>Preflight instalasi — read only</h2>
            <p>
              Wizard ini hanya membaca evidence server dan governed release package. Tidak ada penulisan
              konfigurasi, migration, seeding, deployment, atau aktivasi yang dijalankan.
            </p>
          </div>
          <span class="readiness-badge" :data-ready="installation_preflight.ready">
            {{ readinessLabel(installation_preflight.ready) }}
          </span>
        </div>

        <div class="evidence-grid" aria-label="Installation evidence sources">
          <div><span>Release manifest</span><strong>{{ installation_preflight.evidence.release_manifest }}</strong></div>
          <div><span>Release artifact</span><strong>{{ installation_preflight.evidence.release_artifact }}</strong></div>
          <div><span>Database</span><strong>{{ installation_preflight.evidence.database }}</strong></div>
          <div><span>Host platform</span><strong>{{ installation_preflight.evidence.host_platform }}</strong></div>
        </div>

        <div class="wizard-steps">
          <article v-for="(step, index) in installationSteps" :key="step.key" class="wizard-step" :data-ready="stepReady(step.checks)">
            <div class="wizard-step-head">
              <span class="step-number">{{ String(index + 1).padStart(2, '0') }}</span>
              <div>
                <p class="status-label">{{ readinessLabel(stepReady(step.checks)) }}</p>
                <h3>{{ step.title }}</h3>
              </div>
            </div>

            <dl class="check-list">
              <div v-for="checkKey in step.checks" :key="checkKey">
                <dt>{{ checkKey.replaceAll('_', ' ') }}</dt>
                <dd>
                  <strong>{{ readinessLabel(installation_preflight.checks[checkKey]?.ready === true) }}</strong>
                  <small>{{ installation_preflight.checks[checkKey]?.reason ?? 'Readiness evidence unavailable.' }}</small>
                </dd>
              </div>
            </dl>
          </article>
        </div>

        <aside class="wizard-boundary">
          <strong>Preflight only — no installation authority</strong>
          <p>
            READY hanya berarti evidence preflight memenuhi contract yang tersedia. Tombol instalasi tetap
            hard-disabled; migration #27, permission provisioning, deployment, Technical Preview, Production,
            dan updater activation tidak mendapat authority dari wizard ini.
          </p>
        </aside>
      </section>

      <section v-if="development_updater.enabled" class="installation-wizard development-update-panel" aria-label="Governed development updater">
        <div class="wizard-heading">
          <div>
            <p class="panel-kicker">Governed development delivery</p>
            <h2>Sync update dari GitHub</h2>
            <p>
              Jalur ini hanya untuk runtime durable-staging. oneQay mengambil artifact resmi dari
              <strong>{{ development_updater.repository }}</strong>, memverifikasi exact hash dan forward-only source,
              lalu cPanel Cron melakukan switch release, attestation, rollback rehearsal, dan re-activation.
            </p>
          </div>
          <span class="readiness-badge" :data-ready="!development_updater.request_pending">
            {{ development_updater.request_pending ? 'PENDING' : 'READY' }}
          </span>
        </div>

        <div class="development-boundary-grid">
          <div><span>Channel</span><strong>{{ development_updater.channel }}</strong></div>
          <div><span>Production</span><strong>LOCKED</strong></div>
          <div><span>Migration</span><strong>NOT EXECUTED</strong></div>
          <div><span>Worker</span><strong>cPanel Cron / PHP CLI</strong></div>
        </div>

        <div v-if="development_update_feedback" class="notice" :data-state="development_update_feedback.state">
          <strong>{{ development_update_feedback.state }}</strong>
          <p>{{ development_update_feedback.message }}</p>
        </div>

        <div v-if="development_updater.last_result" class="development-result">
          <span>Last result</span>
          <strong>{{ development_updater.last_result.state ?? 'UNKNOWN' }}</strong>
          <small>
            {{ development_updater.last_result.release_id ?? 'No release' }}
            · {{ compactCommit(development_updater.last_result.source_commit) }}
            · {{ development_updater.last_result.safe_code ?? 'no-code' }}
          </small>
        </div>

        <form class="development-form" @submit.prevent="submitDevelopmentUpdate" autocomplete="off">
          <label>
            <span>Operator token</span>
            <input
              v-model="developmentUpdateForm.operator_token"
              type="password"
              minlength="32"
              maxlength="1024"
              required
              autocomplete="current-password"
              :disabled="development_updater.request_pending || developmentUpdateForm.processing"
            >
          </label>

          <label>
            <span>TOTP code</span>
            <input
              v-model="developmentUpdateForm.totp_code"
              type="text"
              inputmode="numeric"
              pattern="[0-9]{6}"
              minlength="6"
              maxlength="6"
              required
              autocomplete="one-time-code"
              :disabled="development_updater.request_pending || developmentUpdateForm.processing"
            >
          </label>

          <label class="development-confirmation">
            <input
              v-model="developmentConfirmation"
              type="checkbox"
              :disabled="development_updater.request_pending || developmentUpdateForm.processing"
            >
            <span>Saya mengotorisasi sinkronisasi artifact STAGING resmi terbaru. Production dan migration tetap tidak diizinkan.</span>
          </label>

          <button
            type="submit"
            class="development-submit"
            :disabled="!developmentConfirmation || development_updater.request_pending || developmentUpdateForm.processing"
          >
            {{ development_updater.request_pending ? 'Update request pending' : (developmentUpdateForm.processing ? 'Submitting…' : 'Sync governed update') }}
          </button>
        </form>

        <aside class="wizard-boundary">
          <strong>Browser tidak mengeksekusi deployment.</strong>
          <p>
            Tombol hanya membuat signed short-lived request. Worker
            <code>php artisan oneqay:update:process-development</code> yang berjalan melalui private cPanel Cron
            mengambil artifact GitHub, melakukan integrity checks, switch atomik, runtime attestation,
            rollback rehearsal, dan menulis staging evidence yang dapat dipakai untuk same-source Production promotion.
          </p>
        </aside>
      </section>

      <section class="action-panel" aria-label="Read-only action boundary">
        <div>
          <p class="panel-kicker">Controlled actions</p>
          <h2>Installation controls are locked</h2>
          <p>
            Tahap ini tidak menyediakan aksi perubahan. Tombol di bawah hanya indikator visual dan tidak
            memiliki event handler, form submission, atau request ke backend.
          </p>
        </div>
        <div class="action-stack">
          <button type="button" disabled>Check for updates — unavailable</button>
          <button type="button" disabled>Install update — locked</button>
        </div>
      </section>

      <footer class="footer">
        <span>Read-only operational view</span>
        <span>{{ status.attribution }}</span>
      </footer>
    </div>
  </main>
</template>

<style scoped>
.update-page {
  min-height: 100vh;
  background: #07111f;
  color: #e8eef7;
  padding: 1.5rem;
}

.update-shell {
  width: min(76rem, 100%);
  margin: 0 auto;
}

.topbar,
.hero,
.panel-heading,
.action-panel,
.footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1.25rem;
}

.topbar {
  min-height: 4.5rem;
  border-bottom: 1px solid #1d2b3d;
}

.brand,
.brand-subtitle,
.eyebrow,
.panel-kicker,
.status-label,
.hero-copy,
.status-card p,
.action-panel p,
.security-note p,
.footer {
  margin: 0;
}

.brand {
  font-size: 1.15rem;
  font-weight: 800;
  letter-spacing: -0.02em;
}

.brand-subtitle,
.status-card p,
.action-panel p,
.footer {
  color: #94a6bd;
}

.brand-subtitle {
  margin-top: 0.1rem;
  font-size: 0.78rem;
}

.mode-chip,
.state-pill {
  border: 1px solid #36516f;
  border-radius: 999px;
  padding: 0.35rem 0.65rem;
  color: #b9d7f5;
  background: #0d1b2b;
  font-size: 0.72rem;
  font-weight: 800;
  letter-spacing: 0.08em;
}

.breadcrumb {
  display: flex;
  gap: 0.55rem;
  padding: 1.2rem 0 0.25rem;
  color: #8094ac;
  font-size: 0.82rem;
}

.breadcrumb strong {
  color: #dce8f5;
}

.hero {
  align-items: flex-end;
  padding: 2.25rem 0 2rem;
}

.eyebrow,
.panel-kicker,
.status-label {
  color: #76b7ef;
  font-size: 0.72rem;
  font-weight: 800;
  letter-spacing: 0.11em;
  text-transform: uppercase;
}

h1,
h2 {
  margin: 0;
  letter-spacing: -0.035em;
}

h1 {
  margin-top: 0.35rem;
  font-size: clamp(2rem, 5vw, 3.8rem);
  line-height: 1.05;
}

h2 {
  margin-top: 0.25rem;
  font-size: 1.2rem;
}

.hero-copy {
  max-width: 45rem;
  margin-top: 0.9rem;
  color: #a9b8ca;
  font-size: 0.98rem;
}

.hero-lock {
  min-width: 13rem;
  display: grid;
  gap: 0.2rem;
  padding: 1rem 1.1rem;
  border: 1px solid #543636;
  border-radius: 0.9rem;
  background: #1b1115;
}

.lock-label,
.hero-lock small {
  color: #c79ca2;
  font-size: 0.72rem;
}

.hero-lock strong {
  color: #ffbcc2;
}

.status-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 0.8rem;
}

.status-card,
.panel,
.action-panel {
  border: 1px solid #1d3147;
  border-radius: 1rem;
  background: #0b1827;
}

.status-card {
  min-height: 9.5rem;
  padding: 1rem;
}

.status-card strong {
  display: block;
  margin: 0.55rem 0 0.45rem;
  font-size: 1.05rem;
}

.status-card p {
  font-size: 0.82rem;
}

.status-card--locked {
  border-color: #49373b;
  background: #171318;
}

.content-grid {
  display: grid;
  grid-template-columns: 1.2fr 0.8fr;
  gap: 0.9rem;
  margin-top: 0.9rem;
}

.panel {
  padding: 1.25rem;
}

.detail-list,
.boundary-list {
  display: grid;
  gap: 0;
  margin: 1rem 0 0;
}

.detail-list > div,
.boundary-list > div {
  display: grid;
  grid-template-columns: minmax(9rem, 0.75fr) minmax(0, 1.25fr);
  gap: 1rem;
  padding: 0.75rem 0;
  border-top: 1px solid #1a2a3d;
}

dt {
  color: #8296ad;
  font-size: 0.8rem;
}

dd {
  margin: 0;
  text-align: right;
  overflow-wrap: anywhere;
}

.mono {
  font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
}

.notice,
.security-note {
  margin-top: 1rem;
  padding: 0.9rem 1rem;
  border-radius: 0.8rem;
}

.notice {
  border: 1px solid #294869;
  background: #0a2138;
  color: #bcd7ef;
  font-size: 0.82rem;
}

.security-note {
  background: #111d2c;
  color: #d8e5f2;
}

.security-note p {
  margin-top: 0.35rem;
  color: #9db0c5;
  font-size: 0.82rem;
}

.installation-wizard {
  margin-top: 0.9rem;
  padding: 1.25rem;
  border: 1px solid #1d3147;
  border-radius: 1rem;
  background: #0b1827;
}

.wizard-heading {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1.25rem;
}

.wizard-heading p {
  max-width: 48rem;
  margin: 0.55rem 0 0;
  color: #94a6bd;
  font-size: 0.86rem;
}

.readiness-badge {
  border: 1px solid #6c4145;
  border-radius: 999px;
  padding: 0.45rem 0.75rem;
  background: #25161a;
  color: #ffb4bc;
  font-size: 0.74rem;
  font-weight: 900;
  letter-spacing: 0.08em;
}

.readiness-badge[data-ready="true"] {
  border-color: #236b52;
  background: #0d2a22;
  color: #8ee0bd;
}

.evidence-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 0.65rem;
  margin-top: 1rem;
}

.evidence-grid > div {
  padding: 0.8rem;
  border: 1px solid #1a2a3d;
  border-radius: 0.75rem;
  background: #091522;
}

.evidence-grid span,
.evidence-grid strong {
  display: block;
}

.evidence-grid span {
  color: #8094ac;
  font-size: 0.72rem;
}

.evidence-grid strong {
  margin-top: 0.25rem;
  overflow-wrap: anywhere;
  font-size: 0.82rem;
}

.wizard-steps {
  display: grid;
  grid-template-columns: repeat(5, minmax(0, 1fr));
  gap: 0.65rem;
  margin-top: 0.75rem;
}

.wizard-step {
  padding: 0.85rem;
  border: 1px solid #4a3438;
  border-radius: 0.8rem;
  background: #151216;
}

.wizard-step[data-ready="true"] {
  border-color: #1e5b47;
  background: #0c201b;
}

.wizard-step-head {
  display: flex;
  gap: 0.6rem;
  align-items: center;
}

.step-number {
  display: grid;
  place-items: center;
  width: 2.2rem;
  height: 2.2rem;
  border-radius: 0.65rem;
  background: #15283b;
  color: #8ec8f5;
  font-size: 0.75rem;
  font-weight: 900;
}

.wizard-step h3 {
  margin: 0.15rem 0 0;
  font-size: 0.92rem;
}

.check-list {
  margin: 0.7rem 0 0;
}

.check-list > div {
  padding: 0.55rem 0;
  border-top: 1px solid #263142;
}

.check-list dt {
  text-transform: capitalize;
}

.check-list dd {
  margin-top: 0.2rem;
  text-align: left;
}

.check-list dd strong,
.check-list dd small {
  display: block;
}

.check-list dd strong {
  color: #cfdbea;
  font-size: 0.72rem;
}

.check-list dd small {
  margin-top: 0.15rem;
  color: #8296ad;
  font-size: 0.7rem;
  line-height: 1.35;
}

.wizard-boundary {
  margin-top: 0.8rem;
  padding: 0.9rem 1rem;
  border: 1px solid #463c2a;
  border-radius: 0.8rem;
  background: #1b180f;
  color: #ecd49a;
}

.wizard-boundary p {
  margin: 0.35rem 0 0;
  color: #bea974;
  font-size: 0.8rem;
}

.development-boundary-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 0.65rem;
  margin-top: 1rem;
}

.development-boundary-grid > div,
.development-result {
  padding: 0.8rem;
  border: 1px solid #1a2a3d;
  border-radius: 0.75rem;
  background: #091522;
}

.development-boundary-grid span,
.development-boundary-grid strong,
.development-result span,
.development-result strong,
.development-result small {
  display: block;
}

.development-boundary-grid span,
.development-result span,
.development-result small {
  color: #8094ac;
  font-size: 0.72rem;
}

.development-boundary-grid strong,
.development-result strong {
  margin-top: 0.25rem;
  overflow-wrap: anywhere;
  font-size: 0.84rem;
}

.development-result {
  margin-top: 0.75rem;
}

.development-result small {
  margin-top: 0.3rem;
}

.development-form {
  display: grid;
  grid-template-columns: 1fr 0.55fr;
  gap: 0.75rem;
  margin-top: 0.9rem;
}

.development-form label > span {
  display: block;
  margin-bottom: 0.35rem;
  color: #9db0c5;
  font-size: 0.78rem;
  font-weight: 700;
}

.development-form input[type="password"],
.development-form input[type="text"] {
  width: 100%;
  box-sizing: border-box;
  border: 1px solid #294869;
  border-radius: 0.7rem;
  padding: 0.75rem 0.8rem;
  background: #071522;
  color: #e8eef7;
  font: inherit;
}

.development-confirmation {
  grid-column: 1 / -1;
  display: flex;
  align-items: flex-start;
  gap: 0.6rem;
  padding: 0.8rem;
  border: 1px solid #463c2a;
  border-radius: 0.75rem;
  background: #15140f;
}

.development-confirmation > span {
  margin: 0 !important;
  color: #c8b984 !important;
  line-height: 1.45;
}

.development-submit {
  grid-column: 1 / -1;
  border-color: #2d668e;
  background: #0c2940;
  color: #d9ecfb;
}

.development-submit:disabled {
  border-color: #314158;
  background: #111a27;
  color: #68798d;
}

.action-panel {
  margin-top: 0.9rem;
  padding: 1.25rem;
}

.action-panel > div:first-child {
  max-width: 45rem;
}

.action-panel p {
  margin-top: 0.55rem;
  font-size: 0.86rem;
}

.action-stack {
  display: grid;
  gap: 0.55rem;
  min-width: 16rem;
}

button {
  border: 1px solid #314158;
  border-radius: 0.7rem;
  padding: 0.7rem 0.85rem;
  background: #111a27;
  color: #68798d;
  font: inherit;
  font-size: 0.82rem;
  font-weight: 700;
}

button:disabled {
  cursor: not-allowed;
  opacity: 0.78;
}

.footer {
  padding: 1.1rem 0 0.25rem;
  font-size: 0.75rem;
}

@media (max-width: 900px) {
  .status-grid,
  .content-grid,
  .evidence-grid,
  .development-boundary-grid {
    grid-template-columns: 1fr 1fr;
  }

  .wizard-steps {
    grid-template-columns: 1fr 1fr;
  }

  .content-grid .panel {
    grid-column: span 2;
  }
}

@media (max-width: 640px) {
  .update-page {
    padding: 1rem;
  }

  .hero,
  .action-panel,
  .footer {
    align-items: stretch;
    flex-direction: column;
  }

  .hero-lock,
  .action-stack {
    min-width: 0;
    width: 100%;
  }

  .status-grid,
  .content-grid,
  .evidence-grid,
  .wizard-steps,
  .development-boundary-grid,
  .development-form {
    grid-template-columns: 1fr;
  }

  .development-confirmation,
  .development-submit {
    grid-column: auto;
  }

  .content-grid .panel {
    grid-column: auto;
  }

  .detail-list > div,
  .boundary-list > div {
    grid-template-columns: 1fr;
    gap: 0.2rem;
  }

  dd {
    text-align: left;
  }
}
</style>
