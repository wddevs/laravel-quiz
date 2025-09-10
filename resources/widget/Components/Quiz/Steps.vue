<script setup>
    import QuizLayout from "../Layouts/QuizLayout.vue";
    import Step from "./Step.vue";
    import { storeToRefs } from 'pinia'
    import { computed } from 'vue'

    import { useQuizStore } from '../../stores/quiz.js'
    import Consultant from "./Consultant.vue";
    const quiz = useQuizStore()

    const {
        quizData,
        currentStep,
        answers,
        totalSteps
    } = storeToRefs(quiz)

    // Обчислювані властивості
    const quizSteps = computed(() => quizData.value?.steps || [])
    const showConsultant = computed(() => quizData.value?.assistant?.enabled)

</script>

<template>
    <QuizLayout>

        <Consultant v-if="showConsultant" />

        <!-- Кроки квізу -->
        <Step
            v-for="(step, index) in quizSteps"
            :key="step.id"
            :step="step"
            :step-index="index"
            :is-active="index === currentStep"
            :answer="answers[step.id]"
        />

    </QuizLayout>
</template>
