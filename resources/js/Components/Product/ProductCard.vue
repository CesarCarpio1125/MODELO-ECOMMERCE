<script setup>
import { Link, router } from '@inertiajs/vue3'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import DangerButton from '@/Components/DangerButton.vue'
import ImageCarousel from '@/Components/Product/ImageCarousel.vue'

const props = defineProps({
  product: {
    type: Object,
    required: true
  },
  showActions: {
    type: Boolean,
    default: true
  },
  carouselAutoPlay: {
    type: Boolean,
    default: true
  },
  carouselInterval: {
    type: Number,
    default: 3000
  }
})

const emit = defineEmits(['edit', 'delete', 'toggle-status'])

// Obtener imágenes para el carrusel
const carouselImages = props.product.carousel_images || []

const formatPrice = (price) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(price)
}

const getStockStatus = (stock) => {
  if (stock === 0) return { text: 'Out of Stock', color: 'text-red-400', bgColor: 'bg-red-500/10', borderColor: 'border-red-500/40' }
  if (stock <= 5) return { text: 'Low Stock', color: 'text-yellow-400', bgColor: 'bg-yellow-500/10', borderColor: 'border-yellow-500/40' }
  return { text: 'In Stock', color: 'text-green-400', bgColor: 'bg-green-500/10', borderColor: 'border-green-500/40' }
}

const getStatusBadge = (status) => {
  const statusMap = {
    'active': { text: 'Active', color: 'text-green-400', bgColor: 'bg-green-500/10', borderColor: 'border-green-500/40' },
    'draft': { text: 'Draft', color: 'text-gray-400', bgColor: 'bg-gray-500/10', borderColor: 'border-gray-500/40' },
    'archived': { text: 'Archived', color: 'text-red-400', bgColor: 'bg-red-500/10', borderColor: 'border-red-500/40' }
  }
  
  return statusMap[status] || { 
    text: status, 
    color: 'text-gray-400', 
    bgColor: 'bg-gray-500/10', 
    borderColor: 'border-gray-500/40' 
  }
}

const stockStatus = getStockStatus(props.product.stock_quantity)
const statusBadge = getStatusBadge(props.product.status)

// Debug: Log image URLs
console.log('ProductCard - Image URLs:', {
  featured_image: props.product.featured_image,
  featured_image_url: props.product.featured_image_url,
  image_url: props.product.image_url
})

const editProduct = () => {
  emit('edit', props.product)
}

const deleteProduct = () => {
  emit('delete', props.product)
}

const toggleStatus = () => {
  emit('toggle-status', props.product)
}
</script>

<template>
  <div class="bg-[#1e2a3a] rounded-2xl overflow-hidden shadow-2xl w-full max-w-sm border border-[#2e3f55] hover:border-[#4d7cfe]/50 transition-all duration-300 hover:shadow-[0_8px_32px_rgba(77,124,254,0.15)]">
    <!-- Product Image Section with Carousel -->
    <div class="relative">
      <ImageCarousel 
        :images="carouselImages"
        :auto-play="carouselAutoPlay"
        :interval="carouselInterval"
        :show-thumbnails="false"
        height="h-56"
        @image-change="(image) => console.log('Image changed:', image)"
      />
      
      <!-- Stock Status Badge -->
      <div :class="`absolute top-3 right-3 ${stockStatus.bgColor} ${stockStatus.borderColor} text-xs font-semibold px-3 py-1 rounded-full border backdrop-blur-sm pointer-events-none`">
        {{ stockStatus.text }}
      </div>
      
      <!-- Status Badge -->
      <div :class="`absolute top-3 left-3 ${statusBadge.bgColor} ${statusBadge.borderColor} ${statusBadge.color} text-xs font-semibold px-3 py-1 rounded-full border backdrop-blur-sm pointer-events-none`">
        {{ statusBadge.text }}
      </div>
    </div>

    <!-- Product Info Section -->
    <div class="p-5">
      <!-- Product Name -->
      <h2 class="text-white text-xl font-bold mb-1 tracking-tight">
        {{ product.name }}
      </h2>
      
      <!-- Product Description -->
      <p v-if="product.description" class="text-[#7a8fa6] text-sm mb-4 leading-relaxed" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
        {{ product.description }}
      </p>

      <!-- Price and Quantity Row -->
      <div class="flex items-center justify-between mb-5">
        <!-- Price -->
        <div class="flex flex-col">
          <span class="text-[#4a5e75] text-xs font-medium uppercase tracking-wider mb-0.5">
            Price
          </span>
          <span class="text-[#4d7cfe] text-2xl font-extrabold">
            {{ formatPrice(product.price) }}
          </span>
        </div>
        
        <!-- Quantity -->
        <div class="flex flex-col items-end">
          <span class="text-[#4a5e75] text-xs font-medium uppercase tracking-wider mb-0.5">
            Quantity
          </span>
          <div class="flex items-center gap-2 bg-[#263347] border border-[#2e3f55] rounded-xl px-3 py-1.5">
            <svg class="w-4 h-4 text-[#4d7cfe]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
            <span class="text-white text-xl font-extrabold">
              {{ product.stock_quantity }}
            </span>
          </div>
        </div>
      </div>

      <!-- SKU if available -->
      <div v-if="product.sku" class="mb-5">
        <span class="text-[#4a5e75] text-xs font-medium uppercase tracking-wider">SKU:</span>
        <span class="text-[#7a8fa6] text-sm font-mono ml-2">{{ product.sku }}</span>
      </div>

      <!-- Action Buttons -->
      <div v-if="showActions" class="flex gap-3">
        <!-- Edit Button -->
        <Link :href="route('store.products.edit', product.id)" class="flex-1">
          <button class="w-full flex items-center justify-center gap-2 bg-[#4d7cfe] hover:bg-[#3a6ae8] text-white font-semibold py-3 rounded-xl transition-all duration-200 text-sm shadow-lg hover:shadow-[0_4px_20px_rgba(77,124,254,0.4)]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 00-2.828 0L4 12m8-4v4m0 4H8m4 0h4" />
            </svg>
            Edit
          </button>
        </Link>
        
        <!-- Delete Button -->
        <button 
          @click="deleteProduct"
          class="flex-1 flex items-center justify-center gap-2 bg-[#e53e3e] hover:bg-[#c53030] text-white font-semibold py-3 rounded-xl transition-all duration-200 text-sm shadow-lg hover:shadow-[0_4px_20px_rgba(229,62,62,0.4)]"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
          </svg>
          Delete
        </button>
      </div>
    </div>
  </div>
</template>
