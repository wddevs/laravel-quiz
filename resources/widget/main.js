import { createApp, nextTick  } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import './tailwind.css'
import './styles.sass'

const params = new URLSearchParams(location.search)
const uuid = params.get('uuid') || 'ecdc23f2-38df-4465-88c9-b92363cb43fb'



const app = createApp(App, { uuid })
app.use(createPinia())
app.mount('#app')

// fire-and-forget impression beacon


if (window.parent !== window) {
    const sendSize = () => {
        const h = Math.max(
            document.documentElement.scrollHeight,
            document.body.scrollHeight
        )
        // на проді заміни '*' на конкретний домен клієнта якщо потрібно
        window.parent.postMessage({ type: 'size', height: h }, '*')
    }
    sendSize()
    nextTick(sendSize)
    const ro = new ResizeObserver(sendSize)
    ro.observe(document.documentElement)
    ro.observe(document.body)
}
