function onlyNumberInput(e) {
    const key = e.keyCode || e.which;

    const LIST_VALIDATE = [69, 187, 188, 189, 190];

    if (LIST_VALIDATE.includes(key)) {
        e.preventDefault();

        return false;
    }
}

function inputPostCode(e) {
    const key = e.keyCode || e.which;

    const LIST_VALIDATE = [69, 187, 188, 189, 190];

    if (LIST_VALIDATE.includes(key) || !(e.shiftKey === false && (e.keyCode === 46 || e.keyCode === 8 || e.keyCode === 37 || e.keyCode === 39 || (e.keyCode >= 48 && e.keyCode <= 57)))) {
        e.preventDefault();

        return false;
    }
}

export {
    onlyNumberInput,
    inputPostCode
};
