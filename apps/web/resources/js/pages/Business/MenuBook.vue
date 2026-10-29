<script setup lang="ts">
// Author by Lab | zefry. Private draft configuration; only explicit publication exposes the selected cover.
import { computed, onBeforeUnmount, reactive, ref, watch } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { locale } from '../../i18n';
import { formatMoney, type CurrencyPreferences } from '../../business-money';
import { useBusinessMutation } from '../../use-business-mutation';
import WorkspaceIcon from '../../components/WorkspaceIcon.vue';
type Product = { product_id:string; display_name:string; unit_price_atomic:string; currency:string; currency_scale:number; active:boolean };
type Item = { category_id:string; visible:boolean; spotlight:boolean; version:number };
const props=defineProps<{products:{data:Product[];page:number;has_more:boolean;total:number};categories:{id:string;name:string}[];items:Record<string,Item>;filters:{q:string};preferences:CurrencyPreferences;publication:{title:string;description:string;cover_id:string|null;published:boolean;version:number}}>();
const page=usePage<{business_profile?:{url?:string|null}|null}>();
const menuUrl=computed(()=>page.props.business_profile?.url ? page.props.business_profile.url.replace(/\/$/,'')+'/menu' : null);
const id=computed(()=>locale.value==='id');
const label=(a:string,b:string)=>id.value?a:b;
const q=ref(props.filters.q);const categoryName=ref('');const selected=ref<Product|null>(null);const dialog=ref<HTMLDialogElement|null>(null);
const publication=reactive({...props.publication});
const uploadBusy=ref(false);const uploadError=ref('');const uploadNotice=ref('');
let uploadController:AbortController|null=null;let uploadTimer:ReturnType<typeof setTimeout>|null=null;let disposed=false;
onBeforeUnmount(()=>{disposed=true;uploadController?.abort();if(uploadTimer)clearTimeout(uploadTimer);});
const coverUrl=computed(()=>publication.cover_id?`/pos/business/menu-book/media/${publication.cover_id}`:null);
watch(()=>props.publication,(value)=>Object.assign(publication,value),{deep:true});
const form=reactive({category_id:'',visible:true,spotlight:false,version:0});const notice=ref('');
const {busy,uncertain,error,submit,clear}=useBusinessMutation();
function search(page=1){router.get('/pos/business/menu-book',{q:q.value,page},{preserveScroll:true});}
async function addCategory(){if(!categoryName.value.trim())return;const v=await submit('/pos/business/menu-book/categories',{name:categoryName.value});if(v){categoryName.value='';notice.value=label('Kategori buku menu tersimpan.','Menu category saved.');clear();router.reload();}}
function edit(p:Product){if(busy.value||uncertain.value)return;clear();selected.value=p;const old=props.items[p.product_id];Object.assign(form,{category_id:old?.category_id??props.categories[0]?.id??'',visible:old?.visible??true,spotlight:old?.spotlight??false,version:old?.version??0});dialog.value?.showModal();}
const publicationAction=ref<'draft'|'publish'|'unpublish'|null>(null);
async function savePublication(action:'draft'|'publish'|'unpublish'){if(uploadBusy.value)return;if(uncertain.value&&publicationAction.value!==action)return;publicationAction.value=action;const v=await submit('/pos/business/menu-book/publication',{title:publication.title,description:publication.description,version:publication.version,cover_id:publication.cover_id,action});if(v){notice.value=label('Pengaturan buku menu tersimpan.','Menu settings saved.');publicationAction.value=null;clear();router.reload();}}
async function save(){if(!selected.value)return;const v=await submit('/pos/business/menu-book/items',{product_id:selected.value.product_id,...form});if(v){dialog.value?.close();notice.value=label('Pengaturan produk tersimpan.','Product settings saved.');clear();router.reload();}}

