<script setup>
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'

const form = defineModel('form', { required: true })
</script>

<template>
  <div class="space-y-4">
    <div>
      <InputLabel for="status" value="Product Status" />
      <select
        id="status"
        v-model="form.status"
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
      >
        <option value="draft">Draft (not visible to customers)</option>
        <option value="active">Active (visible to customers)</option>
      </select>
      <InputError :message="form.errors.status" class="mt-2" />
      
      <div class="mt-3 p-3 rounded-lg border" :class="form.status === 'active' ? 'bg-green-50 border-green-200 dark:bg-green-900/20 dark:border-green-800' : 'bg-yellow-50 border-yellow-200 dark:bg-yellow-900/20 dark:border-yellow-800'">
        <div class="flex items-start gap-2">
          <div class="flex-shrink-0 mt-0.5">
            <div v-if="form.status === 'active'" class="w-2 h-2 bg-green-500 rounded-full"></div>
            <div v-else class="w-2 h-2 bg-yellow-500 rounded-full"></div>
          </div>
          <div class="text-sm">
            <p v-if="form.status === 'active'" class="text-green-800 dark:text-green-200">
              <strong>✅ Active:</strong> This product will be visible to customers and available for purchase.
            </p>
            <p v-else class="text-yellow-800 dark:text-yellow-200">
              <strong>📝 Draft:</strong> This product will not be visible to customers. You can publish it later.
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
