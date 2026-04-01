import { getPagination } from '@/utils/handleMultipleEditData';

const state = {
    pagination: getPagination() || {},
    is_show_full: false,
};

const mutations = {
    SET_PAGINATION(state, data) {
        state.pagination = data;
        window.localStorage.setItem('route_master_edit_pagination', JSON.stringify(state.pagination));
    },

    SET_IS_SHOW_FULL(state, value) {
        state.is_show_full = value;
        window.localStorage.setItem('route_master_edit_is_show_full', JSON.stringify(state.is_show_full));
    },
};

const actions = {
    setPagination({ commit }, data) {
        commit('SET_PAGINATION', data);
    },

    setIsShowFull({ commit }, value) {
        commit('SET_IS_SHOW_FULL', value);
    },
};

export default {
    namespaced: true,
    state,
    mutations,
    actions,
};
