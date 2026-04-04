<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { ref, computed } from 'vue';

const { t } = useI18n();

const props = defineProps({
    bookings: Array,
    blockedDates: Array,
    properties: Array,
    selectedPropertyId: [Number, String, null],
});

const currentMonth = ref(new Date());
const selectedPropertyFilter = ref(props.selectedPropertyId || '');

// Block date modal
const showBlockModal = ref(false);
const blockForm = ref({
    date_from: '',
    date_to: '',
    reason: '',
});

function filterByProperty() {
    const params = {};
    if (selectedPropertyFilter.value) params.property_id = selectedPropertyFilter.value;
    params.month = currentMonth.value.toISOString().slice(0, 7);
    router.get('/admin/bookings/calendar', params, { preserveState: true, replace: true });
}

function prevMonth() {
    const d = new Date(currentMonth.value);
    d.setMonth(d.getMonth() - 1);
    currentMonth.value = d;
    navigateMonth();
}

function nextMonth() {
    const d = new Date(currentMonth.value);
    d.setMonth(d.getMonth() + 1);
    currentMonth.value = d;
    navigateMonth();
}

function navigateMonth() {
    const params = {};
    if (selectedPropertyFilter.value) params.property_id = selectedPropertyFilter.value;
    params.month = currentMonth.value.toISOString().slice(0, 7);
    router.get('/admin/bookings/calendar', params, { preserveState: true, replace: true });
}

const monthLabel = computed(() => {
    return currentMonth.value.toLocaleDateString('pl-PL', { month: 'long', year: 'numeric' });
});

const weekDays = ['Pn', 'Wt', 'Sr', 'Cz', 'Pt', 'So', 'Nd'];

const calendarGrid = computed(() => {
    const year = currentMonth.value.getFullYear();
    const month = currentMonth.value.getMonth();

    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);

    // Monday = 0, Sunday = 6
    let startDow = firstDay.getDay() - 1;
    if (startDow < 0) startDow = 6;

    const weeks = [];
    let currentWeek = [];

    // Fill leading empty cells
    for (let i = 0; i < startDow; i++) {
        currentWeek.push(null);
    }

    for (let day = 1; day <= lastDay.getDate(); day++) {
        const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        currentWeek.push({
            day,
            date: dateStr,
            bookings: getBookingsForDate(dateStr),
            blocked: isBlocked(dateStr),
        });

        if (currentWeek.length === 7) {
            weeks.push(currentWeek);
            currentWeek = [];
        }
    }

    // Fill trailing empty cells
    if (currentWeek.length > 0) {
        while (currentWeek.length < 7) {
            currentWeek.push(null);
        }
        weeks.push(currentWeek);
    }

    return weeks;
});

function toDate(d) {
    if (!d) return '';
    return d.substring(0, 10);
}

function getBookingsForDate(dateStr) {
    if (!props.bookings) return [];
    return props.bookings.filter(b => {
        return dateStr >= toDate(b.check_in) && dateStr < toDate(b.check_out);
    });
}

function isBlocked(dateStr) {
    if (!props.blockedDates) return false;
    return props.blockedDates.some(bd => {
        return dateStr >= toDate(bd.date_from) && dateStr <= toDate(bd.date_to);
    });
}

function bookingBarClass(status) {
    const map = {
        confirmed: 'bg-green-500 text-white',
        pending: 'bg-yellow-400 text-yellow-900',
        checked_in: 'bg-blue-500 text-white',
        checked_out: 'bg-gray-400 text-white',
        cancelled: 'bg-red-400 text-white',
        no_show: 'bg-red-400 text-white',
    };
    return map[status] || 'bg-gray-400 text-white';
}

function openBlockModal(dateStr) {
    blockForm.value.date_from = dateStr;
    blockForm.value.date_to = dateStr;
    blockForm.value.reason = '';
    showBlockModal.value = true;
}

function submitBlock() {
    router.post('/admin/blocked-dates', {
        ...blockForm.value,
        property_id: selectedPropertyFilter.value || null,
    }, {
        preserveState: true,
        onSuccess: () => {
            showBlockModal.value = false;
        },
    });
}

const isToday = (dateStr) => {
    const today = new Date().toISOString().slice(0, 10);
    return dateStr === today;
};
</script>

