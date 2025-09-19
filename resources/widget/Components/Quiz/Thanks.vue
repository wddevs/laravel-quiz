<script setup>
import { computed } from 'vue'
import { storeToRefs } from 'pinia'
import { useQuizStore } from '../../stores/quiz.js'

const quiz = useQuizStore()
const { quizData, answers, totalSteps } = storeToRefs(quiz)

/** theme */
const theme = computed(() => quizData.value?.theme || {})
const cssVars = computed(() => ({
    '--primary': theme.value.primary || '#3B82F6',
    '--text': theme.value.text || '#0f172a',
}))

/** thanks */
const thanks = computed(() => quizData.value?.thanksPage || {})
const header = computed(() => thanks.value?.header || 'Дякуємо!')
const content = computed(() => thanks.value?.content || '')
const socialLinks = computed(() =>
    Array.isArray(thanks.value?.socials) ? thanks.value.socials : []
)

/** marketing (discount + bonuses) */
const marketing = computed(() => quizData.value?.marketing || {})
const discountCfg = computed(() => marketing.value?.discount || null)

const answeredCount = computed(() => Object.keys(answers.value || {}).length)

const maxPercent = computed(() => {
    const v = Number(discountCfg.value?.value ?? 0)
    return Number.isFinite(v) ? v : 0
})

/* крок знижки: max / totalSteps (захист від ділення на 0) */
const stepGain = computed(() => {
    const steps = Number(totalSteps.value || 0)
    if (!steps || !maxPercent.value) return 0
    return maxPercent.value / steps
})

const discountValue = computed(() => {
    const cfg = discountCfg.value
    if (!cfg || !maxPercent.value) return 0

    if (cfg.type === 'percent') {
        if (cfg.effect === 'increasing') {
            const raw = answeredCount.value * stepGain.value
            const rounded = Math.round(raw)
            return Math.min(Math.max(rounded, 0), maxPercent.value)
        }
        return maxPercent.value
    }

    if (cfg.type === 'fixed') {
        return maxPercent.value;
    }
    return 0
})

const hasDiscount = computed(() => discountValue.value > 0)
const discountTitle = computed(() => discountCfg.value?.title || 'Ваша знижка')

const bonusesEnabled = computed(() => !!marketing.value?.bonusesEnabled)
const bonusTitle = computed(() => marketing.value?.bonusesTitle || '')
const bonuses = computed(() =>
    Array.isArray(marketing.value?.bonuses) ? marketing.value.bonuses : []
)

/** іконки соціалок (мінімальний меппер по name) */
const socialIcon = (name = '') => {
    const n = name.toLowerCase()
    if (n.includes('instagram')) {
        return {
            viewBox: '0 0 24 24',
            d: 'M7.8,2H16.2C19.4,2 22,4.6 22,7.8V16.2A5.8,5.8 0 0,1 16.2,22H7.8C4.6,22 2,19.4 2,16.2V7.8A5.8,5.8 0 0,1 7.8,2M7.6,4A3.6,3.6 0 0,0 4,7.6V16.4C4,18.39 5.61,20 7.6,20H16.4A3.6,3.6 0 0,0 20,16.4V7.6C20,5.61 18.39,4 16.4,4H7.6M17.25,5.5A1.25,1.25 0 0,1 18.5,6.75A1.25,1.25 0 0,1 17.25,8A1.25,1.25 0 0,1 16,6.75A1.25,1.25 0 0,1 17.25,5.5M12,7A5,5 0 0,1 17,12A5,5 0 0,1 12,17A5,5 0 0,1 7,12A5,5 0 0,1 12,7M12,9A3,3 0 0,0 9,12A3,3 0 0,0 12,15A3,3 0 0,0 15,12A3,3 0 0,0 12,9Z'
        }
    }
    if (n.includes('youtube')) {
        return {
            viewBox: '0 0 24 24',
            d: 'M10,15L15.19,12L10,9V15M21.56,7.17C21.69,7.64 21.78,8.27 21.84,9.07C21.91,9.87 21.94,10.56 21.94,11.16L22,12C22,14.19 21.84,15.8 21.56,16.83C21.31,17.73 20.73,18.31 19.83,18.56C19.36,18.69 18.5,18.78 17.18,18.84C15.88,18.91 14.69,18.94 13.59,18.94L12,19C7.81,19 5.2,18.84 4.17,18.56C3.27,18.31 2.69,17.73 2.44,16.83C2.31,16.36 2.22,15.73 2.16,14.93C2.09,14.13 2.06,13.44 2.06,12.84L2,12C2,9.81 2.16,8.2 2.44,7.17C2.69,6.27 3.27,5.69 4.17,5.44C4.64,5.31 5.5,5.22 6.82,5.16C8.12,5.09 9.31,5.06 10.41,5.06L12,5C16.19,5 18.8,5.16 19.83,5.44C20.73,5.69 21.31,6.27 21.56,7.17Z'
        }
    }
    // tiktok або інші — простий generic-лінк
    return {
        viewBox: '0 0 24 24',
        d: 'M10.5 3a1.5 1.5 0 0 1 1.5 1.5V6h2a4 4 0 0 0 4 4v2a6 6 0 0 1-4-1.5V17a5 5 0 1 1-5-5h1.5v2H9a3 3 0 1 0 3 3V4.5A1.5 1.5 0 0 1 13.5 3h-3Z'
    }
}

