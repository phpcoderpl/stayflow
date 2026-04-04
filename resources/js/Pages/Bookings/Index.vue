<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { ref, watch } from 'vue';

const { t } = useI18n();

const props = defineProps({
    bookings: Object,
    properties: Array,
    filters: Object,
});

const statusFilter = ref(props.filters?.status || '');
const propertyFilter = ref(props.filters?.property_id || '');

const statuses = [
    'pending',
    'confirmed',
    'checked_in',
    'checked_out',
    'cancelled',
    'no_show',
];

const statusBadgeClass = (status) => {
    const map = {
        pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
        confirmed: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
        checked_in: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
        checked_out: 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400',
        cancelled: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
        no_show: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
    };
    return map[status] || 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400';
};

const formatPrice = (grosze) => {
    return (grosze / 100).toFixed(2) + ' PLN';
};

function applyFilters() {
    const params = {};
    if (statusFilter.value) params.status = statusFilter.value;
    if (propertyFilter.value) params.property_id = propertyFilter.value;
    router.get('/admin/bookings', params, { preserveState: true, replace: true });
}

watch([statusFilter, propertyFilter], () => {
    applyFilters();
});
</script>

<template>
    <AdminLayout>
        <div class="p-4 sm:p-6 lg:p-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ t('bookings.title') }}
                </h1>
                <Link
                    href="/admin/bookings/create"
                    class="bg-sky-600 hover:bg-sky-700 text-white rounded-lg px-4 py-2 text-sm font-medium"
                >
                    {{ t('bookings.add') }}
                </Link>
            </div>

            <!-- Filter bar -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-6 flex flex-wrap gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                        {{ t('bookings.status') }}
                    </label>
                    <select
                        v-model="statusFilter"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm px-3 py-2 border"
                    >
                        <option value="">{{ t('bookings.allStatuses') }}</option>
                        <option v-for="s in statuses" :key="s" :value="s">
                            {{ t('bookings.statuses.' + s) }}
                        </option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                        {{ t('bookings.property') }}
                    </label>
                    <select
                        v-model="propertyFilter"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm px-3 py-2 border"
                    >
                        <option value="">{{ t('bookings.allProperties') }}</option>
                        <option v-for="p in properties" :key="p.id" :value="p.id">
                            {{ p.name }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- Empty state -->
            <div
                v-if="!bookings.data || bookings.data.length === 0"
                class="bg-white dark:bg-gray-800 rounded-lg shadow p-12 text-center"
            >
                <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">
                    {{ t('bookings.empty') }}
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ t('bookings.emptyDesc') }}
                </p>
                <Link
                    href="/admin/bookings/create"
                    class="mt-4 inline-block bg-sky-600 hover:bg-sky-700 text-white rounded-lg px-4 py-2 text-sm font-medium"
                >
                    {{ t('bookings.add') }}
                </Link>
            </div>

            <!-- Table -->
            <div v-else class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    {{ t('bookings.confirmationCode') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    {{ t('bookings.guest') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    {{ t('bookings.property') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    {{ t('bookings.checkIn') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    {{ t('bookings.checkOut') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    {{ t('bookings.nights') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    {{ t('bookings.totalPrice') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    {{ t('bookings.status') }}
                                </th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    {{ t('bookings.actions') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="booking in bookings.data" :key="booking.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                <td class="px-4 py-3 text-sm font-mono text-gray-900 dark:text-white">
                                    {{ booking.confirmation_code }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                    {{ booking.guest?.first_name }} {{ booking.guest?.last_name }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                    {{ booking.property?.name }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                    {{ booking.check_in }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                    {{ booking.check_out }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                    {{ booking.nights }}
                                </td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">
                                    {{ formatPrice(booking.total_price) }}
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        :class="[
                                            'inline-flex items-center px-2 py-0.5 rounded text-xs font-medium',
                                            statusBadgeClass(booking.status)
                                        ]"
                                    >
                                        {{ t('bookings.statuses.' + booking.status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <Link
                                        :href="`/admin/bookings/${booking.id}`"
                                        class="text-sky-600 hover:text-sky-700 dark:text-sky-400 dark:hover:text-sky-300 text-sm font-medium"
                                    >
                                        {{ t('bookings.view') }}
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="bookings.links && bookings.links.length > 3" class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 flex items-center justify-center gap-1">
                    <template v-for="link in bookings.links" :key="link.label">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            :class="[
                                'px-3 py-1 rounded text-sm',
                                link.active
                                    ? 'bg-sky-600 text-white'
                                    : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'
                            ]"
                            v-html="link.label"
                            preserve-state
                        />
                        <span
                            v-else
                            class="px-3 py-1 text-sm text-gray-400 dark:text-gray-500"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
