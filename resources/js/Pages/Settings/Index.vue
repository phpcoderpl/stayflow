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
    widgets: Array,
});

const activeTab = ref('general');

const tabs = [
    { key: 'general', label: 'settings.tabs.general' },
    { key: 'payments', label: 'settings.tabs.payments' },
    { key: 'contact', label: 'settings.tabs.contact' },
    { key: 'terms', label: 'settings.tabs.terms' },
    { key: 'integrations', label: 'settings.tabs.integrations' },
    { key: 'email', label: 'settings.tabs.email' },
    { key: 'seo', label: 'settings.tabs.seo' },
    { key: 'widgets', label: 'settings.tabs.widgets' },
];

// General / SEO form
const generalForm = useForm({
    brand_name: props.settings.brand_name ?? '',
    brand_primary_color: props.settings.brand_primary_color ?? '#0ea5e9',
    property_list_layout: props.settings.property_list_layout ?? 'auto',
    deposit_percent: props.settings.deposit_percent ?? 0,
    admin_email: props.settings.admin_email ?? '',
    google_analytics_id: props.settings.google_analytics_id ?? '',
    pre_arrival_days: props.settings.pre_arrival_days ?? 1,
    post_stay_days: props.settings.post_stay_days ?? 1,
    contact_name: props.settings.contact_name ?? '',
    contact_email: props.settings.contact_email ?? '',
    contact_phone: props.settings.contact_phone ?? '',
    contact_address: props.settings.contact_address ?? '',
    contact_description_pl: props.settings.contact_description_pl ?? '',
    contact_description_en: props.settings.contact_description_en ?? '',
    terms_pl: props.settings.terms_pl ?? '',
    terms_en: props.settings.terms_en ?? '',
    mail_mailer: props.settings.mail_mailer ?? 'smtp',
    mail_host: props.settings.mail_host ?? '',
    mail_port: props.settings.mail_port ?? '587',
    mail_username: props.settings.mail_username ?? '',
    mail_password: props.settings.mail_password ?? '',
    mail_encryption: props.settings.mail_encryption ?? 'tls',
    mail_from_address: props.settings.mail_from_address ?? '',
    mail_from_name: props.settings.mail_from_name ?? '',
});

// Payment settings form
const paymentForm = useForm({
    payment_provider: props.settings.payment_provider ?? 'stripe',
    stripe_publishable_key: props.settings.stripe_publishable_key ?? '',
    stripe_secret_key: props.settings.stripe_secret_key ?? '',
    stripe_webhook_secret: props.settings.stripe_webhook_secret ?? '',
    hotpay_secret: props.settings.hotpay_secret ?? '',
    hotpay_notification_password: props.settings.hotpay_notification_password ?? '',
    payu_pos_id: props.settings.payu_pos_id ?? '',
    payu_client_secret: props.settings.payu_client_secret ?? '',
    payu_second_key: props.settings.payu_second_key ?? '',
    payu_sandbox: props.settings.payu_sandbox ?? '1',
});

function submitPayment() {
    paymentForm.put('/admin/settings');
}

const baseUrl = typeof window !== 'undefined' ? window.location.origin : '';
const hotpayNotifyUrl = baseUrl + '/hotpay/notify';
const stripeWebhookUrl = baseUrl + '/stripe/webhook';

// Contact photo upload
const contactPhotoUrl = ref(props.settings.contact_photo ? `/storage/${props.settings.contact_photo}` : null);

function uploadContactPhoto(event) {
    const file = event.target.files[0];
    if (!file) return;
    const formData = new FormData();
    formData.append('photo', file);
    router.post('/admin/settings/contact-photo', formData, {
        onSuccess: () => {
            contactPhotoUrl.value = URL.createObjectURL(file);
        },
    });
}

function saveGeneral() {
    generalForm.put('/admin/settings');
}

// Calendar sync
const syncForm = useForm({
    property_id: '',
    provider: 'ical',
    direction: 'import',
    ical_url: '',
});

