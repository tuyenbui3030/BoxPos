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
                'packages/customer/resources/css/customer-components.css',
                'packages/customer/resources/js/constants.js',
                'packages/customer/resources/js/alpine-components.js'
            ],
            refresh: true,
        }),
    ],
});
