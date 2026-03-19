import { ref, computed } from 'vue'

export function useProductFilters(initialProducts = [], initialFilters = {}) {
  const searchQuery = ref(initialFilters.search || '')
  const statusFilter = ref(initialFilters.status || 'all')
  const categoryFilter = ref(initialFilters.category || 'all')
  const sortBy = ref(initialFilters.sortBy || 'name')

  const filteredProducts = computed(() => {
    let filtered = initialProducts

    // Status filter
    if (statusFilter.value !== 'all') {
      filtered = filtered.filter(product => product.status === statusFilter.value)
    }

    // Category filter (if products have categories)
    if (categoryFilter.value !== 'all') {
      filtered = filtered.filter(product => product.category === categoryFilter.value)
    }

    // Search filter
    if (searchQuery.value) {
      const query = searchQuery.value.toLowerCase()
      filtered = filtered.filter(product =>
        product.name.toLowerCase().includes(query) ||
        product.description?.toLowerCase().includes(query) ||
        product.sku?.toLowerCase().includes(query)
      )
    }

    // Sort
    return filtered.sort((a, b) => {
      switch (sortBy.value) {
        case 'name':
          return a.name.localeCompare(b.name)
        case 'price_low':
          return a.price - b.price
        case 'price_high':
          return b.price - a.price
        case 'stock':
          return b.stock_quantity - a.stock_quantity
        case 'created':
          return new Date(b.created_at) - new Date(a.created_at)
        default:
          return 0
      }
    })
  })

  const productStats = computed(() => ({
    total: filteredProducts.value.length,
    active: filteredProducts.value.filter(p => p.status === 'active').length,
    draft: filteredProducts.value.filter(p => p.status === 'draft').length,
    archived: filteredProducts.value.filter(p => p.status === 'archived').length,
    lowStock: filteredProducts.value.filter(p => p.stock_quantity <= 5 && p.stock_quantity > 0).length,
    outOfStock: filteredProducts.value.filter(p => p.stock_quantity === 0).length
  }))

  const resetFilters = () => {
    searchQuery.value = ''
    statusFilter.value = 'all'
    categoryFilter.value = 'all'
    sortBy.value = 'name'
  }

  return {
    searchQuery,
    statusFilter,
    categoryFilter,
    sortBy,
    filteredProducts,
    productStats,
    resetFilters
  }
}
