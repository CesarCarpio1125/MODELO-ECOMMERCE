<script setup>
import { ref, onMounted, computed } from 'vue';
import { useChartFilters } from '@/composables/useChartFilters';

const props = defineProps({
    chartData: {
        type: Object,
        default: () => ({ labels: [], data: [], count: [] })
    },
    formattedTotalSales: {
        type: String,
        default: '0.00'
    },
    totalOrders: {
        type: [Number, String],
        default: 0
    }
});

// Use chart filters composable
const { selectedPeriod, isLoading, periods, currentPeriod, selectPeriod } = useChartFilters();

// Process chart data for display
const processedChartData = computed(() => {
    if (!props.chartData || !props.chartData.labels.length) {
        return {
            labels: ['No Data'],
            datasets: [{
                label: 'Sales',
                data: [0],
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4
            }]
        };
    }
    
    return {
        labels: props.chartData.labels,
        datasets: [{
            label: 'Sales',
            data: props.chartData.data,
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4
        }]
    };
});

// Check if chart data exists
const hasChartData = computed(() => {
    return props.chartData && props.chartData.labels && props.chartData.labels.length > 0;
});

const chartOptions = ref({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: true,
            position: 'top',
        },
        title: {
            display: true,
            text: 'Sales Overview'
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                callback: function(value) {
                    return '$' + value.toLocaleString();
                }
            }
        }
    }
});

// Make refresh function available globally
onMounted(() => {
    window.refreshChart = (days) => {
        isLoading.value = true;
        // Emit event to parent or call API
        console.log('Refreshing chart with days:', days);
        setTimeout(() => {
            isLoading.value = false;
        }, 1000);
    };
});
</script>

<template>
    <div class="overflow-hidden rounded-lg bg-white shadow-lg dark:bg-zinc-900">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    Sales Overview
                </h3>
                <div class="flex items-center space-x-2">
                    <button 
                        v-for="period in periods" 
                        :key="period.value"
                        @click="selectPeriod(period.value)"
                        :class="[
                            'rounded-md px-3 py-1 text-sm font-medium transition-colors',
                            selectedPeriod === period.value 
                                ? 'bg-blue-600 text-white hover:bg-blue-700' 
                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300'
                        ]"
                        :disabled="isLoading"
                    >
                        {{ period.label }}
                    </button>
                </div>
            </div>
            
            <div class="mt-6 h-80">
                <!-- Chart with real data -->
                <div v-if="hasChartData" class="flex h-full items-center justify-center rounded-lg bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-blue-900/20 dark:to-indigo-900/20">
                    <div class="text-center">
                        <svg class="mx-auto size-12 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <p class="mt-2 text-sm font-medium text-gray-600 dark:text-gray-400">
                            Chart Data Available
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-500">
                            {{ chartData.labels.length }} data points loaded
                        </p>
                    </div>
                </div>
                
                <!-- No data message -->
                <div v-else class="flex h-full items-center justify-center rounded-lg bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900/20 dark:to-gray-800/20">
                    <div class="text-center">
                        <svg class="mx-auto size-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <p class="mt-2 text-sm font-medium text-gray-600 dark:text-gray-400">
                            No Chart Data Available
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-500">
                            Start creating orders to see chart data
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="mt-4 grid grid-cols-2 gap-4">
                <div class="rounded-lg bg-blue-50 p-3 dark:bg-blue-900/20">
                    <p class="text-sm text-blue-600 dark:text-blue-400">Total Sales</p>
                    <p class="text-lg font-semibold text-blue-900 dark:text-blue-300">
                        ${{ formattedTotalSales }}
                    </p>
                </div>
                <div class="rounded-lg bg-green-50 p-3 dark:bg-green-900/20">
                    <p class="text-sm text-green-600 dark:text-green-400">Total Orders</p>
                    <p class="text-lg font-semibold text-green-900 dark:text-green-300">
                        {{ totalOrders }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
