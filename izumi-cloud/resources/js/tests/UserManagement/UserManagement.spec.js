import { mount, createLocalVue } from '@vue/test-utils';
import store from '@/store';
import router from '@/router';
import UserManagementIndex from '@/pages/UserManagement/List';
import UserManagementFilter from '@/components/organisms/UserManagementFilter';
import TableUserManagement from '@/components/organisms/TableUserManagement';

describe('TEST COMPONENT USER MANAGEMENT - INDEX', () => {
    test('Test component render data component correct', () => {
        const localVue = createLocalVue();
        const wrapper = mount(UserManagementIndex, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
        });

        const DataTable = [];
        expect(wrapper.vm.vItems).toEqual(DataTable);

        const DataPagination = {
            current_page: 1,
            per_page: 20,
            total_rows: 0,
        };
        expect(wrapper.vm.pagination).toEqual(DataPagination);

        // #Wait for the api complete to test.
        // const DataIsFilter = {
        //     status: false,
        //     value: '',
        // };
        // expect(wrapper.vm.DataIsFilter).toEqual(DataIsFilter);

        // const DataFilterQuery = {
        //     order_column: '',
        //     order_type: '',
        // };
        // expect(wrapper.vm.DataFilterQuery).toEqual(DataFilterQuery);

        const DataOverlay = {
            show: true,
            variant: 'light',
            opacity: 1,
            blur: '1rem',
            rounded: 'sm',
        };
        expect(wrapper.vm.overlay).toEqual(DataOverlay);

        wrapper.destroy();
    });

    test('Test component render Title Header', () => {
        const localVue = createLocalVue();
        const wrapper = mount(UserManagementIndex, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
        });

        const TitleHeader = wrapper.find('.user-management__title-header');
        expect(TitleHeader.exists()).toBe(true);
        expect(TitleHeader.text()).toEqual(wrapper.vm.$t('PAGE_TITLE.USER_MANAGEMENT'));

        wrapper.destroy();
    });

    test('Test component is render Filter Table', () => {
        const localVue = createLocalVue();
        const wrapper = mount(UserManagementIndex, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
        });

        const FilterTable = wrapper.find('.user-management__filter');
        expect(FilterTable.exists()).toBe(true);

        wrapper.destroy();
    });

    test('Test component render Filter Table', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(UserManagementFilter, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
        });

        const FilterTable = wrapper.find('.organisms-user-management-filter');
        expect(FilterTable.exists()).toBe(true);

        const ButtonClearAll = FilterTable.find('span.text-clear-all');
        expect(ButtonClearAll.exists()).toBe(true);
        expect(ButtonClearAll.text()).toEqual('USER_MANAGEMENT.CLEAR_ALL');

        const doClearFilter = jest.spyOn(wrapper.vm, 'doClearFilter');
        await ButtonClearAll.trigger('click');
        expect(doClearFilter).toHaveBeenCalled();

        const InputDataName = FilterTable.find('.zone-input');
        expect(InputDataName.exists()).toBe(true);

        const ButtonApply = wrapper.find('.zone-btn-apply > button');
        expect(ButtonApply.exists()).toBe(true);
        expect(ButtonApply.text()).toBe('BUTTON.APPLY');

        const doApply = jest.spyOn(wrapper.vm, 'doApply');
        await ButtonApply.trigger('click');
        expect(doApply).toHaveBeenCalled();

        wrapper.destroy();
    });

    test('Test function doApply to emit', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(UserManagementFilter, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
        });

        const ButtonApply = wrapper.find('.zone-btn-apply > button');
        expect(ButtonApply.exists()).toBe(true);
        expect(ButtonApply.text()).toBe('BUTTON.APPLY');

        const doApply = jest.spyOn(wrapper.vm, 'doApply');
        const bus = jest.spyOn(wrapper.vm.$bus, 'emit');
        await ButtonApply.trigger('click');
        expect(doApply).toHaveBeenCalled();
        expect(bus).toHaveBeenCalledWith('doApplyUserManagementFilter');

        wrapper.destroy();
    });

    test('Test function doClearFilter to clear the input of the filter zone', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(UserManagementFilter, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
        });

        const Data = 'Thanh Nghien';
        await wrapper.setData({ userName: Data });
        await wrapper.vm.doClearFilter();
        expect(wrapper.vm.userName).toEqual('');

        wrapper.destroy();
    });

    test('Test component off Event bus when destroyed', () => {
        const mocks = {
            $bus: {
                on: jest.fn(),
                off: jest.fn(),
            },
        };

        const localVue = createLocalVue();
        const wrapper = mount(UserManagementIndex, {
            localVue,
            router,
            store,
            mocks,
            stubs: {
                BIcon: true,
            },
        });

        expect(wrapper.vm.$bus.on).toHaveBeenCalledTimes(10);
        wrapper.destroy();
        expect(wrapper.vm.$bus.off).toHaveBeenCalledTimes(9);
    });

    test('Test component call function getListUserData when created', () => {
        const getListUserData = jest.fn();
        const localVue = createLocalVue();
        const wrapper = mount(UserManagementIndex, {
            localVue,
            router,
            store,
            methods: {
                getListUserData,
            },
            stubs: {
                BIcon: true,
            },
        });

        expect(getListUserData).toHaveBeenCalled();

        wrapper.destroy();
    });

    test('Test component render Table Data List', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(UserManagementIndex, {
            localVue,
            router,
            store,
            mocks: {
                $t: (key) => key,
            },
            stubs: {
                BIcon: true,
            },
        });

        const DATA = [
            {
                'id': 1,
                'name': 'Thanh Nghiên',
                'role': '',
                'created_at': 1633406812,
                'updated_at': 1633406812,
            },
            {
                'id': 2,
                'name': 'Niên Hoa',
                'role': '',
                'created_at': 1633406812,
                'updated_at': 1633406812,
            },
            {
                'id': 3,
                'name': 'Thu Uyên',
                'role': '',
                'created_at': 1633406812,
                'updated_at': 1633406812,
            },
            {
                'id': 4,
                'name': 'Bảo Linh',
                'role': '',
                'created_at': 1633406812,
                'updated_at': 1633406812,
            },
            {
                'id': 5,
                'name': 'Thu Thủy',
                'role': '',
                'created_at': 1633406812,
                'updated_at': 1633406812,
            },
            {
                'id': 6,
                'name': 'Thúy Hằng',
                'role': '',
                'created_at': 1633406812,
                'updated_at': 1633406812,
            },
            {
                'id': 7,
                'name': 'Hồng Linh',
                'role': '',
                'created_at': 1633406812,
                'updated_at': 1633406812,
            },
            {
                'id': 8,
                'name': 'Ngọc Anh',
                'role': '',
                'created_at': 1633406812,
                'updated_at': 1633406812,
            },
            {
                'id': 9,
                'name': 'Thanh Hoàn',
                'role': '',
                'created_at': 1633406812,
                'updated_at': 1633406812,
            },
        ];

        await wrapper.setData({ vItems: DATA });

        const UserListTable = wrapper.find('#table-user-management');
        expect(UserListTable.exists()).toBe(true);

        const TableHeader = wrapper.find('thead');
        const ListHeader = TableHeader.findAll('th');
        expect(ListHeader.length).toEqual(3);

        const ListHeaderText = [
            'USER_MANAGEMENT.USER_ROLE (Click to sort ascending)',
            'USER_MANAGEMENT.EMPLOYEE_NAME (Click to sort ascending)',
            'USER_MANAGEMENT.USER_ID (Click to sort ascending)',
        ];

        for (let th = 0; th < ListHeader.length; th++) {
            expect(ListHeader.at(th).text()).toEqual(ListHeaderText[th]);
        }

        const TableBody = wrapper.find('tbody');
        const ListRow = TableBody.findAll('tr');
        expect(ListRow.length).toEqual(9);

        for (let tr = 0; tr < ListRow.length; tr++) {
            const TR = ListRow.at(tr);
            const ListTD = TR.findAll('td');

            expect(ListTD.length).toEqual(3);
            expect(ListTD.at(0).text()).toEqual(DATA[tr].role + '');
            expect(ListTD.at(1).text()).toEqual(DATA[tr].name + '');
            expect(ListTD.at(2).text()).toEqual(DATA[tr].id + '');
        }

        await wrapper.setData({ vItems: [] });
        expect(wrapper.find('#table-user-management tbody tr').text()).toEqual('TABLE_EMPTY');

        wrapper.destroy();
    });

    test('Test component function click to Detail in Table User Management', async() => {
        const FIELDS = [
            { key: 'role', sortable: true, label: 'USER_MANAGEMENT.USER_ROLE', tdClass: 'text-center', thClass: 'text-center' },
            { key: 'name', sortable: true, label: 'USER_MANAGEMENT.EMPLOYEE_NAME', tdClass: 'text-center', thClass: 'text-center' },
            { key: 'id', sortable: true, label: 'USER_MANAGEMENT.USER_ID', tdClass: 'text-center', thClass: 'text-center' },
            { key: 'edit', sortable: false, label: '', tdClass: 'text-center', thClass: 'text-center' },
            { key: 'remove', sortable: false, label: '', tdClass: 'text-center', thClass: 'text-center' },
        ];

        const DATA = [
            {
                'id': 1,
                'name': 'Thanh Nghiên',
                'role': '',
                'created_at': 1633406812,
                'updated_at': 1633406812,
            },
            {
                'id': 2,
                'name': 'Niên Hoa',
                'role': '',
                'created_at': 1633406812,
                'updated_at': 1633406812,
            },
            {
                'id': 3,
                'name': 'Thu Uyên',
                'role': '',
                'created_at': 1633406812,
                'updated_at': 1633406812,
            },
            {
                'id': 4,
                'name': 'Bảo Linh',
                'role': '',
                'created_at': 1633406812,
                'updated_at': 1633406812,
            },
            {
                'id': 5,
                'name': 'Thu Thủy',
                'role': '',
                'created_at': 1633406812,
                'updated_at': 1633406812,
            },
            {
                'id': 6,
                'name': 'Thúy Hằng',
                'role': '',
                'created_at': 1633406812,
                'updated_at': 1633406812,
            },
            {
                'id': 7,
                'name': 'Hồng Linh',
                'role': '',
                'created_at': 1633406812,
                'updated_at': 1633406812,
            },
            {
                'id': 8,
                'name': 'Ngọc Anh',
                'role': '',
                'created_at': 1633406812,
                'updated_at': 1633406812,
            },
            {
                'id': 9,
                'name': 'Thanh Hoàn',
                'role': '',
                'created_at': 1633406812,
                'updated_at': 1633406812,
            },
        ];

        const localVue = createLocalVue();
        const wrapper = mount(TableUserManagement, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
        });

        await wrapper.setProps({
            fields: FIELDS,
            items: DATA,
        });

        const goToEdit = jest.spyOn(wrapper.vm, 'goToEdit');
        const showModalDelete = jest.spyOn(wrapper.vm, 'showModalDelete');
        const BodyTable = wrapper.find('tbody');
        const ListTr = BodyTable.findAll('tr');
        ListTr.at(0).findAll('td').at(3).find('span').trigger('click');
        ListTr.at(0).findAll('td').at(4).find('span').trigger('click');
        expect(goToEdit).toHaveBeenCalled();
        expect(showModalDelete).toHaveBeenCalled();

        wrapper.destroy();
    });
});

