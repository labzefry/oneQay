<script setup lang="ts">
import {computed} from 'vue';
import collection from './icons.json';
const props=defineProps<{name:string}>();
const body=computed(()=>(collection.icons as Record<string,{body:string}>)[props.name]?.body??'');
</script>
<template><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false" v-html="body"/></template>
