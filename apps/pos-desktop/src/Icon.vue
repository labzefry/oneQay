<script setup lang="ts">
import { computed } from 'vue'
import data from './icons/collection.json'
const props = withDefaults(defineProps<{name:string;size?:number}>(),{size:20})
const icons:Record<string,{body:string}>=data.icons
const body=computed(()=>icons[props.name]?.body??icons['circle-alert'].body)
</script>
<template><svg :width="size" :height="size" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false" v-html="body" /></template>
