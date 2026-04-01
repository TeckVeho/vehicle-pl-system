const state = {
    yearMonthPicker: '',
};

const mutations = {
    SET_YEAR_MONTH(state, yearMonth) {
        state.yearMonthPicker = yearMonth;
    },
};

const actions = {
    setYearMonth({ commit }, yearMonth) {
        commit('SET_YEAR_MONTH', yearMonth);
    },
};

export default {
    namespaced: true,
    state,
    mutations,
    actions,
};
