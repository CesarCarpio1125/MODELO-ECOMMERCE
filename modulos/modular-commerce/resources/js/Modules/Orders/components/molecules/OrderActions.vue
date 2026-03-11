<template>
    <div class="flex items-center space-x-2">
        <Link 
            v-if="order?.id"
            :href="route('orders.edit', order.id)" 
            class="text-blue-600 hover:text-blue-900 font-medium text-sm"
        >
            Edit
        </Link>
        <Link 
            v-if="order?.id"
            :href="route('orders.show', order.id)" 
            class="text-blue-600 hover:text-blue-900 font-medium text-sm"
        >
            View
        </Link>
        <button 
            v-if="order?.id"
            @click="handleDelete(order.id, order.order_number)" 
            class="text-red-600 hover:text-red-900 font-medium text-sm"
        >
            Delete
        </button>
        <span v-else class="text-gray-500 text-sm">No actions available</span>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    order: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['delete']);

const handleDelete = (orderId, orderNumber) => {
    emit('delete', { orderId, orderNumber });
};
</script>
