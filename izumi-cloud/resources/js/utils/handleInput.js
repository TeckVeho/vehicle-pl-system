function validInputNumber(e) {
    var keystroke = String.fromCharCode(e.keyCode).toLowerCase();

    if (e.ctrlKey && keystroke === 'v') {
        e.returnValue = false;
    }

    const key = e.keyCode || e.which;

    const LIST_VALIDATE = [69, 187, 189, 190];

    if (LIST_VALIDATE.includes(key)) {
        e.preventDefault();

        return false;
    }
}

function validInputHalfWidthNumber(e) {
    const key = e.keyCode || e.which;

    if ([8, 9, 27, 13, 46, 35, 36, 37, 38, 39, 40].indexOf(key) !== -1 ||
        (key === 65 && e.ctrlKey === true) ||
        (key === 67 && e.ctrlKey === true) ||
        (key === 86 && e.ctrlKey === true) ||
        (key === 88 && e.ctrlKey === true)) {
        return;
    }

    if ((key < 48 || key > 57) && (key < 96 || key > 105)) {
        e.preventDefault();
        return false;
    }
}

function validInputHalfWidthNumberPaste(e) {
    const paste = (e.clipboardData || window.clipboardData).getData('text');

    if (!/^[0-9]*$/.test(paste)) {
        e.preventDefault();
        return false;
    }
}

export {
    validInputNumber,
    validInputHalfWidthNumber,
    validInputHalfWidthNumberPaste
};
