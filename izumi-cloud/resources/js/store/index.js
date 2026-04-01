import Vue from 'vue';
import Vuex from 'vuex';

import app from './modules/app';
import user from './modules/user';
import role from './modules/role';
import filter from './modules/filter';
import pagination from './modules/pagination';
import route_master from './modules/route_master';
import vehicleMaster from './modules/vehicle_master';
import driverRecorder from './modules/driver_recorder';
import user_management from './modules/user_management';
import driver_recorder from './modules/driver_recorder';
import store_management from './modules/store_management';

import getters from './getters';

Vue.use(Vuex);

const modules = {
    app,
    user,
    role,
    filter,
    pagination,
    route_master,
    vehicleMaster,
    driverRecorder,
    user_management,
    driver_recorder,
    store_management,
};

const store = new Vuex.Store({
    modules,
    getters,
});

export default store;
