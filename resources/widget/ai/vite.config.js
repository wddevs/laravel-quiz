import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import tailwind from '@tailwindcss/vite'
import path from 'node:path'

export default defineConfig({
    base: '/embed/',
    root: __dirname,
    plugins: [vue(), tailwind()],
    server: { 
        port: 5175, 
        strictPort: true 
    },
    resolve: {
        alias: {
            '@': path.resolve(__dirname, '.'),
        },
    },
    build: {
        outDir: path.resolve(__dirname, '../../public/embed'),
        emptyOutDir: true,
        rollupOptions: {
            input: path.resolve(__dirname, 'index.html'),
            output: {
                entryFileNames: `quiz.[hash].js`,
                assetFileNames: a =>
                    a.name && a.name.endsWith('.css') ? `quiz.[hash].css` : `[name].[hash].[ext]`
            }
        }
    }
})

