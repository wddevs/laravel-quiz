# Інструкції по інтеграції

## 🚀 Швидкий старт

### 1. Встановлення залежностей
```bash
cd ai
npm install
```

### 2. Запуск в режимі розробки
```bash
npm run dev
```

### 3. Збірка для продакшену
```bash
npm run build
```

## 📁 Структура файлів

```
ai/
├── App.vue                    # Головний компонент
├── QuizLayout.vue             # Лейаут квізу
├── QuizStep.vue               # Компонент кроку
├── QuizConsultant.vue         # Блок консультанта
├── QuizSidebar.vue            # Сайдбар
├── QuizFooter.vue             # Футер з навігацією
├── ContactForm.vue            # Форма зворотного зв'язку
├── stores/quiz.js             # Store (скопіюйте з основного проекту)
├── main.js                    # Точка входу
├── index.html                 # HTML шаблон
├── quiz-styles.sass           # Стилі
├── vite.config.js             # Конфігурація Vite
├── tailwind.config.js         # Конфігурація Tailwind
└── package.json               # Залежності
```

## 🔧 Інтеграція в основний проект

### 1. Копіювання компонентів
Скопіюйте всі файли з папки `ai/` в ваш основний проект.

### 2. Оновлення App.vue
Замініть існуючий `App.vue` на версію з папки `ai/`.

### 3. Оновлення store
Оновіть `stores/quiz.js` додатковими методами з `ai/stores/quiz.js`.

### 4. Додавання стилів
Імпортуйте `quiz-styles.sass` в ваш основний файл стилів.

### 5. Оновлення Vite конфігурації
Додайте налаштування з `ai/vite.config.js` в ваш основний конфіг.

## 🎨 Кастомізація

### Зміна кольорів
Відредагуйте `tailwind.config.js`:
```javascript
export default {
  theme: {
    extend: {
      colors: { 
        brand: '#YOUR_COLOR' // Ваш колір
      }
    }
  }
}
```

### Зміна стилів
Відредагуйте `quiz-styles.sass` для кастомних стилів.

### Додавання нових типів питань
Розширте логіку в `QuizStep.vue`:
```javascript
const stepType = computed(() => {
  // Ваша логіка визначення типу
  if (customCondition) return 'custom'
  // ... існуюча логіка
})
```

## 🔌 API інтеграція

### Структура API відповіді
```json
{
  "uuid": "quiz-uuid",
  "assistant": {
    "enabled": true,
    "name": "Аліна",
    "title": "Помічник нотаріуса",
    "avatar": "url-to-avatar",
    "message": "Повідомлення консультанта"
  },
  "marketing": {
    "discount": {
      "value": 15,
      "type": "percentage"
    },
    "bonuses": [
      {
        "title": "Назва бонусу",
        "image": "url-to-image"
      }
    ]
  },
  "steps": [
    {
      "id": 1,
      "title": "Питання",
      "type": "radio",
      "required": false,
      "answers": [
        {
          "id": 1,
          "label": "Відповідь",
          "value": "value",
          "image": "url-to-image" // Опціонально
        }
      ]
    }
  ]
}
```

### Налаштування API
В `stores/quiz.js`:
```javascript
const API_BASE = import.meta.env.VITE_API_BASE || 'http://your-api.com/api/v1'
```

## 📱 Адаптивність

### Breakpoints
- **Mobile**: до 768px
- **Tablet**: 768px - 1024px
- **Desktop**: 1024px+

### Кастомізація адаптивності
Відредагуйте медіа-запити в `quiz-styles.sass`:
```sass
@media (max-width: 768px)
  .quiz__layout
    flex-direction: column
```

## 🎭 Анімації

### Додавання нових анімацій
В `quiz-styles.sass`:
```sass
@keyframes your-animation
  0%
    // початковий стан
  100%
    // кінцевий стан

.your-element
  animation: your-animation 1s ease-in-out
```

## 🚀 Продакшен

### Збірка
```bash
npm run build
```

### Розгортання
1. Файли збираються в `../../public/embed/`
2. Завантажте файли на ваш сервер
3. Вбудовуйте iframe з правильним URL

### Iframe вбудовування
```html
<iframe 
  src="https://your-domain.com/embed/?uuid=quiz-uuid" 
  width="100%" 
  height="600"
  frameborder="0">
</iframe>
```

## 🔍 Відладка

### Dev режим
```bash
npm run dev
```

### Логи
Всі помилки логуються в консоль браузера.

### Перевірка API
Перевірте, що API endpoint повертає правильну структуру даних.

## 📞 Підтримка

При виникненні проблем:
1. Перевірте консоль браузера на помилки
2. Переконайтеся, що API повертає правильні дані
3. Перевірте, що всі залежності встановлені
4. Переконайтеся, що конфігурація Vite правильна

