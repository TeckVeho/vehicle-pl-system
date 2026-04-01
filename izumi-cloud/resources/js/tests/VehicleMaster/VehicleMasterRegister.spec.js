import { mount, createLocalVue } from '@vue/test-utils';
import store from '@/store';
import router from '@/router';
import VehicleMasterRegister from '@/pages/VehicleMaster/create';

describe('TEST COMPONENT VEHICLE MASTER REGISTER', () => {
    test('Call function onClickRegisterButton and function handleValidateData when click on register button', async() => {
        const onClickRegisterButton = jest.fn();
        const handleValidateData = jest.fn();

        const localVue = createLocalVue();
        const wrapper = mount(VehicleMasterRegister, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
            methods: {
                onClickRegisterButton,
                handleValidateData,
            },
        });

        const BTN_REGISTER = wrapper.find('.btn-save-main');

        await BTN_REGISTER.trigger('click');

        expect(onClickRegisterButton).toHaveBeenCalled();

        await wrapper.vm.handleValidateData();

        expect(handleValidateData).toHaveBeenCalled();

        wrapper.destroy();
    });

    test('Call function onClickBackButton when click on back button', async() => {
        const onClickBackButton = jest.fn();

        const localVue = createLocalVue();
        const wrapper = mount(VehicleMasterRegister, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
            methods: {
                onClickBackButton,
            },
        });

        const BTN_BACK = wrapper.find('.btn-back-main');

        await BTN_BACK.trigger('click');

        expect(onClickBackButton).toHaveBeenCalled();

        wrapper.destroy();
    });
});