const normHref = (u) => (typeof u === 'string' && u ? u : '#')
</script>

<template>
    <div class="quiz__thanks" :style="cssVars">

        <article class="thank-page">
            <div class="thank-page__content">
                <!-- LEFT -->
                <div class="thank-page__col thank-page__col_left">
                    <p class="title thank-page__title" v-if="header">{{ header }}</p>
                    <p class="subtitle thank-page__subtitle" v-if="content">{{ content }}</p>

                    <!-- socials from config -->
                    <div class="thank-page__socials" v-if="socialLinks.length">
                        <a
                            v-for="(s, i) in socialLinks"
                            :key="i"
                            class="thank-page__social"
                            :href="normHref(s.link)"
                            target="_blank"
                            rel="noopener"
                            :title="s.name"
                            aria-label="Відкрити у новій вкладці"
                        >
                            <svg class="mdi-icon" :viewBox="socialIcon(s.name).viewBox">
                                <path :d="socialIcon(s.name).d" stroke-width="0" fill-rule="nonzero"/>
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- RIGHT -->
                <div class="thank-page__col thank-page__col_right">
                    <!-- discount -->
                    <div class="discount-badge thank-page__discount" v-if="hasDiscount">
                        <div class="discount-badge__title">{{ discountTitle }}</div>
                        <div class="discount-badge__value">{{ discountValue }}%</div>
                    </div>

                    <!-- bonuses -->
                    <template v-if="bonusesEnabled && bonuses.length">
                        <div class="thank-page__bonuses-title" v-if="bonusTitle">{{ bonusTitle }}</div>
                        <div class="bonuses thank-page__bonuses">
                            <div
                                v-for="(b, idx) in bonuses"
                                :key="idx"
                                class="bonus bonuses__bonus"
                            >
                                <a
                                    class="bonus__wrapper"
                                    :href="normHref(b.link)"
                                    target="_blank"
                                    rel="noopener"
                                    :style="b.image ? { backgroundImage: `url(${b.image})` } : {}"
                                >
                                    <div class="bonus__layer"></div>
                                    <span class="bonus__text">{{ b.name }}</span>
                                </a>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="thank-page__white-label"><!-- опціонально --></div>
            </div>
        </article>
    </div>
</template>


<style>
.thank-page {
    width: 100%;
    min-height: 100%;
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    padding: 4rem 1rem;
    //background-color: var(--color-bg-quiz,#fff)
}

.thank-page_rtl::-webkit-scrollbar {
    width: 10px
}

.thank-page_rtl::-webkit-scrollbar-thumb,.thank-page_rtl::-webkit-scrollbar-track {
    border-left: none;
    border-right: 8px solid transparent
}

.thank-page_content-over {
    position: static;
    padding-top: 2.1875rem
}

.thank-page_assistant {
    padding-top: 3.75rem
}

.thank-page__white-label {
    position: fixed;
    bottom: 1.875rem
}

.thank-page__content {
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-orient: vertical;
    -webkit-box-direction: normal;
    -ms-flex-direction: column;
    flex-direction: column;
    -webkit-box-align: center;
    -ms-flex-align: center;
    align-items: center;
    width: 100%;
    place-content: center;
    margin: auto 0
}

