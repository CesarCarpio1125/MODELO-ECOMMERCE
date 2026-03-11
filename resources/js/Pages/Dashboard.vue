<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DashboardStats from '@/Components/Dashboard/DashboardStats.vue';
import RecentActivity from '@/Components/Dashboard/RecentActivity.vue';
import QuickActions from '@/Components/Dashboard/QuickActions.vue';
import ChartWidget from '@/Components/Dashboard/ChartWidget.vue';
import SellerPanelButton from '@/modules/vendor/components/atoms/SellerPanelButton.vue';
import VendorCTACard from '@/modules/vendor/components/organisms/VendorCTACard.vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useDashboard } from '@/composables/useDashboard';

// Get page props for flash messages
const page = usePage();
const flash = computed(() => page.props.flash || {});

// Check if vendor was just activated (from flash message)
const vendorJustActivated = computed(() => flash.value.vendor_activated === true);

// Use composable for dashboard logic
const {
    stats,
    recentActivities,
    quickActions,
    chartData,
    currentUser,
    isAdmin,
    formattedTotalSales,
    totalOrders,
    hasChartData
} = useDashboard();

// Check if user is vendor (from backend data, more reliable)
const isVendor = computed(() => page.props.isVendor || currentUser.role === 'vendor' || vendorJustActivated.value);
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Dashboard
                    </h2>
                    <span v-if="isAdmin" class="px-3 py-1 text-xs font-medium text-white bg-red-600 rounded-full">
                        Admin
                    </span>
                    <span v-else-if="isVendor" class="px-3 py-1 text-xs font-medium text-white bg-green-600 rounded-full">
                        Vendor
                    </span>
                    <span v-else class="px-3 py-1 text-xs font-medium text-white bg-blue-600 rounded-full">
                        Customer
                    </span>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        Welcome back, {{ currentUser.name }}!
                    </span>
                    <!-- Seller Panel Button for Vendors -->
                    <SellerPanelButton 
                        v-if="isVendor"
                        :href="route('vendor.dashboard')"
                    />
                </div>
            </div>
        </template>

        <div class="py-6">
            <!-- Success Message -->
            <div v-if="flash.success" class="mb-6 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 dark:bg-green-900 dark:border-green-800">
                    <div class="flex">
                        <svg class="w-5 h-5 text-green-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="text-sm text-green-800 dark:text-green-200">
                            {{ flash.success }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Vendor CTA for non-vendors -->
                <div v-if="!isVendor && !isAdmin" class="mb-8">
                    <VendorCTACard />
                </div>

                <!-- Stats Grid -->
                <DashboardStats :stats="stats" class="mb-8" />

                <!-- Main Content Grid -->
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <!-- Chart Section -->
                    <div class="lg:col-span-2">
                        <ChartWidget 
                            :chart-data="chartData" 
                            :formatted-total-sales="formattedTotalSales"
                            :total-orders="totalOrders"
                            class="h-96" 
                        />
                    </div>

                    <!-- Recent Activity -->
                    <div class="lg:col-span-1">
                        <RecentActivity :activities="recentActivities" />
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="mt-8">
                    <QuickActions :actions="quickActions" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
