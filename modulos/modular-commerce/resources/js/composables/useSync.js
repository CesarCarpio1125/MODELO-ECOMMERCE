import { ref } from 'vue'

export function useSync() {
    const isSyncing = ref(false)
    const lastSync = ref(localStorage.getItem('last_sync') || null)
    const syncError = ref(null)
    
    /**
     * Sync all data from server
     */
    const syncData = async (force = false) => {
        if (isSyncing.value) return
        
        isSyncing.value = true
        syncError.value = null
        
        try {
            const params = new URLSearchParams()
            if (lastSync.value && !force) {
                params.append('last_sync', lastSync.value)
            }
            
            // Get CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            
            const response = await fetch(`/api/sync?${params}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    ...(csrfToken && { 'X-CSRF-TOKEN': csrfToken })
                }
            })
            
            if (!response.ok) {
                throw new Error(`Sync failed: ${response.status}`)
            }
            
            const data = await response.json()
            
            // Update last sync timestamp
            lastSync.value = data.timestamp
            localStorage.setItem('last_sync', data.timestamp)
            
            // Emit custom event for components to listen
            window.dispatchEvent(new CustomEvent('data-synced', { 
                detail: data 
            }))
            
            // Force Inertia page reload if we're on vendor dashboard
            if (window.location.pathname.includes('/vendor/dashboard')) {
                // Reload the page to get fresh vendor data
                window.location.reload()
            }
            
            console.log('Data synced successfully:', data)
            return data
            
        } catch (error) {
            console.error('Sync error:', error)
            syncError.value = error.message
            throw error
        } finally {
            isSyncing.value = false
        }
    }
    
    /**
     * Force refresh all caches
     */
    const refreshCache = async () => {
        try {
            // Get CSRF token first
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            
            const response = await fetch('/api/refresh', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                    ...(csrfToken && { 'X-CSRF-TOKEN': csrfToken })
                }
            })
            
            if (!response.ok) {
                throw new Error(`Refresh failed: ${response.status}`)
            }
            
            const result = await response.json()
            console.log('Cache refreshed:', result)
            
            // Force sync after refresh
            await syncData(true)
            
            return result
            
        } catch (error) {
            console.error('Refresh error:', error)
            syncError.value = error.message
            throw error
        }
    }
    
    /**
     * Get system status
     */
    const getStatus = async () => {
        try {
            // Get CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            
            const response = await fetch('/api/status', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    ...(csrfToken && { 'X-CSRF-TOKEN': csrfToken })
                }
            })
            
            if (!response.ok) {
                throw new Error(`Status check failed: ${response.status}`)
            }
            
            return await response.json()
            
        } catch (error) {
            console.error('Status error:', error)
            throw error
        }
    }
    
    /**
     * Auto-sync on interval
     */
    const startAutoSync = (intervalMs = 30000) => {
        // Sync immediately
        syncData()
        
        // Then sync on interval
        return setInterval(() => {
            syncData()
        }, intervalMs)
    }
    
    /**
     * Clear sync data
     */
    const clearSyncData = () => {
        lastSync.value = null
        localStorage.removeItem('last_sync')
        syncError.value = null
    }
    
    return {
        isSyncing,
        lastSync,
        syncError,
        syncData,
        refreshCache,
        getStatus,
        startAutoSync,
        clearSyncData
    }
}