async function uploadCover(event:Event){
 const input=event.target as HTMLInputElement;const file=input.files?.[0];input.value='';
 if(!file||busy.value||uncertain.value||uploadBusy.value)return;
 uploadError.value='';uploadNotice.value='';
 if(file.size>3*1024*1024||!['image/jpeg','image/png','image/webp'].includes(file.type)){
  uploadError.value=label('Pilih gambar JPG, PNG, atau WebP maksimal 3 MB.','Choose a JPG, PNG or WebP image up to 3 MB.');return;
 }
 const token=document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content;
 if(!token){window.location.assign('/business-login?reason=session-expired');return;}
 const controller=new AbortController();uploadController=controller;uploadBusy.value=true;
 uploadTimer=setTimeout(()=>controller.abort(),30000);
 try{
  const body=new FormData();body.append('image',file);
  const response=await fetch('/pos/business/menu-book/media',{method:'POST',body,credentials:'same-origin',redirect:'error',signal:controller.signal,headers:{Accept:'application/json','X-CSRF-TOKEN':token}});
  if(response.status===401||response.status===419){window.location.assign('/business-login?reason=session-expired');return;}
  const data=await response.json();if(disposed)return;if(controller.signal.aborted)throw new Error('timeout');
  if(!response.ok){
   const raw=data?.error?.code??data?.code;const code=typeof raw==='string'&&/^[A-Z][A-Z0-9_]{1,63}$/.test(raw)?raw:'MENU_IMAGE_UPLOAD_FAILED';
   const messages:Record<string,[string,string]>={
    MENU_IMAGE_QUOTA_EXCEEDED:['Penyimpanan gambar buku menu penuh. Hubungi pengelola aplikasi.','Menu image storage is full. Contact the application operator.'],
    MENU_IMAGE_PROCESSING_UNAVAILABLE:['Pengolah gambar server belum tersedia. Hubungi pengelola aplikasi.','Server image processing is unavailable. Contact the application operator.'],
    MENU_IMAGE_INVALID:['Gambar tidak dapat dibaca. Gunakan JPG, PNG, atau WebP dengan dimensi maksimal 4.000 piksel dan 8 megapiksel.','Image could not be read. Use JPG, PNG or WebP up to 4,000 pixels per side and 8 megapixels.'],
    MENU_IMAGE_SIZE_INVALID:['Ukuran gambar terlalu besar. Perkecil gambar hingga maksimal 3 MB.','The image is too large. Resize it to 3 MB or less.']
   };
   const message=messages[code];uploadError.value=(message?label(...message):label('Gambar belum tersimpan. Silakan coba lagi.','Image was not saved. Please try again.'))+' ('+code+')';return;
  }
  if(typeof data?.record?.id!=='string'||!/^[a-f0-9]{32}$/.test(data.record.id))throw new Error('invalid response');
  publication.cover_id=data.record.id;uploadNotice.value=label('Cover siap dipilih. Simpan draft untuk pratinjau, lalu terbitkan agar terlihat oleh pelanggan.','Cover selected. Save the draft to preview it, then publish to show it to customers.');
 }catch{
  if(!disposed)uploadError.value=label('Hasil unggah belum terkonfirmasi. Pilih ulang gambar yang sama untuk mencoba lagi; gambar tidak akan digandakan.','Upload result is unconfirmed. Select the same image to retry; it will not be duplicated.');
 }finally{if(uploadTimer)clearTimeout(uploadTimer);uploadTimer=null;uploadController=null;if(!disposed)uploadBusy.value=false;}
}
</script>
<template>
 <Head :title="label('Buku menu','Menu book')"/>
 <main class="merch-page">
  <header class="menu-header"><div><p class="menu-eyebrow">{{label('KATALOG USAHA','BUSINESS CATALOG')}}</p><h1>{{label('Buku menu','Menu book')}}</h1><p>{{label('Kelola kategori, produk yang ditampilkan, dan pilihan sorotan dari katalog produk Anda.','Manage menu categories, visible products and featured selections from your product catalog.')}}</p></div><WorkspaceIcon name="book-open" :size="32"/></header>
  <p class="menu-note">{{label('Buku menu dapat diterbitkan pada subdomain bisnis. Cover dapat Anda atur di bawah. Foto produk dan pemesanan meja masih dalam pengembangan.','The menu can be published on your business subdomain. Configure your cover below. Product photos and table ordering are still in development.')}}</p>
  <p>{{label('Simpan draft dan lihat pratinjau sebelum menerbitkan. Harga dan status aktif produk selalu mengikuti katalog terkini.','Save the draft and preview it before publishing. Prices and active status always follow the current catalog.')}}</p><p><a href="/pos/business/menu-book/preview" target="_blank" rel="noopener">{{label('Lihat pratinjau buku menu','Preview menu book')}}</a></p>

  <section class="menu-card cover-editor" :aria-busy="uploadBusy">
   <div><p class="menu-eyebrow">{{label('IDENTITAS MENU','MENU IDENTITY')}}</p><h2>{{label('Cover buku menu','Menu book cover')}}</h2><p>{{label('Pilih foto milik bisnis Anda. Gambar hanya terlihat oleh pelanggan setelah menu diterbitkan.','Choose your business photo. Customers see it only after you publish the menu.')}}</p>
    <label class="cover-upload">{{label('Unggah cover','Upload cover')}}<input type="file" accept="image/jpeg,image/png,image/webp" :disabled="uploadBusy||busy||uncertain" @change="uploadCover"/></label>
    <p class="cover-hint">JPG / PNG / WebP · {{label('maks. 3 MB, 4.000 piksel per sisi, 8 megapiksel','max. 3 MB, 4,000 pixels per side, 8 megapixels')}}</p>
    <button v-if="publication.cover_id" type="button" :disabled="uploadBusy||busy||uncertain" @click="publication.cover_id=null;uploadNotice='';uploadError=''">{{label('Hapus pilihan cover','Remove cover selection')}}</button>
    <p v-if="uploadNotice" role="status">{{uploadNotice}}</p><p v-if="uploadError" class="merch-error" role="alert">{{uploadError}}</p>
   </div><div class="cover-frame"><img v-if="coverUrl" :src="coverUrl" :alt="label('Pilihan cover buku menu','Selected menu cover')"/><div v-else class="cover-empty"><WorkspaceIcon name="book-open" :size="40"/><span>{{label('Cover bisnis Anda','Your business cover')}}</span></div></div>
  </section>
  <div v-if="uploadBusy||busy" class="menu-progress" role="status" aria-live="polite"><span class="menu-spinner" aria-hidden="true"></span>{{uploadBusy?label('Mengunggah dan memproses gambar…','Uploading and processing image…'):label('Menyimpan perubahan…','Saving changes…')}}</div>
  <section class="menu-card"><h2>{{label('Tampilan dan publikasi','Display and publishing')}}</h2><form class="menu-controls" @submit.prevent="savePublication('draft')"><label class="menu-grow">{{label('Judul buku menu','Menu title')}}<input v-model="publication.title" required maxlength="160" :disabled="uploadBusy||busy||uncertain"/></label><label class="menu-grow">{{label('Pengantar','Introduction')}}<input v-model="publication.description" maxlength="500" :disabled="uploadBusy||busy||uncertain"/></label><p>{{publication.published?label('Status: Terbit','Status: Published'):label('Status: Belum terbit','Status: Unpublished')}}</p><button :disabled="uploadBusy||busy||(uncertain&&publicationAction!=='draft')">{{label('Simpan draft','Save draft')}}</button><button type="button" :disabled="uploadBusy||busy||(uncertain&&publicationAction!=='publish')" @click="savePublication('publish')">{{label('Terbitkan menu','Publish menu')}}</button><button v-if="publication.published" type="button" :disabled="uploadBusy||busy||(uncertain&&publicationAction!=='unpublish')" @click="savePublication('unpublish')">{{label('Hentikan publikasi','Unpublish')}}</button></form><p>{{label('Alamat menu pelanggan:','Customer menu address:')}} <a v-if="menuUrl" :href="menuUrl" target="_blank" rel="noopener">{{menuUrl}}</a><span v-else>{{label('Subdomain bisnis belum tersedia.','Business subdomain is not available.')}}</span></p></section>
  <section class="menu-card"><h2>{{label('Kategori buku menu','Menu categories')}}</h2><p>{{label('Kategori ini terpisah dari kategori inventori.','These categories are separate from inventory categories.')}}</p><form class="menu-controls" @submit.prevent="addCategory"><label class="menu-grow">{{label('Nama kategori','Category name')}}<input v-model="categoryName" maxlength="120" required :disabled="uploadBusy||busy||uncertain" :placeholder="label('Contoh: Kopi','Example: Coffee')"/></label><button :disabled="uploadBusy||busy">{{uncertain?label('Coba permintaan yang sama','Retry same request'):label('Tambah kategori','Add category')}}</button></form><div class="menu-chips"><span v-for="c in categories" :key="c.id">{{c.name}}</span><span v-if="!categories.length">{{label('Belum ada kategori.','No categories yet.')}}</span></div></section>
  <section class="menu-card"><div class="menu-row"><div><h2>{{label('Produk dalam buku menu','Menu products')}}</h2><p>{{label('Harga mengikuti katalog produk.','Prices come from the product catalog.')}}</p></div><form class="menu-controls" @submit.prevent="search()"><label>{{label('Cari produk','Search products')}}<input v-model="q" maxlength="120"/></label><button>{{label('Cari','Search')}}</button></form></div>
   <p v-if="notice" class="merch-success" role="status">{{notice}}</p><p v-if="error" class="merch-error" role="alert">{{error}}</p>
   <div class="merch-table-wrap"><table class="merch-table"><thead><tr><th>{{label('Produk','Product')}}</th><th>{{label('Harga','Price')}}</th><th>{{label('Kategori menu','Menu category')}}</th><th>{{label('Tampilan','Display')}}</th><th>{{label('Aksi','Action')}}</th></tr></thead><tbody><tr v-for="p in products.data" :key="p.product_id"><td><strong>{{p.display_name}}</strong><br/><small>{{p.product_id}}</small></td><td>{{formatMoney(p.unit_price_atomic,p.currency,p.currency_scale)}}</td><td>{{categories.find(c=>c.id===items[p.product_id]?.category_id)?.name??'—'}}</td><td>{{items[p.product_id]?(items[p.product_id].visible?label('Ditampilkan','Visible'):label('Disembunyikan','Hidden')):label('Belum diatur','Not configured')}}<span v-if="items[p.product_id]?.spotlight"> · {{label('Sorotan','Featured')}}</span></td><td><button type="button" :disabled="!categories.length||busy||uncertain" @click="edit(p)">{{label('Atur','Configure')}}</button></td></tr></tbody></table><p v-if="!products.data.length">{{label('Tidak ada produk yang ditemukan.','No products found.')}}</p></div>
   <div class="menu-controls"><button type="button" :disabled="products.page<=1" @click="search(products.page-1)">{{label('Sebelumnya','Previous')}}</button><span>{{label('Halaman','Page')}} {{products.page}}</span><button type="button" :disabled="!products.has_more" @click="search(products.page+1)">{{label('Berikutnya','Next')}}</button></div>
  </section>
  <dialog ref="dialog" class="merch-dialog" @cancel.prevent="!busy&&!uncertain&&dialog?.close()"><form @submit.prevent="save"><header><div><h2>{{label('Atur buku menu','Configure menu item')}}</h2><p>{{selected?.display_name}}</p></div></header><fieldset :disabled="uploadBusy||busy||uncertain"><label>{{label('Kategori buku menu','Menu category')}}<select v-model="form.category_id" required><option v-for="c in categories" :key="c.id" :value="c.id">{{c.name}}</option></select></label><label><input v-model="form.visible" type="checkbox"/> {{label('Tampilkan produk','Show product')}}</label><label><input v-model="form.spotlight" type="checkbox"/> {{label('Jadikan sorotan','Feature as spotlight')}}</label></fieldset><p v-if="error" class="merch-error" role="alert">{{error}}</p><footer><button type="button" class="secondary" :disabled="uploadBusy||busy||uncertain" @click="dialog?.close()">{{label('Batal','Cancel')}}</button><button :disabled="uploadBusy||busy">{{busy?label('Menyimpan…','Saving…'):uncertain?label('Coba permintaan yang sama','Retry same request'):label('Simpan','Save')}}</button></footer></form></dialog>
 </main>
