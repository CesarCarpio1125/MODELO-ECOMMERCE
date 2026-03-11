<script setup>
import TextInput from '@/Components/TextInput.vue'
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'

const form = defineModel('form', { required: true })
</script>

<template>
  <div class="space-y-6">
    <!-- Weight -->
    <div>
      <InputLabel for="weight" value="Weight (kg)" />
      <TextInput
        id="weight"
        v-model="form.weight"
        type="number"
        step="0.01"
        min="0"
        class="mt-1 block w-full"
        placeholder="0.00"
      />
      <InputError :message="form.errors.weight" class="mt-2" />
    </div>

    <!-- Dimensions -->
    <div>
      <h4 class="text-md font-medium text-gray-900 dark:text-white mb-4">Dimensions</h4>
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <InputLabel for="length" value="Length" />
          <TextInput
            id="length"
            v-model="form.dimensions.length"
            type="number"
            step="0.01"
            min="0"
            class="mt-1 block w-full"
            placeholder="0.00"
          />
          <InputError :message="form.errors['dimensions.length']" class="mt-2" />
        </div>

        <div>
          <InputLabel for="width" value="Width" />
          <TextInput
            id="width"
            v-model="form.dimensions.width"
            type="number"
            step="0.01"
            min="0"
            class="mt-1 block w-full"
            placeholder="0.00"
          />
          <InputError :message="form.errors['dimensions.width']" class="mt-2" />
        </div>

        <div>
          <InputLabel for="height" value="Height" />
          <TextInput
            id="height"
            v-model="form.dimensions.height"
            type="number"
            step="0.01"
            min="0"
            class="mt-1 block w-full"
            placeholder="0.00"
          />
          <InputError :message="form.errors['dimensions.height']" class="mt-2" />
        </div>

        <div>
          <InputLabel for="unit" value="Unit" />
          <select
            id="unit"
            v-model="form.dimensions.unit"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
          >
            <option value="cm">Centimeters</option>
            <option value="in">Inches</option>
          </select>
          <InputError :message="form.errors['dimensions.unit']" class="mt-2" />
        </div>
      </div>
    </div>
  </div>
</template>
