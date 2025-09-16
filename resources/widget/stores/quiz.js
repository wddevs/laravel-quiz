// stores/quiz.js
import { defineStore } from 'pinia'
import { ref, computed, shallowRef } from 'vue'

const API_BASE = import.meta.env.VITE_API_BASE || 'https://quiz.web-deluxe.com/api/v1'
const TTL_MS = 5 * 60 * 1000 // 5 хв

// stores/quiz.js
import { getQuizConfig } from '../services/quizApi'
import { emitOpenOnce } from '../services/events'

export const useQuizStore = defineStore('quiz', () => {
    // --- state
    const quizData = shallowRef(null)           // беремо як є з бекенду
    const status = ref('idle')                  // 'idle'|'loading'|'ready'|'error'
    const error = ref(null)

    const inactive = ref(false)    // <-- NEW
    const notFound = ref(false)    // <-- NEW

    const currentStep = ref(0)
    const answers = ref({})

    const isLoading = computed(() => status.value === 'loading')
    const isReady   = computed(() => status.value === 'ready')
    const isError   = computed(() => status.value === 'error')

    const isInactive = computed(() => inactive.value)  // <-- NEW
    const isNotFound = computed(() => notFound.value)  // <-- NEW



    // --- state
    const phase = ref('start') // 'start' | 'questions' | 'leadform' | 'thanks'




    // кеш + аборт
    const _cache = new Map() // uuid -> { data, ts, etag? }
    let _controller = null

    // --- getters (без жодних перетворень)
    const totalSteps = computed(() => quizData.value?.steps?.length ?? 0)
    const currentStepData = computed(() => {
        const steps = quizData.value?.steps
        const idx = currentStep.value
        if (!steps || idx < 0 || idx >= steps.length) return null
        return steps[idx]
    })
    const isCompleted = computed(() => currentStep.value >= (totalSteps.value - 1))
    const hasStartPage = computed(() => !!quizData.value?.startPage?.enabled)
    const canGoNext = computed(() => currentStep.value < (totalSteps.value - 1))
    const canGoPrev = computed(() => currentStep.value > 0)

    // --- derived flags
    const isStart     = computed(() => phase.value === 'start')
    const isQuestions = computed(() => phase.value === 'questions')
    const isLeadForm  = computed(() => phase.value === 'leadform')
    const isThanks    = computed(() => phase.value === 'thanks')

    function getStepById(id) {
        return quizData.value?.steps?.find(s => s.id === id) ?? null
    }

    // --- actions

    function applyQuiz(raw) {
        quizData.value = raw
        currentStep.value = 0
        // якщо маєш phase у сторі:
        phase.value = raw?.startPage?.enabled ? 'start' : 'questions'
    }

    async function loadQuiz(uuid, { force = false } = {}) {
        if (!uuid) return
        status.value = 'loading'
        error.value = null

        const cached = _cache.get(uuid)
        const now = Date.now()

        // TTL-кеш
        if (cached && !force && (now - cached.ts < TTL_MS)) {
            applyQuiz(cached.data)
            status.value = 'ready'
            hydrateAnswers(uuid)
            return
        }

        // скасувати попередній запит
        if (_controller) _controller.abort()
        _controller = new AbortController()

        try {
            const { status: httpStatus, etag, data } = await getQuizConfig(uuid, {
                etag: cached?.etag,
                signal: _controller.signal,
            })

            if (httpStatus === 304 && cached) {
                // не змінилось — беремо кеш, але оновимо мітку часу/etag
                applyQuiz(cached.data)
                _cache.set(uuid, { data: cached.data, ts: now, etag: etag ?? cached.etag })
            } else {
                applyQuiz(data)
                _cache.set(uuid, { data, ts: now, etag })
            }

            status.value = 'ready'
            hydrateAnswers(uuid)
        } catch (e) {
            if (e?.name === 'AbortError') return

            // Важливо: очікуємо e.status із quizApi
            const st = e?.status ?? e?.response?.status

            if (st === 423 || e?.data?.status === 'inactive') {
                inactive.value = true
                // не кешуємо, щоб не зафіксувати "заморожений" стан назавжди
                _cache.delete(uuid)
            } else if (st === 404) {
                notFound.value = true
                _cache.delete(uuid)
            } else {
                error.value = e?.message || 'Failed to load quiz'
                status.value = 'error'
            }

            // Якщо inactive/notFound — виводимо “готово”, щоб UI показав екран повідомлення,
            // а не загальний "error", бо це нормальні очікувані стани
            if (inactive.value || notFound.value) status.value = 'ready'
        } finally {
            _controller = null
        }
    }

    function setAnswer(stepId, answer) {
        answers.value[stepId] = answer
        // persistAnswers(quizData.value?.uuid)

        // авто-Next для radio
        const type = currentStepData.value?.type
        if (type === 'radio') {
            setTimeout(() => proceed(), 150) // трішки затримки для анімації
        }
    }

    function startQuiz() {
        emitOpenOnce({ userInitiated: true })
        phase.value = 'questions'
    }
    function goToLeadForm() { phase.value = 'leadform' }
    function goToThanks() { phase.value = 'thanks' }

    function proceed() {
        if (phase.value === 'start') {
            startQuiz()
            return
        }
        if (phase.value === 'questions') {
            if (!canProceedToNext.value) return
            if (canGoNext.value) currentStep.value++
            else goToLeadForm()
        }
        // для leadform -> thanks переведемо у сабміті форми
    }

    function restart() {
        currentStep.value = 0
        answers.value = {}
        persistAnswers(quizData.value?.uuid)
        phase.value = quizData.value?.startPage?.enabled ? 'start' : 'questions'
    }

    const canProceedToNext = computed(() => {
        const current = currentStepData.value
        if (!current) return false

        // Якщо крок обов'язковий, перевіряємо чи є відповідь
        if (current.required) {
            return isStepAnswered(current.id)
        }

        return true
    })

// Оновити nextStep:
    function nextStep() {
        if (canProceedToNext.value && canGoNext.value) {
            currentStep.value++
        }
    }

    function prevStep() {
        if (canGoPrev.value) currentStep.value--
    }

    function goToStep(indexOrId) {
        const steps = quizData.value?.steps ?? []
        let idx = indexOrId
        if (typeof indexOrId !== 'number' || indexOrId >= steps.length) {
            const foundIdx = steps.findIndex(s => s.id === indexOrId)
            if (foundIdx >= 0) idx = foundIdx
        }
        if (idx >= 0 && idx < steps.length) currentStep.value = idx
    }

    function nextUnansweredStep() {
        const steps = quizData.value?.steps ?? []
        const idx = steps.findIndex(s => s.required && !(s.id in answers.value))
        if (idx >= 0) currentStep.value = idx
    }

    // персист відповідей
    function persistAnswers(uuid) {
        if (!uuid) return
        try {
            localStorage.setItem(`quiz_answers_${uuid}`, JSON.stringify(answers.value))
        } catch {}
    }

    function hydrateAnswers(uuid) {
        if (!uuid) return
        try {
            const raw = localStorage.getItem(`quiz_answers_${uuid}`)
            if (raw) answers.value = JSON.parse(raw)
        } catch {}
    }

    // Додаткові методи для роботи з квізом
    function getAnswerForStep(stepId) {
        return answers.value[stepId] || null
    }

    function isStepAnswered(stepId) {
        return stepId in answers.value
    }

    function getAnsweredStepsCount() {
        return Object.keys(answers.value).length
    }

    function getProgressPercentage() {
        if (totalSteps.value === 0) return 0
        return Math.round((getAnsweredStepsCount() / totalSteps.value) * 100)
    }

    function submitQuiz() {
        // Логіка відправки квізу
        const quizResults = {
            uuid: quizData.value?.uuid,
            answers: answers.value,
            completedAt: new Date().toISOString(),
            progress: getProgressPercentage()
        }

        console.log('Quiz submitted:', quizResults)
        return quizResults
    }

    function clearAnswers() {
        answers.value = {}
        persistAnswers(quizData.value?.uuid)
    }

    return {
        // state
        quizData, status, error, currentStep, answers, phase,  inactive, notFound, // <-- export flags

        // flags
       isReady, isError, isInactive, isNotFound, // <-- export computed
        isStart, isQuestions, isLeadForm, isThanks,

        // getters
        totalSteps, currentStepData, isCompleted, hasStartPage, canGoNext, canGoPrev, isLoading, canProceedToNext,


        // actions
        loadQuiz, setAnswer, nextStep, prevStep, goToStep, nextUnansweredStep, restart,
        getAnswerForStep, isStepAnswered, getAnsweredStepsCount, getProgressPercentage,
        submitQuiz, clearAnswers,

        startQuiz, goToLeadForm, goToThanks, proceed,
    }
})
