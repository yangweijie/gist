import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/code-highlight.js',
                'resources/js/htmx-config.js',
                'resources/css/code-themes.css'
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        headers: {
            'Cross-Origin-Embedder-Policy': 'require-corp',
            'Cross-Origin-Opener-Policy': 'same-origin',
        },
    },
    optimizeDeps: {
        exclude: ['@php-wasm/web'],
    },
    build: {
        rollupOptions: {
            external: ['@php-wasm/web'],
        },
    },
});
