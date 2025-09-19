<script setup>
import { computed, onMounted, onBeforeUnmount, nextTick, watch } from 'vue'
import { useQuizStore } from '../../stores/quiz'
import { storeToRefs } from 'pinia'
import { useLeadForm } from '../../composables/useLeadForm'
import { loadScript, loadStyle } from '../../utils/loadExternal'

// --- CDN посилання
const ITI_CSS  = 'https://cdn.jsdelivr.net/npm/intl-tel-input@19.5.5/build/css/intlTelInput.css'
const ITI_JS   = 'https://cdn.jsdelivr.net/npm/intl-tel-input@19.5.5/build/js/intlTelInput.min.js'
const ITI_UTIL = 'https://cdn.jsdelivr.net/npm/intl-tel-input@19.5.5/build/js/utils.js'

// --- refs для інпутів і інстансів
const phoneRefs = new Map()     // key -> HTMLInputElement
const itiInstances = new Map()  // key -> iti instance

function storePhoneRef(key, el) {
    if (el) phoneRefs.set(key, el)
    else phoneRefs.delete(key)
}

// --- завантаження бібліотеки один раз
async function ensureIntlTelInput() {
    if (typeof window === 'undefined') return
    await loadStyle(ITI_CSS)
    await loadScript(ITI_JS)
}

// --- ініціалізація/знищення
function destroyTelInputs() {
    itiInstances.forEach(inst => { try { inst.destroy() } catch {} })
    itiInstances.clear()
}

function initTelInputs() {
    if (!window.intlTelInput) return
    // Почистити можливі попередні інстанси
    destroyTelInputs()

    // Пройтись по ВСІХ знайдених tel-полях і навісити плагін
    phoneRefs.forEach((el, key) => {
        if (!el) return
        const iti = window.intlTelInput(el, {
            initialCountry: 'auto',
            onlyCountries: ['ua', 'pl', 'sk'],
            separateDialCode: true,
            nationalMode: false,
            formatOnDisplay: true,
            dropdownContainer: document.body,
            utilsScript: ITI_UTIL, // автозавантаження utils
            geoIpLookup: cb => {
                fetch('https://ipapi.co/json')
                    .then(r => r.json())
                    .then(d => {
                        const cc = (d?.country_code || 'UA').toLowerCase()
                        cb(['ua','pl','sk'].includes(cc) ? cc : 'ua')
                    })
                    .catch(() => cb('ua'))
            },
        })

        // Якщо користувач міняє країну — можна одразу підчистити помилку поля
        el.addEventListener('countrychange', () => {
            // наприклад, перевалідувати:
            // validateField(key, el.value)
        })

        itiInstances.set(key, iti)
    })
}

// --- дістати E.164 і записати у form перед сабмітом
function syncTelValuesIntoForm() {
    itiInstances.forEach((iti, key) => {
        const e164 = iti.getNumber() // +380..., +48..., тощо
        if (e164) {
            // перезаписати в реактивну форму, щоб пішло на бек саме нормалізоване значення
            form[key] = e164
        }
    })
}

// -------------------------

const quiz = useQuizStore()
const { quizData } = storeToRefs(quiz)

const leadCfg = computed(() => quizData.value?.leadForm || {})
const header = computed(() => leadCfg.value.header)
const content = computed(() => leadCfg.value.content)
const buttonText = computed(() => leadCfg.value.buttonText || 'Відправити')

const {
    fields, form, privacy, errors, submitting,
    validateField, submit,
} = useLeadForm()

// Сабміт
async function onSubmit(e) {
    e.preventDefault()
    // перед сабмітом — синхронізуємо телефонні поля у формат E.164
    syncTelValuesIntoForm()
    const { ok } = await submit({ autoAdvance: true })
    if (ok) {
        // якщо у сторі крок thanks окремий — можеш викликати quiz.goToThanks()
        quiz.nextStep()
    }
}

// --- монтаж: завантажити бібліотеку й ініціалізувати після рендера
onMounted(async () => {
    await ensureIntlTelInput()
    await nextTick()
    initTelInputs()
})

