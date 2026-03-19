import { computed } from 'vue'

export function useProductHelpers() {
  const formatPrice = (price, currency = 'USD', locale = 'en-US') => {
    return new Intl.NumberFormat(locale, {
      style: 'currency',
      currency: currency,
    }).format(price)
  }

  const getStockStatus = (stock) => {
    if (stock === 0) return { 
      text: 'Out of Stock', 
      color: 'text-red-400', 
      bgColor: 'bg-red-500/10', 
      borderColor: 'border-red-500/40',
      level: 'out'
    }
    if (stock <= 5) return { 
      text: 'Low Stock', 
      color: 'text-yellow-400', 
      bgColor: 'bg-yellow-500/10', 
      borderColor: 'border-yellow-500/40',
      level: 'low'
    }
    return { 
      text: 'In Stock', 
      color: 'text-green-400', 
      bgColor: 'bg-green-500/10', 
      borderColor: 'border-green-500/40',
      level: 'in'
    }
  }

  const getStatusBadge = (status) => {
    const statusMap = {
      'active': { text: 'Active', color: 'text-green-400', bgColor: 'bg-green-500/10', borderColor: 'border-green-500/40' },
      'draft': { text: 'Draft', color: 'text-gray-400', bgColor: 'bg-gray-500/10', borderColor: 'border-gray-500/40' },
      'archived': { text: 'Archived', color: 'text-red-400', bgColor: 'bg-red-500/10', borderColor: 'border-red-500/40' }
    }
    
    return statusMap[status] || { 
      text: status, 
      color: 'text-gray-400', 
      bgColor: 'bg-gray-500/10', 
      borderColor: 'border-gray-500/40' 
    }
  }

  const isProductAvailable = (product) => {
    return product.status === 'active' && product.stock_quantity > 0
  }

  const getProductImage = (product) => {
    // Try different image URL fields that might exist
    const possibleUrls = [
      product.featured_image_url,
      product.image_url,
      product.thumbnail_url,
      product.featured_image
    ]
    
    // Return the first valid URL
    return possibleUrls.find(url => url && url !== '' && url !== null) || null
  }

  return {
    formatPrice,
    getStockStatus,
    getStatusBadge,
    isProductAvailable,
    getProductImage
  }
}
