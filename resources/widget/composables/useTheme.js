// useTheme.js
import { watch, onMounted } from 'vue'

const DEFAULT_THEME = {
    primary: '#3B82F6',
    bg: '#ffffff',
    text: '#0f172a',
    title: '#0b1220',
    font: 'Inter',
    btnRadius: 12,
    btnColor: '#3B82F6',
    btnTextColor: '#ffffff',
    inputRadius: 10,
}

const FALLBACK_FONT_STACK =
    "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', " +
    "'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', 'Noto Color Emoji', sans-serif"

/* ===================== Color utils ===================== */

function clamp(v, min = 0, max = 1) { return Math.min(max, Math.max(min, v)) }

function hexToRgb(hex) {
    const s = String(hex || '').trim()
    const m = /^#?([a-f\d]{3}|[a-f\d]{6})$/i.exec(s)
    if (!m) return null
    let h = m[1]
    if (h.length === 3) h = h.split('').map(ch => ch + ch).join('')
    const int = parseInt(h, 16)
    return { r: (int >> 16) & 255, g: (int >> 8) & 255, b: int & 255 }
}
function rgbToHex({ r, g, b }) {
    const toHex = (n) => n.toString(16).padStart(2, '0')
    return `#${toHex(r)}${toHex(g)}${toHex(b)}`
}
function rgbToCss({ r, g, b }) { return `rgb(${r}, ${g}, ${b})` }

function rgbToHsl({ r, g, b }) {
    r /= 255; g /= 255; b /= 255
    const max = Math.max(r, g, b), min = Math.min(r, g, b)
    let h, s, l = (max + min) / 2
    if (max === min) { h = s = 0 }
    else {
        const d = max - min
        s = l > 0.5 ? d / (2 - max - min) : d / (max + min)
        switch (max) {
            case r: h = (g - b) / d + (g < b ? 6 : 0); break
            case g: h = (b - r) / d + 2; break
            default: h = (r - g) / d + 4
        }
        h /= 6
    }
    return { h, s, l }
}
function hslToRgb({ h, s, l }) {
    let r, g, b
    if (s === 0) { r = g = b = l } else {
        const hue2rgb = (p, q, t) => {
            if (t < 0) t += 1
            if (t > 1) t -= 1
            if (t < 1 / 6) return p + (q - p) * 6 * t
            if (t < 1 / 2) return q
            if (t < 2 / 3) return p + (q - p) * (2 / 3 - t) * 6
            return p
        }
        const q = l < 0.5 ? l * (1 + s) : l + s - l * s
        const p = 2 * l - q
        r = hue2rgb(p, q, h + 1 / 3)
        g = hue2rgb(p, q, h)
        b = hue2rgb(p, q, h - 1 / 3)
    }
    return { r: Math.round(r * 255), g: Math.round(g * 255), b: Math.round(b * 255) }
}
function lighten(hex, percent) {
    const rgb = hexToRgb(hex); if (!rgb) return hex
    const hsl = rgbToHsl(rgb)
    hsl.l = clamp(hsl.l + percent / 100)
    return rgbToHex(hslToRgb(hsl))
}
function darken(hex, percent) {
    const rgb = hexToRgb(hex); if (!rgb) return hex
    const hsl = rgbToHsl(rgb)
    hsl.l = clamp(hsl.l - percent / 100)
    return rgbToHex(hslToRgb(hsl))
}
function mix(hex1, hex2, percent /*0..100 => hex2 weight*/) {
    const a = hexToRgb(hex1), b = hexToRgb(hex2)
    if (!a || !b) return hex1
    const t = clamp(percent / 100)
    const r = Math.round(a.r * (1 - t) + b.r * t)
    const g = Math.round(a.g * (1 - t) + b.g * t)
    const b2 = Math.round(a.b * (1 - t) + b.b * t)
    return rgbToHex({ r, g, b: b2 })
}
function alpha(hex, a) {
    const rgb = hexToRgb(hex); if (!rgb) return `rgba(0,0,0,${a})`
    return `rgba(${rgb.r}, ${rgb.g}, ${rgb.b}, ${clamp(a)})`
}
// відносна яскравість для контрасту
function relLum(hex) {
    const rgb = hexToRgb(hex) || { r: 0, g: 0, b: 0 }
    const norm = ['r','g','b'].map(k => {
        let c = rgb[k] / 255
        return c <= 0.03928 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4)
    })
    return 0.2126 * norm[0] + 0.7152 * norm[1] + 0.0722 * norm[2]
}
function textOn(bgHex) {
    // вибір між чорним/білим для достатнього контрасту
    const L = relLum(bgHex)
    // поріг ~0.5 працює стабільно для UI
    return L > 0.45 ? '#000000' : '#ffffff'
}

