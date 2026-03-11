<script setup>
import { getIconSvg, getColorClasses } from '@/utils/iconUtils';

const props = defineProps({
    section: {
        type: Object,
        required: true
    }
});

const getIconSvgContent = (iconName) => getIconSvg(iconName);
const getIconColorClasses = (colorName) => getColorClasses(colorName, 'both');
</script>

<template>
    <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-gray-200 dark:border-zinc-700 p-6 hover:shadow-md transition-shadow">
        <div class="flex items-center mb-4">
            <div
                :class="[
                    'flex items-center justify-center w-10 h-10 rounded-lg mr-3',
                    getIconColorClasses(section.color)
                ]"
            >
                <svg
                    class="w-5 h-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    v-html="getIconSvgContent(section.icon)"
                />
            </div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                {{ section.title }}
            </h3>
        </div>
        <ul class="space-y-2">
            <li v-for="article in section.articles" :key="article.slug">
                <a
                    :href="`/help/${article.slug}`"
                    class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 text-sm transition-colors"
                >
                    {{ article.title }}
                </a>
            </li>
        </ul>
    </div>
</template>
