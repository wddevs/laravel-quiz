<script setup>
import { ref, computed, onMounted } from 'vue'

const props = defineProps({
    uuid: { type: String, default: null }
})

const data = ref(null)
const loading = ref(true)
const error = ref(null)

// Якщо UUID не передали пропсом, витягнемо з query (?uuid=...)
const uuid = computed(() => {
    if (props.uuid) return props.uuid
    const u = new URL(window.location.href)
    return u.searchParams.get('uuid')
})

onMounted(async () => {
    if (!uuid.value) {
        error.value = 'UUID is missing'
        loading.value = false
        return
    }

    try {
        const res = await fetch(`/api/v1/quizzes/${uuid.value}/config`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            },
            credentials: 'same-origin' // якщо інший домен → 'include' і налаштувати CORS
        })

        if (!res.ok) {
            const text = await res.text()
            throw new Error(`HTTP ${res.status}: ${text.slice(0, 200)}`)
        }

        data.value = await res.json()
    } catch (e) {
        console.error(e)
        error.value = e?.message || 'Failed to load quiz config'
    } finally {
        loading.value = false
    }
})
</script>

<template>
    <div class="p-10 bg-white">
        <div v-if="loading">Loading…</div>
        <div v-else-if="error" class="text-red-600">Error: {{ error }}</div>
        <template v-else>
            <h1 class="text-2xl font-semibold mb-2">{{ data.title }}</h1>
            <p class="text-slate-600 mb-6">{{ data.description }}</p>

            <div v-for="s in data.steps" :key="s.id" class="border rounded p-3 mb-3">
                <div class="flex items-center gap-3">
                    <div class="text-slate-500">#{{ s.order }}</div>
                    <h2 class="font-medium">{{ s.title }}</h2>
                </div>

                <img v-if="s.image" :src="s.image" class="h-24 mt-2 rounded border" />

                <ul v-if="['radio','checkbox'].includes(s.type)" class="mt-2 list-disc pl-6">
                    <li v-for="a in s.answers" :key="a.id">
                        <span>{{ a.label }}<span v-if="a.value"> ({{ a.value }})</span></span>
                        <img v-if="a.image" :src="a.image" class="h-10 inline-block ml-2 border rounded" />
                    </li>
                </ul>

                <div v-else class="text-sm text-slate-500 mt-2">
                    Type: text (user inputs free text)
                </div>
            </div>
        </template>
    </div>
</template>
