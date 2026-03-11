import { ref } from 'vue';

/**
 * Simple order filtering composable for template components
 */
export function useSimpleOrderFilters() {
    // Reactive filter state
    const search = ref('');
    const statusFilter = ref('');
    const dateFromFilter = ref('');
    const dateToFilter = ref('');
    const isLoading = ref(false);
    
    // Apply filters function
    const applyFilters = () => {
        isLoading.value = true;
        // This will be handled by the parent component
        setTimeout(() => {
            isLoading.value = false;
        }, 500);
    };
    
    // Clear filters function
    const clearFilters = () => {
        search.value = '';
        statusFilter.value = '';
        dateFromFilter.value = '';
        dateToFilter.value = '';
    };
    
    return {
        search,
        statusFilter,
        dateFromFilter,
        dateToFilter,
        isLoading,
        applyFilters,
        clearFilters
    };
}
