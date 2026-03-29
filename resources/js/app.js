import "../css/app.css";
import "./bootstrap";

import { createInertiaApp, router } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { createApp, h } from "vue";
import { ZiggyVue } from "../../vendor/tightenco/ziggy";

const appName = import.meta.env.VITE_APP_NAME || "";

router.on("finish", () => document.body.classList.remove("sidebar-open"));

createInertiaApp({
    title: (title) => `${title} | ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob("./Pages/**/*.vue"),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue);

        app.mount(el);

        initTreeview();

        return app;
    },
    progress: {
        color: "#007bff",
    },
});

function initTreeview() {
    $(() => {
        const $treeview = $('[data-widget="treeview"]');
        const $pushmenu = $('[data-widget="pushmenu"]');

        if ($treeview.length) $treeview.Treeview("destroy").Treeview("init");
        if ($pushmenu.length) {
            $pushmenu.PushMenu("destroy").PushMenu({
                autoCollapseSize: 1024,
                enableRemember: false,
                collapseScreenSize: 1024,
            });
            $(document).on("click", (e) => {
                if (
                    $(window).width() < 1024 &&
                    $("body").hasClass("sidebar-open") &&
                    !$(e.target).closest(
                        ".main-sidebar, [data-widget='pushmenu']",
                    ).length
                ) {
                    $pushmenu.PushMenu("collapse");
                }
            });
        }
    });
}
