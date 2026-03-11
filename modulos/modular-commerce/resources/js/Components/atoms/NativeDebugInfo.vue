<template>
    <div v-if="showDebug" class="fixed bottom-4 right-4 bg-black text-white p-4 rounded-lg text-xs z-50 max-w-sm">
        <div class="font-bold mb-2">NativePHP Debug Info</div>
        <div>Environment: {{ isNative ? 'Electron' : 'Web' }}</div>
        <div>Host: {{ currentHost }}</div>
        <div>Protocol: {{ currentProtocol }}</div>
        <div>User Agent: {{ userAgent }}</div>
        <div v-if="lastSync">Last Sync: {{ formatTime(lastSync) }}</div>
        <div v-if="isSyncing" class="text-yellow-400">Syncing...</div>
        <div v-if="syncError" class="text-red-400">{{ syncError }}</div>
        
        <div class="mt-2 space-y-2">
            <div class="flex flex-wrap gap-1">
                <button 
                    @click="syncData" 
                    :disabled="isSyncing"
                    class="bg-green-500 text-white px-2 py-1 rounded text-xs disabled:opacity-50"
                >
                    {{ isSyncing ? 'Syncing...' : 'Sync Data' }}
                </button>
                <button 
                    @click="refreshImages" 
                    class="bg-blue-500 text-white px-2 py-1 rounded text-xs"
                >
                    Refresh Images
                </button>
                <button 
                    @click="refreshAll" 
                    :disabled="isRefreshing"
                    class="bg-purple-500 text-white px-2 py-1 rounded text-xs disabled:opacity-50"
                >
                    {{ isRefreshing ? 'Refreshing...' : 'Refresh All' }}
                </button>
            </div>
            <div class="flex flex-wrap gap-1">
                <button 
                    @click="clearCache" 
                    class="bg-red-500 text-white px-2 py-1 rounded text-xs"
                >
                    Clear Cache
                </button>
                <button 
                    @click="checkStatus" 
                    class="bg-gray-500 text-white px-2 py-1 rounded text-xs"
                >
                    Status
                </button>
                <button 
                    @click="fixAuth" 
                    :disabled="isFixingAuth"
                    class="bg-orange-500 text-white px-2 py-1 rounded text-xs disabled:opacity-50"
                >
                    {{ isFixingAuth ? 'Fixing...' : 'Fix Auth' }}
                </button>
            </div>
        </div>
        
        <!-- Status Display -->
        <div v-if="status" class="mt-2 text-xs">
            <div class="font-bold">System Status:</div>
            <div>Env: {{ status.environment }}</div>
            <div>Native: {{ status.is_native ? 'Yes' : 'No' }}</div>
            <div>Base URL: {{ status.base_url }}</div>
            <div v-if="status.auth_status" class="mt-1 font-bold">Auth Status:</div>
            <div v-if="status.auth_status">
                <div>Auth: {{ status.auth_status.authenticated ? 'Yes' : 'No' }}</div>
                <div v-if="status.auth_status.user_id">User ID: {{ status.auth_status.user_id }}</div>
                <div v-if="status.auth_status.user_email">Email: {{ status.auth_status.user_email }}</div>
                <div v-if="status.auth_status.user_name">Name: {{ status.auth_status.user_name }}</div>
                <div v-if="status.auth_status.user_role">Role: {{ status.auth_status.user_role }}</div>
                <div v-if="status.auth_status.vendors_count !== undefined">Vendors: {{ status.auth_status.vendors_count }}</div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useNativeImages } from '@/composables/useNativeImages'
import { useSync } from '@/composables/useSync'

const { refreshImageCache } = useNativeImages()
const { 
    isSyncing, 
    lastSync, 
    syncError, 
    syncData, 
    refreshCache, 
    getStatus 
} = useSync()

const showDebug = ref(false)
const currentHost = ref('')
const currentProtocol = ref('')
const userAgent = ref('')
const status = ref(null)
const isRefreshing = ref(false)
const isFixingAuth = ref(false)

const isNative = computed(() => {
    return window.electronAPI || 
           navigator.userAgent.includes('Electron') || 
           navigator.userAgent.includes('NativePHP')
})

const formatTime = (timestamp) => {
    if (!timestamp) return 'Never'
    return new Date(timestamp).toLocaleTimeString()
}

const refreshImages = () => {
    refreshImageCache()
    console.log('Images refreshed')
}

const refreshAll = async () => {
    isRefreshing.value = true
    try {
        await refreshCache()
        refreshImages()
    } finally {
        isRefreshing.value = false
    }
}

const clearCache = () => {
    localStorage.clear()
    sessionStorage.clear()
    location.reload()
}

const checkStatus = async () => {
    try {
        status.value = await getStatus()
        console.log('System status:', status.value)
    } catch (error) {
        console.error('Failed to get status:', error)
    }
}

const fixAuth = async () => {
    isFixingAuth.value = true
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        
        const response = await fetch('/api/auth/fix', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
                ...(csrfToken && { 'X-CSRF-TOKEN': csrfToken })
            }
        })
        
        if (response.ok) {
            const result = await response.json()
            console.log('Auth fixed:', result)
            alert('Authentication fixed! User data refreshed.')
            // Reload to get fresh data
            window.location.reload()
        } else {
            throw new Error(`Failed to fix auth: ${response.status}`)
        }
    } catch (error) {
        console.error('Auth fix error:', error)
        alert('Error fixing auth. Please try logging out manually.')
    } finally {
        isFixingAuth.value = false
    }
}

onMounted(() => {
    currentHost.value = window.location.host
    currentProtocol.value = window.location.protocol
    userAgent.value = navigator.userAgent
    
    // Show debug info in development
    if (import.meta.env.DEV) {
        showDebug.value = true
    }
    
    // Toggle debug with Ctrl+Shift+D
    document.addEventListener('keydown', (e) => {
        if (e.ctrlKey && e.shiftKey && e.key === 'D') {
            showDebug.value = !showDebug.value
        }
    })
})
</script>
