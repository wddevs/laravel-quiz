/** @type {import('tailwindcss').Config} */
export default {
    content: ['./index.html', './**/*.{vue,js}'],
    prefix: 'rq-',
    theme: {
        extend: {
            colors: { brand: '#3B82F6' }
        }
    },
    // Якщо рендер не в iframe і боїшся глобального reset:
    // corePlugins: { preflight: false },
    plugins: []
}
