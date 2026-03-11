<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import OrderStatusBadge from '../atoms/OrderStatusBadge.vue';
import OrderAmount from '../atoms/OrderAmount.vue';
import OrderActions from './OrderActions.vue';
import { useOrderHelpers } from '@/composables/useOrderHelpers';

const props = defineProps({
    order: {
        type: Object,
        required: true
    },
    showActions: {
        type: Boolean,
        default: true
    }
});

const emit = defineEmits(['delete']);

const { formatOrderNumber } = useOrderHelpers();

// Computed properties
const hasValidId = computed(() => !!props.order?.id);
const customerName = computed(() => {
    return `${props.order?.customer?.first_name || ''} ${props.order?.customer?.last_name || ''}`.trim();
});
const createdDate = computed(() => {
    return props.order?.created_at
        ? new Date(props.order.created_at).toLocaleDateString()
        : '';
});
</script>

<template>
    <tr class="hover:bg-gray-50 dark:hover:bg-zinc-700">
        <!-- Order Number -->
        <td class="px-6 py-4 whitespace-nowrap">
            <Link 
                v-if="hasValidId"
                :href="route('orders.show', order?.id)" 
                class="text-blue-600 hover:text-blue-900 font-medium"
            >
                {{ formatOrderNumber(order?.order_number) }}
            </Link>
            <span v-else class="text-gray-500">No ID</span>
        </td>

        <!-- Customer -->
        <td class="px-6 py-4 whitespace-nowrap">
            {{ customerName }}
        </td>

        <!-- Total Amount -->
        <td class="px-6 py-4 whitespace-nowrap">
            <OrderAmount :amount="order?.total_amount || 0" size="sm" />
        </td>

        <!-- Status -->
        <td class="px-6 py-4 whitespace-nowrap">
            <OrderStatusBadge :status="order?.status || 'unknown'" />
        </td>

        <!-- Created Date -->
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
            {{ createdDate }}
        </td>

        <!-- Actions -->
        <td v-if="showActions" class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
            <OrderActions :order="order" @delete="emit('delete', $event)" />
        </td>
    </tr>
</template>
