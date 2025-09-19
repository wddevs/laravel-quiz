<script setup>
import { computed } from 'vue'
import { useQuizStore } from '../../stores/quiz.js'
import { storeToRefs } from 'pinia'

const props = defineProps({
    /** answered — частка відповідей; step — залежно від активного кроку */
    mode: { type: String, default: 'answered' },
    /** можна сховати смугу, якщо потрібно */
    show: { type: Boolean, default: true },
})

const quiz = useQuizStore()
const { totalSteps, answers, currentStep, isLoading } = storeToRefs(quiz)

// скільки відповідей дано
const answeredCount = computed(() => Object.keys(answers.value || {}).length)

// % прогресу
const progress = computed(() => {
    const total = totalSteps.value || 0
    if (!total) return 0

    if (props.mode === 'step') {
        // прогрес від активного кроку (0..total)
        // якщо хочеш враховувати відповідь на поточному кроці — додай +1
        const done = Math.min(currentStep.value, total) // без +1, щоб не стрибало
        return Math.round((done / total) * 100)
    }

    // answered mode
    return Math.round((answeredCount.value / total) * 100)
})

// стиль ширини
const widthStyle = computed(() => {
    // індикатор завантаження: невелика смуга з "бігущими" смугами
    if (isLoading.value) return { width: '25%' }
    return { width: `${progress.value}%` }
})

// ARIA
const ariaNow = computed(() => (isLoading.value ? 0 : progress.value))
</script>

<template>
    <div
        v-if="show"
        class="quiz-progress"
        role="progressbar"
        :aria-valuemin="0"
        :aria-valuemax="100"
        :aria-valuenow="ariaNow"
    >
        <div
            class="quiz-progress__line"
            :class="{ 'is-loading': isLoading }"
            :style="widthStyle"
        >

        </div>
    </div>
</template>

<style lang="sass" scoped>
    .quiz-progress
        position: absolute
        bottom: 0
        width: calc(100% + .3125rem)
        left: -.1875rem
        padding: 0
        margin: 0
        z-index: 101
        background-image: -webkit-gradient(linear, left top, right top, from(var(--color-alpha3)), to(var(--color-alpha3)))
        //background-image: linear-gradient(90deg, var(--color-darken10), var(--color-lighten10))
        -webkit-box-shadow: inset 0 .125rem .5625rem hsla(0, 0%, 100%, .3), inset 0 -.125rem .375rem rgba(0, 0, 0, .4), .0375rem .1187rem .4375rem 0 var(--color-alpha2)
        box-shadow: inset 0 .125rem .5625rem hsla(0, 0%, 100%, .3), inset 0 -.125rem .375rem rgba(0, 0, 0, .4), .0375rem .1187rem .4375rem 0 var(--color-alpha2)

        overflow: hidden
        -webkit-transition: all .3s ease
        transition: all .3s ease
        &::after
            content: ""
            position: absolute
            top: 0
            left: 0
            bottom: 0
            right: 0
            background-image: linear-gradient(-45deg, hsla(0, 0%, 100%, .08) 25%, transparent 0, transparent 50%, hsla(0, 0%, 100%, .08) 0, hsla(0, 0%, 100%, .08) 75%, transparent 0, transparent)
            z-index: 1
            background-size: 1.25rem 1.25rem
            -webkit-animation: move 1.5s linear infinite
            animation: move 1.5s linear infinite
            border-radius: 1.25rem
            overflow: hidden
        &__line
            display: block
            border-radius: 1.25rem
            position: relative
            overflow: hidden
            -webkit-transition: all .3s ease
            transition: all .3s ease
            height: 6px
            background: var(--quiz-primary)


    @keyframes move
        0%
            background-position: 0 0

        to
            background-position: 1.25rem 1.25rem
</style>
