<script setup>
import { ref, onMounted } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    customers: {
        type: Array,
        required: true
    },
    products: {
        type: Array,
        required: true
    }
});

const form = useForm({
    customer_id: '',
    items: [
        {
            product_id: '',
            quantity: 1,
            unit_price: 0
        }
    ],
    notes: '',
    payment_method: 'credit_card'
});

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
    return form.items.reduce((total, item) => {
        return total + (item.quantity * item.unit_price);
    }, 0);
};

const submit = () => {
    form.post(route('orders.store'), {
        onSuccess: () => {
            form.reset();
        }
    });
};

onMounted(() => {
    // Set first customer as default if available
    if (props.customers.length > 0) {
        form.customer_id = props.customers[0].id;
    }
});
</script>

<template>
    <Head title="Create Order" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Create New Order
                </h2>
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
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Customer Selection -->
                    <div class="bg-white dark:bg-zinc-800 shadow rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                            Customer Information
                        </h3>
                        
                        <div class="grid grid-cols-1 gap-6">
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
                                    <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                                        {{ customer.first_name }} {{ customer.last_name }} ({{ customer.email }})
                                    </option>
                                </select>
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
                            <div v-for="(item, index) in form.items" :key="index" class="grid grid-cols-1 md:grid-cols-4 gap-4 p-4 border border border-gray-200 dark:border-zinc-700 rounded-lg">
                                <div>
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
                                        <option v-for="product in products" :key="product.id" :value="product.id">
                                            {{ product.name }} ({{ product.sku }}) - ${{ product.price }}
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
                                
                                <div class="flex items-center justify-center">
                                    <button 
                                        type="button" 
                                        @click="removeItem(index)" 
                                        class="text-red-600 hover:text-red-800"
                                        v-if="form.items.length > 1"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6M14 10v6m-4 0v-6" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 p-4 bg-gray-50 dark:bg-zinc-700 rounded-lg">
                            <div class="flex justify-between items-center">
                                <span class="text-lg font-medium text-gray-900 dark:text-white">
                                    Total: ${{ calculateTotal().toFixed(2) }}
                                </span>
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
                                    <option value="paypal">PayPal</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Notes
                                </label>
                                <textarea 
                                    v-model="form.notes" 
                                    rows="3" 
                                    class="w-full rounded-md border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="Order notes..."
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end">
                        <button 
                            type="submit" 
                            :disabled="form.processing" 
                            class="inline-flex items-center px-6 py-3 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                        >
                            <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4V12z"></path>
                            </svg>
                            Create Order
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
