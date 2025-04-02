import { createApp, h } from "vue";
import { createInertiaApp, router } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { ZiggyVue } from "../../vendor/tightenco/ziggy";
import { setupPlugins } from "./plugins";
import { setupThirdParty } from "./bootstrap";
import { toast } from "vue3-toastify";

import "../css/app.css";
import "../css/toastify.css";
import "icheck-bootstrap/icheck-bootstrap.min.css";
import "select2/dist/css/select2.css";
import "admin-lte/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css";
import "vue3-toastify/dist/index.css";

setupThirdParty();

const appName = import.meta.env.VITE_APP_NAME || "Laravel";

router.on("invalid", (event) => {
    const responseBody = event.detail.response?.data;
    if (responseBody?.error_message) {
        toast.error(responseBody.error_message, {
            autoClose: 3000,
            position: toast.POSITION.TOP_RIGHT,
        });
        event.preventDefault();
    }
});

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob("./Pages/**/*.vue")
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue);

        setupPlugins(app);

        app.mount(el);

        initTreeview();

        return app;
    },
    scrollRegions: [".main-sidebar .sidebar"],
    progress: {
        color: "#007BFF",
    },
});

function initTreeview() {
    jQuery(function () {
        try {
            $('[data-widget="treeview"]').Treeview("destroy");
        } catch (e) {}
        setTimeout(function () {
            $('[data-widget="treeview"]').Treeview("init");
        }, 200);
    });
}
