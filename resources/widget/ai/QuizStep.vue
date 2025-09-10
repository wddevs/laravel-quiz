<template>
  <div 
    v-if="isActive"
    class="quiz__question"
    :class="{
      'quiz__question--emoji': stepType === 'emoji',
      'quiz__question--text': stepType === 'text'
    }"
  >
    <h1 class="quiz__title">{{ step.title }}</h1>
    
    <div 
      class="quiz__wrapper"
      :class="{
        'quiz__wrapper--emoji': stepType === 'emoji',
        'quiz__wrapper--text': stepType === 'text'
      }"
    >
      <div
        v-for="(answer, index) in step.answers"
        :key="answer.id"
        class="quiz__item"
        :class="{
          'quiz__item--emoji': stepType === 'emoji',
          'quiz__item--text': stepType === 'text'
        }"
      >
        <label :for="`q${step.id}-a${answer.id}`">
          <input
            :id="`q${step.id}-a${answer.id}`"
            :name="`q${step.id}`"
            type="radio"
            :value="answer.value"
            :checked="selectedAnswer === answer.value"
            @change="handleAnswerChange(answer)"
          />
          <span 
            class="check"
            :class="{
              'quiz__item-check--text': stepType === 'text'
            }"
          ></span>
          <span class="control-label">
            <div 
              class="quiz__item-wrapper"
              :class="{
                'quiz__item-wrapper--emoji': stepType === 'emoji'
              }"
            >
              <!-- Зображення або емоджі -->
              <div class="quiz__item-image">
                <img
                  v-if="answer.image && stepType !== 'emoji'"
                  :src="answer.image"
                  :alt="answer.label"
                />
                <span 
                  v-else-if="stepType === 'emoji'"
                  class="quiz__item-emoji"
                >
                  {{ getEmoji(answer.label) }}
                </span>
              </div>
              
              <!-- Текст відповіді -->
              <div class="quiz__item-text">
                <h2 
                  class="quiz__item-title"
                  :class="{
                    'quiz__item-title--emoji': stepType === 'emoji',
                    'quiz__item-title--text': stepType === 'text'
                  }"
                >
                  {{ answer.label }}
                </h2>
              </div>
            </div>
          </span>
        </label>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  step: {
    type: Object,
    required: true
  },
  stepIndex: {
    type: Number,
    required: true
  },
  isActive: {
    type: Boolean,
    default: false
  },
  answer: {
    type: [String, Number],
    default: null
  }
})

const emit = defineEmits(['answer'])

// Визначаємо тип кроку на основі відповідей
const stepType = computed(() => {
  if (!props.step?.answers?.length) return 'default'
  
  const firstAnswer = props.step.answers[0]
  
  // Перевіряємо чи є зображення
  if (firstAnswer.image) return 'image'
  
  // Перевіряємо чи потрібно показати емоджі
  if (shouldShowEmoji(firstAnswer.label)) return 'emoji'
  
  // Перевіряємо чи це текстовий тип (без зображень)
  return 'text'
})

const selectedAnswer = computed(() => props.answer)

// Функція для визначення чи потрібно показати емоджі
const shouldShowEmoji = (label) => {
  const emojiPatterns = [
    /^\d+\s*(рік|роки|років)$/i,
    /^(сьогодні|завтра|тиждень|терміново)$/i,
    /^(так|ні|маю|не маю)$/i
  ]
  
  return emojiPatterns.some(pattern => pattern.test(label))
}

// Функція для отримання емоджі за лейблом
const getEmoji = (label) => {
  const emojiMap = {
    // Терміни
    '1 рік': '1️⃣',
    '2 роки': '2️⃣', 
    '3 роки': '3️⃣',
    '4 роки': '4️⃣',
    '5 років': '5️⃣',
    
    // Терміновість
    'Сьогодні': '😛',
    'Завтра': '👍',
    'Цього тижня': '😜',
    'Не терміново': '🤩',
    
    // Так/Ні
    'Так, маю': '👍',
    'Ні, не маю': '😱',
    
    // Інше
    'Інше': '👉🏻',
    'Інше...': '👉🏻'
  }
  
  return emojiMap[label] || '❓'
}

// Обробник зміни відповіді
const handleAnswerChange = (answer) => {
  emit('answer', props.step.id, answer.value)
}
</script>

<style scoped>
.quiz__question {
  margin-bottom: 2rem;
  padding: 2rem;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.quiz__title {
  font-size: 1.5rem;
  font-weight: 600;
  margin-bottom: 1.5rem;
  color: #1f2937;
}

.quiz__wrapper {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.quiz__wrapper--emoji {
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
}

.quiz__wrapper--text {
  grid-template-columns: 1fr;
  gap: 0.5rem;
}

.quiz__item {
  position: relative;
}

.quiz__item input[type="radio"] {
  position: absolute;
  opacity: 0;
  pointer-events: none;
}

.quiz__item label {
  display: block;
  cursor: pointer;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  padding: 1rem;
  transition: all 0.2s ease;
  background: white;
}

.quiz__item label:hover {
  border-color: #3b82f6;
  box-shadow: 0 2px 8px rgba(59, 130, 246, 0.1);
}

.quiz__item input[type="radio"]:checked + .check + .control-label {
  border-color: #3b82f6;
  background: #eff6ff;
}

.quiz__item-wrapper {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.quiz__item-wrapper--emoji {
  flex-direction: column;
  text-align: center;
  gap: 0.5rem;
}

.quiz__item-image {
  flex-shrink: 0;
}

.quiz__item-image img {
  width: 60px;
  height: 60px;
  object-fit: cover;
  border-radius: 8px;
}

.quiz__item-emoji {
  font-size: 2rem;
  display: block;
}

.quiz__item-title {
  font-size: 0.9rem;
  font-weight: 500;
  color: #374151;
  margin: 0;
}

.quiz__item-title--emoji {
  font-size: 0.8rem;
}

.quiz__item-title--text {
  font-size: 1rem;
}

.check {
  position: absolute;
  top: 50%;
  right: 1rem;
  transform: translateY(-50%);
  width: 20px;
  height: 20px;
  border: 2px solid #d1d5db;
  border-radius: 50%;
  background: white;
  transition: all 0.2s ease;
}

.quiz__item input[type="radio"]:checked + .check {
  border-color: #3b82f6;
  background: #3b82f6;
}

.quiz__item input[type="radio"]:checked + .check::after {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 8px;
  height: 8px;
  background: white;
  border-radius: 50%;
}

.quiz__item-check--text {
  border-radius: 4px;
}

.quiz__item input[type="radio"]:checked + .quiz__item-check--text::after {
  border-radius: 2px;
}

@media (max-width: 768px) {
  .quiz__question {
    padding: 1rem;
  }
  
  .quiz__wrapper {
    grid-template-columns: 1fr;
  }
  
  .quiz__wrapper--emoji {
    grid-template-columns: repeat(2, 1fr);
  }
}
</style>

