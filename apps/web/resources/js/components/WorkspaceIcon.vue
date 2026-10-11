<script setup lang="ts">
// Lucide via Iconify, ISC license. Author by Lab | zefry.
import { computed } from 'vue';
import collection from '../icons/workspace-icons.json';
const props = withDefaults(defineProps<{ name: string; size?: number }>(), { size: 20 });
const icons: Record<string, { body: string; width?: number; height?: number }> = collection.icons;
const icon = computed(() => icons[props.name] ?? icons['layout-dashboard']);
</script>
<template>
    <svg :width="size" :height="size" :viewBox="`0 0 ${icon.width ?? collection.width} ${icon.height ?? collection.height}`"
        fill="none" aria-hidden="true" focusable="false" class="oq-icon" v-html="icon.body" />
</template>
