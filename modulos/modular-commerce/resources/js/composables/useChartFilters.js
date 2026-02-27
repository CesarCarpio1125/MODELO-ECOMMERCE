import { ref, computed } from 'vue';

export function useChartFilters() {
    const selectedPeriod = ref('month');
    const isLoading = ref(false);
    
    const periods = [
        { value: 'week', label: 'Week', days: 7 },
        { value: 'month', label: 'Month', days: 30 },
        { value: 'year', label: 'Year', days: 365 }
    ];
    
    const currentPeriod = computed(() => {
        return periods.find(p => p.value === selectedPeriod.value) || periods[1];
    });
    
    const selectPeriod = (period) => {
        selectedPeriod.value = period;
        // Emit event or call callback to refresh data
        if (window.refreshChart && currentPeriod.value?.days) {
            window.refreshChart(currentPeriod.value.days);
        }
    };
    
    return {
        selectedPeriod,
        isLoading,
        periods,
        currentPeriod,
        selectPeriod,
    };
}
