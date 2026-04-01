import { mount, createLocalVue } from '@vue/test-utils';
import store from '@/store';
import router from '@/router';
import StoreMasterEdit from '@/pages/StoreMaster/edit';

describe('TEST COMPONENT Store MASTER EDIT', () => {
    test('Test render data', () => {
        const localVue = createLocalVue();
        const wrapper = mount(StoreMasterEdit, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
        });

        expect(wrapper.vm.store_name).toEqual('');

        wrapper.destroy();
    });

    test('Test render header', () => {
        const localVue = createLocalVue();
        const wrapper = mount(StoreMasterEdit, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
        });

        const PAGE = wrapper.find('.store-master-edit');
        const HEADER = PAGE.find('.store-master-edit__header');
        expect(HEADER.exists()).toBe(true);
        expect(HEADER.text()).toEqual('ROUTER_STORE_MASTER');

        wrapper.destroy();
    });

    test('Test render form', () => {
        const localVue = createLocalVue();
        const wrapper = mount(StoreMasterEdit, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
        });

        const PAGE = wrapper.find('.store-master-edit');
        const LABEL_STORE_NAME = PAGE.find('label[for="input-store-name"]');
        expect(LABEL_STORE_NAME.exists()).toBe(true);
        expect(LABEL_STORE_NAME.text()).toEqual('STORE_MASTER_TABLE_LABLE_STORE_NAME');
        const INPUT_STORE_NAME = PAGE.find('#input-store-name');
        expect(INPUT_STORE_NAME.exists()).toBe(true);
        expect(INPUT_STORE_NAME.element.value).toEqual('');

        wrapper.destroy();
    });

    test('Test render handle', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(StoreMasterEdit, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
        });

        const PAGE = wrapper.find('.store-master-edit');
        const BODY = PAGE.find('.store-master-edit__body');

        expect(PAGE).toBeTruthy();
        expect(BODY).toBeTruthy();

        // const BUTTON_BACK = BODY.find('button.v-button-default');
        // expect(BUTTON_BACK.exists()).toBe(true);
        // expect(BUTTON_BACK.text()).toEqual('BUTTON.BACK');
        // const onClickBack = jest.spyOn(wrapper.vm, 'onClickBack');
        // await BUTTON_BACK.trigger('click');
        // expect(onClickBack).toHaveBeenCalled();

        // const BUTTON_REGISTER = BODY.find('button.btn-registration');
        // expect(BUTTON_REGISTER.exists()).toBe(true);
        // expect(BUTTON_REGISTER.text()).toEqual('BUTTON.SAVE');
        // const onClickSave = jest.spyOn(wrapper.vm, 'onClickSave');
        // await BUTTON_REGISTER.trigger('click');
        // expect(onClickSave).toHaveBeenCalled();

        wrapper.destroy();
    });

    test('Test function validate', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(StoreMasterEdit, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
        });

        const validateData = jest.spyOn(wrapper.vm, 'validateData');

        await wrapper.setData({ store_name: '' });
        expect(validateData()).toBe(false);
        await wrapper.setData({ store_name: 'Vin Mart' });
        expect(validateData()).toBe(true);
        await wrapper.setData({ store_name: 'Vin Mart Vin Mart Vin Mart Vin Mart Vin Mart' });
        expect(validateData()).toBe(false);

        wrapper.destroy();
    });

    test('Test event open page', () => {
        const handleGetOneStore = jest.fn();

        const localVue = createLocalVue();
        const wrapper = mount(StoreMasterEdit, {
            localVue,
            router,
            store,
            methods: {
                handleGetOneStore,
            },
            stubs: {
                BIcon: true,
            },
        });

        expect(handleGetOneStore).toHaveBeenCalled();

        wrapper.destroy();
    });
});
