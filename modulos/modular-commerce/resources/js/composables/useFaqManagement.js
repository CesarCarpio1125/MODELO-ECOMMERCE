import { ref, computed } from 'vue';

/**
 * FAQ management composable
 * Handles FAQ filtering, searching, and expansion logic
 */
export function useFaqManagement(faqs) {
    // Reactive state
    const searchQuery = ref('');
    const selectedCategory = ref('all');
    const expandedFAQ = ref(null);

    // Computed properties
    const filteredFAQs = computed(() => {
        let filtered = faqs.value || [];

        if (selectedCategory.value !== 'all') {
            filtered = filtered.filter(faq => faq.category.toLowerCase() === selectedCategory.value);
        }

        if (searchQuery.value) {
            const query = searchQuery.value.toLowerCase();
            filtered = filtered.filter(faq =>
                faq.question.toLowerCase().includes(query) ||
                faq.answer.toLowerCase().includes(query)
            );
        }

        return filtered;
    });

    const categories = computed(() => {
        const faqList = faqs.value || [];
        const cats = ['all', ...new Set(faqList.map(faq => faq.category.toLowerCase()))];
        return cats.map(cat => ({
            value: cat,
            label: cat.charAt(0).toUpperCase() + cat.slice(1)
        }));
    });

    const hasResults = computed(() => filteredFAQs.value.length > 0);

    // Methods
    const toggleFAQ = (index) => {
        expandedFAQ.value = expandedFAQ.value === index ? null : index;
    };

    const resetFilters = () => {
        searchQuery.value = '';
        selectedCategory.value = 'all';
        expandedFAQ.value = null;
    };

    return {
        // State
        searchQuery,
        selectedCategory,
        expandedFAQ,

        // Computed
        filteredFAQs,
        categories,
        hasResults,

        // Methods
        toggleFAQ,
        resetFilters
    };
}
