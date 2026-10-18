// Author by Lab | zefry. Only an interface preference is stored in this browser.
import { readonly, ref } from 'vue';
import catalogData from './catalog.json';
import messages from './messages.json';

export type Locale = 'id' | 'en';
const preferenceKey = 'oneqay.locale';
const current = ref<Locale>('id');
export const locale = readonly(current);
const catalog: Record<string, Record<Locale, string>> = catalogData;
const valid = (value: unknown): value is Locale => value === 'id' || value === 'en';
const patterns = messages.map(([source, id, en]) => ({
  expression: new RegExp('^' + source.split('{value}').map((part) => part.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')).join('([\\s\\S]*?)') + '$'),
  id, en,
}));

export function setLocale(value: unknown): void {
  current.value = valid(value) ? value : 'id';
  if (typeof document !== 'undefined') { document.documentElement.lang = current.value; document.cookie = `oneqay_locale=${current.value}; Path=/; SameSite=Lax; Max-Age=31536000${location.protocol === 'https:' ? '; Secure' : ''}`; }
  try { window.localStorage.setItem(preferenceKey, current.value); } catch { /* In-memory choice still works. */ }
}

export function initializeLocale(): void {
  let saved: unknown;
  try { saved = window.localStorage.getItem(preferenceKey); } catch { /* Use automatic detection. */ }
  const country = document.querySelector<HTMLMetaElement>('meta[name="oneqay-country"]')?.content;
  let zone = ''; try { zone = Intl.DateTimeFormat().resolvedOptions().timeZone; } catch { /* Language fallback. */ }
  const indonesian = /^(id)(-|$)/i.test(navigator.languages?.[0] ?? navigator.language ?? '') || ['Asia/Jakarta','Asia/Pontianak','Asia/Makassar','Asia/Jayapura'].includes(zone);
  current.value = valid(saved) ? saved : country ? (country === 'ID' ? 'id' : 'en') : indonesian ? 'id' : 'en';
  document.documentElement.lang = current.value;
}

export function t(value: string | number | null | undefined): string {
  const source = value == null ? '' : String(value);
  const selected = current.value;
  if (Object.prototype.hasOwnProperty.call(catalog, source)) return catalog[source][selected];
  for (const pattern of patterns) {
    const match = source.match(pattern.expression);
    if (match) return pattern[selected].replace('{value}', () => match[1]);
  }
  return source;
}
