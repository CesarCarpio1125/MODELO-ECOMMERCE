<script setup>
import TextInput from '@/Components/TextInput.vue'
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'

const form = defineModel('form', { required: true })
const tagInput = ref('')

const addTag = () => {
  if (tagInput.value.trim() && !form.value.tags.includes(tagInput.value.trim())) {
    form.value.tags.push(tagInput.value.trim())
    tagInput.value = ''
  }
}

const removeTag = (index) => {
  form.value.tags.splice(index, 1)
}
</script>

<template>
  <div class="space-y-4">
    <div>
      <InputLabel for="tag_input" value="Add Tags" />
      <div class="flex gap-3">
        <TextInput
          id="tag_input"
          v-model="tagInput"
          type="text"
          class="flex-1"
          placeholder="Enter a tag and press Add"
          @keyup.enter="addTag"
        />
        <button
          type="button"
          @click="addTag"
          class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors"
        >
          Add
        </button>
      </div>
      <p class="mt-2 text-sm text-gray-500">Add tags to help customers find your product</p>
    </div>

    <div v-if="form.tags.length > 0" class="flex flex-wrap gap-2">
      <span
        v-for="(tag, index) in form.tags"
        :key="index"
        class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200"
      >
        {{ tag }}
        <button
          type="button"
          @click="removeTag(index)"
          class="ml-2 text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300"
        >
          ×
        </button>
      </span>
    </div>
    
    <div v-else class="text-center py-8 text-gray-500 border-2 border-dashed border-gray-300 rounded-lg">
      <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
      </svg>
      <p class="text-sm">No tags added yet</p>
    </div>
  </div>
</template>
