import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import tailwind from '@tailwindcss/vite'
import path from 'node:path'

export default defineConfig({
    base: '/embed/',                  // <— ВАЖЛИВО
    root: __dirname,
    plugins: [vue(), tailwind()],
    server: { port: 5174, strictPort: true },
    build: {
        outDir: path.resolve(__dirname, '../../public/embed'),
        emptyOutDir: true,
        rollupOptions: {
            input: path.resolve(__dirname, 'index.html'),
            output: {
                entryFileNames: `app.[hash].js`,
                assetFileNames: a =>
                    a.name && a.name.endsWith('.css') ? `app.[hash].css` : `[name].[hash].[ext]`
            }
        }
    }
})
