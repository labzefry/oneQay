<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

type Destination = {
    key: string;
    title: string;
    description: string;
    url: string;
    category: string;
};

const props = defineProps<{
    scope: {
        tenant_id: string;
        organization_id: string;
        outlet_id: string;
        device_id: string;
    };
    destinations: Destination[];
    correlation_id: string;
}>();
</script>

<template>
    <Head title="POS Operations" />
    <main class="hub-shell">
        <header class="hero">
            <div>
                <p class="eyebrow">oneQay · Point of Sale</p>
                <h1>POS Operations</h1>
                <p class="subtitle">
                    Satu pintu untuk workspace POS yang sedang delivered dan diizinkan pada konteks ini.
                    Setiap workspace tetap melakukan pemeriksaan otorisasi sendiri.
                </p>
            </div>
            <div class="scope-card">
                <span>Outlet aktif</span>
                <strong>{{ props.scope.outlet_id }}</strong>
                <small>Device · {{ props.scope.device_id }}</small>
            </div>
        </header>

        <section class="context-strip" aria-label="Current POS context">
            <div><span>Tenant</span><strong>{{ props.scope.tenant_id }}</strong></div>
            <div><span>Organization</span><strong>{{ props.scope.organization_id }}</strong></div>
            <div><span>Outlet</span><strong>{{ props.scope.outlet_id }}</strong></div>
            <div><span>Device</span><strong>{{ props.scope.device_id }}</strong></div>
        </section>

        <section class="workspace-panel">
            <div class="panel-heading">
                <div>
                    <p class="eyebrow">Authorized destinations</p>
                    <h2>Operational workspaces</h2>
                </div>
                <span class="count">{{ props.destinations.length }} available</span>
            </div>

            <div class="workspace-grid">
                <a
                    v-for="destination in props.destinations"
                    :key="destination.key"
                    class="workspace-card"
                    :href="destination.url"
                >
                    <div class="card-topline">
                        <span class="category">{{ destination.category }}</span>
                        <span class="arrow" aria-hidden="true">→</span>
                    </div>
                    <h3>{{ destination.title }}</h3>
                    <p>{{ destination.description }}</p>
                    <span class="open-label">Open workspace</span>
                </a>
            </div>
        </section>

        <aside class="safety-note">
            <strong>Fail-closed navigation</strong>
            <p>
                Hub ini tidak memberikan permission dan tidak mengaktifkan capability. Tautan hanya muncul bila
                route target sedang terdaftar dan permission current context memenuhi requirement workspace tersebut.
            </p>
        </aside>

        <footer>Correlation: <span class="mono">{{ props.correlation_id }}</span></footer>
    </main>
</template>

<style scoped>
.hub-shell { min-height:100vh; background:#f5f7fa; color:#172033; padding:32px; font-family:Inter,ui-sans-serif,system-ui,sans-serif; }
.hero { max-width:1200px; margin:0 auto 22px; display:flex; justify-content:space-between; gap:28px; align-items:flex-end; }
h1 { margin:5px 0 9px; font-size:clamp(2.1rem,4vw,3.2rem); letter-spacing:-.045em; }
.subtitle { max-width:720px; margin:0; color:#667085; line-height:1.65; }
.eyebrow { margin:0; color:#526175; font-size:.74rem; font-weight:800; text-transform:uppercase; letter-spacing:.12em; }
.scope-card,.context-strip,.workspace-panel,.safety-note { background:#fff; border:1px solid #e4e8ef; border-radius:18px; box-shadow:0 8px 24px rgba(16,24,40,.04); }
.scope-card { padding:17px 20px; min-width:260px; }.scope-card span,.scope-card small{display:block;color:#667085;font-size:.76rem}.scope-card strong{display:block;margin:5px 0;font-size:1.05rem;word-break:break-word}
.context-strip { max-width:1152px; margin:0 auto 20px; padding:16px 24px; display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:18px; }.context-strip span{display:block;color:#98a2b3;font-size:.7rem;text-transform:uppercase;letter-spacing:.08em;font-weight:700}.context-strip strong{display:block;margin-top:5px;font-size:.82rem;word-break:break-all}
.workspace-panel { max-width:1152px; margin:0 auto 20px; padding:26px; }.panel-heading{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:20px}h2{margin:4px 0 0;font-size:1.25rem}.count{padding:7px 11px;border-radius:999px;background:#eef2f6;font-size:.74rem;font-weight:800}
.workspace-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:14px; }.workspace-card{display:flex;min-height:170px;flex-direction:column;text-decoration:none;color:inherit;border:1px solid #e4e8ef;border-radius:15px;padding:19px;background:#fbfcfd;transition:transform .15s ease,box-shadow .15s ease,border-color .15s ease}.workspace-card:hover,.workspace-card:focus-visible{transform:translateY(-2px);border-color:#c8d1de;box-shadow:0 12px 28px rgba(16,24,40,.08);outline:none}.card-topline{display:flex;justify-content:space-between;align-items:center}.category{font-size:.67rem;font-weight:900;letter-spacing:.11em;color:#667085}.arrow{font-size:1.25rem}.workspace-card h3{margin:20px 0 7px;font-size:1.05rem}.workspace-card p{margin:0;color:#667085;font-size:.83rem;line-height:1.55;flex:1}.open-label{margin-top:18px;font-size:.74rem;font-weight:800}
.safety-note { max-width:1152px; margin:0 auto; padding:18px 22px; display:grid; grid-template-columns:190px 1fr; gap:18px; align-items:start }.safety-note strong{font-size:.84rem}.safety-note p{margin:0;color:#667085;font-size:.8rem;line-height:1.55}footer{max-width:1200px;margin:14px auto 0;color:#98a2b3;font-size:.72rem}.mono{font-family:ui-monospace,SFMono-Regular,Menlo,monospace}
@media (max-width:900px){.workspace-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.context-strip{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media (max-width:640px){.hub-shell{padding:20px 14px}.hero{align-items:stretch;flex-direction:column}.scope-card{min-width:0}.context-strip,.workspace-grid{grid-template-columns:1fr}.workspace-panel{padding:18px}.panel-heading{align-items:flex-start;flex-direction:column}.safety-note{grid-template-columns:1fr}}
</style>
