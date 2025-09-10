# Опис компонентів квізу

## 📋 Огляд

Всі компоненти розбиті з HTML розмітки та готові для інтеграції в Vue.js проект.

## 🧩 Компоненти

### 1. QuizLayout.vue
**Основний лейаут квізу**
- Об'єднує всі компоненти квізу
- Управляє станом та передає дані до дочірніх компонентів
- Підтримує різні типи кроків (зображення, емоджі, текст)

**Props:**
- `quizData` - дані квізу з API
- `currentStep` - поточний крок
- `answers` - відповіді користувача
- `totalSteps` - загальна кількість кроків

**Events:**
- `@answer` - вибір відповіді
- `@prev` - перехід до попереднього кроку
- `@next` - перехід до наступного кроку
- `@form-submit` - відправка форми

### 2. QuizStep.vue
**Компонент кроку квізу**
- Відображає питання та варіанти відповідей
- Автоматично визначає тип кроку (зображення, емоджі, текст)
- Підтримує різні типи питань

**Props:**
- `step` - дані кроку
- `stepIndex` - індекс кроку
- `isActive` - чи активний крок
- `answer` - вибрана відповідь

**Events:**
- `@answer` - вибір відповіді

**Особливості:**
- Автоматичне визначення емоджі за лейблом
- Підтримка зображень
- Адаптивна сітка

### 3. QuizConsultant.vue
**Блок консультанта**
- Відображає інформацію про консультанта
- Показує повідомлення консультанта
- Підтримує аватар та статус онлайн

**Props:**
- `consultant` - дані консультанта
- `message` - повідомлення консультанта

### 4. QuizSidebar.vue
**Сайдбар з бонусами**
- Відображає знижку користувача
- Показує бонуси
- Блок чату з консультантом

**Props:**
- `discount` - дані знижки
- `bonuses` - список бонусів
- `consultant` - дані консультанта
- `consultantMessage` - повідомлення консультанта

### 5. QuizFooter.vue
**Футер з навігацією**
- Прогрес-бар з точками
- Кнопки навігації
- Відображення бонусів
- Підтримка клавіші Enter

**Props:**
- `currentStep` - поточний крок
- `totalSteps` - загальна кількість кроків
- `canGoPrev` - чи можна йти назад
- `canGoNext` - чи можна йти вперед
- `discount` - дані знижки
- `bonuses` - список бонусів

**Events:**
- `@prev` - перехід назад
- `@next` - перехід вперед

### 6. ContactForm.vue
**Форма зворотного зв'язку**
- Поля для імені та телефону
- Валідація форми
- Підтвердження політики конфіденційності
- Стан завантаження

**Events:**
- `@submit` - відправка форми

**Особливості:**
- Валідація обов'язкових полів
- Іконки в полях вводу
- Адаптивний дизайн

## 🎨 Стилі

### quiz-styles.sass
**Основні стилі квізу**
- Стилі для всіх компонентів
- Адаптивний дизайн
- Анімації та переходи
- Підтримка різних типів питань

**Особливості:**
- SASS з використанням змінних
- Адаптивні медіа-запити
- Анімації для інтерактивних елементів
- Підтримка темної теми для сайдбару

## 🔧 Store

### stores/quiz.js
**Оновлений Pinia store**
- Управління станом квізу
- Кешування даних
- Персистентність відповідей
- API інтеграція

**Нові методи:**
- `getAnswerForStep(stepId)` - отримати відповідь для кроку
- `isStepAnswered(stepId)` - чи відповів на крок
- `getAnsweredStepsCount()` - кількість відповідей
- `getProgressPercentage()` - відсоток прогресу
- `submitQuiz()` - відправка квізу
- `clearAnswers()` - очищення відповідей

## 🚀 Використання

### Базове використання
```vue
<template>
  <QuizLayout
    :quiz-data="quizData"
    :current-step="currentStep"
    :answers="answers"
    :total-steps="totalSteps"
    @answer="handleAnswer"
    @prev="handlePrev"
    @next="handleNext"
  />
</template>

<script setup>
import { useQuizStore } from './stores/quiz'
import QuizLayout from './QuizLayout.vue'

const quiz = useQuizStore()
const { quizData, currentStep, answers, totalSteps } = storeToRefs(quiz)

const handleAnswer = (stepId, answer) => {
  quiz.setAnswer(stepId, answer)
}

const handlePrev = () => {
  quiz.prevStep()
}

const handleNext = () => {
  quiz.nextStep()
}
</script>
```

### Індивідуальні компоненти
```vue
<template>
  <!-- Крок квізу -->
  <QuizStep
    :step="step"
    :step-index="0"
    :is-active="true"
    :answer="selectedAnswer"
    @answer="handleAnswer"
  />
  
  <!-- Консультант -->
  <QuizConsultant
    :consultant="consultant"
    :message="message"
  />
  
  <!-- Форма -->
  <ContactForm
    @submit="handleFormSubmit"
  />
</template>
```

## 📱 Адаптивність

### Breakpoints
- **Mobile**: до 768px
- **Tablet**: 768px - 1024px
- **Desktop**: 1024px+

### Особливості адаптивності
- Мобільна навігація
- Адаптивна сітка для питань
- Вертикальний лейаут на мобільних
- Оптимізовані розміри елементів

## 🎭 Анімації

### Доступні анімації
- Переходи між кроками
- Hover ефекти
- Завантаження
- Прогрес-бар
- Бонусні елементи

### Додавання нових анімацій
```sass
@keyframes your-animation
  0%
    opacity: 0
    transform: translateY(20px)
  100%
    opacity: 1
    transform: translateY(0)

.your-element
  animation: your-animation 0.3s ease-out
```

## 🔌 API інтеграція

### Структура даних
```javascript
{
  uuid: "quiz-uuid",
  assistant: {
    enabled: true,
    name: "Аліна",
    title: "Помічник нотаріуса",
    avatar: "url",
    message: "Повідомлення"
  },
  marketing: {
    discount: { value: 15, type: "percentage" },
    bonuses: [{ title: "Бонус", image: "url" }]
  },
  steps: [
    {
      id: 1,
      title: "Питання",
      type: "radio",
      answers: [
        { id: 1, label: "Відповідь", value: "value", image: "url" }
      ]
    }
  ]
}
```

## 🚀 Продакшен

### Збірка
```bash
npm run build
```

### Розгортання
1. Файли збираються в `../../public/embed/`
2. Завантажте на сервер
3. Вбудовуйте iframe

### Iframe
```html
<iframe 
  src="https://your-domain.com/embed/?uuid=quiz-uuid" 
  width="100%" 
  height="600"
  frameborder="0">
</iframe>
```

