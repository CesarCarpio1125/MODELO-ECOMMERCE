<script setup>
import { computed } from 'vue'

const props = defineProps({
  icon: {
    type: String,
    required: true
  },
  title: {
    type: String,
    required: true
  },
  description: {
    type: String,
    default: ''
  },
  hasValue: {
    type: Boolean,
    default: false
  },
  badge: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['click'])

const buttonClass = computed(() => {
  const base = 'group relative flex items-center gap-3 p-4 rounded-xl border-2 transition-all duration-200 hover:shadow-lg cursor-pointer'
  
  if (props.hasValue) {
    return `${base} border-indigo-200 bg-indigo-50 dark:border-indigo-700 dark:bg-indigo-900/20 hover:border-indigo-300 dark:hover:border-indigo-600`
  } else {
    return `${base} border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800 hover:border-gray-300 dark:hover:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-750`
  }
})

const iconClass = computed(() => {
  return props.hasValue 
    ? 'text-indigo-600 dark:text-indigo-400' 
    : 'text-gray-400 dark:text-gray-500 group-hover:text-gray-600 dark:group-hover:text-gray-400'
})
</script>

<template>
  <div @click="$emit('click')" :class="buttonClass">
    <!-- Icon -->
    <div class="flex-shrink-0">
      <div class="w-12 h-12 rounded-lg flex items-center justify-center text-2xl">
        {{ icon }}
      </div>
    </div>
    
    <!-- Content -->
    <div class="flex-1 min-w-0">
      <div class="flex items-center gap-2">
        <h3 class="font-medium text-gray-900 dark:text-white truncate">{{ title }}</h3>
        <span v-if="badge" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">
          {{ badge }}
        </span>
      </div>
      <p v-if="description" class="text-sm text-gray-500 dark:text-gray-400 mt-1 truncate">{{ description }}</p>
      <div v-if="hasValue" class="flex items-center gap-1 mt-2 text-xs text-indigo-600 dark:text-indigo-400">
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
        Configured
      </div>
    </div>
    
    <!-- Arrow -->
    <div class="flex-shrink-0">
      <svg class="w-5 h-5 text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
      </svg>
    </div>
  </div>
</template>
