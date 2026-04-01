/**
 * Function helper pagination
 * @param {Array} arr
 * @param {Number} size
 * @returns List array with pagination
 */
export function handlePaginate(arr, size) {
    return arr.reduce((acc, val, i) => {
        const idx = Math.floor(i / size);
        const page = acc[idx] || (acc[idx] = []);
        page.push(val);

        return acc;
    }, []);
}