<template>
    <AdminLayout>
        <div class="p-4 sm:p-6 lg:p-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ t('bookings.calendar') }}
                </h1>
            </div>

            <!-- Property filter -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-6">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                    {{ t('bookings.property') }}
                </label>
                <select
                    v-model="selectedPropertyFilter"
                    @change="filterByProperty"
                    class="mt-1 block w-full sm:w-64 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm px-3 py-2 border"
                >
                    <option value="">{{ t('bookings.allProperties') }}</option>
                    <option v-for="p in properties" :key="p.id" :value="p.id">
                        {{ p.name }}
                    </option>
                </select>
            </div>

            <!-- Calendar -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <!-- Month navigation -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <button
                        @click="prevMonth"
                        class="p-2 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                        </svg>
                    </button>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white capitalize">
                        {{ monthLabel }}
                    </h2>
                    <button
                        @click="nextMonth"
                        class="p-2 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </button>
                </div>

                <!-- Week day headers -->
                <div class="grid grid-cols-7 border-b border-gray-200 dark:border-gray-700">
                    <div
                        v-for="day in weekDays"
                        :key="day"
                        class="px-2 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase"
                    >
                        {{ day }}
                    </div>
                </div>

                <!-- Calendar grid -->
                <div v-for="(week, wi) in calendarGrid" :key="wi" class="grid grid-cols-7 border-b border-gray-200 dark:border-gray-700 last:border-b-0">
                    <div
                        v-for="(cell, ci) in week"
                        :key="ci"
                        :class="[
                            'min-h-[80px] sm:min-h-[100px] p-1 border-r border-gray-100 dark:border-gray-700 last:border-r-0',
                            cell && isToday(cell.date) ? 'bg-sky-50 dark:bg-sky-900/20' : '',
                        ]"
                    >
                        <template v-if="cell">
                            <div
                                class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-1 cursor-pointer hover:text-sky-600 dark:hover:text-sky-400"
                                @click="openBlockModal(cell.date)"
                            >
                                {{ cell.day }}
                            </div>

                            <!-- Blocked indicator -->
                            <div
                                v-if="cell.blocked"
                                class="text-[10px] leading-tight px-1 py-0.5 rounded bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400 truncate mb-0.5"
                            >
                                {{ t('bookings.blocked') }}
                            </div>

                            <!-- Booking indicators -->
                            <Link
                                v-for="booking in cell.bookings.slice(0, 3)"
                                :key="booking.id"
                                :href="`/admin/bookings/${booking.id}`"
                                :class="[
                                    'block text-[10px] leading-tight px-1 py-0.5 rounded truncate mb-0.5 hover:opacity-80',
                                    bookingBarClass(booking.status)
                                ]"
                                :title="booking.guest?.last_name + ' (' + booking.confirmation_code + ')'"
                            >
                                {{ booking.guest?.last_name }}
                            </Link>
                            <div
                                v-if="cell.bookings.length > 3"
                                class="text-[10px] text-gray-400 dark:text-gray-500 px-1"
                            >
                                +{{ cell.bookings.length - 3 }}
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Block date modal -->
            <div v-if="showBlockModal" class="fixed inset-0 z-50 flex items-center justify-center">
                <div class="fixed inset-0 bg-black/50" @click="showBlockModal = false" />
                <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 w-full max-w-md mx-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        {{ t('bookings.blockDates') }}
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ t('bookings.dateFrom') }}
                            </label>
                            <input
                                v-model="blockForm.date_from"
                                type="date"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm px-3 py-2 border"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ t('bookings.dateTo') }}
                            </label>
                            <input
                                v-model="blockForm.date_to"
                                type="date"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm px-3 py-2 border"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ t('bookings.reason') }}
                            </label>
                            <textarea
                                v-model="blockForm.reason"
                                rows="2"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm px-3 py-2 border"
                            />
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button
                            @click="showBlockModal = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg"
                        >
                            {{ t('bookings.cancel') }}
                        </button>
                        <button
                            @click="submitBlock"
                            class="bg-sky-600 hover:bg-sky-700 text-white rounded-lg px-4 py-2 text-sm font-medium"
                        >
                            {{ t('bookings.blockSave') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
