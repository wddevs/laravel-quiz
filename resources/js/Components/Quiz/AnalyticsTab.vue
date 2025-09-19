<!-- Components/Quiz/AnalyticsTab.vue -->
<script setup>
import { computed } from 'vue'
const props = defineProps({ modelValue: { type: Object, default: () => ({}) } })
const emit = defineEmits(['update:modelValue'])

const model = computed({
    get: () => props.modelValue,
    set: v => emit('update:modelValue', v || {})
})

function ensure(path, def) {
    const seg = path.split('.')
    let cur = model.value || {}
    seg.slice(0, -1).forEach(k => (cur = (cur[k] ||= {})))
    if (model.value == null) emit('update:modelValue', (model.value = {}))
    if (seg.length) {
        const last = seg[seg.length - 1]
        if (cur[last] == null) cur[last] = def
    }
}

ensure('enabled', false)
ensure('scripts.head', '')
ensure('scripts.bodyEnd', '')
ensure('providers.ga4.enabled', false)
ensure('providers.ga4.measurementId', '')
ensure('providers.fb.enabled', false)
ensure('providers.fb.pixelId', '')
ensure('providers.tt.enabled', false)
ensure('providers.tt.pixelId', '')
ensure('providers.gtm.enabled', false)
ensure('providers.gtm.containerId', '')
ensure('events', { impression:'quiz_impression', start:'quiz_start', step:'quiz_step_view', leadView:'lead_view', leadSubmit:'lead_submit' })
</script>

<template>
    <div class="space-y-6">
        <label class="flex items-center gap-2">
            <input type="checkbox" v-model="model.enabled" />
            <span>Enable Analytics</span>
        </label>

        <fieldset class="border p-4 rounded">
            <legend class="text-sm font-semibold">Quick Providers</legend>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="flex items-center gap-2"><input type="checkbox" v-model="model.providers.ga4.enabled" /> GA4</label>
                    <input class="border rounded px-3 py-2 w-full" placeholder="G-XXXXXXX" v-model="model.providers.ga4.measurementId" />
                </div>
                <div>
                    <label class="flex items-center gap-2"><input type="checkbox" v-model="model.providers.fb.enabled" /> Facebook Pixel</label>
                    <input class="border rounded px-3 py-2 w-full" placeholder="1234567890" v-model="model.providers.fb.pixelId" />
                </div>
                <div>
                    <label class="flex items-center gap-2"><input type="checkbox" v-model="model.providers.tt.enabled" /> TikTok Pixel</label>
                    <input class="border rounded px-3 py-2 w-full" placeholder="CBADEF..." v-model="model.providers.tt.pixelId" />
                </div>
                <div>
                    <label class="flex items-center gap-2"><input type="checkbox" v-model="model.providers.gtm.enabled" /> Google Tag Manager</label>
                    <input class="border rounded px-3 py-2 w-full" placeholder="GTM-XXXXXX" v-model="model.providers.gtm.containerId" />
                </div>
            </div>
        </fieldset>

        <fieldset class="border p-4 rounded">
            <legend class="text-sm font-semibold">Raw scripts (optional)</legend>
            <div>
                <div class="text-sm font-medium mb-1">Head</div>
                <textarea class="border rounded px-3 py-2 w-full" rows="4" v-model="model.scripts.head"
                          placeholder="Будь-які <script> для вставки в <head>"></textarea>
            </div>
            <div class="mt-3">
                <div class="text-sm font-medium mb-1">End of Body</div>
                <textarea class="border rounded px-3 py-2 w-full" rows="4" v-model="model.scripts.bodyEnd"
                          placeholder="Скрипти/пікселі наприкінці <body>"></textarea>
            </div>
        </fieldset>

        <fieldset class="border p-4 rounded">
            <legend class="text-sm font-semibold">Event names</legend>
            <div class="grid sm:grid-cols-2 gap-4">
                <label>Impression <input class="border rounded px-3 py-2 w-full" v-model="model.events.impression" /></label>
                <label>Quiz Start <input class="border rounded px-3 py-2 w-full" v-model="model.events.start" /></label>
                <label>Step View <input class="border rounded px-3 py-2 w-full" v-model="model.events.step" /></label>
                <label>Lead View <input class="border rounded px-3 py-2 w-full" v-model="model.events.leadView" /></label>
                <label>Lead Submit <input class="border rounded px-3 py-2 w-full" v-model="model.events.leadSubmit" /></label>
            </div>
        </fieldset>
    </div>
</template>
