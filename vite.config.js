import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        {
            name: 'externalize-electron',
            enforce: 'pre',
            resolveId(id) {
                if (id === 'electron') {
                    return { id, external: true };
                }
            },
        },
    ],
    build: {
        rollupOptions: {
            external: ['electron'],
        },
    },
});
