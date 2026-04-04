<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const { t } = useI18n();

const props = defineProps({
    amenities: Array,
});

// Steps: 'choose' = pick method, 'form' = edit & import
const step = ref('choose');
const method = ref(null); // 'auto', 'html', 'bookmarklet', 'manual'

// Form data
const form = ref({
    url: '',
    name: '',
    type: 'apartment',
    address: '',
    city: '',
    postal_code: '',
    country: 'Poland',
    latitude: null,
    longitude: null,
    description_pl: '',
    description_en: '',
    max_guests: 2,
    bedrooms: 1,
    bathrooms: 1,
    area_sqm: null,
    base_price_per_night: 35000,
    cleaning_fee: 10000,
    check_in_time: '15:00',
    check_out_time: '11:00',
    min_nights: 1,
    photo_urls: [],
    amenity_ids: [],
});

// UI state
const loading = ref(false);
const importing = ref(false);
const error = ref(null);
const successMsg = ref(null);
const selectedPhotos = ref([]);
const pastedHtml = ref('');
const bookmarkletListening = ref(false);

// Property types
const propertyTypes = [
    { value: 'apartment', label: t('properties.types.apartment') },
    { value: 'house', label: t('properties.types.house') },
    { value: 'villa', label: t('properties.types.villa') },
    { value: 'room', label: t('properties.types.room') },
];

// Bookmarklet URL
const bookmarkletCode = computed(() => {
    const origin = window.location.origin;
    const code = `javascript:void(function(){var d=document;var data={name:'',description:'',address:'',city:'',country:'',latitude:null,longitude:null,photos:[],max_guests:null,bedrooms:null,bathrooms:null,area_sqm:null,price:null,amenities:[]};try{var ld=d.querySelectorAll('script[type="application/ld+json"]');for(var i=0;i<ld.length;i++){try{var j=JSON.parse(ld[i].textContent);if(j['@type']&&/Hotel|Lodging|Vacation|Apartment|House/i.test(j['@type'])){if(j.name)data.name=j.name;if(j.description)data.description=j.description;if(j.address){data.address=j.address.streetAddress||'';data.city=j.address.addressLocality||'';data.country=j.address.addressCountry||''}if(j.geo){data.latitude=parseFloat(j.geo.latitude);data.longitude=parseFloat(j.geo.longitude)}if(j.image){var imgs=Array.isArray(j.image)?j.image:[j.image];imgs.forEach(function(im){var u=typeof im==='string'?im:(im.contentUrl||im.url);if(u)data.photos.push(u)})}}}catch(e){}}if(!data.name){var og=d.querySelector('meta[property="og:title"]');if(og)data.name=og.content}var imgs=d.querySelectorAll('img[src*="bstatic.com"]');imgs.forEach(function(im){var s=im.src.replace(/\\/max\\d+(?:x\\d+)?\\//,'/max1024x768/').replace(/\\/square\\d+\\//,'/max1024x768/');if(data.photos.indexOf(s)===-1&&data.photos.length<20)data.photos.push(s)});var allText=d.body.innerText;var gm=allText.match(/(\\d+)\\s*(?:guests?|people|persons?|sleeps)/i);if(gm)data.max_guests=parseInt(gm[1]);var bm=allText.match(/(\\d+)\\s*(?:-?\\s*bedrooms?)/i);if(bm)data.bedrooms=parseInt(bm[1]);var btm=allText.match(/(\\d+)\\s*(?:bathrooms?)/i);if(btm)data.bathrooms=parseInt(btm[1]);var am=allText.match(/(\\d+)\\s*(?:m²|m2|sqm)/i);if(am)data.area_sqm=parseInt(am[1]);var kw=['wifi','parking','kitchen','air conditioning','washing machine','dishwasher','tv','balcony','terrace','pool','elevator','iron','hair dryer','coffee','microwave','safe'];kw.forEach(function(k){if(allText.toLowerCase().indexOf(k)!==-1)data.amenities.push(k)})}catch(e){alert('Error: '+e.message)}window.open('${origin}/admin/import?bookmarklet='+encodeURIComponent(JSON.stringify(data)),'_self')}())`;
    return code;
});

