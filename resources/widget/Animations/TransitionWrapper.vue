<template>
    <Transition
        :name="transitionName"
        :mode="mode"
        @before-enter="onBeforeEnter"
        @enter="onEnter"
        @leave="onLeave"
        :appear="true"
    >
        <slot />
    </Transition>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
    transitionName: {
        type: String,
        default: 'fade'
    },
    mode: {
        type: String,
        default: 'out-in'
    }
})

const onBeforeEnter = (el) => {
    el.style.opacity = '0'
    el.style.transform = 'translateY(20px)'
}

const onEnter = (el, done) => {
    el.offsetHeight // trigger reflow
    el.style.transition = 'all 1s ease'
    el.style.opacity = '1'
    el.style.transform = 'translateY(0)'

    setTimeout(done, 300)
}

const onLeave = (el, done) => {
    el.style.transition = 'all 1s ease'
    el.style.opacity = '0'
    el.style.transform = 'translateY(-20px)'

    setTimeout(done, 300)
}
</script>

<style scoped>
/* Додаткові анімації */
.fade-enter-active,
.fade-leave-active {
    transition: all 1s ease;
}

.fade-enter-from {
    opacity: 0;
    transform: translateY(20px);
}

.fade-leave-to {
    opacity: 0;
    transform: translateY(-20px);
}

.slide-enter-active,
.slide-leave-active {
    transition: all 1s ease;
}

.slide-enter-from {
    opacity: 0;
    transform: translateX(30px);
}

.slide-leave-to {
    opacity: 0;
    transform: translateX(-30px);
}

.scale-enter-active,
.scale-leave-active {
    transition: all 1s ease;
}

.scale-enter-from {
    opacity: 0;
    transform: scale(0.9);
}

.scale-leave-to {
    opacity: 0;
    transform: scale(1.1);
}
</style>
