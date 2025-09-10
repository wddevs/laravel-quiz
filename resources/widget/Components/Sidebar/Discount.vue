<script setup>
import { computed } from 'vue'
import { useQuizStore } from '../../stores/quiz.js'
import { storeToRefs } from 'pinia'

const quiz = useQuizStore()
const { quizData, answers, totalSteps } = storeToRefs(quiz)

/* marketing config */
const marketing   = computed(() => quizData.value?.marketing || {})
const discountCfg = computed(() => marketing.value?.discount || null)

const maxPercent = computed(() => {
    const v = Number(discountCfg.value?.value ?? 0)
    return Number.isFinite(v) ? v : 0
})

/* скільки вже відповідей */
const answeredCount = computed(() => Object.keys(answers.value || {}).length)

/* крок знижки: max / totalSteps (захист від ділення на 0) */
const stepGain = computed(() => {
    const steps = Number(totalSteps.value || 0)
    if (!steps || !maxPercent.value) return 0
    return maxPercent.value / steps
})

/* фактична знижка:
   - якщо effect === 'increasing' → пропорційно прогресу
   - інакше (fixed/інше) → просто maxPercent
   округлюємо до найближчого цілого, обмежуємо 0..maxPercent */
const discountValue = computed(() => {
    const cfg = discountCfg.value
    if (!cfg || !maxPercent.value) return 0

    if (cfg.type === 'percent') {
        if (cfg.effect === 'increasing') {
            const raw = answeredCount.value * stepGain.value
            const rounded = Math.round(raw)
            return Math.min(Math.max(rounded, 0), maxPercent.value)
        }
        return maxPercent.value
    }
    return 0
})

const discountTitle = computed(() => discountCfg.value?.title || 'Твоя знижка')
</script>

<template>
    <div class="sidebar__discount">
        <div class="sidebar__discount-text">
            <span>{{ discountTitle }}:</span>
            <div class="sidebar__discount-value"
                 :aria-label="`${discountTitle}: ${discountValue}%`">
                <span>{{ discountValue }}%</span>
                <svg class="arrow arrow_animation" width="8" height="8" viewBox="0 0 10 10" fill="none">
                    <path d="M5 0L0.757355 4.24265L2.17157 5.65686L5 2.82843L7.82843 5.65686L9.24265 4.24265L5 0Z" fill="#44bc75"/>
                    <path d="M5 4L0.757355 8.24265L2.17157 9.65686L5 6.82843L7.82843 9.65686L9.24265 8.24265L5 4Z" fill="#44bc75"/>
                </svg>
            </div>
        </div>
    </div>
</template>
