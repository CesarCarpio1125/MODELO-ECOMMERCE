import { computed } from 'vue';
import { usePagination } from '@/composables/usePagination';

export function useOrdersData(orders) {
    const { getPaginationInfo } = usePagination();
    
    // Computed properties
    const paginationInfo = computed(() => getPaginationInfo(orders));
    const hasOrders = computed(() => {
        return orders?.data && orders.data.length > 0;
    });
    const ordersList = computed(() => orders?.data || []);
    
    return {
        paginationInfo,
        hasOrders,
        ordersList
    };
}
