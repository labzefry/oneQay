// @ts-expect-error Shared build-only JavaScript plugin.
import { surfaceColors } from '../../packages/ui-theme/surface-colors.mjs'
import { fileURLToPath } from 'node:url'
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
export default defineConfig({ css: { postcss: { plugins: [surfaceColors()] } }, resolve: {alias: {vue: fileURLToPath(new URL('./node_modules/vue/dist/vue.runtime.esm-bundler.js',import.meta.url))}}, plugins: [vue()], clearScreen: false, server: { port: 1420, strictPort: true }, build: { target: 'es2021' } })
