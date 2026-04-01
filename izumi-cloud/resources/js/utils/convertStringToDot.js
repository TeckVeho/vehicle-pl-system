export function convertStringToDot(string) {
    const NORMAL_STATE_LENGTH = 20;
    if (string.length < NORMAL_STATE_LENGTH) {
        return string;
    } else {
        const fistHalf = string.slice(0, NORMAL_STATE_LENGTH);
        const secondHalf = '......';
        return fistHalf + secondHalf;
    }
}

export function convertStringWithLength(string, length) {
    const DOT = '...';
    if (!string) {
        return '';
    }

    if (length <= 0) {
        return '';
    }

    const LIST_TEXT = string.split(' ');

    if (LIST_TEXT.length < length) {
        return `${LIST_TEXT.join(' ')}`;
    }

    LIST_TEXT.length = length;

    return `${LIST_TEXT.join(' ')}${DOT}`;
}
