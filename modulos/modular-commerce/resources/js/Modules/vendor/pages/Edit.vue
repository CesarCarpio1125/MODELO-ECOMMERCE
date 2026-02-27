<script setup>
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import VendorEditForm from '../components/molecules/VendorEditForm.vue'

defineProps({
    vendor: {
        type: Object,
        required: true
    },
    user: {
        type: Object,
        required: true
    }
})

// No emitimos nada aquí, el formulario se encarga de cerrar/redirigir
</script>

<template>
    <Head title="Editar Tienda" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Editar Tienda
                    </h2>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        {{ vendor.store_name }}
                    </span>
                    <a
                        href="#"
                        @click.prevent="$inertia.visit(route('vendor.dashboard'))"
                        class="text-indigo-600 hover:text-indigo-500 dark:text-indigo-400"
                    >
                        ← Volver al Dashboard
                    </a>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                            Editar Información de la Tienda
                        </h3>
                        <p class="text-gray-600 dark:text-gray-400">
                            Actualiza el nombre, descripción e imagen de tu tienda.
                        </p>
                    </div>
                </div>

                <!-- Edit Form Modal (always open for edit page) -->
                <VendorEditForm
                    :vendor="vendor"
                    :user="user"
                    @close="$inertia.visit(route('vendor.dashboard'))"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
