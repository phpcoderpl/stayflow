<script setup>
import { ref, computed, reactive, onMounted, nextTick } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import axios from 'axios';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { getIconPath, isEmoji } from '@/composables/amenityIcons.js';

const { t, locale } = useI18n();

const props = defineProps({
    property: { type: Object, required: true },
    unavailableDates: { type: Object, default: () => ({}) },
    brandName: { type: String, default: 'StayFlow' },
});

// Photo gallery
const selectedPhotoIndex = ref(0);

const photos = computed(() => props.property.photos || []);
const mainPhoto = computed(() => photos.value[selectedPhotoIndex.value] || null);

function photoUrl(photo) {
    if (!photo) return '';
    if (photo.url) return photo.url;
    if (photo.path && photo.path.startsWith('http')) return photo.path;
    if (photo.path) return `/storage/${photo.path}`;
    return '';
}

// Localized description
const description = computed(() => {
    if (locale.value === 'en' && props.property.description_en) {
        return props.property.description_en;
    }
    return props.property.description_pl || props.property.description || '';
});

// Video embed
const videoEmbedUrl = computed(() => {
    const url = props.property.video_url;
    if (!url) return null;

    // YouTube
    const ytMatch = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})/);
    if (ytMatch) return `https://www.youtube.com/embed/${ytMatch[1]}`;

    // Vimeo
    const vimeoMatch = url.match(/(?:vimeo\.com\/)(\d+)/);
    if (vimeoMatch) return `https://player.vimeo.com/video/${vimeoMatch[1]}`;

    return null;
});

// Availability check
const checkForm = reactive({
    check_in: '',
    check_out: '',
    guests_count: 1,
});

const checking = ref(false);
const checkResult = ref(null);
const checkError = ref(null);

async function checkAvailability() {
    checking.value = true;
    checkResult.value = null;
    checkError.value = null;

    try {
        const response = await axios.post('/bookings/check-availability', {
            property_id: props.property.id,
            check_in: checkForm.check_in,
            check_out: checkForm.check_out,
        });
        checkResult.value = response.data;
    } catch (err) {
        if (err.response?.data?.message) {
            checkError.value = err.response.data.message;
        } else {
            checkError.value = locale.value === 'pl' ? 'Wystapil blad. Sprobuj ponownie.' : 'An error occurred. Please try again.';
        }
    } finally {
        checking.value = false;
    }
}

// Booking form
const bookingForm = useForm({
    property_id: props.property.id,
    check_in: '',
    check_out: '',
    guests_count: 1,
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    special_requests: '',
});

function submitBooking() {
    bookingForm.check_in = checkForm.check_in;
    bookingForm.check_out = checkForm.check_out;
    bookingForm.guests_count = checkForm.guests_count;
    bookingForm.post('/bookings');
}

// Amenity icons - imported from shared composable

function amenityName(amenity) {
    return locale.value === 'en' && amenity.name_en ? amenity.name_en : amenity.name_pl || amenity;
}

// Helpers
function formatPrice(cents) {
    if (!cents && cents !== 0) return '0,00';
    return (cents / 100).toFixed(2).replace('.', ',');
}

const nights = computed(() => {
    if (!checkForm.check_in || !checkForm.check_out) return 0;
    const start = new Date(checkForm.check_in);
    const end = new Date(checkForm.check_out);
    const diff = Math.round((end - start) / (1000 * 60 * 60 * 24));
    return diff > 0 ? diff : 0;
});

const minNightsMet = computed(() => {
    if (!props.property.min_nights) return true;
    return nights.value >= props.property.min_nights;
});

// Map (Leaflet)
const mapContainer = ref(null);

onMounted(async () => {
    if (!props.property.latitude || !props.property.longitude) return;

    // Load Leaflet CSS
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
    document.head.appendChild(link);

    // Load Leaflet JS
    const script = document.createElement('script');
    script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
    script.onload = () => {
        nextTick(() => {
            if (!mapContainer.value) return;
            const L = window.L;
            const map = L.map(mapContainer.value).setView([props.property.latitude, props.property.longitude], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap',
                maxZoom: 18,
            }).addTo(map);
            L.marker([props.property.latitude, props.property.longitude]).addTo(map)
                .bindPopup(`<strong>${props.property.name}</strong><br>${props.property.address || ''}`);
        });
    };
    document.head.appendChild(script);
});
</script>

