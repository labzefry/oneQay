// Author by Lab | zefry. Integer decimal formatting; persisted currency is server-owned.
import { ref } from 'vue';
import { locale } from './i18n';
export type CurrencyPreferences = { business_name: string; currency: string; symbol: string; scale: number; version: number };
export const currencyPreferences = ref<CurrencyPreferences>({ business_name:'oneQay',currency:'IDR',symbol:'Rp',scale:0,version:0 });
export function setCurrencyPreferences(value: unknown): void {
    if (!value || typeof value !== 'object') return;
    const v=value as CurrencyPreferences;
    if (/^[A-Z]{3}$/.test(v.currency) && typeof v.symbol==='string' && v.symbol.length<=16 && Number.isInteger(Number(v.scale))) currencyPreferences.value={...v,scale:Number(v.scale),version:Number(v.version)};
}
export function formatMoney(atomic: unknown,currency='IDR',scale=0): string {
    const raw=String(atomic??'0');if(!/^-?\d+$/.test(raw)||!Number.isInteger(scale)||scale<0||scale>6)return '—';
    const negative=raw.startsWith('-');const v=(negative?raw.slice(1):raw).padStart(scale+1,'0');const major=scale?v.slice(0,-scale):v;
    const grouped=major.replace(/\B(?=(\d{3})+(?!\d))/g,locale.value==='id'?'.':',');
    const symbol=currencyPreferences.value.currency===currency?currencyPreferences.value.symbol:currency;
    return `${negative?'-':''}${symbol} ${grouped}${scale?(locale.value==='id'?',':'.')+v.slice(-scale):''}`;
}
export function majorAmount(value: unknown,scale: number): string {const v=String(value??'0').padStart(scale+1,'0');return scale?`${v.slice(0,-scale)}.${v.slice(-scale)}`:v;}
