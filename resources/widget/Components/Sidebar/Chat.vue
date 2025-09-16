<script setup>
import { computed } from 'vue'
import { storeToRefs } from 'pinia'
import { useQuizStore } from '../../stores/quiz'
import TransitionWrapper from '../../Animations/TransitionWrapper.vue'

const quiz = useQuizStore()
const { quizData, isQuestions, currentStepData } = storeToRefs(quiz)

const assistant = computed(() => quizData.value?.assistant || {})
const avatar = computed(() => assistant.value?.avatar || false)
const name   = computed(() => assistant.value?.name   || false)
const title  = computed(() => assistant.value?.title  || false)

const isAssistantVisible = computed(() => !!assistant.value?.enabled)

// показуємо віджет ТІЛЬКИ під час питань і коли асистент увімкнений
const showWidget = computed(() => Boolean(assistant.value.enabled) && isQuestions.value)

// текст підказки з поточного кроку
const rawHelp   = computed(() => currentStepData.value?.help ?? '')
const helpText  = computed(() => typeof rawHelp.value === 'string' ? rawHelp.value.trim() : '')
const showHint  = computed(() => showWidget.value && helpText.value.length > 0)

// ключ для плавної заміни повідомлення при зміні кроку
const messageKey = computed(() => `step-${currentStepData.value?.id || 'none'}`)

</script>

<template>
    <div class="sidebar__chat" v-if="showWidget">
        <div class="sidebar__chat-profile">
            <div class="sidebar__chat-avatar" v-if="avatar">
                <img :src="avatar" :alt="name" v-if="avatar" />
                <div class="sidebar__chat-status"></div>
            </div>
            <div class="sidebar__chat-info">
                <div class="sidebar__chat-name" v-if="name">{{name}}</div>
                <div class="sidebar__chat-role" v-if="title">{{title}}</div>
            </div>
        </div>
        <!-- Булька показується тільки якщо в кроку є help -->
        <TransitionWrapper transition-name="fade" mode="out-in">
            <div class="sidebar__chat-message" v-if="showHint" :key="messageKey">
                {{ helpText }}
                <!-- якщо help може містити HTML, заміни на: v-html="helpText" -->
            </div>
        </TransitionWrapper>
    </div>
</template>
