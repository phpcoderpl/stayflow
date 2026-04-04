<script setup>
import { ref, onMounted, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { locale } = useI18n();
const page = usePage();

const brandName = computed(() => page.props.brandName || 'StayFlow');

const isDark = ref(false);
const mobileMenu = ref(false);

onMounted(() => {
    isDark.value = localStorage.getItem('stayflow_dark') === 'true';
    applyDarkMode();
});

function applyDarkMode() {
    if (isDark.value) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
}

function toggleDarkMode() {
    isDark.value = !isDark.value;
    localStorage.setItem('stayflow_dark', isDark.value);
    applyDarkMode();
}

function toggleLocale() {
    const newLocale = locale.value === 'pl' ? 'en' : 'pl';
    locale.value = newLocale;
    localStorage.setItem('stayflow_locale', newLocale);
}
</script>

<template>
    <div class="min-h-screen flex flex-col bg-white dark:bg-gray-950">
        <!-- Header -->
        <header class="sticky top-0 z-30 bg-white/80 dark:bg-gray-950/80 backdrop-blur border-b border-gray-100 dark:border-gray-800">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
                <!-- Brand -->
                <Link href="/" class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-sky-500 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                    </div>
                    <span class="text-lg font-bold text-gray-900 dark:text-white">{{ brandName }}</span>
                </Link>

                <!-- Navigation links -->
                <nav class="hidden sm:flex items-center gap-1">
                    <Link href="/" class="px-3 py-1.5 rounded-lg text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                        {{ locale === 'pl' ? 'Oferta' : 'Properties' }}
                    </Link>
                    <Link href="/contact" class="px-3 py-1.5 rounded-lg text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                        {{ locale === 'pl' ? 'Kontakt' : 'Contact' }}
                    </Link>
                    <Link href="/terms" class="px-3 py-1.5 rounded-lg text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                        {{ locale === 'pl' ? 'Regulamin' : 'Terms' }}
                    </Link>
                </nav>

                <!-- Right controls -->
                <div class="flex items-center gap-1.5">
                    <!-- Mobile menu -->
                    <div class="sm:hidden relative">
                        <button @click="mobileMenu = !mobileMenu" class="p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                            </svg>
                        </button>
                        <div v-if="mobileMenu" class="absolute right-0 top-full mt-1 w-48 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg py-2 z-50">
                            <Link href="/" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800" @click="mobileMenu = false">
                                {{ locale === 'pl' ? 'Oferta' : 'Properties' }}
                            </Link>
                            <Link href="/contact" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800" @click="mobileMenu = false">
                                {{ locale === 'pl' ? 'Kontakt' : 'Contact' }}
                            </Link>
                            <Link href="/terms" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800" @click="mobileMenu = false">
                                {{ locale === 'pl' ? 'Regulamin' : 'Terms' }}
                            </Link>
                        </div>
                    </div>

                    <!-- Locale toggle -->
                    <button
                        @click="toggleLocale"
                        class="px-2.5 py-1.5 rounded-lg text-xs font-semibold text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition uppercase"
                    >
                        {{ locale === 'pl' ? 'EN' : 'PL' }}
                    </button>

                    <!-- Dark mode toggle -->
                    <button
                        @click="toggleDarkMode"
                        class="p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition"
                    >
                        <svg v-if="isDark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                        </svg>
                        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                        </svg>
                    </button>
                </div>
            </div>
        </header>

        <!-- Main content -->
        <main class="flex-1">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 py-8">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-gray-500 dark:text-gray-400">
                    <div class="flex items-center gap-2">
                        <div class="w-5 h-5 rounded bg-sky-500 flex items-center justify-center">
                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                            </svg>
                        </div>
                        <span class="font-medium text-gray-700 dark:text-gray-300">{{ brandName }}</span>
                        <span>&copy; 2026</span>
                    </div>
                    <div class="text-xs text-gray-400 dark:text-gray-500">
                        Powered by <span class="font-medium text-sky-600 dark:text-sky-400">StayFlow</span>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
