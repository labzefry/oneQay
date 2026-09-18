<script setup lang="ts">
import { computed, ref } from 'vue'

defineProps<{ headline: string }>()

const enabled = document.querySelector('meta[name="oneqay-merchant-entry"]')?.getAttribute('content') === 'enabled'
const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? ''
const tenantId=ref(''), identityId=ref(''), password=ref(''), organizationId=ref(''), outletId=ref(''), deviceId=ref(''), code=ref('')
const phase=ref<'login'|'challenge'|'enrollment'|'reenroll'>('login'), busy=ref(false), error=ref(''), provisioningUri=ref(''), secret=ref('')
const ready=computed(()=>!!tenantId.value.trim()&&!!identityId.value.trim()&&!!password.value&&!!organizationId.value.trim())

async function post(url:string,body:Record<string,string>){
 const r=await fetch(url,{method:'POST',credentials:'same-origin',headers:{Accept:'application/json','Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify(body)})
 return {r,data:r.status===204?{}:await r.json().catch(()=>({}))}
}
async function login(){
 if(!ready.value||busy.value)return; busy.value=true; error.value=''
 const body:Record<string,string>={tenant_id:tenantId.value.trim(),identity_id:identityId.value.trim(),password:password.value,organization_id:organizationId.value.trim()}
 if(outletId.value.trim())body.outlet_id=outletId.value.trim(); if(deviceId.value.trim())body.device_id=deviceId.value.trim()
 try{const {r,data}=await post('/auth/login',body); password.value=''
  if(r.ok&&data.status==='ok'){location.assign('/pos');return}
  if(r.status===202&&data.code==='MFA_CHALLENGE_REQUIRED'){phase.value='challenge';return}
  if(r.status===202&&data.code==='MFA_ENROLLMENT_REQUIRED'){const x=await post('/auth/mfa/totp/enrollment/start',{});if(x.r.ok){provisioningUri.value=String(x.data.provisioning_uri??'');secret.value=String(x.data.secret??'');phase.value='enrollment';return}}
  error.value='Sign-in could not be completed. Verify the authorized merchant context and try again.'
 }catch{error.value='Sign-in could not be completed safely.'}finally{busy.value=false}
}
async function verify(enrollment=false){
 if(!/^\d{6}$/.test(code.value)||busy.value)return;busy.value=true;error.value=''
 try{const {r,data}=await post(enrollment?'/auth/mfa/totp/enrollment/confirm':'/auth/mfa/totp/challenge',{code:code.value});code.value=''
  if(r.ok){if(enrollment){phase.value='reenroll';return}if(data.status==='ok'){location.assign('/pos');return}}
  error.value='Verification failed. No application access was granted.'
 }catch{error.value='Verification could not be completed safely.'}finally{busy.value=false}
}
function reset(){phase.value='login';code.value='';provisioningUri.value='';secret.value='';error.value=''}
</script>

<template>
 <main v-if="enabled" class="entry">
  <section class="brand"><p class="eyebrow">oneQay · Merchant Workspace</p><h1>Run your business from one secure workspace.</h1><p>First-party access remains tenant-scoped, session-controlled and permission-filtered.</p></section>
  <section class="card">
   <template v-if="phase==='login'"><p class="eyebrow">First-party access</p><h2>Sign in</h2><p class="muted">Use the exact merchant context provisioned for this installation.</p>
    <form @submit.prevent="login"><label>Tenant ID<input v-model="tenantId" required autocomplete="organization"></label><label>Identity ID<input v-model="identityId" required autocomplete="username"></label><label>Password<input v-model="password" type="password" required autocomplete="current-password"></label><label>Organization ID<input v-model="organizationId" required></label><div class="pair"><label>Outlet ID<input v-model="outletId"></label><label>Device ID<input v-model="deviceId"></label></div><button :disabled="!ready||busy">{{busy?'Verifying…':'Continue securely'}}</button></form>
   </template>
   <template v-else-if="phase==='challenge'"><p class="eyebrow">Additional verification</p><h2>Authenticator code</h2><p class="muted">Application access remains locked until MFA succeeds.</p><form @submit.prevent="verify(false)"><label>6-digit code<input v-model="code" inputmode="numeric" maxlength="6" autocomplete="one-time-code"></label><button :disabled="busy||!/^\d{6}$/.test(code)">Verify and open workspace</button></form></template>
   <template v-else-if="phase==='enrollment'"><p class="eyebrow">MFA enrollment</p><h2>Protect this account</h2><div class="secret"><small>Provisioning URI</small><code>{{provisioningUri}}</code><small>Secret</small><code>{{secret}}</code></div><form @submit.prevent="verify(true)"><label>6-digit code<input v-model="code" inputmode="numeric" maxlength="6"></label><button :disabled="busy||!/^\d{6}$/.test(code)">Confirm enrollment</button></form></template>
   <template v-else><p class="eyebrow">Enrollment complete</p><h2>Sign in again</h2><p class="muted">The enrollment session was invalidated by design. Start a fresh sign-in.</p><button @click="reset">Return to sign in</button></template>
   <p v-if="error" class="error" role="alert">{{error}}</p><footer>No public registration · No implicit permission grant · Lab | zefry</footer>
  </section>
 </main>
 <main v-else class="foundation-shell"><section class="foundation-card" aria-labelledby="foundation-title"><p class="eyebrow">Lab | zefry · M7.1</p><h1 id="foundation-title">{{headline}}</h1><p>Local/Test/CI application skeleton. Business capabilities, deployment, and Production remain outside this milestone.</p></section></main>
</template>

<style scoped>
.entry{min-height:100vh;display:grid;grid-template-columns:1.05fr .95fr;background:#f4f6f8;color:#172033;font-family:Inter,ui-sans-serif,system-ui,sans-serif}.brand{padding:clamp(40px,8vw,110px);display:flex;flex-direction:column;justify-content:center;background:#101828;color:#fff}.brand h1{font-size:clamp(2.5rem,5vw,4.8rem);line-height:1.02;letter-spacing:-.055em;margin:0}.brand p{max-width:620px;color:#cbd5e1;line-height:1.7}.eyebrow{font-size:.72rem;font-weight:850;letter-spacing:.13em;text-transform:uppercase;opacity:.7}.card{align-self:center;justify-self:center;width:min(480px,calc(100% - 40px));background:#fff;border:1px solid #e4e7ec;border-radius:22px;padding:32px;box-shadow:0 22px 55px rgba(16,24,40,.09)}h2{font-size:1.8rem;margin:0}.muted{color:#667085;line-height:1.55}form{display:grid;gap:14px}label{display:grid;gap:6px;font-size:.76rem;font-weight:750;color:#475467}input{border:1px solid #d0d5dd;border-radius:10px;padding:11px 12px;font:inherit}.pair{display:grid;grid-template-columns:1fr 1fr;gap:12px}button{border:0;border-radius:11px;padding:12px 15px;background:#101828;color:#fff;font-weight:800}button:disabled{opacity:.45}.error{background:#fff1f0;color:#912018;border-radius:10px;padding:11px}.secret{display:grid;gap:7px;background:#f8fafc;padding:13px;border-radius:12px}.secret code{overflow-wrap:anywhere;font-size:.72rem}footer{margin-top:22px;color:#98a2b3;font-size:.68rem;text-align:center}@media(max-width:850px){.entry{grid-template-columns:1fr}.brand{padding:42px 24px}.card{margin:28px auto}.pair{grid-template-columns:1fr}}
</style>
