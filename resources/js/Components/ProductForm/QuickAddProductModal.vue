<script setup>
import { useForm } from '@inertiajs/vue3'
import MiniModal from '@/Components/ProductForm/MiniModal.vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  vendor: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['close'])

const form = useForm({
  name: '',
  description: '',
  price: '',
  stock_quantity: 0,
  featured_image: null
})

const submit = () => {
  console.log('Vendor props:', props.vendor)
  console.log('Vendor ID:', props.vendor?.id)
  console.log('Route name:', 'store.products.quick-store')
  console.log('Submitting quick product form...', {
    name: form.name,
    description: form.description,
    price: form.price,
    stock_quantity: form.stock_quantity,
    featured_image: form.featured_image
  })

  if (!props.vendor?.id) {
    console.error('No vendor ID available!')
    return
  }

  form.post(route('store.products.quick-store', props.vendor.id), {
    onSuccess: () => {
      console.log('Quick product created successfully!')
      form.reset()
      emit('close')
    },
    onError: (errors) => {
      console.log('Quick form errors:', errors)
    },
    onFinish: () => {
      console.log('Quick form submission finished')
    }
  })
}

const close = () => {
  emit('close')
  form.reset()
}
</script>

<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition-all duration-300 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-all duration-200 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm"></div>

        <!-- Modal Content -->
        <div class="flex min-h-full items-center justify-center p-4">
          <Transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="opacity-0 scale-95 translate-y-4"
            enter-to-class="opacity-100 scale-100 translate-y-0"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="opacity-100 scale-100 translate-y-0"
            leave-to-class="opacity-0 scale-95 translate-y-4"
          >
            <div
              v-if="show"
              class="w-full max-w-lg bg-black border border-white/[0.08] rounded-2xl shadow-[0_4px_40px_rgba(0,0,0,0.4)]"
            >
              <!-- Header -->
              <div class="flex items-center justify-between p-6 border-b border-white/[0.08]">
                <div>
                  <h3 class="text-xl font-semibold text-white">Quick Add Product</h3>
                  <p class="text-sm text-white/40 mt-1">Add a new product with basic information</p>
                </div>
                <button
                  @click="close"
                  class="p-2 text-white/30 hover:text-white/60 transition-colors rounded-lg hover:bg-white/[0.08]"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                  </svg>
                </button>
              </div>

              <!-- Content -->
              <div class="p-6">
                <form @submit.prevent="submit" class="space-y-5">
                  <!-- Product Name -->
                  <div class="space-y-1.5">
                    <label for="name" class="text-xs font-medium text-white/50 uppercase tracking-widest">
                      Product Name
                    </label>
                    <div class="relative">
                      <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-white/30">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                        </svg>
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
                    <div v-if="form.errors.name" class="text-xs text-red-400 mt-1">
                      {{ form.errors.name }}
                    </div>
                  </div>

                  <!-- Description -->
                  <div class="space-y-1.5">
                    <label for="description" class="text-xs font-medium text-white/50 uppercase tracking-widest">
                      Description
                    </label>
                    <div class="relative">
                      <div class="absolute left-3.5 top-3.5 text-white/30">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                      </div>
                      <textarea
                        id="description"
                        v-model="form.description"
                        rows="3"
                        placeholder="Describe your product..."
                        class="w-full bg-white/[0.06] border border-white/[0.08] rounded-xl pl-10 pr-4 py-3 text-sm text-white placeholder-white/20 focus:outline-none focus:border-violet-500/60 focus:bg-white/[0.08] transition-all duration-200 resize-none"
                      ></textarea>
                    </div>
                    <div v-if="form.errors.description" class="text-xs text-red-400 mt-1">
                      {{ form.errors.description }}
                    </div>
                  </div>

                  <!-- Featured Image -->
                  <div class="space-y-1.5">
                    <label for="featured_image" class="text-xs font-medium text-white/50 uppercase tracking-widest">
                      Featured Image
                    </label>
                    <div class="relative">
                      <input
                        type="file"
                        id="featured_image"
                        @change="form.featured_image = $event.target.files[0]"
                        accept="image/*"
                        class="w-full bg-white/[0.06] border border-white/[0.08] rounded-xl px-4 py-3 text-sm text-white file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-violet-600 file:text-white hover:file:bg-violet-500 cursor-pointer focus:outline-none focus:border-violet-500/60 focus:bg-white/[0.08] transition-all duration-200"
                      />
                    </div>
                    <div v-if="form.errors.featured_image" class="text-xs text-red-400 mt-1">
                      {{ form.errors.featured_image }}
                    </div>
                    <div v-if="form.featured_image" class="mt-2">
                      <img
                        :src="URL.createObjectURL(form.featured_image)"
                        alt="Preview"
                        class="h-20 w-20 object-cover rounded-lg"
                      />
                    </div>
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
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                          </svg>
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
                      <div v-if="form.errors.price" class="text-xs text-red-400 mt-1">
                        {{ form.errors.price }}
                      </div>
                    </div>

                    <!-- Quantity -->
                    <div class="space-y-1.5">
                      <label for="stock_quantity" class="text-xs font-medium text-white/50 uppercase tracking-widest">
                        Quantity
                      </label>
                      <div class="relative">
                        <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-white/30">
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                          </svg>
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
                      <div v-if="form.errors.stock_quantity" class="text-xs text-red-400 mt-1">
                        {{ form.errors.stock_quantity }}
                      </div>
                    </div>
                  </div>

                  <!-- Divider -->
                  <div class="border-t border-white/[0.06] my-2"></div>

                  <!-- Actions -->
                  <div class="flex items-center gap-3 pt-1">
                    <button type="button" @click="close" :disabled="form.processing" class="flex-1 py-3 rounded-xl text-sm font-medium text-white/40 bg-white/[0.04] border border-white/[0.07] hover:bg-white/[0.08] hover:text-white/60 transition-all duration-200">
                      Cancel
                    </button>
                    <button type="submit" :disabled="form.processing" class="flex-1 py-3 rounded-xl text-sm font-semibold text-white bg-violet-600 hover:bg-violet-500 shadow-[0_4px_20px_rgba(124,58,237,0.35)] hover:shadow-[0_4px_28px_rgba(124,58,237,0.55)] transition-all duration-200 flex items-center justify-center gap-2">
                      <svg v-if="!form.processing" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                      </svg>
                      <svg v-else class="animate-spin w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 0114 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                      {{ form.processing ? 'Adding...' : 'Add Product' }}
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </Transition>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>
