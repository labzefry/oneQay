<script setup lang="ts">
defineProps<{
  headline: string
}>()
</script>

<template>
  <main class="foundation-shell">
    <section class="foundation-card" aria-labelledby="foundation-title">
      <p class="eyebrow">Lab | zefry · M7.1</p>
      <h1 id="foundation-title">{{ headline }}</h1>
      <p>
        Local/Test/CI application skeleton. Business capabilities, deployment,
        and Production remain outside this milestone.
      </p>
    </section>
  </main>
</template>
