import jQuery from "jquery";
import Select2 from "select2";
import "bootstrap/dist/js/bootstrap.bundle.min.js";
import "admin-lte/dist/js/adminlte.min.js";
import moment from 'moment';

export function setupThirdParty() {
    window.$ = window.jQuery = jQuery;
    window.moment = moment;
    Select2($);
}
