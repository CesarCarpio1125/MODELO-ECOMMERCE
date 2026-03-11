<script setup>
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    vendor: {
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
    emit('newStore')
}
</script>

<template>
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            Acciones de Tienda
        </h3>
        <div class="grid grid-cols-2 gap-4">
            <!-- Enter Store -->
            <button
                @click="enterStore"
                class="flex items-center justify-center px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                Entrar a la Tienda
            </button>

            <!-- Edit Store -->
            <Link
                :href="route('vendor.edit')"
                class="flex items-center justify-center px-4 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors focus:ring-2 focus:ring-green-500 focus:ring-offset-2 focus:outline-none"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Editar Tienda
            </Link>

            <!-- Delete Store -->
            <button
                @click="deleteStore"
                class="flex items-center justify-center px-4 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:outline-none"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 7.514A2 2 0 00-2.828 0l-5.586 5.586a2 2 0 01.414 0l-5.586 5.586a2 2 0 005.828 0L12 21.172z" />
                </svg>
                Eliminar Tienda
            </button>

            <!-- New Store -->
            <button
                @click="newStore"
                class="flex items-center justify-center px-4 py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 focus:outline-none"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Nueva Tienda
            </button>
        </div>
    </div>
</template>
