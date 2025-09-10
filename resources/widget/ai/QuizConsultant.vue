<template>
  <div class="quiz__consultant">
    <div class="quiz__consultant-profile">
      <div class="quiz__consultant-avatar">
        <img 
          :src="consultant.avatar" 
          :alt="consultant.name"
          @error="handleImageError"
        />
        <div class="quiz__consultant-status"></div>
      </div>
      <div class="quiz__consultant-info">
        <div class="quiz__consultant-name">{{ consultant.name }}</div>
        <div class="quiz__consultant-role">{{ consultant.role }}</div>
        <div class="quiz__consultant-message">
          {{ message }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  consultant: {
    type: Object,
    required: true,
    default: () => ({
      name: 'Аліна',
      role: 'Помічник нотаріуса',
      avatar: '/assets/images/manager.webp'
    })
  },
  message: {
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
.quiz__consultant {
  margin-bottom: 2rem;
  padding: 1.5rem;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  border-left: 4px solid #3b82f6;
}

.quiz__consultant-profile {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
}

.quiz__consultant-avatar {
  position: relative;
  flex-shrink: 0;
}

.quiz__consultant-avatar img {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #e5e7eb;
}

.quiz__consultant-status {
  position: absolute;
  bottom: 2px;
  right: 2px;
  width: 12px;
  height: 12px;
  background: #10b981;
  border: 2px solid white;
  border-radius: 50%;
}

.quiz__consultant-info {
  flex: 1;
}

.quiz__consultant-name {
  font-size: 1rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 0.25rem;
}

.quiz__consultant-role {
  font-size: 0.875rem;
  color: #6b7280;
  margin-bottom: 0.5rem;
}

.quiz__consultant-message {
  font-size: 0.9rem;
  color: #374151;
  line-height: 1.5;
  background: #f9fafb;
  padding: 0.75rem;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
}

@media (max-width: 768px) {
  .quiz__consultant {
    padding: 1rem;
  }
  
  .quiz__consultant-profile {
    gap: 0.75rem;
  }
  
  .quiz__consultant-avatar img {
    width: 40px;
    height: 40px;
  }
  
  .quiz__consultant-status {
    width: 10px;
    height: 10px;
  }
}
</style>

