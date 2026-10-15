<script setup lang="ts">
// Author by Lab | zefry. Lucide icons retrieved from the Iconify API and bundled for availability and CSP safety.
import { computed } from 'vue';
import data from './icons.json';
const props=withDefaults(defineProps<{name:string;size?:number}>(),{size:20});
const icons=data.icons as Record<string,{body:string}>;const body=computed(()=>icons[props.name]?.body??icons['store'].body);
</script>
<template><svg class="oq-icon" :width="size" :height="size" :viewBox="`0 0 ${data.width} ${data.height}`" fill="none" aria-hidden="true" focusable="false" v-html="body"></svg></template>
<style>.oq-icon{display:inline-block;vertical-align:middle;flex-shrink:0}</style>
