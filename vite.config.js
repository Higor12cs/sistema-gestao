import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";

export default defineConfig({
    plugins: [
        laravel({
            input: "resources/js/app.js",
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    adminlte: ["admin-lte/dist/js/adminlte.min.js"],
                    jquery: ["jquery"],
                    bootstrap: [
                        "admin-lte/plugins/bootstrap/js/bootstrap.bundle.min.js",
                    ],
                },
            },
        },
    },
    define: {
        global: "globalThis",
    },
});
