import { mount, createLocalVue } from '@vue/test-utils';
import store from '@/store';
import router from '@/router';
import DriverRecorderRegister from '@/pages/DriverRecorder/Create/index';

describe('TEST SCREEN DRIVER RECORDER REGISTER', () => {
    test('Add and delete upload row', () => {
        const localVue = createLocalVue();
        const wrapper = mount(DriverRecorderRegister, {
            localVue,
            router,
            store,
            stubs: { BIcon: true },
        });

        const initial = wrapper.vm.sampleUploadData.length;
        wrapper.vm.eventAddTableUploadFile();
        expect(wrapper.vm.sampleUploadData.length).toBe(initial + 1);

        wrapper.vm.eventDeleteTableUploadFile(0);
        expect(wrapper.vm.sampleUploadData.length).toBe(initial);

        wrapper.destroy();
    });

    test('Back button calls onClickBack', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(DriverRecorderRegister, {
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
