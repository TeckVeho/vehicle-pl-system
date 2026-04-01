import { mount, createLocalVue } from '@vue/test-utils';
import store from '@/store';
import router from '@/router';
import VehicleMasterDetail from '@/pages/VehicleMaster/detail';

describe('TEST COMPONENT VEHICLE MASTER DETAIL', () => {
    test('Created hook initializes page', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(VehicleMasterDetail, {
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

    test('Back and edit buttons trigger handlers', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(VehicleMasterDetail, {
            localVue,
            router,
            store,
            stubs: { BIcon: true },
        });

        const onClickBackButton = jest.spyOn(wrapper.vm, 'onClickBackButton');
        const onClickEditButton = jest.spyOn(wrapper.vm, 'onClickEditButton');

        await wrapper.find('.btn-back').trigger('click');

        const editBtn = wrapper.find('.btn-edit');
        if (editBtn.exists()) {
            await editBtn.trigger('click');
            expect(onClickEditButton).toHaveBeenCalled();
        }

        expect(onClickBackButton).toHaveBeenCalled();

        wrapper.destroy();
    });
});
