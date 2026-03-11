import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => {
        // First try to resolve from Modules directory
        const modulePages = import.meta.glob('./Modules/**/*.vue');
        
        // Try direct path
        let modulePath = `./Modules/${name}.vue`;
        if (modulePages[modulePath]) {
            return modulePages[modulePath]();
        }
        
        // Try with pages subdirectory for Store modules
        if (name.startsWith('Store/')) {
            const parts = name.split('/');
            const moduleName = parts[1]; // Product
            const pageName = parts[2]; // Create, Edit, etc.
            modulePath = `./Modules/Store/${moduleName}/pages/${pageName}.vue`;
            if (modulePages[modulePath]) {
                return modulePages[modulePath]();
            }
        }

        // Fallback to Pages directory
        return resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        );
    },
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
