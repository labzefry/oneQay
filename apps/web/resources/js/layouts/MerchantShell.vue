<script setup lang="ts">
import { t } from "../i18n";
// Author by Lab | zefry. Read-only navigation always follows the current server scope.
import { usePage } from '@inertiajs/vue3';
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import LanguageSwitcher from '../components/LanguageSwitcher.vue';
import WorkspaceIcon from '../components/WorkspaceIcon.vue';
type Destination = { key: string; title: string; description: string; url: string; category: string };
type Scope = { tenant_id: string; organization_id: string; outlet_id: string; device_id: string };
type WorkspaceProps = { [key: string]: unknown; scope?: Scope; destinations?: Destination[] };
const page = usePage<WorkspaceProps>();
const navigation = ref<Destination[]>([]);
const mobileOpen = ref(false);
const loading = ref(false);
const mobileViewport = ref(false);
const search = ref('');
const drawer = ref<HTMLElement | null>(null);
const menu = ref<HTMLButtonElement | null>(null);
let generation = 0;
let request: AbortController | null = null;
const labels: Record<string, { title: string; icon: string }> = {
    catalog_inventory: { title: 'Catalog & opening stock', icon: 'package' },
    inventory_replenishment: { title: 'Receive stock', icon: 'truck' },
    inventory_accountability: { title: 'Inventory overview', icon: 'boxes' },
    shift_start: { title: 'Start shift', icon: 'clock-3' },
    cashier: { title: 'Cashier', icon: 'shopping-cart' },
    sales_summary: { title: 'Sales overview', icon: 'chart-column' },
    active_shift_performance: { title: 'Current shift', icon: 'wallet' },
    shift_history_performance: { title: 'Shift history', icon: 'clipboard-list' },
    product_sales_performance: { title: 'Product performance', icon: 'chart-no-axes-combined' },
    sale_history: { title: 'Receipts & history', icon: 'receipt-text' },
    corrections: { title: 'Sale corrections', icon: 'sliders-horizontal' },
    cash_variance_reconciliation: { title: 'Cash reconciliation', icon: 'wallet' },
    shift_close: { title: 'Close shift', icon: 'circle-check' },
    business_suppliers: { title: 'Suppliers', icon: 'truck' },
    business_purchases: { title: 'Purchasing', icon: 'clipboard-list' },
    business_customers: { title: 'Customers', icon: 'users' },
    business_finance: { title: 'Finance', icon: 'wallet' },
    business_people: { title: 'People', icon: 'users' },
    business_audit: { title: 'Activity log', icon: 'clipboard-list' },
};
const categories = [
    { key: 'SETUP', title: 'Products' }, { key: 'SELL', title: 'Selling' },
    { key: 'SHIFT', title: 'Shifts' }, { key: 'STOCK', title: 'Inventory' },
    { key: 'REVIEW', title: 'Insights' }, { key: 'CONTROL', title: 'Controls' },
    { key: 'MANAGE', title: 'Business management' },
];
const titles: Record<string, string> = {
    OperationsHub: 'Business Command Center', Cashier: 'Cashier', CatalogInventorySetup: 'Catalog & stock',
    ShiftStart: 'Start shift', ShiftClose: 'Close shift', InventoryReplenishment: 'Receive stock',
    InventoryAccountability: 'Inventory overview', SalesSummary: 'Sales overview',
    ActiveShiftPerformance: 'Current shift', ShiftHistoryPerformance: 'Shift history',
    ProductSalesPerformance: 'Product performance', SaleHistory: 'Receipts & history',
    SaleCorrections: 'Sale corrections', CashVarianceReconciliation: 'Cash reconciliation',
};
const title = computed(() => titles[page.component.split('/').pop() ?? ''] ?? 'Workspace');
const activePath = computed(() => new URL(page.url, window.location.origin).pathname.replace(/\/$/, ''));
const groups = computed(() => categories.map((category) => ({
    ...category,
    items: navigation.value.filter((item) => item.category === category.key)
        .filter((item) => t(labels[item.key]?.title ?? item.title).toLowerCase().includes(search.value.toLowerCase().trim())),
})).filter((group) => group.items.length > 0));
function isActive(url: string): boolean { return activePath.value === new URL(url, window.location.origin).pathname.replace(/\/$/, ''); }
function validDestinations(value: unknown): Destination[] {
    if (!Array.isArray(value)) return [];
    return value.filter((item): item is Destination => {
        if (!item || typeof item !== 'object') return false;
        if (!['key', 'title', 'description', 'url', 'category'].every((key) => typeof item[key] === 'string')) return false;
        try { const url = new URL(item.url, window.location.origin); return url.origin === window.location.origin && url.pathname.startsWith('/pos/'); }
        catch { return false; }
    }).map((item) => ({ key: item.key, title: item.title, description: item.description, url: item.url, category: item.category }));
}
function sameScope(left: Scope, right: unknown): boolean {
    if (!right || typeof right !== 'object') return false;
    const other = right as Record<string, unknown>;
    return ['tenant_id', 'organization_id', 'outlet_id', 'device_id'].every((key) => other[key] === left[key as keyof Scope]);
}
watch(() => [page.url, page.props.scope, page.props.destinations] as const, async (_, __, onCleanup) => {
    const current = ++generation;
    request?.abort(); mobileOpen.value = false; navigation.value = []; search.value = ''; loading.value = false;
    if (Array.isArray(page.props.destinations)) { navigation.value = validDestinations(page.props.destinations); return; }
    const scope = page.props.scope;
    request = new AbortController(); const controller = request;
    const timeout = window.setTimeout(() => controller.abort(), 5000);
    onCleanup(() => { controller.abort(); window.clearTimeout(timeout); });
    loading.value = true;
    try {
        const response = await fetch(new URL('/pos', window.location.origin), {
            method: 'GET', credentials: 'same-origin', cache: 'no-store', signal: controller.signal, redirect: 'error',
            headers: { 'X-Inertia': 'true', 'X-Inertia-Version': page.version ?? '', 'Accept': 'application/json' },
        });
        if (!response.ok || response.headers.get('X-Inertia')?.toLowerCase() !== 'true') return;
        const data = await response.json();
        if (current !== generation || data.component !== 'Pos/OperationsHub' || (scope && !sameScope(scope, data.props?.scope))) return;
        navigation.value = validDestinations(data.props?.destinations);
    } catch { /* Keep overview available. Never infer missing permissions or retry mutations. */ }
    finally { window.clearTimeout(timeout); if (current === generation) loading.value = false; }
}, { immediate: true });
watch(mobileOpen, async (open) => {
    document.body.classList.toggle('oq-drawer-open', open);
    await nextTick();
    if (open) drawer.value?.querySelector<HTMLElement>('a,button,input')?.focus();
    else if (window.matchMedia('(max-width: 800px)').matches) menu.value?.focus();
});
function keydown(event: KeyboardEvent): void {
    if (!mobileOpen.value) return;
    if (event.key === 'Escape') { event.preventDefault(); mobileOpen.value = false; }
    if (event.key !== 'Tab') return;
    const items = Array.from(drawer.value?.querySelectorAll<HTMLElement>('a,button,input') ?? []).filter((item) => item.getClientRects().length > 0);
    const first = items[0], last = items[items.length - 1];
    if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last?.focus(); }
    else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first?.focus(); }
}
function resize(): void { mobileViewport.value = window.matchMedia('(max-width: 800px)').matches; if (!mobileViewport.value) mobileOpen.value = false; }
function unfinishedSale(): boolean { return document.querySelector('[data-leave-confirmation="true"]') !== null; }
function beforeUnload(event: BeforeUnloadEvent): void { if (unfinishedSale()) { event.preventDefault(); event.returnValue = ''; } }
function confirmLeave(event: MouseEvent): void {
    if (!unfinishedSale() || event.defaultPrevented || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
    const anchor = (event.target instanceof Element ? event.target : null)?.closest('a');
    if (!anchor || anchor.hasAttribute('download') || (anchor.target && anchor.target !== '_self')) return;
    const target = new URL(anchor.href, window.location.origin);
    if (target.pathname === window.location.pathname && target.search === window.location.search && target.hash) return;
    if (!window.confirm(t('You have an unfinished sale. Leave this workspace?'))) { event.preventDefault(); event.stopPropagation(); }
}
onMounted(() => { resize(); document.addEventListener('keydown', keydown); window.addEventListener('resize', resize); document.addEventListener('click', confirmLeave, true); window.addEventListener('beforeunload', beforeUnload); });
onBeforeUnmount(() => { request?.abort(); document.removeEventListener('keydown', keydown); window.removeEventListener('resize', resize); document.removeEventListener('click', confirmLeave, true); window.removeEventListener('beforeunload', beforeUnload); document.body.classList.remove('oq-drawer-open'); });
</script>
<template>
    <div class="oq-app">
        <a class="oq-skip" href="#workspace-content">{{ t("Skip to workspace") }}</a>
        <button v-if="mobileOpen" class="oq-backdrop no-print" type="button" :aria-label="t('Close navigation')" @click="mobileOpen = false" />
        <aside id="workspace-navigation" ref="drawer" class="oq-sidebar no-print" :class="{ 'is-open': mobileOpen }" :inert="mobileViewport && !mobileOpen" :aria-label="t('Workspace navigation')">
            <a class="oq-brand" href="/pos" :aria-label="t('oneQay overview')"><span class="oq-brand-mark">{{ t("Q") }}<span /></span><span>{{ t("oneQay") }}<small>{{ t("INTELLIGENT BUSINESS OS") }}</small></span></a>
            <button class="oq-mobile-close" type="button" :aria-label="t('Close navigation')" @click="mobileOpen = false"><WorkspaceIcon name="x" /></button>
            <div class="oq-workspace"><span class="oq-workspace-icon"><WorkspaceIcon name="store" /></span><div><strong>{{ t("Merchant workspace") }}</strong><small>{{ t("Your active outlet") }}</small></div></div>
            <label class="oq-nav-search"><WorkspaceIcon name="search" :size="17" /><input v-model="search" type="search" :placeholder="t('Find a workspace')" :aria-label="t('Find a workspace')" /></label>
            <nav class="oq-nav">
                <a class="oq-nav-link" href="/pos" :aria-current="activePath === '/pos' ? 'page' : undefined"><WorkspaceIcon name="layout-dashboard" /><span>{{ t("Command Center") }}</span></a>
                <p v-if="loading" class="oq-nav-empty" role="status">{{ t("Loading navigation…") }}</p>
                <section v-for="group in groups" :key="group.key" class="oq-nav-group"><h2>{{ t(group.title) }}</h2>
                    <a v-for="item in group.items" :key="item.key" class="oq-nav-link" :href="item.url" :aria-current="isActive(item.url) ? 'page' : undefined"><WorkspaceIcon :name="labels[item.key]?.icon ?? 'clipboard-list'" /><span>{{ t(labels[item.key]?.title ?? item.title) }}</span></a>
                </section>
                <p v-if="search && groups.length === 0 && !loading" class="oq-nav-empty">{{ t("No matching workspace.") }}</p>
            </nav>
            <div class="oq-sidebar-bottom"><a class="oq-nav-link" href="/pos?panel=security#account-security"><WorkspaceIcon name="shield-check" /><span>{{ t("Account & security") }}</span></a><p>{{ t("Author by") }} <strong>{{ t("Lab | zefry") }}</strong></p></div>
        </aside>
        <div class="oq-content" :inert="mobileViewport && mobileOpen">
            <header class="oq-topbar no-print">
                <div class="oq-topbar-leading"><button ref="menu" type="button" class="oq-menu-button" :aria-label="t('Open navigation')" :aria-expanded="mobileOpen" aria-controls="workspace-navigation" @click="mobileOpen = !mobileOpen"><WorkspaceIcon name="menu" /></button><a href="/pos">{{ t("Workspace") }}</a><WorkspaceIcon name="chevron-right" :size="15" /><strong>{{ t(title) }}</strong></div>
                <div class="oq-topbar-trailing"><LanguageSwitcher /><span class="oq-session"><WorkspaceIcon name="shield-check" :size="16" />{{ t("Merchant session") }}</span><a href="/pos?panel=security#account-security" class="oq-account" :aria-label="t('Open account and security')"><WorkspaceIcon name="settings" :size="18" /><span class="oq-avatar">{{ t("Q") }}</span></a></div>
            </header>
            <div id="workspace-content" class="oq-page" tabindex="-1"><slot /></div>
            <footer class="oq-app-footer no-print"><span>{{ t("oneQay · Intelligent business management") }}</span><span>{{ t("Lab | zefry") }}</span></footer>
        </div>
    </div>
</template>
