import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
            // Configure for Native/Electron backend
            server: {
                host: '127.0.0.1',
                port: 5173, // Vite dev server port
                strictPort: true,
            },
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    // Development server configuration for Native
    server: {
        proxy: {
            // Proxy API requests to Native backend
            '/api': {
                target: 'http://127.0.0.1:8100',
                changeOrigin: true,
                secure: false,
            },
            // Proxy storage requests to Native backend
            '/storage': {
                target: 'http://127.0.0.1:8100',
                changeOrigin: true,
                secure: false,
            },
        },
    },
});
