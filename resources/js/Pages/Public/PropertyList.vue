<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { getIconPath, isEmoji } from '@/composables/amenityIcons.js';

const { t, locale } = useI18n();

const props = defineProps({
    properties: { type: Array, default: () => [] },
    brandName: { type: String, default: 'StayFlow' },
    listLayout: { type: String, default: 'auto' },
});


const isSingle = computed(() => props.properties.length === 1);

const gridClass = computed(() => {
    const count = props.properties.length;
    switch (props.listLayout) {
        case '1': return '';
        case '2': return 'md:grid-cols-2';
        case '3': return 'md:grid-cols-2 lg:grid-cols-3';
        default: // 'auto'
            if (count === 1) return '';
            if (count === 2) return 'md:grid-cols-2';
            return 'md:grid-cols-2 lg:grid-cols-3';
    }
});

function formatPrice(cents) {
    return (cents / 100).toFixed(0);
}

function photoUrl(photo) {
    if (!photo) return '';
    if (photo.url) return photo.url;
    if (photo.path && photo.path.startsWith('http')) return photo.path;
    if (photo.path) return `/storage/${photo.path}`;
    return '';
}

function propertyName(property) {
    if (locale.value === 'en' && property.name_en) return property.name_en;
    return property.name || property.name_pl || '';
}

function amenityName(amenity) {
    return locale.value === 'en' && amenity.name_en ? amenity.name_en : amenity.name_pl || amenity;
}

// Hero carousel state (for single property)
const heroIndex = ref(0);

const heroPhotos = computed(() => {
    if (!props.properties.length) return [];
    return props.properties[0].photos || [];
});

function heroNext() {
    if (heroPhotos.value.length === 0) return;
    heroIndex.value = (heroIndex.value + 1) % heroPhotos.value.length;
}

function heroPrev() {
    if (heroPhotos.value.length === 0) return;
    heroIndex.value = (heroIndex.value - 1 + heroPhotos.value.length) % heroPhotos.value.length;
}

// Multi-property carousel state (per property)
const cardIndexes = ref({});

function cardIndex(propertyId) {
    return cardIndexes.value[propertyId] || 0;
}

function cardNext(property) {
    const photos = property.photos || [];
    if (photos.length === 0) return;
    const current = cardIndexes.value[property.id] || 0;
    cardIndexes.value[property.id] = (current + 1) % photos.length;
}

function cardPrev(property) {
    const photos = property.photos || [];
    if (photos.length === 0) return;
    const current = cardIndexes.value[property.id] || 0;
    cardIndexes.value[property.id] = (current - 1 + photos.length) % photos.length;
}
</script>

