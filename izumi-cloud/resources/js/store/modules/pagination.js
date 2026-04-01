import Cookies from 'js-cookie';

const state = {
    employee_master_cp: null,
    employee_master_per_page: 20,
    employee_maste_upload: null,
    employee_maste_upload_per_page: 20,
    course_master_cp: null,
    course_master_per_page: 20,
    route_master_cp: null,
    route_master_per_page: 20,
    store_master_cp: null,
    store_master_per_page: 20,
    customer_master_cp: null,
    customer_master_per_page: 20,
    vehicle_master_cp: null,
    vehicle_master_per_page: 50,
    user_management_cp: null,
    user_management_per_page: 20,
};

const mutations = {
    SET_USER_MANAGEMENT_CP(state, user_management_cp) {
        state.user_management_cp = user_management_cp;
        Cookies.set('user_management_cp', user_management_cp);
    },
    SET_USER_MANAGEMENT_PER_PAGE(state, user_management_per_page) {
        state.user_management_per_page = user_management_per_page;
        Cookies.set('user_management_per_page', user_management_per_page);
    },
    SET_VEHICLE_MASTER_CP(state, vehicle_master_cp) {
        state.vehicle_master_cp = vehicle_master_cp;
        Cookies.set('vehicle_master_cp', vehicle_master_cp);
    },
    SET_VEHICLE_MASTER_PER_PAGE(state, vehicle_master_per_page) {
        state.vehicle_master_per_page = vehicle_master_per_page;
        Cookies.set('vehicle_master_per_page', vehicle_master_per_page);
    },
    SET_CUSTOMER_MASTER_CP(state, customer_master_cp) {
        state.customer_master_cp = customer_master_cp;
        Cookies.set('customer_master_cp', customer_master_cp);
    },
    SET_CUSTOMER_MASTER_PER_PAGE(state, customer_master_per_page) {
        state.customer_master_per_page = customer_master_per_page;
        Cookies.set('customer_master_per_page', customer_master_per_page);
    },
    SET_STORE_MASTER_CP(state, store_master_cp) {
        state.store_master_cp = store_master_cp;
        Cookies.set('store_master_cp', store_master_cp);
    },
    SET_STORE_MASTER_PER_PAGE(state, store_master_per_page) {
        state.store_master_per_page = store_master_per_page;
        Cookies.set('store_master_per_page', store_master_per_page);
    },
    SET_ROUTE_MASTER_CP(state, route_master_cp) {
        state.route_master_cp = route_master_cp;
        Cookies.set('route_master_cp', route_master_cp);
    },
    SET_ROUTE_MASTER_PER_PAGE(state, route_master_per_page) {
        state.route_master_per_page = route_master_per_page;
        Cookies.set('route_master_per_page', route_master_per_page);
    },
    SET_COURSE_MASTER_CP(state, course_master_cp) {
        state.course_master_cp = course_master_cp;
        Cookies.set('course_master_cp', course_master_cp);
    },
    SET_COURSE_MASTER_PER_PAGE(state, course_master_per_page) {
        state.course_master_per_page = course_master_per_page;
        Cookies.set('course_master_per_page', course_master_per_page);
    },
    SET_EMPLOYEE_MASTER_CP(state, employee_master_cp) {
        state.employee_master_cp = employee_master_cp;
        Cookies.set('employee_master_cp', employee_master_cp);
    },
    SET_EMPLOYEE_MASTER_PER_PAGE(state, employee_master_per_page) {
        state.employee_master_per_page = employee_master_per_page;
        Cookies.set('employee_master_per_page', employee_master_per_page);
    },
    SET_EMPLOYEE_UPLOAD(state, employee_maste_upload) {
        state.employee_maste_upload = employee_maste_upload;
        Cookies.set('employee_maste_upload', employee_maste_upload);
    },
    SET_EMPLOYEE_UPLOAD_PER_PAGE(state, employee_maste_upload_per_page) {
        state.employee_maste_upload_per_page = employee_maste_upload_per_page;
        Cookies.set('employee_maste_upload_per_page', employee_maste_upload_per_page);
    },
};

const actions = {
    setUserManagementCP({ commit }, user_management_cp) {
        commit('SET_USER_MANAGEMENT_CP', user_management_cp);
    },
    setUserManagementPerPage({ commit }, user_management_per_page) {
        commit('SET_USER_MANAGEMENT_PER_PAGE', user_management_per_page);
    },
    setVehicleMasterCP({ commit }, vehicle_master_cp) {
        commit('SET_VEHICLE_MASTER_CP', vehicle_master_cp);
    },
    setVehicleMasterPerPage({ commit }, vehicle_master_per_page) {
        commit('SET_VEHICLE_MASTER_PER_PAGE', vehicle_master_per_page);
    },
    setCustomerMasterCP({ commit }, customer_master_cp) {
        commit('SET_CUSTOMER_MASTER_CP', customer_master_cp);
    },
    setCustomerMasterPerPage({ commit }, customer_master_per_page) {
        commit('SET_CUSTOMER_MASTER_PER_PAGE', customer_master_per_page);
    },
    setStoreMasterCP({ commit }, store_master_cp) {
        commit('SET_STORE_MASTER_CP', store_master_cp);
    },
    setStoreMasterPerPage({ commit }, store_master_per_page) {
        commit('SET_STORE_MASTER_PER_PAGE', store_master_per_page);
    },
    setRouteMasterCP({ commit }, route_master_cp) {
        commit('SET_ROUTE_MASTER_CP', route_master_cp);
    },
    setRouteMasterPerPage({ commit }, route_master_per_page) {
        commit('SET_ROUTE_MASTER_PER_PAGE', route_master_per_page);
    },
    setCourseMasterCP({ commit }, course_master_cp) {
        commit('SET_COURSE_MASTER_CP', course_master_cp);
    },
    setCourseMasterPerPage({ commit }, course_master_per_page) {
        commit('SET_COURSE_MASTER_PER_PAGE', course_master_per_page);
    },
    setEmployeeMasterCP({ commit }, employee_master_cp) {
        commit('SET_EMPLOYEE_MASTER_CP', employee_master_cp);
    },
    setEmployeeMasterPerPage({ commit }, employee_master_per_page) {
        commit('SET_EMPLOYEE_MASTER_PER_PAGE', employee_master_per_page);
    },
    setEmployeeUpload({ commit }, employee_maste_upload) {
        commit('SET_EMPLOYEE_UPLOAD', employee_maste_upload);
    },
    setEmployeeUploadPerPage({ commit }, employee_maste_upload_per_page) {
        commit('SET_EMPLOYEE_UPLOAD_PER_PAGE', employee_maste_upload_per_page);
    },
};

export default {
    state,
    actions,
    mutations,
    namespaced: true,
};
