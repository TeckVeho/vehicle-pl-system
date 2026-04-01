import { mount, createLocalVue } from '@vue/test-utils';
import store from '@/store';
import router from '@/router';
import VehicleMasterEdit from '@/pages/VehicleMaster/edit';

describe('TEST COMPONENT VEHICLE MASTER EDIT', () => {
    test('Created hook initializes page', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(VehicleMasterEdit, {
            localVue,
            router,
            store,
            stubs: { BIcon: true },
        });

        const onCreatedComponent = jest.spyOn(wrapper.vm, 'onCreatedComponent');
        await wrapper.vm.onCreatedComponent();
        expect(onCreatedComponent).toHaveBeenCalled();

        wrapper.destroy();
    });

    test('Click back and save buttons', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(VehicleMasterEdit, {
            localVue,
            router,
            store,
            stubs: { BIcon: true },
        });

        const onClickBackButton = jest.spyOn(wrapper.vm, 'onClickBackButton');
        const onClickSaveButton = jest.spyOn(wrapper.vm, 'onClickSaveButton');

        await wrapper.find('.btn-back-main').trigger('click');
        await wrapper.find('.btn-save-main').trigger('click');

        expect(onClickBackButton).toHaveBeenCalled();
        expect(onClickSaveButton).toHaveBeenCalled();

        wrapper.destroy();
    });
});
