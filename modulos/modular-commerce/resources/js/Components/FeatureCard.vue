<script setup>
defineProps({
    title: String,
    description: String,
    icon: String,
    href: String,
});
</script>

<template>
    <a
        :href="href"
        class="flex items-start gap-4 rounded-lg bg-white p-6 shadow-lg ring-1 ring-white/[0.05] transition duration-300 hover:text-black/70 hover:ring-black/20 focus:outline-none focus-visible:ring-primary lg:pb-10 dark:bg-zinc-900 dark:ring-zinc-800 dark:hover:text-white/70 dark:hover:ring-zinc-700 dark:focus-visible:ring-primary"
    >
        <div class="flex size-12 shrink-0 items-center justify-center rounded-full bg-primary/10 sm:size-16">
            <svg class="size-5 sm:size-6" :class="icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <!-- Box icon -->
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
        </div>

        <div class="pt-3 sm:pt-5">
            <h2 class="text-xl font-semibold text-black dark:text-white">
                {{ title }}
            </h2>

            <p class="mt-4 text-sm/relaxed">
                {{ description }}
            </p>
        </div>

        <svg class="size-6 shrink-0 self-center stroke-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12h15m0 0l-6.75-6.75M19.5 12l-6.75 6.75"/>
        </svg>
    </a>
</template>
