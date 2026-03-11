<script setup>
import { ref, computed } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import DangerButton from '@/Components/DangerButton.vue'
import TextInput from '@/Components/TextInput.vue'
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'
import Modal from '@/Components/Modal.vue'
import QuickAddProductModal from '@/Components/ProductForm/QuickAddProductModal.vue'

const props = defineProps({
  vendor: Object,
  products: Array,
  filters: Object,
})

const searchQuery = ref(props.filters.search || '')
const statusFilter = ref(props.filters.status || 'all')

const filteredProducts = computed(() => {
  let filtered = props.products

  if (statusFilter.value !== 'all') {
    filtered = filtered.filter(product => product.status === statusFilter.value)
  }

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(product =>
      product.name.toLowerCase().includes(query) ||
      product.description?.toLowerCase().includes(query) ||
      product.sku?.toLowerCase().includes(query)
    )
  }

  return filtered
})

const deleteProductForm = useForm({})
const showDeleteModal = ref(false)
const productToDelete = ref(null)
const showQuickAddModal = ref(false)

const confirmDelete = (product) => {
  productToDelete.value = product
  showDeleteModal.value = true
}

const deleteProduct = () => {
  deleteProductForm.delete(route('store.products.destroy', productToDelete.value.id), {
    onSuccess: () => {
      showDeleteModal.value = false
      productToDelete.value = null
    },
  })
}

const toggleProductStatus = (product) => {
  router.patch(route('store.products.toggle-status', product.id))
}

const formatPrice = (price) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(price)
}

const getStatusColor = (status) => {
  switch (status) {
    case 'active': return 'bg-green-100 text-green-800'
    case 'draft': return 'bg-gray-100 text-gray-800'
    case 'archived': return 'bg-red-100 text-red-800'
    default: return 'bg-gray-100 text-gray-800'
  }
}

const getStockStatus = (stock) => {
  if (stock === 0) return { text: 'Out of Stock', color: 'text-red-600' }
  if (stock <= 5) return { text: 'Low Stock', color: 'text-yellow-600' }
  return { text: 'In Stock', color: 'text-green-600' }
}
</script>

<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <div>
          <h2 class="text-xl font-semibold leading-tight text-gray-800">
            Store Management - {{ vendor.store_name }}
          </h2>
          <p class="mt-1 text-sm text-gray-600">
            Manage your products and inventory
          </p>
        </div>
        <div class="flex gap-3">
          <SecondaryButton @click="showQuickAddModal = true">
            Quick Add Product
          </SecondaryButton>
          <Link :href="route('store.products.create', vendor.id)">
            <PrimaryButton>
              Add New Product
            </PrimaryButton>
          </Link>
        </div>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Filters -->
        <div class="bg-white p-6 rounded-lg shadow mb-6">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <InputLabel for="search" value="Search Products" />
              <TextInput
                id="search"
                v-model="searchQuery"
                type="text"
                class="mt-1 block w-full"
                placeholder="Search by name, SKU, or description..."
              />
            </div>
            <div>
              <InputLabel for="status" value="Status Filter" />
              <select
                id="status"
                v-model="statusFilter"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              >
                <option value="all">All Products</option>
                <option value="active">Active</option>
                <option value="draft">Draft</option>
                <option value="archived">Archived</option>
              </select>
            </div>
            <div class="flex items-end">
              <div class="text-sm text-gray-600">
                <p>Total Products: {{ filteredProducts.length }}</p>
                <p>Active: {{ filteredProducts.filter(p => p.status === 'active').length }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Products Grid -->
        <div v-if="filteredProducts.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="product in filteredProducts"
            :key="product.id"
            class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow"
          >
            <!-- Product Image -->
            <div class="aspect-w-16 aspect-h-9 bg-gray-200 rounded-t-lg">
              <img
                v-if="product.featured_image"
                :src="product.featured_image_url"
                :alt="product.name"
                class="w-full h-48 object-cover rounded-t-lg"
              />
              <div v-else class="w-full h-48 bg-gray-200 rounded-t-lg flex items-center justify-center">
                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>
            </div>

            <!-- Product Info -->
            <div class="p-4">
              <div class="flex justify-between items-start mb-2">
                <h3 class="text-lg font-semibold text-gray-900 truncate">
                  {{ product.name }}
                </h3>
                <span :class="getStatusColor(product.status)" class="px-2 py-1 text-xs rounded-full">
                  {{ product.status }}
                </span>
              </div>

              <p v-if="product.description" class="text-sm text-gray-600 mb-3 line-clamp-2">
                {{ product.description }}
              </p>

              <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                  <span class="text-gray-500">Price:</span>
                  <span class="font-semibold">{{ formatPrice(product.price) }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-500">Stock:</span>
                  <span :class="getStockStatus(product.stock_quantity).color">
                    {{ product.stock_quantity }} ({{ getStockStatus(product.stock_quantity).text }})
                  </span>
                </div>
                <div v-if="product.sku" class="flex justify-between">
                  <span class="text-gray-500">SKU:</span>
                  <span class="font-mono text-xs">{{ product.sku }}</span>
                </div>
                <div v-if="product.variants && product.variants.length > 0" class="flex justify-between">
                  <span class="text-gray-500">Variants:</span>
                  <span>{{ product.variants.length }}</span>
                </div>
              </div>

              <!-- Actions -->
              <div class="mt-4 flex gap-2">
                <Link :href="route('store.products.edit', product.id)">
                  <SecondaryButton size="sm">
                    Edit
                  </SecondaryButton>
                </Link>
                <SecondaryButton
                  size="sm"
                  @click="toggleProductStatus(product)"
                  :disabled="deleteProductForm.processing"
                >
                  {{ product.status === 'active' ? 'Deactivate' : 'Activate' }}
                </SecondaryButton>
                <DangerButton
                  size="sm"
                  @click="confirmDelete(product)"
                  :disabled="deleteProductForm.processing"
                >
                  Delete
                </DangerButton>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="bg-white p-12 rounded-lg shadow text-center">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">No products found</h3>
          <p class="mt-1 text-sm text-gray-500">
            {{ searchQuery || statusFilter !== 'all' ? 'Try adjusting your filters' : 'Get started by creating your first product' }}
          </p>
          <div class="mt-6 flex gap-3 justify-center">
            <SecondaryButton @click="showQuickAddModal = true">
              Quick Add Product
            </SecondaryButton>
            <Link :href="route('store.products.create', vendor.id)">
              <PrimaryButton>
                Add New Product
              </PrimaryButton>
            </Link>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Modal -->
    <Modal :show="showDeleteModal" @close="showDeleteModal = false">
      <div class="p-6">
        <h2 class="text-lg font-medium text-gray-900">
          Delete Product
        </h2>
        <p class="mt-1 text-sm text-gray-600">
          Are you sure you want to delete "{{ productToDelete?.name }}"? This action cannot be undone.
        </p>

        <div class="mt-6 flex justify-end gap-3">
          <SecondaryButton @click="showDeleteModal = false">
            Cancel
          </SecondaryButton>
          <DangerButton
            @click="deleteProduct"
            :disabled="deleteProductForm.processing"
          >
            Delete Product
          </DangerButton>
        </div>
      </div>
    </Modal>

    <!-- Quick Add Product Modal -->
    <QuickAddProductModal
      :show="showQuickAddModal"
      :vendor="vendor"
      @close="showQuickAddModal = false"
    />
  </AuthenticatedLayout>
</template>
