<template>
    <div class="bg-white dark:bg-zinc-800 px-4 py-3 flex items-center justify-between border-t border-gray-200 dark:border-zinc-700 sm:px-6">
        <!-- Mobile pagination -->
        <div class="flex-1 flex justify-between sm:hidden">
            <Link 
                v-if="paginationInfo.prev_page_url" 
                :href="paginationInfo.prev_page_url" 
                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 dark:bg-zinc-800 dark:border-zinc-600 dark:text-gray-300 dark:hover:bg-zinc-700"
            >
                Previous
            </Link>
            <span 
                v-else 
                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-400 bg-gray-100 dark:bg-zinc-800 dark:border-zinc-600 dark:text-gray-500 cursor-not-allowed"
            >
                Previous
            </span>
            <Link 
                v-if="paginationInfo.next_page_url" 
                :href="paginationInfo.next_page_url" 
                class="relative ml-3 inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 dark:bg-zinc-800 dark:border-zinc-600 dark:text-gray-300 dark:hover:bg-zinc-700"
            >
                Next
            </Link>
            <span 
                v-else 
                class="relative ml-3 inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-400 bg-gray-100 dark:bg-zinc-800 dark:border-zinc-600 dark:text-gray-500 cursor-not-allowed"
            >
                Next
            </span>
        </div>

        <!-- Desktop pagination -->
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-700 dark:text-gray-300">
                    Showing
                    <span class="font-medium">{{ paginationInfo.from }}</span>
                    to
                    <span class="font-medium">{{ paginationInfo.to }}</span>
                    of
                    <span class="font-medium">{{ paginationInfo.total }}</span>
                    results
                </p>
            </div>
            <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                    <Link 
                        v-for="link in paginationInfo.links" 
                        :key="link?.label" 
                        :href="link?.url" 
                        :class="link?.active ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50 dark:bg-zinc-800 dark:border-zinc-600 dark:text-gray-300 dark:hover:bg-zinc-700'" 
                        class="relative inline-flex items-center px-4 py-2 border text-sm font-medium focus:z-20 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        {{ link?.label }}
                    </Link>
                </nav>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    paginationInfo: {
        type: Object,
        required: true
    }
});
</script>
