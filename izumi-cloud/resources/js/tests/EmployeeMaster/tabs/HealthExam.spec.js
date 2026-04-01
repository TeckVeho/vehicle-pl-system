import { mount, createLocalVue } from '@vue/test-utils';
import store from '@/store';
import router from '@/router';
import HealthExam from '@/pages/EmployeeMaster/tabs/HealthExam.vue';

describe('TEST COMPONENT HEALTH EXAM', () => {
    test('Render and map empty data safely', () => {
        const localVue = createLocalVue();
        const wrapper = mount(HealthExam, {
            localVue,
            store,
            router,
            propsData: { data: { id: 1, health_examination_results: [] }},
            stubs: { BRow: true, BCol: true, BInput: true, BTable: true, ModalViewPDF: true, vTitle: true, vButton: true },
        });

        expect(wrapper.find('.worker-basic-info').exists()).toBe(true);
        expect(Array.isArray(wrapper.vm.items)).toBe(true);

        wrapper.destroy();
    });
});