/* ===================== Google Fonts ===================== */

function ensurePreconnectOnce() {
    if (!document.getElementById('gf-preconnect-1')) {
        const l1 = document.createElement('link')
        l1.id = 'gf-preconnect-1'
        l1.rel = 'preconnect'
        l1.href = 'https://fonts.googleapis.com'
        document.head.appendChild(l1)
    }
    if (!document.getElementById('gf-preconnect-2')) {
        const l2 = document.createElement('link')
        l2.id = 'gf-preconnect-2'
        l2.rel = 'preconnect'
        l2.href = 'https://fonts.gstatic.com'
        l2.crossOrigin = 'anonymous'
        document.head.appendChild(l2)
    }
}
function gfFamily(name) {
    return encodeURIComponent(String(name || '').trim()).replace(/%20/g, '+')
}
function ensureFont(name) {
    const fam = String(name || '').trim()
    if (!fam) return
    ensurePreconnectOnce()
    const id = 'quiz-theme-font'
    const href = `https://fonts.googleapis.com/css2?family=${gfFamily(fam)}&display=swap`
    let el = document.getElementById(id)
    if (!el) {
        el = document.createElement('link')
        el.id = id
        el.rel = 'stylesheet'
        document.head.appendChild(el)
    }
    if (el.href !== href) el.href = href
}

/* ===================== Variables builder ===================== */

function toPx(n, fallback = 0) {
    const num = Number(n)
    return Number.isFinite(num) ? `${num}px` : `${fallback}px`
}

function hueShift(hex, deg) {
    const rgb = hexToRgb(hex); if (!rgb) return hex
    const hsl = rgbToHsl(rgb)
    let h = (hsl.h * 360 + (deg % 360) + 360) % 360
    const out = hslToRgb({ h: h / 360, s: hsl.s, l: hsl.l })
    return rgbToHex(out)
}

