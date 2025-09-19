<script setup>
import { computed } from 'vue'
import { useQuizStore } from '../../stores/quiz.js'
import TransitionWrapper from '../../Animations/TransitionWrapper.vue'

const emit = defineEmits(['answer'])

const props = defineProps({
    step: {
        type: Object,
        required: true
    },
    isActive: {
        type: Boolean,
        default: false
    },
})

const quizStore = useQuizStore()

const isAnswerSelected = (answerId) => {
    return quizStore.getAnswerForStep(props.step.id) === answerId
}

const selectAnswer = (answerId) => {
    quizStore.setAnswer(props.step.id, answerId)
}

</script>

<template>
    <TransitionWrapper transition-name="slide" mode="out-in">
        <div class="quiz__question" :key="step.id" v-if="isActive && step">
            <h1 class="quiz__title">{{ step.title }}</h1>
            <!-- Варіанти відповідей -->
            <div class="quiz__wrapper">
                <div class="quiz__item"
                     v-for="answer in step.answers"
                     :key="answer.id"
                     :class="['quiz__item', step?.type && `quiz__item--${step.type}`, isAnswerSelected(answer.id) ? 'quiz__item--selected' : '',
                         answer.image ? 'quiz__item--with-image' :''
                     ]"
                >
                    <label :for="`answer-${answer.id}`">
                        <input
                            :id="`answer-${answer.id}`"
                            :type="step.type"
                            :name="`step-${step.id}`"
                            :value="answer.id"
                            :checked="isAnswerSelected(answer.id)"
                            @change="selectAnswer(answer.id)"
                        />
                        <span class="check"></span>
                        <span class="control-label">
                            <div class="quiz__item-wrapper">
                                <div class="quiz__item-image"
                                     v-if="answer.image"
                                >
                                    <img
                                        :src="answer.image"
                                        alt="answer.label"
                                    />
                                </div>
                                <div class="quiz__item-text" v-if="answer.label">
                                    <h2 class="quiz__item-title">{{ answer.label }}</h2>
                                </div>
                            </div>
                        </span>
                    </label>
                </div>
            </div>
        </div>
    </TransitionWrapper>
</template>
