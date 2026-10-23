import { fileURLToPath } from 'node:url'
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import laravel from 'laravel-vite-plugin'

export default defineConfig({ resolve: {alias: {vue: fileURLToPath(new URL('./node_modules/vue/dist/vue.runtime.esm-bundler.js',import.meta.url))}},
  plugins: [
    laravel({
      input: ['resources/js/app.ts'],
      refresh: false,
    }),
    vue(),
  ],
})
