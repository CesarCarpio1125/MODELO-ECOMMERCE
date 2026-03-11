<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import OrdersPageTemplate from '@/Modules/Orders/components/templates/OrdersPageTemplate.vue';

defineProps({
    orders: {
        type: Object,
        required: true
    },
    filters: {
        type: Object,
        default: () => ({})
    }
});
</script>

<template>
    <Head title="My Orders" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                                My Orders
                            </h1>
                            <p class="mt-2 text-gray-600 dark:text-gray-400">
                                View and manage your orders
                            </p>
                        </div>
                        <Link 
                            :href="route('orders.create')"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H8m8 8l-8-8-8 8" />
                            </svg>
                            New Order
                        </Link>
                    </div>
                </div>

                <OrdersPageTemplate :orders="orders" :filters="filters" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
