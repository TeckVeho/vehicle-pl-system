const state = {
    current_page: 1,
    per_page: 20,
};

const mutations = {
    SET_CURRENT_PAGE(state, currentPage) {
        state.current_page = currentPage;
    },
    SET_PER_PAGE(state, perPage) {
        state.per_page = perPage;
    },
};

const actions = {
    setCurrentPage({ commit }, currentPage) {
        commit('SET_CURRENT_PAGE', currentPage);
    },
    setPerPage({ commit }, perPage) {
        commit('SET_PER_PAGE', perPage);
    },
};

export default {
    namespaced: true,
    state,
    mutations,
    actions,
};
