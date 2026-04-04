<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, useForm, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { ref, computed } from 'vue';

const { t } = useI18n();

const props = defineProps({
    property: Object,
    amenities: Array,
});

const activeTab = ref('details');

// Main form - prices converted from grosze to PLN
const form = useForm({
    name: props.property.name || '',
    type: props.property.type || 'apartment',
    address: props.property.address || '',
    city: props.property.city || '',
    postal_code: props.property.postal_code || '',
    country: props.property.country || 'PL',
    description_pl: props.property.description_pl || '',
    description_en: props.property.description_en || '',
    max_guests: props.property.max_guests || 4,
    bedrooms: props.property.bedrooms || 1,
    bathrooms: props.property.bathrooms || 1,
    area_sqm: props.property.area_sqm || null,
    base_price_per_night: props.property.base_price_per_night ? (props.property.base_price_per_night / 100).toFixed(2) : '',
    cleaning_fee: props.property.cleaning_fee ? (props.property.cleaning_fee / 100).toFixed(2) : '',
    check_in_time: props.property.check_in_time || '15:00',
    check_out_time: props.property.check_out_time || '11:00',
    min_nights: props.property.min_nights || 1,
    video_url: props.property.video_url || '',
    reservations_enabled: props.property.reservations_enabled ?? true,
    is_published: props.property.is_published ?? false,
    sort_order: props.property.sort_order ?? 0,
    amenity_ids: props.property.amenities ? props.property.amenities.map(a => a.id) : [],
});

// Photo upload form
const photoForm = useForm({
    photos: null,
});
const photoInput = ref(null);

// Seasonal price form
const seasonForm = useForm({
    name: '',
    date_from: '',
    date_to: '',
    price_per_night: '',
    min_nights: 1,
    priority: 0,
});

const amenitiesByCategory = computed(() => {
    const grouped = {};
    if (props.amenities) {
        props.amenities.forEach((a) => {
            if (!grouped[a.category]) {
                grouped[a.category] = [];
            }
            grouped[a.category].push(a);
        });
    }
    return grouped;
});

const toggleAmenity = (id) => {
    const idx = form.amenity_ids.indexOf(id);
    if (idx === -1) {
        form.amenity_ids.push(id);
    } else {
        form.amenity_ids.splice(idx, 1);
    }
};

const submitDetails = () => {
    const data = form.data();
    if (data.base_price_per_night !== '' && data.base_price_per_night !== null) {
        data.base_price_per_night = Math.round(parseFloat(data.base_price_per_night) * 100);
    }
    if (data.cleaning_fee !== '' && data.cleaning_fee !== null) {
        data.cleaning_fee = Math.round(parseFloat(data.cleaning_fee) * 100);
    }
    form.transform(() => data).put(`/admin/properties/${props.property.id}`);
};

const uploadPhotos = () => {
    if (!photoInput.value?.files?.length) return;
    const formData = useForm({
        photos: Array.from(photoInput.value.files),
    });
    formData.post(`/admin/properties/${props.property.id}/photos`, {
        forceFormData: true,
        onSuccess: () => {
            photoInput.value.value = '';
        },
    });
};

const setCover = (photoId) => {
    router.post(`/admin/properties/${props.property.id}/photos/${photoId}/cover`);
};

const deletePhoto = (photoId) => {
    if (confirm(t('properties.deletePhotoConfirm'))) {
        router.delete(`/admin/properties/${props.property.id}/photos/${photoId}`);
    }
};

const submitSeason = () => {
    const data = seasonForm.data();
    if (data.price_per_night !== '' && data.price_per_night !== null) {
        data.price_per_night = Math.round(parseFloat(data.price_per_night) * 100);
    }
    seasonForm.transform(() => data).post(`/admin/properties/${props.property.id}/seasonal-prices`, {
        onSuccess: () => {
            seasonForm.reset();
        },
    });
};

const deleteSeasonalPrice = (priceId) => {
    if (confirm(t('properties.deleteSeasonConfirm'))) {
        router.delete(`/admin/properties/${props.property.id}/seasonal-prices/${priceId}`);
    }
};

const formatPrice = (grosze) => {
    return (grosze / 100).toFixed(2);
};
</script>