<template>
    <PublicLayout>
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-6 sm:py-10">

            <!-- Photo gallery -->
            <div class="mb-8">
                <!-- Main photo with arrows -->
                <div class="rounded-2xl overflow-hidden bg-gray-100 dark:bg-gray-800 aspect-[16/9] sm:aspect-[2/1] mb-3 relative group">
                    <transition name="fade" mode="out-in">
                        <img
                            v-if="mainPhoto"
                            :key="selectedPhotoIndex"
                            :src="photoUrl(mainPhoto)"
                            :alt="property.name"
                            class="absolute inset-0 w-full h-full object-cover"
                        />
                    </transition>
                    <div v-if="!mainPhoto" class="flex items-center justify-center h-full">
                        <svg class="w-20 h-20 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z" />
                        </svg>
                    </div>

                    <!-- Arrow navigation -->
                    <template v-if="photos.length > 1">
                        <button
                            @click="selectedPhotoIndex = (selectedPhotoIndex - 1 + photos.length) % photos.length"
                            class="absolute left-3 top-1/2 -translate-y-1/2 z-10 w-10 h-10 rounded-full bg-black/40 hover:bg-black/60 backdrop-blur-sm text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                            </svg>
                        </button>
                        <button
                            @click="selectedPhotoIndex = (selectedPhotoIndex + 1) % photos.length"
                            class="absolute right-3 top-1/2 -translate-y-1/2 z-10 w-10 h-10 rounded-full bg-black/40 hover:bg-black/60 backdrop-blur-sm text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                            </svg>
                        </button>
                    </template>

                    <!-- Photo counter badge -->
                    <div v-if="photos.length > 1" class="absolute bottom-3 right-3 z-10 px-3 py-1 rounded-full bg-black/50 backdrop-blur-sm text-white text-xs font-medium">
                        {{ selectedPhotoIndex + 1 }} / {{ photos.length }}
                    </div>
                </div>

                <!-- Thumbnails -->
                <div v-if="photos.length > 1" class="flex gap-2 overflow-x-auto pb-2">
                    <button
                        v-for="(photo, index) in photos"
                        :key="photo.id || index"
                        @click="selectedPhotoIndex = index"
                        :class="[
                            'shrink-0 w-20 h-14 sm:w-24 sm:h-16 rounded-lg overflow-hidden border-2 transition',
                            selectedPhotoIndex === index
                                ? 'border-sky-500 ring-1 ring-sky-500'
                                : 'border-transparent opacity-70 hover:opacity-100'
                        ]"
                    >
                        <img :src="photoUrl(photo)" :alt="`Photo ${index + 1}`" class="w-full h-full object-cover" />
                    </button>
                </div>
            </div>

            <!-- Property header -->
            <div class="mb-8">
                <div class="flex flex-wrap items-start gap-3 mb-2">
                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white">
                        {{ property.name }}
                    </h1>
                    <span
                        v-if="property.type"
                        class="mt-1 px-3 py-1 rounded-full bg-sky-100 dark:bg-sky-900/30 text-sky-700 dark:text-sky-300 text-xs font-medium"
                    >
                        {{ property.type }}
                    </span>
                </div>
                <p v-if="property.city || property.address" class="text-gray-500 dark:text-gray-400">
                    <span v-if="property.city">{{ property.city }}</span>
                    <span v-if="property.city && property.address"> &middot; </span>
                    <span v-if="property.address">{{ property.address }}</span>
                </p>
            </div>

            <!-- Two column layout -->
            <div class="grid lg:grid-cols-3 gap-8 lg:gap-10">

                <!-- Left content -->
                <div class="lg:col-span-2 space-y-8">

                    <!-- Description -->
                    <div v-if="description">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">
                            {{ locale === 'pl' ? 'Opis' : 'Description' }}
                        </h2>
                        <div class="prose prose-gray dark:prose-invert max-w-none text-gray-600 dark:text-gray-300 leading-relaxed whitespace-pre-line">
                            {{ description }}
                        </div>
                    </div>

                    <!-- Amenities -->
                    <div v-if="property.amenities && property.amenities.length">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            {{ locale === 'pl' ? 'Udogodnienia' : 'Amenities' }}
                        </h2>
                        <div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-6 gap-4">
                            <div
                                v-for="amenity in property.amenities"
                                :key="amenity.id || amenity"
                                class="flex flex-col items-center gap-2 p-3 rounded-xl bg-gray-50 dark:bg-gray-800/50 hover:bg-sky-50 dark:hover:bg-sky-900/20 transition group"
                            >
                                <div class="w-10 h-10 rounded-lg bg-white dark:bg-gray-800 shadow-sm flex items-center justify-center group-hover:bg-sky-100 dark:group-hover:bg-sky-900/40 transition">
                                    <svg class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" :d="getIconPath(amenity.icon)" />
                                    </svg>
                                </div>
                                <span class="text-xs text-gray-600 dark:text-gray-400 text-center leading-tight">
                                    {{ amenityName(amenity) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Details -->
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">
                            {{ locale === 'pl' ? 'Szczegoly' : 'Details' }}
                        </h2>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                            <div v-if="property.bedrooms" class="flex flex-col gap-1 p-3 rounded-lg bg-gray-50 dark:bg-gray-900">
                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ locale === 'pl' ? 'Sypialnie' : 'Bedrooms' }}</span>
                                <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ property.bedrooms }}</span>
                            </div>
                            <div v-if="property.bathrooms" class="flex flex-col gap-1 p-3 rounded-lg bg-gray-50 dark:bg-gray-900">
                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ locale === 'pl' ? 'Lazienki' : 'Bathrooms' }}</span>
                                <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ property.bathrooms }}</span>
                            </div>
                            <div v-if="property.max_guests" class="flex flex-col gap-1 p-3 rounded-lg bg-gray-50 dark:bg-gray-900">
                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ locale === 'pl' ? 'Maks. gosci' : 'Max guests' }}</span>
                                <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ property.max_guests }}</span>
                            </div>
                            <div v-if="property.area_sqm" class="flex flex-col gap-1 p-3 rounded-lg bg-gray-50 dark:bg-gray-900">
                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ locale === 'pl' ? 'Powierzchnia' : 'Area' }}</span>
                                <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ property.area_sqm }} m&sup2;</span>
                            </div>
                            <div v-if="property.check_in_time" class="flex flex-col gap-1 p-3 rounded-lg bg-gray-50 dark:bg-gray-900">
                                <span class="text-xs text-gray-500 dark:text-gray-400">Check-in</span>
                                <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ property.check_in_time }}</span>
                            </div>
                            <div v-if="property.check_out_time" class="flex flex-col gap-1 p-3 rounded-lg bg-gray-50 dark:bg-gray-900">
                                <span class="text-xs text-gray-500 dark:text-gray-400">Check-out</span>
                                <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ property.check_out_time }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Video -->
                    <div v-if="videoEmbedUrl">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">
                            {{ locale === 'pl' ? 'Film' : 'Video' }}
                        </h2>
                        <div class="aspect-video rounded-xl overflow-hidden bg-gray-100 dark:bg-gray-800">
                            <iframe
                                :src="videoEmbedUrl"
                                class="w-full h-full"
                                frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen
                            />
                        </div>
                    </div>

                    <!-- Min nights info -->
                    <div
                        v-if="property.min_nights && property.min_nights > 1"
                        class="flex items-center gap-3 p-4 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800"
                    >
                        <svg class="w-5 h-5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                        <span class="text-sm text-amber-800 dark:text-amber-200">
                            {{ locale === 'pl' ? `Minimalny pobyt: ${property.min_nights} nocy` : `Minimum stay: ${property.min_nights} nights` }}
                        </span>
                    </div>

                    <!-- Map -->
                    <div v-if="property.latitude && property.longitude">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">
                            {{ locale === 'pl' ? 'Lokalizacja' : 'Location' }}
                        </h2>
                        <div ref="mapContainer" class="w-full h-[300px] sm:h-[400px] rounded-2xl overflow-hidden border border-gray-200 dark:border-gray-700 z-0"></div>
                        <p v-if="property.city" class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                            {{ property.address ? property.address + ', ' : '' }}{{ property.city }}
                        </p>
                    </div>
                </div>

                <!-- Right sidebar -->
                <div class="lg:col-span-1">
                    <div class="sticky top-24 bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">
                            {{ locale === 'pl' ? 'Zarezerwuj' : 'Book now' }}
                        </h3>
                        <p v-if="property.base_price_per_night" class="text-sm text-gray-500 dark:text-gray-400 mb-5">
                            {{ locale === 'pl' ? 'od' : 'from' }} <span class="text-lg font-bold text-gray-900 dark:text-white">{{ formatPrice(property.base_price_per_night) }} PLN</span> / {{ locale === 'pl' ? 'noc' : 'night' }}
                        </p>

                        <div class="space-y-3 mb-5">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                                    {{ locale === 'pl' ? 'Zameldowanie' : 'Check-in' }}
                                </label>
                                <input
                                    v-model="checkForm.check_in"
                                    type="date"
                                    class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition"
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                                    {{ locale === 'pl' ? 'Wymeldowanie' : 'Check-out' }}
                                </label>
                                <input
                                    v-model="checkForm.check_out"
                                    type="date"
                                    class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition"
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                                    {{ locale === 'pl' ? 'Liczba gosci' : 'Guests' }}
                                </label>
                                <input
                                    v-model.number="checkForm.guests_count"
                                    type="number"
                                    min="1"
                                    :max="property.max_guests || 20"
                                    class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition"
                                />
                            </div>
                        </div>

                        <!-- Min nights warning -->
                        <div
                            v-if="nights > 0 && !minNightsMet"
                            class="mb-4 p-3 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800"
                        >
                            <p class="text-xs text-amber-700 dark:text-amber-300">
                                {{ locale === 'pl'
                                    ? `Minimalny pobyt to ${property.min_nights} nocy. Wybrales ${nights}.`
                                    : `Minimum stay is ${property.min_nights} nights. You selected ${nights}.`
                                }}
                            </p>
                        </div>

                        <button
                            @click="checkAvailability"
                            :disabled="checking || !checkForm.check_in || !checkForm.check_out"
                            class="w-full py-3 rounded-xl bg-sky-500 hover:bg-sky-600 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold text-sm transition flex items-center justify-center gap-2"
                        >
                            <svg v-if="checking" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                            </svg>
                            {{ locale === 'pl' ? 'Sprawdz dostepnosc' : 'Check availability' }}
                        </button>

                        <!-- Error -->
                        <div v-if="checkError" class="mt-4 p-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
                            <p class="text-sm text-red-700 dark:text-red-300">{{ checkError }}</p>
                        </div>

                        <!-- Availability result -->
                        <div v-if="checkResult" class="mt-5">
                            <!-- Unavailable -->
                            <div v-if="!checkResult.available" class="p-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
                                <p class="text-sm text-red-700 dark:text-red-300 font-medium">
                                    {{ locale === 'pl' ? 'Termin niedostepny' : 'Dates not available' }}
                                </p>
                                <p class="text-xs text-red-600 dark:text-red-400 mt-1">
                                    {{ locale === 'pl' ? 'Wybierz inny termin pobytu.' : 'Please select different dates.' }}
                                </p>
                            </div>

                            <!-- Available -->
                            <template v-if="checkResult.available">
                                <!-- Pricing breakdown -->
                                <div class="space-y-2 pb-4 border-b border-gray-100 dark:border-gray-800 mb-4">
                                    <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400">
                                        <span>{{ checkResult.pricing.nights }} {{ locale === 'pl' ? (checkResult.pricing.nights === 1 ? 'noc' : 'nocy') : (checkResult.pricing.nights === 1 ? 'night' : 'nights') }}</span>
                                        <span>{{ formatPrice(checkResult.pricing.base_total) }} PLN</span>
                                    </div>
                                    <div v-if="checkResult.pricing.cleaning_fee" class="flex justify-between text-sm text-gray-600 dark:text-gray-400">
                                        <span>{{ locale === 'pl' ? 'Oplata za sprzatanie' : 'Cleaning fee' }}</span>
                                        <span>{{ formatPrice(checkResult.pricing.cleaning_fee) }} PLN</span>
                                    </div>
                                    <div class="flex justify-between text-base font-bold text-gray-900 dark:text-white pt-2">
                                        <span>{{ locale === 'pl' ? 'Razem' : 'Total' }}</span>
                                        <span>{{ formatPrice(checkResult.pricing.total_price) }} PLN</span>
                                    </div>
                                    <div v-if="checkResult.deposit_amount" class="flex justify-between text-xs text-gray-500 dark:text-gray-400">
                                        <span>{{ locale === 'pl' ? 'Zaliczka' : 'Deposit' }} ({{ checkResult.deposit_percent }}%)</span>
                                        <span>{{ formatPrice(checkResult.deposit_amount) }} PLN</span>
                                    </div>
                                </div>

                                <!-- Guest form -->
                                <div class="space-y-3 mb-5">
                                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white">
                                        {{ locale === 'pl' ? 'Dane goscia' : 'Guest details' }}
                                    </h4>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">{{ locale === 'pl' ? 'Imie' : 'First name' }}</label>
                                            <input
                                                v-model="bookingForm.first_name"
                                                type="text"
                                                class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition"
                                            />
                                            <p v-if="bookingForm.errors.first_name" class="text-xs text-red-500 mt-1">{{ bookingForm.errors.first_name }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">{{ locale === 'pl' ? 'Nazwisko' : 'Last name' }}</label>
                                            <input
                                                v-model="bookingForm.last_name"
                                                type="text"
                                                class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition"
                                            />
                                            <p v-if="bookingForm.errors.last_name" class="text-xs text-red-500 mt-1">{{ bookingForm.errors.last_name }}</p>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Email</label>
                                        <input
                                            v-model="bookingForm.email"
                                            type="email"
                                            class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition"
                                        />
                                        <p v-if="bookingForm.errors.email" class="text-xs text-red-500 mt-1">{{ bookingForm.errors.email }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">{{ locale === 'pl' ? 'Telefon' : 'Phone' }}</label>
                                        <input
                                            v-model="bookingForm.phone"
                                            type="tel"
                                            class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition"
                                        />
                                        <p v-if="bookingForm.errors.phone" class="text-xs text-red-500 mt-1">{{ bookingForm.errors.phone }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">{{ locale === 'pl' ? 'Uwagi' : 'Special requests' }}</label>
                                        <textarea
                                            v-model="bookingForm.special_requests"
                                            rows="3"
                                            class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition resize-none"
                                        />
                                    </div>
                                </div>

                                <button
                                    @click="submitBooking"
                                    :disabled="bookingForm.processing"
                                    class="w-full py-3 rounded-xl bg-sky-500 hover:bg-sky-600 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold text-sm transition flex items-center justify-center gap-2"
                                >
                                    <svg v-if="bookingForm.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                    </svg>
                                    {{ locale === 'pl' ? 'Potwierdz rezerwacje' : 'Confirm booking' }}
                                </button>

                                <!-- General form errors -->
                                <p v-if="bookingForm.errors.check_in" class="text-xs text-red-500 mt-2">{{ bookingForm.errors.check_in }}</p>
                                <p v-if="bookingForm.errors.check_out" class="text-xs text-red-500 mt-1">{{ bookingForm.errors.check_out }}</p>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
