<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue'

const props = defineProps({
  images: {
    type: Array,
    default: () => []
  },
  autoPlay: {
    type: Boolean,
    default: true
  },
  interval: {
    type: Number,
    default: 3000 // 3 segundos
  },
  showThumbnails: {
    type: Boolean,
    default: true
  },
  height: {
    type: String,
    default: 'h-56'
  }
})

const emit = defineEmits(['imageChange'])

const currentIndex = ref(0)
const isPlaying = ref(props.autoPlay)
const processedImages = ref([])

// Detectar si estamos en NativePHP
const isNativeEnvironment = () => {
  return typeof window !== 'undefined' && window.nativeAPI !== undefined
}

// Convertir URL a Base64 para NativePHP
const convertToBase64 = async (url) => {
  if (!url || !isNativeEnvironment()) {
    return url
  }
  
  try {
    const response = await fetch(url)
    const blob = await response.blob()
    return new Promise((resolve, reject) => {
      const reader = new FileReader()
      reader.onload = () => resolve(reader.result)
      reader.onerror = reject
      reader.readAsDataURL(blob)
    })
  } catch (error) {
    console.warn('Failed to convert image to base64:', error)
    return url
  }
}

// Procesar imágenes cuando cambian
const processImages = async () => {
  if (!isNativeEnvironment()) {
    processedImages.value = props.images
    return
  }
  
  processedImages.value = await Promise.all(
    props.images.map(async (img) => {
      const base64Url = await convertToBase64(img.url || img)
      return {
        ...img,
        url: base64Url,
        thumb: await convertToBase64(img.thumb),
        large: await convertToBase64(img.large)
      }
    })
  )
}
let intervalId = null

const nextImage = () => {
  if (processedImages.value.length === 0) return
  currentIndex.value = (currentIndex.value + 1) % processedImages.value.length
  emit('imageChange', processedImages.value[currentIndex.value])
}

const prevImage = () => {
  if (processedImages.value.length === 0) return
  currentIndex.value = currentIndex.value === 0 ? processedImages.value.length - 1 : currentIndex.value - 1
  emit('imageChange', processedImages.value[currentIndex.value])
}

const goToImage = (index) => {
  currentIndex.value = index
  emit('imageChange', processedImages.value[currentIndex.value])
}

const toggleAutoPlay = () => {
  isPlaying.value = !isPlaying.value
  if (isPlaying.value) {
    startAutoPlay()
  } else {
    stopAutoPlay()
  }
}

const startAutoPlay = () => {
  if (props.images.length <= 1) return
  intervalId = setInterval(nextImage, props.interval)
}

const stopAutoPlay = () => {
  if (intervalId) {
    clearInterval(intervalId)
    intervalId = null
  }
}

onMounted(() => {
  if (isPlaying.value) {
    startAutoPlay()
  }
})

onUnmounted(() => {
  stopAutoPlay()
})

// Reiniciar auto-play cuando cambian las imágenes
watch(() => props.images, () => {
  currentIndex.value = 0
  processImages() // Procesar nuevas imágenes
  if (isPlaying.value) {
    stopAutoPlay()
    startAutoPlay()
  }
}, { immediate: true })

// Procesar imágenes al montar el componente
onMounted(() => {
  processImages()
})
</script>

<template>
  <div class="relative w-full group">
    <!-- Contenedor Principal -->
    <div :class="`${height} relative bg-[#263347] overflow-hidden rounded-t-2xl`">
      <!-- Imagen Actual -->
      <div v-if="images.length > 0" class="relative w-full h-full">
        <img
          :src="images[currentIndex]?.url || images[currentIndex]"
          :alt="`Product image ${currentIndex + 1}`"
          class="w-full h-full object-cover transition-opacity duration-500"
          @error="$event.target.src = '/images/placeholder-product.jpg'"
        />
        
        <!-- Overlay degradado para mejor contraste -->
        <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent pointer-events-none" />
      </div>
      
      <!-- Placeholder cuando no hay imágenes -->
      <div v-else class="flex flex-col items-center justify-center h-full text-[#4a5e75]">
        <svg class="w-16 h-16 stroke-1 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <span class="text-sm">No images available</span>
      </div>
      
      <!-- Controles de Navegación (solo si hay más de 1 imagen) -->
      <template v-if="images.length > 1">
        <!-- Botón Anterior -->
        <button
          @click="prevImage"
          class="absolute left-3 top-1/2 -translate-y-1/2 bg-black/50 hover:bg-black/70 text-white p-2 rounded-full opacity-0 group-hover:opacity-100 transition-all duration-200 backdrop-blur-sm"
          aria-label="Previous image"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </button>
        
        <!-- Botón Siguiente -->
        <button
          @click="nextImage"
          class="absolute right-3 top-1/2 -translate-y-1/2 bg-black/50 hover:bg-black/70 text-white p-2 rounded-full opacity-0 group-hover:opacity-100 transition-all duration-200 backdrop-blur-sm"
          aria-label="Next image"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </button>
        
        <!-- Botón Play/Pause -->
        <button
          @click="toggleAutoPlay"
          class="absolute bottom-3 right-3 bg-black/50 hover:bg-black/70 text-white p-2 rounded-full opacity-0 group-hover:opacity-100 transition-all duration-200 backdrop-blur-sm"
          :aria-label="isPlaying ? 'Pause slideshow' : 'Play slideshow'"
        >
          <svg v-if="isPlaying" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </button>
        
        <!-- Indicadores de posición -->
        <div class="absolute bottom-3 left-1/2 -translate-x-1/2 flex gap-1">
          <button
            v-for="(image, index) in images"
            :key="index"
            @click="goToImage(index)"
            class="w-2 h-2 rounded-full transition-all duration-200"
            :class="index === currentIndex 
              ? 'bg-white w-6' 
              : 'bg-white/50 hover:bg-white/75'"
            :aria-label="`Go to image ${index + 1}`"
          />
        </div>
      </template>
    </div>
    
    <!-- Thumbnails (opcional) -->
    <div v-if="showThumbnails && images.length > 1" class="bg-[#1e2a3a] p-2 border-t border-[#2e3f55]">
      <div class="flex gap-2 overflow-x-auto scrollbar-thin scrollbar-thumb-[#4d7cfe] scrollbar-track-[#263347]">
        <button
          v-for="(image, index) in images"
          :key="index"
          @click="goToImage(index)"
          class="flex-shrink-0 w-16 h-16 rounded-lg overflow-hidden border-2 transition-all duration-200"
          :class="index === currentIndex 
            ? 'border-[#4d7cfe] scale-110' 
            : 'border-transparent hover:border-[#4d7cfe]/50'"
        >
          <img
            :src="image.url || image"
            :alt="`Thumbnail ${index + 1}`"
            class="w-full h-full object-cover"
            @error="$event.target.src = '/images/placeholder-product.jpg'"
          />
        </button>
      </div>
    </div>
  </div>
</template>
