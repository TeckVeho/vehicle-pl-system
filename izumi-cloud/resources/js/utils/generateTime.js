function generateTime(hour, min) {
    if (isNaN(hour)) {
        hour = 0;
    }

    if (isNaN(min)) {
        min = 0;
    }

    if ((hour >= 0 && hour <= 23) && ([0, 10, 20, 30, 40, 50].includes(min))) {
        return `${format2Digit(hour)}:${format2Digit(min)}:00`;
    }

    return '';
}

function generateMonth(year, month) {
    if (!year || !month) {
        return null;
    }

    return `${year}-${format2Digit(month)}`;
}

function format2Digit(number) {
    if (number <= 0) {
        return '00';
    }

    return number > 9 ? '' + number : '0' + number;
}

export { generateTime, generateMonth };
