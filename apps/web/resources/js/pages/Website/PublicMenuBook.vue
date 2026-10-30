<script setup lang="ts">
// Author by Lab | zefry. Public menu plus opt-in, unpaid QR handoff to a verified cashier.
import { computed, ref } from 'vue';
import QRCode from 'qrcode';
import { Head } from '@inertiajs/vue3';
import { locale, setLocale } from '../../i18n';
import { formatMoney, setCurrencyPreferences }  from '../../business-money';
import WorkspaceIcon from '../../components/WorkspaceIcon.vue';
type MenuItem={product_id:string;category:string;name:string;price_atomic:string;currency:string;scale:number;spotlight:boolean};
const props=defineProps<{title:string;description:string;cover_url?:string|null;items:MenuItem[];preferences:unknown;preview?:boolean;ordering_enabled?:boolean}>();
setCurrencyPreferences(props.preferences);
const index=ref(0);const pages=computed(()=>Math.max(1,Math.ceil(props.items.length/6)));const current=computed(()=>props.items.slice(index.value*6,index.value*6+6));
const direction=ref<'next'|'previous'>('next');let touchStartX:number|null=null;let touchStartY:number|null=null;
function turn(step:number){const next=index.value+step;if(next<0||next>=pages.value)return;direction.value=step>0?'next':'previous';index.value=next;}
function startTouch(event:TouchEvent){if(event.touches.length!==1)return;touchStartX=event.touches[0].clientX;touchStartY=event.touches[0].clientY;}
function endTouch(event:TouchEvent){if(touchStartX===null||touchStartY===null||event.changedTouches.length!==1)return;const dx=event.changedTouches[0].clientX-touchStartX;const dy=event.changedTouches[0].clientY-touchStartY;touchStartX=null;touchStartY=null;if(Math.abs(dx)>70&&Math.abs(dx)>Math.abs(dy)*1.5)turn(dx<0?1:-1);}
const label=(id:string,en:string)=>locale.value==='id'?id:en;
function changeLanguage(event:Event){setLocale((event.target as HTMLSelectElement).value);}
const orderEnabled=computed(()=>props.ordering_enabled===true && props.preview!==true);
const quantities=ref<Record<string,number>>({});
const customer=ref({customer_name:'',phone:'',email:'',table_label:''});
const sending=ref(false);
const failure=ref('');
const confirmation=ref<null|{order_id:string;order_token:string;token_expires_at:number;total_atomic:string;currency:string;currency_scale:number;table_label:string|null}>(null);
const qrImage=ref('');
let lastPayload='';let stableOperation='';
const basketLines=computed(()=>props.items.filter(i=>(quantities.value[i.product_id]||0)>0).map(i=>({item:i,quantity:quantities.value[i.product_id]})));
const basketTotal=computed(()=>basketLines.value.reduce((sum,line)=>sum+BigInt(line.item.price_atomic)*BigInt(line.quantity),0n).toString());
const basketFirst=computed(()=>basketLines.value[0]?.item);
function addOne(item:MenuItem,increment:number){
 if(confirmation.value||sending.value)return;
 const previous=quantities.value[item.product_id]||0;
 quantities.value={...quantities.value,[item.product_id]:Math.max(0,Math.min(99,previous+increment))};
 failure.value='';
}
async function sendOrder(){
 if(sending.value||confirmation.value||!orderEnabled.value)return;
 const lines=basketLines.value.map(v=>({product_id:v.item.product_id,quantity:v.quantity}));
 if(!lines.length){failure.value=label('Pilih setidaknya satu menu.','Select at least one menu item.');return;}
 if(!/^\+[1-9][0-9]{7,15}$/.test(customer.value.phone.trim())){
  failure.value=label('Nomor telepon harus format internasional, contoh +628123456789.','Use international phone format, e.g. +628123456789.');return;
 }
 const data={identity_mode:'guest',customer_name:customer.value.customer_name.trim(),phone:customer.value.phone.trim(),email:customer.value.email.trim(),lines,table_label:customer.value.table_label.trim()};
 const serialized=JSON.stringify(data);
 if(serialized!==lastPayload){lastPayload=serialized;stableOperation=crypto.randomUUID().replace(/-/g,'');}
 sending.value=true;failure.value='';
 try {
  const csrf=document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content||'';
  const response=await fetch('/menu/orders',{method:'POST',credentials:'same-origin',headers:{'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify({...data,operation_id:stableOperation})});
  const answer=await response.json();
  if(!response.ok||!answer.order?.order_token){
   throw new Error(answer.error?.code||('HTTP_'+response.status));
  }
  confirmation.value=answer.order;
  try { qrImage.value=await QRCode.toDataURL(answer.order.order_token,{width:280,margin:2,errorCorrectionLevel:'M'}); }
  catch { qrImage.value=''; }
 } catch(e) {
  failure.value=label('Pesanan belum dapat dikirim: ','Order could not be submitted: ')+(e instanceof Error?e.message:'UNKNOWN_ERROR');
 } finally {sending.value=false;}
}
function clearOrder(){
 if(sending.value)return;
 confirmation.value=null;qrImage.value='';quantities.value={};lastPayload='';stableOperation='';failure.value='';
}
async function copyToken(){
 if(!confirmation.value)return;
 try { await navigator.clipboard.writeText(confirmation.value.order_token); }
 catch { failure.value=label('Salin kode secara manual.','Copy the token manually.'); }
}
</script>
<template>
 <Head :title="title+' | oneQay'"/>
 <main class="public-menu"><p v-if="preview" class="preview-banner" role="status">{{label('Pratinjau pribadi — belum tentu diterbitkan untuk pelanggan.','Private preview — this menu may not be published.')}}</p><header><div class="public-brand">one<span>Qay</span></div><label class="menu-language"><WorkspaceIcon name="globe" :size="18"/><select :value="locale" :aria-label="label('Bahasa','Language')" @change="changeLanguage"><option value="id">ID</option><option value="en">ENG</option></select></label></header><section class="menu-cover"><div><p class="menu-kicker">{{label('BUKU MENU','MENU BOOK')}}</p><h1>{{title}}</h1><p>{{description}}</p></div><img v-if="cover_url" class="cover-photo" :src="cover_url" :alt="title" fetchpriority="high"/><WorkspaceIcon v-else name="book-open" :size="44"/></section>
 <section class="menu-sheet" :aria-label="label('Buku menu interaktif','Interactive menu book')" tabindex="0" @keydown.left.prevent="turn(-1)" @keydown.right.prevent="turn(1)" @touchstart.passive="startTouch" @touchend.passive="endTouch">
 <div class="sheet-header"><div><p class="book-label">{{label('BUKU MENU INTERAKTIF','INTERACTIVE MENU BOOK')}}</p><h2>{{label('Pilihan menu','Our menu')}}</h2></div><span aria-live="polite">{{label('Halaman','Page')}} {{index+1}} / {{pages}}</span></div>
 <p class="book-help">{{label('Geser halaman atau gunakan tombol panah untuk melihat pilihan berikutnya.','Swipe or use the arrow buttons to explore the menu.')}}</p>
 <p v-if="!items.length">{{label('Menu sedang disiapkan.','The menu is being prepared.')}}</p>
 <div class="book-stage"><Transition :name="direction==='next'?'book-next':'book-previous'" mode="out-in"><div :key="index" class="book-spread"><div v-for="side in 2" :key="side" class="book-page"><p class="page-number">{{title}} · {{(index*2)+side}}</p><article v-for="(item,i) in current.slice((side-1)*3,side*3)" :key="i" class="menu-item"><p>{{item.category}} <span v-if="item.spotlight"> · {{label('Pilihan','Featured')}}</span></p><div><strong>{{item.name}}</strong><b>{{formatMoney(item.price_atomic,item.currency,item.scale)}}</b></div><div v-if="orderEnabled" class="order-quantity"><button type="button" :disabled="(quantities[item.product_id]||0)===0 || !!confirmation" :aria-label="label('Kurangi','Decrease')+' '+item.name" @click="addOne(item,-1)">−</button><span :aria-label="label('Jumlah','Quantity')">{{quantities[item.product_id]||0}}</span><button type="button" :disabled="(quantities[item.product_id]||0)>=99 || !!confirmation" :aria-label="label('Tambah','Add')+' '+item.name" @click="addOne(item,1)">+</button></div></article><p v-if="side===2&&current.length<=3" class="page-empty">{{label('Terima kasih telah melihat menu kami.','Thank you for exploring our menu.')}}</p></div></div></Transition></div>
 <nav :aria-label="label('Halaman buku menu','Menu book pages')"><button :disabled="index===0" @click="turn(-1)"><WorkspaceIcon name="chevron-left" :size="18"/> {{label('Sebelumnya','Previous')}}</button><span aria-hidden="true">{{index+1}} / {{pages}}</span><button :disabled="index>=pages-1" @click="turn(1)">{{label('Berikutnya','Next')}} <WorkspaceIcon name="chevron-right" :size="18"/></button></nav></section>
 <section v-if="orderEnabled" class="guest-order" :aria-label="label('Pemesanan QR','QR ordering')">
  <template v-if="!confirmation">
   <div class="order-heading"><div><p class="book-label">{{label('PESAN DARI MENU','ORDER FROM MENU')}}</p><h2>{{label('Pesanan Anda','Your order')}}</h2></div><p>{{label('Bayar langsung kepada kasir.','Pay directly at the cashier.')}}</p></div>
   <p v-if="!basketLines.length">{{label('Gunakan tombol + pada pilihan menu untuk menambahkan pesanan.','Use + on any menu item to build your order.')}}</p>
   <ul v-else class="order-list"><li v-for="line in basketLines" :key="line.item.product_id">{{line.quantity}} × {{line.item.name}} <strong>{{formatMoney((BigInt(line.item.price_atomic)*BigInt(line.quantity)).toString(),line.item.currency,line.item.scale)}}</strong></li></ul>
   <p v-if="basketFirst" class="order-total">{{label('Estimasi total','Estimated total')}} <strong>{{formatMoney(basketTotal,basketFirst.currency,basketFirst.scale)}}</strong></p>
   <form class="guest-form" @submit.prevent="sendOrder">
    <label>{{label('Nama pemesan','Customer name')}}<input v-model="customer.customer_name" name="customer_name" maxlength="120" required autocomplete="name" :disabled="sending"/></label>
    <label>{{label('Telepon (format +62...)','Phone (international format)')}}<input v-model="customer.phone" name="phone" type="tel" maxlength="17" placeholder="+628123456789" required autocomplete="tel" :disabled="sending"/></label>
    <label>Email<input v-model="customer.email" name="email" type="email" maxlength="254" required autocomplete="email" :disabled="sending"/></label>
    <label>{{label('Nomor/nama meja (opsional)','Table number/name (optional)')}}<input v-model="customer.table_label" name="table_label" maxlength="32" :placeholder="label('Boleh dikosongkan','Leave blank if not needed')" :disabled="sending"/></label>
    <p class="order-disclaimer">{{label('Pengiriman pesanan hanya membuat kode QR untuk ditunjukkan kepada kasir. Belum ada pembayaran dan stok belum dipotong. Harga akhir diverifikasi ulang oleh kasir.','Submitting creates an unpaid QR handoff for the cashier. No payment or stock deduction occurs yet. The cashier confirms the current price.')}}</p>
    <p v-if="failure" class="order-error" role="alert">{{failure}}</p>
    <button type="submit" :disabled="sending || !basketLines.length" class="order-submit"><WorkspaceIcon name="qr-code" :size="18"/>{{sending?label('Mengirim pesanan…','Submitting…'):label('Buat kode QR pesanan','Create order QR')}}</button>
   </form>
  </template>
  <div v-else class="guest-confirmation" role="status" aria-live="polite">
   <h2>{{label('Pesanan berhasil dikirim ke kasir','Order sent to cashier')}}</h2>
   <p>{{label('Status: menunggu pembayaran. Tunjukkan kode ini ke kasir.','Status: unpaid. Present this code to the cashier.')}}</p>
   <img v-if="qrImage" :src="qrImage" :alt="label('Kode QR pesanan untuk dipindai kasir','Order QR code for cashier')" width="240" height="240"/>
   <p class="order-token"><code>{{confirmation.order_token}}</code></p>
   <p>{{label('QR berlaku hingga','QR valid until')}} {{new Date(confirmation.token_expires_at*1000).toLocaleString(locale==='id'?'id-ID':'en-US')}}</p>
   <p>{{label('Total saat pemesanan','Total at submission')}}: {{formatMoney(confirmation.total_atomic,confirmation.currency,confirmation.currency_scale)}}</p>
   <div class="order-actions"><button type="button" @click="copyToken">{{label('Salin kode','Copy code')}}</button><button type="button" @click="clearOrder">{{label('Buat pesanan baru','New order')}}</button></div>
   <p v-if="failure" role="alert">{{failure}}</p>
  </div>
 </section><footer>© {{new Date().getFullYear()}} oneQay</footer></main>
</template>
<style scoped>
.public-menu{min-height:100vh;background:var(--oq-white);color:var(--oq-dark);padding:1rem max(1rem,calc((100vw - 1000px)/2));font-size:1rem}.preview-banner{padding:.8rem 1rem;background:var(--oq-white);color:var(--oq-dark);border:1px solid var(--oq-soft);border-radius:10px}.public-menu header{display:flex;align-items:center;justify-content:space-between;padding:1rem 0}.menu-language{display:flex;align-items:center;gap:.4rem;background:var(--oq-white);border:1px solid var(--oq-soft);color:var(--oq-dark);border-radius:10px;padding:.5rem}.menu-language select{border:0;background:var(--oq-white);color:var(--oq-dark);font-size:1rem}.public-brand{font-size:1.5rem;font-weight:800}.public-brand span{color:var(--oq-primary)}.menu-cover{background:var(--oq-primary);color:var(--oq-white);border-radius:24px;padding:clamp(1.5rem,5vw,3rem);display:flex;justify-content:space-between;gap:1rem}.menu-cover h1{font-size:clamp(2rem,6vw,3.5rem);margin:.3rem 0}.menu-cover p{line-height:1.5}.menu-kicker{font-size:.875rem;font-weight:700;letter-spacing:.1em}.menu-sheet{background:var(--oq-white);color:var(--oq-dark);padding:clamp(1.2rem,4vw,2.5rem);border-radius:22px;margin:1rem 0;border:1px solid var(--oq-soft)}.sheet-header,.menu-item div,.menu-sheet nav{display:flex;justify-content:space-between;align-items:center;gap:1rem}.menu-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem}.menu-item{padding:1rem;border:1px solid var(--oq-soft);border-radius:14px}.menu-item p{font-size:.875rem;color:var(--oq-dark)}.menu-item div{align-items:start}.menu-item b{white-space:nowrap}.menu-sheet nav{margin-top:2rem}.menu-sheet button{display:inline-flex;align-items:center;gap:.4rem;padding:.75rem 1rem;min-height:44px;border:0;border-radius:10px;background:var(--oq-primary);color:var(--oq-white)}.menu-sheet button:disabled{background:var(--oq-white);color:var(--oq-dark)}footer{text-align:center;padding:2rem}@media(max-width:600px){.menu-grid{grid-template-columns:1fr}.menu-cover{border-radius:16px}.menu-item div{flex-wrap:wrap}}
.cover-photo{width:38%;max-height:320px;object-fit:contain;align-self:center;border-radius:14px}.menu-cover>div{min-width:0;overflow-wrap:anywhere}@media(max-width:600px){.menu-cover{flex-direction:column}.cover-photo{width:100%;max-height:260px}}
.menu-sheet{perspective:1400px}.menu-turn-enter-active,.menu-turn-leave-active{transition:transform .22s ease,opacity .22s ease;transform-origin:left center}.menu-turn-enter-from{transform:rotateY(-12deg);opacity:0}.menu-turn-leave-to{transform:rotateY(12deg);opacity:0}@media(prefers-reduced-motion:reduce){.menu-turn-enter-active,.menu-turn-leave-active{transition:none}.menu-turn-enter-from,.menu-turn-leave-to{transform:none}}

.book-label,.page-number{font-size:.875rem;font-weight:700;letter-spacing:.06em}.book-help{line-height:1.5}.book-stage{perspective:1600px;min-height:18rem}.book-spread{display:grid;grid-template-columns:1fr 1fr;transform-style:preserve-3d;box-shadow:0 10px 22px var(--oq-soft);border-radius:14px;overflow:hidden}.book-page{min-width:0;min-height:18rem;background:var(--oq-white);color:var(--oq-dark);padding:1.25rem;border:1px solid var(--oq-soft)}.book-page:first-child{border-right:4px solid var(--oq-soft)}.menu-item{margin:.75rem 0}.page-empty{line-height:1.5}.book-next-enter-active,.book-next-leave-active,.book-previous-enter-active,.book-previous-leave-active{transition:transform .28s ease,opacity .28s ease;backface-visibility:hidden}.book-next-enter-active,.book-next-leave-active{transform-origin:left center}.book-previous-enter-active,.book-previous-leave-active{transform-origin:right center}.book-next-enter-from{transform:rotateY(-55deg);opacity:0}.book-next-leave-to{transform:rotateY(55deg);opacity:0}.book-previous-enter-from{transform:rotateY(55deg);opacity:0}.book-previous-leave-to{transform:rotateY(-55deg);opacity:0}@media(max-width:600px){.book-spread{grid-template-columns:1fr}.book-page{min-height:auto}.book-page:first-child{border-right:1px solid var(--oq-soft);border-bottom:4px solid var(--oq-soft)}}@media(prefers-reduced-motion:reduce){.book-next-enter-active,.book-next-leave-active,.book-previous-enter-active,.book-previous-leave-active{transition:none}.book-next-enter-from,.book-next-leave-to,.book-previous-enter-from,.book-previous-leave-to{transform:none;opacity:1}}

.guest-order{margin:1rem 0 2rem;padding:clamp(1rem,4vw,2rem);border:1px solid var(--oq-soft);border-radius:20px;background:var(--oq-white);color:var(--oq-dark)}
.order-heading,.order-list li,.order-total{display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap}
.order-list{padding:0;list-style:none}.order-list li{padding:.6rem 0;border-bottom:1px solid var(--oq-soft)}
.guest-form{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem}.guest-form label{display:flex;flex-direction:column;gap:.4rem;min-width:0}
.guest-form input{padding:.8rem;border:1px solid var(--oq-soft);border-radius:9px;min-height:44px;background:var(--oq-white);color:var(--oq-dark)}
.guest-form input:focus-visible,.guest-order button:focus-visible{outline:3px solid var(--oq-primary);outline-offset:2px}
.order-disclaimer,.order-error,.order-submit{grid-column:1/-1}.order-error{color:var(--oq-dark);font-weight:700}
.order-submit,.order-actions button,.order-quantity button{border:1px solid var(--oq-primary);border-radius:10px;background:var(--oq-primary);color:var(--oq-white);padding:.75rem 1rem;min-height:44px;cursor:pointer}
.order-submit{display:flex;align-items:center;justify-content:center;gap:.5rem}.order-submit:disabled,.order-quantity button:disabled{opacity:.5;cursor:not-allowed}
.order-quantity{display:flex;justify-content:flex-start;gap:.75rem;margin-top:.7rem}.order-quantity button{min-width:44px}
.guest-confirmation{text-align:center}.guest-confirmation img{display:block;margin:1rem auto;max-width:100%;height:auto}.order-token{overflow-wrap:anywhere}.order-actions{display:flex;justify-content:center;flex-wrap:wrap;gap:.75rem}
@media(max-width:600px){.guest-form{grid-template-columns:1fr}.order-heading{display:block}}

</style>
