// Author by Lab | zefry. Browser fixtures complement the separate real SQL integration tests.
import assert from 'node:assert/strict';
import { createServer } from 'node:http';
import { readFile, mkdir, writeFile } from 'node:fs/promises';
import path from 'node:path';
import { pathToFileURL } from 'node:url';
const { chromium } = await import(pathToFileURL(process.env.PLAYWRIGHT_MODULE).href);
const build = path.resolve('apps/web/public/build'), output = path.resolve('dist/business-ui-qa');
await mkdir(output, { recursive: true });
const dictionary = JSON.parse(await readFile('apps/web/resources/js/i18n/catalog.json', 'utf8'));
const id = text => dictionary[text]?.id ?? text;
const manifest = JSON.parse(await readFile(path.join(build, 'manifest.json'), 'utf8'));
const entry = manifest['resources/js/app.ts']; assert(entry?.file);
const scope = { tenant_id: 'fixture-tenant', organization_id: 'fixture-org', outlet_id: 'fixture-outlet', device_id: 'fixture-device' };
const names = { suppliers: 'Suppliers', purchases: 'Purchasing', customers: 'Customers', finance: 'Finance', people: 'People', audit: 'Activity log', access: 'Business access' };
const modules = Object.entries(names).map(([key, title]) => ({ key: `business_${key}`, title, description: title, url: `/pos/business/${key}`, category: 'MANAGE' }));
const empty = { data: [], page: 1, has_more: false };
const requests = []; let ambiguous = true;
function inertia(url) {
    const parsed = new URL(url, 'http://localhost'), module = parsed.pathname.split('/').pop();
    const props = module === 'access'
        ? { scope, identity_id: 'fixture-admin', outlet_id: scope.outlet_id, templates: { 'oneqay-business-manager': { title: 'Business manager', permissions: ['business.finance.view','business.purchases.view'] } }, step_up_url: '/fixture/step-up' }
        : { scope, module, title: names[module], records: empty, actions: ['supplier.save','customer.save','purchase.create','invoice.create','journal.post','employee.save'], modules, search: '', suppliers: [], partners: [], receipts: empty, journals: module === 'finance' ? empty : undefined, attendance: module === 'people' ? empty : undefined, leave: module === 'people' ? empty : undefined, accounts: {'1000':['Cash','ASSET'],'3000':['Equity','EQUITY']}, balances: [], from: '2026-01-01', to: '2026-10-12', correlation_id: 'fixture-only' };
    return { component: parsed.pathname === '/pos' ? 'Pos/OperationsHub' : module === 'access' ? 'Business/Access' : 'Business/Workspace', props: parsed.pathname === '/pos' ? {scope,destinations:modules} : props, url, version: 'business-fixture', clearHistory: false, encryptHistory: false };
}
const server = createServer(async (req,res) => {
    try {
        const parsed = new URL(req.url,'http://localhost');
        if (parsed.pathname.startsWith('/build/')) {
            const file = parsed.pathname.slice(7); assert(!file.includes('..'));
            res.writeHead(200, {'Content-Type': file.endsWith('.js')?'application/javascript':file.endsWith('.css')?'text/css':'application/octet-stream'});res.end(await readFile(path.join(build,file)));return;
        }
        if (req.method === 'POST') {
            let raw='';for await(const chunk of req) raw+=chunk;
            requests.push({url:parsed.pathname,body:JSON.parse(raw)});
            assert.equal(req.headers['x-csrf-token'],'fixture-csrf');
            res.writeHead(ambiguous?503:200,{'Content-Type':'application/json'});res.end(JSON.stringify(ambiguous?{error:{code:'FIXTURE_RETRY'}}:{status:'saved',record:{id:'fixture-record'}}));return;
        }
        const data=inertia(req.url);
        if(req.headers['x-inertia']==='true'){res.writeHead(200,{'Content-Type':'application/json','X-Inertia':'true'});res.end(JSON.stringify(data));return;}
        res.writeHead(200,{'Content-Type':'text/html'});
        res.end(`<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="csrf-token" content="fixture-csrf"><meta name="viewport" content="width=device-width,initial-scale=1">${(entry.css??[]).map(f=>`<link rel="stylesheet" href="/build/${f}">`).join('')}<script type="module" src="/build/${entry.file}"></script></head><body><script data-page="app" type="application/json">${JSON.stringify(data).replaceAll('<','\\u003c')}</script><div id="app"></div></body></html>`);
    } catch(e){res.writeHead(500);res.end(String(e));}
});
await new Promise(resolve=>server.listen(0,'127.0.0.1',resolve));
const origin=`http://127.0.0.1:${server.address().port}`;
const browser=await chromium.launch({headless:true});const page=await browser.newPage({viewport:{width:1440,height:1000}});
const errors=[];page.on('pageerror',error=>errors.push(String(error)));const checks=[];
async function check(name,work){await work();checks.push(name);console.log(`BUSINESS_UI_${name}=PASS`);}
async function open(module){await page.goto(`${origin}/pos/business/${module}`);await page.getByRole('heading',{level:1,name:id(names[module]),exact:true}).waitFor();}
try {
    await check('ALL_SEVEN_PAGES_DEFAULT_INDONESIAN',async()=>{
        for(const module of Object.keys(names)){await open(module);assert.equal(await page.getByTestId('language-switcher').inputValue(),'id');}
    });
    await check('SUPPLIER_FORM_STABLE_RETRY',async()=>{
        await open('suppliers');await page.getByRole('button',{name:`+ ${id('Add supplier')}`,exact:true}).click();
        const dialog=page.getByRole('dialog');await dialog.getByLabel(id('Code'),{exact:true}).fill('SUP-001');await dialog.getByLabel(id('Name'),{exact:true}).fill('Pemasok Nusantara');
        await dialog.getByRole('button',{name:id('Confirm and save'),exact:true}).click();
        await dialog.getByText(id('The result is not confirmed. Retry the same request to avoid duplicate records.'),{exact:true}).waitFor();
        assert.equal(await dialog.getByLabel(id('Name'),{exact:true}).isDisabled(),true);
        ambiguous=false;await dialog.getByRole('button',{name:id('Retry same request'),exact:true}).click();await dialog.waitFor({state:'hidden'});
        assert.deepEqual(requests[0],requests[1]);assert.equal(requests[0].url,'/pos/business/commands/supplier.save');assert.equal(requests[0].body.data.name,'Pemasok Nusantara');
    });
    await check('ENGLISH_SWITCH_AND_MOBILE_LAYOUT',async()=>{
        await page.getByTestId('language-switcher').selectOption('en');await page.getByRole('heading',{level:1,name:'Suppliers',exact:true}).waitFor();
        await page.getByTestId('language-switcher').selectOption('id');await page.setViewportSize({width:390,height:844});
        for(const module of ['purchases','finance','people','access']){await open(module);assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth+1),false,`${module} overflow`);await page.screenshot({path:path.join(output,`${module}-mobile.png`),fullPage:true});}
    });
    await check('ADMINISTRATOR_ROLE_FORM',async()=>{
        await page.getByLabel(id('Team member identity ID'),{exact:true}).fill('fixture-member');
        await page.getByRole('button',{name:id('Save access'),exact:true}).click();await page.getByText(id('Business access saved. Open the workspace to refresh the available modules.'),{exact:false}).waitFor();
        const request=requests.at(-1);assert.equal(request.url,'/pos/business/access/roles');assert.equal(request.body.identity_id,'fixture-member');assert.equal(request.body.role,'oneqay-business-manager');assert.equal(request.body.action,'assign');
    });
    assert.deepEqual(errors,[]);await writeFile(path.join(output,'result.json'),JSON.stringify({state:'PASS',checks,fixtureOnly:true},null,2));
} catch(e){await page.screenshot({path:path.join(output,'failure.png'),fullPage:true});console.error('Browser errors:',errors);throw e;}
finally{await browser.close();await new Promise(resolve=>server.close(resolve));}
