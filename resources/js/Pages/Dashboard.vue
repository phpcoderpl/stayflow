<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const { t } = useI18n();

const props = defineProps({
    stats: Object,
    recentBookings: Array,
    upcomingBookings: Array,
});

function formatPLN(cents) {
    return new Intl.NumberFormat('pl-PL', { style: 'currency', currency: 'PLN' }).format(cents / 100);
}

const statusColors = {
    confirmed: 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
    pending: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300',
    cancelled: 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
    completed: 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
};

function statusClass(status) {
    return statusColors[status] || 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300';
}

const statCards = computed(() => [
    {
        label: t('dashboard.total_bookings'),
        value: props.stats?.totalBookings ?? 0,
        sub: t('dashboard.monthly_bookings', { count: props.stats?.monthlyBookings ?? 0 }),
        color: 'border-sky-500',
        icon: 'calendar',
    },
    {
        label: t('dashboard.revenue'),
        value: formatPLN(props.stats?.totalRevenue ?? 0),
        sub: t('dashboard.monthly_revenue', { amount: formatPLN(props.stats?.monthlyRevenue ?? 0) }),
        color: 'border-emerald-500',
        icon: 'currency',
    },
    {
        label: t('dashboard.upcoming_checkins'),
        value: props.stats?.upcomingCheckins ?? 0,
        sub: t('dashboard.next_7_days'),
        color: 'border-amber-500',
        icon: 'login',
    },
    {
        label: t('dashboard.occupancy'),
        value: (props.stats?.occupancy ?? 0) + '%',
        sub: t('dashboard.occupancy_placeholder'),
        color: 'border-purple-500',
        icon: 'chart',
    },
]);
</script>

<template>
    <AdminLayout>
        <div class="p-4 sm:p-6 lg:p-8 space-y-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $t('dashboard.title') }}</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $t('app.tagline') }}</p>
            </div>

            <!-- Stats cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div
                    v-for="(card, idx) in statCards"
                    :key="idx"
                    :class="['bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 border-l-4', card.color]"
                >
                    <div class="flex items-start justify-between">
                        <div class="min-w-0">
                            <p class="text-2xl font-bold text-gray-900 dark:text-white truncate">{{ card.value }}</p>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mt-1">{{ card.label }}</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ card.sub }}</p>
                        </div>
                        <div class="shrink-0 w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                            <!-- Calendar -->
                            <svg v-if="card.icon === 'calendar'" class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                            </svg>
                            <!-- Currency -->
                            <svg v-if="card.icon === 'currency'" class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <!-- Login -->
                            <svg v-if="card.icon === 'login'" class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                            </svg>
                            <!-- Chart -->
                            <svg v-if="card.icon === 'chart'" class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Two column layout -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent bookings -->
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $t('dashboard.recent_bookings') }}</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-100 dark:border-gray-700">
                                    <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ $t('dashboard.guest') }}</th>
                                    <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ $t('dashboard.property') }}</th>
                                    <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ $t('dashboard.dates') }}</th>
                                    <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ $t('dashboard.status') }}</th>
                                    <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ $t('dashboard.amount') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <tr v-for="booking in recentBookings" :key="booking.id" class="hover:bg-gray-50 dark:hover:bg-gray-750 transition">
                                    <td class="px-4 py-2.5 text-gray-900 dark:text-white whitespace-nowrap">{{ booking.guest_name }}</td>
                                    <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400 whitespace-nowrap">{{ booking.property_name }}</td>
                                    <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400 whitespace-nowrap">{{ booking.check_in }} - {{ booking.check_out }}</td>
                                    <td class="px-4 py-2.5 whitespace-nowrap">
                                        <span :class="['inline-flex items-center px-2 py-0.5 rounded text-xs font-medium', statusClass(booking.status)]">
                                            {{ booking.status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2.5 text-right text-gray-900 dark:text-white whitespace-nowrap">{{ formatPLN(booking.total_price) }}</td>
                                </tr>
                                <tr v-if="!recentBookings?.length">
                                    <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-400 dark:text-gray-500">
                                        {{ $t('dashboard.no_recent_bookings') }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Upcoming check-ins -->
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $t('dashboard.upcoming_checkins') }}</h2>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-gray-700">
                        <div
                            v-for="booking in upcomingBookings"
                            :key="booking.id"
                            class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-750 transition"
                        >
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ booking.guest_name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ booking.property_name }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-900 dark:text-white">{{ booking.check_in }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $t('dashboard.nights', { count: booking.nights }) }}</p>
                            </div>
                        </div>
                        <div v-if="!upcomingBookings?.length" class="px-4 py-6 text-center text-sm text-gray-400 dark:text-gray-500">
                            {{ $t('dashboard.no_upcoming_checkins') }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick actions -->
            <div class="flex flex-wrap gap-3">
                <Link
                    href="/admin/bookings/create"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-sky-500 hover:bg-sky-600 text-white text-sm font-medium transition"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    {{ $t('dashboard.new_booking') }}
                </Link>
                <Link
                    href="/admin/calendar?mode=block"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                    {{ $t('dashboard.block_dates') }}
                </Link>
                <Link
                    href="/admin/calendar"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                    </svg>
                    {{ $t('dashboard.view_calendar') }}
                </Link>
            </div>
        </div>
    </AdminLayout>
</template>
