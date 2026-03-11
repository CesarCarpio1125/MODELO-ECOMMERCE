<script setup>
import { Link } from '@inertiajs/vue3'

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

const emit = defineEmits(['editStore', 'deleteStore', 'newStore'])

const enterStore = () => {
    // Navigate to store public view
    window.location.href = route('store.show', props.vendor.store_slug)
}

const editStore = () => {
    emit('editStore')
}

const deleteStore = () => {
    if (confirm('¿Estás seguro de que quieres eliminar tu tienda? Esta acción no se puede deshacer.')) {
        emit('deleteStore')
    }
}

const newStore = () => {
    // This could create a new store for the same user
    emit('newStore')
}
</script>

<template>
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 overflow-hidden">
        <!-- Store Image -->
        <div class="relative h-48 bg-gray-200 dark:bg-gray-800">
            <img
                v-if="vendor.store_image"
                :src="vendor.store_image_url"
                :alt="vendor.store_name"
                class="w-full h-full object-cover"
            />
            <div
                v-else
                class="w-full h-full flex items-center justify-center bg-gradient-to-r from-indigo-600 to-purple-600"
            >
                <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
            </div>

            <!-- Status Badge -->
            <div class="absolute top-3 right-3">
                <span
                    :class="{
                        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200': vendor.status === 'pending',
                        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': vendor.status === 'active',
                        'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': vendor.status === 'suspended'
                    }"
                    class="px-2 py-1 text-xs font-medium rounded-full"
                >
                    {{ vendor.status.charAt(0).toUpperCase() + vendor.status.slice(1) }}
                </span>
            </div>
        </div>

        <!-- Store Info -->
        <div class="p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-1">
                        {{ vendor.store_name }}
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        @{{ vendor.store_slug }}
                    </p>
                </div>
            </div>

            <!-- Description -->
            <div class="mb-6">
                <p class="text-gray-700 dark:text-gray-300 text-sm leading-relaxed">
                    {{ vendor.description || 'Sin descripción' }}
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="grid grid-cols-2 gap-3">
                <!-- Enter Store -->
                <button
                    @click="enterStore"
                    class="flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    Ver Tienda
                </button>

                <!-- Edit Store -->
                <Link
                    :href="route('vendor.edit')"
                    class="flex items-center justify-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors focus:ring-2 focus:ring-green-500 focus:ring-offset-2 focus:outline-none"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Editar
                </Link>

                <!-- Delete Store -->
                <button
                    @click="deleteStore"
                    class="flex items-center justify-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:outline-none"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 7.514A2 2 0 00-2.828 0l-5.586 5.586a2 2 0 01.414 0l-5.586 5.586a2 2 0 005.828 0L12 21.172z" />
                    </svg>
                    Eliminar
                </button>

                <!-- New Store -->
                <button
                    @click="newStore"
                    class="flex items-center justify-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 focus:outline-none"
                    disabled
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Nueva
                </button>
            </div>
        </div>
    </div>
</template>
