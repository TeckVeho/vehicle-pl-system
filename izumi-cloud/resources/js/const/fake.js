export const TABLE_COURSE = [
    {
        id: 1,
        department: '東京',
        course_id: 'AAA',
        total_fare: '10,000',
        highway_fee_total: '1,000',
        delivery_route: 'ローソンA店',
        total_operating_days: 25,
    },
    {
        id: 2,
        department: '東京',
        course_id: 'BBB',
        total_fare: '10,000',
        highway_fee_total: '1,000',
        delivery_route: 'ローソンB店, ローソンC店',
        total_operating_days: 25,
    },
    {
        id: 3,
        department: '東京',
        course_id: 'BBB',
        total_fare: '10,000',
        highway_fee_total: '1,000',
        delivery_route: 'ローソンB店, ローソンC店',
        total_operating_days: 25,
    },
    {
        id: 4,
        department: '東京',
        course_id: 'BBB',
        total_fare: '10,000',
        highway_fee_total: '1,000',
        delivery_route: 'ローソンB店, ローソンC店',
        total_operating_days: 25,
    },
    {
        id: 5,
        department: '東京',
        course_id: 'BBB',
        total_fare: '10,000',
        highway_fee_total: '1,000',
        delivery_route: 'ローソンB店, ローソンC店',
        total_operating_days: 25,
    },
    {
        id: 6,
        department: '東京',
        course_id: 'BBB',
        total_fare: '10,000',
        highway_fee_total: '1,000',
        delivery_route: 'ローソンB店, ローソンC店',
        total_operating_days: 25,
    },
    {
        id: 7,
        department: '東京',
        course_id: 'BBB',
        total_fare: '10,000',
        highway_fee_total: '1,000',
        delivery_route: 'ローソンB店, ローソンC店',
        total_operating_days: 25,
    },
    {
        id: 8,
        department: '東京',
        course_id: 'BBB',
        total_fare: '10,000',
        highway_fee_total: '1,000',
        delivery_route: 'ローソンB店, ローソンC店',
        total_operating_days: 25,
    },
];

export function fakeDataFare() {
    const FAKE = ['AAAA', 'BBBB', 'CCCC', 'DDDD', ''];

    const random = Math.floor(Math.random() * FAKE.length);

    return FAKE[random];
}

