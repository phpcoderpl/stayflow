<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const { t, locale } = useI18n();

const props = defineProps({
    year: Number,
    availableYears: Array,
    properties: Array,
    selectedPropertyId: [Number, String],
    monthlyRevenue: Object,
    monthlyBookings: Object,
    monthlyOccupancy: Object,
    perPropertyData: Object,
    stats: Object,
});

function formatPLN(cents) {
    return new Intl.NumberFormat('pl-PL', { style: 'currency', currency: 'PLN' }).format(cents / 100);
}

const monthNames = computed(() => {
    const isPolish = locale.value === 'pl';
    return isPolish
        ? ['Sty', 'Lut', 'Mar', 'Kwi', 'Maj', 'Cze', 'Lip', 'Sie', 'Wrz', 'Paź', 'Lis', 'Gru']
        : ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
});

const maxRevenue = computed(() => {
    return Math.max(...Object.values(props.monthlyRevenue), 1);
});

const maxBookings = computed(() => {
    return Math.max(...Object.values(props.monthlyBookings), 1);
});

function revenueBarHeight(month) {
    const revenue = props.monthlyRevenue[month] || 0;
    return (revenue / maxRevenue.value) * 100;
}

function bookingsBarHeight(month) {
    const bookings = props.monthlyBookings[month] || 0;
    return (bookings / maxBookings.value) * 100;
}

function changeYear(event) {
    router.get('/admin/reports', {
        year: event.target.value,
        property_id: props.selectedPropertyId || undefined
    }, { preserveScroll: true });
}

function changeProperty(event) {
    const propertyId = event.target.value === '' ? undefined : event.target.value;
    router.get('/admin/reports', {
        year: props.year,
        property_id: propertyId
    }, { preserveScroll: true });
}

const perPropertyArray = computed(() => {
    return Object.values(props.perPropertyData || {});
});

const tableData = computed(() => {
    return Array.from({ length: 12 }, (_, i) => {
        const month = i + 1;
        return {
            month: monthNames.value[i],
            bookings: props.monthlyBookings[month] || 0,
            revenue: props.monthlyRevenue[month] || 0,
            avgPrice: props.monthlyBookings[month] > 0
                ? Math.round(props.monthlyRevenue[month] / props.monthlyBookings[month])
                : 0,
            occupancy: props.monthlyOccupancy[month] || 0,
        };
    });
});
</script>

