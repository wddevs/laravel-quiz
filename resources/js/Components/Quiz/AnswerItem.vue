<script setup>
const props = defineProps({ answer: { type: Object, required: true } })
const emit = defineEmits(['update:answer'])

async function onFile (e) {
    const file = e.target.files?.[0]
    if (!file) return
    const fd = new FormData()
    fd.append('file', file)
    const res = await fetch(route('admin.upload'), {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
        body: fd
    })
    const json = await res.json()
    props.answer.image_path = json.path
    emit('update:answer', props.answer)
}
function clearImg () {
    props.answer.image_path = null
    emit('update:answer', props.answer)
}
</script>

<template>
    <div class="grid md:grid-cols-2 gap-3">
        <label class="block">
            <div class="text-sm">Label</div>
            <input v-model="answer.label" class="border rounded px-3 py-2 w-full" />
        </label>
        <label class="block">
            <div class="text-sm">Value</div>
            <input v-model="answer.value" class="border rounded px-3 py-2 w-full" />
        </label>
    </div>

    <div class="grid gap-2 mt-1">
        <div class="text-sm">Answer image (optional)</div>
        <div class="flex items-center gap-3">
            <input type="file" accept="image/*" @change="onFile">
            <button v-if="answer.image_path" class="px-2 py-1 border rounded" @click="clearImg">Remove</button>
        </div>
        <img v-if="answer.image_path" :src="'/storage/'+answer.image_path" class="h-16 rounded border" />
    </div>
</template>
