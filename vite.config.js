import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    build: {
        // بودجه‌ی وزن صفحه (NFR): هشدار پیش از عبور از سقف
        chunkSizeWarningLimit: 200,
    },
});
