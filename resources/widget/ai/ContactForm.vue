<template>
  <div class="form">
    <h2 class="form__title">
      Залиште свій номер телефону та отримайте терміни та вартість за
      Вашим запитом у месенджері
    </h2>
    <p class="form__subtitle">
      Помічний нотаріуса перегляне заповнену Вами інформацію, та напише
      відповідь Вам протягом 30 хвилин
    </p>

    <form class="form__body" @submit.prevent="handleSubmit">
      <div class="form__row">
        <div class="form__group">
          <label class="form__label" for="form-name">Ім'я</label>
          <div class="form__input-wrapper">
            <span class="form__icon" aria-hidden="true">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                <path
                  d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"
                  fill="currentColor"
                />
              </svg>
            </span>
            <input
              class="form__input"
              type="text"
              id="form-name"
              v-model="formData.name"
              placeholder="Ваше ім'я"
              required
            />
          </div>
        </div>

        <div class="form__group">
          <label class="form__label" for="form-phone">Телефон</label>
          <div class="form__input-wrapper">
            <span class="form__icon" aria-hidden="true">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                <path
                  d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"
                  fill="currentColor"
                />
              </svg>
            </span>
            <input
              class="form__input"
              type="tel"
              id="form-phone"
              v-model="formData.phone"
              placeholder="Ваш номер телефону"
              required
            />
          </div>
        </div>
      </div>

      <div class="form__actions">
        <button 
          type="submit" 
          class="button form__submit"
          :disabled="isSubmitting"
        >
          <span v-if="!isSubmitting">Відправити</span>
          <span v-else>Відправляємо...</span>
        </button>
      </div>

      <label class="form__privacy">
        <input 
          type="checkbox" 
          v-model="formData.privacy"
          required
        />
        <span>З політикою конфіденційності ознайомлений(а)</span>
      </label>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'

const emit = defineEmits(['submit'])

const isSubmitting = ref(false)

const formData = reactive({
  name: '',
  phone: '',
  privacy: false
})

const handleSubmit = async () => {
  if (!formData.privacy) {
    alert('Будь ласка, підтвердіть ознайомлення з політикою конфіденційності')
    return
  }

  isSubmitting.value = true

  try {
    // Симуляція відправки форми
    await new Promise(resolve => setTimeout(resolve, 1000))
    
    emit('submit', { ...formData })
    
    // Скидаємо форму після успішної відправки
    formData.name = ''
    formData.phone = ''
    formData.privacy = false
    
    alert('Дякуємо! Ваша заявка відправлена. Ми зв\'яжемося з вами найближчим часом.')
  } catch (error) {
    console.error('Помилка відправки форми:', error)
    alert('Виникла помилка при відправці форми. Спробуйте ще раз.')
  } finally {
    isSubmitting.value = false
  }
}
</script>

<style scoped>
.form {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  margin-bottom: 2rem;
}

.form__title {
  font-size: 1.5rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 1rem;
  line-height: 1.4;
}

.form__subtitle {
  font-size: 1rem;
  color: #6b7280;
  margin-bottom: 2rem;
  line-height: 1.5;
}

.form__body {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.form__row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.form__group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form__label {
  font-size: 0.9rem;
  font-weight: 500;
  color: #374151;
}

.form__input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.form__icon {
  position: absolute;
  left: 1rem;
  color: #9ca3af;
  z-index: 1;
}

.form__input {
  width: 100%;
  padding: 0.75rem 1rem 0.75rem 2.5rem;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 1rem;
  transition: border-color 0.2s ease;
  background: white;
}

.form__input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form__input::placeholder {
  color: #9ca3af;
}

.form__actions {
  display: flex;
  justify-content: center;
}

.form__submit {
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 8px;
  padding: 1rem 2rem;
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s ease;
  min-width: 200px;
}

.form__submit:hover:not(:disabled) {
  background: #2563eb;
}

.form__submit:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.form__privacy {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.9rem;
  color: #6b7280;
  cursor: pointer;
}

.form__privacy input[type="checkbox"] {
  width: 16px;
  height: 16px;
  accent-color: #3b82f6;
}

@media (max-width: 768px) {
  .form {
    padding: 1.5rem;
  }
  
  .form__row {
    grid-template-columns: 1fr;
  }
  
  .form__title {
    font-size: 1.25rem;
  }
  
  .form__submit {
    width: 100%;
  }
}
</style>

