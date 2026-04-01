import Cookies from 'js-cookie';
import CONST_ROLE from '@/const/role';
import { hasRole } from '@/utils/hasRole';

function handleInitFilterEmployeeMaster() {
    const PROFILE = Cookies.get('profile');

    if (PROFILE) {
        const _PROFILE = JSON.parse(PROFILE);
        const DEPARTMENT = _PROFILE.department;
        const ROLE = _PROFILE.roles;
        const ROLE_CAN_EDIT = [CONST_ROLE.CLERKS, CONST_ROLE.TL, CONST_ROLE.DEPARTMENT_OFFICE_STAFF];

        if (hasRole(ROLE, ROLE_CAN_EDIT)) {
            return {
                affiliationBase: {
                    status: true,
                    value: DEPARTMENT.id,
                },
                supportBase: {
                    status: true,
                    value: DEPARTMENT.id,
                },
                employeeId: {
                    status: false,
                    value: '',
                },
                employeeName: {
                    status: false,
                    value: '',
                },
            };
        }
    }

    return {
        affiliationBase: {
            status: false,
            value: null,
        },
        supportBase: {
            status: false,
            value: null,
        },
        employeeId: {
            status: false,
            value: '',
        },
        employeeName: {
            status: false,
            value: '',
        },
    };
}

const state = {
    employeeMaster: handleInitFilterEmployeeMaster(),
    courseMaster: {
        department: {
            status: false,
            value: null,
        },
        course_id: {
            status: false,
            value: '',
        },
    },
    routeMaster: {
        department: {
            is_check: false,
            department_option: null,
        },
        routeName: {
            is_check: false,
            route_name_option: null,
        },
        customer: {
            is_check: false,
            customer_option: null,
        },
    },
    userMaster: {
        userName: {
            status: false,
            value: '',
        },
        userID: {
            status: false,
            value: '',
        },
        role: {
            status: false,
            value: '',
        },
    },
    dataList: {
        status: false,
        value: '',
    },
    dataConnect: {
        final_transfer_time: {
            status: false,
            from: '',
            to: '',
        },
        connection_data_name: {
            status: false,
            value: '',
        },
    },
    typeDateChange: '',
    vehicleMaster: {
        department_id: {
            status: false,
            value: '',
        },
        vehicle_no: {
            status: false,
            value: '',
        },
        number_plate: {
            status: false,
            value: '',
        },
        scrap_date: {
            status: false,
            value: '',
        },
        vehicle_inspection_expiry_date: {
            status: false,
            value: '',
        },
        vehicle_scrapped: {
            status: 1,
        },
    },
    storeMaster: {
        store_name: {
            status: false,
            value: '',
        },
    },
    urgentContactMaster: {
        userId: {
            status: false,
            value: '',
        },
        userName: {
            status: false,
            value: '',
        },
        departmentName: {
            status: false,
            value: null,
        },
    },
};

const mutations = {
    SET_FILTER_EMPLOYEE_MASTER(state, filter) {
        state.employeeMaster = filter;
    },
    SET_FILTER_COURSE_MASTER(state, filter) {
        state.courseMaster = filter;
    },
    SET_FILTER_ROUTE_MASTER(state, filter) {
        state.routeMaster = filter;
    },
    SET_FILTER_USER_MASTER(state, filter) {
        state.userMaster = filter;
    },
    SET_FILTER_DATA_LIST(state, filter) {
        state.dataList = filter;
    },
    SET_FILTER_DATA_CONNECT(state, filter) {
        state.dataConnect = filter;
    },
    SET_TYPE_DATE_CHANGE(state, type) {
        state.typeDateChange = type;
    },
    SET_FILTER_VEHICLE_MASTER(state, filter) {
        state.vehicleMaster = filter;
    },
    SET_FILTER_STORE_MASTER(state, filter) {
        state.storeMaster = filter;
    },
    SET_FILTER_URGENT_CONTACT_MASTER(state, filter) {
        state.urgentContactMaster = filter;
    },
};

const actions = {
    setFilterEmployeeMaster({ commit }, filter) {
        commit('SET_FILTER_EMPLOYEE_MASTER', filter);
    },
    setFilterCourseMaster({ commit }, filter) {
        commit('SET_FILTER_COURSE_MASTER', filter);
    },
    setFilterRouteMaster({ commit }, filter) {
        commit('SET_FILTER_ROUTE_MASTER', filter);
    },
    setFilterUserMaster({ commit }, filter) {
        commit('SET_FILTER_USER_MASTER', filter);
    },
    setFilterDataList({ commit }, filter) {
        commit('SET_FILTER_DATA_LIST', filter);
    },
    setFilterDataConnect({ commit }, filter) {
        commit('SET_FILTER_DATA_CONNECT', filter);
    },
    setTypeDateChange({ commit }, type) {
        commit('SET_TYPE_DATE_CHANGE', type);
    },
    setFilterVehicleMaster({ commit }, filter) {
        commit('SET_FILTER_VEHICLE_MASTER', filter);
    },
    setFilterStoreMaster({ commit }, filter) {
        commit('SET_FILTER_STORE_MASTER', filter);
    },
    setFilterUrgentContactMaster({ commit }, filter) {
        commit('SET_FILTER_URGENT_CONTACT_MASTER', filter);
    },
};

export default {
    state,
    actions,
    mutations,
    namespaced: true,
};
