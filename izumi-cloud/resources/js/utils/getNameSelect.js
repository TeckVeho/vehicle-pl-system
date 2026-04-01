function getCrouseType(type) {
    const TYPE = {
        1: 'COURSE_TYPE_CSV',
        2: 'COURSE_TYPE_MASS_SALE',
        3: 'COURSE_TYPE_CO_DELIVERY',
        4: 'COURSE_TYPE_SIDEWAYS',
        5: 'COURSE_TYPE_KURABIN',
        6: 'COURSE_TYPE_GENERAL',
        7: 'COURSE_TYPE_TRUNK_LINE',
        8: 'COURSE_TYPE_STORE_DELIVERY',
        9: 'COURSE_TYPE_OTHER',
    };

    return TYPE[type] || '';
}

function getDeliveryType(type) {
    const TYPE = {
        0: 'DELIVERY_TYPE_DRY',
        1: 'DELIVERY_TYPE_CHILLED',
        2: 'DELIVERY_TYPE_FROZEN',
    };

    return TYPE[type] || '';
}

function getBinType(type) {
    const TYPE = {
        1: 'BIN_TYPE_ONE_DAY',
        2: 'BIN_TYPE_FIRST_HALF',
        3: 'BIN_TYPE_SECOND',
    };

    return TYPE[type] || '';
}

function getGate(type) {
    const TYPE = {
        0: 'GATE_EXISTING',
        1: 'GATE_NONE',
    };

    return TYPE[type] || '';
}

function getWing(type) {
    const TYPE = {
        0: 'WING_EXISTING',
        1: 'WING_NONE',
    };

    return TYPE[type] || '';
}

function getNameDepartment(list = [], id) {
    const DEPARTMENT = list.find(item => item.value === id);

    return DEPARTMENT ? DEPARTMENT.text : '';
}

function getNameRoutes(list = []) {
    if (Array.isArray(list)) {
        if (list.length > 0) {
            return list.map(item => item.name).join(', ');
        }
    }

    return '';
}

function getKeyInList(list = [], key) {
    let idx = 0;
    const len = list.length;

    const result = [];

    while (idx < len) {
        result.push(list[idx][key]);

        idx++;
    }

    return result;
}

function getHMS(time) {
    const DEFAULT = {
        hour: 0,
        min: 0,
        sec: 0,
    };

    const re = /^([0-1]?[0-9]|2[0-4]):([0-5][0-9])(:[0-5][0-9])?$/;

    const validate = re.test(time);

    if (validate) {
        const arr = time.split(':');

        return {
            hour: parseInt(arr[0]),
            min: parseInt(arr[1]),
            sec: parseInt(arr[2]),
        };
    }

    return DEFAULT;
}

function getDayInWeek(year, month, date) {
    if (!year || !month || !date) {
        return '';
    }

    const d = new Date(year, month - 1, date);

    const day = d.getDay();

    const LIBRARY = {
        0: 'DAY.SUN',
        1: 'DAY.MON',
        2: 'DAY.TUE',
        3: 'DAY.WED',
        4: 'DAY.THU',
        5: 'DAY.FRI',
        6: 'DAY.SAT',
    };

    return LIBRARY[day] || '';
}

export {
    getCrouseType,
    getDeliveryType,
    getBinType,
    getGate,
    getWing,
    getNameDepartment,
    getNameRoutes,
    getKeyInList,
    getHMS,
    getDayInWeek
};
