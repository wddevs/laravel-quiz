<script setup>
import { reactive } from 'vue'
import QuestionItem from './QuestionItem.vue'

const props = defineProps({ questions: { type: Array, default: () => [] } })
const emit = defineEmits(['update:questions'])

function addQuestion () {
    const order = (props.questions.at(-1)?.order ?? 0) + 1
    props.questions.push({
        id: null, order, title: 'New question', type: 'radio',
        required: false, help_text: '', image_path: null, answers: []
    })
    emit('update:questions', props.questions)
}

function move (idx, dir) {
    const to = idx + dir
    if (to < 0 || to >= props.questions.length) return
    const tmp = props.questions[idx]
    props.questions[idx] = props.questions[to]
    props.questions[to] = tmp
    props.questions.forEach((q, i) => q.order = i + 1)
    emit('update:questions', props.questions)
}

function removeQuestion (idx) {
    props.questions.splice(idx, 1)
    props.questions.forEach((q, i) => q.order = i + 1)
    emit('update:questions', props.questions)
}
</script>

<template>
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold">Questions</h2>
            <button class="px-3 py-2 rounded bg-emerald-600 text-white" @click="addQuestion()">Add question</button>
        </div>

        <div class="space-y-3">
            <div v-for="(q, idx) in questions" :key="idx" class="border rounded p-3">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-slate-600">#{{ q.order }}</div>
                    <div class="flex gap-2">
                        <button class="px-2 py-1 border rounded" @click="move(idx,-1)">↑</button>
                        <button class="px-2 py-1 border rounded" @click="move(idx, 1)">↓</button>
                        <button class="px-2 py-1 border rounded text-rose-600" @click="removeQuestion(idx)">Delete</button>
                    </div>
                </div>

                <QuestionItem v-model:question="questions[idx]" />
            </div>
        </div>
    </div>
</template>
