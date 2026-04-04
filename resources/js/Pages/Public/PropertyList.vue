<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const { t, locale } = useI18n();

const props = defineProps({
    properties: { type: Array, default: () => [] },
    brandName: { type: String, default: 'StayFlow' },
});

const isSingle = computed(() => props.properties.length === 1);

function formatPrice(cents) {
    return (cents / 100).toFixed(0);
}

function photoUrl(photo) {
    if (!photo) return '';
    if (photo.url) return photo.url;
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

// Icon SVG paths mapped to amenity icon slugs
const iconPaths = {
    wifi: 'M8.288 15.038a5.25 5.25 0 017.424 0M5.106 11.856c3.807-3.808 9.98-3.808 13.788 0M1.924 8.674c5.565-5.565 14.587-5.565 20.152 0M12.53 18.22l-.53.53-.53-.53a.75.75 0 011.06 0z',
    car: 'M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12',
    snowflake: 'M12 3v18m0-18l-3 3m3-3l3 3m-3 15l-3-3m3 3l3-3M3 12h18M3 12l3-3m-3 3l3 3m15-3l-3-3m3 3l-3 3',
    utensils: 'M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12',
    tv: 'M6 20.25h12m-7.5-3v3m3-3v3m-10.125-3h17.25c.621 0 1.125-.504 1.125-1.125V4.875c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125z',
    pool: 'M12 6v6m0 0c-2.5 0-4.5 2-4.5 4.5M12 12c2.5 0 4.5 2 4.5 4.5M3 17.25C3.75 18 5.25 19.5 6.75 19.5s3-1.5 3.75-2.25c.75.75 2.25 2.25 3.75 2.25s3-1.5 3.75-2.25',
    tree: 'M12 21v-6m0 0l-4-6h8l-4 6zm0-6l-3-5h6l-3 5zm0-5l-2-4h4l-2 4z',
    flame: 'M15.362 5.214A8.252 8.252 0 0112 21 8.25 8.25 0 016.038 7.048 8.287 8.287 0 009 9.6a8.983 8.983 0 013.361-6.867 8.21 8.21 0 003 2.48z',
    paw: 'M6.633 10.5c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 012.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 00.322-1.672V3.75a.75.75 0 01.75-.75A2.25 2.25 0 0116.5 5.25c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 01-2.649 7.521c-.388.482-.987.729-1.605.729H13.48a4.53 4.53 0 01-1.423-.23l-3.114-1.04a4.501 4.501 0 00-1.423-.23H5.904M14.25 9h2.25M5.904 18.75c.083.205.173.405.27.602.197.4-.078.898-.523.898h-.908c-.889 0-1.713-.518-1.972-1.368a12 12 0 01-.521-3.507c0-1.553.295-3.036.831-4.398C3.387 10.07 4.167 9.75 5 9.75h1.053c.472 0 .745.556.5.96a8.958 8.958 0 00-1.302 4.665c0 1.194.232 2.333.654 3.375z',
    baby: 'M15.182 15.182a4.5 4.5 0 01-6.364 0M21 12a9 9 0 11-18 0 9 9 0 0118 0zM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75zm-.375 0h.008v.015h-.008V9.75zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75zm-.375 0h.008v.015h-.008V9.75z',
    iron: 'M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5',
    wind: 'M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25',
    towel: 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v6a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 12V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25v2.25A2.25 2.25 0 018.25 20.25H6a2.25 2.25 0 01-2.25-2.25v-2.25z',
    bed: 'M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25',
    dish: 'M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714a2.25 2.25 0 00.659 1.591L19 14.5M14.25 3.104c.251.023.501.05.75.082M19 14.5l-2 4.5H7l-2-4.5',
    microwave: 'M3.75 7.5h16.5M3.75 7.5A2.25 2.25 0 011.5 5.25 2.25 2.25 0 013.75 3h16.5A2.25 2.25 0 0122.5 5.25a2.25 2.25 0 01-2.25 2.25m-16.5 0v9A2.25 2.25 0 006 18.75h12A2.25 2.25 0 0020.25 16.5v-9m-16.5 0h16.5',
    coffee: 'M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z',
    balcony: 'M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z',
    elevator: 'M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M8.25 12l2.25-2.25L12.75 12m0 3l-2.25 2.25L8.25 15',
    lock: 'M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z',
    alarm: 'M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0',
    'first-aid': 'M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z',
    washer: 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',
};

function getIconPath(icon) {
    return iconPaths[icon] || 'M4.5 12.75l6 6 9-13.5';
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

                <div class="grid gap-8 md:grid-cols-2">
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
