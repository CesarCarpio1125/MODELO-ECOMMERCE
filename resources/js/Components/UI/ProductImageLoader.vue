<script setup>
import { ref, computed, toRefs, watch, onMounted } from 'vue'

const props = defineProps({
  src: {
    type: String,
    default: null
  },
  alt: {
    type: String,
    required: true
  },
  class: {
    type: String,
    default: ''
  },
  placeholderClass: {
    type: String,
    default: 'w-16 h-16 stroke-1'
  }
})

const emit = defineEmits(['load', 'error'])

const imageLoaded = ref(false)
const imageError = ref(false)
const imageSrc = ref(null)

const shouldShowImage = computed(() => props.src && !imageError.value)
const shouldShowPlaceholder = computed(() => !props.src || imageError.value)

const loadImage = () => {
  if (!props.src) return
  
  imageSrc.value = props.src
  imageError.value = false
  imageLoaded.value = false
  
  const img = new Image()
  img.onload = () => {
    imageLoaded.value = true
    emit('load')
  }
  img.onerror = () => {
    imageError.value = true
    emit('error')
  }
  img.src = props.src
}

// Watch for src changes
watch(() => props.src, () => {
  loadImage()
}, { immediate: true })

onMounted(() => {
  loadImage()
})
</script>

<template>
  <div class="relative w-full h-full flex items-center justify-center">
    <!-- Actual Image -->
    <img
      v-if="shouldShowImage"
      :src="imageSrc"
      :alt="alt"
      class="absolute inset-0 w-full h-full object-cover transition-opacity duration-300"
      :class="imageLoaded ? 'opacity-100' : 'opacity-0'"
    />
    
    <!-- Loading State -->
    <div 
      v-if="shouldShowImage && !imageLoaded" 
      class="absolute inset-0 flex items-center justify-center bg-gray-200/10"
    >
      <div class="w-8 h-8 border-2 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
    </div>
    
    <!-- Placeholder -->
    <div 
      v-if="shouldShowPlaceholder" 
      class="flex flex-col items-center gap-2 text-[#4a5e75]"
    >
      <svg 
        :class="placeholderClass" 
        fill="none" 
        stroke="currentColor" 
        viewBox="0 0 24 24"
      >
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
      </svg>
    </div>
  </div>
</template>
