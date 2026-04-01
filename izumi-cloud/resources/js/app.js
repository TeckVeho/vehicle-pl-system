import Vue from 'vue';
import App from '@/pages/App.vue';

import { MonthPicker } from 'vue-month-picker';
import { MonthPickerInput } from 'vue-month-picker';

Vue.use(MonthPicker);
Vue.use(MonthPickerInput);

// Permission
import './permission';

// Import Config Pusher
import './bootstrap';

// Router
import router from '@/router';

// Multi language
import i18n from '@/lang';

// Store
import store from '@/store';

// Event bus
import Bus from '@/eventBus';
Vue.use(Bus);

import MakeToast from '@/utils/toastMessage';
Vue.use(MakeToast);

// Component library
import { BootstrapVue, IconsPlugin } from 'bootstrap-vue';

import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';

Vue.use(BootstrapVue);
Vue.use(IconsPlugin);

// Icon Library
import '@/assets/font-awesome/css/all.min.css';

// Style
import '@/scss/index.scss';

Vue.config.productionTip = false;

new Vue({
    el: '#app',
    router,
    store,
    i18n,
    render: h => h(App),
});
