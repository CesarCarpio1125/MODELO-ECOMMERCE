<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    stats: {
        type: Array,
        required: true
    }
});

const getColorClasses = (color) => {
    const colors = {
        blue: 'bg-blue-500 text-white',
        green: 'bg-green-500 text-white',
        purple: 'bg-purple-500 text-white',
        orange: 'bg-orange-500 text-white'
    };
    return colors[color] || 'bg-gray-500 text-white';
};

const getTrendIcon = (trend) => {
    return trend === 'up' ? '↑' : '↓';
};
</script>

<template>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div
            v-for="stat in stats"
            :key="stat.label"
            class="overflow-hidden rounded-lg bg-white shadow-lg transition-all duration-300 hover:shadow-xl dark:bg-zinc-900"
        >
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                            {{ stat.label }}
                        </p>
                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                            {{ stat.value }}
                        </p>
                    </div>
                    <div
                        :class="[
                            'flex size-12 items-center justify-center rounded-full',
                            getColorClasses(stat.color)
                        ]"
                    >
                        <span class="text-lg font-bold">
                            {{ getTrendIcon(stat.trend) }}
                        </span>
                    </div>
                </div>
                <div class="mt-4 flex items-center">
                    <span
                        :class="[
                            'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                            stat.trend === 'up' 
                                ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                        ]"
                    >
                        {{ getTrendIcon(stat.trend) }} {{ stat.change }}
                    </span>
                    <span class="ml-2 text-sm text-gray-500 dark:text-gray-400">
                        from last month
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>
