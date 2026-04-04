<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
</script>

<template>
    <AdminLayout>
        <div class="p-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $t('dashboard.title') }}</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">{{ $t('app.tagline') }}</p>
        </div>
    </AdminLayout>
</template>
