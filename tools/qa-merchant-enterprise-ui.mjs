// Author by Lab | zefry. Compiled UI, isolated localhost fixtures; no production requests.
import assert from 'node:assert/strict';
import { createServer } from 'node:http';
import { readFile, mkdir, writeFile } from 'node:fs/promises';
import path from 'node:path';
import { pathToFileURL } from 'node:url';

const { chromium } = await import(pathToFileURL(process.env.PLAYWRIGHT_MODULE).href);
const build = path.resolve('apps/web/public/build');
const output = path.resolve('dist/merchant-ui-qa');
const dictionary = JSON.parse(await readFile('apps/web/resources/js/i18n/catalog.json', 'utf8'));
const id = (source) => dictionary[source]?.id ?? source;
await mkdir(output, { recursive: true });
const manifest = JSON.parse(await readFile(path.join(build, 'manifest.json'), 'utf8'));
const entry = manifest['resources/js/app.ts'];
assert(entry?.file && entry.css?.length, 'Actual Vite application build required');
const scope = { tenant_id: 'fixture-tenant', organization_id: 'fixture-org', outlet_id: 'fixture-outlet', device_id: 'fixture-device' };
const destinations = [
    ['catalog_inventory', 'Catalog', '/pos/catalog', 'SETUP'], ['cashier', 'Cashier', '/pos/cashier', 'SELL'],
    ['shift_start', 'Start shift', '/pos/shift/start', 'SHIFT'], ['sale_history', 'Sale history', '/pos/sales/history', 'REVIEW'],
].map(([key, title, url, category]) => ({ key, title, url, category, description: 'Authorized fixture destination' }));
const readiness = { state: 'cashier_ready', recommended_key: 'cashier', catalog_item_count: 3, sellable_item_count: 2, shift_active: true, opening_cash_ready: true };
const security = { can_change_password: false, can_rotate_password_recovery_codes: false, can_rotate_totp_recovery_codes: false, can_logout: false };
const catalog = [
    { product_id: 'fixture-coffee', display_name: 'Kopi Arabika', available_quantity: 2, unit_price: { atomic_units: '25000', currency: 'IDR', scale: 0 } },
    { product_id: 'fixture-tea', display_name: 'Teh Melati', available_quantity: 5, unit_price: { atomic_units: '15000', currency: 'IDR', scale: 0 } },
    { product_id: 'fixture-other-currency', display_name: 'Other currency fixture', available_quantity: 2, unit_price: { atomic_units: '125', currency: 'USD', scale: 2 } },
];
let navMode = 'normal', saleMode = 'success', saleRequests = [];
function inertia(url) {
    const parsed = new URL(url, 'http://localhost');
    if (parsed.pathname === '/login') return { component: 'Foundation', url, version: 'fixture-v1', props: { headline: 'oneQay application foundation' } };
    const cashier = parsed.pathname === '/pos/cashier';
    const unknown = parsed.searchParams.has('unknown');
    return { component: cashier ? 'Pos/Cashier' : 'Pos/OperationsHub', url, version: 'fixture-v1', clearHistory: false, encryptHistory: false,
        props: cashier ? { scope, active_shift_id: parsed.searchParams.has('no-shift') ? null : 'fixture-shift', catalog, sale_endpoint: '/fixture/sale', csrf_token: 'fixture-csrf', correlation_id: 'fixture-correlation' }
            : { scope, destinations, readiness: unknown ? { ...readiness, state: 'guidance_unavailable', recommended_key: null, catalog_item_count: null, sellable_item_count: null, shift_active: null, opening_cash_ready: null } : readiness, security, correlation_id: 'fixture-correlation' } };
}
const htmlEscape = (s) => s.replaceAll('&', '&amp;').replaceAll('"', '&quot;').replaceAll('<', '&lt;');
const server = createServer(async (req, res) => {
    try {
        const parsed = new URL(req.url, 'http://localhost');
        if (parsed.pathname.startsWith('/build/')) {
            const relative = parsed.pathname.slice('/build/'.length);
            assert(!relative.includes('..'));
            const file = await readFile(path.join(build, relative));
            res.writeHead(200, { 'Content-Type': relative.endsWith('.js') ? 'application/javascript' : relative.endsWith('.css') ? 'text/css' : 'application/octet-stream' });
            res.end(file); return;
        }
        if (req.method === 'POST' && parsed.pathname === '/fixture/sale') {
            let raw = ''; for await (const chunk of req) raw += chunk;
            const body = JSON.parse(raw); saleRequests.push(body);
            await new Promise((resolve) => setTimeout(resolve, 250));
            if (saleMode === 'denied') { res.writeHead(409, { 'Content-Type': 'application/json' }); res.end(JSON.stringify({ error: { code: 'SALE_DENIED' } })); return; }
            const lines = body.lines.map((line) => { const item = catalog.find((c) => c.product_id === line.product_id); const price = { atomic_units: Number(item.unit_price.atomic_units), currency: item.unit_price.currency, scale: item.unit_price.scale }; return { ...line, unit_price: price, line_total: { ...price, atomic_units: price.atomic_units * line.quantity } }; });
            const total = lines.reduce((sum, line) => sum + line.line_total.atomic_units, 0);
            res.writeHead(200, { 'Content-Type': 'application/json' });
            res.end(JSON.stringify({ status: 'completed', sale_id: 'fixture-sale', operation_id: body.operation_id, ...scope, register_context: { device_id: scope.device_id }, lines, total: { atomic_units: total, currency: body.currency, scale: body.currency_scale }, tender_category: body.tender_category, evidence_mode: 'fixture-only', change: { atomic_units: body.tendered_atomic_units - total, currency: body.currency, scale: body.currency_scale }, correlation_id: 'fixture-correlation' })); return;
        }
        if (req.headers['x-inertia'] === 'true') {
            assert.equal(req.method, 'GET'); assert.equal(parsed.pathname, '/pos'); assert.equal(req.headers['x-inertia-version'], 'fixture-v1');
            if (navMode === 'denied') { res.writeHead(403); res.end('{}'); return; }
            if (navMode === 'redirect') { res.writeHead(302, { Location: '/pos' }); res.end(); return; }
            const data = inertia('/pos');
            if (navMode === 'mismatch') data.props.scope = { ...scope, outlet_id: 'different-outlet' };
            if (navMode === 'malicious') data.props.destinations = [...destinations, { key: 'bad', title: 'External bad link', description: 'Denied fixture', category: 'SELL', url: 'https://example.invalid/pos/bad' }];
            res.writeHead(200, { 'Content-Type': 'application/json', 'X-Inertia': 'true' }); res.end(JSON.stringify(data)); return;
        }
        const data = inertia(req.url);
        res.writeHead(200, { 'Content-Type': 'text/html' });
        res.end(`<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="oneqay-country" content="ID"><meta name="oneqay-merchant-entry" content="enabled"><meta name="csrf-token" content="fixture-csrf"><script id="oneqay-merchant-login-context" type="application/json">${JSON.stringify({...scope,identity_id:'fixture-identity'})}</script><meta name="viewport" content="width=device-width, initial-scale=1">${entry.css.map((c) => `<link rel="stylesheet" href="/build/${c}">`).join('')}<script type="module" src="/build/${entry.file}"></script></head><body><script data-page="app" type="application/json">${JSON.stringify(data).replaceAll('<', '\\u003c')}</script><div id="app"></div></body></html>`);
    } catch (error) { res.writeHead(500); res.end(String(error)); }
});
await new Promise((resolve) => server.listen(0, '127.0.0.1', resolve));
const origin = `http://127.0.0.1:${server.address().port}`;
const browser = await chromium.launch({ headless: true });
const checks = [];
let page;
async function check(name, fn) { await fn(); checks.push(name); console.log(`MERCHANT_UI_${name}=PASS`); }
async function open(url = '/pos', width = 1440, height = 1000) {
    if (page) await page.close();
    page = await browser.newPage({ viewport: { width, height } });
    const errors = []; page.on('pageerror', (e) => { errors.push(String(e)); console.error('BROWSER_PAGE_ERROR', String(e)); });
    page.on('console', (message) => { if (message.type() === 'error') console.error('BROWSER_CONSOLE_ERROR', message.text()); });
    page.on('requestfailed', (request) => console.error('BROWSER_REQUEST_FAILED', request.url(), request.failure()));
    await page.goto(origin + url);
    try { await page.getByTestId('language-switcher').waitFor({ state: 'attached', timeout: 10000 }); await page.locator('main').waitFor({ timeout: 10000 }); }
    catch (error) { await page.screenshot({ path: path.join(output, 'failure.png'), fullPage: true }); console.error('BROWSER_DOM', (await page.content()).slice(-8000)); throw error; }
    await page.waitForTimeout(100);
    assert.equal(errors.length, 0, errors.join('\n'));
}
async function noOverflow() { assert.equal(await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth + 1), false, 'Horizontal overflow'); }
const product = (name) => page.locator('.product-card').filter({ hasText: name });
const payment = () => page.getByLabel(id('Nominal pembayaran'));
try {
    await check('DESKTOP_ACTUAL_READINESS', async () => { await open(); assert.equal(await page.locator('.metric-card strong').allTextContents().then((a) => a.join('|')), `3|2|${id('Active')}|${id('Recorded')}`); assert.equal(await page.locator('.oq-nav a[href="/pos/cashier"]').count(), 1); assert.equal(await page.locator('.oq-nav a[href*="correction"]').count(), 0); await noOverflow(); await page.screenshot({ path: path.join(output, 'overview-desktop.png'), fullPage: true }); });
    await check('UNKNOWN_NOT_FABRICATED', async () => { await open('/pos?unknown=1'); assert.equal(await page.locator('.metric-card strong').filter({ hasText: id('Not verified') }).count(), 4); assert.equal(await page.locator('a.guidance-link').count(), 0); });
    for (const width of [1024, 768, 390]) await check(`RESPONSIVE_${width}`, async () => { await open('/pos', width, 844); await noOverflow(); await page.screenshot({ path: path.join(output, `overview-${width}.png`), fullPage: true }); });
    await check('MOBILE_DRAWER_KEYBOARD', async () => { await open('/pos', 390, 844); assert.equal(await page.locator('.oq-sidebar').evaluate((el) => el.inert), true); await page.getByRole('button', { name: id('Open navigation'), exact: true }).click(); assert.equal(await page.locator('.oq-content').evaluate((el) => el.inert), true); const last = page.locator('.oq-sidebar a').last(); await last.focus(); await page.keyboard.press('Tab'); assert.equal(await page.evaluate(() => document.activeElement.closest('.oq-sidebar') !== null), true); await page.keyboard.press('Escape'); assert.equal(await page.getByRole('button', { name: id('Open navigation'), exact: true }).evaluate((el) => el === document.activeElement), true); assert.equal(await page.locator('.oq-sidebar').evaluate((el) => el.inert), true); });
    await check('DEEP_NAVIGATION_CURRENT_SCOPE', async () => { navMode = 'normal'; await open('/pos/cashier'); await page.locator('.oq-nav a[href="/pos/cashier"]').waitFor(); assert.equal(await page.locator('.oq-nav a[aria-current="page"]').getAttribute('href'), '/pos/cashier'); });
    for (const mode of ['denied', 'redirect', 'mismatch']) await check(`NAVIGATION_${mode.toUpperCase()}_REJECTED`, async () => { navMode = mode; await open('/pos/cashier'); await page.waitForTimeout(250); assert.equal(await page.locator('.oq-nav a').count(), 1); });
    await check('CROSS_ORIGIN_LINK_REJECTED', async () => { navMode = 'malicious'; await open('/pos/cashier'); await page.locator('.oq-nav a[href="/pos/cashier"]').waitFor(); assert.equal(await page.locator('a[href*="example.invalid"]').count(), 0); });
    navMode = 'normal';
    await check('CASHIER_STOCK_CURRENCY_TOTAL', async () => { await open('/pos/cashier'); await product('Kopi Arabika').getByRole('button', { name: id('Tambah'), exact: true }).click(); await product('Kopi Arabika').getByRole('button', { name: id('Tambah'), exact: true }).click(); assert.equal(await product('Kopi Arabika').getByRole('button', { name: id('Tambah'), exact: true }).isDisabled(), true); assert.equal(await product('Other currency fixture').getByRole('button', { name: id('Tambah'), exact: true }).isDisabled(), true); assert.match(await page.locator('.total-row strong').innerText(), /50[.,]000/); await page.screenshot({ path: path.join(output, 'cashier-desktop.png'), fullPage: true }); });
    await check('UNFINISHED_CART_NAVIGATION_CONFIRMATION', async () => { page.once('dialog', (dialog) => dialog.dismiss()); await page.locator('.oq-brand').click(); assert.equal(new URL(page.url()).pathname, '/pos/cashier'); assert.equal(await page.locator('.cart-line').count(), 1); });
    await check('CASH_UNDERPAYMENT_AND_SUCCESS_RECEIPT', async () => { saleRequests = []; await payment().fill('1'); await page.getByRole('button', { name: id('Selesaikan transaksi'), exact: true }).click(); assert.equal(saleRequests.length, 0); await page.getByText(id('Nominal pembayaran lebih kecil dari total transaksi.'), { exact: true }).waitFor(); await payment().fill('60000'); await page.getByRole('button', { name: id('Selesaikan transaksi'), exact: true }).dblclick(); await page.locator('.receipt-card').waitFor(); assert.equal(saleRequests.length, 1); assert.equal(saleRequests[0]._token, 'fixture-csrf'); assert.equal(saleRequests[0].tender_category, 'CASH'); assert.equal(saleRequests[0].tendered_atomic_units, 60000); assert.match(saleRequests[0].operation_id, /^sale:[0-9a-f-]{36}$/); assert.deepEqual(saleRequests[0].lines, [{ product_id: 'fixture-coffee', quantity: 2 }]); assert.equal(await page.locator('.cart-line').count(), 0); assert.equal(await product('Kopi Arabika').count(), 0); assert.match(await page.locator('.receipt-card').innerText(), /10[.,]000/); });
    await check('RECEIPT_PRINT_HIDES_SHELL', async () => { await page.emulateMedia({ media: 'print' }); assert.equal(await page.locator('.oq-sidebar').isVisible(), false); assert.equal(await page.locator('.oq-topbar').isVisible(), false); assert.equal(await page.locator('.receipt-card').isVisible(), true); await page.emulateMedia({ media: 'screen' }); });
    for (const mode of ['denied', 'network']) await check(`SALE_${mode.toUpperCase()}_PRESERVES_CART_NO_RETRY`, async () => { saleMode = mode; saleRequests = []; await open('/pos/cashier'); if (mode === 'network') await page.route('**/fixture/sale', async (route) => { saleRequests.push(route.request().postDataJSON()); await route.abort('failed'); }); await product('Teh Melati').getByRole('button', { name: id('Tambah'), exact: true }).click(); await page.getByRole('button', { name: id('Pas'), exact: true }).click(); await page.getByRole('button', { name: id('Selesaikan transaksi'), exact: true }).click(); await page.locator('.result').waitFor(); await page.waitForTimeout(400); assert.equal(saleRequests.length, 1); assert.equal(await page.locator('.cart-line').count(), 1); assert.equal(await page.locator('.receipt-card').count(), 0); assert.equal(await page.getByRole('button', { name: id('Selesaikan transaksi'), exact: true }).isDisabled(), false); });
    saleMode = 'success';
    await check('NO_SHIFT_CHECKOUT_DISABLED', async () => { await open('/pos/cashier?no-shift=1'); await product('Teh Melati').getByRole('button', { name: id('Tambah'), exact: true }).click(); assert.equal(await page.getByRole('button', { name: id('Selesaikan transaksi'), exact: true }).isDisabled(), true); });
    await check('CASHIER_MOBILE', async () => { await open('/pos/cashier', 390, 844); await product('Teh Melati').getByRole('button', { name: id('Tambah'), exact: true }).click(); await noOverflow(); await page.screenshot({ path: path.join(output, 'cashier-mobile.png'), fullPage: true }); });

    await check('INDONESIAN_DEFAULT_ENGLISH_BROWSER', async () => {
        await open(); assert.equal(await page.locator('html').getAttribute('lang'), 'id');
        assert.equal(await page.getByTestId('language-switcher').inputValue(), 'id');
        assert.equal(await page.getByRole('heading', { name: id('Your workspaces'), exact: true }).count(), 1);
    });
    await check('ENGLISH_SWITCH_PERSISTENCE_AND_SEARCH', async () => {
        await page.getByTestId('language-switcher').selectOption('en');
        await page.getByRole('heading', { name: 'Your workspaces', exact: true }).waitFor();
        assert.equal(await page.locator('html').getAttribute('lang'), 'en');
        await page.getByLabel('Find a workspace').fill('cashier');
        assert.equal(await page.locator('.oq-nav-group .oq-nav-link').count(), 1);
        await page.reload(); await page.getByRole('heading', { name: 'Your workspaces', exact: true }).waitFor();
        assert.equal(await page.getByTestId('language-switcher').inputValue(), 'en');
        await page.screenshot({path:path.join(output,'overview-english.png'),fullPage:true});
    });
    await check('LANGUAGE_SWITCH_PRESERVES_CART_AND_PAYMENT', async () => {
        await open('/pos/cashier'); await product('Teh Melati').getByRole('button',{name:id('Tambah'),exact:true}).click();
        await payment().fill('20000'); const total = await page.locator('.total-row strong').innerText();
        await page.getByTestId('language-switcher').selectOption('en');
        assert.equal(await page.locator('.cart-line').count(),1);
        assert.equal(await page.locator('.total-row strong').innerText(),total);
        assert.equal(await page.getByLabel(dictionary['Nominal pembayaran'].en).inputValue(),'20000');
        assert.equal(await page.getByRole('button',{name:'Complete sale',exact:true}).count(),1);
        assert.match(await page.locator('.cart-line').innerText(),/Teh Melati/);
        saleRequests=[]; await page.getByRole('button',{name:'Complete sale',exact:true}).click(); await page.locator('.receipt-card').waitFor();
        assert.equal(saleRequests.length,1); assert.equal(saleRequests[0].tender_category,'CASH'); assert.equal(saleRequests[0]._token,'fixture-csrf');
        assert.deepEqual(saleRequests[0].lines,[{product_id:'fixture-tea',quantity:1}]); assert.equal(saleRequests[0].tendered_atomic_units,20000);
    });
    await check('LOGIN_SWITCH_WITHOUT_CREDENTIAL_LOSS', async () => {
        await open('/login',390,844); await page.getByLabel(id('Password'),{exact:true}).fill('fixture-only-not-a-credential');
        await page.getByTestId('language-switcher').selectOption('en');
        assert.equal(await page.getByLabel('Password',{exact:true}).inputValue(),'fixture-only-not-a-credential');
        await page.getByRole('heading',{name:'Sign in',exact:true}).waitFor(); await noOverflow();
        await page.getByTestId('language-switcher').selectOption('id');
        await page.getByRole('heading',{name:id('Sign in'),exact:true}).waitFor();
        await page.getByLabel(id('Password'),{exact:true}).fill('');
        await page.screenshot({path:path.join(output,'login-indonesian-mobile.png'),fullPage:true});
    });
    await check('INVALID_OR_BLOCKED_PREFERENCE_FALLS_BACK', async () => {
        await page.evaluate(()=>localStorage.setItem('oneqay.locale','fr')); await page.reload();
        await page.getByTestId('language-switcher').waitFor(); assert.equal(await page.locator('html').getAttribute('lang'),'id');
        await page.addInitScript(()=>Object.defineProperty(window,'localStorage',{get(){throw new Error('fixture blocked')}}));
        await page.reload(); await page.getByTestId('language-switcher').waitFor();
        assert.equal(await page.locator('html').getAttribute('lang'),'id');
        await page.getByTestId('language-switcher').selectOption('en');
        await page.getByRole('heading',{name:'Sign in',exact:true}).waitFor();
    });
    await check('ENGLISH_MOBILE_NO_OVERFLOW', async () => {
        await open('/pos',390,844); await page.getByTestId('language-switcher').selectOption('en'); await noOverflow();
        await page.screenshot({path:path.join(output,'overview-english-mobile.png'),fullPage:true});
    });
    await writeFile(path.join(output, 'results.json'), JSON.stringify({ state: 'PASS', checks, fixture_only: true, production_requests: 0, source: process.env.GITHUB_SHA ?? null }, null, 2));
} finally { await browser.close(); await new Promise((resolve) => server.close(resolve)); }
