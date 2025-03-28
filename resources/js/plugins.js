import Vue3Toastify, { toast } from 'vue3-toastify';

export function setupPlugins(app) {
    app.use(Vue3Toastify, {
        autoClose: 3000,
        position: toast.POSITION.TOP_RIGHT,
        clearOnUrlChange: false,
        theme: 'light',
        closeOnClick: true,
        pauseOnHover: true,
        hideProgressBar: false,
        transition: 'bounce',
    });
}
