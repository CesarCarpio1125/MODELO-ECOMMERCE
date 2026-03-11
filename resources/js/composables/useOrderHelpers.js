import { computed } from 'vue';

/**
 * Order-related utility functions and composables
 * Provides consistent formatting and status handling across order components
 */
export function useOrderHelpers() {
    /**
     * Format amount as USD currency
     * @param {number} amount - The amount to format
     * @returns {string} Formatted currency string
     */
    const formatCurrency = (amount) => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD'
        }).format(amount || 0);
    };

    /**
     * Get status color for Tailwind CSS classes
     * @param {string} status - Order status
     * @returns {string} Color name for CSS classes
     */
    const getStatusColor = (status) => {
        if (!status) return 'gray';
        const colors = {
            pending: 'yellow',
            processing: 'blue',
            shipped: 'purple',
            delivered: 'green',
            cancelled: 'red'
        };
        return colors[status] || 'gray';
    };

    /**
     * Get status icon SVG path
     * @param {string} status - Order status
     * @returns {string} SVG path data
     */
    const getStatusIcon = (status) => {
        if (!status) return 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z';
        const icons = {
            pending: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
            processing: 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',
            shipped: 'M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0zM13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0',
            delivered: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
            cancelled: 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'
        };
        return icons[status] || 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z';
    };

    /**
     * Get status CSS classes for badges
     * @param {string} status - Order status
     * @returns {string} CSS class string
     */
    const getStatusClasses = (status) => {
        const color = getStatusColor(status);
        return `bg-${color}-100 text-${color}-800`;
    };

    /**
     * Calculate order total from items
     * @param {Array} items - Order items array
     * @returns {number} Total amount
     */
    const calculateOrderTotal = (items) => {
        return items?.reduce((total, item) => {
            return total + ((item?.quantity || 0) * (item?.unit_price || 0));
        }, 0) || 0;
    };

    /**
     * Format order number with prefix
     * @param {string|number} orderNumber - Raw order number
     * @returns {string} Formatted order number
     */
    const formatOrderNumber = (orderNumber) => {
        if (!orderNumber) return 'N/A';
        return orderNumber.toString().startsWith('ORD-') 
            ? orderNumber 
            : `ORD-${orderNumber}`;
    };

    return {
        formatCurrency,
        getStatusColor,
        getStatusIcon,
        getStatusClasses,
        calculateOrderTotal,
        formatOrderNumber
    };
}
