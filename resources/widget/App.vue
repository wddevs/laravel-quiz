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
        const res = await fetch(`http://laravel-quiz.test/api/v1/quizzes/${uuid.value}/config`, {
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
  echo {{props}}
</template>

