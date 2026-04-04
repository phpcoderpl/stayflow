<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { router, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { ref, computed } from 'vue';
import { getIconPath, isEmoji, iconPaths, iconLabels } from '@/composables/amenityIcons.js';
import axios from 'axios';

const { t } = useI18n();

const props = defineProps({
    amenities: Array,
});

const showForm = ref(false);
const editingId = ref(null);
const imageInput = ref(null);
const showIconPicker = ref(false);
const newCategory = ref('');
const showNewCategory = ref(false);

const dragItem = ref(null);
const dragOverItem = ref(null);

const form = useForm({
    name_pl: '',
    name_en: '',
    category: 'general',
    icon: '',
    image: null,
    sort_order: 0,
});

// Built-in + custom categories (derived from existing amenities)
const defaultCategories = ['general', 'kitchen', 'outdoor', 'entertainment', 'bathroom', 'safety'];

const allCategories = computed(() => {
    const cats = new Set(defaultCategories);
    if (props.amenities) {
        props.amenities.forEach(a => {
            if (a.category) cats.add(a.category);
        });
    }
    return [...cats];
});

const amenitiesByCategory = computed(() => {
    const grouped = {};
    allCategories.value.forEach(c => { grouped[c] = []; });
    if (props.amenities) {
        props.amenities.forEach(a => {
            if (!grouped[a.category]) grouped[a.category] = [];
            grouped[a.category].push(a);
        });
    }
    return grouped;
});

function categoryLabel(cat) {
    const key = 'amenitiesAdmin.categories.' + cat;
    const translated = t(key);
    // If no translation, capitalize the raw category name
    return translated === key ? cat.charAt(0).toUpperCase() + cat.slice(1) : translated;
}

// Emoji icon picker - common amenity icons
const iconGroups = [
    { label: 'Ogolne', icons: ['📶', '🅿️', '❄️', '🔥', '🧹', '🛗', '🔑', '🏠', '🏢', '🌟', '✨', '💡'] },
    { label: 'Kuchnia', icons: ['🍳', '🥘', '☕', '🫖', '🍽️', '🧊', '🔪', '🥤', '🍕', '🧂', '🥄', '🫕'] },
    { label: 'Lazienka', icons: ['🚿', '🛁', '🧴', '🪥', '💇', '🧻', '🧼', '🪒', '🩹', '💊'] },
    { label: 'Sypialnia', icons: ['🛏️', '🛋️', '🧸', '👕', '🧺', '🪟', '🪞', '🧲'] },
    { label: 'Rozrywka', icons: ['📺', '🎮', '📻', '🎵', '📚', '🎲', '🏓', '🎯'] },
    { label: 'Na zewnatrz', icons: ['🏊', '🌳', '🌺', '🍖', '☀️', '🏖️', '⛱️', '🚲', '🧗', '🎣'] },
    { label: 'Bezpieczenstwo', icons: ['🔒', '🧯', '🚨', '🩺', '📹', '🔔', '🛡️', '🆘'] },
    { label: 'Dzieci', icons: ['👶', '🧒', '🎠', '🪀', '🧩', '🎨', '📐', '🎒'] },
    { label: 'Zwierzeta', icons: ['🐕', '🐈', '🦮', '🐾', '🦴'] },
];

const svgIconSlugs = Object.keys(iconPaths);

function selectIcon(icon) {
    form.icon = icon;
    showIconPicker.value = false;
}

function addCategory() {
    if (newCategory.value.trim()) {
        form.category = newCategory.value.trim().toLowerCase().replace(/\s+/g, '_');
        newCategory.value = '';
        showNewCategory.value = false;
    }
}

function openAdd() {
    editingId.value = null;
    form.reset();
    showForm.value = true;
}

function openEdit(amenity) {
    editingId.value = amenity.id;
    form.name_pl = amenity.name_pl;
    form.name_en = amenity.name_en || '';
    form.category = amenity.category;
    form.icon = amenity.icon || '';
    form.image = null;
    form.sort_order = amenity.sort_order || 0;
    showForm.value = true;
}

function submit() {
    const formData = new FormData();
    if (editingId.value) formData.append('_method', 'PUT');
    formData.append('name_pl', form.name_pl);
    formData.append('name_en', form.name_en);
    formData.append('category', form.category);
    formData.append('icon', form.icon);
    formData.append('sort_order', form.sort_order);
    if (imageInput.value?.files?.[0]) {
        formData.append('image', imageInput.value.files[0]);
    }
    const url = editingId.value ? `/admin/amenities/${editingId.value}` : '/admin/amenities';
    router.post(url, formData, {
        onSuccess: () => {
            showForm.value = false;
            form.reset();
        },
    });
}

function deleteAmenity(id) {
    if (confirm(t('amenitiesAdmin.deleteConfirm'))) {
        router.delete(`/admin/amenities/${id}`);
    }
}

function deleteCategory(cat) {
    if (cat === 'general') return;
    if (confirm(t('amenitiesAdmin.deleteCategoryConfirm'))) {
        router.delete('/admin/amenities-category', { data: { category: cat } });
    }
}

function onDragStart(e, amenity, cat) {
    dragItem.value = { amenity, cat };
    e.dataTransfer.effectAllowed = 'move';
}

function onDragOver(e, amenity, cat) {
    e.preventDefault();
    if (dragItem.value?.cat === cat) {
        dragOverItem.value = amenity.id;
    }
}

function onDragEnd() {
    dragItem.value = null;
    dragOverItem.value = null;
}

function onDrop(e, targetAmenity, cat) {
    e.preventDefault();
    if (!dragItem.value || dragItem.value.cat !== cat) return;

    const items = [...(amenitiesByCategory.value[cat] || [])];
    const fromIdx = items.findIndex(a => a.id === dragItem.value.amenity.id);
    const toIdx = items.findIndex(a => a.id === targetAmenity.id);
    if (fromIdx === -1 || toIdx === -1 || fromIdx === toIdx) return;

    const [moved] = items.splice(fromIdx, 1);
    items.splice(toIdx, 0, moved);

    const order = items.map(a => a.id);
    axios.post('/admin/amenities/reorder', { order });

    // Optimistic local update
    items.forEach((a, i) => a.sort_order = i);

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
                    {{ t('amenitiesAdmin.title') }}
                </h1>
                <button
                    @click="openAdd"
                    class="bg-sky-600 hover:bg-sky-700 text-white rounded-lg px-4 py-2 text-sm font-medium"
                >
                    {{ t('amenitiesAdmin.add') }}
                </button>
            </div>

            <!-- Add/Edit Form Modal -->
            <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center">
                <div class="fixed inset-0 bg-black/50" @click="showForm = false" />
                <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        {{ editingId ? t('amenitiesAdmin.edit') : t('amenitiesAdmin.add') }}
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ t('amenitiesAdmin.name_pl') }} *
                            </label>
                            <input
                                v-model="form.name_pl"
                                type="text"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ t('amenitiesAdmin.name_en') }}
                            </label>
                            <input
                                v-model="form.name_en"
                                type="text"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500"
                            />
                        </div>

                        <!-- Category with option to add new -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ t('amenitiesAdmin.category') }}
                            </label>
                            <div class="flex gap-2">
                                <select
                                    v-if="!showNewCategory"
                                    v-model="form.category"
                                    class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500"
                                >
                                    <option v-for="cat in allCategories" :key="cat" :value="cat">
                                        {{ categoryLabel(cat) }}
                                    </option>
                                </select>
                                <div v-else class="flex-1 flex gap-2">
                                    <input
                                        v-model="newCategory"
                                        type="text"
                                        :placeholder="t('amenitiesAdmin.newCategoryPlaceholder')"
                                        class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500"
                                        @keyup.enter="addCategory"
                                    />
                                    <button
                                        @click="addCategory"
                                        class="px-3 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700"
                                    >OK</button>
                                </div>
                                <button
                                    @click="showNewCategory = !showNewCategory"
                                    class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700"
                                    :title="showNewCategory ? t('amenitiesAdmin.cancel') : t('amenitiesAdmin.newCategory')"
                                >
                                    {{ showNewCategory ? '×' : '+' }}
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ t('amenitiesAdmin.sort_order') }}
                            </label>
                            <input
                                v-model="form.sort_order"
                                type="number"
                                class="w-24 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500"
                            />
                        </div>

                        <!-- Icon picker -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ t('amenitiesAdmin.icon') }}
                            </label>
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-12 h-12 flex items-center justify-center rounded-lg border-2 border-gray-300 dark:border-gray-600 cursor-pointer hover:border-sky-500 transition bg-white dark:bg-gray-900"
                                    @click="showIconPicker = !showIconPicker"
                                >
                                    <svg v-if="form.icon && getIconPath(form.icon)" class="w-6 h-6 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" :d="getIconPath(form.icon)" />
                                    </svg>
                                    <span v-else class="text-2xl">{{ form.icon || '?' }}</span>
                                </div>
                                <div class="flex-1">
                                    <span v-if="form.icon && getIconPath(form.icon)" class="text-sm text-gray-700 dark:text-gray-300">
                                        {{ iconLabels[form.icon] || form.icon }}
                                    </span>
                                    <input
                                        v-else
                                        v-model="form.icon"
                                        type="text"
                                        :placeholder="t('amenitiesAdmin.iconPlaceholder')"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500"
                                    />
                                </div>
                                <button
                                    type="button"
                                    @click="showIconPicker = !showIconPicker"
                                    class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700"
                                >
                                    {{ showIconPicker ? t('amenitiesAdmin.hide') : t('amenitiesAdmin.pick') }}
                                </button>
                            </div>
                            <!-- Icon picker grid -->
                            <div v-if="showIconPicker" class="mt-3 p-3 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-900 max-h-72 overflow-y-auto">
                                <!-- SVG icons (matching public site) -->
                                <p class="text-xs font-semibold text-sky-600 dark:text-sky-400 mb-2">SVG</p>
                                <div class="flex flex-wrap gap-1 mb-4">
                                    <button
                                        v-for="slug in svgIconSlugs"
                                        :key="slug"
                                        type="button"
                                        @click="selectIcon(slug)"
                                        class="w-10 h-10 flex items-center justify-center rounded border border-gray-200 dark:border-gray-700 hover:bg-sky-100 dark:hover:bg-sky-900/30 transition"
                                        :class="form.icon === slug ? 'bg-sky-100 dark:bg-sky-900/30 ring-2 ring-sky-500' : 'bg-white dark:bg-gray-800'"
                                        :title="iconLabels[slug] || slug"
                                    >
                                        <svg class="w-5 h-5 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" :d="getIconPath(slug)" />
                                        </svg>
                                    </button>
                                </div>

                                <!-- Emoji icons -->
                                <p class="text-xs font-semibold text-sky-600 dark:text-sky-400 mb-2">Emoji</p>
                                <div v-for="group in iconGroups" :key="group.label" class="mb-3 last:mb-0">
                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ group.label }}</p>
                                    <div class="flex flex-wrap gap-1">
                                        <button
                                            v-for="icon in group.icons"
                                            :key="icon"
                                            type="button"
                                            @click="selectIcon(icon)"
                                            class="w-9 h-9 flex items-center justify-center rounded hover:bg-sky-100 dark:hover:bg-sky-900/30 text-xl transition"
                                            :class="form.icon === icon ? 'bg-sky-100 dark:bg-sky-900/30 ring-2 ring-sky-500' : ''"
                                        >
                                            {{ icon }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ t('amenitiesAdmin.image') }}
                            </label>
                            <input
                                ref="imageInput"
                                type="file"
                                accept="image/*"
                                class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-sky-50 file:text-sky-600 hover:file:bg-sky-100 dark:file:bg-sky-900/30 dark:file:text-sky-400"
                            />
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ t('amenitiesAdmin.imageOrIcon') }}</p>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button
                            @click="showForm = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg"
                        >
                            {{ t('amenitiesAdmin.cancel') }}
                        </button>
                        <button
                            @click="submit"
                            class="bg-sky-600 hover:bg-sky-700 text-white rounded-lg px-4 py-2 text-sm font-medium"
                        >
                            {{ t('amenitiesAdmin.save') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Amenities by category -->
            <div class="space-y-6">
                <template v-for="cat in allCategories" :key="cat">
                    <div v-if="amenitiesByCategory[cat]?.length > 0" class="bg-white dark:bg-gray-800 rounded-lg shadow">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ categoryLabel(cat) }}
                            </h2>
                            <button
                                v-if="cat !== 'general'"
                                @click="deleteCategory(cat)"
                                class="text-xs text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                                :title="t('amenitiesAdmin.deleteCategoryHint')"
                            >
                                {{ t('amenitiesAdmin.deleteCategory') }}
                            </button>
                        </div>
                        <div class="divide-y divide-gray-100 dark:divide-gray-700">
                            <div
                                v-for="amenity in amenitiesByCategory[cat]"
                                :key="amenity.id"
                                class="px-6 py-3 flex items-center justify-between transition-colors"
                                :class="dragOverItem === amenity.id ? 'bg-sky-50 dark:bg-sky-900/20' : ''"
                                draggable="true"
                                @dragstart="onDragStart($event, amenity, cat)"
                                @dragover="onDragOver($event, amenity, cat)"
                                @drop="onDrop($event, amenity, cat)"
                                @dragend="onDragEnd"
                            >
                                <div class="flex items-center gap-3">
                                    <svg class="w-4 h-4 text-gray-400 cursor-grab" fill="currentColor" viewBox="0 0 24 24">
                                        <circle cx="9" cy="5" r="1.5"/><circle cx="15" cy="5" r="1.5"/>
                                        <circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/>
                                        <circle cx="9" cy="19" r="1.5"/><circle cx="15" cy="19" r="1.5"/>
                                    </svg>
                                    <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700 overflow-hidden flex-shrink-0">
                                        <img
                                            v-if="amenity.image_path"
                                            :src="`/storage/${amenity.image_path}`"
                                            :alt="amenity.name_pl"
                                            class="w-full h-full object-cover"
                                        />
                                        <span v-else-if="amenity.icon && isEmoji(amenity.icon)" class="text-lg">{{ amenity.icon }}</span>
                                        <svg v-else-if="amenity.icon && getIconPath(amenity.icon)" class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" :d="getIconPath(amenity.icon)" />
                                        </svg>
                                        <span v-else-if="amenity.icon" class="text-lg">{{ amenity.icon }}</span>
                                        <span v-else class="text-gray-400 text-xs">-</span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ amenity.name_pl }}
                                        </p>
                                        <p v-if="amenity.name_en" class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ amenity.name_en }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-gray-400 dark:text-gray-500 mr-2">
                                        #{{ amenity.sort_order }}
                                    </span>
                                    <button
                                        @click="openEdit(amenity)"
                                        class="text-sm text-sky-600 dark:text-sky-400 hover:underline"
                                    >
                                        {{ t('amenitiesAdmin.edit') }}
                                    </button>
                                    <button
                                        @click="deleteAmenity(amenity.id)"
                                        class="text-sm text-red-600 dark:text-red-400 hover:underline"
                                    >
                                        {{ t('app.delete') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <div
                    v-if="!amenities || amenities.length === 0"
                    class="bg-white dark:bg-gray-800 rounded-lg shadow p-12 text-center"
                >
                    <p class="text-gray-500 dark:text-gray-400">
                        {{ t('amenitiesAdmin.noAmenities') }}
                    </p>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