<template>
    <AdminLayout>
        <div class="p-4 sm:p-6 lg:p-8 max-w-4xl">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ t('properties.editProperty') }}: {{ property.name }}
                </h1>
                <Link
                    href="/admin/properties"
                    class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200"
                >
                    {{ t('properties.backToList') }}
                </Link>
            </div>

            <!-- Tabs -->
            <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
                <nav class="flex gap-4 -mb-px">
                    <button
                        @click="activeTab = 'details'"
                        :class="[
                            'px-1 pb-3 text-sm font-medium border-b-2 transition',
                            activeTab === 'details'
                                ? 'border-sky-500 text-sky-600 dark:text-sky-400'
                                : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300'
                        ]"
                    >
                        {{ t('properties.tabDetails') }}
                    </button>
                    <button
                        @click="activeTab = 'photos'"
                        :class="[
                            'px-1 pb-3 text-sm font-medium border-b-2 transition',
                            activeTab === 'photos'
                                ? 'border-sky-500 text-sky-600 dark:text-sky-400'
                                : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300'
                        ]"
                    >
                        {{ t('properties.tabPhotos') }}
                    </button>
                    <button
                        @click="activeTab = 'pricing'"
                        :class="[
                            'px-1 pb-3 text-sm font-medium border-b-2 transition',
                            activeTab === 'pricing'
                                ? 'border-sky-500 text-sky-600 dark:text-sky-400'
                                : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300'
                        ]"
                    >
                        {{ t('properties.tabPricing') }}
                    </button>
                </nav>
            </div>

            <!-- Details tab -->
            <div v-show="activeTab === 'details'">
                <form @submit.prevent="submitDetails" class="space-y-6">
                    <!-- Basic info -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            {{ t('properties.basicInfo') }}
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ t('properties.name') }}
                                </label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm px-3 py-2 border"
                                />
                                <p v-if="form.errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.name }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ t('properties.type') }}
                                </label>
                                <select
                                    v-model="form.type"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm px-3 py-2 border"
                                >
                                    <option value="apartment">{{ t('properties.types.apartment') }}</option>
                                    <option value="house">{{ t('properties.types.house') }}</option>
                                    <option value="room">{{ t('properties.types.room') }}</option>
                                    <option value="villa">{{ t('properties.types.villa') }}</option>
                                    <option value="studio">{{ t('properties.types.studio') }}</option>
                                </select>
                                <p v-if="form.errors.type" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.type }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ t('properties.videoUrl') }}
                                </label>
                                <input
                                    v-model="form.video_url"
                                    type="url"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm px-3 py-2 border"
                                />
                                <p v-if="form.errors.video_url" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.video_url }}</p>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ t('properties.descriptionPl') }}
                                </label>
                                <textarea
                                    v-model="form.description_pl"
                                    rows="4"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm px-3 py-2 border"
                                />
                                <p v-if="form.errors.description_pl" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.description_pl }}</p>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ t('properties.descriptionEn') }}
                                </label>
                                <textarea
                                    v-model="form.description_en"
                                    rows="4"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm px-3 py-2 border"
                                />
                                <p v-if="form.errors.description_en" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.description_en }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ t('properties.maxGuests') }}
                                </label>
                                <input
                                    v-model.number="form.max_guests"
                                    type="number"
                                    min="1"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm px-3 py-2 border"
                                />
                                <p v-if="form.errors.max_guests" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.max_guests }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ t('properties.bedrooms') }}
                                </label>
                                <input
                                    v-model.number="form.bedrooms"
                                    type="number"
                                    min="0"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm px-3 py-2 border"
                                />
                                <p v-if="form.errors.bedrooms" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.bedrooms }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ t('properties.bathrooms') }}
                                </label>
                                <input
                                    v-model.number="form.bathrooms"
                                    type="number"
                                    min="0"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm px-3 py-2 border"
                                />
                                <p v-if="form.errors.bathrooms" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.bathrooms }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ t('properties.areaSqm') }}
                                </label>
                                <input
                                    v-model.number="form.area_sqm"
                                    type="number"
                                    min="0"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm px-3 py-2 border"
                                />
                                <p v-if="form.errors.area_sqm" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.area_sqm }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Location -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            {{ t('properties.location') }}
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ t('properties.address') }}
                                </label>
                                <input
                                    v-model="form.address"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm px-3 py-2 border"
                                />
                                <p v-if="form.errors.address" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.address }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ t('properties.city') }}
                                </label>
                                <input
                                    v-model="form.city"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm px-3 py-2 border"
                                />
                                <p v-if="form.errors.city" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.city }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ t('properties.postalCode') }}
                                </label>
                                <input
                                    v-model="form.postal_code"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm px-3 py-2 border"
                                />
                                <p v-if="form.errors.postal_code" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.postal_code }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ t('properties.country') }}
                                </label>
                                <input
                                    v-model="form.country"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm px-3 py-2 border"
                                />
                                <p v-if="form.errors.country" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.country }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing fields in details -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            {{ t('properties.pricing') }}
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ t('properties.basePricePerNight') }} (PLN)
                                </label>
                                <input
                                    v-model="form.base_price_per_night"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm px-3 py-2 border"
                                />
                                <p v-if="form.errors.base_price_per_night" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.base_price_per_night }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ t('properties.cleaningFee') }} (PLN)
                                </label>
                                <input
                                    v-model="form.cleaning_fee"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm px-3 py-2 border"
                                />
                                <p v-if="form.errors.cleaning_fee" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.cleaning_fee }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ t('properties.minNights') }}
                                </label>
                                <input
                                    v-model.number="form.min_nights"
                                    type="number"
                                    min="1"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm px-3 py-2 border"
                                />
                                <p v-if="form.errors.min_nights" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.min_nights }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ t('properties.checkInTime') }}
                                </label>
                                <input
                                    v-model="form.check_in_time"
                                    type="time"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm px-3 py-2 border"
                                />
                                <p v-if="form.errors.check_in_time" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.check_in_time }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ t('properties.checkOutTime') }}
                                </label>
                                <input
                                    v-model="form.check_out_time"
                                    type="time"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm px-3 py-2 border"
                                />
                                <p v-if="form.errors.check_out_time" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.check_out_time }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Amenities -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            {{ t('properties.amenities') }}
                        </h2>
                        <div v-for="(items, category) in amenitiesByCategory" :key="category" class="mb-4 last:mb-0">
                            <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide mb-2">
                                {{ category }}
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                                <label
                                    v-for="amenity in items"
                                    :key="amenity.id"
                                    class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer"
                                >
                                    <input
                                        type="checkbox"
                                        :value="amenity.id"
                                        :checked="form.amenity_ids.includes(amenity.id)"
                                        @change="toggleAmenity(amenity.id)"
                                        class="rounded border-gray-300 dark:border-gray-600 text-sky-600 focus:ring-sky-500"
                                    />
                                    {{ amenity.name_pl }}
                                </label>
                            </div>
                        </div>
                        <p v-if="form.errors.amenity_ids" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.amenity_ids }}</p>
                    </div>

                    <!-- Toggles -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            {{ t('properties.settings') }}
                        </h2>
                        <div class="space-y-4">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input
                                    v-model="form.reservations_enabled"
                                    type="checkbox"
                                    class="rounded border-gray-300 dark:border-gray-600 text-sky-600 focus:ring-sky-500 h-5 w-5"
                                />
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ t('properties.reservationsEnabled') }}
                                </span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input
                                    v-model="form.is_published"
                                    type="checkbox"
                                    class="rounded border-gray-300 dark:border-gray-600 text-sky-600 focus:ring-sky-500 h-5 w-5"
                                />
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ t('properties.isPublished') }}
                                </span>
                            </label>
                        </div>
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ t('properties.sortOrder') }}
                            </label>
                            <input
                                v-model.number="form.sort_order"
                                type="number"
                                min="0"
                                class="mt-1 block w-24 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm px-3 py-2 border"
                            />
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ t('properties.sortOrderHelp') }}</p>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="flex justify-end">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="bg-sky-600 hover:bg-sky-700 text-white rounded-lg px-4 py-2 text-sm font-medium disabled:opacity-50"
                        >
                            {{ t('properties.save') }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Photos tab -->
            <div v-show="activeTab === 'photos'" class="space-y-6">
                <!-- Upload area -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        {{ t('properties.uploadPhotos') }}
                    </h2>
                    <div class="flex items-center gap-4">
                        <input
                            ref="photoInput"
                            type="file"
                            multiple
                            accept="image/*"
                            class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-sky-50 file:text-sky-600 hover:file:bg-sky-100 dark:file:bg-sky-900/30 dark:file:text-sky-400"
                        />
                        <button
                            @click="uploadPhotos"
                            class="bg-sky-600 hover:bg-sky-700 text-white rounded-lg px-4 py-2 text-sm font-medium whitespace-nowrap"
                        >
                            {{ t('properties.upload') }}
                        </button>
                    </div>
                </div>

                <!-- Photos grid -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        {{ t('properties.existingPhotos') }}
                    </h2>
                    <div v-if="property.photos && property.photos.length > 0" class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div
                            v-for="photo in property.photos"
                            :key="photo.id"
                            class="relative group rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700"
                        >
                            <img
                                :src="photo.path.startsWith('http') ? photo.path : `/storage/${photo.path}`"
                                :alt="photo.filename"
                                class="w-full h-32 object-cover"
                            />
                            <div class="p-2">
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                    {{ photo.filename }}
                                </p>
                                <span
                                    v-if="photo.is_cover"
                                    class="inline-block mt-1 px-1.5 py-0.5 text-xs font-medium rounded bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-400"
                                >
                                    {{ t('properties.cover') }}
                                </span>
                            </div>
                            <div class="p-2 pt-0 flex gap-2">
                                <button
                                    v-if="!photo.is_cover"
                                    @click="setCover(photo.id)"
                                    class="text-xs text-sky-600 dark:text-sky-400 hover:underline"
                                >
                                    {{ t('properties.setCover') }}
                                </button>
                                <button
                                    @click="deletePhoto(photo.id)"
                                    class="text-xs text-red-600 dark:text-red-400 hover:underline"
                                >
                                    {{ t('properties.delete') }}
                                </button>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-sm text-gray-500 dark:text-gray-400">
                        {{ t('properties.noPhotos') }}
                    </p>
                </div>
            </div>

            <!-- Pricing tab -->
            <div v-show="activeTab === 'pricing'" class="space-y-6">
                <!-- Existing seasonal prices -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        {{ t('properties.seasonalPrices') }}
                    </h2>
                    <div v-if="property.seasonalPrices && property.seasonalPrices.length > 0" class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                    <th class="pb-2 font-medium text-gray-500 dark:text-gray-400">{{ t('properties.seasonName') }}</th>
                                    <th class="pb-2 font-medium text-gray-500 dark:text-gray-400">{{ t('properties.dateFrom') }}</th>
                                    <th class="pb-2 font-medium text-gray-500 dark:text-gray-400">{{ t('properties.dateTo') }}</th>
                                    <th class="pb-2 font-medium text-gray-500 dark:text-gray-400">{{ t('properties.pricePerNight') }}</th>
                                    <th class="pb-2 font-medium text-gray-500 dark:text-gray-400">{{ t('properties.minNights') }}</th>
                                    <th class="pb-2 font-medium text-gray-500 dark:text-gray-400">{{ t('properties.priority') }}</th>
                                    <th class="pb-2"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="sp in property.seasonalPrices"
                                    :key="sp.id"
                                    class="border-b border-gray-100 dark:border-gray-700/50"
                                >
                                    <td class="py-2 text-gray-900 dark:text-white">{{ sp.name }}</td>
                                    <td class="py-2 text-gray-600 dark:text-gray-300">{{ sp.date_from }}</td>
                                    <td class="py-2 text-gray-600 dark:text-gray-300">{{ sp.date_to }}</td>
                                    <td class="py-2 text-gray-900 dark:text-white">{{ formatPrice(sp.price_per_night) }} PLN</td>
                                    <td class="py-2 text-gray-600 dark:text-gray-300">{{ sp.min_nights }}</td>
                                    <td class="py-2 text-gray-600 dark:text-gray-300">{{ sp.priority }}</td>
                                    <td class="py-2">
                                        <button
                                            @click="deleteSeasonalPrice(sp.id)"
                                            class="text-xs text-red-600 dark:text-red-400 hover:underline"
                                        >
                                            {{ t('properties.delete') }}
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p v-else class="text-sm text-gray-500 dark:text-gray-400">
                        {{ t('properties.noSeasonalPrices') }}
                    </p>
                </div>

                <!-- Add season form -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        {{ t('properties.addSeason') }}
                    </h2>
                    <form @submit.prevent="submitSeason" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ t('properties.seasonName') }}
                            </label>
                            <input
                                v-model="seasonForm.name"
                                type="text"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm px-3 py-2 border"
                            />
                            <p v-if="seasonForm.errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ seasonForm.errors.name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ t('properties.dateFrom') }}
                            </label>
                            <input
                                v-model="seasonForm.date_from"
                                type="date"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm px-3 py-2 border"
                            />
                            <p v-if="seasonForm.errors.date_from" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ seasonForm.errors.date_from }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ t('properties.dateTo') }}
                            </label>
                            <input
                                v-model="seasonForm.date_to"
                                type="date"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm px-3 py-2 border"
                            />
                            <p v-if="seasonForm.errors.date_to" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ seasonForm.errors.date_to }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ t('properties.pricePerNight') }} (PLN)
                            </label>
                            <input
                                v-model="seasonForm.price_per_night"
                                type="number"
                                step="0.01"
                                min="0"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm px-3 py-2 border"
                            />
                            <p v-if="seasonForm.errors.price_per_night" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ seasonForm.errors.price_per_night }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ t('properties.minNights') }}
                            </label>
                            <input
                                v-model.number="seasonForm.min_nights"
                                type="number"
                                min="1"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm px-3 py-2 border"
                            />
                            <p v-if="seasonForm.errors.min_nights" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ seasonForm.errors.min_nights }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ t('properties.priority') }}
                            </label>
                            <input
                                v-model.number="seasonForm.priority"
                                type="number"
                                min="0"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm px-3 py-2 border"
                            />
                            <p v-if="seasonForm.errors.priority" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ seasonForm.errors.priority }}</p>
                        </div>

                        <div class="md:col-span-2 flex justify-end">
                            <button
                                type="submit"
                                :disabled="seasonForm.processing"
                                class="bg-sky-600 hover:bg-sky-700 text-white rounded-lg px-4 py-2 text-sm font-medium disabled:opacity-50"
                            >
                                {{ t('properties.addSeason') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
