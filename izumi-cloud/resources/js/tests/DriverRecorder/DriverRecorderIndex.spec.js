import { mount, createLocalVue } from '@vue/test-utils';
import store from '@/store';
import router from '@/router';
import DriverRecorderList from '@/pages/DriverRecorder/List/index';

describe('TEST SCREEN DRIVER RECORDER LIST', () => {
    test('Render page and default pagination', () => {
        const localVue = createLocalVue();
        const wrapper = mount(DriverRecorderList, {
            localVue,
            router,
            store,
            stubs: { BIcon: true },
        });

        expect(wrapper.find('.driver-recorder-list').exists()).toBe(true);
        expect(wrapper.vm.pagination.current_page).toBe(1);
        expect(wrapper.vm.pagination.per_page).toBe(20);

        wrapper.destroy();
    });

    test('Click upload button calls handler', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(DriverRecorderList, {
            localVue,
            router,
            store,
            stubs: { BIcon: true },
        });

        const onClickUpload = jest.spyOn(wrapper.vm, 'onClickUpload');
        wrapper.vm.onClickUpload();

        expect(onClickUpload).toHaveBeenCalled();
        wrapper.destroy();
    });
});
