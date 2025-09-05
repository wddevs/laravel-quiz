<script setup>
import AnswerItem from './AnswerItem.vue'
import ImageUpload from './ImageUpload.vue'
const props = defineProps({ question: { type: Object, required: true } })
const emit = defineEmits(['update:question'])

function addAnswer () {
    const order = (props.question.answers.at(-1)?.order ?? 0) + 1
    props.question.answers.push({
        id: null, order, label: 'Option', value: '', image_path: null
    })
    emit('update:question', props.question)
}

function removeAnswer (idx) {
    props.question.answers.splice(idx, 1)
    props.question.answers.forEach((a, i) => a.order = i + 1)
    emit('update:question', props.question)
}
</script>

<template>
    <div class="grid gap-3 mt-3">
        <label class="block">
            <div class="text-sm">Title</div>
            <input v-model="question.title" class="border rounded px-3 py-2 w-full" />
        </label>

        <div class="grid grid-cols-3 gap-3">
            <label class="block">
                <div class="text-sm">Type</div>
                <select v-model="question.type" class="border rounded px-3 py-2 w-full">
                    <option value="radio">Single choice</option>
                    <option value="checkbox">Multiple choice</option>
                    <option value="text">Text input</option>
                </select>
            </label>

            <label class="block">
                <div class="text-sm">Help text</div>
                <input v-model="question.help_text" class="border rounded px-3 py-2 w-full" />
            </label>

            <label class="inline-flex items-center gap-2 mt-6">
                <input type="checkbox" v-model="question.required" />
                <span>Required</span>
            </label>
        </div>

        <ImageUpload v-model:path="question.image_path" />

        <!-- для text-питань відповіді не обов'язкові, але дозволимо додавати (іконки/підказки) -->
        <div v-if="question.type !== 'text'" class="mt-2">
            <div class="flex items-center justify-between">
                <div class="font-medium">Answers</div>
                <button class="px-2 py-1 border rounded" @click="addAnswer()">Add answer</button>
            </div>

            <div class="mt-2 space-y-2">
                <div v-for="(a, i) in question.answers" :key="i" class="border rounded p-2">
                    <div class="flex items-center justify-between mb-2">
                        <div class="text-sm text-slate-600">#{{ a.order }}</div>
                        <button class="px-2 py-1 border rounded text-rose-600" @click="removeAnswer(i)">Remove</button>
                    </div>
                    <AnswerItem v-model:answer="question.answers[i]" />
                </div>
            </div>
        </div>
    </div>
</template>

<script>

</script>
