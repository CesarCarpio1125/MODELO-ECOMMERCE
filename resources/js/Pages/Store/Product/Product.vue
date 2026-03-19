<script setup>
import { ref, computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'

const props = defineProps({
  vendor: Object,
  product: Object, // Para edit mode
})

// Determinar si es create o edit mode
const isEditMode = computed(() => !!props.product)

// Determinar la ruta de regreso correcta
const backRoute = computed(() => {
  // Si estamos en modo edición o tenemos vendor, ir a store.manage
  if (isEditMode.value || props.vendor) {
    return route('store.manage')
  }
  // Si no hay vendor, ir al dashboard del vendor
  return route('vendor.dashboard')
})

const form = useForm({
  name: props.product?.name || '',
  description: props.product?.description || '',
  price: props.product?.price || '',
  stock_quantity: props.product?.stock_quantity || 1,
  featured_image: null,
  images: []
})

const imagePreviews = ref([])
const featuredImagePreview = ref(null)
const isDragging = ref(false)

// Cargar imágenes existentes si está en edit mode
if (isEditMode.value && props.product) {
  // Cargar imagen destacada existente
  if (props.product.featured_image_url) {
    featuredImagePreview.value = props.product.featured_image_url
  }
  
  // Cargar galería existente
  if (props.product.getCarouselImages) {
    const carouselImages = props.product.getCarouselImages()
    imagePreviews.value = carouselImages.map(img => ({
      url: img.url,
      id: img.id,
      existing: true,
      name: img.name
    }))
  } else if (props.product.images) {
    // Fallback para sistema legacy
    imagePreviews.value = props.product.images.map((img, index) => ({
      url: typeof img === 'string' ? img : img.url,
      id: img.id || `legacy-${index}`,
      existing: true,
      name: img.name || `Image ${index + 1}`
    }))
  }
}

const handleFileUpload = (event) => {
  const files = Array.from(event.target.files)
  processFiles(files)
}

const handleFeaturedImageUpload = (event) => {
  const file = event.target.files[0]
  if (file && file.type.startsWith('image/')) {
    form.featured_image = file
    const reader = new FileReader()
    reader.onload = (e) => {
      featuredImagePreview.value = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

const handleDrop = (event) => {
  event.preventDefault()
  isDragging.value = false
  const files = Array.from(event.dataTransfer.files)
  processFiles(files)
}

const handleDragOver = (event) => {
  event.preventDefault()
  isDragging.value = true
}

const handleDragLeave = () => {
  isDragging.value = false
}

const processFiles = (files) => {
  console.log('DEBUG - Processing files:', files.length)
  files.forEach((file, index) => {
    console.log(`DEBUG - File ${index}:`, {
      name: file.name,
      type: file.type,
      size: file.size,
      isImage: file.type.startsWith('image/')
    })
    
    if (file.type.startsWith('image/')) {
      const reader = new FileReader()
      reader.onload = (e) => {
        const newImage = {
          url: e.target.result,
          file: file,
          id: Date.now() + Math.random(),
          existing: false
        }
        
        console.log('DEBUG - Adding new image to previews:', newImage)
        imagePreviews.value.push(newImage)
        console.log('DEBUG - Total previews now:', imagePreviews.value.length)
      }
      reader.readAsDataURL(file)
    } else {
      console.warn(`DEBUG - Skipping non-image file: ${file.name} (${file.type})`)
    }
  })
}

const removeImage = (imageId) => {
  imagePreviews.value = imagePreviews.value.filter(img => img.id !== imageId)
}

const submit = async () => {
  if (isEditMode.value) {
    // Edit mode - usar FormData con fetch directo para archivos
    const formData = new FormData()
    formData.append('name', form.name)
    formData.append('description', form.description)
    formData.append('price', form.price)
    formData.append('stock_quantity', form.stock_quantity)
    formData.append('_method', 'PUT') // Method spoofing para PUT
    
    // Debug logging
    console.log('DEBUG - Submitting with:', {
      imagePreviews: imagePreviews.value,
      newImages: imagePreviews.value.filter(img => !img.existing),
      featuredImage: form.featured_image
    })
    
    // Agregar imagen destacada si hay una nueva
    if (form.featured_image) {
      formData.append('featured_image', form.featured_image)
      console.log('DEBUG - Added featured image:', form.featured_image.name)
    }
    
    // Agregar imágenes nuevas
    const newImages = imagePreviews.value.filter(img => !img.existing)
    console.log('DEBUG - New images to upload:', newImages.length)
    
    newImages.forEach((img, index) => {
      if (img.file) {
        formData.append(`images[${index}]`, img.file)
        console.log(`DEBUG - Adding image ${index}:`, img.file.name)
      } else {
        console.warn(`DEBUG - Image ${index} has no file:`, img)
      }
    })
    
    // Debug FormData contents
    console.log('DEBUG - FormData contents:')
    for (let [key, value] of formData.entries()) {
      console.log(`  ${key}:`, value instanceof File ? value.name : value)
    }

    try {
      const response = await fetch(route('store.products.update', props.product.id), {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          'Accept': 'application/json',
        },
        body: formData
      })
      
      if (response.ok) {
        console.log('DEBUG - Product updated successfully')
        // Redirigir usando el vendor del producto o fallback a manage
        const redirectUrl = props.product?.vendor?.store_slug 
          ? route('store.show', [props.product.vendor.store_slug])
          : route('store.manage')
        window.location.href = redirectUrl
      } else {
        const error = await response.json()
        console.error('DEBUG - Update errors:', error)
        // Handle validation errors
        if (error.errors) {
          // Mostrar errores de validación
          Object.entries(error.errors).forEach(([field, messages]) => {
            console.error(`${field}:`, messages)
          })
        }
      }
    } catch (error) {
      console.error('DEBUG - Network error:', error)
    }
  } else {
    // Create mode - usar fetch directo para FormData
    console.log('DEBUG - CREATE MODE ACTIVATED')
    console.log('DEBUG - Submitting with:', {
      imagePreviews: imagePreviews.value,
      newImages: imagePreviews.value.filter(img => !img.existing),
      featuredImage: form.featured_image
    })
    
    const formData = new FormData()
    formData.append('name', form.name)
    formData.append('description', form.description)
    formData.append('price', form.price)
    formData.append('stock_quantity', form.stock_quantity)
    
    // Agregar imagen destacada si hay
    if (form.featured_image) {
      formData.append('featured_image', form.featured_image)
      console.log('DEBUG - CREATE: Added featured image:', form.featured_image.name)
    }
    
    // Agregar imágenes nuevas
    const newImages = imagePreviews.value.filter(img => !img.existing)
    console.log('DEBUG - CREATE: New images to upload:', newImages.length)
    
    newImages.forEach((img, index) => {
      if (img.file) {
        formData.append(`images[${index}]`, img.file)
        console.log(`DEBUG - CREATE: Adding image ${index}:`, img.file.name)
      } else {
        console.warn(`DEBUG - CREATE: Image ${index} has no file:`, img)
      }
    })
    
    // Debug FormData contents
    console.log('DEBUG - CREATE: FormData contents:')
    for (let [key, value] of formData.entries()) {
      console.log(`  ${key}:`, value instanceof File ? value.name : value)
    }

    try {
      const response = await fetch(route('store.products.store', props.vendor.id), {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          'Accept': 'application/json',
        },
        body: formData
      })
      
      if (response.ok) {
        console.log('DEBUG - CREATE: Product created successfully')
        form.reset()
        imagePreviews.value = []
        featuredImagePreview.value = null
        window.location.href = route('store.show', [props.vendor.store_slug])
      } else {
        const error = await response.json()
        console.error('DEBUG - CREATE: Create errors:', error)
        // Handle validation errors
        if (error.errors) {
          Object.entries(error.errors).forEach(([field, messages]) => {
            console.error(`${field}:`, messages)
          })
        }
      }
    } catch (error) {
      console.error('DEBUG - CREATE: Network error:', error)
    }
  }
}
</script>

<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <div>
          <div class="inline-flex items-center gap-2 bg-white/5 border border-white/10 rounded-full px-3 py-1 mb-4">
            <svg class="w-3.5 h-3.5 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8-4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
            <span class="text-xs text-white/50 tracking-wide uppercase">Inventory</span>
          </div>
          <h1 class="text-2xl font-semibold text-white tracking-tight">
            {{ isEditMode ? 'Update Product' : 'Add New Product' }}
          </h1>
          <p class="text-sm text-white/40 mt-1">
            {{ isEditMode ? 'Update the product details below.' : 'Fill in the details below to list a new product.' }}
          </p>
        </div>
        <Link :href="backRoute">
          <SecondaryButton>Back to Products</SecondaryButton>
        </Link>
      </div>
    </template>

    <div class="py-8 bg-black min-h-screen">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <form @submit.prevent="submit" class="space-y-6">
          <!-- Product Information Card -->
          <div class="bg-white/[0.04] border border-white/[0.08] rounded-2xl p-8 shadow-[0_4px_40px_rgba(0,0,0,0.4)]">
            <div class="space-y-5">
              <!-- Product Name -->
              <div class="space-y-1.5">
                <InputLabel for="name" value="Product Name" class="text-xs font-medium text-white/50 uppercase tracking-widest" />
                <div class="relative">
                  <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-white/30">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                  </div>
                  <TextInput
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="w-full bg-white/[0.06] border border-white/[0.08] rounded-xl pl-10 pr-4 py-3 text-sm text-white placeholder-white/20 focus:outline-none focus:border-violet-500/60 focus:bg-white/[0.08] transition-all duration-200"
                    placeholder="e.g. Wireless Headphones"
                    required
                  />
                </div>
                <InputError :message="form.errors.name" class="mt-2" />
              </div>

              <!-- Description -->
              <div class="space-y-1.5">
                <InputLabel for="description" value="Description" class="text-xs font-medium text-white/50 uppercase tracking-widest" />
                <div class="relative">
                  <div class="absolute left-3.5 top-3.5 text-white/30">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                  </div>
                  <textarea
                    id="description"
                    v-model="form.description"
                    rows="4"
                    class="w-full bg-white/[0.06] border border-white/[0.08] rounded-xl pl-10 pr-4 py-3 text-sm text-white placeholder-white/20 focus:outline-none focus:border-violet-500/60 focus:bg-white/[0.08] transition-all duration-200 resize-none"
                    placeholder="Describe your product..."
                  ></textarea>
                </div>
                <InputError :message="form.errors.description" class="mt-2" />
              </div>

              <!-- Price and Quantity Row -->
              <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1.5">
                  <InputLabel for="price" value="Price" class="text-xs font-medium text-white/50 uppercase tracking-widest" />
                  <div class="relative">
                    <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-white/30">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                    </div>
                    <TextInput
                      id="price"
                      v-model="form.price"
                      type="number"
                      step="0.01"
                      min="0"
                      class="w-full bg-white/[0.06] border border-white/[0.08] rounded-xl pl-10 pr-4 py-3 text-sm text-white placeholder-white/20 focus:outline-none focus:border-violet-500/60 focus:bg-white/[0.08] transition-all duration-200"
                      placeholder="0.00"
                      required
                    />
                  </div>
                  <InputError :message="form.errors.price" class="mt-2" />
                </div>

                <div class="space-y-1.5">
                  <InputLabel for="stock_quantity" value="Quantity" class="text-xs font-medium text-white/50 uppercase tracking-widest" />
                  <div class="relative">
                    <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-white/30">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                      </svg>
                    </div>
                    <TextInput
                      id="stock_quantity"
                      v-model="form.stock_quantity"
                      type="number"
                      min="0"
                      class="w-full bg-white/[0.06] border border-white/[0.08] rounded-xl pl-10 pr-4 py-3 text-sm text-white placeholder-white/20 focus:outline-none focus:border-violet-500/60 focus:bg-white/[0.08] transition-all duration-200"
                      placeholder="0"
                      required
                    />
                  </div>
                  <InputError :message="form.errors.stock_quantity" class="mt-2" />
                </div>
              </div>
            </div>
          </div>

          <!-- Product Images Card -->
          <div class="bg-white/[0.04] border border-white/[0.08] rounded-2xl p-8 shadow-[0_4px_40px_rgba(0,0,0,0.4)]">
            <div class="space-y-1.5">
              <InputLabel value="Product Images" class="text-xs font-medium text-white/50 uppercase tracking-widest" />
              
              <!-- Drop Zone -->
              <div
                class="relative border border-dashed border-white/[0.12] rounded-xl bg-white/[0.03] hover:bg-white/[0.05] hover:border-violet-500/40 transition-all duration-200 cursor-pointer group"
                :class="{ 'border-violet-500/60 bg-violet-500/5': isDragging }"
                @drop="handleDrop"
                @dragover="handleDragOver"
                @dragleave="handleDragLeave"
              >
                <input
                  type="file"
                  accept="image/png, image/jpeg, image/webp"
                  multiple
                  class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                  @change="handleFileUpload"
                >
                <div class="flex flex-col items-center justify-center py-7 px-4 text-center">
                  <div class="w-10 h-10 rounded-xl bg-white/[0.06] border border-white/[0.08] flex items-center justify-center mb-3 group-hover:border-violet-500/40 group-hover:bg-violet-500/10 transition-all duration-200">
                    <svg class="w-5 h-5 text-white/30 group-hover:text-violet-400 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                  </div>
                  <p class="text-sm font-medium text-white/40 group-hover:text-white/60 transition-colors duration-200">
                    Drag & drop or <span class="text-violet-400">click to upload</span>
                  </p>
                  <p class="text-xs text-white/20 mt-1">PNG, JPG, WEBP — up to 10MB each</p>
                </div>
              </div>

              <!-- Image Preview Grid -->
              <div class="grid grid-cols-3 gap-2 mt-2">
                <div
                  v-for="image in imagePreviews"
                  :key="image.id"
                  class="relative group rounded-xl overflow-hidden border border-white/[0.08] aspect-square bg-white/[0.04]"
                >
                  <img :src="image.url" alt="Product preview" class="w-full h-full object-cover">
                  <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center">
                    <button
                      type="button"
                      class="w-7 h-7 rounded-full bg-red-500/80 flex items-center justify-center"
                      @click="removeImage(image.id)"
                    >
                      <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </button>
                  </div>
                </div>
                
                <!-- Add More Slot -->
                <div
                  v-if="imagePreviews.length < 6"
                  class="rounded-xl border border-dashed border-white/[0.08] aspect-square bg-white/[0.02] flex items-center justify-center cursor-pointer hover:border-violet-500/30 hover:bg-violet-500/5 transition-all duration-200 group"
                  @click="document.querySelector('input[type=file]').click()"
                >
                  <div class="flex flex-col items-center gap-1">
                    <svg class="w-5 h-5 text-white/20 group-hover:text-violet-400 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span class="text-xs text-white/20 group-hover:text-violet-400 transition-colors duration-200">More</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="bg-white/[0.04] border border-white/[0.08] rounded-2xl p-8 shadow-[0_4px_40px_rgba(0,0,0,0.4)]">
            <div class="border-t border-white/[0.06] -mx-8 -mb-8 px-8 pt-6 flex items-center gap-3">
              <Link :href="backRoute">
                <SecondaryButton class="flex-1 py-3 text-sm font-medium">Cancel</SecondaryButton>
              </Link>
              <PrimaryButton
                :disabled="form.processing"
                class="flex-1 py-3 text-sm font-semibold bg-violet-600 hover:bg-violet-500 shadow-[0_4px_20px_rgba(124,58,237,0.35)] hover:shadow-[0_4px_28px_rgba(124,58,237,0.55)] transition-all duration-200 flex items-center justify-center gap-2"
              >
                <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 0114 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span v-if="form.processing">{{ isEditMode ? 'Updating...' : 'Creating...' }}</span>
                <span v-else>{{ isEditMode ? 'Update Product' : 'Add Product' }}</span>
              </PrimaryButton>
            </div>
          </div>
        </form>

        <p class="text-center text-xs text-white/20 mt-6">All fields are required before submitting.</p>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
