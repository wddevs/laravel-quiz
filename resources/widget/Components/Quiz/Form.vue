<script setup>
    import {computed} from 'vue'
    import {useQuizStore} from "../../stores/quiz";
    import {storeToRefs} from "pinia";
    import { useLeadForm } from '../../composables/useLeadForm'

    const quiz = useQuizStore()
    const {quizData} = storeToRefs(quiz)

    const leadCfg = computed(() => quizData.value?.leadForm || {})
    const header = computed(() => leadCfg.value.header)
    const content = computed(() => leadCfg.value.content)
    const buttonText = computed(() => leadCfg.value.buttonText)

    const {
        fields, form, privacy, errors, submitting,
        validateField, submit,
    } = useLeadForm()

    async function onSubmit(e) {
        e.preventDefault()
        const { ok } = await submit({ autoAdvance: true }) // переводить у thanks
        if (ok) {
            quiz.nextStep() // перейти до кроку дякуємо
        }
    }
</script>

<template>
    <!-- Форма зворотного зв'язку -->
    <div class="form">
        <h2 class="form__title" v-if="header">
            {{ header }}
        </h2>
        <p class="form__subtitle" v-if="content">
            {{ content }}
        </p>

        <form class="form__body" @submit="onSubmit" novalidate>
            <div class="form__row">
                <div class="form__group" v-for="f in fields" :key="f.key">
                    <label class="form__label" :for="`form-${f.key}`">{{ f.label }}</label>

                    <div class="form__input-wrapper" :class="{ 'has-error': !!errors[f.key] }">
            <span class="form__icon" aria-hidden="true">
              <!-- Іконки за типом -->
              <svg v-if="f.type==='name'" width="16" height="16" viewBox="0 0 24 24"><path d="M12 12a4 4 0 1 0-0.001-8.001A4 4 0 0 0 12 12Zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4Z" fill="currentColor"/></svg>
              <svg v-else-if="f.type==='tel'" width="16" height="16" viewBox="0 0 24 24"><path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2a1 1 0 0 1 1.02-.24c1.12.37 2.33.57 3.57.57a1 1 0 0 1 1 1V20a1 1 0 0 1-1 1C10.01 21 2 12.99 2 3.5A1 1 0 0 1 3 2.5h3.5a1 1 0 0 1 1 1c0 1.25.2 2.45.57 3.57a1 1 0 0 1-.25 1.02l-2.2 2.2Z" fill="currentColor"/></svg>
            </span>

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

                        <input
                            v-else-if="f.type==='tel'"
                            class="form__input"
                            type="tel"
                            :id="`form-${f.key}`"
                            :name="f.key"
                            :placeholder="f.placeholder || ''"
                            v-model.trim="form[f.key]"
                            @blur="validateField(f.key, form[f.key])"
                            autocomplete="tel"
                            :required="!!f.required"
                            inputmode="tel"
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
                <input type="checkbox" checked v-model="privacy"/>
                <span>З політикою конфіденційності ознайомлений(а)</span>
            </label>
            <p v-if="errors.__privacy" class="form__error">{{ errors.__privacy }}</p>
        </form>
    </div>
</template>

<style scoped>
.has-error .form__input { border-color: #e5484d; }
.form__error { color: #e5484d; font-size: 0.875rem; margin-top: .25rem; }


</style>
