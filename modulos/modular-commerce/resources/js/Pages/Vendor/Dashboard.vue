<script setup>
import { Head } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'
import { ref } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import StoreCard from '@/modules/vendor/components/organisms/StoreCard.vue'
import VendorEditForm from '@/modules/vendor/components/molecules/VendorEditForm.vue'
import { router } from '@inertiajs/vue3'

const { vendors, user } = defineProps({
    vendors: {
        type: Array,
        required: true
    },
    user: {
        type: Object,
        required: true
    }
})

// Edit form state
const showEditForm = ref(false)
const editingVendor = ref(null)

const editStore = (vendor) => {
    // Navigate to edit page
    window.location.href = route('vendor.edit', { vendor: vendor.id })
}

const deleteStore = (vendor) => {
    if (confirm(`¿Estás seguro de que quieres eliminar "${vendor.store_name}"? Esta acción no se puede deshacer.`)) {
        router.delete(route('vendor.destroy', vendor.id))
    }
}

const viewStore = (vendor) => {
    // Navigate to store public view
    window.location.href = route('store.show', vendor.store_slug)
}

const closeEditForm = () => {
    showEditForm.value = false
    editingVendor.value = null
}
</script>

<template>
    <Head title="Seller Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Mis Tiendas ({{ vendors.length }})
                    </h2>
                    <span class="px-3 py-1 text-xs font-medium text-white bg-green-600 rounded-full">
                        Vendor
                    </span>
                </div>
                <div class="flex items-center space-x-4">
                    <Link
                        :href="route('vendor.activate')"
                        class="inline-flex items-center justify-center rounded-lg px-4 py-2 font-medium text-white bg-gradient-to-r from-indigo-600 to-purple-600 transition-all duration-200 hover:from-indigo-700 hover:to-purple-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Nueva Tienda
                    </Link>
                    <Link 
                        :href="route('dashboard')"
                        class="text-sm text-indigo-600 hover:text-indigo-500 dark:text-indigo-400"
                    >
                        Back to Main Dashboard
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- No stores message -->
                <div v-if="vendors.length === 0" class="text-center py-12">
                    <div class="mx-auto w-24 h-24 bg-gray-200 dark:bg-gray-800 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                        No tienes tiendas aún
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">
                        Crea tu primera tienda para empezar a vender
                    </p>
                    <Link
                        :href="route('vendor.activate')"
                        class="inline-flex items-center justify-center rounded-lg px-6 py-3 font-medium text-white bg-gradient-to-r from-indigo-600 to-purple-600 transition-all duration-200 hover:from-indigo-700 hover:to-purple-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Crear Primera Tienda
                    </Link>
                </div>

                <!-- Stores Grid -->
                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <StoreCard
                        v-for="vendor in vendors"
                        :key="vendor.id"
                        :vendor="vendor"
                        @editStore="editStore"
                        @deleteStore="deleteStore"
                        @viewStore="viewStore"
                    />
                </div>
            </div>
        </div>

        <!-- Edit Form Modal -->
        <VendorEditForm
            v-if="showEditForm && editingVendor"
            :vendor="editingVendor"
            :user="user"
            @close="closeEditForm"
        />
    </AuthenticatedLayout>
</template>
