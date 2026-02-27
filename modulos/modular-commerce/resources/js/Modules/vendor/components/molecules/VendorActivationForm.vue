<script setup>
import { useForm } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
    user: {
        type: Object,
        required: true
    }
})

const form = useForm({
    store_name: '',
    description: '',
    store_image: null
})

const isNativeEnvironment = computed(() => {
    // Detect if running in Electron/NativePHP
    return typeof window !== 'undefined' && 
           (window.navigator.userAgent.includes('Electron') || 
            window.navigator.userAgent.includes('NativePHP'))
})

const submit = () => {
    // For NativePHP, convert file to base64 if it's a File object
    if (isNativeEnvironment.value && form.store_image instanceof File) {
        convertFileToBase64(form.store_image).then(base64Image => {
            form.store_image = base64Image
            submitForm()
        })
    } else {
        submitForm()
    }
}

const submitForm = () => {
    form.post(route('vendor.activate.store'), {
        onSuccess: () => {
            // El backend hará la redirección al dashboard
        },
        onError: (errors) => {
            console.log('Validation errors:', errors)
        }
    })
}

const convertFileToBase64 = (file) => {
    return new Promise((resolve, reject) => {
        const reader = new FileReader()
        reader.onload = () => resolve(reader.result)
        reader.onerror = reject
        reader.readAsDataURL(file)
    })
}

const handleImageUpload = (event) => {
    const file = event.target.files[0]
    if (file) {
        // Store the File object - will be converted to base64 on submit if needed
        form.store_image = file
        
        // Always create preview using FileReader
        const reader = new FileReader()
        reader.onload = (e) => {
            form.image_preview = e.target.result
        }
        reader.readAsDataURL(file)
    }
}
</script>

<template>
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 p-6">
        <!-- Header -->
        <div class="mb-6">
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                Create Your Store
            </h3>
            <p class="text-gray-600 dark:text-gray-400">
                Fill in the details below to create your online store and start selling today.
            </p>
        </div>

        <!-- Preview Section -->
        <div v-if="form.image_preview" class="mb-6 text-center">
            <img 
                :src="form.image_preview" 
                alt="Preview de la tienda" 
                class="w-32 h-32 rounded-lg object-cover mx-auto border border-gray-200 dark:border-gray-700"
            />
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                Store logo preview
            </p>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" class="space-y-6">
            <div>
                <label for="store_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Store Name *
                </label>
                <input
                    id="store_name"
                    v-model="form.store_name"
                    type="text"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Ej: Tenis Deportivos Magangue"
                    required
                />
                <div v-if="form.errors.store_name" class="mt-1 text-sm text-red-600 dark:text-red-400">
                    {{ form.errors.store_name }}
                </div>
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Store Description
                </label>
                <textarea
                    id="description"
                    v-model="form.description"
                    rows="4"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Describe your store, products, or services..."
                ></textarea>
                <div v-if="form.errors.description" class="mt-1 text-sm text-red-600 dark:text-red-400">
                    {{ form.errors.description }}
                </div>
            </div>

            <!-- Logo Upload -->
            <div>
                <label for="store_image" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Store Logo (Optional)
                </label>
                <input
                    id="store_image"
                    type="file"
                    @change="handleImageUpload"
                    accept="image/*"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 file:mr-4 file:text-gray-500"
                />
                <div v-if="form.errors.store_image" class="mt-1 text-sm text-red-600 dark:text-red-400">
                    {{ form.errors.store_image }}
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-4">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full rounded-lg bg-indigo-600 px-6 py-3 font-medium text-white transition-colors hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span v-if="form.processing">Creating Store...</span>
                    <span v-else>Create Store</span>
                </button>
            </div>
        </form>
    </div>
</template>
