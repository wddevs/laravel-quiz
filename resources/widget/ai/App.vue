<template>
  <div class="quiz-shell">
    <!-- Завантаження -->
    <div v-if="isLoading" class="quiz-loading">
      <div class="loading-spinner"></div>
      <p>Завантаження квізу...</p>
    </div>

    <!-- Помилка -->
    <div v-else-if="isError" class="quiz-error">
      <h2>Помилка завантаження</h2>
      <p>{{ error }}</p>
      <button @click="retryLoad" class="retry-btn">Спробувати знову</button>
    </div>

    <!-- Стартова сторінка -->
    <StartPage 
      v-else-if="hasStartPage && !showQuiz" 
      :data="quizData" 
      @start-quiz="startQuiz"
    />

    <!-- Квіз -->
    <div v-else-if="showQuiz" class="quiz-container">
      <QuizLayout
        :quiz-data="quizData"
        :current-step="currentStep"
        :answers="answers"
        :total-steps="totalSteps"
        :can-go-prev="canGoPrev"
        :can-go-next="canGoNext"
        :discount="discount"
        :bonuses="bonuses"
        :consultant="consultant"
        :consultant-message="consultantMessage"
        @answer="handleAnswer"
        @prev="handlePrev"
        @next="handleNext"
        @form-submit="handleFormSubmit"
      />

      <QuizFooter
        :current-step="currentStep"
        :total-steps="totalSteps"
        :can-go-prev="canGoPrev"
        :can-go-next="canGoNext"
        :discount="discount"
        :bonuses="bonuses"
        @prev="handlePrev"
        @next="handleNext"
      />
    </div>

    <!-- Пуста сторінка -->
    <div v-else class="quiz-empty">
      <h2>Квіз недоступний</h2>
      <p>Стартова сторінка недоступна або квіз не знайдено.</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useQuizStore } from './stores/quiz'
import { storeToRefs } from 'pinia'
import StartPage from './Components/StartPage.vue'
import QuizLayout from './QuizLayout.vue'
import QuizFooter from './QuizFooter.vue'

const props = defineProps({ 
  uuid: { type: String, default: null } 
})

const quiz = useQuizStore()
const { 
  quizData, 
  hasStartPage, 
  isLoading, 
  isError, 
  error,
  currentStep,
  answers,
  totalSteps,
  canGoPrev,
  canGoNext
} = storeToRefs(quiz)

// Локальний стан
const showQuiz = ref(false)

// Обчислювані властивості
const discount = computed(() => ({
  value: quizData.value?.marketing?.discount?.value || 0,
  type: quizData.value?.marketing?.discount?.type,
  effect: quizData.value?.marketing?.discount?.effect
}))

const bonuses = computed(() => quizData.value?.marketing?.bonuses || [])

const consultant = computed(() => ({
  name: quizData.value?.assistant?.name || 'Аліна',
  role: quizData.value?.assistant?.title || 'Помічник нотаріуса',
  avatar: quizData.value?.assistant?.avatar || '/assets/images/manager.webp'
}))

const consultantMessage = computed(() => 
  quizData.value?.assistant?.message || 'Доброго дня! Тут вкажіть довіреність яка Вам необхідна'
)

// Спостерігачі
watch(() => props.uuid, (id) => {
  if (id) {
    quiz.loadQuiz(id)
    showQuiz.value = false // Скидаємо показ квізу при завантаженні нового
  }
}, { immediate: true })

// Обробники подій
const startQuiz = () => {
  showQuiz.value = true
}

const handleAnswer = (stepId, answer) => {
  quiz.setAnswer(stepId, answer)
}

const handlePrev = () => {
  quiz.prevStep()
}

const handleNext = () => {
  if (currentStep.value >= totalSteps.value - 1) {
    // Останній крок - завершуємо квіз
    handleQuizComplete()
  } else {
    quiz.nextStep()
  }
}

const handleQuizComplete = () => {
  const results = quiz.submitQuiz()
  console.log('Quiz completed:', results)
  // Тут можна додати логіку завершення квізу
  alert('Дякуємо за проходження квізу!')
}

const handleFormSubmit = (formData) => {
  console.log('Form submitted:', formData)
  // Тут буде логіка відправки форми
  alert('Дякуємо! Ваша заявка відправлена.')
}

const retryLoad = () => {
  if (props.uuid) {
    quiz.loadQuiz(props.uuid, { force: true })
  }
}
</script>

<style scoped>
.quiz-shell {
  min-height: 100vh;
  background: #f8fafc;
}

.quiz-loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  gap: 1rem;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #e5e7eb;
  border-top: 4px solid #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.quiz-error {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  gap: 1rem;
  text-align: center;
  padding: 2rem;
}

.quiz-error h2 {
  color: #dc2626;
  font-size: 1.5rem;
  margin-bottom: 0.5rem;
}

.quiz-error p {
  color: #6b7280;
  margin-bottom: 1rem;
}

.retry-btn {
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 8px;
  padding: 0.75rem 1.5rem;
  font-size: 1rem;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.retry-btn:hover {
  background: #2563eb;
}

.quiz-container {
  position: relative;
  min-height: 100vh;
  padding-bottom: 80px; /* Місце для футеру */
}

.quiz-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  gap: 1rem;
  text-align: center;
  padding: 2rem;
}

.quiz-empty h2 {
  color: #374151;
  font-size: 1.5rem;
  margin-bottom: 0.5rem;
}

.quiz-empty p {
  color: #6b7280;
}
</style>

