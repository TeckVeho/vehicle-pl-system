import { mount, createLocalVue } from '@vue/test-utils';
import store from '@/store';
import router from '@/router';
import List from '@/pages/RouteMaster/index';

describe('TEST COMPONENT ROUTE MASTER', () => {
    test('Test render data', () => {
        const localVue = createLocalVue();
        const wrapper = mount(List, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
        });

        const FILTER = {
            department: {
                is_check: false,
                department_option: null,
            },
            routeName: {
                is_check: false,
                route_name_option: null,
            },
            customer: {
                is_check: false,
                customer_option: null,
            },
        };
        expect(wrapper.vm.filter).toEqual(FILTER);

        const PAGINATION_OPTIONS = [
            { value: 10, text: '10' },
            { value: 20, text: '20' },
            { value: 50, text: '50' },
            { value: 100, text: '100' },
            { value: 250, text: '250' },
            { value: 500, text: '500' },
        ];
        expect(wrapper.vm.pagination_options).toEqual(PAGINATION_OPTIONS);

        const PAGINATION = {
            current_page: 1,
            per_page: 10,
            total_rows: 0,
        };
        expect(wrapper.vm.pagination).toEqual(PAGINATION);

        const items = [];
        expect(wrapper.vm.vItems).toEqual(items);

        const NUMBER_DATE = 31;
        expect(wrapper.vm.numberDate).toEqual(NUMBER_DATE);

        const IS_SHOW_FULL_DATE = false;
        expect(wrapper.vm.isShowFullDate).toEqual(IS_SHOW_FULL_DATE);

        const SHOW_MODAL = false;
        expect(wrapper.vm.showModal).toEqual(SHOW_MODAL);

        const SORT_TABLE = {
            sortBy: '',
            sortType: null,
        };
        expect(wrapper.vm.sortTable).toEqual(SORT_TABLE);

        wrapper.destroy();
    });

    test('Test render header', () => {
        const localVue = createLocalVue();
        const wrapper = mount(List, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
        });

        const HEADER = wrapper.find('.route-master__header');
        expect(HEADER.exists()).toBe(true);
        expect(HEADER.text()).toEqual('ROUTER_ROUTE_MASTER');

        wrapper.destroy();
    });

    test('Test render filter', async() => {
        const doApply = jest.fn();

        const localVue = createLocalVue();
        const wrapper = mount(List, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
            methods: {
                doApply,
            },
        });

        const FILTER = wrapper.find('.route-master__filter');
        expect(FILTER.exists()).toBe(true);

        const BUTTON_CLEAR = FILTER.find('.text-clear-all');
        expect(BUTTON_CLEAR.exists()).toBe(true);
        expect(BUTTON_CLEAR.text()).toEqual('CLEAR_ALL');

        const FILTER_DEPARTMENT = FILTER.find('#filter-by-department');
        expect(FILTER_DEPARTMENT.exists()).toBe(true);

        const FILTER_ROUTE_NAME = FILTER.find('#filter-by-route-name');
        expect(FILTER_ROUTE_NAME.exists()).toBe(true);

        const FILTER_CUSTOMER = FILTER.find('#filter-by-customer');
        expect(FILTER_CUSTOMER.exists()).toBe(true);

        const BUTTON_APPLY_FILTER = FILTER.find('.apply-filter-button');
        expect(BUTTON_APPLY_FILTER.exists()).toBe(true);
        expect(BUTTON_APPLY_FILTER.text()).toEqual('APPLY');
        await BUTTON_APPLY_FILTER.trigger('click');
        expect(doApply).toHaveBeenCalled();

        wrapper.destroy();
    });

    test('Test render handle', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(List, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
        });

        await store.dispatch('user/saveLogin', {
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
        });
        await wrapper.vm.$nextTick();

        const HANDLE = wrapper.find('.route-master__functional-button-header');
        expect(HANDLE.exists()).toBe(true);

        const BUTTON_REGISTER = HANDLE.find('.button-register');
        expect(BUTTON_REGISTER.exists()).toBe(true);
        expect(BUTTON_REGISTER.text()).toEqual('ROUTE_MASTER_REGISTER');

        const BUTTON_MULTI_EDIT = HANDLE.find('.button-multi-editing');
        expect(BUTTON_MULTI_EDIT.exists()).toBe(false);

        wrapper.destroy();
    });

    test('Test render table header', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(List, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
        });

        await store.dispatch('user/saveLogin', {
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
        });
        await wrapper.vm.$nextTick();

        const ZONE_TABLE = wrapper.find('.route-master__table');
        expect(ZONE_TABLE.exists()).toBe(true);

        const TABLE = ZONE_TABLE.find('.table-route-master-content .table-route-master');
        expect(TABLE.exists()).toBe(true);

        const HEADER = TABLE.find('thead');
        expect(HEADER.exists()).toBe(true);

        const HEADER_ROW = HEADER.findAll('th');

        expect(HEADER_ROW.at(0).text()).toEqual('ROUTE_MASTER_DEPARTMENT');
        expect(HEADER_ROW.at(1).text()).toEqual('ROUTE_MASTER_ROUTE_ID');
        expect(HEADER_ROW.at(2).text()).toEqual('ROUTE_MASTER_ROUTE_NAME');
        expect(HEADER_ROW.at(3).text()).toEqual('ROUTE_MASTER_CUSTOMER');
        expect(HEADER_ROW.at(4).text()).toEqual('ROUTE_MASTER_FARE_TYPE');
        expect(HEADER_ROW.at(5).text()).toEqual('ROUTE_MASTER_FARE');
        expect(HEADER_ROW.at(6).text()).toEqual('ROUTE_MASTER_HIGHWAY_FEE');
        expect(HEADER_ROW.at(7).text()).toEqual('ROUTE_MASTER_HIGHWAY_FEE_HOLIDAY');
        expect(HEADER_ROW.at(8).text()).toEqual('ROUTE_MASTER_THE_NUMBER_OF_STORE');
        expect(HEADER_ROW.at(9).text()).toEqual('ROUTE_MASTER_SUSPENSION_OF_SERVICE');
        expect(HEADER_ROW.at(10).text()).toEqual('');
        expect(HEADER_ROW.at(11).text()).toEqual('ROUTE_MASTER_REMARK');
        expect(HEADER_ROW.at(12).text()).toEqual('ROUTE_MASTER_BUTTON_EDIT');
        expect(HEADER_ROW.at(13).text()).toEqual('ROUTE_MASTER_BUTTON_DELETE');

        wrapper.destroy();
    });

    test('Test reder table body', () => {
        const localVue = createLocalVue();
        const wrapper = mount(List, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
        });

        const ZONE_TABLE = wrapper.find('.route-master__table');
        expect(ZONE_TABLE.exists()).toBe(true);

        const TABLE = ZONE_TABLE.find('.table-route-master-content .table-route-master');
        expect(TABLE.exists()).toBe(true);

        const BODY = TABLE.find('tbody');
        expect(BODY.exists()).toBe(true);
        const BODY_ROW = BODY.findAll('td');
        expect(BODY_ROW.at(0).text()).toEqual('ROUTER_MASTER_TABLE_NO_DATA');

        wrapper.destroy();
    });

    test('Test render select per page', () => {
        const localVue = createLocalVue();
        const wrapper = mount(List, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
            data() {
                return {
                    pagination: {
                        current_page: 1,
                        per_page: 10,
                        total_rows: 100,
                    },
                };
            },
        });

        const SELECT_PER_PAGE = wrapper.find('.route-master__custom-per-page');
        expect(SELECT_PER_PAGE.exists()).toBe(true);

        wrapper.destroy();
    });

    test('Test render pagination', () => {
        const localVue = createLocalVue();
        const wrapper = mount(List, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
            data() {
                return {
                    pagination: {
                        current_page: 1,
                        per_page: 10,
                        total_rows: 100,
                    },
                };
            },
        });

        const PAGINATION = wrapper.find('.route-master__pagination');
        expect(PAGINATION.exists()).toBe(true);

        wrapper.destroy();
    });

    test('Test render show modal', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(List, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
            data() {
                return {
                    showModal: true,
                };
            },
        });

        const MODAL = wrapper.find('#modal-cf');
        expect(MODAL.exists()).toBe(true);

        const BUTTON_CANCEL = MODAL.find('.btn-cancel');
        expect(BUTTON_CANCEL.exists()).toBe(true);

        const BUTTON_APPLY = MODAL.find('.btn-apply');
        expect(BUTTON_APPLY.exists()).toBe(true);

        wrapper.destroy();
    });
});
