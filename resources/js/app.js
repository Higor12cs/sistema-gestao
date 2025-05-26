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
            import.meta.glob("./Pages/**/*.vue")
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
    jQuery(() => {
        try {
            $('[data-widget="treeview"]').Treeview("destroy");
        } catch {}
        setTimeout(() => {
            $('[data-widget="treeview"]').Treeview("init");
            try {
                $('[data-widget="pushmenu"]').PushMenu("destroy");
            } catch {}
            $('[data-widget="pushmenu"]').PushMenu({
                autoCollapseSize: 992,
                enableRemember: false,
                collapseScreenSize: 992,
            });
            $(document).on("click", (e) => {
                if (
                    $(window).width() < 992 &&
                    $("body").hasClass("sidebar-open") &&
                    !$(e.target).closest(".main-sidebar").length &&
                    !$(e.target).closest('[data-widget="pushmenu"]').length
                ) {
                    $('[data-widget="pushmenu"]').PushMenu("collapse");
                }
            });
        }, 200);
    });
}
