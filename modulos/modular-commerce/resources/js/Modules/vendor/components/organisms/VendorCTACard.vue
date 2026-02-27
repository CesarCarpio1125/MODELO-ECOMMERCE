<script setup>
import { ref } from 'vue'
import BecomeSellerButton from '../atoms/BecomeSellerButton.vue'
import VendorActivationForm from '../molecules/VendorActivationForm.vue'

const showActivationForm = ref(false)

const openActivationForm = () => {
    showActivationForm.value = true
}

const closeActivationForm = () => {
    showActivationForm.value = false
}
</script>

<template>
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 p-6">
        <!-- Header -->
        <div class="text-center mb-6">
            <div class="mx-auto w-16 h-16 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                Start Selling Today
            </h3>
            <p class="text-gray-600 dark:text-gray-400 max-w-sm mx-auto">
                Turn your passion into profit. Join thousands of sellers on our marketplace and reach millions of customers.
            </p>
        </div>

        <!-- Benefits -->
        <div class="space-y-3 mb-6">
            <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                No setup fees or monthly charges
            </div>
            <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Reach millions of customers
            </div>
            <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Easy-to-use seller tools
            </div>
        </div>

        <!-- CTA Button -->
        <div class="text-center">
            <a
                :href="route('vendor.activate')"
                class="inline-flex items-center justify-center rounded-lg px-6 py-3 font-medium text-white bg-gradient-to-r from-indigo-600 to-purple-600 transition-all duration-200 hover:from-indigo-700 hover:to-purple-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none w-full"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Become a Seller
            </a>
        </div>
    </div>
</template>
