import { mount, createLocalVue } from '@vue/test-utils';
import store from '@/store';
import router from '@/router';
import DrivingRecord from '@/pages/EmployeeMaster/tabs/DrivingRecord.vue';

describe('TEST COMPONENT DRIVING RECORD', () => {
    test('Render and map empty data safely', () => {
        const localVue = createLocalVue();
        const wrapper = mount(DrivingRecord, {
            localVue,
            store,
            router,
            propsData: { data: { id: 1, driving_record_certificates: [] }},
            stubs: { BRow: true, BCol: true, BInput: true, BTable: true, ModalViewPDF: true, vTitle: true, vButton: true },
        });

        expect(wrapper.find('.worker-basic-info').exists()).toBe(true);
        expect(Array.isArray(wrapper.vm.items)).toBe(true);

        wrapper.destroy();
    });
});
