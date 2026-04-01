import Cookies from 'js-cookie';

function handleFormatMonth(month) {
    if (parseInt(month) < 10) {
        return `0${month}`;
    } else {
        return month;
    }
}

function handleReturnYearMonth() {
    const STORED_DATE = Cookies.get('driverRecorderYearMonth');

    const YEAR = new Date().getFullYear();
    const MONTH = new Date().getMonth() + 1;
    const DAY = new Date().getDate();

    if (STORED_DATE) {
        return JSON.parse(STORED_DATE);
    } else {
        return {
            date: `${YEAR}-${handleFormatMonth(MONTH)}`,
            year: YEAR,
            month: MONTH,
            day: DAY,
        };
    }
}

const state = {
    yearMonthPicker: '',
    yearMonth: handleReturnYearMonth(),
    current_page: 1,
    per_page: 20,
    current_index_recorder: parseInt(Cookies.get('current_index_recorder')) || 0,
    is_autoplay: Cookies.get('is_autoplay') || false,
};

const mutations = {
    SET_YEAR_MONTH(state, yearMonth) {
        state.yearMonthPicker = yearMonth;
    },
    SET_CURRENT_PAGE(state, currentPage) {
        state.current_page = currentPage;
    },
    SET_PER_PAGE(state, perPage) {
        state.per_page = perPage;
    },
    SET_CURRENT_INDEX_RECORDER(state, currentIndexRecorder) {
        state.current_index_recorder = currentIndexRecorder;
        Cookies.set('current_index_recorder', currentIndexRecorder);
    },
    SET_AUTOPLAY_STATUS(state, is_autoplay) {
        state.is_autoplay = is_autoplay;
        Cookies.set('is_autoplay', is_autoplay);
    },
    SET_DRIVER_RECORDER_YEAR_MONTH(state, date) {
        state.yearMonth = date;
        Cookies.set('driverRecorderYearMonth', date);
    },
};

const actions = {
    setYearMonth({ commit }, yearMonth) {
        commit('SET_YEAR_MONTH', yearMonth);
    },
    setCurrentPage({ commit }, currentPage) {
        commit('SET_CURRENT_PAGE', currentPage);
    },
    setPerPage({ commit }, perPage) {
        commit('SET_PER_PAGE', perPage);
    },
    setCurrentIndexRecorder({ commit }, currentIndexRecorder) {
        commit('SET_CURRENT_INDEX_RECORDER', currentIndexRecorder);
    },
    setAutoplayStatus({ commit }, is_autoplay) {
        commit('SET_AUTOPLAY_STATUS', is_autoplay);
    },
    setDriverRecorderYearMonth({ commit }, date) {
        commit('SET_DRIVER_RECORDER_YEAR_MONTH', date);
    },
};

export default {
    namespaced: true,
    state,
    mutations,
    actions,
};
