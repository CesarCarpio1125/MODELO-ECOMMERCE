<script setup>
defineProps({
    laravelVersion: String,
    phpVersion: String,
});
</script>

<template>
    <footer class="py-16 text-center text-sm text-black dark:text-white/70">
        Modular Commerce v1.0 (Laravel v{{ laravelVersion }}, PHP v{{ phpVersion }})
    </footer>
</template>
