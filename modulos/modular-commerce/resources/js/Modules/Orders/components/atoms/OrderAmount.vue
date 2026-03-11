<script setup>
import { computed } from 'vue';
import { useOrderHelpers } from '@/composables/useOrderHelpers';

const props = defineProps({
    amount: {
        type: [Number, String],
        required: true
    },
    currency: {
        type: String,
        default: 'USD'
    },
    showCurrency: {
        type: Boolean,
        default: true
    },
    size: {
        type: String,
        default: 'default',
        validator: (value) => ['sm', 'default', 'lg', 'xl'].includes(value)
    }
});

const { formatCurrency } = useOrderHelpers();

// Computed properties
const formattedAmount = computed(() => {
    const numAmount = typeof props.amount === 'string' 
        ? parseFloat(props.amount) 
        : props.amount;
    return formatCurrency(numAmount);
});

// Size classes
const sizeClasses = computed(() => {
    const sizes = {
        sm: 'text-sm',
        default: 'text-base',
        lg: 'text-lg',
        xl: 'text-xl'
    };
    return sizes[props.size] || sizes.default;
});

// Weight classes
const weightClasses = computed(() => {
    const weights = {
        sm: 'font-medium',
        default: 'font-semibold',
        lg: 'font-bold',
        xl: 'font-bold'
    };
    return weights[props.size] || weights.default;
});
</script>

<template>
    <span 
        :class="[sizeClasses, weightClasses]"
        class="text-gray-900 dark:text-white"
    >
        {{ formattedAmount }}
    </span>
</template>
