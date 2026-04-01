import { mount, createLocalVue } from '@vue/test-utils';
import store from '@/store';
import router from '@/router';
import CustomerMasterEdit from '@/pages/CustomerMaster/edit';

describe('TEST COMPONENT CUSTOMER MASTER EDIT', () => {
    test('Test render data', () => {
        const localVue = createLocalVue();
        const wrapper = mount(CustomerMasterEdit, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
        });

        expect(wrapper.vm.customerName).toEqual('');

        wrapper.destroy();
    });

    test('Test render header', () => {
        const localVue = createLocalVue();
        const wrapper = mount(CustomerMasterEdit, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
        });

        const PAGE = wrapper.find('.customer-master-edit');
        const HEADER = PAGE.find('.customer-master-edit__header');
        expect(HEADER.exists()).toBe(true);
        expect(HEADER.text()).toEqual('ROUTER_CUSTOMER_MASTER');

        wrapper.destroy();
    });

    test('Test render form', () => {
        const localVue = createLocalVue();
        const wrapper = mount(CustomerMasterEdit, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
        });

        const PAGE = wrapper.find('.customer-master-edit');
        const BODY = PAGE.find('.customer-master-edit__body');

        const CUSTOMER_NAME = BODY.find('.input-customer-name');
        const LABEL_CUSTOMER_NAME = CUSTOMER_NAME.find('label');
        expect(LABEL_CUSTOMER_NAME.exists()).toBe(true);
        expect(LABEL_CUSTOMER_NAME.text()).toEqual('CUSTOMER_MASTER_TABLE_LABLE_CUSTOMER_NAME');
        const INPUT_CUSTOMER_NAME = CUSTOMER_NAME.find('input#input-customer-name');
        expect(INPUT_CUSTOMER_NAME.exists()).toBe(true);
        expect(INPUT_CUSTOMER_NAME.element.value).toEqual('');

        wrapper.destroy();
    });

    test('Test render handle', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(CustomerMasterEdit, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
        });

        const PAGE = wrapper.find('.customer-master-edit');
        const BODY = PAGE.find('.customer-master-edit__body');

        const BUTTON_BACK = BODY.find('button.v-button-default');
        expect(BUTTON_BACK.exists()).toBe(true);
        expect(BUTTON_BACK.text()).toEqual('BUTTON.BACK');
        const onClickBack = jest.spyOn(wrapper.vm, 'onClickBack');
        await BUTTON_BACK.trigger('click');
        expect(onClickBack).toHaveBeenCalled();

        const BUTTON_REGISTER = BODY.find('button.btn-registration');
        expect(BUTTON_REGISTER.exists()).toBe(true);
        expect(BUTTON_REGISTER.text()).toEqual('BUTTON.SAVE');
        const onClickSave = jest.spyOn(wrapper.vm, 'onClickSave');
        await BUTTON_REGISTER.trigger('click');
        expect(onClickSave).toHaveBeenCalled();

        wrapper.destroy();
    });

    test('Test function validate', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(CustomerMasterEdit, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
        });

        const validateCreateCustomer = jest.spyOn(wrapper.vm, 'validateCreateCustomer');

        await wrapper.setData({ customerName: '' });
        expect(validateCreateCustomer()).toBe(false);
        await wrapper.setData({ customerName: 'Vin Mart' });
        expect(validateCreateCustomer()).toBe(true);
        await wrapper.setData({ customerName: 'Vin Mart Vin Mart Vin Mart Vin Mart Vin Mart' });
        expect(validateCreateCustomer()).toBe(false);

        wrapper.destroy();
    });

    test('Test event open page', () => {
        const handleGetOneCustomer = jest.fn();

        const localVue = createLocalVue();
        const wrapper = mount(CustomerMasterEdit, {
            localVue,
            router,
            store,
            methods: {
                handleGetOneCustomer,
            },
            stubs: {
                BIcon: true,
            },
        });

        expect(handleGetOneCustomer).toHaveBeenCalled();

        wrapper.destroy();
    });
});
