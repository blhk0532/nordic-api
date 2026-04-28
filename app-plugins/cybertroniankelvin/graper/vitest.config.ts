import { defineConfig } from 'vitest/config';

export default defineConfig({
    test: {
        environment: 'jsdom',
        globals: true,
        include: ['resources/js/**/*.test.ts'],
        coverage: {
            provider: 'v8',
            include: ['resources/js/**/*.ts'],
            exclude: ['resources/js/**/*.test.ts'],
            thresholds: {
                lines: 80,
                functions: 80,
                branches: 80,
            },
        },
    },
});
