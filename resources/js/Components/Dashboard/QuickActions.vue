<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    actions: {
        type: Array,
        required: true
    }
});

const getColorClasses = (color) => {
    const colors = {
        blue: 'bg-blue-500 hover:bg-blue-600 text-white',
        green: 'bg-green-500 hover:bg-green-600 text-white',
        purple: 'bg-purple-500 hover:bg-purple-600 text-white',
        orange: 'bg-orange-500 hover:bg-orange-600 text-white'
    };
    return colors[color] || 'bg-gray-500 hover:bg-gray-600 text-white';
};

const getIconSvg = (icon) => {
    const svgs = {
        'plus-circle': '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        'package': '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>',
        'chart-bar': '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>',
        'headset': '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>'
    };
    return svgs[icon] || svgs['plus-circle'];
};
</script>

<template>
    <div class="overflow-hidden rounded-lg bg-white shadow-lg dark:bg-zinc-900">
        <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                Quick Actions
            </h3>
            <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Link
                    v-for="action in actions"
                    :key="action.label"
                    :href="action.href"
                    :class="[
                        'flex items-center justify-center space-x-2 rounded-lg px-4 py-3 text-sm font-medium transition-colors duration-200',
                        getColorClasses(action.color)
                    ]"
                >
                    <svg
                        class="size-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg"
                        v-html="getIconSvg(action.icon)"
                    />
                    <span>{{ action.label }}</span>
                </Link>
            </div>
        </div>
    </div>
</template>
