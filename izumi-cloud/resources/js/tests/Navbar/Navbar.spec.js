import { mount, createLocalVue } from '@vue/test-utils';
import store from '@/store';
import router from '@/router';
import Navbar from '@/layout/components/Navbar';

describe('TEST COMPONENT NAVBAR', () => {
    test('Test component render Navbar', () => {
        const localVue = createLocalVue();
        const wrapper = mount(Navbar, {
            localVue,
            store,
            router,
        });

        const HeaderBar = wrapper.find('.nav-bar');
        expect(HeaderBar.exists()).toBe(true);

        wrapper.destroy();
    });

    test('Test component render UI', () => {
        const localVue = createLocalVue();
        const wrapper = mount(Navbar, {
            localVue,
            router,
            store,
        });

        const Logo = wrapper.find('.izumi-logo');
        expect(Logo.exists()).toBe(true);

        const ToggleIcon = wrapper.find('#toggle-menu');
        expect(ToggleIcon.exists()).toBe(true);

        const NavFasIcon = wrapper.find('.fas');
        expect(NavFasIcon.exists()).toBe(true);

        const ButtonLogout = wrapper.find('.btn-logout');
        expect(ButtonLogout.exists()).toBe(true);
        expect(ButtonLogout.text()).toBe('NAVBAR_LOGOUT');

        const ButtonShowEmpName = wrapper.find('.btn-show-emp-name');
        expect(ButtonShowEmpName.exists()).toBe(true);
        expect(ButtonShowEmpName.text()).not.toBe(null);

        const NavbarCollapseContent = wrapper.find('#nav-collapse');
        expect(NavbarCollapseContent.exists()).toBe(true);
    });

    test('Test function doLogout in Navbar', () => {
        const localVue = createLocalVue();
        const wrapper = mount(Navbar, {
            localVue,
            router,
            store,
        });

        const ButtonLogout = wrapper.find('.btn-logout');
        expect(ButtonLogout.exists()).toBe(true);
        expect(ButtonLogout.text()).toBe('NAVBAR_LOGOUT');

        const doLogout = jest.spyOn(wrapper.vm, 'doLogout');
        ButtonLogout.trigger('click');
        expect(doLogout).toHaveBeenCalled();

        wrapper.destroy();
    });
});
