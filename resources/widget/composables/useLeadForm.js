import { reactive, ref, computed } from 'vue'
import { useQuizStore } from '../stores/quiz'
import { submitLead } from '../services/quizApi'

const PHONE_RE = /^[+]?[\d\s().-]{7,20}$/

export function useLeadForm() {
    const quiz = useQuizStore()

    // --- конфіг знижки з маркетингу
    const discountCfg  = computed(() => quiz.quizData?.marketing?.discount || null)
    const maxDiscount  = computed(() => Number(discountCfg.value?.value) || 0)
    const effect       = computed(() => discountCfg.value?.effect || 'increasing') // 'increasing' | 'fixed'
    const type         = computed(() => discountCfg.value?.type   || 'percent')

    // Скільки кроків/відповідей
    const totalSteps = computed(() => quiz.quizData?.steps?.length || 0)
    const answeredCount = computed(() => Object.keys(quiz.answers || {}).length)

    // Головна формула знижки (STEP GAIN): пропорційно кількості відповідей
    const discountPercent = computed(() => {
        if (!maxDiscount.value) return 0
        if (effect.value === 'fixed') return maxDiscount.value
        if (!totalSteps.value) return 0
        const p = Math.round((answeredCount.value / totalSteps.value) * maxDiscount.value)
        return Math.min(p, maxDiscount.value)
    })

    // --- state з конфіга leadForm
    const leadCfg = computed(() => quiz.quizData?.leadForm || {})
    const fields = computed(() => Array.isArray(leadCfg.value.fields) ? leadCfg.value.fields : [])

    // ініціалізація форми за ключами полів
    const form = reactive(Object.fromEntries(fields.value.map(f => [f.key, ''])))
    const privacy = ref(true) // якщо треба — візьми з leadCfg
    const errors = reactive({})
    const submitting = ref(false)

    // --- валідація
    function validateField(key, value) {
        const def = fields.value.find(f => f.key === key)
        let err = ''
        const val = (value ?? '').toString().trim()

        if (def?.required && !val) err = 'Обов’язкове поле'
        if (!err && def?.type === 'tel' && val && !PHONE_RE.test(val)) err = 'Некоректний телефон'
        if (!err && def?.type === 'name' && val.length < 2) err = 'Занадто коротке ім’я'

        errors[key] = err
        return !err
    }

    function validateAll() {
        let ok = true
        for (const f of fields.value) {
            if (!validateField(f.key, form[f.key])) ok = false
        }
        if (!privacy.value) {
            errors.__privacy = 'Підтвердіть політику конфіденційності'
            ok = false
        } else {
            delete errors.__privacy
        }
        return ok
    }

    // --- маппінг "типу питання" для бекенду
    function mapType(step) {
        // спроба інферу типу для беку
        const hasImages = Array.isArray(step.answers) && step.answers.some(a => a?.image)
        if (hasImages) return 'images'
        // якщо у вас є власний атрибут uiType — віддайте його:
        // return step.uiType || step.type || 'variants'
        return step.type === 'text' ? 'text' : 'variants'
    }

    // --- побудова answers[]
    function buildAnswersArray() {
        const data = quiz.quizData
        if (!data?.steps) return []
        const ans = quiz.answers // { [stepId]: answerId/value }
        return data.steps.map((step) => {
            const raw = ans[step.id]
            // витягуємо label відповіді якщо це radio/checkbox
            let label = raw
            if (Array.isArray(step.answers)) {
                const found = step.answers.find(a => a.id === raw || a.value === raw)
                if (found?.label) label = found.label
            }
            return {
                q: step.title,
                t: mapType(step),
                a: (label ?? '').toString(),
            }
        })
    }

    // --- побудова contacts{}
    function buildContacts() {
        const out = {}
        for (const f of fields.value) {
            const clean = (form[f.key] ?? '').toString().trim()
            out[f.key] = clean
        }
        // сумісність з потрібним беком: "text" = телефон?
        if (out.phone && !out.text) out.text = out.phone
        return out
    }

    // --- extra метадані (на що вистачає фронта)
    function buildExtra() {
        const tz = -new Date().getTimezoneOffset() / 60
        const qs = new URLSearchParams(location.search)
        return {
            href: typeof location !== 'undefined' ? location.href : '',
            userAgent: typeof navigator !== 'undefined' ? navigator.userAgent : '',
            lang: typeof navigator !== 'undefined' ? (navigator.language || 'uk') : 'uk',
            timezone: tz,
            visitor: qs.get('vid') || '',           // 👈 ДОДАТИ
            session_id: qs.get('sid') || '',           // 👈 ДОДАТИ
            parent_origin: qs.get('parentOrigin') || '',           // 👈 ДОДАТИ
            project: qs.get('project') || '',           // 👈 ДОДАТИ
            page: qs.get('page') || '',           // 👈 ДОДАТИ
            cookies: {},
        }
    }

    // --- повний payload
    function buildPayload() {
        const data = quiz.quizData
        return {
            version: '1',
            answers: buildAnswersArray(),
            contacts: buildContacts(),
            status: 'new',
            viewed: null,
            paid: false,
            created: new Date().toISOString(),
            quizId: data?.uuid || data?._id || '',
            extra: buildExtra(),
            result: {},
            userId: null,
            id: null,
            projectId: null,
            delivery: {},
            bonuses: {
                enabled: Boolean(data?.marketing?.bonusesEnabled),
                items: Array.isArray(data?.marketing?.bonuses) ? data.marketing.bonuses : [],
                title: data?.marketing?.bonusesTitle || '',
            },
            preset: 'quiz',
            form: { id: 'lead-form' },
            updated: new Date().toISOString(),
            discount_percent: discountPercent.value || 0,
        }
    }

    // --- submit
    async function submit({ autoAdvance = true } = {}) {
        if (!validateAll()) return { ok: false, reason: 'validation' }
        submitting.value = true
        try {
            const payload = buildPayload()
            const resp = await submitLead(payload)

            console.log('Lead submitted, response:', payload)

            if (autoAdvance) quiz.goToThanks?.()
            return { ok: true, resp, payload }
        } catch (e) {
            errors.__submit = e?.message || 'Не вдалося надіслати форму'
            return { ok: false, reason: 'network', error: e }
        } finally {
            submitting.value = false
        }
    }

    return {
        // state
        fields, form, privacy, errors, submitting, leadCfg,
        // api
        validateField, validateAll, buildPayload, submit,
    }
}
