import Cookies from 'js-cookie';

import { getToken } from '@/utils/handleToken';
import { getProfile } from '@/utils/handleProfile';
import constAuth from '@/const/auth';

const state = {
    access_token: getToken(),
    profile: getProfile(),
};

const mutations = {
    SET_ACCESS_TOKEN: (state, access_token) => {
        Cookies.set('token', access_token);
        state.access_token = access_token;
    },
    SET_PROFILE: (state, profile) => {
        Cookies.set('exp_token', profile.expToken);
        Cookies.set('profile', profile);
        state.profile = profile;
    },
    DO_LOGOUT: (state, profile) => {
        state.access_token = '';
        state.profile = profile;
        state.role = [];
        state.permission = [];
    },
    SET_USERNAME: (state, username) => {
        state.profile.name = username;
    },
};

const actions = {
    saveLogin({ commit }, userInfo) {
        commit('SET_PROFILE', userInfo.USER);
        commit('SET_ACCESS_TOKEN', userInfo.TOKEN);
    },
    setAutoLogin({ commit }, auth) {
        commit('SET_PROFILE', auth.USER);
        commit('SET_ACCESS_TOKEN', auth.TOKEN);
    },
    doLogout({ commit }) {
        const PROFILE = constAuth.PROFILE;

        commit('DO_LOGOUT', PROFILE);
        commit('SET_ACCESS_TOKEN', '');
        commit('SET_PROFILE', PROFILE);

        window.localStorage.setItem('multiple_edit_data', []);
    },
    setUsername({ commit }, username) {
        commit('SET_USERNAME', username);
    },
    setRefreshToken({ commit }, refreshToken) {
        commit('SET_ACCESS_TOKEN', refreshToken);
    },
};

export default {
    namespaced: true,
    state,
    mutations,
    actions,
};
