<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { computed } from 'vue';

const { t } = useI18n();

const props = defineProps({
    properties: Array,
});

const form = useForm({
    property_id: '',
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    check_in: '',
    check_out: '',
    guests_count: 1,
    status: 'pending',
    special_requests: '',
    admin_notes: '',
});

const selectedProperty = computed(() => {
    if (!form.property_id) return null;
    return props.properties.find(p => p.id === Number(form.property_id));
});

const nightsEstimate = computed(() => {
    if (!form.check_in || !form.check_out) return 0;
    const start = new Date(form.check_in);
    const end = new Date(form.check_out);
    const diff = Math.ceil((end - start) / (1000 * 60 * 60 * 24));
    return diff > 0 ? diff : 0;
});

const pricePreview = computed(() => {
    if (!selectedProperty.value || nightsEstimate.value <= 0) return null;
    const base = selectedProperty.value.base_price_per_night * nightsEstimate.value;
    const cleaning = selectedProperty.value.cleaning_fee || 0;
    return {
        base,
        cleaning,
        total: base + cleaning,
    };
});

const formatPrice = (grosze) => {
    return (grosze / 100).toFixed(2) + ' PLN';
};

function submit() {
    form.post('/admin/bookings');
}
</script>

<template>
    <AdminLayout>
        <div class="p-4 sm:p-6 lg:p-8">
            <!-- Header -->
            <div class="flex items-center gap-3 mb-6">
                <Link href="/admin/bookings" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                </Link>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ t('bookings.create') }}
                </h1>
            </div>

            <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main form -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Property -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            {{ t('bookings.property') }}
                        </h2>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ t('bookings.selectProperty') }}
                            </label>
                            <select
                                v-model="form.property_id"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm px-3 py-2 border"
                            >
                                <option value="">{{ t('bookings.chooseProperty') }}</option>
                                <option v-for="p in properties" :key="p.id" :value="p.id">
                                    {{ p.name }} ({{ formatPrice(p.base_price_per_night) }} / {{ t('bookings.night') }})
                                </option>
                            </select>
                            <p v-if="form.errors.property_id" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.property_id }}</p>
                        </div>
                    </div>

                    <!-- Guest details -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            {{ t('bookings.guestDetails') }}
                        </h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ t('guests.firstName') }}
                                </label>
                                <input
                                    v-model="form.first_name"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm px-3 py-2 border"
                                />
                                <p v-if="form.errors.first_name" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.first_name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ t('guests.lastName') }}
                                </label>
                                <input
                                    v-model="form.last_name"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm px-3 py-2 border"
                                />
                                <p v-if="form.errors.last_name" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.last_name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ t('guests.email') }}
                                </label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm px-3 py-2 border"
                                />
                                <p v-if="form.errors.email" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.email }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ t('guests.phone') }}
                                </label>
                                <input
                                    v-model="form.phone"
                                    type="tel"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm px-3 py-2 border"
                                />
                                <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.phone }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Booking details -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            {{ t('bookings.bookingDetails') }}
                        </h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ t('bookings.checkIn') }}
                                </label>
                                <input
                                    v-model="form.check_in"
                                    type="date"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm px-3 py-2 border"
                                />
                                <p v-if="form.errors.check_in" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.check_in }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ t('bookings.checkOut') }}
                                </label>
                                <input
                                    v-model="form.check_out"
                                    type="date"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm px-3 py-2 border"
                                />
                                <p v-if="form.errors.check_out" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.check_out }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ t('bookings.guestsCount') }}
                                </label>
                                <input
                                    v-model="form.guests_count"
                                    type="number"
                                    min="1"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm px-3 py-2 border"
                                />
                                <p v-if="form.errors.guests_count" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.guests_count }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ t('bookings.status') }}
                                </label>
                                <select
                                    v-model="form.status"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm px-3 py-2 border"
                                >
                                    <option value="pending">{{ t('bookings.statuses.pending') }}</option>
                                    <option value="confirmed">{{ t('bookings.statuses.confirmed') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ t('bookings.specialRequests') }}
                            </label>
                            <textarea
                                v-model="form.special_requests"
                                rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm px-3 py-2 border"
                            />
                        </div>
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ t('bookings.adminNotes') }}
                            </label>
                            <textarea
                                v-model="form.admin_notes"
                                rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm px-3 py-2 border"
                            />
                        </div>
                    </div>
                </div>

                <!-- Sidebar: Price preview + Submit -->
                <div class="space-y-6">
                    <!-- Price preview -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            {{ t('bookings.pricePreview') }}
                        </h2>
                        <div v-if="pricePreview" class="space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500 dark:text-gray-400">
                                    {{ formatPrice(selectedProperty.base_price_per_night) }} x {{ nightsEstimate }} {{ t('bookings.nights') }}
                                </span>
                                <span class="font-medium text-gray-900 dark:text-white">{{ formatPrice(pricePreview.base) }}</span>
                            </div>
                            <div v-if="pricePreview.cleaning > 0" class="flex justify-between text-sm">
                                <span class="text-gray-500 dark:text-gray-400">{{ t('bookings.cleaningFee') }}</span>
                                <span class="font-medium text-gray-900 dark:text-white">{{ formatPrice(pricePreview.cleaning) }}</span>
                            </div>
                            <div class="flex justify-between text-sm pt-2 border-t border-gray-100 dark:border-gray-700">
                                <span class="font-semibold text-gray-900 dark:text-white">{{ t('bookings.total') }}</span>
                                <span class="font-bold text-gray-900 dark:text-white">{{ formatPrice(pricePreview.total) }}</span>
                            </div>
                        </div>
                        <p v-else class="text-sm text-gray-500 dark:text-gray-400">
                            {{ t('bookings.selectPropertyAndDates') }}
                        </p>
                    </div>

                    <!-- Submit -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full bg-sky-600 hover:bg-sky-700 text-white rounded-lg px-4 py-2 text-sm font-medium disabled:opacity-50"
                    >
                        {{ t('bookings.createBooking') }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
