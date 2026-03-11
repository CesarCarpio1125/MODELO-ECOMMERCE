<script setup>
import { Link, useForm, onMounted } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import InputError from '@/Components/InputError.vue'

const props = defineProps({
  // For Edit mode
  product: {
    type: Object,
    default: null
  },
  // For Create mode
  vendor: {
    type: Object,
    default: null
  }
})

// Detect if we're in edit or create mode
const isEditMode = computed(() => !!props.product)
const isCreateMode = computed(() => !!props.vendor && !props.product)

// Form data - initialize differently for edit vs create
const form = useForm(isEditMode.value ? {
  name: props.product.name,
  description: props.product.description || '',
  price: props.product.price,
  stock_quantity: props.product.stock_quantity,
  status: props.product.status,
} : {
  name: '',
  description: '',
  price: '',
  stock_quantity: 0,
  status: 'draft'
})

// Dynamic computed properties
const title = computed(() => isEditMode.value ? 'Update Product' : 'Add New Product')
const subtitle = computed(() => isEditMode.value
  ? `Modify the details for "${props.product.name}".`
  : 'Fill in details below to list a new product.'
)
const submitButtonText = computed(() => isEditMode.value
  ? (form.processing ? 'Updating...' : 'Update Product')
  : (form.processing ? 'Adding...' : 'Add Product')
)
const submitIcon = computed(() => isEditMode.value ? 'save' : 'plus')

const submit = () => {
  if (isEditMode.value) {
    // Edit mode - PUT to update
    form.put(route('store.products.update', props.product.id))
  } else {
    // Create mode - POST to store
    if (!props.vendor?.id) {
      console.error('No vendor ID available!')
      return
    }

    form.post(route('store.products.store', props.vendor.id), {
      onSuccess: () => {
        console.log('Product created successfully!')
        form.reset()
      },
      onError: (errors) => {
        console.log('Form errors:', errors)
      }
    })
  }
}

// Initialize Lucide icons
onMounted(() => {
  if (typeof lucide !== 'undefined') {
    lucide.createIcons()
  }
})
</script>