</template>
<style scoped>
.menu-header,.menu-row{display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap}.menu-header h1{font-size:2rem;margin:.25rem 0}.menu-header p,.menu-card p{line-height:1.5}.menu-eyebrow{font-weight:700;letter-spacing:.08em;font-size:.875rem}.menu-card,.menu-note{padding:1.25rem;border:1px solid var(--oq-soft);border-radius:16px;background:var(--oq-white);color:var(--oq-dark);margin:1rem 0}.menu-note{background:var(--oq-white)}.menu-card h2{font-size:1.2rem;margin:0}.menu-controls{display:flex;gap:.75rem;align-items:end;flex-wrap:wrap}.menu-controls label{display:grid;gap:.35rem}.menu-grow{flex:1;min-width:180px}.menu-controls input{width:100%;min-height:44px}.menu-chips{display:flex;flex-wrap:wrap;gap:.5rem;margin-top:1rem}.menu-chips span{border:1px solid var(--oq-soft);border-radius:999px;padding:.4rem .8rem;background:var(--oq-white);color:var(--oq-dark)}.merch-table-wrap{margin:1rem 0}.merch-table td button{min-height:40px}@media(max-width:600px){.menu-header h1{font-size:1.6rem}.menu-controls{align-items:stretch}.menu-controls label{width:100%}}

