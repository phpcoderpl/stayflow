<script setup>
import { ref, reactive } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const { t } = useI18n();

const props = defineProps({
    settings: Object,
    properties: Array,
    calendarSyncs: Array,
    emailTemplates: Array,
});

const activeTab = ref('general');

const tabs = [
    { key: 'general', label: 'settings.tabs.general' },
    { key: 'integrations', label: 'settings.tabs.integrations' },
    { key: 'email', label: 'settings.tabs.email' },
    { key: 'seo', label: 'settings.tabs.seo' },
];

// General / SEO form
const generalForm = useForm({
    brand_name: props.settings.brand_name ?? '',
    brand_primary_color: props.settings.brand_primary_color ?? '#0ea5e9',
    deposit_percent: props.settings.deposit_percent ?? 0,
    admin_email: props.settings.admin_email ?? '',
    google_analytics_id: props.settings.google_analytics_id ?? '',
    pre_arrival_days: props.settings.pre_arrival_days ?? 1,
    post_stay_days: props.settings.post_stay_days ?? 1,
});

function saveGeneral() {
    generalForm.put('/admin/settings');
}

// Calendar sync
const syncForm = useForm({
    property_id: '',
    provider: 'ical',
    ical_url: '',
});

function addSync() {
    syncForm.post('/admin/settings/calendar-sync', {
        onSuccess: () => syncForm.reset(),
    });
}

function deleteSync(id) {
    if (confirm(t('settings.confirm_delete_sync'))) {
        router.delete('/admin/settings/calendar-sync/' + id);
    }
}

function copyExportUrl(token) {
    const url = window.location.origin + '/ical/' + token + '.ics';
    navigator.clipboard.writeText(url);
}

function exportUrl(token) {
    return window.location.origin + '/ical/' + token + '.ics';
}

// Email templates
const expandedTemplates = ref({});

function toggleTemplate(id) {
    expandedTemplates.value[id] = !expandedTemplates.value[id];
}

const templateForms = reactive({});
props.emailTemplates.forEach((tpl) => {
    templateForms[tpl.id] = useForm({
        is_active: tpl.is_active,
        subject_pl: tpl.subject_pl ?? '',
        subject_en: tpl.subject_en ?? '',
        body_pl: tpl.body_pl ?? '',
        body_en: tpl.body_en ?? '',
    });
});

function saveTemplate(id) {
    templateForms[id].put('/admin/settings/email-templates/' + id);
}
</script>

