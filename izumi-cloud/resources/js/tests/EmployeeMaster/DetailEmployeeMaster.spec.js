import store from '@/store';
import router from '@/router';
import { mount, createLocalVue } from '@vue/test-utils';
import Detail from '@/pages/EmployeeMaster/detail';

const DX_MANAGER_USER = {
    USER: {
        id: '',
        uuid: '',
        name: '',
        email: '',
        supervisor_email: '',
        department_code: '',
        department: '',
        role: '',
        roles: ['dx_manager'],
        expToken: '',
    },
    TOKEN: 'Izumi_Cloud',
};

async function saveLoginDxManager() {
    await store.dispatch('user/saveLogin', DX_MANAGER_USER);
}

describe('TEST COMPONENT DETAIL EMPLOYEE MASTER', () => {
    test('Check component render page', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(Detail, {
            localVue,
            store,
            router,
        });

        await saveLoginDxManager();
        await wrapper.vm.$nextTick();

        const PAGE = wrapper.find('.employee-master-detail');
        expect(PAGE.exists()).toBe(true);

        wrapper.destroy();
    });

    test('Check component render title page', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(Detail, {
            localVue,
            store,
            router,
        });

        await saveLoginDxManager();
        await wrapper.vm.$nextTick();

        const PAGE = wrapper.find('.employee-master-detail__title-header');
        expect(PAGE.text()).toEqual('PAGE_TITLE.EMPLOYEE_MASTER');

        wrapper.destroy();
    });

    test('Check component render form', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(Detail, {
            localVue,
            store,
            router,
        });

        await saveLoginDxManager();
        await wrapper.vm.$nextTick();

        const LIST_ITEM_FORM = wrapper.findAll('.item-data');
        expect(LIST_ITEM_FORM.length).toBe(37);

        wrapper.destroy();
    });

    test('Check component render text change history', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(Detail, {
            localVue,
            store,
            router,
        });

        await saveLoginDxManager();
        await wrapper.vm.$nextTick();

        const CHANGE_HISTORY = wrapper.find('.text-link');

        expect(CHANGE_HISTORY.exists()).toBe(true);
        expect(CHANGE_HISTORY.text()).toBe('EMPLOYEE_MASTER_DETAIL_LABEL_CHANGING_HISTORY');

        wrapper.destroy();
    });

    test('Check click change history', async() => {
        const onClickChangeHistory = jest.fn();

        const localVue = createLocalVue();
        const wrapper = mount(Detail, {
            localVue,
            store,
            router,
            methods: {
                onClickChangeHistory,
            },
        });

        await saveLoginDxManager();
        await wrapper.vm.$nextTick();

        const CHANGE_HISTORY = wrapper.find('.text-link');

        await CHANGE_HISTORY.trigger('click');

        expect(onClickChangeHistory).toHaveBeenCalled();

        wrapper.destroy();
    });

    test('Check component render table change history', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(Detail, {
            localVue,
            store,
            router,
        });

        await saveLoginDxManager();
        await wrapper.vm.$nextTick();

        const MODAL = wrapper.find('#modal-change-history');
        expect(MODAL.exists()).toBe(true);

        const TABLE_HISTORY = MODAL.find('table');
        expect(TABLE_HISTORY.exists()).toBe(true);

        wrapper.destroy();
    });

    test('Check component render modal other base', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(Detail, {
            localVue,
            store,
            router,
        });

        await saveLoginDxManager();
        await wrapper.vm.$nextTick();

        const MODAL = wrapper.find('#modal-other-base');
        expect(MODAL.exists()).toBe(true);

        const MODAL_CONTENT = MODAL.find('.other-base-content');
        expect(MODAL_CONTENT.exists()).toBe(true);

        const BUTTON = MODAL.find('.button-to-edit-screen');
        expect(BUTTON.exists()).toBe(true);

        wrapper.destroy();
    });

    test('Check render modal detail affilication support base', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(Detail, {
            localVue,
            store,
            router,
        });

        await saveLoginDxManager();
        await wrapper.vm.$nextTick();

        const MODAL = wrapper.find('#modal-affiliation-support-base-detail');
        expect(MODAL.exists()).toBe(true);

        const MODAL_CONTENT = MODAL.find('.affiliation-support-base-detail-content');
        expect(MODAL_CONTENT.exists()).toBe(true);

        const BUTTON = MODAL.find('.button-to-edit-screen');
        expect(BUTTON.exists()).toBe(true);

        wrapper.destroy();
    });

    test('Check render modal edit affilication support base', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(Detail, {
            localVue,
            store,
            router,
        });

        await saveLoginDxManager();
        await wrapper.vm.$nextTick();

        const MODAL = wrapper.find('#modal-affiliation-support-base-edit');
        expect(MODAL.exists()).toBe(true);

        const MODAL_CONTENT = MODAL.find('.affiliation-support-base-edit-content');
        expect(MODAL_CONTENT.exists()).toBe(true);

        const BUTTON_RETURN = MODAL.find('.button-return');
        expect(BUTTON_RETURN.exists()).toBe(true);

        const BUTTON_SAVE = MODAL.find('.button-save');
        expect(BUTTON_SAVE.exists()).toBe(true);

        wrapper.destroy();
    });
});
