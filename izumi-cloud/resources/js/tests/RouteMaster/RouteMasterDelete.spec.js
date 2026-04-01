import { mount, createLocalVue } from '@vue/test-utils';
import store from '@/store';
import router from '@/router';
import Index from '@/pages/RouteMaster/index';

describe('TEST COMPONENT ROUTE MASTER', () => {
    test('Test when click on the delete button, call function delete route', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(Index, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
            data() {
                return {
                    showModal: false,
                    urlAPI: {
                        apiRemoveRoute: '/route',
                    },
                    delete_id: 1,
                    vItems: [
                        {
                            id: 1,
                            department: '',
                            route_id: '',
                            route_name: '',
                            customer: '',
                            fare_type: 1,
                            fare: 0,
                            highway_fee: 0,
                            highway_fee_holiday: 0,
                            store_count: 0,
                            stores: [],
                            suspension_of_service: [],
                            schedule: [],
                            remark: '',
                        },
                    ],
                };
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

        const handleDelete = jest.spyOn(wrapper.vm, 'onClickDelete');

        await wrapper.vm.onClickDelete(1);

        expect(handleDelete).toHaveBeenCalledWith(1);

        handleDelete.mockRestore();

        wrapper.destroy();
    });
});
