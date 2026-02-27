import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export function useDashboard() {
    const page = usePage();
    
    // Reactive data from props
    const stats = ref(page.props.stats || []);
    const recentActivities = ref(page.props.recentActivities || []);
    const quickActions = ref(page.props.quickActions || []);
    const chartData = ref(page.props.chartData || { labels: [], data: [], count: [] });
    
    // Current user info
    const currentUser = computed(() => page.props.auth.user);
    const isAdmin = computed(() => currentUser.value?.email === 'admin@modular-commerce.com');
    
    // Computed properties for better performance
    const totalSales = computed(() => {
        const data = chartData.value?.data;
        if (!Array.isArray(data)) return 0;
        return data.reduce((sum, val) => sum + parseFloat(val || 0), 0);
    });
    
    const totalOrders = computed(() => {
        const count = chartData.value?.count;
        if (!Array.isArray(count)) return 0;
        return count.reduce((sum, val) => sum + (val || 0), 0);
    });
    
    const formattedTotalSales = computed(() => {
        return totalSales.value.toLocaleString('en-US', { 
            minimumFractionDigits: 2, 
            maximumFractionDigits: 2 
        });
    });
    
    const hasChartData = computed(() => {
        const labels = chartData.value?.labels;
        const data = chartData.value?.data;
        const count = chartData.value?.count;
        
        return Array.isArray(labels) && Array.isArray(data) && Array.isArray(count) && 
               (labels.length > 0 || data.length > 0 || count.length > 0);
    });
    
    return {
        // Data
        stats,
        recentActivities,
        quickActions,
        chartData,
        
        // User info
        currentUser,
        isAdmin,
        
        // Computed
        totalSales,
        totalOrders,
        formattedTotalSales,
        hasChartData,
    };
}
