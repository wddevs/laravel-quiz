<script setup>
import { Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
const props = defineProps({ lead: Object })

const contact = props.lead?.data?.contact

const lead = props.lead.data

function blockIp() {
    if (!props.lead.data.ip) return alert('No IP detected')
    router.post(route('leads.blockIp', props.lead.data.uuid), {}, { preserveScroll: true })
}
</script>

<template>
    <AuthenticatedLayout title="Управління квізами">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Заявка від {{ contact.name }} ({{ contact.phone }}) - IP: {{ props.lead.data.ip || 'N/A' }}
            </h2>
        </template>
        <div class="max-w-4xl mx-auto py-6 space-y-6">
            <div class="flex items-start justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-800">Lead #{{ lead.id }}</h1>
                    <div class="text-gray-800">{{ lead.created_human }} · {{ lead.quiz?.title || lead.quiz?.domain }}</div>
                </div>
                <div class="flex gap-2">
                    <span class="px-2 py-1 rounded bg-slate-700 text-slate-200 text-sm">{{ lead.status }}</span>
                    <Link as="button" method="delete" :href="route('leads.destroy', lead.uuid)"
                          class="px-3 py-2 rounded bg-red-50 text-red-600">🗑 Delete</Link>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-6">
                <!-- Contacts -->
                <div class="bg-white rounded-xl p-4">
                    <h2 class="text-lg font-semibold mb-2">Contacts</h2>
                    <div class="text-sm">
                        <div><b>Name:</b> {{ lead.contact?.name || '—' }}</div>
                        <div><b>Phone:</b> {{ lead.contact?.phone || '—' }}</div>
                        <div><b>Email:</b> {{ lead.contact?.email || '—' }}</div>
                        <div><b>Text:</b> {{ lead.contact?.text || '—' }}</div>
                    </div>
                </div>

                <!-- Info -->
                <div class="bg-white rounded-xl p-4">
                    <h2 class="text-lg font-semibold mb-2">Additional Information</h2>
                    <div class="text-sm space-y-1">
                        <div><b>Source:</b> <a v-if="lead.source_url" :href="lead.source_url" class="text-blue-600 underline" target="_blank">{{ lead.source_url }}</a><span v-else>—</span></div>
                        <div><b>Referrer:</b> <a v-if="lead.referrer" :href="lead.referrer" class="text-blue-600 underline" target="_blank">{{ lead.referrer }}</a><span v-else>—</span></div>
                        <div><b>IP address:</b> {{ lead.ip || '—' }}</div>
                        <div><b>Location:</b> {{ lead.location?.country || '—' }}<span v-if="lead.location?.city">, {{ lead.location.city }}</span></div>
                        <div><b>Discount:</b> <span v-if="lead.discount_percent != null">{{ lead.discount_percent }}%</span><span v-else>—</span></div>
                        <div><b>Cookies:</b> <pre class="bg-slate-50 rounded p-2 overflow-auto max-h-40">{{ JSON.stringify(lead.extra?.cookies || {}, null, 2) }}</pre></div>
                    </div>
                    <div class="mt-3">
                        <button @click="blockIp" class="px-3 py-2 rounded bg-slate-900 text-white">🚫 Block IP</button>
                    </div>
                </div>

                <!-- Answers -->
                <div class="md:col-span-2 bg-white rounded-xl p-4">
                    <h2 class="text-lg font-semibold mb-2">Answers</h2>
                    <div class="space-y-2 text-sm">
                        <div v-for="(a,i) in lead.answers" :key="i" class="border rounded-lg p-3">
                            <div class="text-slate-500">{{ a.q }}</div>
                            <div class="font-medium text-slate-900">
                                <template v-if="Array.isArray(a.a)">{{ a.a.join(', ') }}</template>
                                <template v-else>{{ a.a }}</template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Results (якщо будуть) -->
                <div v-if="lead.result && Object.keys(lead.result).length" class="md:col-span-2 bg-white rounded-xl p-4">
                    <h2 class="text-lg font-semibold mb-2">Results</h2>
                    <pre class="text-sm bg-slate-50 rounded p-2 overflow-auto">{{ JSON.stringify(lead.result, null, 2) }}</pre>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
