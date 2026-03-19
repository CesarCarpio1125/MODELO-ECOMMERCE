<script setup>
import { ref } from 'vue'
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
import ProductCard from '@/Components/Product/ProductCard.vue'
import { useProductFilters } from '@/composables/useProductFilters'

const props = defineProps({
  vendor: Object,
  products: Array,
  filters: Object,
})

const {
  searchQuery,
  statusFilter,
  filteredProducts,
  productStats,
  resetFilters
} = useProductFilters(props.products, props.filters)

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
</script>

<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <div>
          <h2 class="text-xl font-semibold leading-tight text-white">
            Store Management - {{ vendor.store_name }}
          </h2>
          <p class="mt-1 text-sm text-white/60">
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

    <div class="py-6 bg-black min-h-screen">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Filters -->
        <div class="bg-white/[0.04] border border-white/[0.08] p-6 rounded-2xl shadow-[0_4px_40px_rgba(0,0,0,0.4)] mb-6">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <InputLabel for="search" value="Search Products" class="text-white/50" />
              <TextInput
                id="search"
                v-model="searchQuery"
                type="text"
                class="mt-1 block w-full bg-white/[0.06] border border-white/[0.08] text-white placeholder-white/20"
                placeholder="Search by name, SKU, or description..."
              />
            </div>
            <div>
              <InputLabel for="status" value="Status Filter" class="text-white/50" />
              <select
                id="status"
                v-model="statusFilter"
                class="mt-1 block w-full rounded-md bg-white/[0.06] border border-white/[0.08] text-white focus:border-violet-500/60 focus:bg-white/[0.08]"
              >
                <option value="all">All Products</option>
                <option value="active">Active</option>
                <option value="draft">Draft</option>
                <option value="archived">Archived</option>
              </select>
            </div>
            <div class="flex items-end">
              <div class="text-sm text-white/60">
                <p>Total Products: {{ productStats.total }}</p>
                <p>Active: {{ productStats.active }}</p>
                <p v-if="productStats.lowStock > 0" class="text-yellow-400">Low Stock: {{ productStats.lowStock }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Products Grid -->
        <div v-if="filteredProducts.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          <ProductCard
            v-for="product in filteredProducts"
            :key="product.id"
            :product="product"
            @edit="router.visit(route('store.products.edit', product.id))"
            @delete="confirmDelete(product)"
            @toggle-status="toggleProductStatus(product)"
          />
        </div>

        <!-- Empty State -->
        <div v-else class="bg-white/[0.04] border border-white/[0.08] p-12 rounded-2xl shadow-[0_4px_40px_rgba(0,0,0,0.4)] text-center">
          <svg class="mx-auto h-12 w-12 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-white">No products found</h3>
          <p class="mt-1 text-sm text-white/40">
            {{ searchQuery || statusFilter !== 'all' ? 'Try adjusting your filters' : 'Get started by creating your first product' }}
          </p>
          <div class="mt-6 flex gap-3 justify-center">
            <SecondaryButton @click="showQuickAddModal = true" class="bg-white/[0.06] border border-white/[0.08] text-white hover:bg-white/[0.10]">
              Quick Add Product
            </SecondaryButton>
            <Link :href="route('store.products.create', vendor.id)">
              <PrimaryButton class="bg-violet-600 hover:bg-violet-500 shadow-[0_4px_20px_rgba(124,58,237,0.35)]">
                Add New Product
              </PrimaryButton>
            </Link>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Modal -->
    <Modal :show="showDeleteModal" @close="showDeleteModal = false">
      <div class="p-6 bg-[#1e2a3a] border border-white/[0.08] rounded-2xl">
        <h2 class="text-lg font-medium text-white">
          Delete Product
        </h2>
        <p class="mt-1 text-sm text-white/60">
          Are you sure you want to delete "{{ productToDelete?.name }}"? This action cannot be undone.
        </p>

        <div class="mt-6 flex justify-end gap-3">
          <SecondaryButton @click="showDeleteModal = false" class="bg-white/[0.06] border border-white/[0.08] text-white hover:bg-white/[0.10]">
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
