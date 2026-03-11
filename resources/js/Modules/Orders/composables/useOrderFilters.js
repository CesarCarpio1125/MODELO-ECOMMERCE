import { ref, computed, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

/**
 * Order filtering composable with performance optimizations
 */
export function useOrderFilters(baseUrl = '/orders') {
    const page = usePage();
    
    // Reactive filter state
    const search = ref('');
    const statusFilter = ref(page.props.filters?.status || '');
    const customerFilter = ref(page.props.filters?.customer_id || '');
    const dateFromFilter = ref(page.props.filters?.date_from || '');
    const dateToFilter = ref(page.props.filters?.date_to || '');
    const isLoading = ref(false);
    
    // Debounce function implementation
    let debounceTimeout = null;
    const debounce = (func, delay) => {
        return (...args) => {
            clearTimeout(debounceTimeout);
            debounceTimeout = setTimeout(() => func.apply(this, args), delay);
        };
    };

    // Computed filters object
    const activeFilters = computed(() => {
        const filters = {};
        
        if (statusFilter.value) filters.status = statusFilter.value;
        if (customerFilter.value) filters.customer_id = customerFilter.value;
        if (dateFromFilter.value) filters.date_from = dateFromFilter.value;
        if (dateToFilter.value) filters.date_to = dateToFilter.value;
        if (search.value) filters.search = search.value;
        
        return filters;
    });

    // Check if any filters are active
    const hasActiveFilters = computed(() => {
        return Object.keys(activeFilters.value).length > 0;
    });

    // Build URL parameters safely
    const buildFilterParams = () => {
        const params = new URLSearchParams();
        
        Object.entries(activeFilters.value).forEach(([key, value]) => {
            if (value !== null && value !== undefined && value !== '') {
                params.set(key, value.toString());
            }
        });
        
        return params;
    };

    // Apply filters with debouncing for performance
    const applyFilters = debounce(() => {
        isLoading.value = true;
        const params = buildFilterParams();
        
        // Use Inertia's visit for better UX
        window.location.href = `${baseUrl}?${params.toString()}`;
    }, 300);

    // Clear all filters
    const clearFilters = () => {
        search.value = '';
        statusFilter.value = '';
        customerFilter.value = '';
        dateFromFilter.value = '';
        dateToFilter.value = '';
        window.location.href = baseUrl;
    };

    // Watch for changes and auto-apply
    watch([search, statusFilter, customerFilter, dateFromFilter, dateToFilter], 
        () => applyFilters(),
        { deep: true }
    );

    return {
        // State
        search,
        statusFilter,
        customerFilter,
        dateFromFilter,
        dateToFilter,
        isLoading,
        
        // Computed
        activeFilters,
        hasActiveFilters,
        
        // Actions
        applyFilters,
        clearFilters
    };
}
