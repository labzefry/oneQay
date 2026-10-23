<script setup lang="ts">
import ServicePosWorkspace from '../../../../../../packages/service-pos-ui/ServicePosWorkspace.vue';import{locale}from'../../i18n';defineProps<{initial:any}>();
async function load(){const r=await fetch('/pos/service/data',{credentials:'same-origin',headers:{Accept:'application/json'},signal:AbortSignal.timeout(15000)});if(!r.ok)throw Error(String(r.status));return r.json();}
async function send(input:Record<string,unknown>){const r=await fetch('/pos/service/commands',{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json',Accept:'application/json','X-CSRF-TOKEN':document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content??''},body:JSON.stringify(input),signal:AbortSignal.timeout(20000)});const d=await r.json();if(!r.ok)throw Error(d.code??String(r.status));return d;}
</script>
<template><ServicePosWorkspace :initial="initial" :load="load" :send="send" :language="locale"/></template>
