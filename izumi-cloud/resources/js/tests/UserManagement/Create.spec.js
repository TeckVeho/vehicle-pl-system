import { mount, createLocalVue } from '@vue/test-utils';
import store from '@/store';
import router from '@/router';
import UserManagementCreate from '@/pages/UserManagement/Create';
import { validPassword } from '@/utils/validate';

describe('TEST COMPONENT USER MANAGEMENT CREATE - CREATE', () => {
    const apiStubs = () => ({
        getListRole: jest.fn().mockResolvedValue(undefined),
    });

    test('Test component render data component correct', () => {
        const localVue = createLocalVue();
        const wrapper = mount(UserManagementCreate, {
            localVue,
            router,
            store,
            methods: apiStubs(),
        });

        const CreateForm = wrapper.find('.content-body');
        expect(CreateForm.exists()).toBe(true);

        const ListInputRow = CreateForm.findAll('.input-row');
        expect(ListInputRow.length).toEqual(5);

        const ListInputLabel = CreateForm.findAll('.user-regis-label');
        expect(ListInputLabel.length).toEqual(4);

        const ListInput = CreateForm.findAll('.user-regis-input');
        expect(ListInput.length).toEqual(3);

        const FooterFunctionalButtons = CreateForm.find('.footer-functional-buttons');
        expect(FooterFunctionalButtons.exists()).toBe(true);

        const ListButton = FooterFunctionalButtons.findAll('.v-button-default');
        expect(ListButton.length).toEqual(2);

        const ButtonBack = FooterFunctionalButtons.find('#btn-back');
        expect(ButtonBack.exists()).toBe(true);
        expect(ButtonBack.text()).toEqual('BUTTON.BACK');

        const ButtonSave = FooterFunctionalButtons.find('#btn-save');
        expect(ButtonSave.exists()).toBe(true);
        expect(ButtonSave.text()).toEqual('BUTTON.SIGN_UP');

        wrapper.destroy();
    });

    test('Test function click to back button', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(UserManagementCreate, {
            localVue,
            router,
            store,
            methods: apiStubs(),
        });

        const ButtonBack = wrapper.find('#btn-back');
        expect(ButtonBack.exists()).toBe(true);
        expect(ButtonBack.text()).toEqual('BUTTON.BACK');

        const doReturnToIndex = jest.spyOn(wrapper.vm, 'backToUserList');
        await ButtonBack.trigger('click');

        wrapper.vm.$nextTick(() => {
            expect(doReturnToIndex).toHaveBeenCalled();
            expect(window.location.hash).toEqual('#/user-management/list');
        });

        wrapper.destroy();
    });

    test('Test function click to save button', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(UserManagementCreate, {
            localVue,
            router,
            store,
            methods: apiStubs(),
        });

        const ButtonSave = wrapper.find('#btn-save');
        expect(ButtonSave.exists()).toBe(true);
        expect(ButtonSave.text()).toEqual('BUTTON.SIGN_UP');

        const doCreateNewUser = jest.spyOn(wrapper.vm, 'doSignUp');
        await ButtonSave.trigger('click');
        expect(doCreateNewUser).toHaveBeenCalled();

        wrapper.destroy();
    });

    test('Test display default', () => {
        const localVue = createLocalVue();
        const wrapper = mount(UserManagementCreate, {
            localVue,
            router,
            store,
            methods: apiStubs(),
        });

        // Test data
        expect(wrapper.vm.user_role).toEqual(null);
        expect(wrapper.vm.employee_name).toEqual('');
        expect(wrapper.vm.user_id).toEqual('');
        expect(wrapper.vm.password).toEqual('');

        // Test display
        const SelectRole = wrapper.find('select.user-regis-select');
        expect(SelectRole.element.value).toEqual('');
        const EmpName = wrapper.find('input.user-full-name');
        expect(EmpName.element.value).toEqual('');
        const UserId = wrapper.find('input.user_id');
        expect(UserId.element.value).toEqual('');
        const Password = wrapper.find('input.user_password');
        expect(Password.element.value).toEqual('');

        wrapper.destroy();
    });

    test('Test msg error when input require is blank', async() => {
        const mocks = {
            $toast: {
                warning: jest.fn(),
            },
        };

        const localVue = createLocalVue();
        const wrapper = mount(UserManagementCreate, {
            localVue,
            router,
            store,
            mocks,
            methods: apiStubs(),
        });

        const validation = jest.spyOn(wrapper.vm, 'validation');

        // Test data
        expect(wrapper.vm.user_role).toEqual(null);
        expect(wrapper.vm.employee_name).toEqual('');
        expect(wrapper.vm.user_id).toEqual('');
        expect(wrapper.vm.password).toEqual('');

        const BtnSave = wrapper.find('button.btn-save');
        await BtnSave.trigger('click');

        expect(validation).toHaveBeenCalled();
        expect(wrapper.vm.$toast.warning).toHaveBeenCalled();
        expect(wrapper.vm.$toast.warning).toHaveBeenCalledWith(
            {
                content: 'REQUIRE_USER_ROLE',
            }
        );

        wrapper.destroy();
    });

    test('Test function validate password', () => {
        expect(validPassword('')).toBe(false);
        expect(validPassword('123')).toBe(false);
        expect(validPassword('                    ')).toBe(false);
        expect(validPassword('123                  ')).toBe(false);
        expect(validPassword('hai hai hai hai')).toBe(false);
        expect(validPassword('hai')).toBe(false);

        expect(validPassword('123456789')).toBe(true);
        expect(validPassword('123123123haihaihaihai')).toBe(true);
        expect(validPassword('*&^%@#$%%$&#$%&#$')).toBe(true);
    });

    test('Test create user with data correct', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(UserManagementCreate, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
            methods: apiStubs(),
        });

        expect(wrapper.vm.user_role).toEqual(null);
        expect(wrapper.vm.employee_name).toEqual('');
        expect(wrapper.vm.user_id).toEqual('');
        expect(wrapper.vm.password).toEqual('');

        wrapper.vm.user_role = 1;
        wrapper.vm.employee_name = 'Vu Duc Viet';
        wrapper.vm.user_id = '131020000';
        wrapper.vm.password = '123456789';

        const doSignUp = jest.spyOn(wrapper.vm, 'doSignUp');
        const validation = jest.spyOn(wrapper.vm, 'validation');

        const BtnSave = wrapper.find('button.btn-save');
        await BtnSave.trigger('click');

        expect(doSignUp).toHaveBeenCalled();
        expect(validation).toHaveBeenCalled();

        wrapper.destroy();
    });

    test('Test if the role list is display successfully', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(UserManagementCreate, {
            localVue,
            router,
            store,
            stubs: {},
            methods: apiStubs(),
        });

        expect(wrapper.vm.user_role).toEqual(null);
        expect(wrapper.vm.employee_name).toEqual('');
        expect(wrapper.vm.user_id).toEqual('');
        expect(wrapper.vm.password).toEqual('');

        expect(wrapper.vm.options.length).toBeGreaterThanOrEqual(1);
        expect(wrapper.vm.options[0].value).toBe(null);

        wrapper.vm.options = [
            {
                'value': null,
                'text': '-- 選んでください --',
                'disabled': false,
            },
            {
                'value': 1,
                'text': 'Crew',
                'disabled': false,
            },
            {
                'value': 2,
                'text': '事務員',
                'disabled': false,
            },
            {
                'value': 3,
                'text': 'TL',
                'disabled': false,
            },
            {
                'value': 4,
                'text': '経理財務課',
                'disabled': false,
            },
            {
                'value': 5,
                'text': '総務DX課',
                'disabled': false,
            },
            {
                'value': 6,
                'text': '人事労務部',
                'disabled': false,
            },
            {
                'value': 7,
                'text': '本社管理者',
                'disabled': false,
            },
            {
                'value': 8,
                'text': 'MG',
                'disabled': false,
            },
            {
                'value': 9,
                'text': '部長/役員',
                'disabled': false,
            },
            {
                'value': 10,
                'text': '経理財務部長',
                'disabled': false,
            },
            {
                'value': 11,
                'text': '人事労務部長',
                'disabled': false,
            },
            {
                'value': 12,
                'text': 'DX管理者',
                'disabled': false,
            },
        ];

        expect(wrapper.vm.options.length).toEqual(13);

        wrapper.destroy();
    });
});
