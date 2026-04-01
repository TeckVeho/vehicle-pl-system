import { mount, createLocalVue } from '@vue/test-utils';
import store from '@/store';
import router from '@/router';
import DriverRecorderDetail from '@/pages/DriverRecorder/Detail/index';

describe('TEST SCREEN DRIVER RECORDER DETAIL', () => {
    test('Render detail page', () => {
        const localVue = createLocalVue();
        const wrapper = mount(DriverRecorderDetail, {
            localVue,
            router,
            store,
            stubs: { BIcon: true },
        });

        expect(wrapper.find('.driver-recorder-detail').exists()).toBe(true);
        wrapper.destroy();
    });

    test('Back button calls handler', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(DriverRecorderDetail, {
            localVue,
            router,
            store,
            stubs: { BIcon: true },
        });

        const onClickBack = jest.spyOn(wrapper.vm, 'onClickBack');
        const btnBack = wrapper.findAll('.btn-registration').at(0);
        await btnBack.trigger('click');

        expect(onClickBack).toHaveBeenCalled();
        wrapper.destroy();
    });
});
