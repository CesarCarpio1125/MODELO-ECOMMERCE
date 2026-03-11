<template>
    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <!-- Filters -->
            <OrderFilters
                :search="search"
                :status-filter="statusFilter"
                :date-from-filter="dateFromFilter"
                :date-to-filter="dateToFilter"
                :loading="isLoading"
                @update:search="updateSearch"
                @update:statusFilter="updateStatusFilter"
                @update:dateFromFilter="updateDateFromFilter"
                @update:dateToFilter="updateDateToFilter"
                @applyFilters="applyFilters"
                @clearFilters="clearFilters"
            />

            <!-- Orders Table -->
            <div class="bg-white dark:bg-zinc-800 shadow rounded-lg overflow-hidden">
                <OrderTable 
                    :orders="ordersList" 
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
import OrderFilters from '../molecules/OrderFilters.vue';
import SimpleModal from '../atoms/SimpleModal.vue';
import { useSimpleOrderFilters } from '../../composables/useSimpleOrderFilters';
import { useOrderActions } from '../../composables/useOrderActions';
import { useOrdersData } from '../../composables/useOrdersData';

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
const { 
    search, 
    statusFilter, 
    dateFromFilter,
    dateToFilter,
    isLoading, 
    applyFilters, 
    clearFilters 
} = useSimpleOrderFilters();
const { deleteModal, showDeleteModal, hideDeleteModal, confirmDelete } = useOrderActions();
const { paginationInfo, hasOrders, ordersList } = useOrdersData(props.orders);

// Event handlers
const updateSearch = (value) => search.value = value;
const updateStatusFilter = (value) => statusFilter.value = value;
const updateDateFromFilter = (value) => dateFromFilter.value = value;
const updateDateToFilter = (value) => dateToFilter.value = value;
const handleDelete = ({ orderId, orderNumber }) => {
    showDeleteModal(orderId, orderNumber);
};
</script>
