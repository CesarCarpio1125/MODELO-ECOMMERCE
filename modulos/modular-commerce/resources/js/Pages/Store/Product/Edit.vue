<script setup>
import { ref, computed } from 'vue'
import { Link, useForm, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import DangerButton from '@/Components/DangerButton.vue'
import TextInput from '@/Components/TextInput.vue'
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'

const props = defineProps({
  product: Object,
})

const form = useForm({
  name: props.product.name,
  description: props.product.description || '',
  price: props.product.price,
  stock_quantity: props.product.stock_quantity,
  sku: props.product.sku || '',
  weight: props.product.weight || '',
  dimensions: {
    length: props.product.dimensions?.length || '',
    width: props.product.dimensions?.width || '',
    height: props.product.dimensions?.height || '',
    unit: props.product.dimensions?.unit || 'cm'
  },
  status: props.product.status,
  category_id: props.product.category_id || '',
  tags: props.product.tags || [],
  featured_image: null,
  images: [],
  variants: props.product.variants || []
})

const tagInput = ref('')
const showVariantForm = ref(false)

const addTag = () => {
  if (tagInput.value.trim() && !form.tags.includes(tagInput.value.trim())) {
    form.tags.push(tagInput.value.trim())
    tagInput.value = ''
  }
}

const removeTag = (index) => {
  form.tags.splice(index, 1)
}

const addVariant = () => {
  form.variants.push({
    name: '',
    price: '',
    stock: 0,
    weight: '',
    attributes: {},
    image: null
  })
}

const removeVariant = (index) => {
  form.variants.splice(index, 1)
}

const updateVariantAttribute = (variantIndex, key, value) => {
  form.variants[variantIndex].attributes[key] = value
}

const handleFeaturedImageUpload = (event) => {
  form.featured_image = event.target.files[0]
}

const handleImagesUpload = (event) => {
  form.images = Array.from(event.target.files)
}

const submit = () => {
  form.put(route('store.products.update', props.product.id))
}

const generateSku = () => {
  if (form.name) {
    const base = form.name.toUpperCase().replace(/[^A-Z0-9]/g, '').substring(0, 8)
    const random = Math.random().toString(36).substring(2, 6).toUpperCase()
    form.sku = base + '-' + random
  }
}

const formatPrice = (price) => {
  if (!price) return ''
  return parseFloat(price).toFixed(2)
}
</script>

<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <div>
          <h2 class="text-xl font-semibold leading-tight text-gray-800">
            Edit Product
          </h2>
          <p class="mt-1 text-sm text-gray-600">
            Update "{{ product.name }}"
          </p>
        </div>
        <Link :href="route('store.manage')">
          <SecondaryButton>
            Back to Products
          </SecondaryButton>
        </Link>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <form @submit.prevent="submit" class="space-y-6">
          <!-- Basic Information -->
          <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="md:col-span-2">
                <InputLabel for="name" value="Product Name *" />
                <TextInput
                  id="name"
                  v-model="form.name"
                  type="text"
                  class="mt-1 block w-full"
                  required
                  autofocus
                />
                <InputError :message="form.errors.name" class="mt-2" />
              </div>

              <div class="md:col-span-2">
                <InputLabel for="description" value="Description" />
                <textarea
                  id="description"
                  v-model="form.description"
                  rows="4"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                  placeholder="Describe your product..."
                ></textarea>
                <InputError :message="form.errors.description" class="mt-2" />
              </div>

              <div>
                <InputLabel for="price" value="Price ($) *" />
                <TextInput
                  id="price"
                  v-model="form.price"
                  type="number"
                  step="0.01"
                  min="0"
                  class="mt-1 block w-full"
                  required
                />
                <InputError :message="form.errors.price" class="mt-2" />
              </div>

              <div>
                <InputLabel for="stock_quantity" value="Stock *" />
                <TextInput
                  id="stock_quantity"
                  v-model.number="form.stock_quantity"
                  type="number"
                  min="0"
                  class="mt-1 block w-full"
                  required
                />
                <InputError :message="form.errors.stock_quantity" class="mt-2" />
              </div>

              <div>
                <InputLabel for="sku" value="SKU" />
                <div class="mt-1 flex gap-2">
                  <TextInput
                    id="sku"
                    v-model="form.sku"
                    type="text"
                    class="flex-1"
                    placeholder="Auto-generated if empty"
                  />
                  <SecondaryButton type="button" @click="generateSku" :disabled="!form.name">
                    Generate
                  </SecondaryButton>
                </div>
                <InputError :message="form.errors.sku" class="mt-2" />
              </div>

              <div>
                <InputLabel for="weight" value="Weight (kg)" />
                <TextInput
                  id="weight"
                  v-model="form.weight"
                  type="number"
                  step="0.01"
                  min="0"
                  class="mt-1 block w-full"
                />
                <InputError :message="form.errors.weight" class="mt-2" />
              </div>
            </div>
          </div>

          <!-- Current Images -->
          <div v-if="product.featured_image || product.images" class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Current Images</h3>
            
            <div class="space-y-4">
              <div v-if="product.featured_image" class="flex items-center gap-4">
                <img
                  :src="product.featured_image_url"
                  :alt="product.name"
                  class="w-24 h-24 object-cover rounded"
                />
                <div>
                  <p class="font-medium">Featured Image</p>
                  <p class="text-sm text-gray-500">This is the main product image</p>
                </div>
              </div>

              <div v-if="product.images && product.images.length > 0">
                <p class="font-medium mb-2">Additional Images</p>
                <div class="flex gap-2 flex-wrap">
                  <img
                    v-for="(image, index) in product.image_urls"
                    :key="index"
                    :src="image"
                    :alt="`${product.name} ${index + 1}`"
                    class="w-16 h-16 object-cover rounded"
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- New Images -->
          <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Upload New Images</h3>
            
            <div class="space-y-4">
              <div>
                <InputLabel for="featured_image" value="New Featured Image" />
                <input
                  id="featured_image"
                  type="file"
                  accept="image/*"
                  @change="handleFeaturedImageUpload"
                  class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                />
                <InputError :message="form.errors.featured_image" class="mt-2" />
                <p class="mt-1 text-sm text-gray-500">Upload a new featured image (max 2MB)</p>
              </div>

              <div>
                <InputLabel for="images" value="Additional Images" />
                <input
                  id="images"
                  type="file"
                  accept="image/*"
                  multiple
                  @change="handleImagesUpload"
                  class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                />
                <InputError :message="form.errors.images" class="mt-2" />
                <p class="mt-1 text-sm text-gray-500">Upload up to 10 additional images (max 2MB each)</p>
              </div>
            </div>
          </div>

          <!-- Tags -->
          <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Tags</h3>
            
            <div class="space-y-4">
              <div>
                <InputLabel for="tag_input" value="Add Tags" />
                <div class="mt-1 flex gap-2">
                  <TextInput
                    id="tag_input"
                    v-model="tagInput"
                    type="text"
                    class="flex-1"
                    placeholder="Enter a tag and press Add"
                    @keyup.enter="addTag"
                  />
                  <SecondaryButton type="button" @click="addTag">
                    Add
                  </SecondaryButton>
                </div>
              </div>

              <div v-if="form.tags.length > 0" class="flex flex-wrap gap-2">
                <span
                  v-for="(tag, index) in form.tags"
                  :key="index"
                  class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-indigo-100 text-indigo-800"
                >
                  {{ tag }}
                  <button
                    type="button"
                    @click="removeTag(index)"
                    class="ml-2 text-indigo-600 hover:text-indigo-800"
                  >
                    ×
                  </button>
                </span>
              </div>
            </div>
          </div>

          <!-- Product Variants -->
          <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-lg font-medium text-gray-900">Product Variants</h3>
              <SecondaryButton type="button" @click="addVariant">
                Add Variant
              </SecondaryButton>
            </div>
            
            <div v-if="form.variants.length === 0" class="text-center py-8 text-gray-500">
              <p>No variants added. Add variants for different sizes, colors, etc.</p>
            </div>

            <div v-else class="space-y-4">
              <div
                v-for="(variant, index) in form.variants"
                :key="variant.id || index"
                class="border rounded-lg p-4"
              >
                <div class="flex justify-between items-start mb-4">
                  <h4 class="font-medium text-gray-900">
                    Variant {{ index + 1 }} {{ variant.name ? `- ${variant.name}` : '' }}
                  </h4>
                  <SecondaryButton
                    type="button"
                    @click="removeVariant(index)"
                    class="text-red-600 hover:text-red-800"
                  >
                    Remove
                  </SecondaryButton>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <div>
                    <InputLabel :for="`variant_name_${index}`" value="Variant Name *" />
                    <TextInput
                      :id="`variant_name_${index}`"
                      v-model="variant.name"
                      type="text"
                      class="mt-1 block w-full"
                      required
                    />
                  </div>

                  <div>
                    <InputLabel :for="`variant_price_${index}`" value="Price ($) *" />
                    <TextInput
                      :id="`variant_price_${index}`"
                      v-model="variant.price"
                      type="number"
                      step="0.01"
                      min="0"
                      class="mt-1 block w-full"
                      required
                    />
                  </div>

                  <div>
                    <InputLabel :for="`variant_stock_${index}`" value="Stock *" />
                    <TextInput
                      :id="`variant_stock_${index}`"
                      v-model.number="variant.stock"
                      type="number"
                      min="0"
                      class="mt-1 block w-full"
                      required
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Status and Submit -->
          <div class="bg-white p-6 rounded-lg shadow">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <InputLabel for="status" value="Product Status" />
                <select
                  id="status"
                  v-model="form.status"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                  <option value="draft">Draft (not visible to customers)</option>
                  <option value="active">Active (visible to customers)</option>
                  <option value="archived">Archived (hidden from customers)</option>
                </select>
                <InputError :message="form.errors.status" class="mt-2" />
              </div>
            </div>

            <div class="mt-6 flex justify-between">
              <div>
                <Link :href="route('store.products.destroy', product.id)" method="delete" as="button">
                  <DangerButton>
                    Delete Product
                  </DangerButton>
                </Link>
              </div>
              <div class="flex gap-3">
                <Link :href="route('store.manage')">
                  <SecondaryButton>
                    Cancel
                  </SecondaryButton>
                </Link>
                <PrimaryButton :disabled="form.processing">
                  Update Product
                </PrimaryButton>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
