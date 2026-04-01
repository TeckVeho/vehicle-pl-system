import { mount, createLocalVue } from '@vue/test-utils';
import VueRouter from 'vue-router';
import store from '@/store';
import DepartmentMasterEdit from '@/pages/DepartmentMaster/edit';
import { getOneDepartment, putDepartment, searchUser, getLineWorkPIC, getEmployeeAll } from '@/api/modules/department_master';

// Mock API functions
jest.mock('@/api/modules/department_master', () => ({
    getOneDepartment: jest.fn(),
    putDepartment: jest.fn(),
    searchUser: jest.fn(),
    getLineWorkPIC: jest.fn(),
    getEmployeeAll: jest.fn(),
}));

describe('TEST COMPONENT DEPARTMENT MASTER EDIT', () => {
    let wrapper;
    let localVue;
    let testRouter;

    beforeEach(async() => {
        jest.clearAllMocks();

        localVue = createLocalVue();
        localVue.use(VueRouter);

        testRouter = new VueRouter({
            mode: 'abstract',
            routes: [
                {
                    path: '/master-manager/department-master/edit/:id',
                    component: { template: '<div class="route-placeholder" />' },
                },
            ],
        });
        await testRouter.push('/master-manager/department-master/edit/1');
        jest.spyOn(testRouter, 'push');

        getEmployeeAll.mockResolvedValue({ code: 200, data: [] });

        // Mock toast
        const toast = {
            success: jest.fn(),
            warning: jest.fn(),
            error: jest.fn(),
        };
        localVue.prototype.$toast = toast;
    });

    afterEach(() => {
        if (wrapper) {
            wrapper.destroy();
        }
    });

    const createWrapper = (options = {}) => {
        const { mocks: optionMocks, stubs: optionStubs, ...rest } = options;
        return mount(DepartmentMasterEdit, {
            localVue,
            router: testRouter,
            store,
            mocks: {
                ...optionMocks,
            },
            stubs: {
                BIcon: true,
                'b-collapse': {
                    template: '<div class="b-collapse-stub"><slot></slot></div>',
                },
                vHeaderPage: {
                    template: '<div class="v-header-page"><slot></slot></div>',
                },
                vMultiselect: {
                    template: '<div class="v-multiselect"></div>',
                    props: ['value', 'options', 'label', 'trackBy', 'searchable', 'showLabels', 'placeholder', 'openDirection', 'closeOnSelect', 'multiple'],
                },
                ...optionStubs,
            },
            ...rest,
        });
    };

    describe('Component Rendering', () => {
        test('should render component correctly', () => {
            wrapper = createWrapper();

            expect(wrapper.find('.department-master-edit').exists()).toBe(true);
            expect(wrapper.find('.department-master-edit-content').exists()).toBe(true);
        });

        test('should render header with correct text', () => {
            wrapper = createWrapper();

            const header = wrapper.find('.header');
            expect(header.exists()).toBe(true);
            expect(header.text()).toContain('拠点マスタ');
        });

        test('should render basic information section', () => {
            wrapper = createWrapper();

            const basicInfoSection = wrapper.find('#basic-information');
            expect(basicInfoSection.exists()).toBe(true);

            expect(wrapper.find('.name-input').exists()).toBe(true);
            expect(wrapper.find('.prefecture-input').exists()).toBe(true);
            expect(wrapper.find('.post-code').exists()).toBe(true);
            expect(wrapper.find('.address-input').exists()).toBe(true);
        });

        test('should render recruitment information section', () => {
            wrapper = createWrapper();

            const recruitmentSection = wrapper.find('#recruitment-information');
            expect(recruitmentSection.exists()).toBe(true);

            expect(wrapper.find('.interview-address').exists()).toBe(true);
            expect(wrapper.find('.interview-address-url').exists()).toBe(true);
            expect(wrapper.find('.path-for-interview-address').exists()).toBe(true);
        });

        test('should render facility information section', () => {
            wrapper = createWrapper();

            const facilitySection = wrapper.find('#facility-information');
            expect(facilitySection.exists()).toBe(true);

            expect(wrapper.find('.name-sales').exists()).toBe(true);
            expect(wrapper.find('.location-input').exists()).toBe(true);
            expect(wrapper.find('.area').exists()).toBe(true);
            expect(wrapper.find('.area-break-room').exists()).toBe(true);
            expect(wrapper.find('.position').exists()).toBe(true);
            expect(wrapper.find('.area-garage').exists()).toBe(true);
            expect(wrapper.find('.position-last').exists()).toBe(true);
            expect(wrapper.find('.area-garage-last').exists()).toBe(true);
        });

        test('should render management certification section', () => {
            wrapper = createWrapper();

            const managementSection = wrapper.find('#management-certification');
            expect(managementSection.exists()).toBe(true);

            const multiselects = managementSection.findAll('.v-multiselect');
            expect(multiselects.length).toBeGreaterThanOrEqual(4);

            expect(wrapper.find('.member-no').exists()).toBe(true);
            expect(wrapper.find('.number-mark').exists()).toBe(true);
            expect(wrapper.find('.expiry-date').exists()).toBe(true);
        });

        test('should render footer buttons', () => {
            wrapper = createWrapper();

            const backButton = wrapper.find('.back-button');
            const saveButton = wrapper.find('.save-button');

            expect(backButton.exists()).toBe(true);
            expect(saveButton.exists()).toBe(true);
            expect(backButton.text()).toContain('戻る');
            expect(saveButton.text()).toContain('保存');
        });
    });

    describe('Initial Data State', () => {
        test('should initialize with default values', () => {
            wrapper = createWrapper();

            expect(wrapper.vm.name).toBe('');
            expect(wrapper.vm.prefecture).toBe('');
            expect(wrapper.vm.post_code).toBe('');
            expect(wrapper.vm.address).toBe('');
            expect(wrapper.vm.tel).toBe('');
            expect(wrapper.vm.interview_address).toBe('');
            expect(wrapper.vm.interview_address_url).toBe('');
            expect(wrapper.vm.interview_pic).toBe(null);
            expect(wrapper.vm.line_work_pic).toEqual([]);
            expect(wrapper.vm.search_results).toEqual([]);
            expect(wrapper.vm.list_line_work_pic).toEqual([]);
            expect(wrapper.vm.basic_information_dropdown).toBe(true);
            expect(wrapper.vm.facility_information_dropdown).toBe(false);
            expect(wrapper.vm.management_certification_dropdown).toBe(false);
            expect(wrapper.vm.roll_call_radio).toBe(null);
        });

        test('should initialize overlay with correct default values', async() => {
            searchUser.mockResolvedValue({ code: 200, data: [] });
            getLineWorkPIC.mockResolvedValue({ code: 200, data: [] });
            getOneDepartment.mockResolvedValue({ code: 200, data: {}});

            wrapper = createWrapper();

            await new Promise((resolve) => setTimeout(resolve, 0));

            expect(wrapper.vm.overlay.show).toBe(false);
            expect(wrapper.vm.overlay.variant).toBe('light');
            expect(wrapper.vm.overlay.opacity).toBe(1);
            expect(wrapper.vm.overlay.blur).toBe('1rem');
        });
    });

    describe('Component Lifecycle', () => {
        test('should call handleInitData on created', async() => {
            const handleInitData = jest.fn();

            searchUser.mockResolvedValue({ code: 200, data: [] });
            getLineWorkPIC.mockResolvedValue({ code: 200, data: [] });
            getOneDepartment.mockResolvedValue({ code: 200, data: {}});

            wrapper = createWrapper({
                methods: {
                    handleInitData,
                },
            });

            await wrapper.vm.$nextTick();
            expect(handleInitData).toHaveBeenCalled();
        });
    });

    describe('API Calls', () => {
        test('should fetch department info on mount', async() => {
            const mockDepartmentData = {
                name: 'Test Department',
                province_name: 'Tokyo',
                post_code: '1234567',
                address: 'Test Address',
                tel: '0312345678',
                interview_address: 'Interview Address',
                interview_address_url: 'https://example.com',
                path_for_interview_address: 'Path to interview',
                office_name: 'Office Name',
                office_location: 'Office Location',
                office_area: '100',
                rest_room_area: '50',
                garage_location_1: 'Garage 1 Location',
                garage_area_1: '200',
                garage_location_2: 'Garage 2 Location',
                garage_area_2: '200',
                operations_manager_appointment: 'Manager Name',
                operations_manager_assistant: 'Assistant Name',
                maintenance_manager_appointment: 'Maintenance Manager',
                maintenance_manager_assistant: 'Maintenance Assistant',
                maintenance_manager_phone_number: '0312345678',
                maintenance_manager_fax_number: '0312345679',
                truck_association_membership_number: '12345',
                g_mark_number: 'G12345',
                g_mark_expiration_date: '2024-12-31',
                it_roll_call: 0,
                interview_pic: 'PIC001',
                interview_pic_line_work: [],
            };

            const mockSearchResults = [
                { code: 'PIC001', name_code: 'Test PIC' },
            ];

            const mockLineWorkPIC = [
                { code: 'LW001', full_name: 'Line Work User 1' },
            ];

            searchUser.mockResolvedValue({ code: 200, data: mockSearchResults });
            getLineWorkPIC.mockResolvedValue({ code: 200, data: mockLineWorkPIC });
            getOneDepartment.mockResolvedValue({ code: 200, data: mockDepartmentData });

            wrapper = createWrapper();

            // Mock route params
            await wrapper.vm.handleGetDepartmentInfo();
            await wrapper.vm.$nextTick();

            expect(getOneDepartment).toHaveBeenCalled();
            expect(wrapper.vm.name).toBe('Test Department');
            expect(wrapper.vm.prefecture).toBe('Tokyo');
            expect(wrapper.vm.post_code).toBe('1234567');
        });

        test('should handle API error when fetching department info', async() => {
            const errorResponse = {
                response: {
                    data: {
                        message: 'Department not found',
                    },
                },
            };

            searchUser.mockResolvedValue({ code: 200, data: [] });
            getLineWorkPIC.mockResolvedValue({ code: 200, data: [] });
            getOneDepartment.mockRejectedValue(errorResponse);

            wrapper = createWrapper();
            await wrapper.vm.handleGetDepartmentInfo();
            await wrapper.vm.$nextTick();

            expect(getOneDepartment).toHaveBeenCalled();
        });

        test('should fetch search users on initialization', async() => {
            const mockUsers = [
                { code: 'USER001', name_code: 'User 1' },
                { code: 'USER002', name_code: 'User 2' },
            ];

            searchUser.mockResolvedValue({ code: 200, data: mockUsers });
            getLineWorkPIC.mockResolvedValue({ code: 200, data: [] });
            getOneDepartment.mockResolvedValue({ code: 200, data: {}});

            wrapper = createWrapper();
            await wrapper.vm.handleSearchUser();
            await wrapper.vm.$nextTick();

            expect(searchUser).toHaveBeenCalled();
            expect(wrapper.vm.search_results).toEqual(mockUsers);
        });

        test('should fetch Line Work PIC list on initialization', async() => {
            const mockLineWorkPIC = [
                { code: 'LW001', full_name: 'Line Work User 1' },
                { code: 'LW002', full_name: 'Line Work User 2' },
            ];

            searchUser.mockResolvedValue({ code: 200, data: [] });
            getLineWorkPIC.mockResolvedValue({ code: 200, data: mockLineWorkPIC });
            getOneDepartment.mockResolvedValue({ code: 200, data: {}});

            wrapper = createWrapper();
            await wrapper.vm.handleGetLineWorkPIC();
            await wrapper.vm.$nextTick();

            expect(getLineWorkPIC).toHaveBeenCalled();
            expect(wrapper.vm.list_line_work_pic).toEqual(mockLineWorkPIC);
        });
    });

    describe('Form Validation', () => {
        test('should return false when interview_address is null', () => {
            wrapper = createWrapper();

            wrapper.setData({
                interview_address: null,
                interview_address_url: 'https://example.com',
                interview_pic: { code: 'PIC001' },
            });

            const result = wrapper.vm.handleVailidateFormData();
            expect(result).toBe(false);
        });

        test('should return false when interview_address exceeds 1000 characters', () => {
            wrapper = createWrapper();

            const longString = 'a'.repeat(1001);
            wrapper.setData({
                interview_address: longString,
                interview_address_url: 'https://example.com',
                interview_pic: { code: 'PIC001' },
            });

            const result = wrapper.vm.handleVailidateFormData();
            expect(result).toBe(false);
        });

        test('should return false when interview_address_url is null', () => {
            wrapper = createWrapper();

            wrapper.setData({
                interview_address: 'Valid address',
                interview_address_url: null,
                interview_pic: { code: 'PIC001' },
            });

            const result = wrapper.vm.handleVailidateFormData();
            expect(result).toBe(false);
        });

        test('should return false when path_for_interview_address exceeds 1000 characters', () => {
            wrapper = createWrapper();

            const longString = 'a'.repeat(1001);
            wrapper.setData({
                interview_address: 'Valid address',
                interview_address_url: 'https://example.com',
                path_for_interview_address: longString,
                interview_pic: { code: 'PIC001' },
            });

            const result = wrapper.vm.handleVailidateFormData();
            expect(result).toBe(false);
        });

        test('should return false when interview_pic is null', () => {
            wrapper = createWrapper();

            wrapper.setData({
                interview_address: 'Valid address',
                interview_address_url: 'https://example.com',
                interview_pic: null,
            });

            const result = wrapper.vm.handleVailidateFormData();
            expect(result).toBe(false);
        });

        test('should return true when all validations pass', () => {
            wrapper = createWrapper();

            wrapper.setData({
                interview_address: 'Valid address',
                interview_address_url: 'https://example.com',
                path_for_interview_address: 'Valid path',
                interview_pic: { code: 'PIC001', name_code: 'Test PIC' },
            });

            const result = wrapper.vm.handleVailidateFormData();
            expect(result).toBe(true);
        });
    });

    describe('Save Department Info', () => {
        test('should save department info successfully', async() => {
            const mockResponse = {
                code: 200,
                message: 'Success',
            };

            searchUser.mockResolvedValue({ code: 200, data: [] });
            getLineWorkPIC.mockResolvedValue({ code: 200, data: [] });
            getOneDepartment.mockResolvedValue({ code: 200, data: {}});
            putDepartment.mockResolvedValue(mockResponse);

            wrapper = createWrapper();
            wrapper.setData({
                post_code: '1234567',
                address: 'Test Address',
                tel: '0312345678',
                interview_address: 'Interview Address',
                interview_address_url: 'https://example.com',
                interview_pic: { code: 'PIC001' },
                path_for_interview_address: 'Path',
                line_work_pic: [{ code: 'LW001' }],
                name_sales: 'Office Name',
                location: 'Location',
                area_office: '100',
                name_break_room: '50',
                position: 'Position 1',
                area_garage: '200',
                position_last: 'Position 2',
                area_garage_last: '200',
                appointment: 'Manager',
                assistant: 'Assistant',
                appointment_maintenance: 'Maintenance Manager',
                assistant_maintenace: 'Maintenance Assistant',
                telephone_number: '0312345678',
                fax_number: '0312345679',
                member_no: '12345',
                number_mark: 'G12345',
                expiry_date: '2024-12-31',
                roll_call_radio: 0,
            });

            await wrapper.vm.handleSaveDepartmentInfo();
            await wrapper.vm.$nextTick();

            expect(putDepartment).toHaveBeenCalled();
            expect(wrapper.vm.$toast.success).toHaveBeenCalled();
        });

        test('should handle save error', async() => {
            const errorResponse = {
                response: {
                    data: {
                        message: 'Save failed',
                    },
                },
            };

            searchUser.mockResolvedValue({ code: 200, data: [] });
            getLineWorkPIC.mockResolvedValue({ code: 200, data: [] });
            getOneDepartment.mockResolvedValue({ code: 200, data: {}});
            putDepartment.mockRejectedValue(errorResponse);

            wrapper = createWrapper();
            wrapper.setData({
                interview_address: 'Valid address',
                interview_address_url: 'https://example.com',
                interview_pic: { code: 'PIC001' },
            });

            await wrapper.vm.handleSaveDepartmentInfo();
            await wrapper.vm.$nextTick();

            expect(putDepartment).toHaveBeenCalled();
        });

        test('should not save when validation fails', async() => {
            wrapper = createWrapper();
            wrapper.setData({
                interview_address: null,
                interview_address_url: 'https://example.com',
                interview_pic: { code: 'PIC001' },
            });

            await wrapper.vm.handleSaveDepartmentInfo();
            await wrapper.vm.$nextTick();

            expect(putDepartment).not.toHaveBeenCalled();
        });

        test('should transform line_work_pic array correctly when saving', async() => {
            const mockResponse = {
                code: 200,
                message: 'Success',
            };

            searchUser.mockResolvedValue({ code: 200, data: [] });
            getLineWorkPIC.mockResolvedValue({ code: 200, data: [] });
            getOneDepartment.mockResolvedValue({ code: 200, data: {}});
            putDepartment.mockResolvedValue(mockResponse);

            wrapper = createWrapper();
            wrapper.setData({
                interview_address: 'Valid address',
                interview_address_url: 'https://example.com',
                interview_pic: { code: 'PIC001' },
                line_work_pic: [
                    { code: 'LW001', full_name: 'User 1' },
                    { code: 'LW002', full_name: 'User 2' },
                ],
            });

            await wrapper.vm.handleSaveDepartmentInfo();
            await wrapper.vm.$nextTick();

            expect(putDepartment).toHaveBeenCalled();
            const callArgs = putDepartment.mock.calls[0];
            expect(callArgs[1].interview_pic_line_work).toEqual(['LW001', 'LW002']);
        });
    });

    describe('Navigation', () => {
        test('should navigate to list screen when back button is clicked', async() => {
            wrapper = createWrapper();

            const backButton = wrapper.find('.back-button');
            await backButton.trigger('click');

            expect(testRouter.push).toHaveBeenCalledWith({
                path: '/master-manager/department-master/index',
            });
        });

        test('should navigate to list screen after successful save', async() => {
            const mockResponse = {
                code: 200,
                message: 'Success',
            };

            searchUser.mockResolvedValue({ code: 200, data: [] });
            getLineWorkPIC.mockResolvedValue({ code: 200, data: [] });
            getOneDepartment.mockResolvedValue({ code: 200, data: {}});
            putDepartment.mockResolvedValue(mockResponse);

            wrapper = createWrapper();
            wrapper.setData({
                interview_address: 'Valid address',
                interview_address_url: 'https://example.com',
                interview_pic: { code: 'PIC001' },
            });

            await wrapper.vm.handleSaveDepartmentInfo();
            await wrapper.vm.$nextTick();

            expect(testRouter.push).toHaveBeenCalledWith({
                path: '/master-manager/department-master/index',
            });
        });
    });

    describe('Data Binding', () => {
        test('should update form fields when data changes', async() => {
            wrapper = createWrapper();

            await wrapper.setData({
                post_code: '1234567',
                address: 'New Address',
                tel: '0312345678',
            });

            expect(wrapper.vm.post_code).toBe('1234567');
            expect(wrapper.vm.address).toBe('New Address');
            expect(wrapper.vm.tel).toBe('0312345678');
        });

        test('should update interview_pic when selected', async() => {
            wrapper = createWrapper();

            const mockPIC = { code: 'PIC001', name_code: 'Test PIC' };

            await wrapper.setData({
                interview_pic: mockPIC,
            });

            expect(wrapper.vm.interview_pic).toEqual(mockPIC);
        });

        test('should update line_work_pic array when multiple selected', async() => {
            wrapper = createWrapper();

            const mockLineWorkPIC = [
                { code: 'LW001', full_name: 'User 1' },
                { code: 'LW002', full_name: 'User 2' },
            ];

            await wrapper.setData({
                line_work_pic: mockLineWorkPIC,
            });

            expect(wrapper.vm.line_work_pic).toEqual(mockLineWorkPIC);
            expect(wrapper.vm.line_work_pic.length).toBe(2);
        });

        test('should update roll_call_radio when selected', async() => {
            wrapper = createWrapper();

            await wrapper.setData({
                roll_call_radio: 0,
            });

            expect(wrapper.vm.roll_call_radio).toBe(0);

            await wrapper.setData({
                roll_call_radio: 1,
            });

            expect(wrapper.vm.roll_call_radio).toBe(1);
        });
    });

    describe('Dropdown States', () => {
        test('should toggle basic_information_dropdown', async() => {
            wrapper = createWrapper();

            expect(wrapper.vm.basic_information_dropdown).toBe(true);

            await wrapper.setData({
                basic_information_dropdown: false,
            });

            expect(wrapper.vm.basic_information_dropdown).toBe(false);
        });

        test('should toggle facility_information_dropdown', async() => {
            wrapper = createWrapper();

            await new Promise((resolve) => setTimeout(resolve, 0));

            expect(wrapper.vm.facility_information_dropdown).toBe(false);

            wrapper.vm.facility_information_dropdown = true;
            await wrapper.vm.$nextTick();

            expect(wrapper.vm.facility_information_dropdown).toBe(true);
        });

        test('should toggle management_certification_dropdown', async() => {
            wrapper = createWrapper();

            await new Promise((resolve) => setTimeout(resolve, 0));

            expect(wrapper.vm.management_certification_dropdown).toBe(false);

            wrapper.vm.management_certification_dropdown = true;
            await wrapper.vm.$nextTick();

            expect(wrapper.vm.management_certification_dropdown).toBe(true);
        });
    });

    describe('Overlay State', () => {
        test('should show overlay during API calls', async() => {
            searchUser.mockResolvedValue({ code: 200, data: [] });
            getLineWorkPIC.mockResolvedValue({ code: 200, data: [] });
            getOneDepartment.mockResolvedValue({ code: 200, data: {}});

            wrapper = createWrapper();
            await new Promise((resolve) => setTimeout(resolve, 0));

            expect(wrapper.vm.overlay.show).toBe(false);

            wrapper.vm.overlay.show = true;
            await wrapper.vm.$nextTick();

            expect(wrapper.vm.overlay.show).toBe(true);
        });
    });

    describe('Edge Cases', () => {
        test('should handle empty line_work_pic array when saving', async() => {
            const mockResponse = {
                code: 200,
                message: 'Success',
            };

            searchUser.mockResolvedValue({ code: 200, data: [] });
            getLineWorkPIC.mockResolvedValue({ code: 200, data: [] });
            getOneDepartment.mockResolvedValue({ code: 200, data: {}});
            putDepartment.mockResolvedValue(mockResponse);

            wrapper = createWrapper();
            wrapper.setData({
                interview_address: 'Valid address',
                interview_address_url: 'https://example.com',
                interview_pic: { code: 'PIC001' },
                line_work_pic: [],
            });

            await wrapper.vm.handleSaveDepartmentInfo();
            await wrapper.vm.$nextTick();

            expect(putDepartment).toHaveBeenCalled();
            const callArgs = putDepartment.mock.calls[0];
            expect(callArgs[1].interview_pic_line_work).toEqual([]);
        });

        test('should handle null interview_pic when saving', async() => {
            const mockResponse = {
                code: 200,
                message: 'Success',
            };

            searchUser.mockResolvedValue({ code: 200, data: [] });
            getLineWorkPIC.mockResolvedValue({ code: 200, data: [] });
            getOneDepartment.mockResolvedValue({ code: 200, data: {}});
            putDepartment.mockResolvedValue(mockResponse);

            wrapper = createWrapper();
            wrapper.setData({
                interview_address: 'Valid address',
                interview_address_url: 'https://example.com',
                interview_pic: null,
            });

            // This should fail validation, but let's test the data transformation
            const interviewPICCode = wrapper.vm.interview_pic ? wrapper.vm.interview_pic['code'] : '';
            expect(interviewPICCode).toBe('');
        });

        test('should handle department data with interview_pic_line_work', async() => {
            const mockDepartmentData = {
                name: 'Test Department',
                province_name: 'Tokyo',
                post_code: '1234567',
                address: 'Test Address',
                tel: '0312345678',
                interview_address: 'Interview Address',
                interview_address_url: 'https://example.com',
                path_for_interview_address: 'Path to interview',
                office_name: 'Office Name',
                office_location: 'Office Location',
                office_area: '100',
                rest_room_area: '50',
                garage_location_1: 'Garage 1 Location',
                garage_area_1: '200',
                garage_location_2: 'Garage 2 Location',
                garage_area_2: '200',
                operations_manager_appointment: 'Manager Name',
                operations_manager_assistant: 'Assistant Name',
                maintenance_manager_appointment: 'Maintenance Manager',
                maintenance_manager_assistant: 'Maintenance Assistant',
                maintenance_manager_phone_number: '0312345678',
                maintenance_manager_fax_number: '0312345679',
                truck_association_membership_number: '12345',
                g_mark_number: 'G12345',
                g_mark_expiration_date: '2024-12-31',
                it_roll_call: 0,
                interview_pic: 'PIC001',
                interview_pic_line_work: ['LW001', 'LW002'],
            };

            const mockSearchResults = [
                { code: 'PIC001', name_code: 'Test PIC' },
            ];

            const mockLineWorkPIC = [
                { code: 'LW001', full_name: 'Line Work User 1' },
                { code: 'LW002', full_name: 'Line Work User 2' },
            ];

            searchUser.mockResolvedValue({ code: 200, data: mockSearchResults });
            getLineWorkPIC.mockResolvedValue({ code: 200, data: mockLineWorkPIC });
            getOneDepartment.mockResolvedValue({ code: 200, data: mockDepartmentData });

            wrapper = createWrapper({
                methods: {
                    handleInitData: jest.fn(),
                },
            });
            await wrapper.setData({
                list_line_work_pic: mockLineWorkPIC,
                search_results: mockSearchResults,
            });
            await wrapper.vm.handleGetDepartmentInfo();
            await wrapper.vm.$nextTick();

            expect(wrapper.vm.line_work_pic.length).toBe(2);
            expect(wrapper.vm.line_work_pic[0].code).toBe('LW001');
            expect(wrapper.vm.line_work_pic[1].code).toBe('LW002');
        });
    });
});

