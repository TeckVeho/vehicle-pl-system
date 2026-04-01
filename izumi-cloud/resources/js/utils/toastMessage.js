import Vue from 'vue';
import { ToastPlugin } from 'bootstrap-vue';

Vue.use(ToastPlugin);

import i18n from '@/lang';

class MakeToast {
    constructor() {
        this.toast = new Vue();
    }

    _toast(content, options) {
        try {
            this.toast.$bvToast.toast(content, options);
        } catch (e) {
            // Jest: bv-toast có thể lỗi khi document / toaster chưa sẵn sàng hoặc sau teardown.
            if (typeof process !== 'undefined' && process.env.JEST_WORKER_ID !== undefined) {
                return;
            }
            throw e;
        }
    }

    show({ variant = null, title, content, toaster = 'b-toaster-top-center', autoHideDelay = 1500 }) {
        this._toast(content, {
            static: true,
            title: title,
            variant: variant,
            toaster: toaster,
            solid: true,
            autoHideDelay: autoHideDelay,
            appendToast: true,
        });
    }

    success({ title = i18n.t('SUCCESS'), content = '', toaster = 'b-toaster-top-center', autoHideDelay = 1500 }) {
        this._toast(content, {
            static: true,
            title: title,
            variant: 'success',
            toaster: toaster,
            solid: true,
            autoHideDelay: autoHideDelay,
            appendToast: true,
        });
    }

    warning({ title = i18n.t('WARNING'), content = '', toaster = 'b-toaster-top-center', autoHideDelay = 1500 }) {
        this._toast(content, {
            static: true,
            title: title,
            variant: 'warning',
            toaster: toaster,
            solid: true,
            autoHideDelay: autoHideDelay,
            appendToast: true,
        });
    }

    danger({ title = i18n.t('DANGER'), content = '', toaster = 'b-toaster-top-center', autoHideDelay = 1500 }) {
        this._toast(content, {
            static: true,
            title: title,
            variant: 'danger',
            toaster: toaster,
            solid: true,
            autoHideDelay: autoHideDelay,
            appendToast: true,
        });
    }

    hide(id) {
        this.toast.$bvToast.hide(id);
    }
}

export default {
    install(Vue) {
        const toast = new MakeToast();

        Vue.prototype.$toast = toast;
    },
};

