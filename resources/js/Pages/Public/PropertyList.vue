<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const { t, locale } = useI18n();

const props = defineProps({
    properties: { type: Array, default: () => [] },
    brandName: { type: String, default: 'StayFlow' },
});

const isSingle = computed(() => props.properties.length === 1);

function formatPrice(cents) {
    return (cents / 100).toFixed(2).replace('.', ',');
}

function coverPhoto(property) {
    if (property.photos && property.photos.length > 0) {
        return property.photos[0].url || property.photos[0].path;
    }
    return null;
}

function propertyName(property) {
    if (locale.value === 'en' && property.name_en) return property.name_en;
    return property.name || property.name_pl || '';
}
</script>

<template>
    <PublicLayout>
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-8 sm:py-12">
            <!-- Single property: hero mode -->
            <template v-if="isSingle">
                <div class="relative rounded-2xl overflow-hidden bg-gray-100 dark:bg-gray-800 min-h-[400px] md:min-h-[500px]">
                    <img
                        v-if="coverPhoto(properties[0])"
                        :src="coverPhoto(properties[0])"
                        :alt="propertyName(properties[0])"
                        class="absolute inset-0 w-full h-full object-cover"
                    />
                    <div v-else class="absolute inset-0 flex items-center justify-center">
                        <svg class="w-20 h-20 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z" />
                        </svg>
                    </div>
                    <!-- Gradient overlay -->
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent" />
                    <!-- Content -->
                    <div class="relative z-10 flex flex-col justify-end h-full min-h-[400px] md:min-h-[500px] p-6 sm:p-10">
                        <h1 class="text-3xl sm:text-4xl md:text-5xl font-bold text-white mb-2">
                            {{ propertyName(properties[0]) }}
                        </h1>
                        <p v-if="properties[0].city" class="text-lg text-white/80 mb-1">
                            {{ properties[0].city }}
                        </p>
                        <p v-if="properties[0].base_price" class="text-white/70 mb-6">
                            {{ locale === 'pl' ? 'od' : 'from' }} {{ formatPrice(properties[0].base_price) }} PLN / {{ locale === 'pl' ? 'noc' : 'night' }}
                        </p>
                        <div v-if="properties[0].amenities && properties[0].amenities.length" class="flex flex-wrap gap-2 mb-6">
                            <span
                                v-for="amenity in properties[0].amenities.slice(0, 6)"
                                :key="amenity.id || amenity"
                                class="px-3 py-1 rounded-full bg-white/20 text-white text-xs font-medium backdrop-blur-sm"
                            >
                                {{ amenity.name || amenity }}
                            </span>
                        </div>
                        <Link
                            :href="`/properties/${properties[0].slug}`"
                            class="inline-flex items-center justify-center w-fit px-8 py-3.5 bg-sky-500 hover:bg-sky-600 text-white font-semibold rounded-xl transition shadow-lg shadow-sky-500/25"
                        >
                            {{ locale === 'pl' ? 'Zarezerwuj' : 'Book now' }}
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </Link>
                    </div>
                </div>
            </template>

            <!-- Multiple properties: grid -->
            <template v-else>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white mb-8">
                    {{ locale === 'pl' ? 'Nasze obiekty' : 'Our properties' }}
                </h1>

                <div v-if="properties.length === 0" class="text-center py-20">
                    <svg class="w-16 h-16 text-gray-300 dark:text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400">{{ locale === 'pl' ? 'Brak dostepnych obiektow' : 'No properties available' }}</p>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <Link
                        v-for="property in properties"
                        :key="property.id"
                        :href="`/properties/${property.slug}`"
                        class="group bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 overflow-hidden hover:shadow-lg hover:shadow-sky-500/5 transition-all duration-300"
                    >
                        <!-- Cover photo -->
                        <div class="aspect-[16/10] bg-gray-100 dark:bg-gray-800 relative overflow-hidden">
                            <img
                                v-if="coverPhoto(property)"
                                :src="coverPhoto(property)"
                                :alt="propertyName(property)"
                                class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                            />
                            <div v-else class="flex items-center justify-center h-full">
                                <svg class="w-12 h-12 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z" />
                                </svg>
                            </div>
                        </div>

                        <!-- Card content -->
                        <div class="p-5">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-1 group-hover:text-sky-600 dark:group-hover:text-sky-400 transition-colors">
                                {{ propertyName(property) }}
                            </h2>
                            <p v-if="property.city" class="text-sm text-gray-500 dark:text-gray-400 mb-3">
                                {{ property.city }}
                            </p>

                            <!-- Amenities -->
                            <div v-if="property.amenities && property.amenities.length" class="flex flex-wrap gap-1.5 mb-4">
                                <span
                                    v-for="amenity in property.amenities.slice(0, 4)"
                                    :key="amenity.id || amenity"
                                    class="px-2 py-0.5 rounded-md bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 text-xs"
                                >
                                    {{ amenity.name || amenity }}
                                </span>
                                <span
                                    v-if="property.amenities.length > 4"
                                    class="px-2 py-0.5 rounded-md bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-500 text-xs"
                                >
                                    +{{ property.amenities.length - 4 }}
                                </span>
                            </div>

                            <!-- Price + CTA -->
                            <div class="flex items-center justify-between">
                                <div v-if="property.base_price">
                                    <span class="text-lg font-bold text-gray-900 dark:text-white">{{ formatPrice(property.base_price) }} PLN</span>
                                    <span class="text-sm text-gray-500 dark:text-gray-400"> / {{ locale === 'pl' ? 'noc' : 'night' }}</span>
                                </div>
                                <span class="inline-flex items-center gap-1 text-sm font-medium text-sky-600 dark:text-sky-400 group-hover:gap-2 transition-all">
                                    {{ locale === 'pl' ? 'Zarezerwuj' : 'Book' }}
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </Link>
                </div>
            </template>
        </div>
    </PublicLayout>
</template>
