<script setup>
import { computed, onMounted, onUnmounted, watch, ref, nextTick } from 'vue'
import { useQuizStore } from '../../stores/quiz.js'
import { storeToRefs } from 'pinia'
import FooterProgress from "./FooterProgress.vue";

const quizStore = useQuizStore()

const { currentStep, totalSteps, canGoPrev, answers, isCompleted, canGoNext, canProceedToNext, quizData } = storeToRefs(quizStore)

/* ---- marketing / discount ---- */
const marketing = computed(() => quizData.value?.marketing || {})
const discountCfg = computed(() => marketing.value?.discount || null)

const answeredCount = computed(() => Object.keys(answers.value || {}).length)

const maxPercent = computed(() => {
    const v = Number(discountCfg.value?.value ?? 0)
    return Number.isFinite(v) ? v : 0
})

const stepGain = computed(() => {
    const steps = Number(totalSteps.value || 0)
    return steps ? maxPercent.value / steps : 0
})

const discountValue = computed(() => {
    const cfg = discountCfg.value
    if (!cfg || !maxPercent.value) return 0
    if (cfg.type === 'percent' && cfg.effect === 'increasing') {
        const raw = answeredCount.value * stepGain.value
        return Math.min(Math.round(raw), maxPercent.value)
    }
    if (cfg.type === 'percent') return maxPercent.value
    return 0
})

const discountTitle = computed(() => discountCfg.value?.title || 'Твоя знижка')
const hasDiscount = computed(() => discountValue.value > 0)

/* ---- bonuses ---- */
const bonusesEnabled = computed(() => !!marketing.value?.bonusesEnabled)
const bonuses = computed(() => Array.isArray(marketing.value?.bonuses) ? marketing.value.bonuses : [])
const showBonuses = computed(() => bonusesEnabled.value && bonuses.value.length > 0)

const goNext = () => {
    if (canGoNext.value) {
        if (isCompleted.value) {
            // Завершуємо квіз
            quizStore.completeQuiz()
        } else {
            quizStore.nextStep()
        }
    }
}

const goPrev = () => {
    if (canGoPrev.value) {
        quizStore.prevStep()
    }
}

const handleKeydown = (event) => {
    if (event.key === 'Enter' && canGoNext.value) {
        event.preventDefault()
        goNext()
    } else if (event.key === 'ArrowLeft' && canGoPrev.value) {
        event.preventDefault()
        goPrev()
    } else if (event.key === 'ArrowRight' && canGoNext.value) {
        event.preventDefault()
        goNext()
    }
}

onMounted(() => {
    document.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeydown)
})

/* ---- анімація для Next ---- */
const nextBtnProcess = ref(false)
let processTimer = null

watch(canProceedToNext, async (now, prev) => {
    if (!prev && now) {
        nextBtnProcess.value = false
        await nextTick()
        nextBtnProcess.value = true

        clearTimeout(processTimer)
        processTimer = setTimeout(() => {
            nextBtnProcess.value = false
        }, 1200) // має збігатися з тривалістю @keyframes
    }
})

// при зміні кроку — скидаємо «пульс»
watch(currentStep, () => {
    // nextBtnProcess.value = false
})

</script>

<template>

    <footer class="quiz__footer">
        <div class="quiz__footer-content">
            <div class="quiz__footer-progress">
                <div class="quiz__footer-step">Крок {{ currentStep + 1 }} з {{ totalSteps }}</div>
                <div class="quiz__footer-dots">

                    <span
                        v-for="(step, index) in totalSteps"
                        :key="index"
                        class="quiz__footer-dot"
                        :class="{
              'quiz__footer-dot--active': index === currentStep,
              'quiz__footer-dot--completed': index < currentStep
            }"
                    ></span>
                </div>
            </div>

            <!-- Бонуси + знижка -->
            <div class="quiz__footer-bonus">
                <div class="quiz__footer-discount" v-if="hasDiscount">
                    {{ discountTitle }}: {{ discountValue }}%
                </div>

                <div v-if="showBonuses" class="quiz__footer-bonus-list">
                    <div
                        v-for="(b, i) in bonuses"
                        :key="i"
                        class="quiz__footer-bonus-item"
                        :class="{ 'is-locked': !isCompleted }"
                    >
                        <img
                            v-if="b?.image"
                            :src="b?.image"
                            :alt="b.name"
                            loading="lazy"
                        />
                        <div class="quiz__footer-bonus-lock" v-if="!isCompleted">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path
                                    d="M12 1C8.676 1 6 3.676 6 7v3H5c-1.103 0-2 .897-2 2v9c0 1.103.897 2 2 2h14c1.103 0 2-.897 2-2v-9c0-1.103-.897-2-2-2h-1V7c0-3.324-2.676-6-6-6zm0 2c2.276 0 4 1.724 4 4v3H8V7c0-2.276 1.724-4 4-4z"
                                />
                            </svg>
                        </div>
                        <span class="quiz__footer-bonus-text">{{ b.name }}</span>
                    </div>
                </div>
            </div>

            <div class="quiz__footer-navigation">
                <div class="quiz__footer-buttons">
                    <button class="quiz__footer-btn quiz__footer-btn--prev" :disabled="!canGoPrev"
                            @click="goPrev"
                    >
                        <span>←</span>
                    </button>
                    <button class="quiz__footer-btn quiz__footer-btn--next"
                            :class="{ process: nextBtnProcess }"
                            :disabled="!canProceedToNext"
                        @click="goNext"
                    >
                        <span>{{ isCompleted && ! nextBtnProcess ? 'Завершити' : 'Далі →' }}</span>
                    </button>
                </div>
                <div class="quiz__footer-hint">або натисніть Enter</div>
            </div>
        </div>

        <FooterProgress />
    </footer>
</template>
