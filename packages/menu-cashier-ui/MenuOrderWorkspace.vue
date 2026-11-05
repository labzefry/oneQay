<script setup lang="ts">
// Author by Lab | zefry. Privileged cashier QR inbox; no silent payment.
import { computed, ref } from 'vue';
import type { Component } from 'vue';
import MemberPicker from './MemberPicker.vue';
type Line = {product_id:string;product_name:string;quantity:number;unit_price_atomic:string;line_total_atomic:string};
type InboxOrder = {order_id:string;status:string;customer_name:string;table_label:string|null;total_atomic:string;currency:string;currency_scale:number;created_at:number;lines:Line[]};
type Quote = {order_id:string;status:string;total_atomic:string;previous_total_atomic:string;currency:string;currency_scale:number;lines:{product_id:string;quantity:number}[]};
const props=defineProps<{
 settlement_enabled:boolean;
 online?:boolean;
 membership_enabled?:boolean;
 language:string;
 icon:Component;
 send:(action:'lookup'|'quote'|'settle'|'cancel'|'member-lookup'|'member-register', body:Record<string,unknown>)=>Promise<any>;
 formatMoney:(value:string,currency:string,scale:number)=>string;
}>();
const emit=defineEmits<{paid:[]}>();
const label=(id:string,en:string)=>props.language==='id'?id:en;
const mode=ref<'qr'|'phone'>('qr');
const searchTerm=ref('');
const busy=ref(false);
const memberBusy=ref(false);
const operationBusy=computed(()=>busy.value||memberBusy.value);
const uncertain=ref(false);
const actionCode=(e:unknown)=>e instanceof Error?e.message:String(e);
const error=ref('');
const notice=ref('');
const matches=ref<InboxOrder[]>([]);
const current=ref<InboxOrder|null>(null);
const member=ref<{id:string;name:string;status:'cashier_enrolled'}|null>(null);
const amounts=ref<Record<string,number>>({});
const quote=ref<Quote|null>(null);
const tender=ref<'CASH'|'MANUAL_EXTERNAL'>('CASH');
const tendered=ref('');
const priceConfirmed=ref(false);
let settlementOperation='';
let sentPayment:Record<string,unknown>|null=null;
const canSettle=computed(()=>!!current.value&&!!quote.value&&props.settlement_enabled&&props.online!==false&&!operationBusy.value&&(uncertain.value?sentPayment!==null:priceConfirmed.value));
const canQuote=computed(()=>current.value!==null && current.value.lines.some(l=>(amounts.value[l.product_id]||0)>0));
const selectedLines=computed(()=>current.value?.lines.filter(l=>(amounts.value[l.product_id]||0)>0).map(l=>({product_id:l.product_id,quantity:amounts.value[l.product_id]}))||[]);
const currency=computed(()=>quote.value?.currency||current.value?.currency||'IDR');
const scale=computed(()=>quote.value?.currency_scale??current.value?.currency_scale??0);
function paymentDisplay(atomic:string, decimals:number):string {
 if (!decimals) return atomic;
 const padded=atomic.padStart(decimals+1,'0');
 return padded.slice(0,-decimals)+'.'+padded.slice(-decimals);
}
function paymentAtomic(value:string, decimals:number):string|null {
 const match=/^(0|[1-9][0-9]{0,12})(?:[.,]([0-9]{1,6}))?$/.exec(value.trim());
 if (!match || (match[2]?.length??0)>decimals) return null;
 return (match[1]+(match[2]??'').padEnd(decimals,'0')).replace(/^0+(?=\d)/,'');
}
const money=(value:string)=>props.formatMoney(value,currency.value,scale.value);
function newOp(){return crypto.randomUUID().replace(/-/g,'');}
function resetQuote(){quote.value=null;priceConfirmed.value=false;settlementOperation='';sentPayment=null;error.value='';notice.value='';}
function choose(order:InboxOrder){
 member.value=null;current.value=order;amounts.value=Object.fromEntries(order.lines.map(line=>[line.product_id,line.quantity]));
 matches.value=[];resetQuote();tendered.value=paymentDisplay(order.total_atomic,order.currency_scale);searchTerm.value='';
}
async function lookup(){
 if(operationBusy.value||uncertain.value||props.online===false)return;
 busy.value=true;error.value='';notice.value='';current.value=null;quote.value=null;matches.value=[];
 try {
  const term=searchTerm.value.trim();
  if(!term)throw new Error('ORDER_LOOKUP_REQUIRED');
  const data=await props.send('lookup',{kind:mode.value,[mode.value==='qr'?'token':'phone']:term});
  const orders:InboxOrder[]=mode.value==='qr'?(data.order?[data.order]:[]):(data.orders||[]);
  if(!orders.length){notice.value=label('Tidak ditemukan pesanan tertunda dalam cakupan outlet ini.','No pending order found for this outlet.');return;}
  if(orders.length===1){choose(orders[0]);return;}
  matches.value=orders;
 }catch(e){error.value=typeof e==='string'?e:e instanceof Error?e.message:'ORDER_LOOKUP_FAILED';}
 finally{busy.value=false;}
}
function setQuantity(product:string,value:number){
 if(operationBusy.value||uncertain.value||!current.value)return;
 const maximum=current.value.lines.find(l=>l.product_id===product)?.quantity||0;
 amounts.value={...amounts.value,[product]:Math.max(0,Math.min(maximum,Math.trunc(value)))};
 resetQuote();
}
async function loadQuote(){
 if(!current.value||!canQuote.value||operationBusy.value||uncertain.value||props.online===false)return;
 busy.value=true;error.value='';notice.value='';priceConfirmed.value=false;
 try{
  const result=await props.send('quote',{order_id:current.value.order_id,selected_lines:selectedLines.value});
  quote.value=result.quote;tendered.value=paymentDisplay(result.quote.total_atomic,result.quote.currency_scale);settlementOperation='';
 }catch(e){quote.value=null;error.value=typeof e==='string'?e:e instanceof Error?e.message:'ORDER_QUOTE_FAILED';}
 finally{busy.value=false;}
}
function onTenderChanged(){
 priceConfirmed.value=false;settlementOperation='';
 if(tender.value==='MANUAL_EXTERNAL'&&quote.value)tendered.value=paymentDisplay(quote.value.total_atomic,quote.value.currency_scale);
}
function onAmountChanged(){priceConfirmed.value=false;settlementOperation='';}
function selectMember(selected:typeof member.value){
 if(busy.value||uncertain.value)return;
 member.value=selected;priceConfirmed.value=false;
}
async function settle(){
 if(!canSettle.value||!current.value||!quote.value)return;
 const retrying=uncertain.value;
 const tenderedAtomic=retrying?null:paymentAtomic(tendered.value,quote.value.currency_scale);
 if(!retrying&&tenderedAtomic===null){error.value='INVALID_NUMBER';return;}
 busy.value=true;error.value='';notice.value='';
 if(!settlementOperation)settlementOperation=newOp();
 try{
  if(!retrying) sentPayment={
   order_id:current.value.order_id,operation_id:settlementOperation,tender_category:tender.value,
   tendered_atomic:tenderedAtomic,expected_total_atomic:quote.value.total_atomic,selected_lines:selectedLines.value,
   ...(member.value?{member_id:member.value.id}:{}),
  };
  if(!sentPayment)throw new Error('ORDER_PAYMENT_PAYLOAD_MISSING');
  const result=await props.send('settle',sentPayment);
  emit('paid');
  notice.value=label('Pembayaran tercatat pada struk POS ','Payment recorded in POS receipt ')+result.settlement.sale_id;
  uncertain.value=false;current.value=null;matches.value=[];
  const paidNotice=notice.value;resetQuote();notice.value=paidNotice;
 }catch(e){
  const code=actionCode(e);error.value=code;
  // An unacknowledged write may already have committed. Retry the same request only.
  const knownRejection=/^(ORDER_(PRICE_CHANGED|PRODUCT_UNAVAILABLE|CURRENCY_CHANGED|TENDER_INVALID|POS_SALE_REJECTED|NOT_PENDING)|MEMBER_(NOT_FOUND|NOT_ACTIVE)|INVALID_NUMBER|POS_MENU_(SHIFT_SYNC_REQUIRED|ONLINE_REQUIRED))(?:$| · )/.test(code);
  // A local retry rejection says nothing about the previous server attempt.
  const localRejection=code.startsWith('POS_MENU_');
  uncertain.value=!knownRejection||(retrying&&localRejection);
  if(!uncertain.value){quote.value=null;priceConfirmed.value=false;settlementOperation='';sentPayment=null;}
 }
 finally{busy.value=false;}
}
async function cancel(){
 if(!current.value||operationBusy.value||uncertain.value||props.online===false)return;
 if(!window.confirm(label('Batalkan pesanan yang belum dibayar?','Cancel this unpaid order?')))return;
 busy.value=true;error.value='';notice.value='';
 try{
  await props.send('cancel',{order_id:current.value.order_id,operation_id:newOp()});
  current.value=null;quote.value=null;notice.value=label('Pesanan belum dibayar telah dibatalkan.','Unpaid order cancelled.');
 }catch(e){error.value=typeof e==='string'?e:e instanceof Error?e.message:'ORDER_CANCEL_FAILED';}
 finally{busy.value=false;}
}
</script>
<template>
 <main class="qr-inbox">
  <header><div><p class="kicker">oneQay · POS</p><h1>{{label('Pesanan QR Pelanggan','Guest QR Orders')}}</h1><p>{{label('Hanya pesanan di outlet aktif. Cari, konfirmasi harga terkini, lalu catat pembayaran pada POS.','Only orders at your active outlet. Look up, confirm current prices and record payment in POS.')}}</p></div><slot name="actions"/></header>
  <section class="panel"><h2>{{label('Cari pesanan tertunda','Find pending order')}}</h2>
   <div class="search-row"><label><span>{{label('Metode pencarian','Lookup method')}}</span><select v-model="mode" :disabled="operationBusy||uncertain||online===false"><option value="qr">{{label('Token QR','QR token')}}</option><option value="phone">{{label('Telepon tepat (+62...)','Exact phone (+62...)')}}</option></select></label><label class="search-input"><span>{{mode==='qr'?label('Kode hasil scan QR','Scanned QR code'):label('Nomor telepon','Phone number')}}</span><input v-model="searchTerm" @keydown.enter.prevent="lookup" :maxlength="mode==='qr'?64:17" :placeholder="mode==='qr'?label('Pindai atau tempel kode','Scan or paste the code'):'+628123456789'" autocomplete="off" :disabled="operationBusy||uncertain||online===false"/></label><button type="button" :disabled="operationBusy||uncertain||online===false" @click="lookup"><component :is="icon" name="search" :size="18"/>{{label('Cari','Search')}}</button></div>
   <p class="hint">{{label('Pencarian QR hanya berlaku selama token aktif; pencarian telepon dibatasi pesanan tertunda dalam 24 jam.','QR lookup requires a live token; exact-phone search is limited to pending orders from the past 24 hours.')}}</p>
  </section>
  <section v-if="matches.length" class="panel"><h2>{{label('Pilih pesanan','Select an order')}}</h2><button v-for="entry in matches" :key="entry.order_id" type="button" class="match" @click="choose(entry)"><strong>{{entry.customer_name}}</strong><span>{{entry.table_label||label('Tanpa nomor meja','No table')}} · {{formatMoney(entry.total_atomic,entry.currency,entry.currency_scale)}}</span></button></section>
  <section v-if="current" class="panel"><div class="between"><div><p class="kicker">{{label('MENUNGGU PEMBAYARAN','AWAITING PAYMENT')}}</p><h2>{{current.customer_name}}</h2><p>{{current.table_label||label('Meja tidak ditentukan','No table specified')}} · {{new Date(current.created_at*1000).toLocaleString(language==='id'?'id-ID':'en-US')}}</p></div><button type="button" class="secondary" :disabled="operationBusy||uncertain||online===false" @click="cancel">{{label('Batalkan pesanan','Cancel unpaid order')}}</button></div>
   <p class="hint">{{label('Kasir dapat mengurangi jumlah atau menghapus item. Menambah item di luar QR tidak diperbolehkan.','Cashier may reduce or remove items, but cannot add items not present in the QR order.')}}</p>
   <div v-for="line in current.lines" :key="line.product_id" class="line"><div><strong>{{line.product_name}}</strong><p>{{money(line.unit_price_atomic)}} · {{label('Dipesan','Ordered')}} {{line.quantity}}</p></div><div class="quantity"><button type="button" :disabled="operationBusy||uncertain||(amounts[line.product_id]||0)===0" @click="setQuantity(line.product_id,(amounts[line.product_id]||0)-1)">−</button><span>{{amounts[line.product_id]||0}}</span><button type="button" :disabled="operationBusy||uncertain||(amounts[line.product_id]||0)>=line.quantity" @click="setQuantity(line.product_id,(amounts[line.product_id]||0)+1)">+</button></div></div>
   <button type="button" class="primary" :disabled="operationBusy||uncertain||online===false||!canQuote" @click="loadQuote"><component :is="icon" name="calculator" :size="18"/>{{label('Periksa harga dan stok terkini','Recalculate current price and stock')}}</button>
   <MemberPicker v-if="membership_enabled" :language="language" :icon="icon" :selected="member" :disabled="busy||uncertain||online===false" :send="send" @busy-change="memberBusy=$event" @select="selectMember"/>
   <div v-if="quote" class="quote"><h3>{{label('Konfirmasi pembayaran','Confirm payment')}}</h3>
    <p class="between">{{label('Total saat pemesanan','Original quoted total')}} <strong>{{money(quote.previous_total_atomic)}}</strong></p>
    <p class="between">{{label('Total terkini di POS','Current POS total')}} <strong>{{money(quote.total_atomic)}}</strong></p>
    <p v-if="quote.previous_total_atomic!==quote.total_atomic" class="price-alert">{{label('Harga berubah. Informasikan total baru kepada pelanggan sebelum melanjutkan.','Price has changed. Inform the customer before proceeding.')}}</p>
    <div class="payment-row"><label>{{label('Metode pembayaran','Payment type')}}<select v-model="tender" :disabled="operationBusy||uncertain||online===false" @change="onTenderChanged"><option value="CASH">{{label('Tunai','Cash')}}</option><option value="MANUAL_EXTERNAL">{{label('Pembayaran eksternal tercatat','Externally recorded payment')}}</option></select></label><label>{{label('Nominal diterima','Amount received')}}<input v-model="tendered" :disabled="operationBusy||uncertain||tender==='MANUAL_EXTERNAL'" type="text" inputmode="decimal" maxlength="16" @input="onAmountChanged"/></label></div>
    <p class="hint">{{label('Untuk IDR skala 0, isi nominal rupiah biasa. Pembayaran eksternal hanya untuk pembayaran yang sudah diverifikasi secara manual; sistem tidak memproses gateway pembayaran.','For IDR scale 0, enter ordinary rupiah amounts. External payments must already be manually verified; this does not charge a payment gateway.')}}</p>
    <label class="confirmation"><input v-model="priceConfirmed" :disabled="operationBusy||uncertain||online===false" type="checkbox"/>{{label('Saya sudah mengonfirmasi harga, barang, dan pembayaran dengan pelanggan.','I have confirmed price, items and payment with the customer.')}}</label>
    <p v-if="memberBusy" class="hint" role="status">{{label('Tunggu pemeriksaan member selesai sebelum menerima pembayaran.','Wait for the member check before taking payment.')}}</p>
    <p v-if="uncertain" class="price-alert" role="status">{{label('Status pembayaran belum pasti. Jangan terima pembayaran lagi. Tekan tombol di bawah untuk memeriksa ulang dengan permintaan yang sama.','Payment status is uncertain. Do not collect payment again. Use the button below to retry the same request.')}}</p>
    <button class="primary" type="button" :disabled="!canSettle" @click="settle"><component :is="icon" name="circle-check" :size="18"/>{{busy?label('Memproses…','Processing…'):uncertain?label('Periksa ulang pembayaran','Retry payment confirmation'):label('Catat pembayaran','Record payment')}}</button>
   </div>
  </section>
  <p v-if="error" class="feedback" role="alert">{{label('Tindakan ditolak: ','Action rejected: ')}}{{error}}</p>
  <p v-if="notice" class="feedback" role="status">{{notice}}</p>
  <p class="hint">{{label('Pelunasan memerlukan shift POS aktif dan hak kasir. Pesanan tidak dibayar otomatis hanya karena QR dipindai.','Settlement requires an active POS shift and cashier permission. Scanning a QR does not take payment.')}}</p>
 </main>
