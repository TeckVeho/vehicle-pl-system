import { mount, createLocalVue } from '@vue/test-utils';
import store from '@/store';
import router from '@/router';
import AptitudeTest from '@/pages/EmployeeMaster/tabs/AptitudeTest.vue';

describe('TEST COMPONENT APTITUDE TEST', () => {
    test('Render and map empty data safely', () => {
        const localVue = createLocalVue();
        const wrapper = mount(AptitudeTest, {
            localVue,
            store,
            router,
            propsData: { data: { id: 1, aptitude_assessment_forms: [] }},
            stubs: { BRow: true, BCol: true, BFormSelect: true, BInput: true, BTable: true, ModalViewPDF: true, vTitle: true, vButton: true },
        });

        expect(wrapper.find('.worker-basic-info').exists()).toBe(true);
        expect(wrapper.vm).toBeTruthy();

        wrapper.destroy();
    });
});
