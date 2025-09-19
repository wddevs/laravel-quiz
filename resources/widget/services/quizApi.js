const API_BASE = import.meta.env.VITE_API_BASE || 'https://quiz.web-deluxe.com/api/v1'


export async function getQuizConfig(uuid, { etag, signal } = {}) {
    const headers = { Accept: 'application/json' }
    if (etag) headers['If-None-Match'] = etag

    const res = await fetch(`${API_BASE}/quizzes/${uuid}/config`, {
        headers,
        credentials: 'same-origin',
        signal,
    })

    const nextEtag = res.headers.get('ETag') || null

    if (res.status === 304) {
        return { status: 304, etag: nextEtag, data: null }
    }

    if (!res.ok) {
        throw new Error(`HTTP ${res.status}`)
    }

    const data = await res.json()
    return { status: 200, etag: nextEtag, data }
}

export async function submitLead(payload, { signal } = {}) {
    const res = await fetch(`${API_BASE}/quizzes/${payload.quizId}/submissions`, {
        method: 'POST',
        headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify(payload),
        signal,
    })
    if (!res.ok) throw new Error(`HTTP ${res.status}`)
    return res.json().catch(() => ({}))
}

export class ApiError extends Error {
    constructor(message, { status, data, etag } = {}) {
        super(message)
        this.name = 'ApiError'
        this.status = status
        this.data = data
        this.etag = etag
    }
}
