<script setup>
import { toRef, computed } from 'vue'
import { useQuizStore } from '../stores/quiz.js'
import { storeToRefs } from 'pinia'
import StartPageLayout from './Layouts/StartPageLayout.vue'

const props = defineProps({
    data: { type: Object, default: () => ({}) }
})

/** reactive props */
const quizData = toRef(props, 'data')
const pageInfo = computed(() => quizData.value?.startPage || {})

/** store (для прогресу — answers/totalSteps) */
const quiz = useQuizStore()
const { answers, totalSteps,  } = storeToRefs(quiz)
const onStart = () => quiz.startQuiz()

/** --- Discount (marketing.discount) --- */
const marketing   = computed(() => quizData.value?.marketing || {})
const discountCfg = computed(() => marketing.value?.discount || null)

const maxPercent = computed(() => {
    const v = Number(discountCfg.value?.value ?? 0)
    return Number.isFinite(v) ? v : 0
})
const discountTitle = computed(() => discountCfg.value?.title || 'Ваша знижка')
const hasDiscount   = computed(() => !!maxPercent.value)

const answeredCount = computed(() => Object.keys(answers.value || {}).length)
const stepGain = computed(() => {
    const steps = Number(totalSteps.value || 0)
    return steps ? maxPercent.value / steps : 0
})
const discountValue = computed(() => {
    const cfg = discountCfg.value
    if (!cfg || !maxPercent.value) return 0
    if (cfg.type === 'percent' && cfg.effect === 'increasing') {
        const raw = answeredCount.value * stepGain.value
        return Math.min(Math.round(raw), maxPercent.value)
    }
    if (cfg.type === 'percent') return maxPercent.value
    return 0
})

/** --- Bonuses (marketing.bonuses) --- */
const bonusesEnabled = computed(() => !!marketing.value?.bonusesEnabled)
const bonusesTitle   = computed(() => marketing.value?.bonusesTitle || '')
const bonuses        = computed(() =>
    Array.isArray(marketing.value?.bonuses) ? marketing.value.bonuses : []
)
const showBonuses    = computed(() => bonusesEnabled.value && bonuses.value.length > 0)
const norm = (u) => (typeof u === 'string' && u ? u : undefined)
</script>

<template>
    <StartPageLayout>
        <div class="home" :class="{ 'has-bg-image': pageInfo.bg  }" :style="{ backgroundImage: pageInfo.bg ? 'url(' + pageInfo.bg + ')' : 'none' }">

            <div class="home__mobile-img-wrapper" >
                <img
                    class="home__mobile-img"
                    :src="pageInfo.bg"
                    alt="home-mobile"
                    v-if="pageInfo.bg"
                />
            </div>
            <div class="container">
                <div class="home__wrapper">
                    <div class="home__inner">
                        <div class="home__header" v-if="pageInfo.logo || pageInfo.company">
                            <img
                                :src="pageInfo.logo"
                                alt="logo"
                                class="home__logo"
                                v-if="pageInfo.logo"
                            />
                            <div class="home__company" v-if="pageInfo.company">
                                {{ pageInfo.company }}
                            </div>
                        </div>
                        <div class="cta">
                            <h1 class="cta__title" v-if="pageInfo.title">
                                {{ pageInfo.title }}
                            </h1>

                            <h2 class="home__subtitle" v-if="pageInfo.subtitle">
                                {{ pageInfo.subtitle }}
                            </h2>

                            <button class="button cta__button" @click="onStart" v-if="pageInfo.buttonText">
                                <span>{{ pageInfo.buttonText }}</span>
                            </button>
                            <div class="cta__decoration" v-if="discountCfg.enabled">
                                <div class="legal-badge">
                                    <div class="legal-badge__icon">
                                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z"
                                                fill="none"
                                                stroke="#f4e4ba"
                                                stroke-width="2"
                                            />
                                            <path
                                                d="M14 2v6h6"
                                                fill="none"
                                                stroke="#f4e4ba"
                                                stroke-width="2"
                                            />
                                            <path
                                                d="M9 15l2 2 4-4"
                                                fill="none"
                                                stroke="#f4e4ba"
                                                stroke-width="2"
                                            />
                                        </svg>
                                    </div>
                                    <div class="legal-badge__content">
                                        <span class="legal-badge__title">{{ discountTitle }}: {{ discountCfg.value }}%</span>
                                        <span class="legal-badge__subtitle"
                                        >При замовленні онлайн</span
                                        >
                                    </div>
                                    <div class="legal-badge__stamp">
                                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <circle
                                                cx="12"
                                                cy="12"
                                                r="10"
                                                fill="none"
                                                stroke="#f4e4ba"
                                                stroke-width="1.5"
                                            />
                                            <path d="M7 14l5-5 5 5z" fill="#f4e4ba" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Бонуси з конфіга -->
                        <div class="bonus" v-if="showBonuses">
                            <h3 class="bonus__title">{{ bonusesTitle || 'Bonuses' }}</h3>

                            <div class="bonus__list" v-if="bonuses.length">
                                <div
                                    class="bonus__item"
                                    v-for="(b, i) in bonuses"
                                    :key="i"
                                    :style="b?.image ? { backgroundImage: `url(${b.image})` } : {}"
                                >
                                    <div class="bonus__lock-icon">
                                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                            <path
                                                d="M12 1C8.676 1 6 3.676 6 7v3H5c-1.103 0-2 .897-2 2v9c0 1.103.897 2 2 2h14c1.103 0 2-.897 2-2v-9c0-1.103-.897-2-2-2h-1V7c0-3.324-2.676-6-6-6zm0 2c2.276 0 4 1.724 4 4v3H8V7c0-2.276 1.724-4 4-4z"
                                            />
                                        </svg>
                                    </div>
                                    <h4 class="bonus__item-title">{{ b.name }}</h4>
                                </div>
                            </div>
                        </div>
                        <!-- /Бонуси -->
                    </div>
                </div>
            </div>
        </div>
    </StartPageLayout>
</template>
