export function changeKey(arr, value = '', text = '') {
    if (!arr) {
        return arr;
    }

    if (!value || !text) {
        return arr;
    }

    let idx = 0;
    const len = arr.length;

    while (idx < len) {
        const OLD_VALUE = arr[idx][value];
        const OLD_TEXT = arr[idx][text];

        delete arr[idx][value];
        delete arr[idx][text];

        arr[idx]['value'] = OLD_VALUE;
        arr[idx]['text'] = OLD_TEXT;

        idx++;
    }

    return arr;
}
