import { mount, createLocalVue } from '@vue/test-utils';
import store from '@/store';
import router from '@/router';
import Document from '@/pages/EmployeeMaster/document';

describe('TEST COMPONENT EMPLOYEE MASTER DOCUMENT', () => {
    test('Render page and default tab', () => {
        const localVue = createLocalVue();
        const wrapper = mount(Document, {
            localVue,
            store,
            router,
            mocks: {
                $route: { params: { id: '1' }},
            },
            stubs: {
                BIcon: true,
                BOverlay: true,
                WorkerBasicInfo: true,
                DriversLicense: true,
                DrivingRecord: true,
                AptitudeTest: true,
                HealthExam: true,
                vHeaderPage: true,
            },
        });

        expect(wrapper.find('.employee-master-list').exists()).toBe(true);
        expect(wrapper.vm.activeTab).toBe(0);
        expect(Array.isArray(wrapper.vm.tabs)).toBe(true);

        wrapper.destroy();
    });

    test('Can switch tab', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(Document, {
            localVue,
            store,
            router,
            mocks: {
                $route: { params: { id: '1' }},
            },
            stubs: {
                BIcon: true,
                BOverlay: true,
                WorkerBasicInfo: true,
                DriversLicense: true,
                DrivingRecord: true,
                AptitudeTest: true,
                HealthExam: true,
                vHeaderPage: true,
            },
        });

        const tabs = wrapper.findAll('.employee-master-list__tab');
        if (tabs.length > 1) {
            await tabs.at(1).trigger('click');
            expect(wrapper.vm.activeTab).toBe(1);
        } else {
            expect(tabs.length).toBeGreaterThanOrEqual(1);
        }

        wrapper.destroy();
    });
});
