<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { ref } from 'vue';
import axios from 'axios';

const { t } = useI18n();
const props = defineProps({ properties: Array });

const dragItem = ref(null);
const dragOverItem = ref(null);

const deleteProperty = (id) => {
    if (confirm(t('properties.deleteConfirm'))) {
        router.delete(`/admin/properties/${id}`);
    }
};

const formatPrice = (grosze) => {
    return (grosze / 100).toFixed(2);
};

function onDragStart(e, property) {
    dragItem.value = property;
    e.dataTransfer.effectAllowed = 'move';
}

function onDragOver(e, property) {
    e.preventDefault();
    dragOverItem.value = property.id;
}

function onDragEnd() {
    dragItem.value = null;
    dragOverItem.value = null;
}

function onDrop(e, targetProperty) {
    e.preventDefault();
    if (!dragItem.value) return;

    const items = [...props.properties];
    const fromIdx = items.findIndex(p => p.id === dragItem.value.id);
    const toIdx = items.findIndex(p => p.id === targetProperty.id);
    if (fromIdx === -1 || toIdx === -1 || fromIdx === toIdx) return;

    const [moved] = items.splice(fromIdx, 1);
    items.splice(toIdx, 0, moved);

    const order = items.map(p => p.id);
    axios.post('/admin/properties/reorder', { order });

    // Optimistic local update
    items.forEach((p, i) => p.sort_order = i);

    dragItem.value = null;
    dragOverItem.value = null;
}
</script>

<template>
    <AdminLayout>
        <div class="p-4 sm:p-6 lg:p-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ t('properties.title') }}
                </h1>
                <Link
                    href="/admin/properties/create"
                    class="bg-sky-600 hover:bg-sky-700 text-white rounded-lg px-4 py-2 text-sm font-medium"
                >
                    {{ t('properties.add') }}
                </Link>
            </div>

            <!-- Empty state -->
            <div
                v-if="!properties || properties.length === 0"
                class="bg-white dark:bg-gray-800 rounded-lg shadow p-12 text-center"
            >
                <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">
                    {{ t('properties.empty') }}
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ t('properties.emptyDesc') }}
                </p>
                <Link
                    href="/admin/properties/create"
                    class="mt-4 inline-block bg-sky-600 hover:bg-sky-700 text-white rounded-lg px-4 py-2 text-sm font-medium"
                >
                    {{ t('properties.add') }}
                </Link>
            </div>

            <!-- Properties grid -->
            <div
                v-else
                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
            >
                <div
                    v-for="p in properties"
                    :key="p.id"
                    class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden transition-all cursor-move"
                    :class="dragOverItem === p.id ? 'ring-2 ring-sky-500 scale-105' : ''"
                    draggable="true"
                    @dragstart="onDragStart($event, p)"
                    @dragover="onDragOver($event, p)"
                    @drop="onDrop($event, p)"
                    @dragend="onDragEnd"
                >
                    <!-- Cover photo or placeholder -->
                    <div class="relative h-48 bg-gray-200 dark:bg-gray-700">
                        <img
                            v-if="p.cover_photo_url"
                            :src="p.cover_photo_url"
                            :alt="p.name"
                            class="w-full h-full object-cover"
                        />
                        <div v-else class="w-full h-full flex items-center justify-center">
                            <svg class="w-12 h-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z" />
                            </svg>
                        </div>
                        <span class="absolute top-2 left-2 px-2 py-0.5 text-xs font-medium rounded bg-sky-500 text-white">
                            {{ p.type }}
                        </span>
                        <div class="absolute top-2 right-2 p-1.5 bg-white/90 dark:bg-gray-800/90 rounded cursor-grab">
                            <svg class="w-4 h-4 text-gray-600 dark:text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                                <circle cx="9" cy="5" r="1.5"/><circle cx="15" cy="5" r="1.5"/>
                                <circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/>
                                <circle cx="9" cy="19" r="1.5"/><circle cx="15" cy="19" r="1.5"/>
                            </svg>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-4">
                        <h3 class="font-semibold text-gray-900 dark:text-white truncate">
                            {{ p.name }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                            {{ p.city }}
                        </p>

                        <p class="mt-2 text-sm font-medium text-gray-900 dark:text-white">
                            {{ formatPrice(p.base_price_per_night) }} PLN / {{ t('public.pricePerNight') }}
                        </p>

                        <!-- Stats row -->
                        <div class="mt-2 flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                            <span v-if="p.photos_count !== undefined">{{ p.photos_count }} {{ t('properties.photos') }}</span>
                            <span v-if="p.bookings_count !== undefined">{{ p.bookings_count }} {{ t('properties.bookings') }}</span>
                        </div>

                        <!-- Published badge -->
                        <div class="mt-3">
                            <span
                                v-if="p.is_published"
                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400"
                            >
                                {{ t('properties.published') }}
                            </span>
                            <span
                                v-else
                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400"
                            >
                                {{ t('properties.unpublished') }}
                            </span>
                        </div>

                        <!-- Actions -->
                        <div class="mt-4 flex items-center justify-between border-t border-gray-100 dark:border-gray-700 pt-3">
                            <Link
                                :href="`/admin/properties/${p.id}/edit`"
                                class="bg-sky-600 hover:bg-sky-700 text-white rounded-lg px-4 py-2 text-sm font-medium"
                            >
                                {{ t('properties.edit') }}
                            </Link>
                            <button
                                @click="deleteProperty(p.id)"
                                class="bg-red-600 hover:bg-red-700 text-white rounded-lg px-4 py-2 text-sm font-medium"
                            >
                                {{ t('properties.delete') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
