<template>
    <AuthenticatedLayout title="Управління квізами">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Управління квізами
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <div v-if="props.quizzes.data.length === 0" class="text-center py-8 space-y-2">
                        <p class="text-gray-500 text-lg">Немає створених квізів</p>
                        <PrimaryLink
                            :href="route('quizzes.create')"
                        >
                            Створити квіз
                        </PrimaryLink>
                    </div>

                    <div v-else class="grid gap-6">
                        <div
                            v-for="quiz in props.quizzes.data"
                            :key="quiz.id"
                            class="border rounded-lg p-6 hover:shadow-lg transition-shadow"
                        >
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <h4 class="text-xl font-semibold text-gray-900 mb-2">
                                        {{ quiz.title }}
                                    </h4>
                                    <p v-if="quiz.description" class="text-gray-600 mb-3">
                                        {{ quiz.description }}
                                    </p>

                                </div>

                                <div class="flex-1">

                                    <input
                                        :value="embedUrl(quiz)"
                                        class="w-full border rounded px-3 py-2 text-sm bg-gray-50"
                                        readonly
                                    />
                                    <button
                                        type="button"
                                        class="px-2 py-2 border rounded text-xs hover:bg-gray-50"
                                        @click="copy(embedUrl(quiz))"
                                        title="Скопіювати"
                                    >
                                        Copy
                                    </button>
                                    <PrimaryLink :href="embedUrl(quiz)" title="Відкрити" target="_blank" rel="noopener">
                                        Open
                                    </PrimaryLink>

                                </div>

                                <div class="flex space-x-2 ml-4">
                                    <PrimaryLink
                                        :href="route('quizzes.preview', quiz.id)"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607ZM10.5 7.5v6m3-3h-6" />
                                        </svg>

                                    </PrimaryLink>
                                    <PrimaryLink
                                        :href="route('quizzes.edit', quiz.id)"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4">
                                            <path d="M13.488 2.513a1.75 1.75 0 0 0-2.475 0L6.75 6.774a2.75 2.75 0 0 0-.596.892l-.848 2.047a.75.75 0 0 0 .98.98l2.047-.848a2.75 2.75 0 0 0 .892-.596l4.261-4.262a1.75 1.75 0 0 0 0-2.474Z" />
                                            <path d="M4.75 3.5c-.69 0-1.25.56-1.25 1.25v6.5c0 .69.56 1.25 1.25 1.25h6.5c.69 0 1.25-.56 1.25-1.25V9A.75.75 0 0 1 14 9v2.25A2.75 2.75 0 0 1 11.25 14h-6.5A2.75 2.75 0 0 1 2 11.25v-6.5A2.75 2.75 0 0 1 4.75 2H7a.75.75 0 0 1 0 1.5H4.75Z" />
                                        </svg>
                                    </PrimaryLink>
                                    <PrimaryButton
                                        @click="deleteQuiz(quiz.id)"
                                        variant="danger"
                                        :href="route('quizzes.destroy', quiz.id)"
                                    >
                                        <svg  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4">
                                            <path fill-rule="evenodd" d="M5 3.25V4H2.75a.75.75 0 0 0 0 1.5h.3l.815 8.15A1.5 1.5 0 0 0 5.357 15h5.285a1.5 1.5 0 0 0 1.493-1.35l.815-8.15h.3a.75.75 0 0 0 0-1.5H11v-.75A2.25 2.25 0 0 0 8.75 1h-1.5A2.25 2.25 0 0 0 5 3.25Zm2.25-.75a.75.75 0 0 0-.75.75V4h3v-.75a.75.75 0 0 0-.75-.75h-1.5ZM6.05 6a.75.75 0 0 1 .787.713l.275 5.5a.75.75 0 0 1-1.498.075l-.275-5.5A.75.75 0 0 1 6.05 6Zm3.9 0a.75.75 0 0 1 .712.787l-.275 5.5a.75.75 0 0 1-1.498-.075l.275-5.5a.75.75 0 0 1 .786-.711Z" clip-rule="evenodd" />
                                        </svg>
                                    </PrimaryButton>
                                </div>
                            </div>
                        </div>

                        <div class="flex  items-end">
                            <PrimaryLink
                                :href="route('quizzes.create')"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4">
                                    <path d="M8.75 3.75a.75.75 0 0 0-1.5 0v3.5h-3.5a.75.75 0 0 0 0 1.5h3.5v3.5a.75.75 0 0 0 1.5 0v-3.5h3.5a.75.75 0 0 0 0-1.5h-3.5v-3.5Z" />
                                </svg>
                            </PrimaryLink>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Link, router, usePage  } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryLink from "@/Components/PrimaryLink.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import notify from "@/Plugins/notify.js";

const page = usePage()
const appUrl = page.props.appUrl || window.location.origin

const props = defineProps({
    quizzes: Object
})

function embedUrl(quiz) {
    // припускаю, що в quiz є uuid
    return `${appUrl}/widget/${quiz.uuid}`
}

async function copy(text) {
    try {
        await navigator.clipboard.writeText(text)
        // можна додати toast
    } catch (e) {
        console.error(e)
    }
}

const deleteQuiz = (quizId) => {
    if (confirm('Ви впевнені, що хочете видалити цей квіз?')) {
        router.delete(route('quizzes.destroy', quizId), {
            onSuccess: () => {
                // Optionally handle success (e.g., show a notification)
                notify.danger('Quiz deleted successfully');
            },
            onError: (errors) => {
                // Optionally handle errors (e.g., show an error message)
                console.error(errors);
            }
        });
    }
};

console.log('77', props)

</script>
