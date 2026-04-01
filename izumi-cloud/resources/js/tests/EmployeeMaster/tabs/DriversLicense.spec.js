import { mount, createLocalVue } from '@vue/test-utils';
import store from '@/store';
import router from '@/router';
import DriversLicense from '@/pages/EmployeeMaster/tabs/DriversLicense.vue';

describe('TEST COMPONENT DRIVERS LICENSE', () => {
    test('Render and map empty data safely', () => {
        const localVue = createLocalVue();
        const wrapper = mount(DriversLicense, {
            localVue,
            store,
            router,
            propsData: {
                data: {
                    id: 1,
                    driver_licenses: [{
                        employee_driver_licenses_history: [],
                        surface_file: null,
                        back_file: null,
                        created_at: '',
                    }],
                },
            },
            stubs: { BRow: true, BCol: true, BInput: true, BTable: true, ModalViewPDF: true, vTitle: true, vButton: true },
        });

        expect(wrapper.find('.worker-basic-info').exists()).toBe(true);
        expect(Array.isArray(wrapper.vm.items)).toBe(true);

        wrapper.destroy();
    });
});
