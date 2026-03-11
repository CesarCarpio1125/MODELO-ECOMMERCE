<script setup>
import { useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { useNativeImages } from '@/composables/useNativeImages'

const props = defineProps({
    vendor: {
        type: Object,
        required: true
    },
    user: {
        type: Object,
        required: true
    }
})

const emit = defineEmits(['close'])

// Use native images composable for image handling
const { 
    isNative: isNativeEnvironment, 
    handleImageLoad, 
    handleImageError, 
    startImageLoad 
} = useNativeImages()

// Form data
const form = useForm({
    store_name: props.vendor.store_name,
    description: props.vendor.description || '',
    store_image: null,
    remove_image: false
})

// Computed property para mostrar la imagen actual
const currentImageUrl = computed(() => {
    if (form.remove_image) {
        return null
    }
    
    if (form.store_image) {
        return form.image_preview
    }
    
    return props.vendor.store_image_url
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
    form.patch(route('vendor.update', props.vendor.id), {
        onSuccess: () => {
            form.reset()
            emit('close')
            // Use Inertia navigation instead of page reload
            window.location.href = route('vendor.dashboard')
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

const close = () => {
    emit('close')
    form.reset()
    form.remove_image = false // Reset removal flag on close
}

const handleImageUpload = (event) => {
    const file = event.target.files[0]
    if (file) {
        // Store the File object - will be converted to base64 on submit if needed
        form.store_image = file
        form.remove_image = false // Reset removal flag when new image is selected
        
        // Always create preview using FileReader
        const reader = new FileReader()
        reader.onload = (e) => {
            form.image_preview = e.target.result
        }
        reader.readAsDataURL(file)
    }
}

const removeImage = () => {
    form.store_image = null
    form.image_preview = null
    form.remove_image = true
}
</script>

<template>
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-50" @click="close"></div>

        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-2xl transform overflow-hidden rounded-xl bg-white dark:bg-gray-900 p-6 text-left shadow-xl transition-all">
                <!-- Header -->
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Editar Tienda
                    </h3>
                    <button
                        @click="close"
                        class="rounded-md p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Current Image Preview -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Imagen Actual de la Tienda
                    </label>
                    <div class="flex items-center space-x-4">
                        <div class="w-24 h-24 bg-gray-200 dark:bg-gray-800 rounded-lg overflow-hidden">
                            <img
                                v-if="currentImageUrl"
                                :src="currentImageUrl"
                                alt="Current store image"
                                class="w-full h-full object-cover"
                            />
                            <div
                                v-else
                                class="w-full h-full flex items-center justify-center bg-gradient-to-r from-indigo-600 to-purple-600"
                            >
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                {{ currentImageUrl ? 'Imagen actual' : 'Sin imagen' }}
                            </p>
                            <div v-if="form.remove_image && !currentImageUrl" class="text-sm text-green-600 dark:text-green-400 mb-2">
                                <span v-if="form.processing">
                                    ✓ Imagen eliminada (guardando cambios...)
                                </span>
                                <span v-else>
                                    ✓ Imagen eliminada (cambios guardados)
                                </span>
                            </div>
                            <button
                                v-if="!form.remove_image && currentImageUrl && !form.image_preview"
                                @click="removeImage"
                                class="text-sm text-red-600 hover:text-red-500 dark:text-red-400"
                            >
                                Remover imagen
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label for="store_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Nombre de la Tienda *
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
                            Descripción de la Tienda
                        </label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="4"
                            class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Describe tu tienda, productos, o servicios..."
                        ></textarea>
                        <div v-if="form.errors.description" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.description }}
                        </div>
                    </div>

                    <!-- Image Upload -->
                    <div>
                        <label for="store_image" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Cambiar Imagen de la Tienda (Opcional)
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

                    <!-- Actions -->
                    <div class="flex gap-3 pt-4">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="flex-1 rounded-lg bg-indigo-600 px-4 py-2 font-medium text-white transition-colors hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span v-if="form.processing">Guardando...</span>
                            <span v-else>Guardar Cambios</span>
                        </button>
                        <button
                            type="button"
                            @click="close"
                            class="flex-1 rounded-lg bg-gray-200 px-4 py-2 font-medium text-gray-700 transition-colors hover:bg-gray-300 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 focus:outline-none dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                        >
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