<template>
    <PublicLayout>
        <!-- Single property: immersive hero -->
        <template v-if="isSingle">
            <!-- Full-width hero carousel -->
            <div class="relative bg-gray-900 overflow-hidden" style="min-height: 70vh;">
                <!-- Photo -->
                <transition name="fade" mode="out-in">
                    <img
                        v-if="heroPhotos.length"
                        :key="heroIndex"
                        :src="photoUrl(heroPhotos[heroIndex])"
                        :alt="propertyName(properties[0])"
                        class="absolute inset-0 w-full h-full object-cover"
                    />
                </transition>
                <div v-if="!heroPhotos.length" class="absolute inset-0 flex items-center justify-center bg-gray-800">
                    <svg class="w-24 h-24 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z" />
                    </svg>
                </div>

                <!-- Gradient overlay -->
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-black/10" />

                <!-- Arrow buttons -->
                <template v-if="heroPhotos.length > 1">
                    <button
                        @click="heroPrev"
                        class="absolute left-4 top-1/2 -translate-y-1/2 z-20 w-11 h-11 rounded-full bg-black/40 hover:bg-black/60 backdrop-blur-sm text-white flex items-center justify-center transition"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                        </svg>
                    </button>
                    <button
                        @click="heroNext"
                        class="absolute right-4 top-1/2 -translate-y-1/2 z-20 w-11 h-11 rounded-full bg-black/40 hover:bg-black/60 backdrop-blur-sm text-white flex items-center justify-center transition"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </button>
                </template>

                <!-- Dots indicator -->
                <div v-if="heroPhotos.length > 1" class="absolute bottom-28 sm:bottom-32 left-1/2 -translate-x-1/2 z-20 flex gap-2">
                    <button
                        v-for="(_, i) in heroPhotos"
                        :key="i"
                        @click="heroIndex = i"
                        :class="[
                            'w-2.5 h-2.5 rounded-full transition-all',
                            heroIndex === i ? 'bg-white scale-110' : 'bg-white/40 hover:bg-white/60'
                        ]"
                    />
                </div>

                <!-- Content overlay -->
                <div class="absolute inset-x-0 bottom-0 z-10 p-6 sm:p-10 md:p-14">
                    <div class="max-w-4xl mx-auto">
                        <p v-if="properties[0].city" class="text-white/70 text-sm sm:text-base font-medium tracking-wide uppercase mb-2">
                            <svg class="w-4 h-4 inline -mt-0.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                            </svg>
                            {{ properties[0].city }}
                        </p>
                        <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-3 leading-tight">
                            {{ propertyName(properties[0]) }}
                        </h1>

                        <!-- Quick stats -->
                        <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-white/80 text-sm sm:text-base mb-5">
                            <span v-if="properties[0].bedrooms" class="flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
                                {{ properties[0].bedrooms }} {{ locale === 'pl' ? 'sypialnie' : 'bedrooms' }}
                            </span>
                            <span v-if="properties[0].max_guests" class="flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
                                {{ locale === 'pl' ? 'do' : 'up to' }} {{ properties[0].max_guests }} {{ locale === 'pl' ? 'osob' : 'guests' }}
                            </span>
                            <span v-if="properties[0].area_sqm" class="flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" /></svg>
                                {{ properties[0].area_sqm }} m&sup2;
                            </span>
                        </div>

                        <!-- Price + CTA -->
                        <div class="flex flex-wrap items-center gap-4">
                            <Link
                                :href="`/properties/${properties[0].slug}`"
                                class="inline-flex items-center justify-center px-8 py-4 bg-sky-500 hover:bg-sky-600 text-white font-bold text-base rounded-xl transition shadow-lg shadow-sky-500/30 hover:shadow-sky-500/50"
                            >
                                {{ locale === 'pl' ? 'Zarezerwuj teraz' : 'Book now' }}
                                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                </svg>
                            </Link>
                            <div v-if="properties[0].base_price_per_night" class="text-white">
                                <span class="text-2xl sm:text-3xl font-bold">{{ formatPrice(properties[0].base_price_per_night) }} PLN</span>
                                <span class="text-white/60 text-sm ml-1">/ {{ locale === 'pl' ? 'noc' : 'night' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Amenities strip below hero — icons with tooltips -->
            <div v-if="properties[0].amenities && properties[0].amenities.length" class="bg-white dark:bg-gray-900 border-b border-gray-100 dark:border-gray-800">
                <div class="max-w-5xl mx-auto px-4 sm:px-6 py-5">
                    <div class="flex flex-wrap justify-center gap-3 sm:gap-4">
                        <div
                            v-for="amenity in properties[0].amenities"
                            :key="amenity.id"
                            class="group relative flex flex-col items-center gap-1"
                        >
                            <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-sky-50 dark:bg-sky-900/20 flex items-center justify-center group-hover:bg-sky-100 dark:group-hover:bg-sky-900/40 transition">
                                <svg class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" :d="getIconPath(amenity.icon)" />
                                </svg>
                            </div>
                            <span class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400 text-center leading-tight max-w-[60px] truncate">
                                {{ amenityName(amenity) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- Multiple properties: attractive grid -->
        <template v-else>
            <div class="max-w-6xl mx-auto px-4 sm:px-6 py-8 sm:py-12">
                <div class="text-center mb-10">
                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-2">
                        {{ locale === 'pl' ? 'Nasze obiekty' : 'Our properties' }}
                    </h1>
                    <p class="text-gray-500 dark:text-gray-400">
                        {{ locale === 'pl' ? 'Wybierz idealny apartament na swoj pobyt' : 'Choose the perfect apartment for your stay' }}
                    </p>
                </div>

                <div v-if="properties.length === 0" class="text-center py-20">
                    <svg class="w-16 h-16 text-gray-300 dark:text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400">{{ locale === 'pl' ? 'Brak dostepnych obiektow' : 'No properties available' }}</p>
                </div>

                <div :class="['grid gap-8', gridClass]">
                    <div
                        v-for="property in properties"
                        :key="property.id"
                        class="group bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 overflow-hidden hover:shadow-xl hover:shadow-sky-500/10 transition-all duration-300"
                    >
                        <!-- Photo carousel -->
                        <div class="aspect-[16/10] bg-gray-100 dark:bg-gray-800 relative overflow-hidden">
                            <img
                                v-if="property.photos && property.photos.length"
                                :src="photoUrl(property.photos[cardIndex(property.id)])"
                                :alt="propertyName(property)"
                                class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                            />
                            <div v-else class="flex items-center justify-center h-full">
                                <svg class="w-12 h-12 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z" />
                                </svg>
                            </div>

                            <!-- Carousel arrows -->
                            <template v-if="property.photos && property.photos.length > 1">
                                <button
                                    @click.prevent="cardPrev(property)"
                                    class="absolute left-2 top-1/2 -translate-y-1/2 z-10 w-8 h-8 rounded-full bg-black/40 hover:bg-black/60 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                                    </svg>
                                </button>
                                <button
                                    @click.prevent="cardNext(property)"
                                    class="absolute right-2 top-1/2 -translate-y-1/2 z-10 w-8 h-8 rounded-full bg-black/40 hover:bg-black/60 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                    </svg>
                                </button>
                                <!-- Dots -->
                                <div class="absolute bottom-2 left-1/2 -translate-x-1/2 z-10 flex gap-1.5">
                                    <span
                                        v-for="(_, i) in property.photos.slice(0, 5)"
                                        :key="i"
                                        :class="[
                                            'w-1.5 h-1.5 rounded-full transition',
                                            cardIndex(property.id) === i ? 'bg-white' : 'bg-white/40'
                                        ]"
                                    />
                                    <span v-if="property.photos.length > 5" class="w-1.5 h-1.5 rounded-full bg-white/40" />
                                </div>
                            </template>

                            <!-- Price badge -->
                            <div v-if="property.base_price_per_night" class="absolute top-3 right-3 z-10 px-3 py-1.5 rounded-lg bg-black/50 backdrop-blur-sm text-white text-sm font-bold">
                                {{ formatPrice(property.base_price_per_night) }} PLN<span class="font-normal text-white/70 text-xs"> / {{ locale === 'pl' ? 'noc' : 'night' }}</span>
                            </div>
                        </div>

                        <!-- Card content -->
                        <Link :href="`/properties/${property.slug}`" class="block p-5">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-1 group-hover:text-sky-600 dark:group-hover:text-sky-400 transition-colors">
                                {{ propertyName(property) }}
                            </h2>

                            <!-- Location + stats -->
                            <div class="flex items-center gap-3 text-sm text-gray-500 dark:text-gray-400 mb-3">
                                <span v-if="property.city" class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                    </svg>
                                    {{ property.city }}
                                </span>
                                <span v-if="property.bedrooms">{{ property.bedrooms }} {{ locale === 'pl' ? 'syp.' : 'bed.' }}</span>
                                <span v-if="property.max_guests">{{ locale === 'pl' ? 'do' : 'up to' }} {{ property.max_guests }} {{ locale === 'pl' ? 'os.' : 'guests' }}</span>
                            </div>

                            <!-- Amenities -->
                            <div v-if="property.amenities && property.amenities.length" class="flex flex-wrap gap-1.5 mb-4">
                                <span
                                    v-for="amenity in property.amenities.slice(0, 5)"
                                    :key="amenity.id || amenity"
                                    class="px-2 py-0.5 rounded-md bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 text-xs"
                                >
                                    {{ amenityName(amenity) }}
                                </span>
                                <span
                                    v-if="property.amenities.length > 5"
                                    class="px-2 py-0.5 rounded-md bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-500 text-xs"
                                >
                                    +{{ property.amenities.length - 5 }}
                                </span>
                            </div>

                            <!-- CTA -->
                            <div class="flex items-center justify-between">
                                <span class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-sky-50 dark:bg-sky-900/20 text-sky-600 dark:text-sky-400 text-sm font-semibold group-hover:bg-sky-500 group-hover:text-white dark:group-hover:bg-sky-500 transition-all">
                                    {{ locale === 'pl' ? 'Zarezerwuj' : 'Book now' }}
                                    <svg class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                    </svg>
                                </span>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
        </template>
    </PublicLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.4s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
