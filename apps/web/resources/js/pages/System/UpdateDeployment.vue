<script setup lang="ts">
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

// Author by Lab | zefry
const props = defineProps<{
  status: UpdateStatus
  ui: UiBoundary
}>()

const displayValue = (value: string | null): string => value ?? '—'
const compactCommit = (value: string | null): string => value ? `${value.slice(0, 12)}…` : '—'
const yesNo = (value: boolean): string => value ? 'Ya' : 'Tidak'
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
  .content-grid {
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
  .content-grid {
    grid-template-columns: 1fr;
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
