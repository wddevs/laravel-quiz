const loaded = new Set()

export function loadStyle(href) {
    return new Promise((resolve, reject) => {
        if (loaded.has(href)) return resolve()
        const link = document.createElement('link')
        link.rel = 'stylesheet'
        link.href = href
        link.onload = () => { loaded.add(href); resolve() }
        link.onerror = reject
        document.head.appendChild(link)
    })
}

export function loadScript(src) {
    return new Promise((resolve, reject) => {
        if (loaded.has(src)) return resolve()
        const s = document.createElement('script')
        s.src = src
        s.async = true
        s.onload = () => { loaded.add(src); resolve() }
        s.onerror = reject
        document.head.appendChild(s)
    })
}
