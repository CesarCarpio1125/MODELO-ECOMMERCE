<script setup>
import { ref, onMounted, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useOrderHelpers } from '@/composables/useOrderHelpers';

const props = defineProps({
    order: {
        type: Object,
        required: true
    },
    customers: {
        type: Array,
        required: true
    },
    products: {
        type: Array,
        required: true
    },
    canUpdateStatus: {
        type: Boolean,
        required: true
    }
});

// Use composables
const { formatCurrency, calculateOrderTotal } = useOrderHelpers();

const form = useForm({
    customer_id: props.order?.customer_id || '',
    items: props.order?.items?.map(item => ({
        id: item?.id,
        product_id: item?.product_id || '',
        quantity: item?.quantity || 1,
        unit_price: item?.unit_price || 0
    })) || [{
        product_id: '',
        quantity: 1,
        unit_price: 0
    }],
    notes: props.order?.notes || '',
    payment_method: props.order?.payment_method || 'credit_card',
    status: props.order?.status || 'pending'
});

// Computed properties for performance
const calculatedTotal = computed(() => calculateOrderTotal(form.items));

const addItem = () => {
    form.items.push({
        product_id: '',
        quantity: 1,
        unit_price: 0
    });
};

const removeItem = (index) => {
    form.items.splice(index, 1);
};

const updateItemPrice = (index) => {
    const product = props.products.find(p => p.id === form.items[index].product_id);
    if (product) {
        form.items[index].unit_price = product.price;
    }
};

const calculateTotal = () => {
    return calculatedTotal.value;
};

const submit = () => {
    const orderId = props.order?.id;
    if (!orderId) {
        console.error('Order ID is null or undefined');
        return;
    }
    form.put(route('orders.update', orderId), {
        onSuccess: () => {
            // Form reset is handled by Inertia
        }
    });
};

onMounted(() => {
    // Ensure we have at least one item
    if (form.items.length === 0) {
        addItem();
    }
});
</script>

<template>
    <Head title="Edit Order" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Edit Order #{{ order?.order_number }}
                </h2>
                <div class="flex space-x-3">
                    <Link 
                        v-if="order?.id"
                        :href="route('orders.show', order.id)" 
                        class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        View Order
                    </Link>
                    <Link 
                        :href="route('orders.index')" 
                        class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7l7 7" />
                        </svg>
                        Back to Orders
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Customer Selection -->
                    <div class="bg-white dark:bg-zinc-800 shadow rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                            Customer Information
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Customer
                                </label>
                                <select 
                                    v-model="form.customer_id" 
                                    class="w-full rounded-md border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white focus:border-blue-500 focus:ring-blue-500"
                                    required
                                >
                                    <option value="">Select a customer</option>
                                    <option v-for="customer in customers" :key="customer?.id" :value="customer?.id">
                                        {{ customer?.first_name }} {{ customer?.last_name }} ({{ customer?.email }})
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Status
                                </label>
                                <select 
                                    v-model="form.status" 
                                    class="w-full rounded-md border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white focus:border-blue-500 focus:ring-blue-500"
                                    :disabled="!canUpdateStatus"
                                    :class="{ 'bg-gray-100 dark:bg-zinc-600 cursor-not-allowed': !canUpdateStatus }"
                                    required
                                >
                                    <option value="pending">Pending</option>
                                    <option value="processing">Processing</option>
                                    <option value="shipped">Shipped</option>
                                    <option value="delivered">Delivered</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                                <p v-if="!canUpdateStatus" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Only vendors and admins can change order status
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div class="bg-white dark:bg-zinc-800 shadow rounded-lg p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                Order Items
                            </h3>
                            <button 
                                type="button" 
                                @click="addItem" 
                                class="inline-flex items-center px-3 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H8m8 8l-8-8-8 8" />
                                </svg>
                                Add Item
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div v-for="(item, index) in form.items" :key="index" class="grid grid-cols-1 md:grid-cols-5 gap-4 p-4 border border-gray-200 dark:border-zinc-700 rounded-lg">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Product
                                    </label>
                                    <select 
                                        v-model="item.product_id" 
                                        @change="updateItemPrice(index)"
                                        class="w-full rounded-md border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white focus:border-blue-500 focus:ring-blue-500"
                                        required
                                    >
                                        <option value="">Select a product</option>
                                        <option v-for="product in products" :key="product?.id" :value="product?.id">
                                            {{ product?.name }} ({{ formatCurrency(product?.price) }})
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Quantity
                                    </label>
                                    <input 
                                        v-model.number="item.quantity" 
                                        type="number" 
                                        min="1" 
                                        class="w-full rounded-md border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white focus:border-blue-500 focus:ring-blue-500"
                                        required
                                    >
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Unit Price
                                    </label>
                                    <input 
                                        v-model.number="item.unit_price" 
                                        type="number" 
                                        step="0.01" 
                                        min="0" 
                                        class="w-full rounded-md border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white focus:border-blue-500 focus:ring-blue-500"
                                        required
                                    >
                                </div>
                                <div class="flex items-end">
                                    <button 
                                        type="button" 
                                        @click="removeItem(index)" 
                                        class="w-full px-3 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                        :disabled="form.items.length === 1"
                                    >
                                        Remove
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Information -->
                    <div class="bg-white dark:bg-zinc-800 shadow rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                            Additional Information
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Payment Method
                                </label>
                                <select 
                                    v-model="form.payment_method" 
                                    class="w-full rounded-md border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white focus:border-blue-500 focus:ring-blue-500"
                                    required
                                >
                                    <option value="credit_card">Credit Card</option>
                                    <option value="debit_card">Debit Card</option>
                                    <option value="paypal">PayPal</option>
                                    <option value="cash">Cash</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Total Amount
                                </label>
                                <div class="text-lg font-bold text-gray-900 dark:text-white">
                                    {{ formatCurrency(calculatedTotal) }}
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Notes
                            </label>
                            <textarea 
                                v-model="form.notes" 
                                rows="3" 
                                class="w-full rounded-md border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Add any notes about this order..."
                            ></textarea>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end space-x-3">
                        <Link 
                            v-if="order?.id"
                            :href="route('orders.show', order.id)" 
                            class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        >
                            Cancel
                        </Link>
                        <button 
                            type="submit" 
                            class="px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            :disabled="form.processing"
                        >
                            {{ form.processing ? 'Updating...' : 'Update Order' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script>
const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount || 0);
};
</script>
