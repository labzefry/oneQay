// @ts-expect-error Shared build-only JavaScript plugin.
import { surfaceColors } from '../../packages/ui-theme/surface-colors.mjs'
import { fileURLToPath } from 'node:url'
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import laravel from 'laravel-vite-plugin'

export default defineConfig({ css: { postcss: { plugins: [surfaceColors()] } }, resolve: {alias: {vue: fileURLToPath(new URL('./node_modules/vue/dist/vue.runtime.esm-bundler.js',import.meta.url))}},
  plugins: [
    laravel({
      input: ['resources/js/app.ts'],
      refresh: false,
    }),
    vue(),
  ],
})
