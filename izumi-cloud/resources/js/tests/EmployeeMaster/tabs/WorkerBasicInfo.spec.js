import { mount, createLocalVue } from '@vue/test-utils';
import store from '@/store';
import router from '@/router';
import WorkerBasicInfo from '@/pages/EmployeeMaster/tabs/WorkerBasicInfo.vue';

describe('TEST COMPONENT WORKER BASIC INFO', () => {
    let wrapper;
    let localVue;

    const mockEmployeeData = {
        id: 'EMP001',
        name: 'Test Employee',
        name_phonetic: 'テスト',
        date_of_appointment: '2023-01-01',
        address: 'Test Address',
        contact_phone_number: '09012345678',
        previous_employment_history: 'Previous Company',
        aptitude_test_date: '2023-02-01',
        medical_examination_date: '2023-03-01',
        selected_classroom: 1,
        selected_practical: 1,
        email: 'test@example.com',
        gender: 'MALE',
        birthday: '1990-01-01',
        workingType: 'DRIVER',
        licenseType: 'ORDINARY',
        employeeType: 'FULL_TIME',
        employeeRole: '',
        hireStartDate: '2023-01-01',
        retirementDate: '',
        welfareExpense: '',
        employee_role: 1,
    };

    beforeEach(() => {
        localVue = createLocalVue();
        wrapper = mount(WorkerBasicInfo, {
            localVue,
            store,
            router,
            propsData: {
                data: mockEmployeeData,
            },
            stubs: {
                BCol: true,
                BFormInput: true,
                BFormSelect: true,
                BFormRadioGroup: true,
            },
        });
    });

    afterEach(() => {
        if (wrapper) {
            wrapper.destroy();
        }
    });

    test('Check component render page', () => {
        const PAGE = wrapper.find('.worker-basic-info');
        expect(PAGE.exists()).toBe(true);
    });

    test('Check component render title', () => {
        const TITLE = wrapper.find('.title');
        expect(TITLE.exists()).toBe(true);
    });

    test('Check component render employee master detail section', () => {
        const SECTION = wrapper.find('.employee-master-detail');
        expect(SECTION.exists()).toBe(true);
    });

    test('Check component render basic data items', () => {
        const ITEMS = wrapper.findAll('.item-data');
        expect(ITEMS.length).toBeGreaterThan(0);
    });

    test('Check employee data is assigned from props', () => {
        expect(wrapper.vm.employee.id).toBe(mockEmployeeData.id);
        expect(wrapper.vm.employee.name).toBe(mockEmployeeData.name);
    });

    test('Check component render employee ID field', () => {
        const EMPLOYEE_ID = wrapper.find('#employee-id');
        expect(EMPLOYEE_ID.exists()).toBe(true);
    });

    test('Check component render employee name field', () => {
        const EMPLOYEE_NAME = wrapper.find('#employee-name');
        expect(EMPLOYEE_NAME.exists()).toBe(true);
    });

    test('Check component render new driver training section', () => {
        const TITLE = wrapper.findAll('.title_new-driver-training');
        expect(TITLE.length).toBeGreaterThanOrEqual(2);
    });

    test('Check component render classroom radio options', () => {
        expect(wrapper.vm.options).toHaveLength(2);
        expect(wrapper.vm.options[0].item).toBe('1');
        expect(wrapper.vm.options[1].item).toBe('2');
    });

    test('Check component render practical radio options', () => {
        expect(wrapper.vm.options_practical).toHaveLength(2);
        expect(wrapper.vm.options_practical[0].item).toBe('1');
        expect(wrapper.vm.options_practical[1].item).toBe('2');
    });

    test('Check component render back button', () => {
        const BACK_BUTTON = wrapper.find('.btn-return');
        expect(BACK_BUTTON.exists()).toBe(true);
    });

    test('Check onClickReturn method', async() => {
        const routerPushSpy = jest.spyOn(router, 'push');
        wrapper.vm.onClickReturn();
        expect(routerPushSpy).toHaveBeenCalledWith({ name: 'EmployeeMaster' });
    });

    test('Check employee_role_options structure', () => {
        expect(wrapper.vm.employee_role_options).toHaveLength(5);
        expect(wrapper.vm.employee_role_options[0].value).toBe(null);
        expect(wrapper.vm.employee_role_options[1].value).toBe(1);
    });

    test('Check selected_classroom is set from data', () => {
        expect(wrapper.vm.employee.selected_classroom).toBe(mockEmployeeData.selected_classroom);
    });

    test('Check selected_practical is set from data', () => {
        expect(wrapper.vm.employee.selected_practical).toBe(mockEmployeeData.selected_practical);
    });

    test('Check component handles empty data', () => {
        const emptyWrapper = mount(WorkerBasicInfo, {
            localVue,
            store,
            router,
            propsData: {
                data: {},
            },
            stubs: {
                BCol: true,
                BFormInput: true,
                BFormSelect: true,
                BFormRadioGroup: true,
            },
        });
        expect(emptyWrapper.vm.employee).toBeDefined();
        emptyWrapper.destroy();
    });
});

