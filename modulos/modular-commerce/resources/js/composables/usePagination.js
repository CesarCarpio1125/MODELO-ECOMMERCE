import { computed } from 'vue';

/**
 * Pagination utilities and safety helpers
 * Provides consistent pagination handling across components
 */
export function usePagination() {
    /**
     * Filter pagination links to remove invalid URLs
     * @param {Array} links - Pagination links array
     * @returns {Array} Filtered links array
     */
    const safePaginationLinks = (links) => {
        return links?.filter(link => {
            return link?.url && 
                   link.url !== 'javascript:void(0)' && 
                   link.url !== '#' &&
                   link?.label;
        }) || [];
    };

    /**
     * Check if URL is valid for navigation
     * @param {string} url - URL to check
     * @returns {boolean} Whether URL is valid
     */
    const hasValidUrl = (url) => {
        return url && 
               url !== 'javascript:void(0)' && 
               url !== '#' &&
               typeof url === 'string';
    };

    /**
     * Get pagination info with safe defaults
     * @param {Object} paginationData - Laravel pagination object
     * @returns {Object} Safe pagination info
     */
    const getPaginationInfo = (paginationData) => {
        return {
            current_page: paginationData?.current_page || 1,
            from: paginationData?.from || 0,
            to: paginationData?.to || 0,
            total: paginationData?.total || 0,
            per_page: paginationData?.per_page || 15,
            last_page: paginationData?.last_page || 1,
            has_more: paginationData?.has_more_pages || false,
            has_previous: (paginationData?.current_page || 1) > 1,
            has_next: (paginationData?.current_page || 1) < (paginationData?.last_page || 1),
            prev_page_url: hasValidUrl(paginationData?.prev_page_url) ? paginationData.prev_page_url : null,
            next_page_url: hasValidUrl(paginationData?.next_page_url) ? paginationData.next_page_url : null,
            links: safePaginationLinks(paginationData?.links)
        };
    };

    /**
     * Generate safe page parameters for URLs
     * @param {number} page - Page number
     * @param {Object} additionalParams - Additional query parameters
     * @returns {URLSearchParams} Safe URL parameters
     */
    const buildPageParams = (page, additionalParams = {}) => {
        const params = new URLSearchParams();
        
        if (page && page > 1) {
            params.set('page', page.toString());
        }
        
        Object.entries(additionalParams).forEach(([key, value]) => {
            if (value !== null && value !== undefined && value !== '') {
                params.set(key, value.toString());
            }
        });
        
        return params;
    };

    return {
        safePaginationLinks,
        hasValidUrl,
        getPaginationInfo,
        buildPageParams
    };
}
