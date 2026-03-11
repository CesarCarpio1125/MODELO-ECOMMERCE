<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Modules/Orders/components/atoms/PageHeader.vue';
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
    <Head title="Orders" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader 
                title="Orders" 
                create-route="orders.create" 
                create-text="New Order" 
            />
        </template>

        <OrdersPageTemplate :orders="orders" :filters="filters" />
    </AuthenticatedLayout>
</template>
