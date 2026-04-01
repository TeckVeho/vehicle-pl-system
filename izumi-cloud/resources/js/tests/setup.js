import Vue from 'vue';
import { config } from '@vue/test-utils';
require('dotenv').config();

import BootstrapVue from 'bootstrap-vue';
Vue.use(BootstrapVue);

import Bus from '@/eventBus';
Vue.use(Bus);

import Toast from '@/utils/toastMessage';
Vue.use(Toast);

import '@/bootstrap';

config.mocks.$t = key => key;

Vue.config.productionTip = false;
config.showDeprecationWarnings = false;

const noop = () => {};
Object.defineProperty(window, 'scrollTo', { value: noop, writable: true });

jest.setTimeout(30000);
