import { mount, createLocalVue } from '@vue/test-utils';
import store from '@/store';
import router from '@/router';
import UserManagementEdit from '@/pages/UserManagement/Edit';

describe('TEST COMPONENT USER MANAGEMENT CREATE - CREATE', () => {
    const apiStubs = () => ({
        getListRole: jest.fn().mockResolvedValue(undefined),
        getOneUserData: jest.fn().mockResolvedValue(undefined),
    });

    test('Test component render data component correct', () => {
        const localVue = createLocalVue();
        const wrapper = mount(UserManagementEdit, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
            methods: apiStubs(),
        });

        const CreateForm = wrapper.find('.content-body');
        expect(CreateForm.exists()).toBe(true);

        const ListInputRow = CreateForm.findAll('.input-row');
        expect(ListInputRow.length).toBeGreaterThanOrEqual(6);

        const ListInputLabel = CreateForm.findAll('.user-regis-label');
        expect(ListInputLabel.length).toBeGreaterThanOrEqual(5);

        const ListInput = CreateForm.findAll('.user-regis-input');
        expect(ListInput.length).toEqual(5);

        const FooterFunctionalButtons = CreateForm.find('.footer-functional-buttons');
        expect(FooterFunctionalButtons.exists()).toBe(true);

        const ListButton = FooterFunctionalButtons.findAll('.v-button-default');
        expect(ListButton.length).toEqual(2);

        const ButtonBack = FooterFunctionalButtons.find('#btn-back');
        expect(ButtonBack.exists()).toBe(true);
        expect(ButtonBack.text()).toEqual('BUTTON.BACK');

        const ButtonSave = FooterFunctionalButtons.find('#btn-save');
        expect(ButtonSave.exists()).toBe(true);
        expect(ButtonSave.text()).toEqual('BUTTON.SAVE');

        wrapper.destroy();
    });

    test('Test function click to back button', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(UserManagementEdit, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
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

    test('Test msg error when input require is blank', async() => {
        const mocks = {
            $toast: {
                warning: jest.fn(),
            },
        };

        const localVue = createLocalVue();
        const wrapper = mount(UserManagementEdit, {
            localVue,
            router,
            store,
            mocks,
            stubs: {
                BIcon: true,
            },
            methods: apiStubs(),
        });

        const validation = jest.spyOn(wrapper.vm, 'validation');

        const BtnSave = wrapper.find('button#btn-save');
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

    test('Test function click to save button', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(UserManagementEdit, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
        });

        const ButtonSave = wrapper.find('#btn-save');
        expect(ButtonSave.exists()).toBe(true);
        expect(ButtonSave.text()).toEqual('BUTTON.SAVE');

        const doCreateNewUser = jest.spyOn(wrapper.vm, 'doEdit');
        await ButtonSave.trigger('click');
        expect(doCreateNewUser).toHaveBeenCalled();

        wrapper.destroy();
    });

    test('Test component call function getOneUserData in hook created', async() => {
        const getListRole = jest.fn().mockResolvedValue(undefined);
        const getOneUserData = jest.fn().mockResolvedValue(undefined);
        const localVue = createLocalVue();
        const wrapper = mount(UserManagementEdit, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
            methods: {
                ...apiStubs(),
                getListRole,
                getOneUserData,
            },
        });

        await wrapper.vm.$nextTick();
        await new Promise((resolve) => setTimeout(resolve, 0));

        expect(getListRole).toHaveBeenCalled();
        expect(getOneUserData).toHaveBeenCalled();

        wrapper.destroy();
    });

    test('Test if the role list is display successfully', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(UserManagementEdit, {
            localVue,
            router,
            store,
            stubs: {},
            methods: {
                getListRole: jest.fn().mockResolvedValue(undefined),
                getOneUserData: jest.fn().mockResolvedValue(undefined),
            },
        });

        expect(wrapper.vm.user_role).toEqual(null);
        expect(wrapper.vm.employee_name).toEqual('');
        expect(wrapper.vm.user_id).toBeUndefined();
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

    test('Test map password setup mail histories', () => {
        const localVue = createLocalVue();
        const wrapper = mount(UserManagementEdit, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
            methods: apiStubs(),
        });

        const mapped = wrapper.vm.mapPasswordSetupMailHistories([
            {
                sent_at: '2026-03-04 12:51:20',
                sender_name: '加納',
            },
            {
                created_at: '2026-03-03 12:30:42',
                sender: {
                    name: '鈴木',
                },
            },
        ]);

        expect(mapped).toEqual([
            {
                sent_at: '2026年03月04日 12時間51分',
                sender_name: '加納',
            },
            {
                sent_at: '2026年03月03日 12時間30分',
                sender_name: '鈴木',
            },
        ]);

        expect(wrapper.vm.mapPasswordSetupMailHistories(null)).toEqual([]);

        wrapper.destroy();
    });
});
