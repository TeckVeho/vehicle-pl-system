import { validateYYYYMMDD } from '@/utils/validate';

export function handleChooseDate(from, to, type) {
    if ((!from && validateYYYYMMDD(to)) || (validateYYYYMMDD(from) && !to)) {
        return 0;
    }

    if (validateYYYYMMDD(from) && validateYYYYMMDD(to)) {
        if (new Date(from) > new Date(to)) {
            switch (type) {
            case 'FROM': {
                return 1;
            }

            case 'TO': {
                return 2;
            }

            default: {
                return -1;
            }
            }
        }

        if (new Date(to) >= new Date(from)) {
            return 0;
        }
    }

    return -1;
}
