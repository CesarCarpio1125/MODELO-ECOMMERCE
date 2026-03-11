<script setup>
defineProps({
    title: String,
    subtitle: String,
});
</script>

<template>
    <section class="text-center py-20">
        <h1 class="text-4xl md:text-6xl font-bold text-black dark:text-white mb-6">
            {{ title }}
        </h1>
        <p class="text-xl text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
            {{ subtitle }}
        </p>
    </section>
</template>
