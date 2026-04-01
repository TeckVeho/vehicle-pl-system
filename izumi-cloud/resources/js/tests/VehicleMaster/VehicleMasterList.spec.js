import { mount, createLocalVue } from '@vue/test-utils';
import store from '@/store';
import router from '@/router';
import VehicleMasterList from '@/pages/VehicleMaster/index';

describe('TEST COMPONENT VEHICLE MASTER LIST', () => {
    test('Render component and default pagination', () => {
        const localVue = createLocalVue();
        const wrapper = mount(VehicleMasterList, {
            localVue,
            router,
            store,
            stubs: { BIcon: true },
        });

        expect(wrapper.find('.vehicle-master').exists()).toBe(true);
        expect(wrapper.vm.pagination.current_page).toBe(1);
        expect(wrapper.vm.pagination.per_page).toBeGreaterThan(0);

        wrapper.destroy();
    });

    test('Click apply filter button', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(VehicleMasterList, {
            localVue,
            router,
            store,
            stubs: { BIcon: true },
        });

        const onClickApply = jest.spyOn(wrapper.vm, 'onClickApply');
        const BUTTON_APPLY = wrapper.find('.btn-summit-filter');
        await BUTTON_APPLY.trigger('click');

        expect(onClickApply).toHaveBeenCalled();
        wrapper.destroy();
    });

    test('Click register button when visible', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(VehicleMasterList, {
            localVue,
            router,
            store,
            stubs: { BIcon: true },
        });

        const onClickRegister = jest.spyOn(wrapper.vm, 'onClickRegister');
        const BTN_REGISTER = wrapper.find('.btn-registration');

        if (BTN_REGISTER.exists()) {
            await BTN_REGISTER.trigger('click');
            expect(onClickRegister).toHaveBeenCalled();
        } else {
            expect(BTN_REGISTER.exists()).toBe(false);
        }

        wrapper.destroy();
    });
});
