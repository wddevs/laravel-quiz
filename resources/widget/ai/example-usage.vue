<template>
  <!-- Приклад використання компонентів квізу -->
  <div class="example-container">
    <h1>Приклад використання компонентів квізу</h1>
    
    <!-- Приклад кроку квізу -->
    <QuizStep
      :step="exampleStep"
      :step-index="0"
      :is-active="true"
      :answer="selectedAnswer"
      @answer="handleAnswer"
    />
    
    <!-- Приклад консультанта -->
    <QuizConsultant
      :consultant="consultant"
      :message="consultantMessage"
    />
    
    <!-- Приклад форми -->
    <ContactForm
      @submit="handleFormSubmit"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import QuizStep from './QuizStep.vue'
import QuizConsultant from './QuizConsultant.vue'
import ContactForm from './ContactForm.vue'

// Приклад даних
const exampleStep = {
  id: 1,
  title: 'Яка довіреність Вам потрібна?',
  type: 'radio',
  required: false,
  answers: [
    {
      id: 1,
      label: 'ЗГОДА НА АВТО',
      value: 'auto',
      image: '/assets/images/quiz-answer/img-4.jpg'
    },
    {
      id: 2,
      label: 'ДОВІРЕНІСТЬ НА НЕРУХОМІСТЬ',
      value: 'real-estate',
      image: '/assets/images/quiz-answer/img-8.jpg'
    },
    {
      id: 3,
      label: 'Інше',
      value: 'other',
      image: '/assets/images/quiz-answer/img-3.jpg'
    }
  ]
}

const consultant = {
  name: 'Аліна',
  role: 'Помічник нотаріуса',
  avatar: '/assets/images/manager.webp'
}

const consultantMessage = 'Доброго дня! Тут вкажіть довіреність яка Вам необхідна'

const selectedAnswer = ref(null)

const handleAnswer = (stepId, answer) => {
  selectedAnswer.value = answer
  console.log('Answer selected:', { stepId, answer })
}

const handleFormSubmit = (formData) => {
  console.log('Form submitted:', formData)
}
</script>

<style scoped>
.example-container {
  max-width: 800px;
  margin: 0 auto;
  padding: 2rem;
}

h1 {
  margin-bottom: 2rem;
  color: #1f2937;
}
</style>

