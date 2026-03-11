<script setup>
import { Link } from '@inertiajs/vue3';
import { getIconSvg, getColorClasses } from '@/utils/iconUtils';

defineProps({
    activities: {
        type: Array,
        required: true
    }
});

const getIconClassesForActivity = (icon) => {
    const icons = {
        'shopping-cart': 'text-blue-500',
        'user-plus': 'text-green-500',
        'star': 'text-yellow-500',
        'edit': 'text-purple-500'
    };
    return icons[icon] || 'text-gray-500';
};
</script>

<template>
    <div class="overflow-hidden rounded-lg bg-white shadow-lg dark:bg-zinc-900">
        <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                Recent Activity
            </h3>
            <div class="mt-6 space-y-4">
                <div
                    v-for="activity in activities"
                    :key="activity.id"
                    class="flex items-start space-x-3"
                >
                    <div
                        :class="[
                            'flex size-8 shrink-0 items-center justify-center rounded-full',
                            getIconClassesForActivity(activity.icon)
                        ]"
                    >
                        <svg
                            class="size-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg"
                            v-html="getIconSvg(activity.icon)"
                        />
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm text-gray-900 dark:text-white">
                            <span class="font-medium">{{ activity.user }}</span>
                            {{ activity.action }}
                        </p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ activity.time }}
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="mt-6">
                <Link
                    :href="route('activity.index')"
                    class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-center text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-gray-300 dark:hover:bg-zinc-700"
                >
                    View all activity
                </Link>
            </div>
        </div>
    </div>
</template>
