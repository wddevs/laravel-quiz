<template>
  <footer class="quiz__footer">
    <div class="quiz__footer-content">
      <!-- Прогрес -->
      <div class="quiz__footer-progress">
        <div class="quiz__footer-step">Крок {{ currentStep + 1 }} з {{ totalSteps }}</div>
        <div class="quiz__footer-dots">
          <span
            v-for="(step, index) in totalSteps"
            :key="index"
            class="quiz__footer-dot"
            :class="{
              'quiz__footer-dot--active': index <= currentStep,
              'quiz__footer-dot--completed': index < currentStep
            }"
          ></span>
        </div>
      </div>

      <!-- Бонуси -->
      <div class="quiz__footer-bonus">
        <div class="quiz__footer-discount">Твоя знижка: {{ discount.value }}%</div>
        <div class="quiz__footer-bonus-list">
          <div
            v-for="(bonus, index) in bonuses"
            :key="index"
            class="quiz__footer-bonus-item"
          >
            <img 
              :src="bonus.image" 
              :alt="bonus.title"
              @error="handleImageError"
            />
            <div class="quiz__footer-bonus-lock">
              <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path
                  d="M12 1C8.676 1 6 3.676 6 7v3H5c-1.103 0-2 .897-2 2v9c0 1.103.897 2 2 2h14c1.103 0 2-.897 2-2v-9c0-1.103-.897-2-2-2h-1V7c0-3.324-2.676-6-6-6zm0 2c2.276 0 4 1.724 4 4v3H8V7c0-2.276 1.724-4 4-4z"
                />
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Навігація -->
      <div class="quiz__footer-navigation">
        <div class="quiz__footer-buttons">
          <button 
            class="quiz__footer-btn quiz__footer-btn--prev"
            :disabled="!canGoPrev"
            @click="handlePrev"
          >
            <span>←</span>
          </button>
          <button 
            class="quiz__footer-btn quiz__footer-btn--next"
            :disabled="!canGoNext"
            @click="handleNext"
          >
            <span>{{ isLastStep ? 'Завершити' : 'Далі →' }}</span>
          </button>
        </div>
        <div class="quiz__footer-hint">або натисніть Enter</div>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  currentStep: {
    type: Number,
    required: true
  },
  totalSteps: {
    type: Number,
    required: true
  },
  canGoPrev: {
    type: Boolean,
    default: false
  },
  canGoNext: {
    type: Boolean,
    default: false
  },
  discount: {
    type: Object,
    default: () => ({ value: 0 })
  },
  bonuses: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['prev', 'next'])

const isLastStep = computed(() => props.currentStep >= props.totalSteps - 1)

const handlePrev = () => {
  if (props.canGoPrev) {
    emit('prev')
  }
}

const handleNext = () => {
  if (props.canGoNext) {
    emit('next')
  }
}

const handleImageError = (event) => {
  event.target.style.display = 'none'
}

// Обробка клавіші Enter
const handleKeydown = (event) => {
  if (event.key === 'Enter' && props.canGoNext) {
    handleNext()
  }
}

// Додаємо слухач клавіш при монтуванні
import { onMounted, onUnmounted } from 'vue'

onMounted(() => {
  document.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown)
})
</script>

<style scoped>
.quiz__footer {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  background: white;
  border-top: 1px solid #e5e7eb;
  box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.1);
  z-index: 100;
}

.quiz__footer-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 2rem;
  max-width: 1200px;
  margin: 0 auto;
  gap: 2rem;
}

.quiz__footer-progress {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  min-width: 120px;
}

.quiz__footer-step {
  font-size: 0.9rem;
  font-weight: 500;
  color: #374151;
}

.quiz__footer-dots {
  display: flex;
  gap: 0.5rem;
}

.quiz__footer-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #d1d5db;
  transition: background-color 0.2s ease;
}

.quiz__footer-dot--active {
  background: #3b82f6;
}

.quiz__footer-dot--completed {
  background: #10b981;
}

.quiz__footer-bonus {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex: 1;
}

.quiz__footer-discount {
  font-size: 0.9rem;
  font-weight: 600;
  color: #059669;
  white-space: nowrap;
}

.quiz__footer-bonus-list {
  display: flex;
  gap: 0.5rem;
}

.quiz__footer-bonus-item {
  position: relative;
  width: 40px;
  height: 40px;
  border-radius: 8px;
  overflow: hidden;
}

.quiz__footer-bonus-item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.quiz__footer-bonus-lock {
  position: absolute;
  top: 2px;
  right: 2px;
  width: 16px;
  height: 16px;
  background: rgba(0, 0, 0, 0.7);
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.quiz__footer-bonus-lock svg {
  width: 10px;
  height: 10px;
  fill: #fbbf24;
}

.quiz__footer-navigation {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 0.5rem;
  min-width: 200px;
}

.quiz__footer-buttons {
  display: flex;
  gap: 0.75rem;
}

.quiz__footer-btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 8px;
  font-size: 0.9rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.quiz__footer-btn--prev {
  background: #f3f4f6;
  color: #6b7280;
  border: 1px solid #d1d5db;
}

.quiz__footer-btn--prev:hover:not(:disabled) {
  background: #e5e7eb;
  color: #374151;
}

.quiz__footer-btn--next {
  background: #3b82f6;
  color: white;
}

.quiz__footer-btn--next:hover:not(:disabled) {
  background: #2563eb;
}

.quiz__footer-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.quiz__footer-hint {
  font-size: 0.8rem;
  color: #9ca3af;
}

@media (max-width: 768px) {
  .quiz__footer-content {
    flex-direction: column;
    gap: 1rem;
    padding: 1rem;
  }
  
  .quiz__footer-progress {
    order: 1;
    align-items: center;
  }
  
  .quiz__footer-bonus {
    order: 2;
    justify-content: center;
  }
  
  .quiz__footer-navigation {
    order: 3;
    align-items: center;
    min-width: auto;
  }
  
  .quiz__footer-buttons {
    width: 100%;
    justify-content: center;
  }
  
  .quiz__footer-btn {
    flex: 1;
    justify-content: center;
  }
}
</style>

