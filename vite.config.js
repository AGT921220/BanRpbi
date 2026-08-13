import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';
import inject from '@rollup/plugin-inject';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/pages/login.css',
                'resources/js/app.js',
                'resources/js/modules/zones/form.js',
                'resources/js/modules/clients/index.js',
                'resources/js/modules/clients/form.js',
                'resources/js/modules/drivers/index.js',
                'resources/js/modules/manifests/index.js',
                'resources/js/modules/manifests/manifestPdf.js',
                'resources/js/modules/manifests/manifestPdfBuilder.js',
                'resources/js/test.js'
            ],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        tailwindcss(),
        inject({
            $: 'jquery',
            jQuery: 'jquery',
            include: ['**/*.js', '**/*.ts', '**/*.jsx', '**/*.tsx'],
        }),
    ],
    optimizeDeps: {
        include: ['jquery'],
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
