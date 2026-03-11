<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import HelpSectionCard from '@/Modules/Help/components/molecules/HelpSectionCard.vue';
import FaqItem from '@/Modules/Help/components/molecules/FaqItem.vue';
import { useFaqManagement } from '@/composables/useFaqManagement.js';

const props = defineProps({
    helpSections: {
        type: Array,
        required: true
    },
    faqs: {
        type: Array,
        required: true
    },
    contactInfo: {
        type: Object,
        required: true
    }
});

// Use FAQ management composable
const {
    searchQuery,
    selectedCategory,
    expandedFAQ,
    filteredFAQs,
    categories,
    hasResults,
    toggleFAQ
} = useFaqManagement(computed(() => props.faqs));

</script>

<template>
    <Head title="Help Center" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8 text-center">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                        Help Center
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                        Find answers to common questions, learn how to use our platform, and get support when you need it.
                    </p>
                </div>

                <!-- Help Sections -->
                <div class="mb-12">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-6">Help Topics</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <HelpSectionCard
                            v-for="section in helpSections"
                            :key="section.title"
                            :section="section"
                        />
                    </div>
                </div>

                <!-- FAQ Section -->
                <div class="mb-12">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-6">Frequently Asked Questions</h2>
                    
                    <!-- Search and Filter -->
                    <div class="mb-6 flex flex-col sm:flex-row gap-4">
                        <div class="flex-1">
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search FAQs..."
                                class="w-full px-4 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-zinc-700 dark:text-white"
                            />
                        </div>
                        <select
                            v-model="selectedCategory"
                            class="px-4 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-zinc-700 dark:text-white"
                        >
                            <option v-for="category in categories" :key="category.value" :value="category.value">
                                {{ category.label }}
                            </option>
                        </select>
                    </div>

                    <!-- FAQ Items -->
                    <div class="space-y-4">
                        <FaqItem
                            v-for="(faq, index) in filteredFAQs"
                            :key="index"
                            :faq="faq"
                            :is-expanded="expandedFAQ === index"
                            @toggle="toggleFAQ(index)"
                        />
                    </div>

                    <div v-if="!hasResults" class="text-center py-8 text-gray-500 dark:text-gray-400">
                        No FAQs found matching your search.
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="bg-blue-50 dark:bg-zinc-800 rounded-lg p-8 text-center">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">Still Need Help?</h2>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">
                        Our support team is here to help you with any questions or issues.
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div>
                            <div class="text-blue-600 dark:text-blue-400 font-medium mb-2">Email</div>
                            <a :href="`mailto:${contactInfo.email}`" class="text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400">
                                {{ contactInfo.email }}
                            </a>
                        </div>
                        <div>
                            <div class="text-blue-600 dark:text-blue-400 font-medium mb-2">Phone</div>
                            <a :href="`tel:${contactInfo.phone}`" class="text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400">
                                {{ contactInfo.phone }}
                            </a>
                        </div>
                        <div>
                            <div class="text-blue-600 dark:text-blue-400 font-medium mb-2">Hours</div>
                            <div class="text-gray-900 dark:text-white">
                                {{ contactInfo.hours }}
                            </div>
                        </div>
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        {{ contactInfo.responseTime }}
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
