<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { getIconSvg, getColorClasses } from '@/utils/iconUtils';

const props = defineProps({
    activities: {
        type: Object,
        required: true
    }
});

// Computed properties
const hasActivities = computed(() => {
    return props.activities?.data && props.activities.data.length > 0;
});

const paginationInfo = computed(() => {
    if (!props.activities) return null;
    
    return {
        current_page: props.activities.current_page,
        from: props.activities.from,
        last_page: props.activities.last_page,
        per_page: props.activities.per_page,
        to: props.activities.to,
        total: props.activities.total,
        has_previous: props.activities.prev_page_url !== null,
        has_next: props.activities.next_page_url !== null,
        prev_page_url: props.activities.prev_page_url,
        next_page_url: props.activities.next_page_url,
        links: props.activities.links || []
    };
});

// Helper functions
const getIconClasses = (icon, color) => {
    return getColorClasses(color, 'both');
};

const getIconSvgContent = (icon) => {
    return getIconSvg(icon);
};
</script>

<template>
    <Head title="Activity" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                        Activity Log
                    </h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        View your recent activity and actions
                    </p>
                </div>

                <!-- Activities List -->
                <div class="bg-white dark:bg-zinc-800 shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <!-- Empty State -->
                        <div v-if="!hasActivities" class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No activity found</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Your activity will appear here as you use the system.</p>
                        </div>

                        <!-- Activities List -->
                        <div v-else class="space-y-4">
                            <div
                                v-for="activity in activities.data"
                                :key="activity.id"
                                class="flex items-start space-x-3 p-4 hover:bg-gray-50 dark:hover:bg-zinc-700 rounded-lg transition-colors"
                            >
                                <div
                                    :class="[
                                        'flex size-8 shrink-0 items-center justify-center rounded-full',
                                        getIconClasses(activity.icon, activity.color)
                                    ]"
                                >
                                    <svg
                                        class="size-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                        xmlns="http://www.w3.org/2000/svg"
                                        v-html="getIconSvgContent(activity.icon)"
                                    />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm text-gray-900 dark:text-white">
                                        <span class="font-medium">{{ activity.user?.name || 'System' }}</span>
                                        {{ activity.description }}
                                    </p>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        {{ activity.created_at?.diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="hasActivities && paginationInfo" class="bg-white dark:bg-zinc-800 px-4 py-3 border-t border-gray-200 dark:border-zinc-700 sm:px-6">
                        <div class="flex items-center justify-between">
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
                                            :key="link.label" 
                                            :href="link.url" 
                                            :class="link.active ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50 dark:bg-zinc-800 dark:border-zinc-600 dark:text-gray-300 dark:hover:bg-zinc-700'" 
                                            class="relative inline-flex items-center px-4 py-2 border text-sm font-medium focus:z-20 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                            v-html="link.label"
                                        />
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