// --- якщо список полів змінився (наприклад, конфіг прийшов пізніше) — перевішуємо
let debounceTimer = null
watch(fields, async () => {
    clearTimeout(debounceTimer)
    debounceTimer = setTimeout(async () => {
        await nextTick()
        initTelInputs()
    }, 50)
}, { immediate: true })

// --- при демонтажі — знищити інстанси
onBeforeUnmount(() => {
    destroyTelInputs()
})
</script>

<template>
    <div class="form">
        <h2 class="form__title" v-if="header">{{ header }}</h2>
        <p class="form__subtitle" v-if="content">{{ content }}</p>

        <form class="form__body" @submit="onSubmit" novalidate>
            <div class="form__row">
                <div class="form__group" v-for="f in fields" :key="f.key">
                    <label class="form__label" :for="`form-${f.key}`">{{ f.label }}</label>

                    <div class="form__input-wrapper" :class="{ 'has-error': !!errors[f.key] }">
            <span class="form__icon" aria-hidden="true">
              <svg v-if="f.type==='name'" width="16" height="16" viewBox="0 0 24 24">
                <path d="M12 12a4 4 0 1 0-0.001-8.001A4 4 0 0 0 12 12Zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4Z" fill="currentColor"/>
              </svg>
              <svg v-else-if="f.type==='tel'" width="16" height="16" viewBox="0 0 24 24">
                <path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2a1 1 0 0 1 1.02-.24c1.12.37 2.33.57 3.57.57a1 1 0 0 1 1 1V20a1 1 0 0 1-1 1C10.01 21 2 12.99 2 3.5A1 1 0 0 1 3 2.5h3.5a1 1 0 0 1 1 1c0 1.25.2 2.45.57 3.57a1 1 0 0 1-.25 1.02l-2.2 2.2Z" fill="currentColor"/>
              </svg>
            </span>

                        <!-- TEXT/NAME -->
                        <input
                            v-if="f.type==='name' || f.type==='text' || !f.type"
                            class="form__input"
                            type="text"
                            :id="`form-${f.key}`"
                            :name="f.key"
                            :placeholder="f.placeholder || ''"
                            v-model.trim="form[f.key]"
                            @blur="validateField(f.key, form[f.key])"
                            :autocomplete="f.type==='name' ? 'name' : 'on'"
                            :required="!!f.required"
                        />

                        <!-- TEL -->
                        <input
                            v-else-if="f.type==='tel'"
                            class="form__input form__input--phone"
                            type="tel"
                            :id="`form-${f.key}`"
                            :name="f.key"
                            :placeholder="f.placeholder || ''"
                            v-model.trim="form[f.key]"
                            @blur="validateField(f.key, form[f.key])"
                            autocomplete="tel"
                            :required="!!f.required"
                            inputmode="tel"
                            :ref="el => storePhoneRef(f.key, el)"
                        />

                        <!-- інші типи можна додати тут -->
                    </div>

                    <p v-if="errors[f.key]" class="form__error">{{ errors[f.key] }}</p>
                </div>
            </div>

            <div class="form__actions">
                <button type="submit" class="button form__submit" :disabled="submitting">
                    <span v-if="!submitting">{{ buttonText }}</span>
                    <span v-else>Надсилаю…</span>
                </button>
            </div>

            <p v-if="errors.__submit" class="form__error">{{ errors.__submit }}</p>

            <label class="form__privacy">
                <input type="checkbox" v-model="privacy" />
                <span>З політикою конфіденційності ознайомлений(а)</span>
            </label>
            <p v-if="errors.__privacy" class="form__error">{{ errors.__privacy }}</p>
        </form>
    </div>
</template>

<style scoped>
.has-error .form__input { border-color: #e5484d; }
.form__error { color: #e5484d; font-size: 0.875rem; margin-top: .25rem; }

/* Коли separateDialCode=true, інпут потребує трохи додаткового відступу ліворуч */
.form__input--phone {
    padding-left: 3.25rem; /* під прапор/код країни */
}
</style>
