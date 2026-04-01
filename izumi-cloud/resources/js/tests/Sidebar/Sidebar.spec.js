import { mount, createLocalVue } from '@vue/test-utils';
import store from '@/store';
import router from '@/router';
import Sidebar from '@/layout/components/Sidebar';
import MenuSidebar from '@/layout/components/Sidebar';

describe('TEST COMPONENT SIDEBAR', () => {
    test('Test component render Sidebar', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(Sidebar, {
            localVue,
            router,
            store,
        });

        const SidebarWrapper = wrapper.find('#sidebar-wrapper');
        expect(SidebarWrapper.exists()).toBe(true);

        const SidebarMenu = wrapper.find('.sidebar-menu');
        expect(SidebarMenu.exists()).toBe(true);

        wrapper.destroy();
    });

    test('Test component render route item in sidebar menu', async() => {
        const DATA = [
            {
                meta: {
                    title: 'ROUTER_DATA_LIST',
                    icon: 'fas fa-folder',
                },
                path: '/data-list',
            },
            {
                meta: {
                    title: 'ROUTER_DATA_CONNECT',
                    icon: 'fas fa-repeat-alt',
                },
                name: 'DataConnect',
                path: '/data-connect',
            },
            {
                meta: {
                    title: 'ROUTER_DATA_IMPORT',
                    icon: 'fas fa-arrow-to-bottom',
                },
                name: 'DataImport',
                path: '/data-import',
            },
            {
                meta: {
                    title: 'ROUTER_USER_MANAGEMENT',
                    icon: 'fas fa-cog',
                },
                name: 'UserManagement',
                path: '/user-management',
            },
        ];

        const localVue = createLocalVue();
        const wrapper = mount(MenuSidebar, {
            localVue,
            router,
            store,
            computed: {
                routes() {
                    return DATA;
                },
            },
        });

        const MenuItem = wrapper.find('.display-menu');
        expect(MenuItem.exists()).toBe(true);

        const ListItem = wrapper.findAll('li');
        expect(ListItem.length).toEqual(4);

        const ListIcon = wrapper.findAll('i');
        expect(ListIcon.length).toEqual(4);

        const ListTitle = wrapper.findAll('span');
        expect(ListTitle.length).toEqual(4);

        for (let itemIndex = 0; itemIndex < DATA.length; itemIndex++) {
            expect(ListItem.at(itemIndex).exists()).not.toBe(null);
            expect(ListIcon.at(itemIndex).classes()).not.toBe(null);
            expect(ListTitle.at(itemIndex).text()).toEqual(DATA[itemIndex].meta.title);
        }
    });

    test('Test routes must be an object', () => {
        const localVue = createLocalVue();
        const wrapper = mount(MenuSidebar, {
            localVue,
            router,
            store,
        });

        expect(router).toBeInstanceOf(Object);

        wrapper.destroy();
    });
});
