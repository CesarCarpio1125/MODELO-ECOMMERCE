<script setup>
import TextInput from '@/Components/TextInput.vue'
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'

const form = defineModel('form', { required: true })

const addVariant = () => {
  form.value.variants.push({
    name: '',
    price: '',
    stock: 0,
    weight: '',
    attributes: {},
    image: null
  })
}

const removeVariant = (index) => {
  form.value.variants.splice(index, 1)
}
</script>

<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <p class="text-sm text-gray-500">Add variants for different sizes, colors, etc.</p>
      <button
        type="button"
        @click="addVariant"
        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
      >
        Add Variant
      </button>
    </div>

    <div v-if="form.variants.length === 0" class="text-center py-8 text-gray-500 border-2 border-dashed border-gray-300 rounded-lg">
      <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
      </svg>
      <p class="text-lg font-medium mb-2">No variants added</p>
      <p class="text-sm">Add variants for different sizes, colors, or other options</p>
    </div>

    <div v-else class="space-y-4 max-h-96 overflow-y-auto">
      <div
        v-for="(variant, index) in form.variants"
        :key="index"
        class="bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4"
      >
        <div class="flex justify-between items-start mb-4">
          <div class="flex items-center">
            <div class="w-8 h-8 bg-orange-100 dark:bg-orange-900 rounded-lg flex items-center justify-center mr-3">
              <span class="text-orange-600 dark:text-orange-400 font-semibold text-sm">{{ index + 1 }}</span>
            </div>
            <div>
              <h4 class="font-semibold text-gray-900 dark:text-white">Variant {{ index + 1 }}</h4>
              <p class="text-sm text-gray-600 dark:text-gray-400">Configure variant options</p>
            </div>
          </div>
          <button
            type="button"
            @click="removeVariant(index)"
            class="px-3 py-1 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm"
          >
            Remove
          </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <InputLabel :for="`variant_name_${index}`" value="Variant Name" />
            <TextInput
              :id="`variant_name_${index}`"
              v-model="variant.name"
              type="text"
              class="mt-1 block w-full"
              placeholder="e.g., Small, Red, Cotton"
              required
            />
          </div>

          <div>
            <InputLabel :for="`variant_price_${index}`" value="Price ($)" />
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="text-gray-500">$</span>
              </div>
              <TextInput
                :id="`variant_price_${index}`"
                v-model="variant.price"
                type="number"
                step="0.01"
                min="0"
                class="mt-1 block w-full pl-8"
                placeholder="0.00"
                required
              />
            </div>
          </div>

          <div>
            <InputLabel :for="`variant_stock_${index}`" value="Stock" />
            <TextInput
              :id="`variant_stock_${index}`"
              v-model.number="variant.stock"
              type="number"
              min="0"
              class="mt-1 block w-full"
              placeholder="0"
              required
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
