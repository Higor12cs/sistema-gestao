import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { ZiggyVue } from "../../vendor/tightenco/ziggy";

import jQuery from "jquery";
import Select2 from "select2";
import "bootstrap/dist/js/bootstrap.bundle.min.js";
import "admin-lte/dist/js/adminlte.min.js";
import "../css/app.css";
import "icheck-bootstrap/icheck-bootstrap.min.css";
import "select2/dist/css/select2.css";
import "admin-lte/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css";
import moment from 'moment';

window.$ = window.jQuery = jQuery;
window.moment = moment;
Select2($);

const appName = import.meta.env.VITE_APP_NAME || "Laravel";

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

        app.mount(el);

        jQuery(function () {
            try {
                $('[data-widget="treeview"]').Treeview("destroy");
            } catch (e) {
                //
            }

            setTimeout(function () {
                $('[data-widget="treeview"]').Treeview("init");
            }, 200);
        });

        return app;
    },
    scrollRegions: ['.main-sidebar .sidebar'],
    progress: {
        color: "#007BFF",
    },
});
