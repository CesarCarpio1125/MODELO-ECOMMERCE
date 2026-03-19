<script setup>
import { Head } from '@inertiajs/vue3'
import { Link, usePage, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { useNativeImages } from '@/composables/useNativeImages'
import ProductCard from '@/Components/Product/ProductCard.vue'

const { vendor, products } = defineProps({
    vendor: {
        type: Object,
        required: true
    },
    products: {
        type: Array,
        default: () => []
    }
})

const page = usePage()
const { 
    imageErrors, 
    loadingImages, 
    isNative, 
    getImageUrl, 
    handleImageLoad, 
    handleImageError, 
    startImageLoad,
    refreshImageCache 
} = useNativeImages()

// Reactive state for image errors (backward compatibility)
const storeImageError = ref(false)
const productImageErrors = ref({})

// Initialize image cache refresh if in NativePHP
if (isNative()) {
    // Force refresh on mount
    setTimeout(() => {
        refreshImageCache()
    }, 1000)
}

// Check if current user owns this store
const isStoreOwner = () => {
    // Convert both to strings for comparison
    return String(vendor.user_id) === String(page.props.auth?.user?.id)
}

// Delete product function
const deleteProduct = async (productId) => {
    console.log('Attempting to delete product:', productId)
    console.log('Current user products:', products.map(p => ({ id: p.id, name: p.name, vendor_id: p.vendor_id })))
    
    if (!confirm('Are you sure you want to delete this product?')) {
        return
    }
    
    try {
        console.log('Sending delete request to:', route('store.products.destroy', productId))
        await router.delete(route('store.products.destroy', productId))
        console.log('Delete request successful')
        // Refresh the page to show updated products
        window.location.reload()
    } catch (error) {
        console.error('Error deleting product:', error)
        console.error('Error details:', error.response?.data || error.message)
    }
}
</script>

<template>
    <Head :title="vendor.store_name" />

    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <!-- Store Header -->
        <div class="bg-white dark:bg-gray-800 shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <!-- Back Navigation -->
                <div class="mb-6">
                    <Link 
                        :href="route('vendor.dashboard')" 
                        class="inline-flex items-center text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Stores
                    </Link>
                </div>

                <div class="flex items-center space-x-6">
                    <!-- Store Image -->
                    <div class="w-24 h-24 bg-gray-200 dark:bg-gray-700 rounded-lg overflow-hidden flex-shrink-0">
                        <img
                            v-if="vendor.store_image_url && !storeImageError && !imageErrors['vendor']"
                            :src="getImageUrl(vendor.store_image_url?.replace(/^.*\/storage\//, ''))"
                            :alt="vendor.store_name"
                            class="w-full h-full object-cover"
                            @load="handleImageLoad('vendor')"
                            @error="() => { handleImageError('vendor'); storeImageError = true }"
                        />
                        <div
                            v-else
                            class="w-full h-full flex items-center justify-center bg-gradient-to-r from-indigo-600 to-purple-600"
                        >
                            <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </div>
                    </div>

                    <!-- Store Info -->
                    <div class="flex-1">
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                            {{ vendor.store_name }}
                        </h1>
                        <p class="text-gray-600 dark:text-gray-400 mt-2">
                            {{ vendor.description || 'Sin descripción disponible' }}
                        </p>
                        <div class="mt-4">
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200"
                            >
                                Tienda Activa
                            </span>
                        </div>
                    </div>

                    <!-- Store Owner Actions -->
                    <div v-if="isStoreOwner()" class="flex-shrink-0">
                        <Link :href="route('store.manage')" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-md transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Manage Store
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Store Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            
            <div v-if="products.length > 0">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-8">
                    Our Products
                </h2>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <ProductCard
                        v-for="product in products"
                        :key="product.id"
                        :product="product"
                        :show-actions="true"
                        @edit="router.visit(route('store.products.edit', product.id))"
                        @delete="deleteProduct(product.id)"
                    />
                </div>
            </div>

            <div v-else class="text-center py-12">
                <div class="w-16 h-16 bg-gray-200 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                </div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                    No Products Available
                </h2>
                <p class="text-gray-600 dark:text-gray-400 mb-6">
                    This store hasn't added any products yet. Check back soon!
                </p>
                
                <!-- Store Owner CTA -->
                <div v-if="isStoreOwner()" class="space-y-4">
                    <p class="text-sm text-indigo-600 dark:text-indigo-400 font-medium">
                        This is your store! Add your first product to get started.
                    </p>
                    <Link :href="route('store.products.create', vendor.id)" class="inline-flex items-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-md transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Your First Product
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
