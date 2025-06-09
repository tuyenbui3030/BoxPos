import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/vendors.css',
                'resources/js/app.js',
                'resources/js/vendors.js',
                // Customer Package Assets
                'packages/Customer/resources/css/customer-components.css',
                'packages/Customer/resources/js/constants.js',
                'packages/Customer/resources/js/alpine-components.js'
            ],
            refresh: true,
        }),
    ],
});
