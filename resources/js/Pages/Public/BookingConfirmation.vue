<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const { locale } = useI18n();

const props = defineProps({
    booking: { type: Object, required: true },
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
        month: 'long',
        year: 'numeric',
    });
}

const nights = computed(() => {
    if (!props.booking.check_in || !props.booking.check_out) return 0;
    const start = new Date(props.booking.check_in);
    const end = new Date(props.booking.check_out);
    return Math.round((end - start) / (1000 * 60 * 60 * 24));
});
</script>

<template>
    <PublicLayout>
        <div class="max-w-2xl mx-auto px-4 sm:px-6 py-10 sm:py-16">

            <!-- Success icon -->
            <div class="text-center mb-8">
                <div class="w-20 h-20 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center mx-auto mb-5">
                    <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                </div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white mb-2">
                    {{ locale === 'pl' ? 'Rezerwacja potwierdzona!' : 'Booking confirmed!' }}
                </h1>
                <p class="text-gray-500 dark:text-gray-400">
                    {{ locale === 'pl' ? 'Dziekujemy za rezerwacje' : 'Thank you for your booking' }}
                </p>
            </div>

            <!-- Confirmation code -->
            <div class="text-center mb-8">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">
                    {{ locale === 'pl' ? 'Kod potwierdzenia' : 'Confirmation code' }}
                </p>
                <div class="inline-block px-6 py-3 rounded-xl bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                    <span class="text-2xl sm:text-3xl font-mono font-bold text-gray-900 dark:text-white tracking-widest">
                        {{ booking.confirmation_code }}
                    </span>
                </div>
            </div>

            <!-- Booking details card -->
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 mb-8">
                <h2 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-4">
                    {{ locale === 'pl' ? 'Szczegoly rezerwacji' : 'Booking details' }}
                </h2>

                <div class="space-y-3">
                    <div v-if="booking.property" class="flex justify-between items-start">
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ locale === 'pl' ? 'Obiekt' : 'Property' }}</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white text-right">{{ booking.property.name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ locale === 'pl' ? 'Zameldowanie' : 'Check-in' }}</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ formatDate(booking.check_in) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ locale === 'pl' ? 'Wymeldowanie' : 'Check-out' }}</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ formatDate(booking.check_out) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ locale === 'pl' ? 'Nocy' : 'Nights' }}</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ nights }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ locale === 'pl' ? 'Gosci' : 'Guests' }}</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ booking.guests_count }}</span>
                    </div>
                    <div class="border-t border-gray-100 dark:border-gray-800 pt-3 flex justify-between">
                        <span class="text-base font-semibold text-gray-900 dark:text-white">{{ locale === 'pl' ? 'Razem' : 'Total' }}</span>
                        <span class="text-base font-bold text-gray-900 dark:text-white">{{ formatPrice(booking.total_price) }} PLN</span>
                    </div>
                </div>
            </div>

            <!-- Email note -->
            <div class="flex items-center gap-3 p-4 rounded-xl bg-sky-50 dark:bg-sky-900/20 border border-sky-200 dark:border-sky-800 mb-8">
                <svg class="w-5 h-5 text-sky-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                </svg>
                <p class="text-sm text-sky-800 dark:text-sky-200">
                    {{ locale === 'pl' ? 'Szczegoly wyslalismy na Twoj email' : 'We sent the details to your email' }}
                </p>
            </div>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row gap-3">
                <Link
                    v-if="booking.guest?.access_token"
                    :href="`/guest/${booking.guest.access_token}`"
                    class="flex-1 inline-flex items-center justify-center px-6 py-3 rounded-xl bg-sky-500 hover:bg-sky-600 text-white font-semibold text-sm transition"
                >
                    {{ locale === 'pl' ? 'Portal goscia' : 'Guest portal' }}
                </Link>
                <Link
                    v-if="booking.property?.slug"
                    :href="`/properties/${booking.property.slug}`"
                    class="flex-1 inline-flex items-center justify-center px-6 py-3 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 font-medium text-sm transition"
                >
                    {{ locale === 'pl' ? 'Wrocdo obiektu' : 'Back to property' }}
                </Link>
            </div>
        </div>
    </PublicLayout>
</template>
