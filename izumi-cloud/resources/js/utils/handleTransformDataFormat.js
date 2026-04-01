function getTheNumberOfStore(STORE_WITH_STATUS) {
    const len = STORE_WITH_STATUS.length;
    let idx = 0;

    const result = [];

    while (idx < len) {
        if (STORE_WITH_STATUS[idx].value === true) {
            result.push({
                id: STORE_WITH_STATUS[idx].id,
                text: STORE_WITH_STATUS[idx].store_name,
                value: STORE_WITH_STATUS[idx].value,
            });
        }

        idx++;
    }

    return result;
}

function getTheNumberOfStoreList(STORE_WITH_STATUS) {
    const len = STORE_WITH_STATUS.length;
    let idx = 0;

    const result = [];

    while (idx < len) {
        result.push({
            id: STORE_WITH_STATUS[idx].id,
            text: STORE_WITH_STATUS[idx].store_name,
            value: STORE_WITH_STATUS[idx].value,
        });

        idx++;
    }

    return result;
}

function handleSelectListStore(listStore, listSelected) {
    const LIST_ID_STORE_SELECTED = getArrValueOfKeyInList(listSelected, 'id');

    const len = listStore.length;
    let idx = 0;

    const result = [];

    while (idx < len) {
        const store = listStore[idx];

        if (LIST_ID_STORE_SELECTED.includes(store.id)) {
            store.value = true;
        } else {
            store.value = false;
        }

        result.push(store);

        idx++;
    }

    return result;
}

function getArrValueOfKeyInList(list = [], key = 'id') {
    const len = list.length;
    let idx = 0;

    const result = [];

    while (idx < len) {
        result.push(list[idx][key]);

        idx++;
    }

    return result;
}

export function handleTransformDataFormat(DATA = [], listStore = []) {
    const RESULT = [];

    if (DATA) {
        // Part 1: Adjust key and simulate key for loop in table
        const len = DATA.length;
        let idx = 0;

        while (idx < len) {
            const DATA_ITEM = DATA[idx];
            const STORE_WITH_STATUS = handleSelectListStore(listStore, DATA_ITEM.stores);

            const RESULT_ITEM = {
                id: DATA_ITEM.id,
                route_id: DATA_ITEM.id,
                department: DATA_ITEM.department_name,
                route_name: DATA_ITEM.name,
                customer: DATA_ITEM.customer_name,
                customer_id: DATA_ITEM.customer_id,
                fare_type: DATA_ITEM.route_fare_type,
                fare: DATA_ITEM.fare,
                highway_fee: DATA_ITEM.highway_fee,
                highway_fee_holiday: DATA_ITEM.highway_fee_holiday,
                suspension_of_service: [
                    {
                        id: 1,
                        value: false,
                    },
                    {
                        id: 2,
                        value: false,
                    },
                    {
                        id: 3,
                        value: false,
                    },
                    {
                        id: 4,
                        value: false,
                    },
                    {
                        id: 5,
                        value: false,
                    },
                    {
                        id: 6,
                        value: false,
                    },
                    {
                        id: 7,
                        value: false,
                    },
                    {
                        id: 8,
                        value: false,
                    },
                ],
                schedule: [
                    {
                        id: 1,
                        value: false,
                    },
                    {
                        id: 2,
                        value: false,
                    },
                    {
                        id: 3,
                        value: false,
                    },
                    {
                        id: 4,
                        value: false,
                    },
                    {
                        id: 5,
                        value: false,
                    },
                    {
                        id: 6,
                        value: false,
                    },
                    {
                        id: 7,
                        value: false,
                    },
                    {
                        id: 8,
                        value: false,
                    },
                    {
                        id: 9,
                        value: false,
                    },
                    {
                        id: 10,
                        value: false,
                    },
                    {
                        id: 11,
                        value: false,
                    },
                    {
                        id: 12,
                        value: false,
                    },
                    {
                        id: 13,
                        value: false,
                    },
                    {
                        id: 14,
                        value: false,
                    },
                    {
                        id: 15,
                        value: false,
                    },
                    {
                        id: 16,
                        value: false,
                    },
                    {
                        id: 17,
                        value: false,
                    },
                    {
                        id: 18,
                        value: false,
                    },
                    {
                        id: 19,
                        value: false,
                    },
                    {
                        id: 20,
                        value: false,
                    },
                    {
                        id: 21,
                        value: false,
                    },
                    {
                        id: 22,
                        value: false,
                    },
                    {
                        id: 23,
                        value: false,
                    },
                    {
                        id: 24,
                        value: false,
                    },
                    {
                        id: 25,
                        value: false,
                    },
                    {
                        id: 26,
                        value: false,
                    },
                    {
                        id: 27,
                        value: false,
                    },
                    {
                        id: 28,
                        value: false,
                    },
                    {
                        id: 29,
                        value: false,
                    },
                    {
                        id: 30,
                        value: false,
                    },
                    {
                        id: 31,
                        value: false,
                    },
                ],
                stores: DATA_ITEM.stores,
                store_count: DATA_ITEM.store_count,
                remark: DATA_ITEM.remark,
                is_government_holiday: DATA_ITEM.is_government_holiday,
                store_with_status: STORE_WITH_STATUS,
                the_number_of_store_list: getTheNumberOfStoreList(STORE_WITH_STATUS),
                the_number_of_store: getTheNumberOfStore(STORE_WITH_STATUS),
            };

            RESULT.push(RESULT_ITEM);

            idx++;
        }

        // Part 2: Handle schedule
        for (let i = 0; i < RESULT.length; i++) {
            for (let j = 0; j < RESULT[i].schedule.length; j++) {
                if (DATA[i].list_month.includes(RESULT[i].schedule[j].id)) {
                    RESULT[i].schedule[j].value = true;
                }
            }
        }

        // Part 3: Handle suspension_of_service
        for (let i = 0; i < RESULT.length; i++) {
            // 3a
            if (RESULT[i].is_government_holiday === 1) {
                RESULT[i].suspension_of_service[RESULT[i].suspension_of_service.length - 1].value = true;
            }

            // 3b
            for (let j = 0; j < RESULT[i].suspension_of_service.length; j++) {
                if (DATA[i].list_week.includes(RESULT[i].suspension_of_service[j].id)) {
                    RESULT[i].suspension_of_service[j].value = true;
                }
            }
        }

        return RESULT;
    }

    return DATA;
}
