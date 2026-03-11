<template>
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ title }}
        </h2>
        <slot name="actions">
            <Link
                v-if="createRoute"
                :href="route(createRoute)"
                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H8m8 8l-8-8-8 8" />
                </svg>
                {{ createText }}
            </Link>
        </slot>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    title: {
        type: String,
        required: true
    },
    createRoute: {
        type: String,
        default: null
    },
    createText: {
        type: String,
        default: 'New Item'
    }
});
</script>
