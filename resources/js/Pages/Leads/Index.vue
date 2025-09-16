<script setup>
import { computed } from 'vue'
import { router, Link, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const props = defineProps({
    filters: Object,
    stats: Object,
    quizzes: Array,
    leads: Object, // Inertia paginator
    rawLeads: Object,
})

function apply(filters) {
    router.get(route('leads.index'), { ...props.filters, ...filters }, { preserveState: true, replace: true })
}

const timeOptions = [
    { value: 'all', label: 'All time' },
    { value: 'today', label: 'Today' },
    { value: '7d', label: 'Last 7 days' },
    { value: '30d', label: 'Last 30 days' },
]

const statusOptions = [
    { value: 'all', label: 'All leads' },
    { value: 'new', label: 'New' },
    { value: 'updated', label: 'Updates' },
    { value: 'error', label: 'Error' },
    { value: 'viewed', label: 'Viewed' },
]
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Ліди
            </h2>
        </template>
        <div class="max-w-6xl mx-auto py-6 space-y-6">
            <div class="flex items-center justify-between">
                <!-- Filters -->
                <div class="flex flex-wrap gap-3 items-center">
                    <select class="bg-grey-800 text-gray-800 rounded px-3 py-2" :value="filters.status"
                            @change="apply({ status: $event.target.value })">
                        <option v-for="o in statusOptions" :key="o.value" :value="o.value">{{ o.label }}</option>
                    </select>

                    <select class="bg-grey-800 text-gray-800 rounded px-3 py-2" :value="filters.quiz_id || ''"
                            @change="apply({ quiz_id: $event.target.value || null })">
                        <option value="">Quizzes (all)</option>
                        <option v-for="q in quizzes" :key="q.id" :value="q.id">{{ q.title || q.domain }}</option>
                    </select>

                    <input class="bg-grey-800 text-gray-800 rounded px-3 py-2" placeholder="City" :value="filters.city || ''"
                           @keyup.enter="apply({ city: $event.target.value })" />

                    <select class="bg-grey-800 text-gray-800 rounded px-3 py-2" :value="filters.time"
                            @change="apply({ time: $event.target.value })">
                        <option v-for="o in timeOptions" :key="o.value" :value="o.value">{{ o.label }}</option>
                    </select>
                </div>
                <div class="flex gap-3">
                    <span class="px-3 py-1 rounded-full bg-grey-700 text-gray-800 text-sm">Leads total: <b>{{ stats.total }}</b></span>
                    <span class="px-3 py-1 rounded-full bg-red-600/20 text-gray-800 text-sm">New: <b>{{ stats.new }}</b></span>
                </div>
            </div>

            <div class="space-y-3">
                <div v-for="(lead, idx) in leads.data" :key="lead.uuid" class="bg-white rounded-xl shadow flex items-center justify-between p-4">
                    <div class="flex items-center gap-4">
                        <span class="px-2 py-1 text-xs rounded-full" :class="lead.status === 'new' ? 'bg-pink-600 text-white' : 'bg-slate-200 text-slate-700'">
                            {{ lead.status }}
                        </span>
                        <div class="text-sm text-slate-600">
                            <div class="font-medium text-slate-900">#{{ lead.id }}</div>
                            <div>{{ lead.created_human }}</div>
                        </div>
                        <div class="text-sm">
                            <div class="text-slate-900">{{ lead.quiz?.title || lead.quiz?.domain }}</div>
                            <div class="text-slate-500">
                                {{ lead.location?.country || '' }} <span v-if="lead.location?.city">, {{ lead.location.city }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-6">
                        <div class="text-sm">
                            <div class="text-slate-900">{{ lead.contact?.name || '—' }}</div>
                            <div class="text-slate-500">{{ lead.contact?.phone || lead.contact?.email || lead.contact?.text || '—' }}</div>
                        </div>
                        <div class="flex items-center gap-2">
                            <Link :href="route('leads.show', lead.uuid)" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-pink-600 text-white">
                                👁️ View
                            </Link>
                            <Link as="button" method="delete" :href="route('leads.destroy', lead.uuid)"
                                  class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-red-50 text-red-600">
                                🗑 Delete
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
/* темний фон як на скрінах */
html,body,#app { background: #1b2033; }
</style>
