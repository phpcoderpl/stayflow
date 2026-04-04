<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { computed } from 'vue';

const { t } = useI18n();

const props = defineProps({
    amenities: Array,
});

const form = useForm({
    name: '',
    type: 'apartment',
    address: '',
    city: '',
    postal_code: '',
    country: 'PL',
    description_pl: '',
    description_en: '',
    max_guests: 4,
    bedrooms: 1,
    bathrooms: 1,
    area_sqm: null,
    base_price_per_night: '',
    cleaning_fee: '',
    check_in_time: '15:00',
    check_out_time: '11:00',
    min_nights: 1,
    video_url: '',
    reservations_enabled: true,
    is_published: false,
    amenity_ids: [],
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

const submit = () => {
    const data = form.data();
    if (data.base_price_per_night !== '' && data.base_price_per_night !== null) {
        data.base_price_per_night = Math.round(parseFloat(data.base_price_per_night) * 100);
    }
    if (data.cleaning_fee !== '' && data.cleaning_fee !== null) {
        data.cleaning_fee = Math.round(parseFloat(data.cleaning_fee) * 100);
    }
    form.transform(() => data).post('/admin/properties');
};
</script>

<template>
    <AdminLayout>
        <div class="p-4 sm:p-6 lg:p-8 max-w-4xl">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ t('properties.create') }}
                </h1>
                <Link
                    href="/admin/properties"
                    class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200"
                >
                    {{ t('properties.backToList') }}
                </Link>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
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

                <!-- Pricing -->
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
    </AdminLayout>
</template>
