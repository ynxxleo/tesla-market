import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/css/premium.css', 'resources/css/trading.css', 'resources/css/operations.css', 'resources/css/experience.css', 'resources/css/mobile-profile.css', 'resources/css/mobile-overhaul.css', 'resources/css/mobile-menu.css', 'resources/css/theme-legal.css', 'resources/css/news.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
