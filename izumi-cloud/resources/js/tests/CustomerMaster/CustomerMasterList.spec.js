import { mount, createLocalVue } from '@vue/test-utils';
import store from '@/store';
import router from '@/router';
import CustomerMasterList from '@/pages/CustomerMaster/index';

describe('TEST COMPONENT CUSTOMER MASTER', () => {
    test('Test render data', () => {
        const localVue = createLocalVue();
        const wrapper = mount(CustomerMasterList, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
        });

        expect(wrapper.vm.items).toEqual([]);
        expect(wrapper.vm.selectedPerPage).toEqual(20);
        expect(JSON.stringify(wrapper.vm.pagination)).toEqual(
            JSON.stringify({
                vCurrentPage: 1,
                vPerPage: 20,
                vTotalRows: 0,
            }));
        expect(wrapper.vm.showModal).toBe(false);
        expect(wrapper.vm.idHandle).toEqual(null);

        wrapper.destroy();
    });

    test('Test render header', () => {
        const localVue = createLocalVue();
        const wrapper = mount(CustomerMasterList, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
        });

        const PAGE = wrapper.find('.customer-master');
        const HEADER = PAGE.find('.customer-master__header');
        expect(HEADER.text()).toEqual('ROUTER_CUSTOMER_MASTER');

        wrapper.destroy();
    });

    test('Test render button register', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(CustomerMasterList, {
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

        const PAGE = wrapper.find('.customer-master');
        const HANDLE = PAGE.find('.customer-master__handle');

        const BUTTON = HANDLE.find('.btn-registration');

        expect(BUTTON.exists()).toBe(true);

        wrapper.destroy();
    });

    test('Test render table header', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(CustomerMasterList, {
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

        const PAGE = wrapper.find('.customer-master');
        const ZONE_TABLE = PAGE.find('.customer-master__table');

        const TABLE = ZONE_TABLE.find('#table-customer-master');
        expect(TABLE.exists()).toBe(true);

        const LIST_TH = TABLE.findAll('th');
        expect(LIST_TH.length).toEqual(3);

        for (let th = 0; th < LIST_TH.length; th++) {
            expect(LIST_TH.at(th).text()).toEqual(wrapper.vm.fields[th].label);
        }

        wrapper.destroy();
    });

    test('Test render table body', () => {
        const FAKE_DATA = [
            {
                'id': 1,
                'customer_name': '1',
                'deleted_at': null,
                'created_at': 1657097343,
                'updated_at': 1657097343,
            },
            {
                'id': 2,
                'customer_name': '2',
                'deleted_at': null,
                'created_at': 1657097343,
                'updated_at': 1657097343,
            },
        ];

        const localVue = createLocalVue();
        const wrapper = mount(CustomerMasterList, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
            data() {
                return {
                    items: FAKE_DATA,
                };
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
            const PAGE = wrapper.find('.customer-master');
            const ZONE_TABLE = PAGE.find('.customer-master__table');

            const TABLE = ZONE_TABLE.find('#table-customer-master');
            expect(TABLE.exists()).toBe(true);

            const LIST_TR = TABLE.findAll('tr');

            expect(LIST_TR.length).toEqual(3);
        });

        wrapper.destroy();
    });

    test('Test render pagination', () => {
        const FAKE_DATA = [];

        for (let i = 0; i < 30; i++) {
            FAKE_DATA.push({
                id: i + 1,
                customer_name: i + 1 + '',
                deleted_at: null,
                created_at: 1657097343,
                updated_at: 1657097343,
            });
        }

        const localVue = createLocalVue();
        const wrapper = mount(CustomerMasterList, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
            data() {
                return {
                    items: FAKE_DATA,
                    pagination: {
                        vCurrentPage: 1,
                        vPerPage: 20,
                        vTotalRows: FAKE_DATA.length,
                    },
                };
            },
        });

        const PAGINATION = wrapper.find('.customer-master__pagination');
        expect(PAGINATION.exists()).toBe(true);

        wrapper.destroy();
    });

    test('Test modal delete', async() => {
        const FAKE_DATA = [
            {
                'id': 1,
                'customer_name': '1',
                'deleted_at': null,
                'created_at': 1657097343,
                'updated_at': 1657097343,
            },
            {
                'id': 2,
                'customer_name': '2',
                'deleted_at': null,
                'created_at': 1657097343,
                'updated_at': 1657097343,
            },
        ];

        const localVue = createLocalVue();
        const wrapper = mount(CustomerMasterList, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
            data() {
                return {
                    items: FAKE_DATA,
                };
            },
        });

        await wrapper.find('.fa-trash').trigger('click');

        expect(wrapper.find('#modal-cf').exists()).toBe(true);

        wrapper.destroy();
    });
});
