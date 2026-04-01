import { mount, createLocalVue } from '@vue/test-utils';
import store from '@/store';
import router from '@/router';
import CourseMasterDetail from '@/pages/CourseMaster/detail';

describe('TEST COMPONENT COURSE MASTER DETAIL', () => {
    const mocks = {
        $toast: {
            show: jest.fn(),
            success: jest.fn(),
            warning: jest.fn(),
            danger: jest.fn(),
        },
    };

    it('Test render data', () => {
        const localVue = createLocalVue();
        const wrapper = mount(CourseMasterDetail, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
        });

        const IS_FORM = {
            base: null,
            course_id: '',
            course_address: '',
            start_date: '',
            end_date: '',
            course_flag: null,
            course_type: null,
            bin_type: null,
            delivery_type: null,
            quantity: '',
            route_start_time_hour: null,
            route_start_time_min: null,
            course_allowance: '',
            gate: null,
            wing: null,
            tonnage: null,
            shipper: null,
            delivery_store: null,
        };

        expect(wrapper.vm.isForm).toEqual(IS_FORM);

        const LIST_BASE = [{ value: null, text: 'COURSE_MASTER_CREATE_PLEASE_SELECT' }];
        const LIST_COURSE_TYPE = [{ value: null, text: 'COURSE_MASTER_CREATE_PLEASE_SELECT' }];
        const LIST_BIN_TYPE = [{ value: null, text: 'COURSE_MASTER_CREATE_PLEASE_SELECT' }];
        const LIST_DELIVERY_TYPE = [{ value: null, text: 'COURSE_MASTER_CREATE_PLEASE_SELECT' }];
        const LIST_ROUTE_START_TIME_HOUR = [{ value: null, text: 'COURSE_MASTER_CREATE_PLEASE_SELECT' }];
        const LIST_ROUTE_START_TIME_MIN = [{ value: null, text: 'COURSE_MASTER_CREATE_PLEASE_SELECT' }];
        const LIST_GATE = [{ value: null, text: 'COURSE_MASTER_CREATE_PLEASE_SELECT' }];
        const LIST_WING = [{ value: null, text: 'COURSE_MASTER_CREATE_PLEASE_SELECT' }];
        const LIST_TONNAGE = [{ value: null, text: 'COURSE_MASTER_CREATE_PLEASE_SELECT' }];
        const LIST_SHIPPER = [{ value: null, text: 'COURSE_MASTER_CREATE_PLEASE_SELECT' }];
        const LIST_DELIVERY_STORE = [{ value: null, text: 'COURSE_MASTER_CREATE_PLEASE_SELECT' }];

        expect(wrapper.vm.listBase).toEqual(LIST_BASE);
        expect(wrapper.vm.listCourseType).toEqual(LIST_COURSE_TYPE);
        expect(wrapper.vm.listFightType).toEqual(LIST_BIN_TYPE);
        expect(wrapper.vm.listDeliveryType).toEqual(LIST_DELIVERY_TYPE);
        expect(wrapper.vm.listRouteStartTimeHour).toEqual(LIST_ROUTE_START_TIME_HOUR);
        expect(wrapper.vm.listRouteStartTimeMin).toEqual(LIST_ROUTE_START_TIME_MIN);
        expect(wrapper.vm.listGate).toEqual(LIST_GATE);
        expect(wrapper.vm.listWing).toEqual(LIST_WING);
        expect(wrapper.vm.listTonnage).toEqual(LIST_TONNAGE);
        expect(wrapper.vm.listShipper).toEqual(LIST_SHIPPER);
        expect(wrapper.vm.listDeliveryStore).toEqual(LIST_DELIVERY_STORE);

        wrapper.destroy();
    });

    it('Test render header', () => {
        const localVue = createLocalVue();
        const wrapper = mount(CourseMasterDetail, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
        });

        store.dispatch('user/saveLogin', {
            USER: {
                id: '',
                uuid: '',
                name: '',
                email: '',
                supervisor_email: '',
                department_code: '',
                department: '',
                role: '',
                roles: ['dx_manager'],
                expToken: '',
            },
            TOKEN: 'Izumi_Cloud',
        }).then(() => {
            const HEADER = wrapper.find('.course-master-detail__header');
            expect(HEADER.exists()).toBe(true);
            expect(HEADER.text()).toEqual('ROUTER_COURSE_MASTER');
        });

        wrapper.destroy();
    });

    it('Test render form', () => {
        const localVue = createLocalVue();
        const wrapper = mount(CourseMasterDetail, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
        });

        store.dispatch('user/saveLogin', {
            USER: {
                id: '',
                uuid: '',
                name: '',
                email: '',
                supervisor_email: '',
                department_code: '',
                department: '',
                role: '',
                roles: ['dx_manager'],
                expToken: '',
            },
            TOKEN: 'Izumi_Cloud',
        }).then(() => {
            const ZONE_FORM = wrapper.find('.course-master-detail');
            expect(ZONE_FORM.exists()).toBe(true);

            const LIST_ITEM_FORM = ZONE_FORM.findAll('.item-form');
            expect(LIST_ITEM_FORM.length).toEqual(17);
        });

        wrapper.destroy();
    });

    test('Test init data', () => {
        const initData = jest.fn();

        const localVue = createLocalVue();
        const wrapper = mount(CourseMasterDetail, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
            methods: {
                initData,
            },
        });

        store.dispatch('user/saveLogin', {
            USER: {
                id: '',
                uuid: '',
                name: '',
                email: '',
                supervisor_email: '',
                department_code: '',
                department: '',
                role: '',
                roles: ['dx_manager'],
                expToken: '',
            },
            TOKEN: 'Izumi_Cloud',
        }).then(() => {
            expect(initData).toHaveBeenCalled();
        });

        wrapper.destroy();
    });

    test('Test get list department', () => {
        const handleGetListDepartment = jest.fn();

        const localVue = createLocalVue();
        const wrapper = mount(CourseMasterDetail, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
            methods: {
                handleGetListDepartment,
            },
        });

        store.dispatch('user/saveLogin', {
            USER: {
                id: '',
                uuid: '',
                name: '',
                email: '',
                supervisor_email: '',
                department_code: '',
                department: '',
                role: '',
                roles: ['dx_manager'],
                expToken: '',
            },
            TOKEN: 'Izumi_Cloud',
        }).then(() => {
            expect(handleGetListDepartment).toHaveBeenCalled();
        });

        wrapper.destroy();
    });

    it('Test function format route name', () => {
        const localVue = createLocalVue();
        const wrapper = mount(CourseMasterDetail, {
            localVue,
            router,
            store,
            mocks,
            stubs: {
                BIcon: true,
            },
        });

        const data = [
            {
                value: 1,
                text: 'Route 1',
                remark: 'Work 12:00',
            },
            {
                value: 2,
                text: 'Route 2',
                remark: null,
            },
        ];

        const result = [
            {
                value: 1,
                text: '1 - Route 1 - Work 12:00',
                remark: 'Work 12:00',
            },
            {
                value: 2,
                text: '2 - Route 2  ',
                remark: null,
            },
        ];

        expect(wrapper.vm.formatNameRoute(data)).toEqual(result);

        wrapper.destroy();
    });

    it('Test click button back', async() => {
        const onClickBack = jest.fn();

        const localVue = createLocalVue();
        const wrapper = mount(CourseMasterDetail, {
            localVue,
            router,
            store,
            mocks,
            stubs: {
                BIcon: true,
            },
            methods: {
                onClickBack,
            },
        });

        const ZONE = wrapper.find('.course-master-detail__handle');
        const BUTTON_BACK = ZONE.find('.text-left');
        const BUTTON = BUTTON_BACK.find('button');
        expect(BUTTON.exists()).toBe(true);

        await BUTTON.trigger('click');

        expect(onClickBack).toHaveBeenCalled();

        wrapper.destroy();
    });

    it('Test click button edit', async() => {
        const onClickSave = jest.fn();

        const localVue = createLocalVue();
        const wrapper = mount(CourseMasterDetail, {
            localVue,
            router,
            store,
            mocks,
            stubs: {
                BIcon: true,
            },
            methods: {
                onClickSave,
            },
        });

        store.dispatch('user/saveLogin', {
            USER: {
                id: '',
                uuid: '',
                name: '',
                email: '',
                supervisor_email: '',
                department_code: '',
                department: '',
                role: '',
                roles: ['dx_manager'],
                expToken: '',
            },
            TOKEN: 'Izumi_Cloud',
        }).then(async() => {
            const ZONE = wrapper.find('.course-master-detail__handle');
            const BUTTON_BACK = ZONE.find('.text-right');
            const BUTTON = BUTTON_BACK.find('button');

            await BUTTON.trigger('click');
        });

        wrapper.destroy();
    });
});
