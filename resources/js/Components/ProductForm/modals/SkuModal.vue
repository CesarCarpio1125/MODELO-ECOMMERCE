<script setup>
import TextInput from '@/Components/TextInput.vue'
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'

const form = defineModel('form', { required: true })

const generateSku = () => {
  if (form.value.name) {
    const base = form.value.name.toUpperCase().replace(/[^A-Z0-9]/g, '').substring(0, 8)
    const random = Math.random().toString(36).substring(2, 6).toUpperCase()
    form.value.sku = base + '-' + random
  }
}
</script>

<template>
  <div class="space-y-4">
    <div>
      <InputLabel for="sku" value="SKU" />
      <div class="flex gap-3">
        <TextInput
          id="sku"
          v-model="form.sku"
          type="text"
          class="flex-1"
          placeholder="Auto-generated if empty"
        />
        <button
          type="button"
          @click="generateSku"
          :disabled="!form.name"
          class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
        >
          Generate
        </button>
      </div>
      <InputError :message="form.errors.sku" class="mt-2" />
      <p class="mt-2 text-sm text-gray-500">Stock Keeping Unit for inventory tracking</p>
    </div>
  </div>
</template>
