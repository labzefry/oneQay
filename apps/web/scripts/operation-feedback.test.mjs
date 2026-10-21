// Author by Lab | zefry. Exercise real composable transitions using synthetic responses only.
import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync, writeFileSync, unlinkSync } from 'node:fs';
import { resolve } from 'node:path';
import { pathToFileURL } from 'node:url';
import ts from 'typescript';
import { createSSRApp, h } from 'vue';
import { renderToString } from 'vue/server-renderer';
const source = resolve(import.meta.dirname, '../resources/js/use-business-mutation.ts');
const temp = resolve(import.meta.dirname, '../resources/js/__operation-contract-test.mjs');
const output = ts.transpileModule(readFileSync(source, 'utf8'), { compilerOptions: { module: ts.ModuleKind.ESNext, target: ts.ScriptTarget.ES2022 } }).outputText;
writeFileSync(temp, output);
const { useBusinessMutation } = await import(pathToFileURL(temp).href);
unlinkSync(temp);
let redirects = [];
Object.defineProperty(globalThis, 'navigator', { configurable: true, value: { onLine: true } });
globalThis.document = { querySelector: () => ({ content: 'synthetic-csrf' }) };
globalThis.window = { location: { assign: url => redirects.push(url) } };
const reply = (status, data) => ({ status, ok: status >= 200 && status < 300, json: async () => data });
async function mount() {
    let operation;
    await renderToString(createSSRApp({ setup() { operation = useBusinessMutation(); return () => h('div'); } }));
    return operation;
}
test('confirmed save reaches terminal success and prevents duplicate submit', async () => {
    const op = await mount(); let writes = 0;
    globalThis.fetch = async () => { writes++; return reply(200, { status: 'saved', record: { id: 'synthetic' } }); };
    assert.equal((await op.submit('/test', { key: 1 })).id, 'synthetic');
    assert.equal(op.status.value, 'succeeded');
    assert.equal(await op.submit('/test', { key: 1 }), null);
    assert.equal(writes, 1);
});
test('422 rejected result is terminal failure rather than uncertain', async () => {
    const op = await mount(); globalThis.fetch = async () => reply(422, { error: { code: 'INVALID_FIELDS' } });
    assert.equal(await op.submit('/test', { key: 1 }), null);
    assert.equal(op.status.value, 'failed');
    assert.equal(op.uncertain.value, false);
});
test('500 unknown preserves exact operation identity on manual retry', async () => {
    const op = await mount(); const ids = []; let calls = 0;
    globalThis.fetch = async (_url, args) => {
        ids.push(JSON.parse(args.body).operation_id); calls++;
        return calls === 1 ? reply(500, {}) : reply(200, { record: { ok: true } });
    };
    assert.equal(await op.submit('/test', { key: 1 }), null);
    assert.equal(op.status.value, 'unknown');
    assert.deepEqual(await op.submit('/test', { key: 2 }), null);
    assert.equal(calls, 1);
    assert.deepEqual(await op.submit('/test', { key: 1 }), { ok: true });
    assert.equal(ids.length, 2); assert.equal(ids[0], ids[1]);
});
test('202 queued never masquerades as saved', async () => {
    const op = await mount(); let writes = 0; globalThis.fetch = async () => { writes++; return reply(202, { status: 'queued' }); };
    await op.submit('/test', { key: 1 });
    assert.equal(op.status.value, 'queued'); assert.equal(op.uncertain.value, true);
    await op.submit('/test', { key: 1 }); assert.equal(writes, 1);
});
test('network abort is unknown, not failed or successful', async () => {
    const op = await mount(); globalThis.fetch = async () => { const e = Error('Synthetic abort'); e.name = 'AbortError'; throw e; };
    await op.submit('/test', { key: 1 });
    assert.equal(op.status.value, 'unknown'); assert.equal(op.busy.value, false);
});
test('401 prompts a new business session', async () => {
    const op = await mount(); redirects = [];
    globalThis.fetch = async () => reply(401, { error: { code: 'SESSION_AUTHORITY_DENIED' } });
    await op.submit('/test', { key: 1 });
    assert.equal(op.status.value, 'session-expired');
    assert.deepEqual(redirects, ['/business-login?reason=session-expired']);
});
test('second click while pending does not create a second write', async () => {
    const op = await mount(); let settle; let writes = 0;
    globalThis.fetch = () => { writes++; return new Promise(resolve => { settle = resolve; }); };
    const pending = op.submit('/test', { key: 1 });
    assert.equal(await op.submit('/test', { key: 1 }), null);
    assert.equal(writes, 1);
    settle(reply(200, { record: { ok: true } }));
    await pending; assert.equal(op.status.value, 'succeeded');
});