.thank-page__content_row {
    -webkit-box-orient: horizontal;
    -webkit-box-direction: normal;
    -ms-flex-direction: row;
    flex-direction: row;
    place-items: flex-start
}

.thank-page__content_row .thank-page__col_left,.thank-page__content_row .thank-page__col_right {
    -webkit-box-align: start;
    -ms-flex-align: start;
    align-items: flex-start;
    text-align: left
}

.thank-page__content_row .thank-page__button {
    max-width: none;
    padding: .375rem 2rem .375rem 1.5rem
}

.thank-page__content_row .thank-page__social:first-child {
    padding-left: 0
}

.thank-page__content_row .thank-page__col:first-child {
    margin-bottom: 0
}

.thank-page__col {
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-pack: center;
    -ms-flex-pack: center;
    justify-content: center;
    -webkit-box-orient: vertical;
    -webkit-box-direction: normal;
    -ms-flex-direction: column;
    flex-direction: column;
    -webkit-box-align: center;
    -ms-flex-align: center;
    align-items: center;
    padding-left: 3.125rem;
    padding-right: 3.125rem
}

.thank-page__col:first-child {
    margin-bottom: 2rem
}

@media (max-width: 767px) {
    .thank-page__col {
        padding-left:1.25rem;
        padding-right: 1.25rem
    }
}

.thank-page__col_left,.thank-page__col_right {
    -webkit-box-align: center;
    -ms-flex-align: center;
    align-items: center;
    text-align: center
}

.thank-page__col_right {
    min-width: 20rem
}

.thank-page__col_left {
    width: 100%;
    max-width: 50rem
}

