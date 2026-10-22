// Author by Lab | zefry. Guard only explicitly migrated owned UI styles; legacy files are not certified.
import { readFileSync } from 'node:fs';
import { resolve } from 'node:path';
const root = resolve(import.meta.dirname, '..');
const palette = JSON.parse(readFileSync(resolve(root, '../../docs/design/ONEQAY_UI_PALETTE_2026-10-21.json'), 'utf8'));
const allowed = Object.values(palette.colors).map(c => c.toUpperCase());
if (allowed.length !== 7 || new Set(allowed).size !== 7) throw Error('Expected exactly seven owner-approved colors');
const files = [
  ['resources/js/components/DesktopSyncPanel.vue', true],
  ['resources/js/components/BusinessDataResetPanel.vue', true],
  ['resources/css/app.css', false],
  ['resources/js/pages/Business/DesktopSettings.vue', true],
  ['resources/js/layouts/LocalizedShell.vue', true],
  ['resources/js/pages/Platform/Control.vue', true],
  ['resources/css/website.css', false],
  ['resources/js/pages/Pos/Cashier.vue', true],
  ['resources/js/pages/Pos/ShiftStart.vue', true],
  ['resources/js/pages/Pos/ShiftClose.vue', true],
  ['resources/js/pages/Business/Access.vue', true],
  ['resources/js/pages/Business/Workspace.vue', true],
  ['resources/js/pages/Platform/Login.vue', true],
  ['resources/js/pages/Website/Register.vue', true],
  ['resources/js/pages/Platform/Activate.vue', true],
];
let inspected = 0;
for (const [path, scoped] of files) {
  const source = readFileSync(resolve(root, path), 'utf8');
  const css = scoped ? [...source.matchAll(/<style[^>]*>([\s\S]*?)<\/style>/g)].map(m => m[1]).join('\n') : source;
  if (!css) throw Error('Missing migrated stylesheet: ' + path);
  const literals = css.match(/#[0-9a-fA-F]{3,8}\b/g) ?? [];
  for (const literal of literals) {
    const value = literal.toUpperCase();
    if (!allowed.includes(value) && !(value.length === 9 && allowed.some(base => value.startsWith(base)))) {
      throw Error(path + ': unapproved color ' + value);
    }
  }
  if (/(?:rgba?|hsla?)\s*\(/i.test(css)) throw Error(path + ': direct RGB/HSL not allowed');
  inspected += literals.length;
}
const app = readFileSync(resolve(root, 'resources/css/app.css'), 'utf8');
for (const color of allowed) if (!app.includes(color)) throw Error('Missing canonical palette value ' + color);
if ((app.match(/:root\s*\{/g) ?? []).length !== 1) throw Error('Use exactly one root token declaration');
console.log('Palette guard passed: 7 colors, ' + files.length + ' migrated stylesheets, ' + inspected + ' approved tokens/alphas.');
