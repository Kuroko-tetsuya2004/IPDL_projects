import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'
import { resolve } from 'path'

export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/css/app.css', 'resources/js/app.js'],
      refresh: true,
    }),
    vue({
      template: {
        transformAssetUrls: {
          base: null,
          includeAbsolute: false,
        },
      },
    }),
  ],
  resolve: {
    alias: {
      '@': resolve(__dirname, 'resources/js'),
      '@components': resolve(__dirname, 'resources/js/Components'),
      '@pages': resolve(__dirname, 'resources/js/Pages'),
      '@layouts': resolve(__dirname, 'resources/js/Layouts'),
      '@composables': resolve(__dirname, 'resources/js/composables'),
    },
  },
})
