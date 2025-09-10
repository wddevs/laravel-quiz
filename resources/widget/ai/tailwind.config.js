/** @type {import('tailwindcss').Config} */
export default {
    content: ['./index.html', './**/*.{vue,js}'],
    prefix: 'rq-',
    theme: {
        extend: {
            colors: { 
                brand: '#3B82F6',
                accent: '#f4e4ba'
            }
        }
    },
    corePlugins: { 
        preflight: false 
    },
    plugins: []
}

