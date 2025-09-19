<script setup>
import { useQuizStore } from './stores/quiz'
import { storeToRefs } from 'pinia'
import {watch, defineAsyncComponent, ref, computed} from 'vue'
import QuizLayout from './Components/Layouts/QuizLayout.vue'
import Form from './Components/Quiz/Form.vue'
import Steps from "./Components/Quiz/Steps.vue";
import Thanks from "./Components/Quiz/Thanks.vue";
import TransitionWrapper from './Animations/TransitionWrapper.vue'
import {useTheme} from "./composables/useTheme.js";


const props = defineProps({ uuid: { type: String, default: null } })

const quiz = useQuizStore()
const { quizData, isLoading, isStart, isQuestions, isLeadForm, isThanks, isCompleted, phase, isInactive, isNotFound, isError, error  } = storeToRefs(quiz)


watch(() => props.uuid, (id) => id && quiz.loadQuiz(id), { immediate: true })

// lazy, але без Suspense
const StartPage = defineAsyncComponent({
    loader: () => import('./Components/StartPage.vue'),
    suspensible: false,   // не блокується Suspense
    delay: 150,           // уникаємо "блимання" лоадера
    timeout: 10000,       // опційно
})

useTheme(() => quizData.value?.theme)
</script>

<template>
    <div class="quiz-shell">
        <div v-if="isLoading" class="quiz-loading  p-4">Завантаження...</div>

        <div v-else-if="isInactive" class="quiz-empty p-4">
            <h2>Квіз недоступний</h2>
            <p>Цей квіз ще не активовано. Спробуйте пізніше.</p>
        </div>

        <div v-else-if="isNotFound" class="quiz-empty  p-4">
            <h2>Квіз не знайдено</h2>
            <p>Перевірте посилання.</p>
        </div>

        <div v-else-if="isError" class="quiz-empty  p-4" style="padding: 20px">
            <h2>Квіз або не доступний або не активований</h2>
            <p>{{ error }}</p>
        </div>

        <TransitionWrapper transition-name="fade" mode="out-in">
            <!-- ключ має змінюватися, щоб Vue запустив перерендер і анімацію -->
            <div :key="quiz.phase">
                <StartPage
                    v-if="isStart && quizData"
                    :data="quizData"

                />

                <div v-if="isQuestions" class="quiz-container" >
                    <Steps />                         <!-- показуємо кроки -->
                </div>

                <div v-if="isLeadForm" class="app__completed">
                    <Form />
                </div>

                <div v-if="isThanks" class="app__completed">
                    <Thanks />
                </div>

            </div>
        </TransitionWrapper>

        <!-- Квіз завершено - показуємо результати та форму -->

        <div v-if="isCompleted" class="quiz-container">

        </div>


 <div v-if="false" class="quiz-empty">
 <h2>Квіз недоступний</h2>
<p>Стартова сторінка недоступна або квіз не знайдено.</p>
 </div>
    </div>
</template>
