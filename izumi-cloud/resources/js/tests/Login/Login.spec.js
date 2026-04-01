import { mount, createLocalVue } from '@vue/test-utils';
import store from '@/store';
import router from '@/router';
import Login from '@/pages/Login/index';
import LoginOrganisms from '@/components/organisms/Login';

describe('TEST COMPONENT LOGIN', () => {
    test('Test component render Login Template', () => {
        const localVue = createLocalVue();
        const wrapper = mount(Login, {
            localVue,
            store,
            router,
        });

        const LoginTemplate = wrapper.findComponent({ name: 'LoginTemplate' });
        expect(LoginTemplate.exists()).toBe(true);

        wrapper.destroy();
    });

    test('Test component render UI', () => {
        const localVue = createLocalVue();
        const wrapper = mount(Login, {
            localVue,
            store,
            router,
        });

        const LoginZone = wrapper.find('.login');
        expect(LoginZone.exists()).toBe(true);
        expect(LoginZone.classes('container-fluid')).toBe(true);

        const LoginForm = wrapper.find('.login-form');
        expect(LoginForm.exists()).toBe(true);

        const ItemInput = LoginForm.findAll('.item-input');
        expect(ItemInput.length).toBe(2);

        const RenderItemInput = [
            {
                label: 'LOGIN_ID',
                type: 'text',
                placeholder: 'LOGIN_PLACEHOLDER_ID',
                disabled: false,
                value: '',
            },
            {
                label: 'LOGIN_PASSWORD',
                type: 'password',
                placeholder: 'LOGIN_PLACEHOLDER_PASSWORD',
                disabled: false,
                value: '',
            },
        ];

        for (let input = 0; input < ItemInput.length; input++) {
            const IndexInput = ItemInput.at(input);
            const Label = IndexInput.find('label');
            const Input = IndexInput.find('input');

            expect(Label.exists()).toBe(true);
            expect(Label.text()).toEqual(RenderItemInput[input].label);

            expect(Input.exists()).toBe(true);
            expect(Input.props('type')).toEqual(RenderItemInput[input].type);
            expect(Input.props('placeholder')).toEqual(RenderItemInput[input].placeholder);
            expect(Input.props('disabled')).toEqual(RenderItemInput[input].disabled);
            expect(Input.props('value')).toEqual(RenderItemInput[input].value);
        }

        const ButtonLogin = LoginZone.find('.login-button-submit');
        expect(ButtonLogin.exists()).toBe(true);
        expect(ButtonLogin.find('button').exists()).toBe(true);
        expect(ButtonLogin.find('button').text()).toEqual('LOGIN_BUTTON_TEXT');
        expect(ButtonLogin.find('button').classes()).toContain('login-btn');

        wrapper.destroy();
    });

    test('Test component validate form (Not Pass)', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(LoginOrganisms, {
            localVue,
            store,
            router,
        });

        const callApiLogin = jest.spyOn(wrapper.vm, 'callApiLogin');

        const ItemInput = wrapper.findAll('.item-input');
        await ItemInput.at(0).find('input[type="text"]').setValue('123');

        const ButtonLogin = wrapper.find('.login-button-submit').find('button');

        await ButtonLogin.trigger('click');

        expect(callApiLogin).not.toHaveBeenCalled();

        wrapper.destroy();
    });

    test('Test component validate form (Pass)', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(LoginOrganisms, {
            localVue,
            store,
            router,
        });

        const callApiLogin = jest.spyOn(wrapper.vm, 'callApiLogin');

        const ItemInput = wrapper.findAll('.item-input');
        await ItemInput.at(0).find('input[type="text"]').setValue('111111');
        await ItemInput.at(1).find('input[type="password"]').setValue('123456789');

        const ButtonLogin = wrapper.find('.login-button-submit').find('button');

        await ButtonLogin.trigger('click');

        expect(callApiLogin).toHaveBeenCalled();

        wrapper.destroy();
    });
});