// Listen for bookmarklet data via URL param
onMounted(() => {
    const params = new URLSearchParams(window.location.search);
    const bookmarkletData = params.get('bookmarklet');
    if (bookmarkletData) {
        try {
            const data = JSON.parse(bookmarkletData);
            applyScrapedData(data);
            method.value = 'bookmarklet';
            step.value = 'form';
            successMsg.value = t('import.bookmarklet_received');
            // Clean URL
            window.history.replaceState({}, '', window.location.pathname);
        } catch (e) {
            console.error('Failed to parse bookmarklet data', e);
        }
    }
});

// Apply scraped data to form
function applyScrapedData(data) {
    if (data.name) form.value.name = data.name;
    if (data.description) {
        form.value.description_pl = data.description;
        form.value.description_en = data.description;
    }
    if (data.address) form.value.address = data.address;
    if (data.city) form.value.city = data.city;
    if (data.postal_code) form.value.postal_code = data.postal_code;
    if (data.country) form.value.country = data.country;
    if (data.latitude) form.value.latitude = data.latitude;
    if (data.longitude) form.value.longitude = data.longitude;
    if (data.max_guests) form.value.max_guests = data.max_guests;
    if (data.bedrooms) form.value.bedrooms = data.bedrooms;
    if (data.bathrooms) form.value.bathrooms = data.bathrooms;
    if (data.area_sqm) form.value.area_sqm = data.area_sqm;
    if (data.price) form.value.base_price_per_night = data.price;

    if (data.photos && data.photos.length > 0) {
        form.value.photo_urls = data.photos;
        selectedPhotos.value = [...data.photos];
    }

    if (data.amenities && data.amenities.length > 0) {
        matchAmenities(data.amenities);
    }
}

// Method 1: Auto-fetch from URL
async function fetchData() {
    if (!form.value.url) return;

    loading.value = true;
    error.value = null;

    try {
        const response = await fetch('/admin/import/scrape', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ url: form.value.url }),
        });

        const result = await response.json();

        if (result.success && result.data) {
            applyScrapedData(result.data);
            step.value = 'form';

            if (!result.data.name && !result.data.description && !result.data.address) {
                error.value = t('import.no_data');
            }
        } else {
            error.value = t('import.error');
        }
    } catch (e) {
        error.value = t('import.error');
        console.error(e);
    } finally {
        loading.value = false;
    }
}

// Method 2: Parse pasted HTML
async function parseHtml() {
    if (!pastedHtml.value || pastedHtml.value.length < 100) return;

    loading.value = true;
    error.value = null;

    try {
        const response = await fetch('/admin/import/parse-html', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ html: pastedHtml.value }),
        });

        const result = await response.json();

        if (result.success && result.data) {
            applyScrapedData(result.data);
            step.value = 'form';

            const d = result.data;
            if (!d.name && !d.description && !d.address) {
                error.value = t('import.no_data');
            } else {
                successMsg.value = t('import.review');
            }
        } else {
            error.value = t('import.error');
        }
    } catch (e) {
        error.value = t('import.error');
        console.error(e);
    } finally {
        loading.value = false;
    }
}

// Method 4: Skip to manual
function goManual() {
    method.value = 'manual';
    step.value = 'form';
}

// Match scraped amenities
function matchAmenities(scrapedAmenities) {
    const matched = [];
    scrapedAmenities.forEach(scraped => {
        const lower = scraped.toLowerCase();
        props.amenities.forEach(amenity => {
            const namePl = amenity.name_pl.toLowerCase();
            const nameEn = amenity.name_en.toLowerCase();
            if (namePl.includes(lower) || nameEn.includes(lower) ||
                lower.includes(namePl) || lower.includes(nameEn)) {
                if (!matched.includes(amenity.id)) {
                    matched.push(amenity.id);
                }
            }
        });
    });
    form.value.amenity_ids = matched;
}

// Toggle photo
function togglePhoto(url) {
    const index = selectedPhotos.value.indexOf(url);
    if (index > -1) {
        selectedPhotos.value.splice(index, 1);
    } else {
        selectedPhotos.value.push(url);
    }
}

