<template>
    <div class="bg-white dark:bg-zinc-800 shadow rounded-lg p-6 mb-6">
        <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Filter Orders</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Search -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Search
                </label>
                <input 
                    :value="search"
                    @input="$emit('update:search', $event.target.value)"
                    type="text" 
                    placeholder="Search orders..."
                    class="w-full rounded-md border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white focus:border-blue-500 focus:ring-blue-500"
                >
            </div>
            
            <!-- Status -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Status
                </label>
                <select 
                    :value="statusFilter"
                    @change="$emit('update:statusFilter', $event.target.value)"
                    class="w-full rounded-md border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white focus:border-blue-500 focus:ring-blue-500"
                >
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="processing">Processing</option>
                    <option value="shipped">Shipped</option>
                    <option value="delivered">Delivered</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            
            <!-- Date From -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Date From
                </label>
                <input
                    :value="dateFromFilter"
                    @input="$emit('update:dateFromFilter', $event.target.value)"
                    type="date"
                    class="w-full rounded-md border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"
                />
            </div>
            
            <!-- Date To -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Date To
                </label>
                <input
                    :value="dateToFilter"
                    @input="$emit('update:dateToFilter', $event.target.value)"
                    type="date"
                    class="w-full rounded-md border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"
                />
            </div>
        </div>
        
        <!-- Filter Actions -->
        <div class="mt-4 flex gap-2">
            <button
                @click="applyFilters"
                :disabled="loading"
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
            >
                <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Apply Filters
            </button>
            <button
                @click="clearFilters"
                class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-zinc-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-zinc-800 hover:bg-gray-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
                Clear
            </button>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    search: {
        type: String,
        default: ''
    },
    statusFilter: {
        type: String,
        default: ''
    },
    dateFromFilter: {
        type: String,
        default: ''
    },
    dateToFilter: {
        type: String,
        default: ''
    },
    loading: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['update:search', 'update:statusFilter', 'update:dateFromFilter', 'update:dateToFilter', 'applyFilters', 'clearFilters']);

// Event handlers
const updateSearch = (value) => emit('update:search', value);
const updateStatusFilter = (value) => emit('update:statusFilter', value);
const updateDateFromFilter = (value) => emit('update:dateFromFilter', value);
const updateDateToFilter = (value) => emit('update:dateToFilter', value);
</script>