function buildVars(theme) {
    const t = { ...DEFAULT_THEME, ...(theme || {}) }

    // Базові
    const primary = t.primary

    // якщо потрібен secondary — обчислимо триадний
    const secondary = t.secondary || hueShift(primary, 120)
    // tertiary: або з конфіга, або триадний зсув +240°
    const tertiary = t.tertiary || hueShift(primary, 240)

    const bg = t.bg
    const text = t.text || textOn(bg)
    const title = t.title || textOn(bg)
    const btnColor = t.btnColor || primary
    const btnTextColor = t.btnTextColor || textOn(btnColor)

    // Похідні для primary (узгоджені відсотки)
    const color_lighten10 = lighten(primary, 10)
    const color_lighten = lighten(primary, 30)
    const color_lighten2 = lighten(primary, 60)

    const color_darken10 = darken(primary, 20)
    const color_darken = darken(primary, 40)

    // Альфи для primary
    const color_alpha05 = alpha(primary, 0.05)
    const color_alpha2 = alpha(primary, 0.2)
    const color_alpha3 = alpha(primary, 0.3)
    const color_alpha5 = alpha(primary, 0.5)
    const color_alpha7 = alpha(primary, 0.7)
    const color_alpha8 = alpha(primary, 0.8)

    // Текст на primary / текст на bg
    const color_text_on_primary = textOn(primary)
    const color_text_on_bg = textOn(bg)

    // Нейтральні та фон-альфи (від bg)
    const overlayBase = color_text_on_bg === '#000000' ? '#000000' : '#ffffff'
    const bg_alpha0 = alpha(bg, 0)
    const bg_alpha05 = alpha(overlayBase, 0.05)
    const bg_alpha07 = alpha(overlayBase, 0.07)
    const bg_alpha10 = alpha(overlayBase, 0.1)
    const bg_alpha2 = alpha(overlayBase, 0.2)
    const bg_alpha3 = alpha(overlayBase, 0.3)
    const bg_alpha5 = alpha(overlayBase, 0.5)
    const bg_alpha7 = alpha(overlayBase, 0.7)

    // Градієнт головний: від темнішого до світлішого primary
    const grad_from = rgbToCss(hexToRgb(color_darken))
    const grad_to = rgbToCss(hexToRgb(color_lighten))
    const gradient_main = `331deg, ${grad_from}, ${grad_to}`

    // Сайдбар/футер/квіз фони на базі bg
    const bg_footer = mix(bg, '#000000', 2)      // трохи темніше за bg
    const bg_sidebar = mix(bg, '#000000', 3)     // на 3% до чорного
    const bg_sidebar_alpha55 = alpha(bg, 0.55)
    const bg_sidebar_alpha0 = alpha(bg, 0)

    // Декілька рівнів «сірого» від bg (працює для будь-якого bg)
    const bg_1 = mix(bg, '#000000', 7)
    const bg_2 = mix(bg, '#000000', 10)
    const bg_3 = mix(bg, '#000000', 20)
    const bg_4 = mix(bg, '#000000', 30)
    const bg_5 = mix(bg, '#000000', 40)
    const bg_6 = bg_alpha7                 // напівпрозорий оверлей
    const bg_7 = bg_1
    const bg_8 = mix(bg, '#000000', 15)
    const bg_9 = mix(bg, '#000000', 79)

    // Квіз-контейнер фони
    const bg_quiz = bg
    const bg_quiz_alpha55 = alpha(bg_quiz, 0.55)
    const bg_quiz_alpha8 = alpha(bg_quiz, 0.8)

    // Шрифт
    const family = `'${String(t.font).replace(/'/g, "\\'")}', ${FALLBACK_FONT_STACK}`

    // Похідні для tertiary (аналогічно до primary)
    const tertiary_lighten10 = lighten(tertiary, 10)
    const tertiary_darken10  = darken(tertiary, 20)
    const tertiary_alpha5    = alpha(tertiary, 0.5)
    const tertiary_text      = textOn(tertiary)

    // (опційно) для secondary
    const secondary_text = textOn(secondary)

    return {
        /* базові */
        '--quiz-primary': primary,
        '--quiz-bg': bg,
        '--quiz-text': text,
        '--quiz-title': title,
        '--quiz-font': family,
        '--quiz-btn-radius': toPx(t.btnRadius, DEFAULT_THEME.btnRadius),
        '--quiz-input-radius': toPx(t.inputRadius, DEFAULT_THEME.inputRadius),
        '--quiz-btn-color': btnColor,
        '--quiz-btn-text-color': btnTextColor,

        /* твоя палітра під ключем --color* */
        '--color': primary,
        '--color-lighten': color_lighten,
        '--color-lighten10': color_lighten10,
        '--color-lighten2': color_lighten2,
        '--color-darken': color_darken,
        '--color-darken10': color_darken10,
        '--color-alpha': color_alpha5,
        '--color-alpha2': color_alpha2,
        '--color-alpha3': color_alpha05, // у твоєму прикладі 0.05 названо alpha3 — лишаємо логіку твоїх назв
        '--color-alpha7': color_alpha7,
        '--color-alpha8': color_alpha8,
        '--color-text': color_text_on_primary,
        '--color-text2': primary,
        '--color-primary-text-button': btnTextColor,

        /* текст на фоні та тайтли */
        '--color-bg-text': color_text_on_bg,
        '--color-bg-title': color_text_on_bg,

        /* градієнт */
        '--gradient-main': gradient_main,

        /* глобальні фони та альфи */
        '--color-bg-footer': bg_footer,
        '--color-bg-sidebar': bg_sidebar,
        '--color-bg-sidebar-alpha55': bg_sidebar_alpha55,
        '--color-bg-sidebar-alpha0': bg_sidebar_alpha0,

        '--color-bg-1': bg_1,
        '--color-bg-2': bg_2,
        '--color-bg-3': bg_3,
        '--color-bg-4': bg_4,
        '--color-bg-5': bg_5,
        '--color-bg-6': bg_6,
        '--color-bg-7': bg_7,
        '--color-bg-8': bg_8,
        '--color-bg-9': bg_9,

        '--color-bg-quiz': bg_quiz,
        '--color-bg-quiz-alpha55': bg_quiz_alpha55,
        '--color-bg-quiz-alpha8': bg_quiz_alpha8,

        '--color-bg-alpha0': bg_alpha0,
        '--color-bg-alpha05': bg_alpha05,
        '--color-bg-alpha07': bg_alpha07,
        '--color-bg-alpha10': bg_alpha10,
        '--color-bg-alpha2': bg_alpha2,
        '--color-bg-alpha3': bg_alpha3,
        '--color-bg-alpha5': bg_alpha5,
        '--color-bg-alpha7': bg_alpha7,

        /* інпути/кнопки */
        '--input-border-radius': toPx(t.inputRadius, DEFAULT_THEME.inputRadius),
        '--input-padding': '1.25rem',
        '--button-border-radius': toPx(t.btnRadius, DEFAULT_THEME.btnRadius),

        /* дублікати зручних alias */
        '--font': `'${String(t.font).replace(/'/g, "\\'")}'`,

        '--color-secondary': secondary,
        '--color-secondary-text': secondary_text,

        // tertiary
        '--color-tertiary': tertiary,
        '--color-tertiary-lighten10': tertiary_lighten10,
        '--color-tertiary-darken10': tertiary_darken10,
        '--color-tertiary-alpha5': tertiary_alpha5,
        '--color-tertiary-text': tertiary_text,

        // зручно мати ще градієнт між primary ↔ tertiary
        '--gradient-accent': `331deg, ${rgbToCss(hexToRgb(darken(tertiary, 30)))}, ${rgbToCss(hexToRgb(lighten(primary, 20)))}`

    }
}

/* ===================== Public API ===================== */

/**
 * useTheme(source, { scope?, loadFont? })
 *  - source: () => theme або plain theme object
 *  - scope: 'root' (default) або Element / CSS selector
 *  - loadFont: true (default) — підвантажити Google Font
 */
export function useTheme(source, options = {}) {
    const target =
        options.scope === 'root' || !options.scope
            ? document.documentElement
            : typeof options.scope === 'string'
                ? document.querySelector(options.scope) || document.documentElement
                : options.scope

    const loadFont = options.loadFont !== false

    function apply(themeObj) {
        if (!target) return
        const vars = buildVars(themeObj)
        for (const [k, v] of Object.entries(vars)) {
            target.style.setProperty(k, v)
        }
        if (loadFont) ensureFont((themeObj && themeObj.font) || DEFAULT_THEME.font)
    }

    onMounted(() => {
        const t = typeof source === 'function' ? source() : source
        apply(t)
    })

    watch(typeof source === 'function' ? source : () => source, (t) => apply(t), { deep: true })

    return { apply }
}
