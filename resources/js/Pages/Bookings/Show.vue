<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { ref } from 'vue';

const { t } = useI18n();

const props = defineProps({
    booking: Object,
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

const statuses = [
    'pending',
    'confirmed',
    'checked_in',
    'checked_out',
    'cancelled',
    'no_show',
];

const statusForm = useForm({
    status: props.booking.status,
    cancellation_reason: '',
});

const showCancellationReason = ref(false);

function onStatusChange() {
    showCancellationReason.value = statusForm.status === 'cancelled';
}

function updateStatus() {
    statusForm.patch(`/admin/bookings/${props.booking.id}/status`, {
        preserveScroll: true,
    });
}

const paymentStatusBadgeClass = (status) => {
    const map = {
        completed: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
        pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
        failed: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
        refunded: 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400',
    };
    return map[status] || 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400';
};
</script>

<template>
    <AdminLayout>
        <div class="p-4 sm:p-6 lg:p-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <Link href="/admin/bookings" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                        </svg>
                    </Link>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ t('bookings.booking') }} {{ booking.confirmation_code }}
                    </h1>
                    <span
                        :class="[
                            'inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium',
                            statusBadgeClass(booking.status)
                        ]"
                    >
                        {{ t('bookings.statuses.' + booking.status) }}
                    </span>
                </div>
            </div>

            <!-- Two column layout -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Left: Booking details -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        {{ t('bookings.details') }}
                    </h2>
                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500 dark:text-gray-400">{{ t('bookings.property') }}</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ booking.property?.name }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500 dark:text-gray-400">{{ t('bookings.checkIn') }}</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ booking.check_in }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500 dark:text-gray-400">{{ t('bookings.checkOut') }}</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ booking.check_out }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500 dark:text-gray-400">{{ t('bookings.nights') }}</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ booking.nights }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500 dark:text-gray-400">{{ t('bookings.guestsCount') }}</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ booking.guests_count }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500 dark:text-gray-400">{{ t('bookings.source') }}</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ booking.source || '-' }}</dd>
                        </div>
                        <div v-if="booking.special_requests" class="pt-2 border-t border-gray-100 dark:border-gray-700">
                            <dt class="text-sm text-gray-500 dark:text-gray-400 mb-1">{{ t('bookings.specialRequests') }}</dt>
                            <dd class="text-sm text-gray-900 dark:text-white whitespace-pre-line">{{ booking.special_requests }}</dd>
                        </div>
                        <div v-if="booking.admin_notes" class="pt-2 border-t border-gray-100 dark:border-gray-700">
                            <dt class="text-sm text-gray-500 dark:text-gray-400 mb-1">{{ t('bookings.adminNotes') }}</dt>
                            <dd class="text-sm text-gray-900 dark:text-white whitespace-pre-line">{{ booking.admin_notes }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Right: Guest info + Financial summary -->
                <div class="space-y-6">
                    <!-- Guest info -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            {{ t('bookings.guestInfo') }}
                        </h2>
                        <dl class="space-y-3">
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500 dark:text-gray-400">{{ t('guests.name') }}</dt>
                                <dd class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ booking.guest?.first_name }} {{ booking.guest?.last_name }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500 dark:text-gray-400">{{ t('guests.email') }}</dt>
                                <dd class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ booking.guest?.email || '-' }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500 dark:text-gray-400">{{ t('guests.phone') }}</dt>
                                <dd class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ booking.guest?.phone || '-' }}
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Financial summary -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            {{ t('bookings.financialSummary') }}
                        </h2>
                        <dl class="space-y-3">
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500 dark:text-gray-400">{{ t('bookings.baseTotal') }}</dt>
                                <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ formatPrice(booking.base_total_price || 0) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500 dark:text-gray-400">{{ t('bookings.cleaningFee') }}</dt>
                                <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ formatPrice(booking.cleaning_fee || 0) }}</dd>
                            </div>
                            <div v-if="booking.discount_amount" class="flex justify-between">
                                <dt class="text-sm text-gray-500 dark:text-gray-400">{{ t('bookings.discount') }}</dt>
                                <dd class="text-sm font-medium text-green-600 dark:text-green-400">-{{ formatPrice(booking.discount_amount) }}</dd>
                            </div>
                            <div class="flex justify-between pt-2 border-t border-gray-100 dark:border-gray-700">
                                <dt class="text-sm font-semibold text-gray-900 dark:text-white">{{ t('bookings.total') }}</dt>
                                <dd class="text-sm font-bold text-gray-900 dark:text-white">{{ formatPrice(booking.total_price) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500 dark:text-gray-400">{{ t('bookings.deposit') }}</dt>
                                <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ formatPrice(booking.deposit_amount || 0) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500 dark:text-gray-400">{{ t('bookings.paidStatus') }}</dt>
                                <dd>
                                    <span
                                        v-if="booking.is_paid"
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400"
                                    >
                                        {{ t('bookings.paid') }}
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400"
                                    >
                                        {{ t('bookings.unpaid') }}
                                    </span>
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Status change -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    {{ t('bookings.changeStatus') }}
                </h2>
                <div class="flex flex-wrap items-end gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ t('bookings.newStatus') }}
                        </label>
                        <select
                            v-model="statusForm.status"
                            @change="onStatusChange"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm px-3 py-2 border"
                        >
                            <option v-for="s in statuses" :key="s" :value="s">
                                {{ t('bookings.statuses.' + s) }}
                            </option>
                        </select>
                    </div>
                    <div v-if="showCancellationReason" class="flex-1 min-w-[200px]">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ t('bookings.cancellationReason') }}
                        </label>
                        <textarea
                            v-model="statusForm.cancellation_reason"
                            rows="2"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm px-3 py-2 border"
                        />
                    </div>
                    <button
                        @click="updateStatus"
                        :disabled="statusForm.processing"
                        class="bg-sky-600 hover:bg-sky-700 text-white rounded-lg px-4 py-2 text-sm font-medium disabled:opacity-50"
                    >
                        {{ t('bookings.updateStatus') }}
                    </button>
                </div>
                <p v-if="statusForm.errors.status" class="mt-2 text-sm text-red-600 dark:text-red-400">
                    {{ statusForm.errors.status }}
                </p>
            </div>

            <!-- Payments -->
            <div v-if="booking.payments && booking.payments.length > 0" class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ t('bookings.payments') }}
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    {{ t('bookings.paymentAmount') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    {{ t('bookings.paymentMethod') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    {{ t('bookings.paymentType') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    {{ t('bookings.paymentStatus') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    {{ t('bookings.paidAt') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="payment in booking.payments" :key="payment.id">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">
                                    {{ formatPrice(payment.amount) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                    {{ payment.method }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                    {{ payment.type }}
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        :class="[
                                            'inline-flex items-center px-2 py-0.5 rounded text-xs font-medium',
                                            paymentStatusBadgeClass(payment.status)
                                        ]"
                                    >
                                        {{ payment.status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                    {{ payment.paid_at || '-' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
