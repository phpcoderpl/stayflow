<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const { locale } = useI18n();

const props = defineProps({
    guest: { type: Object, required: true },
    brandName: { type: String, default: 'StayFlow' },
});

function formatPrice(cents) {
    if (!cents && cents !== 0) return '0,00';
    return (cents / 100).toFixed(2).replace('.', ',');
}

function formatDate(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return d.toLocaleDateString(locale.value === 'pl' ? 'pl-PL' : 'en-GB', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
}

function nightsCount(booking) {
    if (!booking.check_in || !booking.check_out) return 0;
    const start = new Date(booking.check_in);
    const end = new Date(booking.check_out);
    return Math.round((end - start) / (1000 * 60 * 60 * 24));
}

const statusConfig = {
    pending: { label_pl: 'Oczekujaca', label_en: 'Pending', color: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' },
    confirmed: { label_pl: 'Potwierdzona', label_en: 'Confirmed', color: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' },
    cancelled: { label_pl: 'Anulowana', label_en: 'Cancelled', color: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' },
    completed: { label_pl: 'Zakonczona', label_en: 'Completed', color: 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400' },
    checked_in: { label_pl: 'Zameldowany', label_en: 'Checked in', color: 'bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-300' },
};

function getStatus(status) {
    return statusConfig[status] || statusConfig.pending;
}

function statusLabel(status) {
    const cfg = getStatus(status);
    return locale.value === 'pl' ? cfg.label_pl : cfg.label_en;
}

const bookings = computed(() => props.guest.bookings || []);
</script>

<template>
    <PublicLayout>
        <div class="max-w-3xl mx-auto px-4 sm:px-6 py-8 sm:py-12">

            <!-- Welcome -->
            <div class="mb-8">
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white mb-1">
                    {{ locale === 'pl' ? 'Witaj' : 'Welcome' }}, {{ guest.first_name }}!
                </h1>
                <p class="text-gray-500 dark:text-gray-400">
                    {{ locale === 'pl' ? 'Twoje rezerwacje' : 'Your bookings' }}
                </p>
            </div>

            <!-- Empty state -->
            <div v-if="bookings.length === 0" class="text-center py-16">
                <div class="w-16 h-16 rounded-2xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                    </svg>
                </div>
                <p class="text-gray-500 dark:text-gray-400 font-medium">
                    {{ locale === 'pl' ? 'Nie masz jeszcze zadnych rezerwacji' : 'No bookings yet' }}
                </p>
            </div>

            <!-- Bookings list -->
            <div class="space-y-4">
                <div
                    v-for="booking in bookings"
                    :key="booking.id"
                    class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5 sm:p-6"
                >
                    <!-- Top row: property name + status -->
                    <div class="flex items-start justify-between gap-3 mb-4">
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white">
                                {{ booking.property?.name || '—' }}
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 font-mono">
                                {{ booking.confirmation_code }}
                            </p>
                        </div>
                        <span :class="['px-3 py-1 rounded-full text-xs font-medium whitespace-nowrap', getStatus(booking.status).color]">
                            {{ statusLabel(booking.status) }}
                        </span>
                    </div>

                    <!-- Details grid -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                        <div>
                            <span class="block text-xs text-gray-500 dark:text-gray-400 mb-0.5">{{ locale === 'pl' ? 'Zameldowanie' : 'Check-in' }}</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ formatDate(booking.check_in) }}</span>
                        </div>
                        <div>
                            <span class="block text-xs text-gray-500 dark:text-gray-400 mb-0.5">{{ locale === 'pl' ? 'Wymeldowanie' : 'Check-out' }}</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ formatDate(booking.check_out) }}</span>
                        </div>
                        <div>
                            <span class="block text-xs text-gray-500 dark:text-gray-400 mb-0.5">{{ locale === 'pl' ? 'Nocy' : 'Nights' }}</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ nightsCount(booking) }}</span>
                        </div>
                        <div>
                            <span class="block text-xs text-gray-500 dark:text-gray-400 mb-0.5">{{ locale === 'pl' ? 'Razem' : 'Total' }}</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ formatPrice(booking.total_price) }} PLN</span>
                        </div>
                    </div>

                    <!-- Deposit indicator -->
                    <div v-if="booking.deposit_paid" class="mt-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-xs text-green-600 dark:text-green-400 font-medium">
                            {{ locale === 'pl' ? 'Zaliczka oplacona' : 'Deposit paid' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
