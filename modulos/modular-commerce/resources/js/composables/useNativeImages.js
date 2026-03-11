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

    // Force cache refresh for NativePHP
    const refreshImageCache = () => {
        if (isNative()) {
            console.log('Aggressive cache refresh triggered')
            
            // Clear any cached image data
            imageErrors.value = {}
            loadingImages.value = {}
            
            // Clear all browser storage
            try {
                localStorage.clear()
                sessionStorage.clear()
            } catch (e) {
                console.warn('Could not clear storage:', e)
            }
            
            // Force reload all images with timestamp
            const images = document.querySelectorAll('img[src*="/storage/"]')
            images.forEach(img => {
                const src = img.src
                if (src) {
                    // Remove existing timestamp
                    const cleanSrc = src.split('?')[0]
                    const timestamp = Date.now()
                    img.src = `${cleanSrc}?_t=${timestamp}`
                    console.log('Refreshed image:', img.src)
                }
            })
            
            // Force page reload after 2 seconds to get fresh data
            setTimeout(() => {
                console.log('Forcing page reload for fresh data...')
                window.location.reload()
            }, 2000)
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
