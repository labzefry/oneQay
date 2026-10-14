<script setup lang="ts">
// Author by Lab | zefry. Decimal strings retain exact values and reject exponent notation.
const props=withDefaults(defineProps<{modelValue: string | number; decimals?:number; required?:boolean; disabled?:boolean; max?:string; placeholder?:string}>(),{decimals:0,required:true,disabled:false});
const emit=defineEmits<{ 'update:modelValue':[value:string] }>();
function input(event:Event):void {const el=event.target as HTMLInputElement;const v=el.value;const pattern=props.decimals===0?/^\d*$/:new RegExp(`^\\d*(?:[.,]\\d{0,${props.decimals}})?$`);if(!pattern.test(v)){el.value=String(props.modelValue);return;}emit('update:modelValue',v.replace(',','.'));}
</script>
<template><input :value="modelValue" type="text" :inputmode="decimals?'decimal':'numeric'" :pattern="decimals?'[0-9]+([.][0-9]{1,'+decimals+'})?':'[0-9]+'" :required="required" :disabled="disabled" :placeholder="placeholder" autocomplete="off" @input="input" /></template>
