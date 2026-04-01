/**
 * Function validate number > 0
 * @param {*} number
 * @returns Boolean
 */
export function validateNumberMoreThanZero(number) {
    const re = /^[1-9][0-9]*$/;
    return re.test(number);
}

/**
 * Function validate has Empty or White Space
 * @param {*} string
 * @returns Boolean
 */
export function validEmptyOrWhiteSpace(string) {
    const re = /^\s*$/;
    return re.test(string);
}

/**
 * Function validate date -> YYYY/MM/DD
 * @param {*} value
 * @returns Boolean
 */
export function validateYYYYMMDD(value) {
    const re = /([12]\d{3}[./-](0[1-9]|1[0-2])[./-](0[1-9]|[12]\d|3[01]))/;
    return re.test(value);
}

/**
 * Function validate password
 * @param {*} password
 * @returns Boolean
 */
export function validPassword(password) {
    const re = /^\S{8,}$/;
    return re.test(password);
}

