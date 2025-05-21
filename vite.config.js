import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";
import { fileURLToPath, URL } from "url";

export default defineConfig({
    plugins: [
        vue(),
        laravel({
            input: ["resources/js/app.js"],
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            "@": fileURLToPath(new URL("./resources/js", import.meta.url)),
            "~": fileURLToPath(new URL("./resources", import.meta.url)),
        },
    },
    build: {
        sourcemap: true,
    },
});