function isPhotoSelected(url) {
    return selectedPhotos.value.includes(url);
}

// PLN computed
const basePricePLN = computed({
    get: () => form.value.base_price_per_night / 100,
    set: (val) => form.value.base_price_per_night = Math.round(val * 100)
});

const cleaningFeePLN = computed({
    get: () => form.value.cleaning_fee / 100,
    set: (val) => form.value.cleaning_fee = Math.round(val * 100)
});

// Import
function importProperty() {
    importing.value = true;
    error.value = null;

    const dataToSubmit = {
        ...form.value,
        photo_urls: selectedPhotos.value,
    };

    router.post('/admin/import', dataToSubmit, {
        onSuccess: () => {
            importing.value = false;
        },
        onError: (errors) => {
            importing.value = false;
            error.value = Object.values(errors).flat().join(', ');
        },
    });
}

// Group amenities
const amenitiesByCategory = computed(() => {
    const grouped = {};
    props.amenities.forEach(amenity => {
        const cat = amenity.category || 'other';
        if (!grouped[cat]) grouped[cat] = [];
        grouped[cat].push(amenity);
    });
    return grouped;
});

// Count filled fields
const filledFields = computed(() => {
    let count = 0;
    if (form.value.name) count++;
    if (form.value.description_pl) count++;
    if (form.value.address) count++;
    if (form.value.city) count++;
    if (form.value.latitude) count++;
    if (form.value.photo_urls.length > 0) count++;
    if (form.value.amenity_ids.length > 0) count++;
    return count;
});
</script>