</template>
<style scoped>
.qr-inbox{--oq-white:#FFFFFF;--oq-dark:#202020;--oq-primary:#3965FA;--oq-soft:#99B7FC;max-width:1080px;margin:0 auto;padding:1.5rem;min-height:100vh;background:var(--oq-white);color:var(--oq-dark)}
header,.between{display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;flex-wrap:wrap}
h1{font-size:clamp(1.8rem,4vw,2.5rem)}h2{font-size:1.35rem}.kicker{letter-spacing:.08em;font-size:.8rem;font-weight:700;color:var(--oq-primary)}
.qr-inbox p{color:var(--oq-dark)}.panel{padding:clamp(1rem,3vw,1.75rem);border:1px solid var(--oq-soft);border-radius:18px;margin:1.25rem 0;background:var(--oq-white)}
.head-actions,.head-actions a,.search-row,.payment-row,.line,.quantity{display:flex;gap:.75rem;align-items:center}.head-actions{flex-wrap:wrap}.head-actions a{text-decoration:none;color:var(--oq-primary)}
.search-row,.payment-row{align-items:flex-end;flex-wrap:wrap}.search-row label,.payment-row label{display:flex;flex-direction:column;gap:.4rem;min-width:10rem}.search-row .search-input{flex:1;min-width:14rem}
input,select{border:1px solid var(--oq-soft);background:var(--oq-white);color:var(--oq-dark);border-radius:9px;padding:.75rem;min-height:44px;font:inherit}
button{min-height:44px;border:1px solid var(--oq-primary);border-radius:9px;background:var(--oq-primary);color:var(--oq-white);padding:.65rem .9rem;cursor:pointer;display:inline-flex;gap:.4rem;align-items:center;justify-content:center}
button:disabled{cursor:not-allowed;opacity:.5}button:focus-visible,input:focus-visible,select:focus-visible{outline:3px solid var(--oq-primary);outline-offset:2px}
.secondary{background:var(--oq-white);color:var(--oq-primary)}.match{display:flex;width:100%;margin:.5rem 0;justify-content:space-between;gap:1rem;flex-wrap:wrap}
.hint{font-size:.9rem;line-height:1.5}.line{justify-content:space-between;border-bottom:1px solid var(--oq-soft);padding:.7rem 0}
.quantity button{min-width:44px}.quote{padding:1rem;border:1px solid var(--oq-primary);margin:1rem 0;border-radius:12px}.quote .between{align-items:center}
.payment-row{margin:1rem 0}.confirmation{display:flex;align-items:center;gap:.6rem;margin:1rem 0}.confirmation input{width:20px;height:20px;min-height:auto}
.price-alert,.feedback{font-weight:700;border:1px solid var(--oq-soft);padding:1rem;border-radius:9px}.primary{margin:.75rem 0}
@media(max-width:600px){.search-row,.payment-row{display:grid;grid-template-columns:1fr}.line{flex-wrap:wrap}.head-actions{width:100%}}
</style>
