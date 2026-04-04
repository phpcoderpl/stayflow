<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const { locale } = useI18n();

const props = defineProps({
    terms_pl: { type: String, default: '' },
    terms_en: { type: String, default: '' },
    brandName: { type: String, default: 'StayFlow' },
});

const content = computed(() => {
    if (locale.value === 'en' && props.terms_en) return props.terms_en;
    return props.terms_pl || '';
});
</script>

<template>
    <PublicLayout>
        <div class="max-w-3xl mx-auto px-4 sm:px-6 py-10 sm:py-16">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white mb-8">
                {{ locale === 'pl' ? 'Regulamin' : 'Terms & Conditions' }}
            </h1>

            <div v-if="content" class="prose prose-gray dark:prose-invert max-w-none text-gray-600 dark:text-gray-300 leading-relaxed whitespace-pre-line">
                {{ content }}
            </div>

            <div v-else class="text-center py-16">
                <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
                <p class="text-gray-500 dark:text-gray-400">
                    {{ locale === 'pl' ? 'Regulamin nie zostal jeszcze dodany.' : 'Terms have not been added yet.' }}
                </p>
            </div>
        </div>
    </PublicLayout>
</template>
