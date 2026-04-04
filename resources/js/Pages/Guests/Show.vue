<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
    guest: Object,
});

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
</script>

<template>
    <AdminLayout>
        <div class="p-4 sm:p-6 lg:p-8">
            <!-- Header -->
            <div class="flex items-center gap-3 mb-6">
                <Link href="/admin/guests" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                </Link>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ guest.first_name }} {{ guest.last_name }}
                </h1>
            </div>

            <!-- Guest info card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    {{ t('guests.guestInfo') }}
                </h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm text-gray-500 dark:text-gray-400">{{ t('guests.name') }}</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">
                            {{ guest.first_name }} {{ guest.last_name }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500 dark:text-gray-400">{{ t('guests.email') }}</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">
                            {{ guest.email || '-' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500 dark:text-gray-400">{{ t('guests.phone') }}</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">
                            {{ guest.phone || '-' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500 dark:text-gray-400">{{ t('guests.country') }}</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">
                            {{ guest.country || '-' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500 dark:text-gray-400">{{ t('guests.city') }}</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">
                            {{ guest.city || '-' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500 dark:text-gray-400">{{ t('guests.accessToken') }}</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">
                            <template v-if="guest.access_token">
                                <a
                                    :href="`/guest/${guest.access_token}`"
                                    target="_blank"
                                    class="text-sky-600 hover:text-sky-700 dark:text-sky-400 dark:hover:text-sky-300 underline"
                                >
                                    {{ t('guests.guestPortalLink') }}
                                </a>
                            </template>
                            <template v-else>-</template>
                        </dd>
                    </div>
                    <div v-if="guest.notes" class="sm:col-span-2">
                        <dt class="text-sm text-gray-500 dark:text-gray-400">{{ t('guests.notes') }}</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-white mt-0.5 whitespace-pre-line">
                            {{ guest.notes }}
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Bookings table -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ t('guests.bookings') }}
                    </h2>
                </div>

                <div v-if="!guest.bookings || guest.bookings.length === 0" class="p-6 text-center text-sm text-gray-500 dark:text-gray-400">
                    {{ t('guests.noBookings') }}
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
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
                                    {{ t('bookings.status') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    {{ t('bookings.totalPrice') }}
                                </th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    {{ t('bookings.actions') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="booking in guest.bookings" :key="booking.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                    {{ booking.property?.name }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                    {{ booking.check_in }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                    {{ booking.check_out }}
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
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">
                                    {{ formatPrice(booking.total_price) }}
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
            </div>
        </div>
    </AdminLayout>
</template>
