<script setup lang="ts">
// Author by Lab | zefry. No contact details in URLs or persistent client storage.
import { ref } from 'vue'
import type { Component } from 'vue'
type Member = {id:string;name:string;status:'cashier_enrolled'}
const props=defineProps<{
 language:string;
 icon:Component;
 disabled?:boolean;
 selected:Member|null;
 send:(action:'member-lookup'|'member-register',body:Record<string,unknown>)=>Promise<{member:Member|null}>;
}>()
const emit=defineEmits<{select:[Member|null];busyChange:[boolean]}>()
const t=(id:string,en:string)=>props.language==='id'?id:en
const kind=ref<'phone'|'email'>('phone')
const lookupValue=ref('')
const name=ref(''),phone=ref(''),email=ref('')
const create=ref(false),busy=ref(false),feedback=ref(''),error=ref('')
let registrationId='', registrationBody=''
function codeMessage(code:string) {
 if(code.includes('MEMBER_CONTACT_EXISTS'))return t('Kontak ini sudah terdaftar. Cari member yang sudah ada.','This contact is already enrolled. Find the existing member.')
 if(code.includes('MEMBER_CUSTOMER_MATCH_CONFLICT'))return t('Kontak cocok dengan data pelanggan yang berbeda. Minta pemilik bisnis memeriksanya.','Contacts match conflicting customer records. Ask the business owner to review.')
 if(code.includes('MEMBER_PHONE_INVALID'))return t('Masukkan nomor HP yang valid, misalnya 0812… atau +62812….','Enter a valid phone number, including the country code.')
 if(code.includes('MEMBER_EMAIL_INVALID'))return t('Periksa alamat email member.','Check the member’s email address.')
 return t('Permintaan belum berhasil. Periksa koneksi atau coba lagi.','Request could not be completed. Check the connection or try again.')
}
function toggle() {
 create.value=!create.value;error.value='';feedback.value=''
 if(create.value) {
  if(kind.value==='phone')phone.value=lookupValue.value
  else email.value=lookupValue.value
 }
}
async function run() {
 if (busy.value||props.disabled) return
 busy.value=true;emit('busyChange',true);error.value='';feedback.value=''
 try {
  const fields={name:name.value.trim(),phone:phone.value.trim(),email:email.value.trim()}
  if(create.value) {
   const body=JSON.stringify(fields)
   if(body!==registrationBody) {
    registrationBody=body
    registrationId=crypto.randomUUID()
   }
  }
  const result=create.value
   ?await props.send('member-register',{operation_id:registrationId,...fields})
   :await props.send('member-lookup',{kind:kind.value,value:lookupValue.value.trim()})
  if(props.disabled)return
  if(!result.member) {
   feedback.value=t('Member belum ditemukan. Periksa kontak atau pilih Daftar member.','No member found. Check the contact or choose Register member.')
   return
  }
  emit('select',result.member)
  lookupValue.value='';name.value='';phone.value='';email.value='';create.value=false
 } catch(e) {
  const code=e instanceof Error?e.message:String(e)
  error.value=codeMessage(code)+' ('+code+')'
 } finally {busy.value=false;emit('busyChange',false)}
}
</script>
<template>
 <section class="member-panel" :aria-busy="busy">
  <div class="member-head"><div class="member-title"><component :is="icon" name="users" :size="20"/><h3>{{t('Member pelanggan','Customer membership')}}</h3></div>
   <button v-if="!selected" type="button" :disabled="busy||disabled" @click="toggle">{{create?t('Cari member','Find member'):t('Daftar member','Register member')}}</button>
  </div>
  <div v-if="selected" class="member-selected">
   <div><strong>{{selected.name}}</strong><p>{{t('Terdaftar melalui kasir','Enrolled at checkout')}}</p></div>
   <button type="button" :disabled="disabled" @click="emit('select',null)">{{t('Ganti','Change')}}</button>
  </div>
  <form v-else class="member-form" @submit.prevent="run">
   <fieldset :disabled="busy||disabled">
    <template v-if="create">
     <label>{{t('Nama lengkap','Full name')}}<input v-model="name" autocomplete="off" required maxlength="160"/></label>
     <label>{{t('Nomor HP','Mobile number')}}<input v-model="phone" type="tel" autocomplete="off" required maxlength="32"/></label>
     <label>Email<input v-model="email" type="email" autocomplete="off" required maxlength="254"/></label>
    </template>
    <template v-else>
     <label>{{t('Cari berdasarkan','Search by')}}<select v-model="kind" @change="lookupValue=''"><option value="phone">{{t('Nomor HP','Mobile number')}}</option><option value="email">Email</option></select></label>
     <label>{{kind==='phone'?t('Nomor HP member','Member mobile number'):'Email'}}<input v-model="lookupValue" :type="kind==='email'?'email':'tel'" autocomplete="off" required :maxlength="kind==='email'?254:32"/></label>
    </template>
    <button class="member-primary" type="submit"><component :is="icon" :name="create?'user-plus':'search'" :size="18"/>{{busy?t('Memproses…','Working…'):create?t('Simpan member','Save member'):t('Cari','Search')}}</button>
   </fieldset>
  </form>
  <p v-if="create">{{t('Mendaftarkan member tidak memberikan izin promosi atau mengaktifkan login pelanggan.','Enrolling a member does not grant marketing consent or enable customer login.')}}</p>
  <p v-if="feedback" role="status">{{feedback}}</p>
  <p v-if="error" role="alert">{{error}}</p>
 </section>
</template>
<style scoped>
.member-panel{margin:1.25rem 0;padding:1rem;border:1px solid var(--oq-soft,#99B7FC);border-radius:14px;background:var(--oq-white,#FFFFFF);color:var(--oq-dark,#202020)}
.member-head,.member-title,.member-selected{display:flex;align-items:center;gap:.65rem}.member-head,.member-selected{justify-content:space-between;flex-wrap:wrap}
.member-panel h3,.member-panel p{color:var(--oq-dark,#202020);margin:.35rem 0}.member-form fieldset{display:flex;align-items:end;flex-wrap:wrap;gap:.65rem;border:0;padding:0;margin:1rem 0}
.member-form label{display:grid;gap:.25rem;min-width:9rem;flex:1}.member-panel input,.member-panel select,.member-panel button{min-height:44px;border:1px solid var(--oq-soft,#99B7FC);border-radius:9px;padding:.55rem .7rem;font:inherit;color:var(--oq-dark,#202020);background:var(--oq-white,#FFFFFF)}
.member-panel button{display:inline-flex;gap:.4rem;align-items:center}.member-panel .member-primary{background:var(--oq-primary,#3965FA);color:var(--oq-white,#FFFFFF);border-color:var(--oq-primary,#3965FA)}
.member-panel :focus-visible{outline:3px solid var(--oq-primary,#3965FA);outline-offset:2px}
</style>
