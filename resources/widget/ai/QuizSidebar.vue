<template>
  <div class="quiz__sidebar">
    <!-- Блок знижки -->
    <div class="sidebar__discount">
      <div class="sidebar__discount-text">
        <span>Твоя знижка:</span>
        <div class="sidebar__discount-value">
          <span>{{ discount.value }}%</span>
          <svg
            class="arrow arrow_animation"
            width="8"
            height="8"
            viewBox="0 0 10 10"
            fill="none"
          >
            <path
              d="M5 0L0.757355 4.24265L2.17157 5.65686L5 2.82843L7.82843 5.65686L9.24265 4.24265L5 0Z"
              fill="#44bc75"
            />
            <path
              d="M5 4L0.757355 8.24265L2.17157 9.65686L5 6.82843L7.82843 9.65686L9.24265 8.24265L5 4Z"
              fill="#44bc75"
            />
          </svg>
        </div>
      </div>
    </div>

    <!-- Блок бонусів -->
    <div v-if="bonuses.length" class="sidebar__bonus">
      <div
        v-for="(bonus, index) in bonuses"
        :key="index"
        class="sidebar__bonus-item"
      >
        <div class="sidebar__bonus-image">
          <img 
            :src="bonus.image" 
            :alt="bonus.title"
            @error="handleImageError"
          />
        </div>
        <div class="sidebar__bonus-text">{{ bonus.title }}</div>
        <div class="sidebar__bonus-lock">
          <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path
              d="M12 1C8.676 1 6 3.676 6 7v3H5c-1.103 0-2 .897-2 2v9c0 1.103.897 2 2 2h14c1.103 0 2-.897 2-2v-9c0-1.103-.897-2-2-2h-1V7c0-3.324-2.676-6-6-6zm0 2c2.276 0 4 1.724 4 4v3H8V7c0-2.276 1.724-4 4-4z"
            />
          </svg>
        </div>
      </div>
    </div>

    <!-- Блок чату з консультантом -->
    <div class="sidebar__chat">
      <div class="sidebar__chat-profile">
        <div class="sidebar__chat-avatar">
          <img 
            :src="consultant.avatar" 
            :alt="consultant.name"
            @error="handleImageError"
          />
          <div class="sidebar__chat-status"></div>
        </div>
        <div class="sidebar__chat-info">
          <div class="sidebar__chat-name">{{ consultant.name }}</div>
          <div class="sidebar__chat-role">{{ consultant.role }}</div>
        </div>
      </div>
      <div class="sidebar__chat-message">
        {{ consultantMessage }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  discount: {
    type: Object,
    default: () => ({ value: 0, type: null, effect: null })
  },
  bonuses: {
    type: Array,
    default: () => []
  },
  consultant: {
    type: Object,
    required: true,
    default: () => ({
      name: 'Аліна',
      role: 'Помічник нотаріуса',
      avatar: '/assets/images/manager.webp'
    })
  },
  consultantMessage: {
    type: String,
    default: 'Доброго дня! Тут вкажіть довіреність яка Вам необхідна'
  }
})

const defaultAvatar = '/assets/images/manager.webp'

const handleImageError = (event) => {
  event.target.src = defaultAvatar
}
</script>

<style scoped>
.quiz__sidebar {
  width: 400px;
  background: #1f2937;
  color: white;
  padding: 2rem;
  display: flex;
  flex-direction: column;
  gap: 2rem;
  position: sticky;
  top: 0;
  height: 100vh;
  overflow-y: auto;
}

.sidebar__discount {
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
  border-radius: 12px;
  padding: 1.5rem;
  text-align: center;
}

.sidebar__discount-text {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
}

.sidebar__discount-text span:first-child {
  font-size: 0.9rem;
  opacity: 0.9;
}

.sidebar__discount-value {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 2rem;
  font-weight: 700;
}

.arrow_animation {
  animation: bounce 2s infinite;
}

@keyframes bounce {
  0%, 20%, 50%, 80%, 100% {
    transform: translateY(0);
  }
  40% {
    transform: translateY(-5px);
  }
  60% {
    transform: translateY(-3px);
  }
}

.sidebar__bonus {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.sidebar__bonus-item {
  position: relative;
  background: #374151;
  border-radius: 8px;
  padding: 1rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  transition: transform 0.2s ease;
}

.sidebar__bonus-item:hover {
  transform: translateY(-2px);
}

.sidebar__bonus-image {
  flex-shrink: 0;
}

.sidebar__bonus-image img {
  width: 50px;
  height: 50px;
  border-radius: 8px;
  object-fit: cover;
}

.sidebar__bonus-text {
  flex: 1;
  font-size: 0.9rem;
  font-weight: 500;
  line-height: 1.4;
}

.sidebar__bonus-lock {
  flex-shrink: 0;
  width: 24px;
  height: 24px;
  color: #fbbf24;
}

.sidebar__bonus-lock svg {
  width: 100%;
  height: 100%;
  fill: currentColor;
}

.sidebar__chat {
  background: #374151;
  border-radius: 12px;
  padding: 1.5rem;
  margin-top: auto;
}

.sidebar__chat-profile {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1rem;
}

.sidebar__chat-avatar {
  position: relative;
  flex-shrink: 0;
}

.sidebar__chat-avatar img {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #4b5563;
}

.sidebar__chat-status {
  position: absolute;
  bottom: 2px;
  right: 2px;
  width: 10px;
  height: 10px;
  background: #10b981;
  border: 2px solid #374151;
  border-radius: 50%;
}

.sidebar__chat-info {
  flex: 1;
}

.sidebar__chat-name {
  font-size: 0.9rem;
  font-weight: 600;
  margin-bottom: 0.25rem;
}

.sidebar__chat-role {
  font-size: 0.8rem;
  color: #9ca3af;
}

.sidebar__chat-message {
  font-size: 0.9rem;
  line-height: 1.5;
  color: #d1d5db;
  background: #4b5563;
  padding: 1rem;
  border-radius: 8px;
  border: 1px solid #6b7280;
}

@media (max-width: 768px) {
  .quiz__sidebar {
    width: 100%;
    height: auto;
    position: static;
    padding: 1rem;
  }
  
  .sidebar__bonus {
    flex-direction: row;
    overflow-x: auto;
    gap: 0.75rem;
  }
  
  .sidebar__bonus-item {
    min-width: 200px;
  }
}
</style>

