import { mount, createLocalVue } from '@vue/test-utils';
import store from '@/store';
import router from '@/router';
import ListEmployeeMaster from '@/pages/EmployeeMaster/index';
import FilterEmployeeMaster from '@/components/organisms/FilterEmployeeMaster';

describe('TEST COMPONENT LIST EMPLOYEE MASTER', () => {
    test('Check component render page', () => {
        const localVue = createLocalVue();
        const wrapper = mount(ListEmployeeMaster, {
            localVue,
            store,
            router,
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
            const PAGE = wrapper.find('.employee-master-list');
            expect(PAGE.exists()).toBe(true);
        });

        wrapper.destroy();
    });

    test('Check render title header', () => {
        const localVue = createLocalVue();
        const wrapper = mount(ListEmployeeMaster, {
            localVue,
            store,
            router,
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
            const TITLE_HEADER = wrapper.find('.employee-master-list__title-header');
            expect(TITLE_HEADER.text()).toEqual('PAGE_TITLE.EMPLOYEE_MASTER');
        });

        wrapper.destroy();
    });

    test('Check render button clear all', () => {
        const localVue = createLocalVue();
        const wrapper = mount(FilterEmployeeMaster, {
            localVue,
            store,
            router,
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
            const BUTTON = wrapper.find('.text-clear-all');
            expect(BUTTON.exists()).toBe(true);
            expect(BUTTON.text()).toEqual('BUTTON.CLEAR_ALL');
        });

        wrapper.destroy();
    });

    test('Check click button clear all', () => {
        const onClickClearFilter = jest.fn();

        const localVue = createLocalVue();
        const wrapper = mount(FilterEmployeeMaster, {
            localVue,
            store,
            router,
            methods: {
                onClickClearFilter,
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
            const BUTTON = wrapper.find('.text-clear-all');

            await BUTTON.trigger('click');

            expect(onClickClearFilter).toHaveBeenCalled();
        });

        wrapper.destroy();
    });

    test('Check render filter', () => {
        const localVue = createLocalVue();
        const wrapper = mount(FilterEmployeeMaster, {
            localVue,
            store,
            router,
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
            const LIST_ITEM = wrapper.findAll('.item-filter');
            expect(LIST_ITEM.length).toEqual(4);
        });

        wrapper.destroy();
    });

    test('Check button apply', () => {
        const localVue = createLocalVue();
        const wrapper = mount(FilterEmployeeMaster, {
            localVue,
            store,
            router,
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
            const BUTTON = wrapper.find('.zone-btn-apply');
            expect(BUTTON.exists()).toBe(true);
        });

        wrapper.destroy();
    });

    test('Check render table', () => {
        const localVue = createLocalVue();
        const wrapper = mount(ListEmployeeMaster, {
            localVue,
            store,
            router,
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
            const ZONE_TABLE = wrapper.find('.employee-master-list__table');
            expect(ZONE_TABLE.exists()).toBe(true);
        });

        wrapper.destroy();
    });

    test('Check sort table', () => {
        const localVue = createLocalVue();
        const wrapper = mount(ListEmployeeMaster, {
            localVue,
            store,
            router,
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
            const LIST_SORT = ['department_base', 'working_base', 'id', 'name', 'retirement_date'];

            const len = wrapper.vm.fields.length;
            let idx = 0;

            let result = 0;

            while (idx < len) {
                if (LIST_SORT.includes(wrapper.vm.fields[idx].key)) {
                    if (wrapper.vm.fields[idx].sortable) {
                        result = result + 1;
                    }
                }

                idx++;
            }

            expect(result).toEqual(5);
        });

        wrapper.destroy();
    });

    test('Check function format 2 digit', () => {
        const localVue = createLocalVue();
        const wrapper = mount(ListEmployeeMaster, {
            localVue,
            store,
            router,
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
            expect(wrapper.vm.format2Digit(1)).toEqual('01');
            expect(wrapper.vm.format2Digit(2)).toEqual('02');
            expect(wrapper.vm.format2Digit(9)).toEqual('09');
            expect(wrapper.vm.format2Digit(10)).toEqual('10');
            expect(wrapper.vm.format2Digit(null)).toEqual(null);
            expect(wrapper.vm.format2Digit(undefined)).toEqual(undefined);
        });

        wrapper.destroy();
    });
});
