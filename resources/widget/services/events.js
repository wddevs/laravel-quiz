// Універсальний сервіс подій для віджета (Vue)
// Використання:
//   import { emitEvent, emitEventOnce } from '@/services/events'
//   await emitEventOnce('open', { userInitiated: true })

const QS = new URLSearchParams(location.search)

// Ідентифікатори/контекст, які бутлоадер прокинув у iframe
const UUID          = QS.get('uuid') || ''
const VID           = QS.get('vid')  || ''
const SID           = QS.get('sid')  || ''
const PARENT_ORIGIN = QS.get('parentOrigin') || '*'

// API базовий URL (можеш задати через .env → VITE_API_BASE=https://api.example.com/api/v1)
const API_BASE = import.meta.env.VITE_API_BASE || 'https://quiz.web-deluxe.com/api/v1'

// Додатковий контекст
const PROJECT = location.host
const PAGE    = location.href

// Чи є батьківське вікно (вбудовано як <iframe>)
const hasParent = typeof window !== 'undefined' && window.parent && window.parent !== window

function toPayload(type, meta = {}) {
    return {
        type,           // 'impression' | 'open' | 'lead' | ...
        vid: VID,
        sid: SID || null,
        page: PAGE,
        project: PROJECT,
        meta
    }
}

// --- Відправка через postMessage до бутлоадера (якщо є parent) ---
function sendToParent(type, meta = {}) {
    try {
        if (!hasParent) return false
        window.parent.postMessage({ type: 'quiz:event', name: type, meta }, PARENT_ORIGIN)
        return true
    } catch {
        return false
    }
}

// --- Пряма відправка в API (/events) ---
async function sendToApi(type, meta = {}, { useBeacon = false } = {}) {
    const body = JSON.stringify(toPayload(type, meta))
    const url  = `${API_BASE}/quizzes/${UUID}/events`
    try {
        if (useBeacon && navigator.sendBeacon) {
            return navigator.sendBeacon(url, new Blob([body], { type: 'application/json' }))
        }
        const res = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body,
            keepalive: true,
        })
        return res.ok
    } catch {
        return false
    }
}

/**
 * Головна функція: шле подію.
 * Якщо є parent → через postMessage, інакше напряму в API.
 * Повертає Promise<boolean> (чи вдалося надіслати).
 */
export async function emitEvent(type, meta = {}, options = {}) {
    if (hasParent) {
        const ok = sendToParent(type, meta)
        if (ok) return true
        // якщо parent не спрацював — підстрахуємо API
        return await sendToApi(type, meta, options)
    }
    return await sendToApi(type, meta, options)
}

/**
 * Одноразова подія за сесію (щоб не дублювати, наприклад, 'open')
 * scopeKey — додатковий ключ (наприклад, сторінка або крок), за замовчуванням 'session'
 */
export async function emitEventOnce(type, meta = {}, scopeKey = 'session') {
    const key = `rq_evt_once:${type}:${UUID}:${scopeKey}`
    try {
        if (sessionStorage.getItem(key)) return false
        sessionStorage.setItem(key, '1')
    } catch {}
    return await emitEvent(type, meta)
}

// Зручно мати готові шорткати
export const emitOpenOnce  = (meta = { userInitiated: true }) => emitEventOnce('open',  meta, 'day')     // або 'session'
export const emitLeadOnce  = (meta = {})                         => emitEventOnce('lead',  meta, 'day')
export const emitImprOnce  = (meta = {})                         => emitEventOnce('impression', meta, 'page')

// На випадок, якщо треба отримати ідентифікатори з інших місць
export function getIds()    { return { uuid: UUID, vid: VID, sid: SID } }
export function hasParentWindow() { return hasParent }

export default { emitEvent, emitEventOnce, emitOpenOnce, emitLeadOnce, emitImprOnce, getIds, hasParentWindow }
