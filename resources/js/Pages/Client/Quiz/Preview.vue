<script setup>
const props = defineProps({
    quiz: {
        type: Object,
        required: true
    }
})
</script>

<template>
    <div class="p-6">
        <h1 class="text-2xl font-semibold mb-2">{{ props.quiz.title }}</h1>
        <p class="text-slate-600 mb-6">{{ props.quiz.description }}</p>

        <div
            v-for="q in props.quiz.questions || []"
            :key="q.id"
            class="border rounded p-3 mb-3"
        >
            <div class="flex items-center gap-3">
                <div class="text-slate-500">#{{ q.order }}</div>
                <h2 class="font-medium">{{ q.title }}</h2>
            </div>

            <img
                v-if="q.image_path"
                :src="'/storage/' + q.image_path"
                class="h-24 mt-2 rounded border"
            />

            <ul
                v-if="['radio', 'checkbox'].includes(q.type)"
                class="mt-2 list-disc pl-6"
            >
                <li v-for="a in q.answers" :key="a.id">
                    <span>{{ a.label }} <span v-if="a.value">({{ a.value }})</span></span>
                    <img
                        v-if="a.image_path"
                        :src="'/storage/' + a.image_path"
                        class="h-10 inline-block ml-2 border rounded"
                    />
                </li>
            </ul>

            <div v-else class="text-sm text-slate-500 mt-2">
                Type: text (user inputs free text)
            </div>
        </div>
    </div>
</template>
