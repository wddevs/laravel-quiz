<template>
    <Link
        :href="href"
        :class="classes"
    >
        <slot />
    </Link>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    variant: {
        type: String,
        default: 'primary', // primary | secondary | ghost
    },
});

const classes = computed(() => {
    const base = "inline-flex gap-1 items-center rounded-md px-4 py-2 text-xs font-semibold uppercase tracking-widest transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2";

    const variants = {
        primary: "bg-gray-800 text-white border border-transparent hover:bg-gray-700 focus:bg-gray-700 focus:ring-indigo-500 active:bg-gray-900",
        secondary: "bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 focus:ring-indigo-500 active:bg-gray-200",
        ghost: "bg-transparent text-gray-800 border border-transparent hover:bg-gray-100 focus:ring-indigo-500 active:bg-gray-200",
        danger: "inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
    };

    return [base, variants[props.variant]];
});
</script>
