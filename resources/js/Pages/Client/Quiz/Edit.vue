<template>
    <authenticated-layout>
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 py-12">
            <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                <h1 class="text-2xl font-semibold mb-6">{{ isEdit ? 'Edit Quiz' : 'New Quiz' }}</h1>

                <div class="grid gap-4 mb-4">
                    <label class="block">
                        <div class="text-sm font-medium">Title</div>
                        <input v-model="form.title" class="border rounded px-3 py-2 w-full" />
                        <InputError class="mt-2" :message="form.errors.title" />
                    </label>

                    <label class="block">
                        <div class="text-sm font-medium">Description</div>
                        <textarea v-model="form.description" rows="3" class="border rounded px-3 py-2 w-full"></textarea>
                    </label>

                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" v-model="form.is_active">
                        <span>Active</span>
                    </label>
                </div>

                <QuizBuilder v-model:questions="form.questions" />

                <div class="flex gap-2">
                    <button @click="submit" class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900" :disabled="form.processing">
                        {{ form.processing ? 'Saving…' : 'Save' }}
                    </button>
                </div>
            </div>
        </div>
    </authenticated-layout>
</template>

<script setup>
import { reactive, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import InputError from "@/Components/InputError.vue";
import notify from '@/Plugins/notify'
import QuizBuilder from '@/Components/Quiz/QuizBuilder.vue'



const props = defineProps({ quiz: Object })

const form = useForm({
    title: props.quiz.title ?? '',
    description: props.quiz.description ?? '',
    is_active: props.quiz.is_active ?? true,
    settings: props.quiz.settings ?? {},
    questions: (props.quiz.questions ?? []).map(q => ({
        id: q.id ?? null,
        order: q.order ?? 1,
        title: q.title ?? '',
        type: q.type ?? 'radio',
        required: !!q.required,
        help_text: q.help_text ?? '',
        image_path: q.image_path ?? null,
        answers: (q.answers ?? []).map(a => ({
            id: a.id ?? null,
            order: a.order ?? 1,
            label: a.label ?? '',
            value: a.value ?? '',
            image_path: a.image_path ?? null,
        })),
    })),
})

const isEdit = computed(() => !!props.quiz.id)

function submit () {
    if (isEdit.value) {
        form.put(
            route('quizzes.update', props.quiz.id), {
                onSuccess: () => notify('Quiz updated successfully'),
            }
        )
    } else {
        form.post(route('quizzes.store'), {
            onSuccess: () => notify('Quiz created successfully'),
        })
    }
}
</script>
