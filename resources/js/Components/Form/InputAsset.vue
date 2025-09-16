<!-- resources/js/Components/InputAsset.vue -->
<script setup>
import axios from 'axios'
import { ref, computed } from 'vue'

const props = defineProps({
    label: { type: String, default: '' },
    endpoint: { type: String, default: '/user/assets' }, // POST
    // якщо треба зберігати об'єкт Asset, передай valueKey=null
    valueKey: { type: String, default: 'url' }, // або 'path'
    collection: { type: String, default: null }
})

const model = defineModel() // v-model

const uploading = ref(false)
const preview = computed(() => {
    if (!model.value) return null
    if (typeof model.value === 'string') return model.value
    if (model.value?.url) return model.value.url
    return null
})

async function onChange(e) {
    const file = e.target.files?.[0]
    if (!file) return
    const fd = new FormData()
    fd.append('files[]', file)
    if (props.collection) fd.append('collection', props.collection)

    uploading.value = true
    try {
        const { data } = await axios.post(props.endpoint, fd, {
            headers: { 'Content-Type': 'multipart/form-data', 'Accept':'application/json' }
        })
        const asset = Array.isArray(data.data) ? data.data[0] : data.data
        model.value = props.valueKey ? asset[props.valueKey] : asset
    } finally {
        uploading.value = false
    }
}

function clearFile() { model.value = null }
</script>

<template>
    <div class="rounded border p-2">
        <label class="mb-2 block text-sm">{{ label }}</label>
        <div class="flex items-center gap-2">
            <input type="file" accept="image/*" @change="onChange" />
            <span v-if="uploading" class="text-xs text-gray-500">Uploading…</span>
            <img v-if="preview" :src="preview" class="h-10 rounded" />
            <button v-if="model" type="button" class="text-red-600 text-sm" @click="clearFile">Remove</button>
        </div>
    </div>
</template>
