<script setup lang="ts">
import { onBeforeUnmount, ref, watch } from 'vue';
import { locale } from '../i18n';
const props=defineProps<{businessUrl:string|null;published:boolean}>();
const image=ref(''),url=ref(''),error=ref(false),busy=ref(false);
const label=(id:string,en:string)=>locale.value==='id'?id:en;
let generation=0;let timer:ReturnType<typeof setTimeout>|null=null;
async function generate(){
 const current=++generation;image.value='';url.value='';error.value=false;busy.value=false;
 if(timer)clearTimeout(timer);if(!props.published||!props.businessUrl)return;
 try{
  const parsed=new URL(props.businessUrl);
  if(parsed.protocol!=='https:'||parsed.username||parsed.password||parsed.port||parsed.search||parsed.hash||!['','/'].includes(parsed.pathname))throw Error('invalid business URL');
  parsed.pathname='/menu';url.value=parsed.toString();busy.value=true;
  const timeout=new Promise<never>((_,reject)=>{timer=setTimeout(()=>reject(Error('timeout')),15000)});
  const task=(async()=>{const qr=await import('qrcode');return qr.toDataURL(parsed.toString(),{width:768,margin:4,errorCorrectionLevel:'M',color:{dark:'#202020',light:'#FFFFFF'}})})();
  const result=await Promise.race([task,timeout]);if(current===generation)image.value=result;
 }catch{if(current===generation)error.value=true;}
 finally{if(current===generation){busy.value=false;if(timer)clearTimeout(timer);timer=null;}}
}
watch(()=>[props.businessUrl,props.published],generate,{immediate:true});
onBeforeUnmount(()=>{generation++;if(timer)clearTimeout(timer)});
</script>
<template>
 <section class="qr-card"><div><p class="eyebrow">{{label('BAGIKAN MENU','SHARE MENU')}}</p><h2>QR {{label('buku menu','menu book')}}</h2><p>{{label('Pelanggan dapat memindai kode ini untuk melihat menu yang telah diterbitkan.','Customers can scan this code to view the published menu.')}}</p><p v-if="!published">{{label('Terbitkan menu terlebih dahulu untuk membuat QR.','Publish the menu first to create a QR code.')}}</p><p v-else-if="!businessUrl">{{label('Subdomain bisnis belum tersedia.','Business subdomain is not available.')}}</p><p v-if="busy" role="status">{{label('Menyiapkan QR…','Preparing QR…')}}</p><p v-if="error" role="alert">{{label('QR belum dapat dibuat. Periksa alamat bisnis dan coba lagi.','Could not create the QR code. Check the business address and try again.')}} <button type="button" @click="generate">{{label('Coba lagi','Try again')}}</button></p><template v-if="image"><a :href="url" target="_blank" rel="noopener">{{url}}</a><p><a class="download" :href="image" download="oneqay-menu-qr.png">{{label('Unduh QR untuk dicetak','Download QR for printing')}}</a></p></template></div><img v-if="image" :src="image" :alt="label('Kode QR untuk membuka buku menu','QR code to open the menu book')" width="200" height="200"/></section>
</template>
<style scoped>
.qr-card{display:flex;justify-content:space-between;align-items:center;gap:1.5rem;flex-wrap:wrap;padding:1.25rem;border:1px solid var(--oq-soft);border-radius:16px;background:var(--oq-white);color:var(--oq-dark);margin:1rem 0}.qr-card h2{font-size:1.2rem}.qr-card p{line-height:1.5}.eyebrow{font-size:.875rem;font-weight:700;letter-spacing:.08em}.qr-card img{max-width:200px;height:auto;border:1px solid var(--oq-soft);border-radius:12px}.download{display:inline-block;padding:.75rem 1rem;border-radius:10px;background:var(--oq-primary);color:var(--oq-white);text-decoration:none}.qr-card a{overflow-wrap:anywhere}
</style>