.thank-page__title {
    margin-bottom: 2.5rem!important;
    color: var(--quiz-title,#fff);
    font-size: 2rem;
    margin-top: 0;
}

.thank-page__subtitle {
    max-width: 50rem;
    font-size: 1.25rem;
    font-weight: 400;
    line-height: 1.25;
    word-break: break-word;
    margin-bottom: 2.5rem!important;
    margin-top: 0;
}

.thank-page__bonuses {
    max-width: 21.75rem
}

.thank-page__bonuses-title {
    font-weight: 500;
    margin-bottom: .625rem;
    color: var(--quiz-title, #fff)
}

.thank-page__discount {
    margin-bottom: .9375rem
}

.thank-page__buttons {
    margin-bottom: 1.875rem;
    width: 100%
}

.thank-page__button {
    width: 100%;
    max-width: 21.75rem;
    padding: .375rem 1.375rem;
    display: block;
    position: relative;
    background-color: var(--color-bg-2,#fff);
    color: var(--color-bg-text,#363636);
    border-radius: var(--button-border-radius,.5rem);
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
    margin: 0 auto .75rem
}

.thank-page__button:last-child {
    margin-bottom: 0
}

.thank-page__button:hover {
    text-decoration: none
}

.thank-page__button:visited {
    color: var(--color-bg-text,#fff)
}

.thank-page__arrow {
    position: absolute;
    top: .1875rem;
    right: .1875rem;
    -webkit-transform: rotate(-45deg);
    transform: rotate(-45deg)
}

.thank-page__arrow svg {
    fill: var(--color-lighten);
    width: 1.25rem
}

.thank-page__socials {
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex
}

.thank-page__social {
    padding-left: .75rem;
    padding-right: .75rem
}

.thank-page__social svg {
    width: 1.5625rem;
    fill: var(--color-lighten);
    transition: fill .15s ease-in-out
}

.thank-page__social:hover svg {
    fill: var(--quiz-primary,#fff)
}

.thank-page__disclaimer-wrapper {
    line-height: .6875rem
}

.thank-page__disclaimer-wrapper_col {
    margin-top: 1.5rem
}

.thank-page__disclaimer-wrapper_not-over-disclaimer {
    position: absolute;
    bottom: 1.5rem
}

.thank-page__disclaimer {
    text-align: center;
    font-weight: 400;
    font-size: .6875rem;
    color: var(--color-bg-6);
    opacity: .6
}

.thank-page__social-disclaimer {
    text-decoration: none!important
}

.thank-page__social-disclaimer:after {
    color: var(--light-grey-blue);
    content: "\2731";
    font-size: .375rem;
    font-style: normal;
    position: absolute;
    height: inherit
}

.bonus {
    position: relative;
    width: 100%;
    height: var(--bonus-height);
    //background: -webkit-gradient(linear,left top,left bottom,from(var(--gradient-main)));
    //background: linear-gradient(var(--gradient-main));
    border-radius: .3125rem;
    line-height: 1;
    margin-bottom: .9375rem
}

@media (max-width: 767px) {
    .bonus {
        margin-bottom:.625rem
    }
}

.bonus:last-child {
    margin-bottom: 0
}

.bonus:not(.bonus_type_custom) .bonus__text {
    text-shadow: none
}

.bonus__wrapper {
    position: relative;
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-align: center;
    -ms-flex-align: center;
    align-items: center;
    overflow: hidden;
    height: 100%;
    background-size: 4.375rem;
    border-radius: .3125rem;
    background-repeat: no-repeat;
    background-position: center;
    background-size: cover;
    -webkit-box-shadow: .1187rem .4188rem .4375rem 0 rgba(0,0,0,.1);
    box-shadow: .1187rem .4188rem .4375rem 0 rgba(0,0,0,.1);
    padding-bottom: 28%;
}

.bonus__layer {
    background: -webkit-gradient(linear,left top,right top,from(rgba(0,0,0,.1)),to(rgba(0,0,0,.55)));
    background: linear-gradient(90deg,rgba(0,0,0,.1),rgba(0,0,0,.55));
    z-index: 10;
}

.bonus__layer,.bonus__lock {
    position: absolute;
    top: 0;
    bottom: 0;
    left: 0;
    right: 0
}

.bonus__lock {
    height: 100%;
    width: 100%;
    z-index: 2;
    -webkit-transition: all 1s ease;
    transition: all 1s ease
}

.bonus__lock-icon {
    position: absolute;
    right: 0;
    top: 0;
    left: unset;
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-align: center;
    -ms-flex-align: center;
    align-items: center;
    -webkit-box-pack: center;
    -ms-flex-pack: center;
    justify-content: center;
    -webkit-transform: translate(40%,-40%);
    transform: translate(40%,-40%);
    background-color: #8d8d8d;
    border-radius: 100%;
    z-index: 2;
    height: 1.562rem;
    width: 1.562rem;
    border: .25rem solid #fff;
    -webkit-box-shadow: 0 .25rem .625rem 0 var(--color-bg-alpha2,rgba(0,0,0,.1));
    box-shadow: 0 .25rem .625rem 0 var(--color-bg-alpha2,rgba(0,0,0,.1))
}

.bonus__lock-icon svg {
    z-index: 2;
    fill: #fff;
    height: .6875rem!important
}

.bonus__unlock {
    width: 1.125rem;
    height: 1.125rem;
    position: relative
}

.bonus__unlock svg {
    position: absolute;
    bottom: 0;
    left: 0;
    z-index: 1;
    -webkit-transition: all .3s ease;
    transition: all .3s ease;
    -webkit-box-shadow: .0625rem .1875rem .25rem rgba(0,0,0,.1);
    box-shadow: .0625rem .1875rem .25rem rgba(0,0,0,.1)
}

.bonus__unlock:hover svg {
    fill: var(--color-lighten)
}

.bonus__unlock+.bonus__emoji {
    display: none
}

.bonus__text {
    position: absolute;
    font-size: .8125rem;
    font-weight: 600;
    color: #fff;
    right: 9%;
    max-height: 60%;
    top: 20%;
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-align: center;
    -ms-flex-align: center;
    align-items: center;
    min-height: 60%;
    max-width: 66%;
    text-align: right;
    word-break: break-word;
    text-shadow: .0625rem .0625rem .125rem rgba(0,0,0,.2);
    z-index: 20;
}

@media (max-width: 819px) {
    .bonus__text {
        min-height:auto;
        overflow-wrap: break-word;
        overflow: hidden;
        max-width: 65%;
        min-width: 60%;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        -webkit-hyphens: auto;
        -ms-hyphens: auto;
        hyphens: auto
    }
}

.bonus_locked {
    cursor: not-allowed
}

.bonus_unlocked {
    cursor: pointer
}

.bonus_type_custom .bonus__wrapper {
    background-size: cover;
    background-position: 50%
}

.bonus_type_icon {
    background: #dbdada
}

.bonus_type_icon .bonus__wrapper {
    background-size: auto 2.75rem;
    background-position: .6875rem 50%;
    -webkit-box-shadow: .1875rem .125rem .3125rem 0 rgba(0,0,0,.1);
    box-shadow: .1875rem .125rem .3125rem 0 rgba(0,0,0,.1)
}

@media (max-width: 375px) {
    .bonus_type_icon .bonus__wrapper {
        background-size:2.375rem 2.375rem;
        background-position: 10% 50%
    }
}

.bonus_type_icon .bonus__text {
    text-shadow: none;
    color: var(--grey-blue);
    max-width: 50%
}

.bonus_type_icon .bonus__layer {
    display: none
}

.bonus_type_icon .bonus__unexpanded .bonus__wrapper {
    background-size: auto
}

.bonus__emoji {
    margin: 1.375rem .8125rem;
    z-index: 1
}

@media (max-width: 819px) {
    .bonus__emoji {
        margin:1.375rem .5625rem
    }
}

.bonus_type_emoji {
    background: none
}

.bonus_type_emoji .bonus__wrapper {
    -webkit-box-shadow: .1875rem .125rem .3125rem 0 rgba(0,0,0,.1);
    box-shadow: .1875rem .125rem .3125rem 0 rgba(0,0,0,.1)
}

.bonus_type_emoji .bonus__lock-icon {
    background-color: #8d8d8d
}

.bonus_type_emoji .bonus__text {
    color: var(--grey-blue);
    max-width: 60%;
    text-shadow: none
}

@media (max-width: 375px) {
    .bonus_type_emoji .bonus__text {
        max-width:64%
    }
}

.bonus_type_emoji.bonus_locked .bonus__layer,.bonus_type_emoji.bonus_unlocked .bonus__layer {
    background: #d1d1d1;
    opacity: 1
}

.bonus_unexpanded.bonus.bonus_type_custom .bonus__wrapper {
    background-size: cover
}

.bonus_unexpanded.bonus.bonus_type_icon .bonus__wrapper {
    background-size: auto 1.812rem
}

.bonus_unexpanded.bonus {
    margin-right: .375rem;
    background: #dbdada;
    border-radius: .5rem
}

.bonus_unexpanded.bonus .bonus__text {
    display: none
}

.bonus_unexpanded.bonus .bonus__emoji {
    width: 1.75rem;
    height: 1.75rem;
    margin: 0 auto
}

.bonus_unexpanded.bonus .bonus__lock {
    display: none
}

.bonus_unexpanded.bonus .bonus__lock-icon {
    height: .9rem;
    width: .9rem;
    border-width: .125rem;
    background-color: #dbdada;
    position: absolute;
    right: .3125rem;
    top: -.0625rem
}

.bonus_unexpanded.bonus .bonus__lock-icon svg {
    fill: var(--grey-blue);
    height: .375rem!important
}

.bonus_unexpanded.bonus .bonus__wrapper {
    border-radius: .5rem;
    -webkit-box-shadow: none;
    box-shadow: none;
    background-size: 1.938rem 2.094rem;
    background-position: 50%;
    background-color: #dbdada
}

.bonus_unexpanded.bonus .bonus__layer {
    display: none
}

.bonus_expanded.bonus {
    background: #dbdada;
    background-image: none;
    height: var(--bonus-height);
    margin-bottom: .8125rem;
    border-radius: .5625rem;
    -webkit-box-shadow: none;
    box-shadow: none
}

.bonus_expanded.bonus.bonus_type_icon .bonus__wrapper {
    background-size: auto 2.375rem;
    background-position: .5rem 50%
}

@media (max-width: 360px) {
    .bonus_expanded.bonus.bonus_type_icon .bonus__wrapper {
        background-size:auto 2.188rem
    }
}

.bonus_expanded.bonus.bonus_type_icon .bonus__layer {
    display: none
}

.bonus_expanded.bonus.bonus_type_icon .bonus__text {
    color: var(--dark-grey-blue);
    text-shadow: none
}

@media (max-width: 819px) {
    .bonus_expanded.bonus.bonus_type_emoji .bonus__emoji {
        height:2rem;
        width: 2rem
    }
}

@media (max-width: 360px) {
    .bonus_expanded.bonus.bonus_type_emoji .bonus__emoji {
        margin:1.375rem .5rem
    }
}

.bonus_expanded.bonus__locked {
    cursor: pointer
}

.bonus_expanded.bonus:not(.bonus_type_icon) .bonus_expanded.bonus:not(.bonus_type_custom) .bonus__layer {
    display: none
}

.bonus_expanded.bonus:not(.bonus_type_icon) .bonus_expanded.bonus:not(.bonus_type_custom) .bonus__text {
    color: var(--grey-blue);
    text-shadow: none
}

.bonus_expanded.bonus:not(.bonus_type_icon) .bonus_expanded.bonus:not(.bonus_type_custom) .bonus__wrapper {
    background-size: 3.438rem 3.688rem;
    background-position-y: .25rem
}

@media (max-width: 360px) {
    .bonus_expanded.bonus:not(.bonus_type_icon) .bonus_expanded.bonus:not(.bonus_type_custom).bonus_single .bonus__wrapper {
        background-size:2.188rem 2.188rem;
        background-position: .4375rem .375rem
    }
}

.bonus_expanded.bonus:not(.bonus_type_icon) .bonus_expanded.bonus:not(.bonus_type_custom).bonus_single.bonus_type_emoji img {
    height: 1.875rem;
    width: 1.875rem
}

.bonus_expanded.bonus:not(.bonus_type_icon) .bonus_expanded.bonus:not(.bonus_type_custom).bonus_single.bonus_type_emoji .bonus__emoji {
    margin-left: .125rem
}

.bonus_expanded.bonus:not(.bonus_type_icon) .bonus_expanded.bonus:not(.bonus_type_custom).bonus_type_emoji img {
    height: 2.5rem;
    width: 2.5rem
}

.bonus_expanded.bonus:not(.bonus_type_icon) .bonus_expanded.bonus:not(.bonus_type_custom).bonus_type_emoji .bonus__emoji {
    margin-left: .4062rem
}

.bonus_expanded.bonus:has(.bonus_type_custom) .bonus__wrapper {
    background-size: cover;
    background-position: 50%
}

.bonus_expanded.bonus:has(.bonus_type_custom) .bonus__wrapper.bonus__text {
    color: var(--white)
}

.bonus_expanded.bonus .bonus__lock {
    display: none
}

.bonus_expanded.bonus .bonus__wrapper {
    -webkit-box-shadow: none;
    box-shadow: none;
    border-radius: .5rem
}

.bonus_expanded.bonus .bonus__lock-icon {
    height: 1.125rem!important;
    width: 1.125rem!important;
    right: 0;
    top: -.125rem;
    border-width: .125rem;
    background-color: #dbdada
}

.bonus_expanded.bonus .bonus__lock-icon svg {
    fill: var(--grey-blue);
    height: .5rem!important
}

.bonus_expanded.bonus .bonus__text {
    font-style: normal;
    font-weight: 600;
    font-size: .6875rem;
    line-height: .8125rem;
    text-align: right;
    margin-right: .6875rem;
    text-overflow: ellipsis;
    right: 0;
    left: auto
}

.bonuses {
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    width: 100%;
    -webkit-box-align: end;
    -ms-flex-align: end;
    align-items: flex-end;
    -webkit-box-orient: vertical;
    -webkit-box-direction: normal;
    -ms-flex-direction: column;
    flex-direction: column;
    -webkit-box-pack: end;
    -ms-flex-pack: end;
    justify-content: flex-end
}

.bonuses_direction_row {
    -webkit-box-orient: horizontal;
    -webkit-box-direction: normal;
    -ms-flex-direction: row;
    flex-direction: row;
    -webkit-box-pack: start;
    -ms-flex-pack: start;
    justify-content: flex-start;
    -webkit-box-align: start;
    -ms-flex-align: start;
    align-items: flex-start
}

.bonuses_direction_row .bonuses__bonus {
    width: var(--bonus-width)
}

.bonuses_direction_row .bonuses__bonus:first-child:not(:last-child) {
    margin-right: 1rem
}

@media (max-width: 465px) {
    .bonuses_direction_row .bonuses__bonus:first-child:not(:last-child) {
        margin-bottom:1rem
    }
}

@media (max-width: 375px) {
    .bonuses_direction_row.bonuses_count_2 .bonus_type_emoji .bonus__emoji {
        width:2.375rem;
        height: 2.375rem;
        margin: .625rem
    }
}

@media (max-width: 767px) {
    .bonuses_direction_row-mobile {
        grid-gap:.625rem;
        grid-template-columns: 1fr 1fr;
        grid-template-rows: var(--bonus-height)
    }
}

@media (max-width: 819px) {
    .bonuses_count_1 .bonus_expanded.bonus_type_emoji .bonus__text {
        text-align:left
    }

    .bonuses_count_1 .bonus_expanded.bonus_type_emoji .bonus__emoji {
        margin: 1.375rem .625rem
    }
}

@media (max-width: 360px) {
    .bonuses_count_1 .bonus_expanded.bonus_type_emoji .bonus__emoji {
        margin:1.375rem .25rem
    }
}

.bonuses_unexpanded.bonuses_direction_row .bonuses__bonus:first-child:not(:last-child) {
    margin-right: .35rem
}

.discount-badge {
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-align: center;
    -ms-flex-align: center;
    align-items: center;
    position: relative;
    white-space: nowrap;
    width: -webkit-min-content;
    width: -moz-min-content;
    width: min-content
}

.discount-badge:after {
    content: "";
    position: absolute;
    background-color: var(--quiz-primary);
    width: 1rem;
    height: 1rem;
    right: -.375rem;
    border-radius: .25rem;
    -webkit-transform: rotate(45deg);
    transform: rotate(45deg);
    top: .125rem;
    z-index: 1
}

.discount-badge:before {
    content: "";
    position: absolute;
    width: .3125rem;
    height: .3125rem;
    border-radius: 50%;
    background-color: #fff;
    border: .0625rem solid var(--color-lighten);
    z-index: 3;
    right: 0
}

.discount-badge_without-title .discount-badge__value {
    border-radius: .25rem;
    margin-left: 0
}

.discount-badge__title {
    font-size: .6875rem;
    font-weight: 500;
    border-radius: .25rem;
    background-color: var(--color-lighten);
    padding: .25rem .9375rem .25rem .3125rem;
    max-width: 12.5rem;
    white-space: nowrap;
    overflow: hidden;
    word-break: break-word;
    text-overflow: ellipsis
}

.discount-badge__title,.discount-badge__value {
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    font-style: normal;
    font-stretch: normal;
    line-height: .75rem;
    height: 1.25rem;
    letter-spacing: normal;
    text-align: left;
    color: #222;
    border: .0625rem solid var(--color-lighten10);
}

.discount-badge__value {
    position: relative;
    margin-left: -.625rem;
    vertical-align: middle;
    font-size: .75rem;
    font-weight: 700;
    background-color: var(--color-lighten10);
    padding: .0625rem .75rem 0 .5rem;
    -webkit-box-align: center;
    -ms-flex-align: center;
    align-items: center;
    border-radius: 0 .25rem .25rem 0;
    z-index: 2
}

.discount-badge__value svg {
    width: .875rem;
    fill: #222
}

.discount-badge_has-effect:after {
    right: -.5rem
}

.discount-badge_has-effect .discount-badge__title {
    background-color: #f7cd47;
    font-weight: 600;
    line-height: .625rem;
    font-size: .75rem;
    border-radius: .25rem 0 0 .25rem;
    padding-right: .5rem;
    padding-left: .25rem
}

.discount-badge_has-effect .discount-badge__value {
    padding-left: 0;
    border-radius: 0 .25rem .25rem 0;
    margin-left: .25rem
}

.discount-badge_has-effect .discount-badge__arrows {
    width: 1.188rem;
    height: 1.25rem;
    background-color: #f7cd47;
    position: relative;
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-align: center;
    -ms-flex-align: center;
    align-items: center;
    z-index: 2
}

.discount-badge_has-effect .discount-badge__arrows svg {
    width: .6875rem
}

.discount-badge_has-effect .discount-badge__union {
    height: 1.25rem;
    margin-left: -1px;
    margin-right: -1px
}

@media (max-width: 767px) {
    .thank-page__col_left {
        padding: 0;
    }

    .bonuses_direction_row {
        flex-direction: column;
    }

    .bonuses_direction_row .bonuses__bonus:first-child:not(:last-child) {
        margin-right: 0;
        margin-bottom: 1rem;
    }

    .bonuses_direction_row .bonuses__bonus {
        width: 100%;
        max-width: 21.75rem;
    }
}

</style>
