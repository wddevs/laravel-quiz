import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useQuizStore = defineStore('quiz', () => {
    // State
    const quizData = ref(null)
    const currentStep = ref(0)
    const answers = ref({})
    const loading = ref(false)
    const error = ref(null)
    const isCompleted = ref(false)

    // Getters
    const totalSteps = computed(() => quizData.value?.steps?.length || 0)
    const currentStepData = computed(() => {
        if (!quizData.value?.steps) return null
        return quizData.value.steps[currentStep.value]
    })
    const progress = computed(() => {
        if (totalSteps.value === 0) return 0
        return ((currentStep.value + 1) / totalSteps.value) * 100
    })
    const canGoNext = computed(() => {
        const step = currentStepData.value
        if (!step) return false

        // Перевіряємо чи є відповідь на поточному кроці
        return answers.value[step.id] !== undefined
    })
    const canGoPrev = computed(() => currentStep.value > 0)

    // Actions
    const setQuizData = (data) => {
        quizData.value = data
        error.value = null
    }

    const setAnswer = (stepId, answerId) => {
        answers.value[stepId] = answerId
    }

    const getAnswer = (stepId) => {
        return answers.value[stepId]
    }

    const nextStep = () => {
        if (canGoNext.value && currentStep.value < totalSteps.value - 1) {
            currentStep.value++
        }
    }

    const prevStep = () => {
        if (canGoPrev.value) {
            currentStep.value--
        }
    }

    const goToStep = (stepIndex) => {
        if (stepIndex >= 0 && stepIndex < totalSteps.value) {
            currentStep.value = stepIndex
        }
    }

    const setLoading = (value) => {
        loading.value = value
    }

    const setError = (err) => {
        error.value = err
    }

    const completeQuiz = () => {
        isCompleted.value = true
    }

    const resetQuiz = () => {
        currentStep.value = 0
        answers.value = {}
        isCompleted.value = false
        error.value = null
    }

    return {
        // State
        quizData,
        currentStep,
        answers,
        loading,
        error,
        isCompleted,

        // Getters
        totalSteps,
        currentStepData,
        progress,
        canGoNext,
        canGoPrev,

        // Actions
        setQuizData,
        setAnswer,
        getAnswer,
        nextStep,
        prevStep,
        goToStep,
        setLoading,
        setError,
        completeQuiz,
        resetQuiz
    }
})