function addSync() {
    // Clear URL for export (backend determines direction from URL presence)
    if (syncForm.direction === 'export') {
        syncForm.ical_url = '';
    }
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

// Widgets
const widgetForm = useForm({
    property_id: '',
    type: '',
    name: '',
});

function addWidget() {
    widgetForm.post('/admin/widgets', {
        onSuccess: () => widgetForm.reset(),
    });
}

function deleteWidget(id) {
    if (confirm(t('widgets.delete_confirm'))) {
        router.delete('/admin/widgets/' + id);
    }
}

function copyEmbedCode(code) {
    navigator.clipboard.writeText(code);
    // Show brief success feedback (you can expand this with a toast)
}

function getWidgetTypeName(type) {
    return t(`widgets.${type}`);
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
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('settings.property_list_layout') }}</label>
                        <select
                            v-model="generalForm.property_list_layout"
                            class="w-full max-w-xs px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500"
                        >
                            <option value="auto">{{ $t('settings.layout_auto') }}</option>
                            <option value="1">{{ $t('settings.layout_1col') }}</option>
                            <option value="2">{{ $t('settings.layout_2col') }}</option>
                            <option value="3">{{ $t('settings.layout_3col') }}</option>
                        </select>
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

            <!-- Payments Tab -->
            <div v-show="activeTab === 'payments'">
                <form @submit.prevent="submitPayment" class="space-y-6 max-w-2xl">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('settings.payment_provider') }}</label>
                        <select
                            v-model="paymentForm.payment_provider"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition"
                        >
                            <option value="stripe">Stripe (BLIK + karty + Przelewy24)</option>
                            <option value="hotpay">HotPay (BLIK + przelewy, bez firmy)</option>
                            <option value="payu">PayU (wymaga firmy)</option>
                        </select>
                    </div>

                    <!-- Stripe settings -->
                    <template v-if="paymentForm.payment_provider === 'stripe'">
                        <div class="p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg border border-indigo-200 dark:border-indigo-800">
                            <p class="text-sm text-indigo-800 dark:text-indigo-300 mb-2 font-medium">Stripe</p>
                            <p class="text-xs text-indigo-700 dark:text-indigo-400">{{ $t('settings.stripe_info') }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Publishable Key</label>
                            <input
                                v-model="paymentForm.stripe_publishable_key"
                                type="text"
                                placeholder="pk_live_..."
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Secret Key</label>
                            <input
                                v-model="paymentForm.stripe_secret_key"
                                type="password"
                                placeholder="sk_live_..."
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Webhook Secret</label>
                            <input
                                v-model="paymentForm.stripe_webhook_secret"
                                type="password"
                                placeholder="whsec_..."
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Webhook URL</label>
                            <input
                                :value="stripeWebhookUrl"
                                type="text"
                                readonly
                                class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-500 dark:text-gray-400"
                            />
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $t('settings.stripe_webhook_help') }}</p>
                        </div>
                    </template>

                    <!-- HotPay settings -->
                    <template v-if="paymentForm.payment_provider === 'hotpay'">
                        <div class="p-4 bg-sky-50 dark:bg-sky-900/20 rounded-lg border border-sky-200 dark:border-sky-800">
                            <p class="text-sm text-sky-800 dark:text-sky-300 mb-2 font-medium">HotPay</p>
                            <p class="text-xs text-sky-700 dark:text-sky-400">{{ $t('settings.hotpay_info') }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('settings.hotpay_secret') }}</label>
                            <input
                                v-model="paymentForm.hotpay_secret"
                                type="text"
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('settings.hotpay_notification_password') }}</label>
                            <input
                                v-model="paymentForm.hotpay_notification_password"
                                type="text"
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('settings.hotpay_notify_url') }}</label>
                            <div class="flex items-center gap-2">
                                <input
                                    :value="hotpayNotifyUrl"
                                    type="text"
                                    readonly
                                    class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-500 dark:text-gray-400"
                                />
                            </div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $t('settings.hotpay_notify_url_help') }}</p>
                        </div>
                    </template>

                    <!-- PayU settings -->
                    <template v-if="paymentForm.payment_provider === 'payu'">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">PayU POS ID</label>
                            <input
                                v-model="paymentForm.payu_pos_id"
                                type="text"
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Client Secret</label>
                            <input
                                v-model="paymentForm.payu_client_secret"
                                type="password"
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Second Key</label>
                            <input
                                v-model="paymentForm.payu_second_key"
                                type="password"
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition"
                            />
                        </div>
                        <div class="flex items-center gap-2">
                            <input
                                v-model="paymentForm.payu_sandbox"
                                type="checkbox"
                                true-value="1"
                                false-value="0"
                                class="rounded border-gray-300 text-sky-600 focus:ring-sky-500"
                            />
                            <label class="text-sm text-gray-700 dark:text-gray-300">Sandbox (testowy)</label>
                        </div>
                    </template>

                    <div class="pt-4">
                        <button
                            type="submit"
                            :disabled="paymentForm.processing"
                            class="inline-flex items-center px-5 py-2.5 rounded-lg bg-sky-500 hover:bg-sky-600 text-white text-sm font-medium transition disabled:opacity-50"
                        >
                            {{ $t('app.save') }}
                        </button>
                        <span v-if="paymentForm.recentlySuccessful" class="ml-3 text-sm text-green-600 dark:text-green-400">{{ $t('settings.saved') }}</span>
                    </div>
                </form>
            </div>

            <!-- Contact Tab -->
            <div v-show="activeTab === 'contact'">
                <div class="space-y-6 max-w-2xl">
                    <!-- Profile photo -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">{{ $t('settings.contact_photo') }}</label>
                        <div class="flex items-center gap-4">
                            <div v-if="contactPhotoUrl" class="w-20 h-20 rounded-xl overflow-hidden">
                                <img :src="contactPhotoUrl" alt="Profile" class="w-full h-full object-cover" />
                            </div>
                            <div v-else class="w-20 h-20 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                </svg>
                            </div>
                            <label class="cursor-pointer inline-flex items-center px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                {{ $t('settings.upload_photo') }}
                                <input type="file" accept="image/*" class="hidden" @change="uploadContactPhoto" />
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('settings.contact_name') }}</label>
                        <input
                            v-model="generalForm.contact_name"
                            type="text"
                            :placeholder="$t('settings.contact_name_placeholder')"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition"
                        />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('settings.contact_email') }}</label>
                            <input
                                v-model="generalForm.contact_email"
                                type="email"
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('settings.contact_phone') }}</label>
                            <input
                                v-model="generalForm.contact_phone"
                                type="tel"
                                placeholder="+48 ..."
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('settings.contact_address') }}</label>
                        <input
                            v-model="generalForm.contact_address"
                            type="text"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('settings.contact_description_pl') }}</label>
                        <textarea
                            v-model="generalForm.contact_description_pl"
                            rows="3"
                            :placeholder="$t('settings.contact_description_placeholder')"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('settings.contact_description_en') }}</label>
                        <textarea
                            v-model="generalForm.contact_description_en"
                            rows="3"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition"
                        />
                    </div>

                    <div class="pt-4">
                        <button
                            @click="saveGeneral"
                            :disabled="generalForm.processing"
                            class="inline-flex items-center px-5 py-2.5 rounded-lg bg-sky-500 hover:bg-sky-600 text-white text-sm font-medium transition disabled:opacity-50"
                        >
                            {{ $t('settings.save') }}
                        </button>
                        <span v-if="generalForm.recentlySuccessful" class="ml-3 text-sm text-green-600 dark:text-green-400">{{ $t('settings.saved') }}</span>
                    </div>
                </div>
            </div>

            <!-- Terms Tab -->
            <div v-show="activeTab === 'terms'">
                <div class="space-y-6 max-w-3xl">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $t('settings.terms_info') }}
                    </p>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('settings.terms_pl') }}</label>
                        <textarea
                            v-model="generalForm.terms_pl"
                            rows="15"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition font-mono"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('settings.terms_en') }}</label>
                        <textarea
                            v-model="generalForm.terms_en"
                            rows="15"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition font-mono"
                        />
                    </div>

                    <div class="pt-4">
                        <button
                            @click="saveGeneral"
                            :disabled="generalForm.processing"
                            class="inline-flex items-center px-5 py-2.5 rounded-lg bg-sky-500 hover:bg-sky-600 text-white text-sm font-medium transition disabled:opacity-50"
                        >
                            {{ $t('settings.save') }}
                        </button>
                        <span v-if="generalForm.recentlySuccessful" class="ml-3 text-sm text-green-600 dark:text-green-400">{{ $t('settings.saved') }}</span>
                    </div>
                </div>
            </div>

            <!-- Integrations Tab -->
            <div v-show="activeTab === 'integrations'">
                <div class="max-w-4xl space-y-8">
                    <!-- How it works -->
                    <div class="bg-sky-50 dark:bg-sky-900/20 border border-sky-200 dark:border-sky-800 rounded-lg p-4">
                        <h3 class="text-sm font-semibold text-sky-800 dark:text-sky-300 mb-2">{{ $t('settings.sync_how_title') }}</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs text-sky-700 dark:text-sky-400">
                            <div>
                                <p class="font-semibold mb-1">{{ $t('settings.sync_import_title') }}</p>
                                <p>{{ $t('settings.sync_import_desc') }}</p>
                            </div>
                            <div>
                                <p class="font-semibold mb-1">{{ $t('settings.sync_export_title') }}</p>
                                <p>{{ $t('settings.sync_export_desc') }}</p>
                            </div>
                        </div>
                    </div>

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
                                    <div class="space-y-1.5 min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ sync.property?.name || $t('settings.unknown_property') }}</span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300">
                                                {{ sync.provider === 'booking_com' ? 'Booking.com' : sync.provider === 'google' ? 'Google' : 'iCal' }}
                                            </span>
                                            <span :class="[
                                                'inline-flex items-center px-2 py-0.5 rounded text-xs font-medium',
                                                sync.direction === 'import'
                                                    ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300'
                                                    : 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300'
                                            ]">
                                                {{ sync.direction === 'import' ? $t('settings.sync_dir_import') : $t('settings.sync_dir_export') }}
                                            </span>
                                        </div>
                                        <!-- Import: show source URL -->
                                        <div v-if="sync.direction === 'import' && sync.ical_url" class="text-xs text-gray-500 dark:text-gray-400">
                                            <span class="font-medium">{{ $t('settings.sync_source') }}:</span>
                                            <span class="ml-1 truncate">{{ sync.ical_url }}</span>
                                        </div>
                                        <!-- Export: show generated URL with copy -->
                                        <div v-if="sync.direction === 'export' && sync.ical_export_token" class="space-y-1">
                                            <p class="text-xs font-medium text-gray-600 dark:text-gray-400">{{ $t('settings.sync_your_link') }}:</p>
                                            <div class="flex items-center gap-2 bg-gray-50 dark:bg-gray-900 rounded px-2 py-1.5">
                                                <code class="text-xs text-gray-700 dark:text-gray-300 truncate flex-1">{{ exportUrl(sync.ical_export_token) }}</code>
                                                <button
                                                    @click="copyExportUrl(sync.ical_export_token)"
                                                    class="shrink-0 text-xs font-medium text-sky-600 dark:text-sky-400 hover:underline"
                                                >
                                                    {{ $t('settings.copy_url') }}
                                                </button>
                                            </div>
                                            <p class="text-xs text-gray-400 dark:text-gray-500">{{ $t('settings.sync_export_hint') }}</p>
                                        </div>
                                        <p v-if="sync.last_synced_at" class="text-xs text-gray-400 dark:text-gray-500">
                                            {{ $t('settings.last_synced') }}: {{ sync.last_synced_at }}
                                        </p>
                                    </div>
                                    <button
                                        @click="deleteSync(sync.id)"
                                        class="shrink-0 p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition"
                                        :title="$t('app.delete')"
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
                            <!-- Direction choice -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ $t('settings.sync_direction') }}</label>
                                <div class="flex gap-3">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" v-model="syncForm.direction" value="import" class="text-sky-500 focus:ring-sky-500" />
                                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ $t('settings.sync_dir_import') }}</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" v-model="syncForm.direction" value="export" class="text-sky-500 focus:ring-sky-500" />
                                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ $t('settings.sync_dir_export') }}</span>
                                    </label>
                                </div>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    {{ syncForm.direction === 'import' ? $t('settings.sync_import_help') : $t('settings.sync_export_help') }}
                                </p>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('settings.property') }}</label>
                                    <select
                                        v-model="syncForm.property_id"
                                        class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500 transition"
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
                                        class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500 transition"
                                    >
                                        <option value="ical">iCal</option>
                                        <option value="booking_com">Booking.com</option>
                                        <option value="google">Google</option>
                                    </select>
                                    <p v-if="syncForm.errors.provider" class="mt-1 text-sm text-red-500">{{ syncForm.errors.provider }}</p>
                                </div>
                            </div>

                            <!-- URL field only for import -->
                            <div v-if="syncForm.direction === 'import'">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('settings.ical_url') }}</label>
                                <input
                                    v-model="syncForm.ical_url"
                                    type="url"
                                    placeholder="https://admin.booking.com/hotel/.../ical/..."
                                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500 transition"
                                />
                                <p v-if="syncForm.errors.ical_url" class="mt-1 text-sm text-red-500">{{ syncForm.errors.ical_url }}</p>
                            </div>

                            <!-- Info for export -->
                            <div v-if="syncForm.direction === 'export'" class="bg-gray-50 dark:bg-gray-900 rounded-lg p-3">
                                <p class="text-xs text-gray-600 dark:text-gray-400">{{ $t('settings.sync_export_create_info') }}</p>
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
                    <!-- SMTP Configuration -->
                    <form @submit.prevent="saveGeneral" class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                        <h3 class="text-md font-semibold text-gray-900 dark:text-white mb-4">{{ $t('settings.smtp_config') }}</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">{{ $t('settings.smtp_info') }}</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('settings.mail_host') }}</label>
                                <input v-model="generalForm.mail_host" type="text" placeholder="smtp.gmail.com" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500 transition" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('settings.mail_port') }}</label>
                                <input v-model="generalForm.mail_port" type="text" placeholder="587" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500 transition" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('settings.mail_username') }}</label>
                                <input v-model="generalForm.mail_username" type="text" placeholder="user@gmail.com" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500 transition" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('settings.mail_password') }}</label>
                                <input v-model="generalForm.mail_password" type="password" placeholder="••••••••" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500 transition" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('settings.mail_encryption') }}</label>
                                <select v-model="generalForm.mail_encryption" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500 transition">
                                    <option value="tls">TLS (port 587)</option>
                                    <option value="ssl">SSL (port 465)</option>
                                    <option value="">{{ $t('settings.none') }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('settings.mail_from_address') }}</label>
                                <input v-model="generalForm.mail_from_address" type="email" placeholder="rezerwacje@twojadomena.pl" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500 transition" />
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('settings.mail_from_name') }}</label>
                                <input v-model="generalForm.mail_from_name" type="text" placeholder="StayFlow Rezerwacje" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500 transition" />
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

            <!-- Widgets Tab -->
            <div v-show="activeTab === 'widgets'">
                <div class="max-w-4xl space-y-8">
                    <!-- Existing widgets -->
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ $t('widgets.title') }}</h2>

                        <div v-if="widgets.length === 0" class="text-sm text-gray-500 dark:text-gray-400 py-4">
                            {{ $t('widgets.no_widgets') }}
                        </div>

                        <div class="space-y-3">
                            <div
                                v-for="widget in widgets"
                                :key="widget.id"
                                class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4"
                            >
                                <div class="space-y-3">
                                    <div class="flex flex-wrap items-start justify-between gap-3">
                                        <div class="space-y-1 min-w-0 flex-1">
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ widget.name }}</span>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300">
                                                    {{ getWidgetTypeName(widget.type) }}
                                                </span>
                                            </div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ widget.property?.name || $t('settings.unknown_property') }}</p>
                                        </div>
                                        <button
                                            @click="deleteWidget(widget.id)"
                                            class="shrink-0 p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                            </svg>
                                        </button>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('widgets.embed_code') }}</label>
                                        <div class="relative">
                                            <textarea
                                                :value="widget.embed_code"
                                                readonly
                                                rows="3"
                                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 px-3 py-2 text-xs text-gray-900 dark:text-white font-mono"
                                            />
                                            <button
                                                @click="copyEmbedCode(widget.embed_code)"
                                                class="absolute top-2 right-2 px-3 py-1.5 rounded bg-sky-500 hover:bg-sky-600 text-white text-xs font-medium transition"
                                            >
                                                {{ $t('widgets.copy_code') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Add new widget -->
                    <div>
                        <h3 class="text-md font-semibold text-gray-900 dark:text-white mb-3">{{ $t('widgets.add') }}</h3>
                        <form @submit.prevent="addWidget" class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('widgets.property') }}</label>
                                    <select
                                        v-model="widgetForm.property_id"
                                        class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition"
                                    >
                                        <option value="">{{ $t('settings.select_property') }}</option>
                                        <option v-for="prop in properties" :key="prop.id" :value="prop.id">{{ prop.name }}</option>
                                    </select>
                                    <p v-if="widgetForm.errors.property_id" class="mt-1 text-sm text-red-500">{{ widgetForm.errors.property_id }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('widgets.type') }}</label>
                                    <select
                                        v-model="widgetForm.type"
                                        class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition"
                                    >
                                        <option value="">{{ $t('app.select') }}</option>
                                        <option value="calendar">{{ $t('widgets.calendar') }}</option>
                                        <option value="booking_form">{{ $t('widgets.booking_form') }}</option>
                                        <option value="showcase">{{ $t('widgets.showcase') }}</option>
                                    </select>
                                    <p v-if="widgetForm.errors.type" class="mt-1 text-sm text-red-500">{{ widgetForm.errors.type }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('widgets.name') }}</label>
                                    <input
                                        v-model="widgetForm.name"
                                        type="text"
                                        :placeholder="$t('widgets.name')"
                                        class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition"
                                    />
                                    <p v-if="widgetForm.errors.name" class="mt-1 text-sm text-red-500">{{ widgetForm.errors.name }}</p>
                                </div>
                            </div>
                            <button
                                type="submit"
                                :disabled="widgetForm.processing"
                                class="inline-flex items-center px-4 py-2 rounded-lg bg-sky-500 hover:bg-sky-600 text-white text-sm font-medium transition disabled:opacity-50"
                            >
                                {{ $t('widgets.add') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
