<script setup>
import { usePage } from '@inertiajs/vue3'

const props = defineProps({ path: String })
const emit = defineEmits(['update:path'])

const page = usePage()
const csrf = page.props.csrf_token

console.log(csrf)


async function onFile(e) {
    const file = e.target.files?.[0]
    if (!file) return
    const fd = new FormData()
    fd.append('file', file)
    const res = await fetch(route('admin.upload'), {
        method: 'POST',
        // headers: { 'X-CSRF-TOKEN': csrf  },
        body: fd
    })
    const json = await res.json()
    emit('update:path', json.path)
}
function clear() { emit('update:path', null) }
</script>

<template>
    <div class="grid gap-2">
        <div class="text-sm">Question image (optional)</div>
        <div class="flex items-center gap-3">
            <input type="file" accept="image/*" @change="onFile" class="block">
            <button v-if="props.path" class="px-2 py-1 border rounded" @click="clear">Remove</button>
        </div>
        <img v-if="props.path" :src="'/storage/'+props.path" alt="" class="h-20 rounded border">
    </div>
</template>
