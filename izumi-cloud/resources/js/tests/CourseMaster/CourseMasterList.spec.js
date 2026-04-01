import { mount, createLocalVue } from '@vue/test-utils';
import store from '@/store';
import router from '@/router';
import CourseMasterList from '@/pages/CourseMaster/index';

describe('TEST COMPONENT COURSE MASTER LIST', () => {
    test('Render page and default data', () => {
        const localVue = createLocalVue();
        const wrapper = mount(CourseMasterList, {
            localVue,
            router,
            store,
            stubs: { BIcon: true },
        });

        expect(wrapper.find('.course-master').exists()).toBe(true);
        expect(wrapper.vm.items).toEqual([]);
        expect(wrapper.vm.pagination.vCurrentPage).toBe(1);
        expect(wrapper.vm.pagination.vPerPage).toBe(20);

        wrapper.destroy();
    });

    test('Buttons/filters are rendered', () => {
        const localVue = createLocalVue();
        const wrapper = mount(CourseMasterList, {
            localVue,
            router,
            store,
            stubs: { BIcon: true },
        });

        expect(wrapper.find('.text-clear-all').exists()).toBe(true);
        expect(wrapper.find('.zone-btn-apply').exists()).toBe(true);

        wrapper.destroy();
    });
});
