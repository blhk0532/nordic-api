import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/index.ts',
            refresh: true,
        }),
    ],
    build: {
        lib: {
            entry: 'resources/js/index.ts',
            name: 'GraperEditor',
            fileName: 'index',
            formats: ['iife'],
        },
        rollupOptions: {
            external: [],
            output: {
                dir: '../../public/build/grapesjs',
                entryFileNames: '[name].js',
                chunkFileNames: '[name].js',
            },
        },
    },
});