<script setup>
import { computed } from 'vue';
import { useOrderHelpers } from '@/composables/useOrderHelpers';

const props = defineProps({
    status: {
        type: String,
        default: 'pending'
    },
    size: {
        type: String,
        default: 'default',
        validator: (value) => ['sm', 'default', 'lg'].includes(value)
    }
});

const { getStatusColor, getStatusIcon, getStatusClasses } = useOrderHelpers();

// Computed properties
const statusColor = computed(() => getStatusColor(props.status));
const statusIcon = computed(() => getStatusIcon(props.status));
const statusClasses = computed(() => getStatusClasses(props.status));

// Size classes
const sizeClasses = computed(() => {
    const sizes = {
        sm: 'px-2 py-0.5 text-xs',
        default: 'px-2.5 py-0.5 text-xs',
        lg: 'px-3 py-1 text-sm'
    };
    return sizes[props.size] || sizes.default;
});
</script>

<template>
    <span 
        class="inline-flex items-center rounded-full font-medium" 
        :class="[sizeClasses, statusClasses]"
    >
        <svg 
            v-if="statusIcon" 
            class="w-4 h-4 mr-1" 
            fill="none" 
            stroke="currentColor" 
            viewBox="0 0 24 24"
        >
            <path 
                stroke-linecap="round" 
                stroke-linejoin="round" 
                stroke-width="2" 
                :d="statusIcon" 
            />
        </svg>
        <slot>{{ status }}</slot>
    </span>
</template>