<template>
  <AuthenticatedLayout>
    <div class="bg-black min-h-screen flex items-center justify-center font-['Inter',system-ui,sans-serif] p-6">
      <div class="w-full max-w-lg">
        <!-- Header -->
        <div class="mb-8">
          <div class="inline-flex items-center gap-2 bg-white/5 border border-white/10 rounded-full px-3 py-1 mb-4">
            <i data-lucide="package" class="w-3.5 h-3.5 text-violet-400" style="stroke-width:1.5"></i>
            <span class="text-xs text-white/50 tracking-wide uppercase">
              Inventory
            </span>
          </div>
          <h1 class="text-2xl font-semibold text-white tracking-tight">
            {{ title }}
          </h1>
          <p class="text-sm text-white/40 mt-1">
            {{ subtitle }}
          </p>
        </div>

        <!-- Form Card -->
        <div class="bg-white/[0.04] border border-white/[0.08] rounded-2xl p-8 shadow-[0_4px_40px_rgba(0,0,0,0.4)]">
          <form @submit.prevent="submit" class="space-y-5">
            <!-- Product Name -->
            <div class="space-y-1.5">
              <label for="name" class="text-xs font-medium text-white/50 uppercase tracking-widest">
                Product Name
              </label>
              <div class="relative">
                <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-white/30">
                  <i data-lucide="tag" class="w-4 h-4" style="stroke-width:1.5"></i>
                </div>
                <input
                  type="text"
                  id="name"
                  v-model="form.name"
                  placeholder="e.g. Wireless Headphones"
                  class="w-full bg-white/[0.06] border border-white/[0.08] rounded-xl pl-10 pr-4 py-3 text-sm text-white placeholder-white/20 focus:outline-none focus:border-violet-500/60 focus:bg-white/[0.08] transition-all duration-200"
                  required
                  autofocus
                />
              </div>
              <InputError :message="form.errors.name" class="mt-2 text-red-400 text-xs" />
            </div>

            <!-- Description -->
            <div class="space-y-1.5">
              <label for="description" class="text-xs font-medium text-white/50 uppercase tracking-widest">
                Description
              </label>
              <div class="relative">
                <div class="absolute left-3.5 top-3.5 text-white/30">
                  <i data-lucide="align-left" class="w-4 h-4" style="stroke-width:1.5"></i>
                </div>
                <textarea
                  id="description"
                  v-model="form.description"
                  rows="4"
                  placeholder="Describe your product..."
                  class="w-full bg-white/[0.06] border border-white/[0.08] rounded-xl pl-10 pr-4 py-3 text-sm text-white placeholder-white/20 focus:outline-none focus:border-violet-500/60 focus:bg-white/[0.08] transition-all duration-200 resize-none"
                ></textarea>
              </div>
              <InputError :message="form.errors.description" class="mt-2 text-red-400 text-xs" />
            </div>

            <!-- Price and Quantity Row -->
            <div class="grid grid-cols-2 gap-4">
              <!-- Price -->
              <div class="space-y-1.5">
                <label for="price" class="text-xs font-medium text-white/50 uppercase tracking-widest">
                  Price
                </label>
                <div class="relative">
                  <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-white/30">
                    <i data-lucide="dollar-sign" class="w-4 h-4" style="stroke-width:1.5"></i>
                  </div>
                  <input
                    type="number"
                    id="price"
                    v-model="form.price"
                    placeholder="0.00"
                    min="0"
                    step="0.01"
                    class="w-full bg-white/[0.06] border border-white/[0.08] rounded-xl pl-10 pr-4 py-3 text-sm text-white placeholder-white/20 focus:outline-none focus:border-violet-500/60 focus:bg-white/[0.08] transition-all duration-200"
                    required
                  />
                </div>
                <InputError :message="form.errors.price" class="mt-2 text-red-400 text-xs" />
              </div>

              <!-- Quantity -->
              <div class="space-y-1.5">
                <label for="stock_quantity" class="text-xs font-medium text-white/50 uppercase tracking-widest">
                  Quantity
                </label>
                <div class="relative">
                  <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-white/30">
                    <i data-lucide="layers" class="w-4 h-4" style="stroke-width:1.5"></i>
                  </div>
                  <input
                    type="number"
                    id="stock_quantity"
                    v-model.number="form.stock_quantity"
                    placeholder="0"
                    min="0"
                    class="w-full bg-white/[0.06] border border-white/[0.08] rounded-xl pl-10 pr-4 py-3 text-sm text-white placeholder-white/20 focus:outline-none focus:border-violet-500/60 focus:bg-white/[0.08] transition-all duration-200"
                    required
                  />
                </div>
                <InputError :message="form.errors.stock_quantity" class="mt-2 text-red-400 text-xs" />
              </div>
            </div>

            <!-- Status (only show in edit mode) -->
            <div v-if="isEditMode" class="space-y-1.5">
              <label for="status" class="text-xs font-medium text-white/50 uppercase tracking-widest">
                Status
              </label>
              <div class="relative">
                <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-white/30">
                  <i data-lucide="settings" class="w-4 h-4" style="stroke-width:1.5"></i>
                </div>
                <select
                  id="status"
                  v-model="form.status"
                  class="w-full bg-white/[0.06] border border-white/[0.08] rounded-xl pl-10 pr-4 py-3 text-sm text-white focus:outline-none focus:border-violet-500/60 focus:bg-white/[0.08] transition-all duration-200 appearance-none"
                >
                  <option value="draft" class="bg-gray-800">Draft (not visible to customers)</option>
                  <option value="active" class="bg-gray-800">Active (visible to customers)</option>
                  <option value="archived" class="bg-gray-800">Archived (hidden from customers)</option>
                </select>
              </div>
              <InputError :message="form.errors.status" class="mt-2 text-red-400 text-xs" />
            </div>

            <!-- Divider -->
            <div class="border-t border-white/[0.06] my-2"></div>

            <!-- Actions -->
            <div class="flex items-center gap-3 pt-1">
              <Link :href="route('store.manage')" class="flex-1">
                <button type="button" class="w-full py-3 rounded-xl text-sm font-medium text-white/40 bg-white/[0.04] border border-white/[0.07] hover:bg-white/[0.08] hover:text-white/60 transition-all duration-200">
                  Cancel
                </button>
              </Link>

              <button
                type="submit"
                :disabled="form.processing"
                class="flex-1 py-3 rounded-xl text-sm font-semibold text-white bg-violet-600 hover:bg-violet-500 shadow-[0_4px_20px_rgba(124,58,237,0.35)] hover:shadow-[0_4px_28px_rgba(124,58,237,0.55)] transition-all duration-200 flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <i :data-lucide="submitIcon" class="w-4 h-4" style="stroke-width:2"></i>
                {{ submitButtonText }}
              </button>
            </div>
          </form>
        </div>

        <!-- Footer -->
        <p class="text-center text-xs text-white/20 mt-6">
          All fields are required before submitting.
        </p>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
