<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const { locale } = useI18n();

const props = defineProps({
    booking: { type: Object, required: true },
    depositPercent: { type: Number, default: 30 },
    brandName: { type: String, default: 'StayFlow' },
    paymentProvider: { type: String, default: 'hotpay' },
});

function formatPrice(cents) {
    if (!cents && cents !== 0) return '0,00';
    return (cents / 100).toFixed(2).replace('.', ',');
}

const nights = computed(() => {
    if (!props.booking.check_in || !props.booking.check_out) return 0;
    const start = new Date(props.booking.check_in);
    const end = new Date(props.booking.check_out);
    return Math.round((end - start) / (1000 * 60 * 60 * 24));
});

const depositAmount = computed(() => {
    return Math.round((props.booking.total_price || 0) * props.depositPercent / 100);
});

function formatDate(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return d.toLocaleDateString(locale.value === 'pl' ? 'pl-PL' : 'en-GB', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
}

const depositForm = useForm({
    booking_id: props.booking.id,
    payment_type: 'deposit',
});

const fullForm = useForm({
    booking_id: props.booking.id,
    payment_type: 'full',
});

const checkoutUrl = computed(() => `/${props.paymentProvider}/checkout`);

function payDeposit() {
    depositForm.post(checkoutUrl.value);
}

function payFull() {
    fullForm.post(checkoutUrl.value);
}
</script>

<template>
    <PublicLayout>
        <div class="max-w-2xl mx-auto px-4 sm:px-6 py-8 sm:py-14">

            <!-- Heading -->
            <div class="text-center mb-8">
                <div class="w-14 h-14 rounded-2xl bg-sky-100 dark:bg-sky-900/30 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7 text-sky-600 dark:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                    </svg>
                </div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white mb-2">
                    {{ locale === 'pl' ? 'Platnosc' : 'Payment' }}
                </h1>
                <p class="text-gray-500 dark:text-gray-400 text-sm">
                    {{ locale === 'pl' ? 'Wybierz forme platnosci za rezerwacje' : 'Choose your payment option' }}
                </p>
            </div>

            <!-- Booking summary card -->
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 mb-8">
                <h2 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-4">
                    {{ locale === 'pl' ? 'Podsumowanie rezerwacji' : 'Booking summary' }}
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

            <!-- Payment options -->
            <div class="space-y-4">
                <!-- Deposit -->
                <button
                    @click="payDeposit"
                    :disabled="depositForm.processing"
                    class="w-full p-5 rounded-2xl border-2 border-sky-200 dark:border-sky-800 bg-sky-50 dark:bg-sky-900/20 hover:border-sky-400 dark:hover:border-sky-600 transition text-left group disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-semibold text-gray-900 dark:text-white mb-1">
                                {{ locale === 'pl' ? `Zaplac zaliczke (${depositPercent}%)` : `Pay deposit (${depositPercent}%)` }}
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ locale === 'pl' ? 'Reszte zaplacisz przed przyjazdem' : 'Pay the rest before arrival' }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-xl font-bold text-sky-600 dark:text-sky-400">{{ formatPrice(depositAmount) }} PLN</p>
                            <svg v-if="depositForm.processing" class="w-5 h-5 animate-spin text-sky-500 ml-auto mt-1" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                            </svg>
                        </div>
                    </div>
                </button>

                <!-- Full payment -->
                <button
                    @click="payFull"
                    :disabled="fullForm.processing"
                    class="w-full p-5 rounded-2xl border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 hover:border-gray-300 dark:hover:border-gray-600 transition text-left group disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-semibold text-gray-900 dark:text-white mb-1">
                                {{ locale === 'pl' ? 'Zaplac calosc' : 'Pay in full' }}
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ locale === 'pl' ? 'Jednorazowa platnosc za caly pobyt' : 'One-time payment for your entire stay' }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-xl font-bold text-gray-900 dark:text-white">{{ formatPrice(booking.total_price) }} PLN</p>
                            <svg v-if="fullForm.processing" class="w-5 h-5 animate-spin text-sky-500 ml-auto mt-1" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                            </svg>
                        </div>
                    </div>
                </button>
            </div>
        </div>
    </PublicLayout>
</template>
