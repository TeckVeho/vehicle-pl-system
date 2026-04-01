const stub = {
    name: 'MonthPickerStub',
    render(h) {
        return h('div', { class: 'month-picker-stub' });
    },
};

module.exports = {
    MonthPicker: stub,
    MonthPickerInput: stub,
};