<template>
    <AdminLayout>
        <div class="p-6 max-w-5xl mx-auto">
            <!-- Header -->
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ t('import.title') }}
                </h1>
                <button
                    v-if="step !== 'choose'"
                    @click="step = 'choose'; error = null; successMsg = null"
                    class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                >
                    &larr; {{ t('app.back') }}
                </button>
            </div>

            <!-- Messages -->
            <div v-if="error" class="mb-6 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg">
                <p class="text-sm text-red-800 dark:text-red-200">{{ error }}</p>
            </div>
            <div v-if="successMsg" class="mb-6 p-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-lg">
                <p class="text-sm text-green-800 dark:text-green-200">{{ successMsg }}</p>
            </div>

            <!-- Step: Choose method -->
            <div v-if="step === 'choose'" class="space-y-4">
                <!-- Method cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <!-- 1. Paste HTML (recommended) -->
                    <div
                        @click="method = 'html'"
                        class="bg-white dark:bg-gray-800 rounded-lg border-2 p-5 cursor-pointer transition hover:shadow-md"
                        :class="method === 'html' ? 'border-sky-500 shadow-md' : 'border-gray-200 dark:border-gray-700'"
                    >
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-10 h-10 rounded-lg bg-sky-100 dark:bg-sky-900/50 flex items-center justify-center">
                                <svg class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">{{ t('import.method_html') }}</h3>
                                <span class="text-xs text-sky-600 dark:text-sky-400 font-medium">REKOMENDOWANE</span>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ t('import.html_desc') }}</p>
                    </div>

                    <!-- 2. Bookmarklet -->
                    <div
                        @click="method = 'bookmarklet'"
                        class="bg-white dark:bg-gray-800 rounded-lg border-2 p-5 cursor-pointer transition hover:shadow-md"
                        :class="method === 'bookmarklet' ? 'border-sky-500 shadow-md' : 'border-gray-200 dark:border-gray-700'"
                    >
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-10 h-10 rounded-lg bg-purple-100 dark:bg-purple-900/50 flex items-center justify-center">
                                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0z" />
                                </svg>
                            </div>
                            <h3 class="font-semibold text-gray-900 dark:text-white">{{ t('import.method_bookmarklet') }}</h3>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ t('import.bookmarklet_desc') }}</p>
                    </div>

                    <!-- 3. Auto URL -->
                    <div
                        @click="method = 'auto'"
                        class="bg-white dark:bg-gray-800 rounded-lg border-2 p-5 cursor-pointer transition hover:shadow-md"
                        :class="method === 'auto' ? 'border-sky-500 shadow-md' : 'border-gray-200 dark:border-gray-700'"
                    >
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-10 h-10 rounded-lg bg-amber-100 dark:bg-amber-900/50 flex items-center justify-center">
                                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m9.924-9.924l4.5 4.5a4.5 4.5 0 010 6.364l-4.5 4.5a4.5 4.5 0 01-7.244-1.242" />
                                </svg>
                            </div>
                            <h3 class="font-semibold text-gray-900 dark:text-white">{{ t('import.method_auto') }}</h3>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ t('import.auto_desc') }}</p>
                    </div>

                    <!-- 4. Manual -->
                    <div
                        @click="method = 'manual'"
                        class="bg-white dark:bg-gray-800 rounded-lg border-2 p-5 cursor-pointer transition hover:shadow-md"
                        :class="method === 'manual' ? 'border-sky-500 shadow-md' : 'border-gray-200 dark:border-gray-700'"
                    >
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                </svg>
                            </div>
                            <h3 class="font-semibold text-gray-900 dark:text-white">{{ t('import.method_manual') }}</h3>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ t('import.manual_desc') }}</p>
                    </div>
                </div>

                <!-- Method-specific action area -->
                <div v-if="method" class="mt-6">
                    <!-- HTML paste -->
                    <div v-if="method === 'html'" class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 space-y-4">
                        <div class="flex items-start gap-3 p-3 bg-sky-50 dark:bg-sky-900/20 rounded-lg">
                            <svg class="w-5 h-5 text-sky-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                            </svg>
                            <div class="text-sm text-sky-800 dark:text-sky-200">
                                <p class="font-medium mb-1">Jak to zrobic:</p>
                                <ol class="list-decimal list-inside space-y-1 text-sky-700 dark:text-sky-300">
                                    <li>Otworz oferte na Booking.com w przegladarce</li>
                                    <li>Nacisnij <kbd class="px-1.5 py-0.5 bg-sky-100 dark:bg-sky-800 rounded text-xs font-mono">Ctrl+U</kbd> (lub PPM → "Wyswietl zrodlo strony")</li>
                                    <li>Zaznacz wszystko <kbd class="px-1.5 py-0.5 bg-sky-100 dark:bg-sky-800 rounded text-xs font-mono">Ctrl+A</kbd> i skopiuj <kbd class="px-1.5 py-0.5 bg-sky-100 dark:bg-sky-800 rounded text-xs font-mono">Ctrl+C</kbd></li>
                                    <li>Wklej ponizej <kbd class="px-1.5 py-0.5 bg-sky-100 dark:bg-sky-800 rounded text-xs font-mono">Ctrl+V</kbd></li>
                                </ol>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ t('import.html_label') }}
                            </label>
                            <textarea
                                v-model="pastedHtml"
                                rows="8"
                                :placeholder="t('import.html_placeholder')"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500 font-mono text-xs"
                            ></textarea>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                {{ pastedHtml.length.toLocaleString() }} znakow
                            </p>
                        </div>

                        <button
                            @click="parseHtml"
                            :disabled="loading || pastedHtml.length < 100"
                            class="px-6 py-2.5 bg-sky-500 hover:bg-sky-600 disabled:bg-gray-300 dark:disabled:bg-gray-700 text-white rounded-lg font-medium transition disabled:cursor-not-allowed"
                        >
                            <span v-if="loading">{{ t('import.html_parsing') }}</span>
                            <span v-else>{{ t('import.html_parse') }}</span>
                        </button>
                    </div>

                    <!-- Bookmarklet -->
                    <div v-if="method === 'bookmarklet'" class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 space-y-4">
                        <div class="flex items-start gap-3 p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                            <svg class="w-5 h-5 text-purple-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                            </svg>
                            <div class="text-sm text-purple-800 dark:text-purple-200">
                                <p class="font-medium mb-1">Jak to zrobic:</p>
                                <ol class="list-decimal list-inside space-y-1 text-purple-700 dark:text-purple-300">
                                    <li>Przeciagnij ponizszy przycisk na pasek zakladek przegladarki</li>
                                    <li>Otworz oferte na Booking.com</li>
                                    <li>Kliknij bookmarklet "StayFlow Import" na pasku zakladek</li>
                                    <li>Dane automatycznie pojawia sie w formularzu</li>
                                </ol>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ t('import.bookmarklet_drag') }}:</p>
                            <a
                                :href="bookmarkletCode"
                                @click.prevent
                                class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-lg font-medium text-sm hover:bg-purple-700 transition cursor-grab active:cursor-grabbing"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0z" />
                                </svg>
                                {{ t('import.bookmarklet_btn') }}
                            </a>
                        </div>

                        <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Po kliknieciu bookmarkletu na stronie Booking.com, ta strona zostanie automatycznie otwarta z danymi obiektu.
                            </p>
                        </div>
                    </div>

                    <!-- Auto URL -->
                    <div v-if="method === 'auto'" class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 space-y-4">
                        <div class="flex items-start gap-3 p-3 bg-amber-50 dark:bg-amber-900/20 rounded-lg">
                            <svg class="w-5 h-5 text-amber-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                            <p class="text-sm text-amber-800 dark:text-amber-200">
                                Booking.com blokuje automatyczne pobieranie danych. Ta metoda moze nie dzialac — jesli nie zadziala, uzyj metody "Wklej HTML".
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ t('import.url_label') }}
                            </label>
                            <input
                                v-model="form.url"
                                type="url"
                                placeholder="https://www.booking.com/hotel/... lub https://www.booking.com/Share-..."
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500"
                                @keypress.enter="fetchData"
                            />
                        </div>

                        <button
                            @click="fetchData"
                            :disabled="loading || !form.url"
                            class="px-6 py-2.5 bg-amber-500 hover:bg-amber-600 disabled:bg-gray-300 dark:disabled:bg-gray-700 text-white rounded-lg font-medium transition disabled:cursor-not-allowed"
                        >
                            <span v-if="loading">{{ t('import.fetching') }}</span>
                            <span v-else>{{ t('import.fetch') }}</span>
                        </button>
                    </div>

                    <!-- Manual — go straight to form -->
                    <div v-if="method === 'manual'" class="text-center py-4">
                        <button
                            @click="goManual"
                            class="px-6 py-2.5 bg-sky-500 hover:bg-sky-600 text-white rounded-lg font-medium transition"
                        >
                            {{ t('import.skip_to_form') }} &rarr;
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step: Edit form -->
            <div v-if="step === 'form'" class="space-y-6">
                <!-- Data summary badge -->
                <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-sky-100 dark:bg-sky-900/40 text-sky-700 dark:text-sky-300 rounded-full text-xs font-medium">
                        {{ filledFields }} / 7 pol wypelnionych
                    </span>
                    <span v-if="form.photo_urls.length > 0" class="inline-flex items-center gap-1 px-2.5 py-1 bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300 rounded-full text-xs font-medium">
                        {{ form.photo_urls.length }} zdjec
                    </span>
                </div>

                <!-- Basic Info -->
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        {{ t('properties.title') }}
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ t('properties.name') }} *
                            </label>
                            <input
                                v-model="form.name"
                                type="text"
                                required
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ t('properties.type') }}
                            </label>
                            <select
                                v-model="form.type"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500"
                            >
                                <option v-for="type in propertyTypes" :key="type.value" :value="type.value">
                                    {{ type.label }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ t('properties.city') }}
                            </label>
                            <input
                                v-model="form.city"
                                type="text"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500"
                            />
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ t('properties.address') }}
                            </label>
                            <input
                                v-model="form.address"
                                type="text"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ t('properties.postalCode') }}
                            </label>
                            <input
                                v-model="form.postal_code"
                                type="text"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ t('properties.country') }}
                            </label>
                            <input
                                v-model="form.country"
                                type="text"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ t('properties.latitude') }}
                            </label>
                            <input
                                v-model.number="form.latitude"
                                type="number"
                                step="any"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ t('properties.longitude') }}
                            </label>
                            <input
                                v-model.number="form.longitude"
                                type="number"
                                step="any"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500"
                            />
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        {{ t('properties.description') }}
                    </h2>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ t('properties.description') }} (PL)
                            </label>
                            <textarea
                                v-model="form.description_pl"
                                rows="4"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500"
                            ></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ t('properties.description') }} (EN)
                            </label>
                            <textarea
                                v-model="form.description_en"
                                rows="4"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500"
                            ></textarea>
                        </div>
                    </div>
                </div>

                <!-- Details -->
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        {{ t('properties.details') }}
                    </h2>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ t('properties.maxGuests') }}
                            </label>
                            <input
                                v-model.number="form.max_guests"
                                type="number"
                                min="1"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ t('properties.bedrooms') }}
                            </label>
                            <input
                                v-model.number="form.bedrooms"
                                type="number"
                                min="0"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ t('properties.bathrooms') }}
                            </label>
                            <input
                                v-model.number="form.bathrooms"
                                type="number"
                                min="0"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ t('properties.area') }}
                            </label>
                            <input
                                v-model.number="form.area_sqm"
                                type="number"
                                min="0"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500"
                            />
                        </div>
                    </div>
                </div>

                <!-- Pricing -->
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        {{ t('properties.pricing') }}
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ t('properties.basePrice') }} (PLN)
                            </label>
                            <input
                                v-model.number="basePricePLN"
                                type="number"
                                min="0"
                                step="0.01"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ t('properties.cleaningFee') }} (PLN)
                            </label>
                            <input
                                v-model.number="cleaningFeePLN"
                                type="number"
                                min="0"
                                step="0.01"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500"
                            />
                        </div>
                    </div>
                </div>

                <!-- Photos -->
                <div v-if="form.photo_urls.length > 0" class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        {{ t('import.photos_label') }}
                    </h2>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div
                            v-for="(url, index) in form.photo_urls"
                            :key="index"
                            @click="togglePhoto(url)"
                            class="relative aspect-square rounded-lg overflow-hidden cursor-pointer border-2 transition"
                            :class="isPhotoSelected(url) ? 'border-sky-500' : 'border-gray-200 dark:border-gray-700 opacity-50'"
                        >
                            <img :src="url" :alt="`Photo ${index + 1}`" class="w-full h-full object-cover" loading="lazy" />
                            <div v-if="isPhotoSelected(url)" class="absolute top-2 right-2 w-6 h-6 bg-sky-500 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                        {{ selectedPhotos.length }} / {{ form.photo_urls.length }} selected
                    </p>
                </div>

                <!-- Amenities -->
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        {{ t('import.select_amenities') }}
                    </h2>

                    <div class="space-y-4">
                        <div v-for="(amenitiesList, category) in amenitiesByCategory" :key="category">
                            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 capitalize">
                                {{ category }}
                            </h3>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                <label
                                    v-for="amenity in amenitiesList"
                                    :key="amenity.id"
                                    class="flex items-center gap-2 cursor-pointer"
                                >
                                    <input
                                        type="checkbox"
                                        :value="amenity.id"
                                        v-model="form.amenity_ids"
                                        class="w-4 h-4 text-sky-500 border-gray-300 dark:border-gray-600 rounded focus:ring-sky-500"
                                    />
                                    <span class="text-sm text-gray-700 dark:text-gray-300">
                                        {{ amenity.name_pl }}
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between">
                    <button
                        @click="step = 'choose'; error = null; successMsg = null"
                        class="px-6 py-2.5 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                    >
                        {{ t('app.back') }}
                    </button>

                    <button
                        @click="importProperty"
                        :disabled="importing || !form.name"
                        class="px-6 py-2.5 bg-sky-500 hover:bg-sky-600 disabled:bg-gray-300 dark:disabled:bg-gray-700 text-white rounded-lg font-medium transition disabled:cursor-not-allowed"
                    >
                        <span v-if="importing">{{ t('import.importing') }}</span>
                        <span v-else>{{ t('import.import_btn') }}</span>
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
