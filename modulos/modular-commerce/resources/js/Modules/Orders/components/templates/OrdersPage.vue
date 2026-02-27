<template>
    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <!-- Filters -->
            <div class="bg-white dark:bg-zinc-800 shadow rounded-lg p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Search
                        </label>
                        <input 
                            v-model="search" 
                            type="text" 
                            placeholder="Search orders..."
                            class="w-full rounded-md border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white focus:border-blue-500 focus:ring-blue-500"
                        >
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Status
                        </label>
                        <select 
                            v-model="statusFilter" 
                            class="w-full rounded-md border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="shipped">Shipped</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Customer
                        </label>
                        <select 
                            v-model="customerFilter" 
                            class="w-full rounded-md border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option value="">All Customers</option>
                            <option v-for="customer in ($page.props.customers || [])" :key="customer?.id" :value="customer?.id">
                                {{ customer?.first_name }} {{ customer?.last_name }}
                            </option>
                        </select>
                    </div>
                    
                    <div class="flex items-end">
                        <button 
                            @click="applyFilters" 
                            class="w-full px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        >
                            Apply Filters
                        </button>
                    </div>
                </div>
            </div>

            <!-- Orders Table -->
            <div class="bg-white dark:bg-zinc-800 shadow rounded-lg overflow-hidden">
                <OrderTable 
                    :orders="orders?.data || []" 
                    :format-currency="formatCurrency"
                    :get-status-classes="getStatusClasses"
                    :get-status-icon="getStatusIcon"
                    :format-order-number="formatOrderNumber"
                    @delete="handleDelete"
                />
                
                <!-- Pagination -->
                <Pagination v-if="paginationInfo.links" :pagination-info="paginationInfo" />
            </div>
        </div>
        
        <!-- Delete Modal -->
        <SimpleModal 
            :show="deleteModal.show"
            :order-number="deleteModal.orderNumber"
            @confirm="confirmDelete"
            @close="hideDeleteModal"
        />
    </div>
</template>

<script setup>
import { computed } from 'vue';
import OrderTable from '../organisms/OrderTable.vue';
import Pagination from '../molecules/Pagination.vue';
import SimpleModal from '../atoms/SimpleModal.vue';
import { useOrderHelpers } from '@/composables/useOrderHelpers';
import { usePagination } from '@/composables/usePagination';
import { useOrderFilters } from '../../composables/useOrderFilters';
import { useOrderActions } from '../../composables/useOrderActions';

const props = defineProps({
    orders: {
        type: Object,
        required: true
    },
    filters: {
        type: Object,
        default: () => ({})
    }
});

// Use composables
const { formatCurrency, getStatusColor, getStatusIcon, getStatusClasses, formatOrderNumber } = useOrderHelpers();
const { getPaginationInfo } = usePagination();
const { 
    search, 
    statusFilter, 
    customerFilter, 
    isLoading, 
    applyFilters, 
    clearFilters 
} = useOrderFilters('/orders');
const { deleteModal, showDeleteModal, hideDeleteModal, confirmDelete } = useOrderActions();

// Computed properties for performance
const paginationInfo = computed(() => getPaginationInfo(props.orders));

// Event handlers
const handleDelete = ({ orderId, orderNumber }) => {
    showDeleteModal(orderId, orderNumber);
};
</script>