export const TABLE_ROUTE_MASTER = [
    {
        id: 1,
        department: '東京',
        route_name: 'AAAA',
        customer: '山崎製パン',
        fare_type: '日額',
        fare: '30,000',
        highway_fee: '1,000',
        highway_fee_holiday: '2,000',
        the_number_of_store: 1,
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
                mvalueizu: false,
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
                value: true,
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
                value: true,
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
        the_number_of_store_list: [
            {
                id: 1,
                text: 'ローソンA店',
            },
            {
                id: 1,
                text: 'ローソンB店',
            },
            {
                id: 1,
                text: 'ローソンC店',
            },
            {
                id: 1,
                text: 'ローソンD店',
            },
            {
                id: 1,
                text: 'ローソンE店',
            },
            {
                id: 1,
                text: 'ローソンF店',
            },
        ],
    }, {
        id: 1,
        department: '東京',
        route_name: 'AAAA',
        customer: '山崎製パン',
        fare_type: '日額',
        fare: '30,000',
        highway_fee: '1,000',
        highway_fee_holiday: '2,000',
        the_number_of_store: 1,
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
                mvalueizu: false,
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
                value: true,
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
                value: true,
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
        the_number_of_store_list: [
            {
                id: 1,
                text: 'ローソンA店',
            },
            {
                id: 1,
                text: 'ローソンB店',
            },
            {
                id: 1,
                text: 'ローソンC店',
            },
            {
                id: 1,
                text: 'ローソンD店',
            },
            {
                id: 1,
                text: 'ローソンE店',
            },
            {
                id: 1,
                text: 'ローソンF店',
            },
        ],
    }, {
        id: 1,
        department: '東京',
        route_name: 'AAAA',
        customer: '山崎製パン',
        fare_type: '日額',
        fare: '30,000',
        highway_fee: '1,000',
        highway_fee_holiday: '2,000',
        the_number_of_store: 1,
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
                mvalueizu: false,
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
                value: true,
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
                value: true,
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
        the_number_of_store_list: [
            {
                id: 1,
                text: 'ローソンA店',
            },
            {
                id: 1,
                text: 'ローソンB店',
            },
            {
                id: 1,
                text: 'ローソンC店',
            },
            {
                id: 1,
                text: 'ローソンD店',
            },
            {
                id: 1,
                text: 'ローソンE店',
            },
            {
                id: 1,
                text: 'ローソンF店',
            },
        ],
    }, {
        id: 1,
        department: '東京',
        route_name: 'AAAA',
        customer: '山崎製パン',
        fare_type: '日額',
        fare: '30,000',
        highway_fee: '1,000',
        highway_fee_holiday: '2,000',
        the_number_of_store: 1,
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
                mvalueizu: false,
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
                value: true,
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
                value: true,
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
        the_number_of_store_list: [
            {
                id: 1,
                text: 'ローソンA店',
            },
            {
                id: 1,
                text: 'ローソンB店',
            },
            {
                id: 1,
                text: 'ローソンC店',
            },
            {
                id: 1,
                text: 'ローソンD店',
            },
            {
                id: 1,
                text: 'ローソンE店',
            },
            {
                id: 1,
                text: 'ローソンF店',
            },
        ],
    }, {
        id: 1,
        department: '東京',
        route_name: 'AAAA',
        customer: '山崎製パン',
        fare_type: '日額',
        fare: '30,000',
        highway_fee: '1,000',
        highway_fee_holiday: '2,000',
        the_number_of_store: 1,
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
                mvalueizu: false,
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
                value: true,
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
                value: true,
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
        the_number_of_store_list: [
            {
                id: 1,
                text: 'ローソンA店',
            },
            {
                id: 1,
                text: 'ローソンB店',
            },
            {
                id: 1,
                text: 'ローソンC店',
            },
            {
                id: 1,
                text: 'ローソンD店',
            },
            {
                id: 1,
                text: 'ローソンE店',
            },
            {
                id: 1,
                text: 'ローソンF店',
            },
        ],
    }, {
        id: 1,
        department: '東京',
        route_name: 'AAAA',
        customer: '山崎製パン',
        fare_type: '日額',
        fare: '30,000',
        highway_fee: '1,000',
        highway_fee_holiday: '2,000',
        the_number_of_store: 1,
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
                mvalueizu: false,
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
                value: true,
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
                value: true,
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
        the_number_of_store_list: [
            {
                id: 1,
                text: 'ローソンA店',
            },
            {
                id: 1,
                text: 'ローソンB店',
            },
            {
                id: 1,
                text: 'ローソンC店',
            },
            {
                id: 1,
                text: 'ローソンD店',
            },
            {
                id: 1,
                text: 'ローソンE店',
            },
            {
                id: 1,
                text: 'ローソンF店',
            },
        ],
    }, {
        id: 1,
        department: '東京',
        route_name: 'AAAA',
        customer: '山崎製パン',
        fare_type: '日額',
        fare: '30,000',
        highway_fee: '1,000',
        highway_fee_holiday: '2,000',
        the_number_of_store: 1,
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
                mvalueizu: false,
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
                value: true,
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
                value: true,
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
        the_number_of_store_list: [
            {
                id: 1,
                text: 'ローソンA店',
            },
            {
                id: 1,
                text: 'ローソンB店',
            },
            {
                id: 1,
                text: 'ローソンC店',
            },
            {
                id: 1,
                text: 'ローソンD店',
            },
            {
                id: 1,
                text: 'ローソンE店',
            },
            {
                id: 1,
                text: 'ローソンF店',
            },
        ],
    }, {
        id: 1,
        department: '東京',
        route_name: 'AAAA',
        customer: '山崎製パン',
        fare_type: '日額',
        fare: '30,000',
        highway_fee: '1,000',
        highway_fee_holiday: '2,000',
        the_number_of_store: 1,
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
                mvalueizu: false,
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
                value: true,
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
                value: true,
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
        the_number_of_store_list: [
            {
                id: 1,
                text: 'ローソンA店',
            },
            {
                id: 1,
                text: 'ローソンB店',
            },
            {
                id: 1,
                text: 'ローソンC店',
            },
            {
                id: 1,
                text: 'ローソンD店',
            },
            {
                id: 1,
                text: 'ローソンE店',
            },
            {
                id: 1,
                text: 'ローソンF店',
            },
        ],
    }, {
        id: 1,
        department: '東京',
        route_name: 'AAAA',
        customer: '山崎製パン',
        fare_type: '日額',
        fare: '30,000',
        highway_fee: '1,000',
        highway_fee_holiday: '2,000',
        the_number_of_store: 1,
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
                mvalueizu: false,
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
                value: true,
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
                value: true,
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
        the_number_of_store_list: [
            {
                id: 1,
                text: 'ローソンA店',
            },
            {
                id: 1,
                text: 'ローソンB店',
            },
            {
                id: 1,
                text: 'ローソンC店',
            },
            {
                id: 1,
                text: 'ローソンD店',
            },
            {
                id: 1,
                text: 'ローソンE店',
            },
            {
                id: 1,
                text: 'ローソンF店',
            },
        ],
    }, {
        id: 1,
        department: '東京',
        route_name: 'AAAA',
        customer: '山崎製パン',
        fare_type: '日額',
        fare: '30,000',
        highway_fee: '1,000',
        highway_fee_holiday: '2,000',
        the_number_of_store: 1,
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
                mvalueizu: false,
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
                value: true,
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
                value: true,
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
        the_number_of_store_list: [
            {
                id: 1,
                text: 'ローソンA店',
            },
            {
                id: 1,
                text: 'ローソンB店',
            },
            {
                id: 1,
                text: 'ローソンC店',
            },
            {
                id: 1,
                text: 'ローソンD店',
            },
            {
                id: 1,
                text: 'ローソンE店',
            },
            {
                id: 1,
                text: 'ローソンF店',
            },
        ],
    },
];

export const ROUTE_MASTER = [
    {
        value: 1,
        text: 'AAAA',
    },
    {
        value: 2,
        text: 'BBBB',
    },
    {
        value: 3,
        text: 'CCCC',
    },
    {
        value: 4,
        text: 'DDDD',
    },
    {
        value: 5,
        text: 'EEEE',
    },
    {
        value: 6,
        text: 'FFFF',
    },
];
