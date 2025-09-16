<script setup>
import { computed } from 'vue'
import { useQuizStore } from '../../stores/quiz.js'
import { storeToRefs } from 'pinia'

const props = defineProps({
    // скільки бонусів показувати у сайдбарі
    limit: { type: Number, default: 2 }
})

const quiz = useQuizStore()
const { quizData, isCompleted } = storeToRefs(quiz)

/* marketing → bonuses */
const marketing = computed(() => quizData.value?.marketing || {})
const bonusesEnabled = computed(() => !!marketing.value?.bonusesEnabled)
const bonusesTitle = computed(() => marketing.value?.bonusesTitle || '')
const bonusesAll = computed(() =>
    Array.isArray(marketing.value?.bonuses) ? marketing.value.bonuses : []
)
const bonuses = computed(() => {
    if (!props.limit || props.limit < 0) return bonusesAll.value
    return bonusesAll.value.slice(0, props.limit)
})
const showBonuses = computed(() => bonusesEnabled.value && bonuses.value.length > 0)

// нормалізація посилання
const href = (u) => (typeof u === 'string' && u ? u : '#')
</script>

<template>
    <div class="sidebar__bonus" v-if="showBonuses">
        <div class="sidebar__bonus-title" v-if="bonusesTitle">{{ bonusesTitle }}</div>

        <div class="sidebar__bonus-list">
            <div
                v-for="(b, i) in bonuses"
                :key="i"
                class="sidebar__bonus-item"
                :class="{ 'is-locked': !isCompleted }"
            >
                <a class="sidebar__bonus-link" :href="href(b.link)" target="_blank" rel="noopener">
                    <div class="sidebar__bonus-image" :style="b.image ? { backgroundImage: `url(${b.image})` } : {}">
                        <img v-if="!b.image" :src="b.image" :alt="b.name" />
                    </div>
                    <div class="sidebar__bonus-text">{{ b.name }}</div>
                </a>

                <div class="sidebar__bonus-lock" v-if="!isCompleted">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path
                            d="M12 1C8.676 1 6 3.676 6 7v3H5c-1.103 0-2 .897-2 2v9c0 1.103.897 2 2 2h14c1.103 0 2-.897 2-2v-9c0-1.103-.897-2-2-2h-1V7c0-3.324-2.676-6-6-6zm0 2c2.276 0 4 1.724 4 4v3H8V7c0-2.276 1.724-4 4-4z"
                        />
                    </svg>
                </div>
            </div>
        </div>
    </div>
</template>
