<script setup>
defineProps({
    faq: {
        type: Object,
        required: true
    },
    isExpanded: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['toggle']);

const handleToggle = () => {
    emit('toggle');
};
</script>

<template>
    <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-gray-200 dark:border-zinc-700">
        <button
            @click="handleToggle"
            class="w-full px-6 py-4 text-left flex items-center justify-between hover:bg-gray-50 dark:hover:bg-zinc-700 transition-colors"
        >
            <div class="flex items-center">
                <span class="text-sm font-medium text-blue-600 dark:text-blue-400 mr-3">
                    {{ faq.category }}
                </span>
                <span class="text-gray-900 dark:text-white font-medium">
                    {{ faq.question }}
                </span>
            </div>
            <svg
                class="w-5 h-5 text-gray-500 transform transition-transform"
                :class="{ 'rotate-180': isExpanded }"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
        <div
            v-show="isExpanded"
            class="px-6 pb-4 text-gray-600 dark:text-gray-400"
        >
            {{ faq.answer }}
        </div>
    </div>
</template>
