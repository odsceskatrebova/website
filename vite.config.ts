import { resolve } from 'path';
import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import vuetify from 'vite-plugin-vuetify';
import symfonyPlugin from 'vite-plugin-symfony';

export default defineConfig({
    plugins: [
        vue(),
        vuetify({ autoImport: true }),
        symfonyPlugin(),
    ],
    resolve: {
        alias: {
            // '@': resolve('./assets/common'),
            '@odsct': resolve('./assets/odsct'),
            '@common': resolve('./assets/common'),
        },
    },
    base: '/build/odsct/',
    build: {
        outDir: './public/build/odsct',
        rollupOptions: {
            input: {
                odsct: './assets/odsct/app.ts',
            },
        },
    },
    server: {
        host: '0.0.0.0',
        port: 5173,
        origin: 'http://vite.odsct.local',
        cors: true,
        watch: {
            usePolling: true,
        },
    },
});
