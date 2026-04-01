import Cookies from 'js-cookie';
import { getLanguage } from '@/lang/helper/getLang';

const state = {
    language: getLanguage(),
    deviceMode: 'desktop',
};

const mutations = {
    SET_LANGUAGE: (state, language) => {
        state.language = language;
        Cookies.set('language', language);
    },
    SET_DEVICE_MODE: (state, deviceMode) => {
        state.deviceMode = deviceMode;
    },
};

const actions = {
    setLanguage({ commit }, language) {
        commit('SET_LANGUAGE', language);
    },
    setDeviceMode({ commit }, deviceMode) {
        commit('SET_DEVICE_MODE', deviceMode);
    },
};

export default {
    namespaced: true,
    state,
    mutations,
    actions,
};
