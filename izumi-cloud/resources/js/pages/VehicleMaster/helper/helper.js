function handleGetCertificateByVehicleTotalWeight(value = null) {
    if (Number.isInteger(+value) && value) {
        if (value < 3500) {
            return '普通';
        }

        if (value >= 3500 && value < 7500) {
            return '準中型';
        }

        if (value >= 7500 && value < 11000) {
            return '中型';
        }

        if (value >= 11000) {
            return '大型';
        }
    }

    return null;
}

function formatDateDisplay(dateString) {
    if (!dateString) {
        return dateString;
    }

    const datePattern = /^(\d{4})-(\d{1,2})(-(\d{1,2}))?$/;
    const match = dateString.match(datePattern);

    if (!match) {
        return dateString;
    }

    const year = match[1];
    const month = match[2].padStart(2, '0');
    const day = match[4];

    if (day) {
        const paddedDay = day.padStart(2, '0');
        return `${year}-${month}-${paddedDay}`;
    } else {
        return `${year}-${month}`;
    }
}

export {
    handleGetCertificateByVehicleTotalWeight,
    formatDateDisplay
};
