<script setup>
import { reactive, computed, ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import InputError from "@/Components/InputError.vue";
import notify from '@/Plugins/notify'
import QuizBuilder from '@/Components/Quiz/QuizBuilder.vue'
import InputAsset from '@/Components/Form/InputAsset.vue'
import AnalyticsTab from "@/Components/Quiz/AnalyticsTab.vue";

const props = defineProps({ quiz: Object })

/** дефолтні налаштування під твої композери */
function defaultSettings() {
    return {
        order: ['start','questions','leadform','thanks'],

        theme:   {
            primary:'#000',
            bg:'#ffffff',
            text:'#000',
            title:'#000',
            font:'Inter',

            btnRadius:12,
            btnColor: "#000",
            btnTextColor: "#fff",

            inputRadius:10
        },
        assistant: {
            enabled: false,
            name:'Manager',
            title: '',
            avatar: ''
        },

        marketing: {
            discount: {
                type:'percent',
                effect:'increasing',
                value:10,
                title:'Discount for you!'
            },
            bonusesEnabled: false,
            bonusesTitle:'',
            bonuses:[]
        },

        start: {
            enabled: true,
            title:'',
            subtitle:'',
            buttonText: 'Start',
            bg:'',
            logo:''
        },

        leadform:{
            header: 'Lead form',
            content: '',
            buttonText: 'Send',
            sendOnFirstStep: true,
            motivationEnabled: false,
            motivationText: ''
        },

        thanks:  {
            enabled: true,
            header: 'Thank You!',
            content: '',
            socials:[
                {
                    name:'Instagram',
                    link:""
                },
                {
                    name:'Youtube',
                    link:''
                },
                {
                    name:'Tiktok',
                    link:''
                }
            ]
        },

        analytics: {
            enabled: false,
            providers: {
                ga4: { enabled: false, measurementId: '' },
                fb:  { enabled: false, pixelId: '' },
                tt:  { enabled: false, pixelId: '' },
                gtm: { enabled: false, containerId: '' },
            },
            scripts: {
                head:'',
                bodyEnd:''
            },
            events: {
                impression:'quiz_impression',
                start:'quiz_start',
                step:'quiz_step_view',
                leadView:'lead_view',
                leadSubmit:'lead_submit'
            }
        },
    }
}

const form = useForm({
    title: props.quiz.title ?? '',
    description: props.quiz.description ?? '',
    domain_allowlist: Array.isArray(props.quiz.domain_allowlist)
        ? props.quiz.domain_allowlist.join(', ')
        : (props.quiz.domain_allowlist ?? ''),
    is_active: props.quiz.is_active ?? true,
    settings: Object.assign(defaultSettings(), props.quiz.settings ?? {}),
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

/** Tabs */
const tabs = [
    { key:'general',   label:'General settings' },
    { key:'start',     label:'Start page' },
    { key:'questions', label:'Questions' },
    { key:'leadform',  label:'Lead form' },
    { key:'thanks',    label:'Thank You Page' },
    { key:'assistant', label:'Assistant' },
    { key:'marketing', label:'Marketing' },
    { key:'theme',     label:'Theme' },
    { key:'order',     label:'Flow / Order' },
    { key:'analytics',     label:'Analytics' },
]
const activeTab = ref('general')

const orderString = computed({
    get: () => (form.settings.order ?? []).join(', '),
    set: (val) => {
        form.settings.order = String(val)
            .split(',')
            .map(s => s.trim())
            .filter(Boolean)
            .filter((s, i, arr) => arr.indexOf(s) === i); // унікальні
    }
});

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


<template>
    <authenticated-layout>
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 py-12">
            <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                <h1 class="text-2xl font-semibold mb-6">{{ isEdit ? 'Edit Quiz' : 'New Quiz' }}</h1>

                <!-- Tabs header -->
                <div class="flex flex-wrap gap-2 mb-6">
                    <button
                        v-for="t in tabs" :key="t.key"
                        class="px-4 py-2 rounded-lg text-sm border transition"
                        :class="activeTab===t.key ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 hover:bg-gray-50'"
                        @click="activeTab=t.key"
                    >
                        {{ t.label }}
                    </button>
                </div>

                <!-- Start page -->
                <section v-if="activeTab==='general'" class="grid gap-4">
                    <div class="grid gap-4">
                        <label class="block">
                            <div class="text-sm font-medium">Title</div>
                            <input v-model="form.title" class="border rounded px-3 py-2 w-full"  />
                            <InputError class="mt-2" :message="form.errors.title" />
                        </label>

                        <label class="block">
                            <div class="text-sm font-medium">Description</div>
                            <textarea v-model="form.description" rows="3" class="border rounded px-3 py-2 w-full"></textarea>
                        </label>

                        <label class="block">
                            <div class="text-sm font-medium">Allowed Domains</div>
                            <textarea v-model="form.domain_allowlist" placeholder="example.com, sub.example.com, *.example.org" rows="3" class="border rounded px-3 py-2 w-full"></textarea>
                            <InputError class="mt-2" :message="form.errors.domain_allowlist" />
                        </label>

                        <label class="inline-flex items-center gap-2">
                            <input type="checkbox" v-model="form.is_active">
                            <span>Active</span>
                        </label>
                    </div>
                </section>

                <!-- Questions -->
                <section v-else-if="activeTab==='questions'">
                    <QuizBuilder v-model:questions="form.questions" />
                </section>

                <!-- Start page -->
                <section v-if="activeTab==='start'" class="grid gap-4">
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" v-model="form.settings.start.enabled">
                        <span>Enable start page</span>
                    </label>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <label class="block">
                            <div class="text-sm font-medium">Title</div>
                            <input v-model="form.settings.start.title" class="border rounded px-3 py-2 w-full" />
                        </label>
                        <label class="block">
                            <div class="text-sm font-medium">Subtitle</div>
                            <input v-model="form.settings.start.subtitle" class="border rounded px-3 py-2 w-full" />
                        </label>

                        <label class="block">
                            <div class="text-sm font-medium">Company</div>
                            <input v-model="form.settings.start.company" class="border rounded px-3 py-2 w-full" />
                        </label>
                        <label class="block">
                            <div class="text-sm font-medium">Phone</div>
                            <input v-model="form.settings.start.phone" class="border rounded px-3 py-2 w-full" />
                        </label>
                        <label class="block">
                            <div class="text-sm font-medium">Legal</div>
                            <input v-model="form.settings.start.legal" class="border rounded px-3 py-2 w-full" />
                        </label>

                        <label class="block">
                            <div class="text-sm font-medium">Button text</div>
                            <input v-model="form.settings.start.buttonText" class="border rounded px-3 py-2 w-full" />
                        </label>
                        <label class="block">
                            <div class="text-sm font-medium">Background image (path or URL)</div>
<!--                            <input v-model="form.settings.start.bg" class="border rounded px-3 py-2 w-full" />-->
                            <InputAsset label="Background" v-model="form.settings.start.bg" value-key="url" />
                        </label>
                        <label class="block">
                            <div class="text-sm font-medium">Logo (path or URL)</div>
                            <InputAsset label="Background" v-model="form.settings.start.logo" value-key="url" />
<!--                            <input v-model="form.settings.start.logo" class="border rounded px-3 py-2 w-full" />-->
                        </label>
                    </div>
                </section>

                <!-- Lead form -->
                <section v-else-if="activeTab==='leadform'" class="grid gap-4  mb-4">
                    <div class="grid sm:grid-cols-2 gap-4">
                        <label class="block">
                            <div class="text-sm font-medium">Header</div>
                            <input v-model="form.settings.leadform.header" class="border rounded px-3 py-2 w-full" />
                        </label>
                        <label class="block">
                            <div class="text-sm font-medium">Button text</div>
                            <input v-model="form.settings.leadform.buttonText" class="border rounded px-3 py-2 w-full" />
                        </label>
                    </div>

                    <label class="block">
                        <div class="text-sm font-medium">Content</div>
                        <textarea v-model="form.settings.leadform.content" rows="3" class="border rounded px-3 py-2 w-full"></textarea>
                    </label>

                    <div class="flex flex-wrap gap-6">
                        <label class="inline-flex items-center gap-2">
                            <input type="checkbox" v-model="form.settings.leadform.sendOnFirstStep">
                            <span>Send on first step</span>
                        </label>
                        <label class="inline-flex items-center gap-2">
                            <input type="checkbox" disabled v-model="form.settings.leadform.motivationEnabled">
                            <span>Show motivation text</span>
                        </label>
                    </div>

                    <label v-if="form.settings.leadform.motivationEnabled" class="block">
                        <div class="text-sm font-medium">Motivation text</div>
                        <input v-model="form.settings.leadform.motivationText" class="border rounded px-3 py-2 w-full" />
                    </label>
                </section>

                <!-- Thanks page -->
                <section v-else-if="activeTab==='thanks'" class="grid gap-4">
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" v-model="form.settings.thanks.enabled">
                        <span>Enable thank you page</span>
                    </label>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <label class="block">
                            <div class="text-sm font-medium">Header</div>
                            <input v-model="form.settings.thanks.header" class="border rounded px-3 py-2 w-full" />
                        </label>
                        <label class="block">
                            <div class="text-sm font-medium">Content</div>
                            <input v-model="form.settings.thanks.content" class="border rounded px-3 py-2 w-full" />
                        </label>
                    </div>

                    <div class="grid gap-2">
                        <div class="text-sm font-medium">Social links</div>
                        <div class="grid gap-3">
                            <div v-for="(s, i) in form.settings.thanks.socials" :key="i" class="grid sm:grid-cols-2 gap-2">
                                <input v-model="s.name" placeholder="Instagram" class="border rounded px-3 py-2 w-full" />
                                <input v-model="s.link" placeholder="https://..." class="border rounded px-3 py-2 w-full" />
                            </div>
                            <button type="button" class="text-sm text-indigo-600" @click="form.settings.thanks.socials.push({name:'',link:''})">
                                + Add social
                            </button>
                        </div>
                    </div>
                </section>

                <!-- Assistant -->
                <section v-else-if="activeTab==='assistant'" class="grid gap-4">
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" v-model="form.settings.assistant.enabled">
                        <span>Enable assistant</span>
                    </label>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <label class="block">
                            <div class="text-sm font-medium">Name</div>
                            <input v-model="form.settings.assistant.name" class="border rounded px-3 py-2 w-full" />
                        </label>
                        <label class="block">
                            <div class="text-sm font-medium">Title</div>
                            <input v-model="form.settings.assistant.title" class="border rounded px-3 py-2 w-full" />
                        </label>
                        <label class="block">
                            <div class="text-sm font-medium">Avatar (path or URL)</div>
<!--                            <input v-model="form.settings.assistant.avatar" class="border rounded px-3 py-2 w-full" />-->
                            <InputAsset label="Background" v-model="form.settings.assistant.avatar" value-key="url" />
                        </label>
                    </div>
                </section>

                <!-- Marketing -->
                <section v-else-if="activeTab==='marketing'" class="grid gap-4">
                    <div class="grid sm:grid-cols-5 gap-4">

                        <label class="block">
                            <div class="text-sm font-medium">Discount type</div>
                            <select disabled v-model="form.settings.marketing.discount.type" class="border rounded px-3 py-2 w-full">
                                <option value="percent">percent</option>
                                <option value="fixed">fixed</option>
                            </select>
                        </label>
                        <label class="block">
                            <div class="text-sm font-medium">Effect</div>
                            <select v-model="form.settings.marketing.discount.effect" class="border rounded px-3 py-2 w-full">
                                <option value="increasing">increasing</option>
                                <option value="constant">constant</option>
                            </select>
                        </label>
                        <label class="block">
                            <div class="text-sm font-medium">Value</div>
                            <input type="number" v-model.number="form.settings.marketing.discount.value" class="border rounded px-3 py-2 w-full" />
                        </label>
                        <label class="block">
                            <div class="text-sm font-medium">Title</div>
                            <input v-model="form.settings.marketing.discount.title" class="border rounded px-3 py-2 w-full" />
                        </label>
                        <label class="inline-flex items-center gap-2">
                            <input type="checkbox" v-model="form.settings.marketing.discount.enabled">
                            <span>Enable discount</span>
                        </label>
                    </div>

                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" v-model="form.settings.marketing.bonusesEnabled">
                        <span>Enable bonuses</span>
                    </label>

                    <div v-if="form.settings.marketing.bonusesEnabled" class="grid gap-3">
                        <label class="block">
                            <div class="text-sm font-medium">Bonuses title</div>
                            <input v-model="form.settings.marketing.bonusesTitle" class="border rounded px-3 py-2 w-full" />
                        </label>
                        <div class="grid gap-3">
                            <div v-for="(b,i) in form.settings.marketing.bonuses" :key="i" class="grid sm:grid-cols-3 items-start gap-2">
                                <input v-model="b.name" placeholder="Name" class="border rounded px-3 py-2" />
                                <input v-model="b.link" placeholder="Link" class="border rounded px-3 py-2" />

                                <select v-model="b.step" class="border rounded px-3 py-2">
                                    <option value="thanks">thanks</option>
                                    <option value="leadform">leadform</option>
                                </select>
<!--                                <input v-model="b.image" placeholder="/images/bonus.webp or URL" class="border rounded px-3 py-2" />-->
                                <div class="col-span-2">
                                    <InputAsset label="Background" v-model="b.image" value-key="url" />
                                </div>
                            </div>
                            <button type="button" class="text-sm text-indigo-600" @click="form.settings.marketing.bonuses.push({name:'',link:'',step:'thanks',image:''})">
                                + Add bonus
                            </button>
                        </div>
                    </div>
                </section>

                <!-- Theme -->
                <section v-else-if="activeTab==='theme'" class="grid sm:grid-cols-3 gap-4">
                    <label class="block">
                        <div class="text-sm font-medium">Primary</div>
                        <input type="color" v-model="form.settings.theme.primary" class="w-16 h-10 p-0 border rounded" />
                    </label>
                    <label class="block">
                        <div class="text-sm font-medium">Background</div>
                        <input type="color" v-model="form.settings.theme.bg" class="w-16 h-10 p-0 border rounded" />
                    </label>
                    <label class="block">
                        <div class="text-sm font-medium">Text</div>
                        <input type="color" v-model="form.settings.theme.text" class="w-16 h-10 p-0 border rounded" />
                    </label>
                    <label class="block">
                        <div class="text-sm font-medium">Title</div>
                        <input type="color" v-model="form.settings.theme.title" class="w-16 h-10 p-0 border rounded" />
                    </label>
                    <label class="block">
                        <div class="text-sm font-medium">Font</div>
                        <input v-model="form.settings.theme.font" class="border rounded px-3 py-2 w-full" />
                    </label>
                    <div class="grid sm:grid-cols-3 gap-4 sm:col-span-3">


                    <label class="block">
                        <div class="text-sm font-medium">Button Text Color</div>
                        <input type="color" v-model.number="form.settings.theme.btnTextColor" class="w-16 h-10 p-0 border rounded" />
                    </label>
                    <label class="block">
                        <div class="text-sm font-medium">Button Background Color</div>
                        <input type="color" v-model.number="form.settings.theme.btnColor" class="w-16 h-10 p-0 border rounded" />
                    </label>
                    <label class="block">
                        <div class="text-sm font-medium">Button radius</div>
                        <input type="number" v-model.number="form.settings.theme.btnRadius" class="border rounded px-3 py-2 w-full" />
                    </label>
                    </div>

                    <label class="block">
                        <div class="text-sm font-medium">Input radius</div>
                        <input type="number" v-model.number="form.settings.theme.inputRadius" class="border rounded px-3 py-2 w-full" />
                    </label>
                </section>

                <!-- Flow / order -->
                <section v-else-if="activeTab==='order'" class="grid gap-3">
                    <div class="text-sm text-gray-600">
                        Порядок кроків (допустимі: start, questions, leadform, thanks)
                    </div>

                    <!-- тільки v-model, без :value і без @input -->
                    <input readonly disabled v-model="orderString" class="border rounded px-3 py-2 w-full" />

                    <div class="text-xs text-gray-500">
                        Напр.: <code>start, questions, leadform, thanks</code>
                    </div>
                </section>

                <section v-else-if="activeTab==='analytics'" class="grid gap-4">
                    <AnalyticsTab v-model="form.settings.analytics" />
                </section>

                <div class="flex gap-2 mt-8">
                    <button @click="submit" class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900" :disabled="form.processing">
                        {{ form.processing ? 'Saving…' : 'Save' }}
                    </button>
                </div>
            </div>
        </div>
    </authenticated-layout>
</template>
