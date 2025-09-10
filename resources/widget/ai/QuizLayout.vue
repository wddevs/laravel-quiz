<template>
  <div class="quiz__layout">
    <!-- Основний контент квізу -->
    <div class="quiz__main">
      <!-- Блок консультанта (мобільний) -->
      <QuizConsultant 
        v-if="showConsultant"
        :consultant="consultant"
        :message="consultantMessage"
      />

      <!-- Кроки квізу -->
      <QuizStep
        v-for="(step, index) in quizSteps"
        :key="step.id"
        :step="step"
        :step-index="index"
        :is-active="index === currentStep"
        :answer="answers[step.id]"
        @answer="handleAnswer"
      />

      <!-- Форма зворотного зв'язку -->
      <ContactForm
        v-if="showContactForm"
        @submit="handleFormSubmit"
      />
    </div>

    <!-- Сайдбар -->
    <QuizSidebar
      :discount="discount"
      :bonuses="bonuses"
      :consultant="consultant"
      :consultant-message="consultantMessage"
    />
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useQuizStore } from '../stores/quiz'
import { storeToRefs } from 'pinia'
import QuizConsultant from './QuizConsultant.vue'
import QuizStep from './QuizStep.vue'
import ContactForm from './ContactForm.vue'
import QuizSidebar from './QuizSidebar.vue'

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
const showContactForm = computed(() => currentStep.value >= totalSteps.value - 1)

const consultant = computed(() => ({
  name: quizData.value?.assistant?.name || 'Аліна',
  role: quizData.value?.assistant?.title || 'Помічник нотаріуса',
  avatar: quizData.value?.assistant?.avatar || '/assets/images/manager.webp'
}))

const consultantMessage = computed(() => 
  quizData.value?.assistant?.message || 'Доброго дня! Тут вкажіть довіреність яка Вам необхідна'
)

const discount = computed(() => ({
  value: quizData.value?.marketing?.discount?.value || 0,
  type: quizData.value?.marketing?.discount?.type,
  effect: quizData.value?.marketing?.discount?.effect
}))

const bonuses = computed(() => quizData.value?.marketing?.bonuses || [])

// Обробники подій
const handleAnswer = (stepId, answer) => {
  quiz.setAnswer(stepId, answer)
}

const handleFormSubmit = (formData) => {
  console.log('Form submitted:', formData)
  // Тут буде логіка відправки форми
}
</script>

<style scoped>
.quiz__layout {
  display: flex;
  min-height: 100vh;
  background: #f8fafc;
}

.quiz__main {
  flex: 1;
  padding: 2rem;
  max-width: calc(100% - 400px);
}

@media (max-width: 768px) {
  .quiz__layout {
    flex-direction: column;
  }
  
  .quiz__main {
    max-width: 100%;
    padding: 1rem;
  }
}
</style>