.cover-editor{display:grid;grid-template-columns:minmax(0,1.4fr) minmax(180px,1fr);gap:1.5rem;align-items:center}.cover-upload{display:grid;gap:.5rem;font-weight:600}.cover-upload input{font-size:1rem;max-width:100%;min-height:44px}.cover-hint{font-size:.875rem}.cover-frame{border:1px solid var(--oq-soft);border-radius:16px;overflow:hidden;aspect-ratio:4/3;background:var(--oq-white);color:var(--oq-dark)}.cover-frame img{width:100%;height:100%;object-fit:contain}.cover-empty{height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:1rem}.menu-progress{position:fixed;bottom:1.5rem;left:50%;transform:translateX(-50%);z-index:1000;display:flex;align-items:center;gap:.75rem;max-width:calc(100vw - 2rem);padding:1rem 1.5rem;background:var(--oq-navy);color:var(--oq-white);border-radius:16px;box-shadow:0 6px 20px var(--oq-soft)}.menu-spinner{width:20px;height:20px;flex-shrink:0;border:2px solid var(--oq-soft);border-top-color:var(--oq-white);border-radius:50%;animation:menu-spin .8s linear infinite}@keyframes menu-spin{to{transform:rotate(360deg)}}@media(prefers-reduced-motion:reduce){.menu-spinner{animation:none}}@media(max-width:600px){.cover-editor{grid-template-columns:1fr}}
</style>
