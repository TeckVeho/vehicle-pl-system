export function convertMonth(month, lang = 'ja') {
    const DICTIONARY = {
        en: {
            1: 'January',
            2: 'February',
            3: 'March',
            4: 'April',
            5: 'May',
            6: 'June',
            7: 'July',
            8: 'August',
            9: 'September',
            10: 'October',
            11: 'November',
            12: 'December',
        },
        ja: {
            1: '1月',
            2: '2月',
            3: '3月',
            4: '4月',
            5: '5月',
            6: '6月',
            7: '7月',
            8: '8月',
            9: '9月',
            10: '10月',
            11: '11月',
            12: '12月',
        },
    };

    if (month >= 1 && month <= 12) {
        if (Object.keys(DICTIONARY).includes(lang)) {
            return DICTIONARY[lang][month];
        }
    }

    return '';
}

export function convertYear(year, lang = 'ja') {
    if (year) {
        if (['en', 'ja'].includes(lang)) {
            switch (lang) {
            case 'en':
                return `${year}`;

            case 'ja':
                return `${year}年`;
            }
        }
    }

    return '';
}

export function getFullText(month, year, lang = 'ja') {
    if (month && year) {
        switch (lang) {
        case 'en':
            return `${convertMonth(month, lang)} | ${convertYear(year, lang)}`;

        case 'ja':
            return `${convertYear(year, lang)} | ${convertMonth(month, lang)}`;
        }
    }

    return '';
}
