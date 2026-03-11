<template>
    <div class="flex items-center gap-2">
        <button
            @click="handleSync"
            :disabled="isSyncing"
            class="inline-flex items-center px-3 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white text-sm font-medium rounded-md transition-colors"
        >
            <svg v-if="isSyncing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <svg v-else class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
            {{ isSyncing ? 'Syncing...' : 'Sync Data' }}
        </button>
        
        <button
            @click="handleRefresh"
            :disabled="isRefreshing"
            class="inline-flex items-center px-3 py-2 bg-green-600 hover:bg-green-700 disabled:bg-green-400 text-white text-sm font-medium rounded-md transition-colors"
        >
            <svg v-if="isRefreshing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <svg v-else class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
            {{ isRefreshing ? 'Refreshing...' : 'Refresh All' }}
        </button>
        
        <div v-if="lastSync" class="text-xs text-gray-500">
            Last: {{ formatTime(lastSync) }}
        </div>
        
        <div v-if="syncError" class="text-xs text-red-500 max-w-xs">
            {{ syncError }}
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { useSync } from '@/composables/useSync'

const { 
    isSyncing, 
    lastSync, 
    syncError, 
    syncData, 
    refreshCache 
} = useSync()

const isRefreshing = ref(false)

const formatTime = (timestamp) => {
    if (!timestamp) return 'Never'
    return new Date(timestamp).toLocaleTimeString()
}

const handleSync = async () => {
    try {
        await syncData()
    } catch (error) {
        // Error is handled by useSync
    }
}

const handleRefresh = async () => {
    isRefreshing.value = true
    try {
        await refreshCache()
    } catch (error) {
        // Error is handled by useSync
    } finally {
        isRefreshing.value = false
    }
}
</script>
