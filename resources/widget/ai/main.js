import { createApp, nextTick } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import './quiz-styles.sass'

// Отримуємо UUID з URL параметрів
const params = new URLSearchParams(location.search)
const uuid = params.get('uuid') || 'ecdc23f2-38df-4465-88c9-b92363cb43fb'

// Створюємо додаток
const app = createApp(App, { uuid })
app.use(createPinia())
app.mount('#app')

// Iframe функціональність
if (window.parent !== window) {
  const sendSize = () => {
    const h = Math.max(
      document.documentElement.scrollHeight,
      document.body.scrollHeight
    )
    // На продакшені заміни '*' на конкретний домен клієнта якщо потрібно
    window.parent.postMessage({ type: 'size', height: h }, '*')
  }
  
  sendSize()
  nextTick(sendSize)
  
  const ro = new ResizeObserver(sendSize)
  ro.observe(document.documentElement)
  ro.observe(document.body)
}

