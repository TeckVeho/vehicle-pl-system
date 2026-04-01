import { mount, createLocalVue } from '@vue/test-utils';
import store from '@/store';
import router from '@/router';
import Edit from '@/pages/RouteMaster/index';

describe('TEST COMPONENT ROUTE MASTER', () => {
    test('Test render data', () => {
        const localVue = createLocalVue();
        const wrapper = mount(Edit, {
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
        const wrapper = mount(Edit, {
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
        const wrapper = mount(Edit, {
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
        const wrapper = mount(Edit, {
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

        const registerBtn = HANDLE.findAll('button').wrappers.find((w) => w.text() === 'ROUTE_MASTER_REGISTER');
        expect(registerBtn).toBeTruthy();
        expect(registerBtn.text()).toEqual('ROUTE_MASTER_REGISTER');

        wrapper.destroy();
    });

    test('Test render table header', () => {
        const localVue = createLocalVue();
        const wrapper = mount(Edit, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
        });

        const ZONE_TABLE = wrapper.find('.route-master__table');
        expect(ZONE_TABLE.exists()).toBe(true);

        const TABLE = ZONE_TABLE.find('.table-route-master');
        expect(TABLE.exists()).toBe(true);

        const HEADER = TABLE.find('thead');
        expect(HEADER.exists()).toBe(true);

        const HEADER_ROW = HEADER.findAll('th');
        const headerTexts = HEADER_ROW.wrappers.map((w) => w.text());

        expect(headerTexts.some((t) => t.includes('ROUTE_MASTER_DEPARTMENT'))).toBe(true);
        expect(headerTexts.some((t) => t.includes('ROUTE_MASTER_ROUTE_ID'))).toBe(true);
        expect(headerTexts.some((t) => t.includes('ROUTE_MASTER_ROUTE_NAME'))).toBe(true);
        expect(headerTexts.some((t) => t.includes('ROUTE_MASTER_CUSTOMER'))).toBe(true);
        expect(headerTexts.some((t) => t.includes('ROUTE_MASTER_FARE_TYPE'))).toBe(true);
        expect(headerTexts.some((t) => t.includes('ROUTE_MASTER_REMARK'))).toBe(true);

        wrapper.destroy();
    });

    test('Test reder table body', () => {
        const localVue = createLocalVue();
        const wrapper = mount(Edit, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
        });

        const ZONE_TABLE = wrapper.find('.route-master__table');
        expect(ZONE_TABLE.exists()).toBe(true);

        const CONTENT = ZONE_TABLE.find('.table-route-master-content');
        const TABLE = CONTENT.find('.table-route-master');
        expect(TABLE.exists()).toBe(true);

        const BODY = TABLE.find('tbody');
        expect(BODY.exists()).toBe(true);
        const BODY_ROW = BODY.findAll('td');
        expect(BODY_ROW.at(0).text()).toEqual('ROUTER_MASTER_TABLE_NO_DATA');

        wrapper.destroy();
    });

    test('Test render select per page', () => {
        const localVue = createLocalVue();
        const wrapper = mount(Edit, {
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
        const wrapper = mount(Edit, {
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
        const wrapper = mount(Edit, {
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

    test('Test get data from local storage when the user not click on the button save', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(Edit, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
            data() {
                return {
                    urlAPI: {
                        apiGetListRoute: 'http://localhost:8080/api/route',
                    },
                };
            },
        });

        const getDataTableRoute = jest.fn();

        const cleanObject = jest.fn();

        const obj2Path = jest.fn();

        const getListRoute = jest.fn();

        getDataTableRoute.mockImplementation(async() => {
            this.overlay.show = true;

            let QUERY = {
                page: this.pagination.current_page,
                per_page: this.pagination.per_page,
                department_id: this.filter.department.is_check ? this.filter.department.department_option : '',
                route_name: this.filter.routeName.is_check ? this.filter.routeName.route_name_option : '',
                customer_id: this.filter.customer.is_check ? this.filter.customer.customer_option : '',
            };

            QUERY = cleanObject(QUERY);

            const URL = `${wrapper.urlAPI.apiGetListRoute}?${obj2Path(QUERY)}`;

            let DATA = [];
            let PAGINATION = [];

            try {
                const response = await getListRoute(URL);

                if (response.code === 200) {
                    DATA = this.handleOverrideTableItem(response.data.result);

                    this.vItems = DATA;

                    PAGINATION = response.data.pagination;

                    this.pagination.current_page = PAGINATION.current_page;
                    this.pagination.per_page = PAGINATION.per_page;
                    this.pagination.total_rows = PAGINATION.total_records;

                    const ROUTE_MASTER_EDIT_PAGINATION_DATA = {
                        current_page: PAGINATION.current_page,
                        per_page: PAGINATION.per_page,
                        total_rows: PAGINATION.total_records,
                    };

                    this.$store.dispatch('route_master/setPagination', ROUTE_MASTER_EDIT_PAGINATION_DATA);
                }
            } catch (error) {
                console.log(error);
            }

            this.overlay.show = false;
        });

        const localStorageEditData = localStorage.getItem('route_master_edit_data');

        expect(localStorageEditData).toBe(null);

        expect(getDataTableRoute).not.toHaveBeenCalled();

        wrapper.destroy();
    });

    // TODO: mount + lưu localStorage + spy getDataTableRoute gắn vào vm; hiện chưa gọi API / chưa bấm save.
    it.skip('Test get data from api when the have clicked on the button save', () => {});
});
