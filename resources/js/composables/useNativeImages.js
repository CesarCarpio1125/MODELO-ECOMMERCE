import { ref, watch } from 'vue'

export function useNativeImages() {
    const imageErrors = ref({})
    const loadingImages = ref({})

    // Detect if we're in Electron/NativePHP
    const isNative = () => {
        return window.electronAPI || 
               navigator.userAgent.includes('Electron') || 
               navigator.userAgent.includes('NativePHP')
    }

    // Generate proper image URL for current environment
    const getImageUrl = (path) => {
        if (!path) return null
        
        // Check if path is already a full URL (from API)
        if (path.startsWith('http://') || path.startsWith('https://')) {
            console.log('Using existing API URL:', path)
            return path
        }
        
        if (isNative()) {
            // In Electron, use the current host and port
            const protocol = window.location.protocol
            const host = window.location.host
            const fullUrl = `${protocol}//${host}/storage/${path}`
            console.log('Generated URL:', fullUrl)
            return fullUrl
        }
        
        return `/storage/${path}`
    }

    // Handle image loading with error tracking
    const handleImageLoad = (imageId) => {
        loadingImages.value[imageId] = false
        delete imageErrors.value[imageId]
    }

    const handleImageError = (imageId) => {
        loadingImages.value[imageId] = false
        imageErrors.value[imageId] = true
        console.warn(`Image failed to load: ${imageId}`)
    }

    const startImageLoad = (imageId) => {
        loadingImages.value[imageId] = true
    }

    // Force cache refresh for NativePHP (only when needed)
    const refreshImageCache = () => {
        if (isNative()) {
            console.log('Cache refresh triggered')
            
            // Clear any cached image data
            imageErrors.value = {}
            loadingImages.value = {}
            
            // Force reload only broken images with timestamp
            const images = document.querySelectorAll('img[src*="/storage/"]')
            let refreshedCount = 0
            images.forEach(img => {
                // Check if image failed to load
                if (img.naturalWidth === 0 || img.complete === false) {
                    const src = img.src
                    if (src) {
                        // Remove existing timestamp
                        const cleanSrc = src.split('?')[0]
                        const timestamp = Date.now()
                        img.src = `${cleanSrc}?_t=${timestamp}`
                        console.log('Refreshed broken image:', img.src)
                        refreshedCount++
                    }
                }
            })
            
            if (refreshedCount === 0) {
                console.log('No broken images found, skipping reload')
                return
            }
            
            console.log(`Refreshed ${refreshedCount} broken images`)
            
            // Only force page reload if there were many broken images
            if (refreshedCount > 3) {
                setTimeout(() => {
                    console.log('Many broken images detected, forcing page reload...')
                    window.location.reload()
                }, 2000)
            }
        }
    }

    return {
        imageErrors,
        loadingImages,
        isNative,
        getImageUrl,
        handleImageLoad,
        handleImageError,
        startImageLoad,
        refreshImageCache
    }
}
