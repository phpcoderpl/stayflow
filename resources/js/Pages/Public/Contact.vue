<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const { locale } = useI18n();

const props = defineProps({
    contact: { type: Object, default: () => ({}) },
    brandName: { type: String, default: 'StayFlow' },
});

const description = computed(() => {
    if (locale.value === 'en' && props.contact.description_en) return props.contact.description_en;
    return props.contact.description_pl || '';
});

const photoUrl = computed(() => {
    if (!props.contact.photo) return null;
    if (props.contact.photo.startsWith('http')) return props.contact.photo;
    return `/storage/${props.contact.photo}`;
});

const hasAnyContact = computed(() => {
    return props.contact.name || props.contact.email || props.contact.phone || props.contact.address;
});
</script>

<template>
    <PublicLayout>
        <div class="max-w-3xl mx-auto px-4 sm:px-6 py-10 sm:py-16">

            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white mb-8 text-center">
                {{ locale === 'pl' ? 'Kontakt' : 'Contact' }}
            </h1>

            <!-- Owner card -->
            <div v-if="hasAnyContact" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden mb-8">
                <div class="p-6 sm:p-8">
                    <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6">
                        <!-- Profile photo -->
                        <div class="shrink-0">
                            <div v-if="photoUrl" class="w-28 h-28 sm:w-32 sm:h-32 rounded-2xl overflow-hidden shadow-lg">
                                <img :src="photoUrl" :alt="contact.name" class="w-full h-full object-cover" />
                            </div>
                            <div v-else class="w-28 h-28 sm:w-32 sm:h-32 rounded-2xl bg-sky-100 dark:bg-sky-900/30 flex items-center justify-center">
                                <svg class="w-12 h-12 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                </svg>
                            </div>
                        </div>

                        <!-- Info -->
                        <div class="flex-1 text-center sm:text-left">
                            <h2 v-if="contact.name" class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white mb-2">
                                {{ contact.name }}
                            </h2>

                            <p v-if="description" class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed mb-5 whitespace-pre-line">
                                {{ description }}
                            </p>

                            <!-- Contact details -->
                            <div class="space-y-3">
                                <a
                                    v-if="contact.email"
                                    :href="`mailto:${contact.email}`"
                                    class="flex items-center gap-3 text-gray-700 dark:text-gray-300 hover:text-sky-600 dark:hover:text-sky-400 transition group"
                                >
                                    <div class="w-10 h-10 rounded-xl bg-sky-50 dark:bg-sky-900/20 flex items-center justify-center group-hover:bg-sky-100 dark:group-hover:bg-sky-900/40 transition">
                                        <svg class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                                        </svg>
                                    </div>
                                    <span class="text-sm font-medium">{{ contact.email }}</span>
                                </a>

                                <a
                                    v-if="contact.phone"
                                    :href="`tel:${contact.phone}`"
                                    class="flex items-center gap-3 text-gray-700 dark:text-gray-300 hover:text-sky-600 dark:hover:text-sky-400 transition group"
                                >
                                    <div class="w-10 h-10 rounded-xl bg-sky-50 dark:bg-sky-900/20 flex items-center justify-center group-hover:bg-sky-100 dark:group-hover:bg-sky-900/40 transition">
                                        <svg class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                                        </svg>
                                    </div>
                                    <span class="text-sm font-medium">{{ contact.phone }}</span>
                                </a>

                                <div
                                    v-if="contact.address"
                                    class="flex items-center gap-3 text-gray-700 dark:text-gray-300"
                                >
                                    <div class="w-10 h-10 rounded-xl bg-sky-50 dark:bg-sky-900/20 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                        </svg>
                                    </div>
                                    <span class="text-sm font-medium">{{ contact.address }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Trust badges -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
                <div class="flex flex-col items-center gap-2 p-5 rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-800">
                    <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                    </svg>
                    <span class="text-sm font-semibold text-gray-900 dark:text-white">
                        {{ locale === 'pl' ? 'Bezpieczne platnosci' : 'Secure payments' }}
                    </span>
                    <span class="text-xs text-gray-500 dark:text-gray-400 text-center">
                        {{ locale === 'pl' ? 'PayU — BLIK, karty, przelewy' : 'PayU — BLIK, cards, transfers' }}
                    </span>
                </div>
                <div class="flex flex-col items-center gap-2 p-5 rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-800">
                    <svg class="w-8 h-8 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
                    </svg>
                    <span class="text-sm font-semibold text-gray-900 dark:text-white">
                        {{ locale === 'pl' ? 'Bezposredni kontakt' : 'Direct contact' }}
                    </span>
                    <span class="text-xs text-gray-500 dark:text-gray-400 text-center">
                        {{ locale === 'pl' ? 'Bez posrednikow, bez prowizji' : 'No middlemen, no commissions' }}
                    </span>
                </div>
                <div class="flex flex-col items-center gap-2 p-5 rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-800">
                    <svg class="w-8 h-8 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                    </svg>
                    <span class="text-sm font-semibold text-gray-900 dark:text-white">
                        {{ locale === 'pl' ? 'Potwierdzenie natychmiast' : 'Instant confirmation' }}
                    </span>
                    <span class="text-xs text-gray-500 dark:text-gray-400 text-center">
                        {{ locale === 'pl' ? 'Rezerwacja potwierdzona od razu' : 'Booking confirmed immediately' }}
                    </span>
                </div>
            </div>

            <!-- Terms link -->
            <div class="text-center">
                <Link href="/terms" class="text-sm text-sky-600 dark:text-sky-400 hover:underline">
                    {{ locale === 'pl' ? 'Regulamin rezerwacji' : 'Terms & conditions' }}
                </Link>
            </div>
        </div>
    </PublicLayout>
</template>