<template>
    <AdminLayout>
        <div class="p-4 sm:p-6 lg:p-8">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">{{ $t('settings.title') }}</h1>

            <!-- Tabs -->
            <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
                <nav class="-mb-px flex space-x-6 overflow-x-auto">
                    <button
                        v-for="tab in tabs"
                        :key="tab.key"
                        @click="activeTab = tab.key"
                        :class="[
                            'whitespace-nowrap pb-3 px-1 text-sm font-medium border-b-2 transition',
                            activeTab === tab.key
                                ? 'border-sky-500 text-sky-600 dark:text-sky-400'
                                : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'
                        ]"
                    >
                        {{ $t(tab.label) }}
                    </button>
                </nav>
            </div>

            <!-- General Tab -->
            <div v-show="activeTab === 'general'">
                <form @submit.prevent="saveGeneral" class="space-y-6 max-w-2xl">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('settings.brand_name') }}</label>
                        <input
                            v-model="generalForm.brand_name"
                            type="text"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition"
                        />
                        <p v-if="generalForm.errors.brand_name" class="mt-1 text-sm text-red-500">{{ generalForm.errors.brand_name }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('settings.primary_color') }}</label>
                        <div class="flex items-center gap-3">
                            <input
                                v-model="generalForm.brand_primary_color"
                                type="color"
                                class="h-10 w-16 rounded border border-gray-300 dark:border-gray-600 cursor-pointer"
                            />
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ generalForm.brand_primary_color }}</span>
                        </div>
                        <p v-if="generalForm.errors.brand_primary_color" class="mt-1 text-sm text-red-500">{{ generalForm.errors.brand_primary_color }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('settings.deposit_percent') }}</label>
                        <div class="relative max-w-xs">
                            <input
                                v-model.number="generalForm.deposit_percent"
                                type="number"
                                min="0"
                                max="100"
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 pr-10 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition"
                            />
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-gray-400">%</span>
                        </div>
                        <p v-if="generalForm.errors.deposit_percent" class="mt-1 text-sm text-red-500">{{ generalForm.errors.deposit_percent }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('settings.admin_email') }}</label>
                        <input
                            v-model="generalForm.admin_email"
                            type="email"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition"
                        />
                        <p v-if="generalForm.errors.admin_email" class="mt-1 text-sm text-red-500">{{ generalForm.errors.admin_email }}</p>
                    </div>

                    <div class="pt-4">
                        <button
                            type="submit"
                            :disabled="generalForm.processing"
                            class="inline-flex items-center px-5 py-2.5 rounded-lg bg-sky-500 hover:bg-sky-600 text-white text-sm font-medium transition disabled:opacity-50"
                        >
                            <svg v-if="generalForm.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                            </svg>
                            {{ $t('settings.save') }}
                        </button>
                        <span v-if="generalForm.recentlySuccessful" class="ml-3 text-sm text-green-600 dark:text-green-400">{{ $t('settings.saved') }}</span>
                    </div>
                </form>
            </div>

            <!-- Integrations Tab -->
            <div v-show="activeTab === 'integrations'">
                <div class="max-w-4xl space-y-8">
                    <!-- Existing syncs -->
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ $t('settings.ical_syncs') }}</h2>

                        <div v-if="calendarSyncs.length === 0" class="text-sm text-gray-500 dark:text-gray-400 py-4">
                            {{ $t('settings.no_syncs') }}
                        </div>

                        <div class="space-y-3">
                            <div
                                v-for="sync in calendarSyncs"
                                :key="sync.id"
                                class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4"
                            >
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div class="space-y-1 min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ sync.property?.name || $t('settings.unknown_property') }}</span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300">
                                                {{ sync.provider }}
                                            </span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                                                {{ sync.direction }}
                                            </span>
                                        </div>
                                        <p v-if="sync.ical_url" class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ sync.ical_url }}</p>
                                        <div v-if="sync.ical_export_token" class="flex items-center gap-2 mt-1">
                                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ exportUrl(sync.ical_export_token) }}</p>
                                            <button
                                                @click="copyExportUrl(sync.ical_export_token)"
                                                class="shrink-0 text-xs text-sky-600 dark:text-sky-400 hover:underline"
                                            >
                                                {{ $t('settings.copy_url') }}
                                            </button>
                                        </div>
                                        <p v-if="sync.last_synced_at" class="text-xs text-gray-400 dark:text-gray-500">
                                            {{ $t('settings.last_synced') }}: {{ sync.last_synced_at }}
                                        </p>
                                    </div>
                                    <button
                                        @click="deleteSync(sync.id)"
                                        class="shrink-0 p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Add new sync -->
                    <div>
                        <h3 class="text-md font-semibold text-gray-900 dark:text-white mb-3">{{ $t('settings.add_sync') }}</h3>
                        <form @submit.prevent="addSync" class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('settings.property') }}</label>
                                    <select
                                        v-model="syncForm.property_id"
                                        class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition"
                                    >
                                        <option value="">{{ $t('settings.select_property') }}</option>
                                        <option v-for="prop in properties" :key="prop.id" :value="prop.id">{{ prop.name }}</option>
                                    </select>
                                    <p v-if="syncForm.errors.property_id" class="mt-1 text-sm text-red-500">{{ syncForm.errors.property_id }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('settings.provider') }}</label>
                                    <select
                                        v-model="syncForm.provider"
                                        class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition"
                                    >
                                        <option value="ical">iCal</option>
                                        <option value="booking_com">Booking.com</option>
                                        <option value="google">Google</option>
                                    </select>
                                    <p v-if="syncForm.errors.provider" class="mt-1 text-sm text-red-500">{{ syncForm.errors.provider }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('settings.ical_url') }}</label>
                                    <input
                                        v-model="syncForm.ical_url"
                                        type="url"
                                        placeholder="https://..."
                                        class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition"
                                    />
                                    <p v-if="syncForm.errors.ical_url" class="mt-1 text-sm text-red-500">{{ syncForm.errors.ical_url }}</p>
                                </div>
                            </div>
                            <button
                                type="submit"
                                :disabled="syncForm.processing"
                                class="inline-flex items-center px-4 py-2 rounded-lg bg-sky-500 hover:bg-sky-600 text-white text-sm font-medium transition disabled:opacity-50"
                            >
                                {{ $t('settings.add_sync') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Email Tab -->
            <div v-show="activeTab === 'email'">
                <div class="max-w-4xl space-y-6">
                    <!-- Pre/Post stay days -->
                    <form @submit.prevent="saveGeneral" class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                        <h3 class="text-md font-semibold text-gray-900 dark:text-white mb-4">{{ $t('settings.stay_automation') }}</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('settings.pre_arrival_days') }}</label>
                                <input
                                    v-model.number="generalForm.pre_arrival_days"
                                    type="number"
                                    min="0"
                                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('settings.post_stay_days') }}</label>
                                <input
                                    v-model.number="generalForm.post_stay_days"
                                    type="number"
                                    min="0"
                                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition"
                                />
                            </div>
                        </div>
                        <button
                            type="submit"
                            :disabled="generalForm.processing"
                            class="inline-flex items-center px-4 py-2 rounded-lg bg-sky-500 hover:bg-sky-600 text-white text-sm font-medium transition disabled:opacity-50"
                        >
                            {{ $t('settings.save') }}
                        </button>
                    </form>

                    <!-- Email templates -->
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ $t('settings.email_templates') }}</h2>

                        <div class="space-y-3">
                            <div
                                v-for="tpl in emailTemplates"
                                :key="tpl.id"
                                class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden"
                            >
                                <!-- Template header -->
                                <div
                                    @click="toggleTemplate(tpl.id)"
                                    class="flex items-center justify-between px-4 py-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-750 transition"
                                >
                                    <div class="flex items-center gap-3">
                                        <svg
                                            :class="['w-4 h-4 text-gray-400 transition-transform', expandedTemplates[tpl.id] ? 'rotate-90' : '']"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                        </svg>
                                        <div>
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ tpl.name }}</span>
                                            <span class="ml-2 text-xs text-gray-400">{{ tpl.slug }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <label class="relative inline-flex items-center cursor-pointer" @click.stop>
                                            <input
                                                type="checkbox"
                                                v-model="templateForms[tpl.id].is_active"
                                                class="sr-only peer"
                                            />
                                            <div class="w-9 h-5 bg-gray-200 dark:bg-gray-600 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-sky-500 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-sky-500"></div>
                                        </label>
                                    </div>
                                </div>

                                <!-- Template body (expanded) -->
                                <div v-if="expandedTemplates[tpl.id]" class="border-t border-gray-200 dark:border-gray-700 px-4 py-4 space-y-4">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('settings.subject_pl') }}</label>
                                            <input
                                                v-model="templateForms[tpl.id].subject_pl"
                                                type="text"
                                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition"
                                            />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('settings.subject_en') }}</label>
                                            <input
                                                v-model="templateForms[tpl.id].subject_en"
                                                type="text"
                                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition"
                                            />
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('settings.body_pl') }}</label>
                                        <textarea
                                            v-model="templateForms[tpl.id].body_pl"
                                            rows="6"
                                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition font-mono"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('settings.body_en') }}</label>
                                        <textarea
                                            v-model="templateForms[tpl.id].body_en"
                                            rows="6"
                                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition font-mono"
                                        />
                                    </div>
                                    <div v-if="tpl.available_variables" class="text-xs text-gray-500 dark:text-gray-400">
                                        <span class="font-medium">{{ $t('settings.available_variables') }}:</span>
                                        {{ tpl.available_variables.join(', ') }}
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <button
                                            @click="saveTemplate(tpl.id)"
                                            :disabled="templateForms[tpl.id].processing"
                                            class="inline-flex items-center px-4 py-2 rounded-lg bg-sky-500 hover:bg-sky-600 text-white text-sm font-medium transition disabled:opacity-50"
                                        >
                                            {{ $t('settings.save') }}
                                        </button>
                                        <span v-if="templateForms[tpl.id].recentlySuccessful" class="text-sm text-green-600 dark:text-green-400">{{ $t('settings.saved') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SEO Tab -->
            <div v-show="activeTab === 'seo'">
                <form @submit.prevent="saveGeneral" class="space-y-6 max-w-2xl">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('settings.google_analytics_id') }}</label>
                        <input
                            v-model="generalForm.google_analytics_id"
                            type="text"
                            placeholder="G-XXXXXXXXXX"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition"
                        />
                        <p v-if="generalForm.errors.google_analytics_id" class="mt-1 text-sm text-red-500">{{ generalForm.errors.google_analytics_id }}</p>
                    </div>

                    <div class="pt-4">
                        <button
                            type="submit"
                            :disabled="generalForm.processing"
                            class="inline-flex items-center px-5 py-2.5 rounded-lg bg-sky-500 hover:bg-sky-600 text-white text-sm font-medium transition disabled:opacity-50"
                        >
                            {{ $t('settings.save') }}
                        </button>
                        <span v-if="generalForm.recentlySuccessful" class="ml-3 text-sm text-green-600 dark:text-green-400">{{ $t('settings.saved') }}</span>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
