<script setup>
import { Head } from '@inertiajs/vue3';
import Header from '@/Components/Header.vue';
import HeroSection from '@/Components/HeroSection.vue';
import FeatureCard from '@/Components/FeatureCard.vue';
import Footer from '@/Components/Footer.vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});

function handleImageError() {
    document.getElementById('screenshot-container')?.classList.add('!hidden');
    document.getElementById('docs-card')?.classList.add('!row-span-1');
    document.getElementById('docs-card-content')?.classList.add('!flex-row');
    document.getElementById('background')?.classList.add('!hidden');
}
</script>

<template>
    <Head title="Welcome" />
    <div class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50">
        <div class="relative flex min-h-screen flex-col items-center justify-center selection:bg-primary selection:text-white">
            <div class="relative w-full max-w-2xl px-6 lg:max-w-7xl">
                <Header :can-login="canLogin" />

                <HeroSection
                    title="Welcome to Modular Commerce"
                    subtitle="Advanced modular e-commerce platform built with Laravel 12 and Vue 3. Featuring secure authentication, comprehensive product management, and scalable architecture."
                />

                <main class="mt-6">
                    <div class="grid gap-6 lg:grid-cols-2 lg:gap-8">
                        <FeatureCard
                            title="Authentication"
                            description="Secure user authentication with 2FA support and Google OAuth integration for enhanced security."
                            href="#"
                        />

                        <FeatureCard
                            title="User Management"
                            description="Comprehensive user management system with roles, permissions, and profile management."
                            href="#"
                        />

                        <FeatureCard
                            title="Product Management"
                            description="Flexible product catalog with categories, variants, and advanced inventory tracking."
                            href="#"
                        />

                        <FeatureCard
                            title="Inventory Control"
                            description="Real-time inventory management with stock alerts, low stock notifications, and automated reordering."
                            href="#"
                        />

                        <FeatureCard
                            title="Order Processing"
                            description="Streamlined order management with payment integration, shipping tracking, and order status updates."
                            href="#"
                        />

                        <FeatureCard
                            title="Dashboard Analytics"
                            description="Powerful dashboard with sales analytics, user insights, and performance metrics."
                            href="#"
                        />
                    </div>
                </main>

                <Footer :laravel-version="laravelVersion" :php-version="phpVersion" />
            </div>
        </div>
    </div>
</template>