<template>
    <AdminLayout>
        <div class="p-4 sm:p-6 lg:p-8 space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $t('reports.title') }}</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $t('reports.yearly_overview') }}</p>
                </div>

                <!-- Selectors -->
                <div class="flex items-center gap-3">
                    <!-- Property selector -->
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mr-2">{{ $t('reports.select_property') }}:</label>
                        <select
                            :value="selectedPropertyId || ''"
                            @change="changeProperty"
                            class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500 dark:focus:ring-sky-400 focus:border-transparent"
                        >
                            <option value="">{{ $t('reports.all_properties') }}</option>
                            <option v-for="property in properties" :key="property.id" :value="property.id">
                                {{ property.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Year selector -->
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mr-2">{{ $t('reports.year') }}:</label>
                        <select
                            :value="year"
                            @change="changeYear"
                            class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500 dark:focus:ring-sky-400 focus:border-transparent"
                        >
                            <option v-for="y in availableYears" :key="y" :value="y">{{ y }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Summary cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Total revenue -->
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ $t('reports.revenue') }}</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ formatPLN(stats.totalRevenue) }}</p>
                        </div>
                        <div class="p-2 bg-sky-100 dark:bg-sky-900/30 rounded-lg">
                            <svg class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Total bookings -->
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ $t('reports.bookings') }}</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ stats.totalBookings }}</p>
                        </div>
                        <div class="p-2 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg">
                            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Average booking value -->
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ $t('reports.avg_value') }}</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ formatPLN(stats.avgBookingValue) }}</p>
                        </div>
                        <div class="p-2 bg-amber-100 dark:bg-amber-900/30 rounded-lg">
                            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Occupancy rate -->
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ $t('reports.occupancy') }}</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ stats.avgOccupancy }}%</p>
                        </div>
                        <div class="p-2 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                            <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Monthly revenue chart -->
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">{{ $t('reports.monthly_revenue') }}</h2>
                    <div class="flex items-end justify-between h-64 gap-2">
                        <div
                            v-for="month in 12"
                            :key="`rev-${month}`"
                            class="flex-1 flex flex-col items-center justify-end group"
                        >
                            <div class="relative w-full mb-2">
                                <div
                                    :style="{ height: revenueBarHeight(month) + '%' }"
                                    class="w-full bg-sky-500 hover:bg-sky-600 dark:bg-sky-600 dark:hover:bg-sky-500 rounded-t transition-all cursor-pointer"
                                    :title="formatPLN(monthlyRevenue[month] || 0)"
                                >
                                    <span class="absolute -top-6 left-1/2 transform -translate-x-1/2 text-xs font-medium text-gray-900 dark:text-white opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                                        {{ formatPLN(monthlyRevenue[month] || 0) }}
                                    </span>
                                </div>
                            </div>
                            <span class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ monthNames[month - 1] }}</span>
                        </div>
                    </div>
                </div>

                <!-- Monthly bookings chart -->
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">{{ $t('reports.monthly_bookings') }}</h2>
                    <div class="flex items-end justify-between h-64 gap-2">
                        <div
                            v-for="month in 12"
                            :key="`book-${month}`"
                            class="flex-1 flex flex-col items-center justify-end group"
                        >
                            <div class="relative w-full mb-2">
                                <div
                                    :style="{ height: bookingsBarHeight(month) + '%' }"
                                    class="w-full bg-sky-500 hover:bg-sky-600 dark:bg-sky-600 dark:hover:bg-sky-500 rounded-t transition-all cursor-pointer"
                                    :title="(monthlyBookings[month] || 0).toString()"
                                >
                                    <span class="absolute -top-6 left-1/2 transform -translate-x-1/2 text-xs font-medium text-gray-900 dark:text-white opacity-0 group-hover:opacity-100 transition-opacity">
                                        {{ monthlyBookings[month] || 0 }}
                                    </span>
                                </div>
                            </div>
                            <span class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ monthNames[month - 1] }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monthly data table -->
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $t('reports.monthly_data') }}</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ $t('reports.month') }}</th>
                                <th class="text-right px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ $t('reports.bookings') }}</th>
                                <th class="text-right px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ $t('reports.revenue') }}</th>
                                <th class="text-right px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ $t('reports.avg_price') }}</th>
                                <th class="text-right px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ $t('reports.occupancy') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <tr v-for="(row, idx) in tableData" :key="idx" class="hover:bg-gray-50 dark:hover:bg-gray-750 transition">
                                <td class="px-6 py-3 text-gray-900 dark:text-white font-medium">{{ row.month }}</td>
                                <td class="px-6 py-3 text-right text-gray-600 dark:text-gray-400">{{ row.bookings }}</td>
                                <td class="px-6 py-3 text-right text-gray-900 dark:text-white font-medium">{{ formatPLN(row.revenue) }}</td>
                                <td class="px-6 py-3 text-right text-gray-600 dark:text-gray-400">{{ formatPLN(row.avgPrice) }}</td>
                                <td class="px-6 py-3 text-right text-gray-600 dark:text-gray-400">{{ row.occupancy }}%</td>
                            </tr>
                            <!-- Totals row -->
                            <tr class="bg-gray-50 dark:bg-gray-750 font-semibold">
                                <td class="px-6 py-3 text-gray-900 dark:text-white">{{ $t('reports.total') }}</td>
                                <td class="px-6 py-3 text-right text-gray-900 dark:text-white">{{ stats.totalBookings }}</td>
                                <td class="px-6 py-3 text-right text-gray-900 dark:text-white">{{ formatPLN(stats.totalRevenue) }}</td>
                                <td class="px-6 py-3 text-right text-gray-900 dark:text-white">{{ formatPLN(stats.avgBookingValue) }}</td>
                                <td class="px-6 py-3 text-right text-gray-900 dark:text-white">{{ stats.avgOccupancy }}%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Top property (if applicable) -->
            <div v-if="stats.topProperty" class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-sky-100 dark:bg-sky-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-sky-600 dark:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 01-.982-3.172M9.497 14.25a7.454 7.454 0 00.981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 007.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 002.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 012.916.52 6.003 6.003 0 01-5.395 4.972m0 0a6.726 6.726 0 01-2.749 1.35m0 0a6.772 6.772 0 01-3.044 0" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $t('reports.top_property') }}</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ stats.topProperty.name }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ formatPLN(stats.topProperty.revenue) }} {{ $t('reports.in_revenue') }}</p>
                    </div>
                </div>
            </div>

            <!-- Per-property breakdown (only when viewing all properties) -->
            <div v-if="!selectedPropertyId && perPropertyArray.length > 0" class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $t('reports.per_property') }}</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ $t('reports.property') }}</th>
                                <th class="text-right px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ $t('reports.bookings') }}</th>
                                <th class="text-right px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ $t('reports.revenue') }}</th>
                                <th class="text-right px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ $t('reports.occupancy') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <tr v-for="property in perPropertyArray" :key="property.name" class="hover:bg-gray-50 dark:hover:bg-gray-750 transition">
                                <td class="px-6 py-3 text-gray-900 dark:text-white font-medium">{{ property.name }}</td>
                                <td class="px-6 py-3 text-right text-gray-600 dark:text-gray-400">{{ property.bookings }}</td>
                                <td class="px-6 py-3 text-right text-gray-900 dark:text-white font-medium">{{ formatPLN(property.revenue) }}</td>
                                <td class="px-6 py-3 text-right text-gray-600 dark:text-gray-400">{{ property.occupancy }}%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
