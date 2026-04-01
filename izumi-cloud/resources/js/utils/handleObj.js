/**
 * Function clean Object if object has value  is [undefinded, ''] -> delete key
 * @param {Object} obj
 * @returns
 */
export function cleanObj(obj) {
    if (!obj) {
        return {};
    }

    for (const propName in obj) {
        if (obj[propName] === undefined || obj[propName] === '' || obj[propName] === null) {
            delete obj[propName];
        }
    }

    return obj;
}
