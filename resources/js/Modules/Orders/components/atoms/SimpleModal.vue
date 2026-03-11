<template>
    <Modal :show="show" @close="handleClose" max-width="lg">
        <div class="bg-white dark:bg-zinc-800 px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
                <!-- Icon container -->
                <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-900 sm:mx-0 sm:h-10 sm:w-10">
                    <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>
                
                <!-- Content -->
                <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                    <h3 class="text-lg font-semibold leading-6 text-gray-900 dark:text-white">
                        ¿Confirmar Eliminación?
                    </h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            ¿Estás seguro de que deseas eliminar la orden 
                            <span class="font-medium text-gray-900 dark:text-white">{{ orderNumber }}</span>?
                        </p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                            Esta acción no se puede deshacer.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Actions -->
        <div class="bg-gray-50 dark:bg-zinc-700 px-4 py-3 flex flex-row-reverse gap-3 sm:px-6">
            <button
                type="button"
                @click="handleConfirm"
                class="flex-1 inline-flex justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 transition-colors"
            >
                Eliminar
            </button>
            <button
                type="button"
                @click="handleClose"
                class="flex-1 inline-flex justify-center rounded-md bg-white dark:bg-zinc-600 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-300 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-zinc-500 hover:bg-gray-50 dark:hover:bg-zinc-500 transition-colors"
            >
                Cancelar
            </button>
        </div>
    </Modal>
</template>

<script setup>
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    orderNumber: {
        type: String,
        required: true
    }
});

const emit = defineEmits(['confirm', 'close']);

const handleConfirm = () => {
    emit('confirm');
};

const handleClose = () => {
    emit('close');
};
</script>
