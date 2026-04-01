import { mount, createLocalVue } from '@vue/test-utils';
import store from '@/store';
import router from '@/router';
import DataListIndex from '@/pages/DataList/List';
import DataListFilter from '@/components/organisms/DataListFilter';
import TableDataList from '@/components/organisms/TableDataList';

describe('TEST COMPONENT DATA LIST - LIST', () => {
    test('Test component render data component correct', () => {
        const localVue = createLocalVue();
        const wrapper = mount(DataListIndex, {
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
            vCurrentPage: 1,
            vPerPage: 20,
            vTotalRows: 0,
        };
        expect(wrapper.vm.pagination).toEqual(DataPagination);

        const DataIsFilter = {
            status: false,
            value: '',
        };
        expect(wrapper.vm.isFilter).toEqual(DataIsFilter);

        const DataFilterQuery = {
            order_column: '',
            order_type: '',
        };
        expect(wrapper.vm.filterQuery).toEqual(DataFilterQuery);

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
        const wrapper = mount(DataListIndex, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
        });

        const TitleHeader = wrapper.find('.data-list__title-header');
        expect(TitleHeader.exists()).toBe(true);
        expect(TitleHeader.text()).toEqual('PAGE_TITLE.DATA_LIST');

        wrapper.destroy();
    });

    test('Test component is render Filter Table', () => {
        const localVue = createLocalVue();
        const wrapper = mount(DataListIndex, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
        });

        const FilterTable = wrapper.find('.data-list__filter');
        expect(FilterTable.exists()).toBe(true);

        wrapper.destroy();
    });

    test('Test component render Filter Table', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(DataListFilter, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
        });

        const FilterTable = wrapper.find('.organisms-data-list-filter');
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
        expect(ButtonApply.text()).toBe('APPLY');

        const doApply = jest.spyOn(wrapper.vm, 'doApply');
        await ButtonApply.trigger('click');
        expect(doApply).toHaveBeenCalled();

        wrapper.destroy();
    });

    test('Test function doApply to emit', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(DataListFilter, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
        });

        const ButtonApply = wrapper.find('.zone-btn-apply > button');
        expect(ButtonApply.exists()).toBe(true);
        expect(ButtonApply.text()).toBe('APPLY');

        const doApply = jest.spyOn(wrapper.vm, 'doApply');
        const bus = jest.spyOn(wrapper.vm.$bus, 'emit');
        await ButtonApply.trigger('click');
        expect(doApply).toHaveBeenCalled();
        expect(bus).toHaveBeenCalledWith('doApplyDataListFilter');

        wrapper.destroy();
    });

    test('Test function setEmitInputDataName emit value input Data name', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(DataListFilter, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
        });

        const bus = jest.spyOn(wrapper.vm.$bus, 'emit');
        const Data = 'Izumi';
        await wrapper.setData({ data: Data });
        expect(bus).toHaveBeenCalledWith('inputDataNameInDataListFilterChange', Data);

        wrapper.destroy();
    });

    test('Test function doClearFilter to clear data input', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(DataListFilter, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
        });

        const Data = 'Izumi';
        await wrapper.setData({ data: Data });
        await wrapper.vm.doClearFilter();
        expect(wrapper.vm.data).toEqual('');

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
        const wrapper = mount(DataListIndex, {
            localVue,
            router,
            store,
            mocks,
            stubs: {
                BIcon: true,
            },
        });

        expect(wrapper.vm.$bus.on).toHaveBeenCalledTimes(5);
        wrapper.destroy();
        expect(wrapper.vm.$bus.off).toHaveBeenCalledTimes(5);
    });

    test('Test component call function handleGetDataList when created', () => {
        const handleGetDataList = jest.fn();
        const localVue = createLocalVue();
        const wrapper = mount(DataListIndex, {
            localVue,
            router,
            store,
            methods: {
                handleGetDataList,
            },
            stubs: {
                BIcon: true,
            },
        });

        expect(handleGetDataList).toHaveBeenCalled();

        wrapper.destroy();
    });

    test('Test component call function handleGetDataList when filterQuery change', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(DataListIndex, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
        });

        const handleGetDataList = jest.spyOn(wrapper.vm, 'handleGetDataList');
        await wrapper.setData({ filterQuery: {
            order_column: 'name',
            order_type: 'desc',
        }});
        expect(handleGetDataList).toHaveBeenCalled();

        wrapper.destroy();
    });

    test('Test component call function handleGetDataList when apply filter', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(DataListIndex, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
        });

        const handleGetDataList = jest.spyOn(wrapper.vm, 'handleGetDataList');
        await wrapper.vm.$bus.emit('doApplyDataListFilter');
        expect(handleGetDataList).toHaveBeenCalled();

        wrapper.destroy();
    });

    test('Test component call function handleGetDataList when change page', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(DataListIndex, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
        });

        const handleGetDataList = jest.spyOn(wrapper.vm, 'handleGetDataList');
        await wrapper.vm.$bus.emit('pageDataListChange', 10);
        expect(handleGetDataList).toHaveBeenCalled();

        wrapper.destroy();
    });

    test('Test component render Table Data List', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(DataListIndex, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
        });

        const DATA = [
            {
                'id': '1',
                'name': 'Employee list',
                'from': {
                    'id': 7,
                    'name': '人事奉行',
                    'deleted_at': null,
                    'created_at': '2021-10-19T02:12:10.000000Z',
                    'updated_at': '2021-10-19T02:12:10.000000Z',
                },
                'to': {
                    'id': 7,
                    'name': '人事奉行',
                    'deleted_at': null,
                    'created_at': '2021-10-19T02:12:10.000000Z',
                    'updated_at': '2021-10-19T02:12:10.000000Z',
                },
                'created_at': 1633406812,
                'updated_at': 1633406812,
                'deleted_at': null,
                'remark': '',
            },
            {
                'id': '2',
                'name': 'Salary data get',
                'from': {
                    'id': 7,
                    'name': '人事奉行',
                    'deleted_at': null,
                    'created_at': '2021-10-19T02:12:10.000000Z',
                    'updated_at': '2021-10-19T02:12:10.000000Z',
                },
                'to': {
                    'id': 7,
                    'name': '人事奉行',
                    'deleted_at': null,
                    'created_at': '2021-10-19T02:12:10.000000Z',
                    'updated_at': '2021-10-19T02:12:10.000000Z',
                },
                'created_at': 1633406812,
                'updated_at': 1633406812,
                'deleted_at': null,
                'remark': '',
            },
            {
                'id': '3',
                'name': 'Salary data import',
                'from': {
                    'id': 7,
                    'name': '人事奉行',
                    'deleted_at': null,
                    'created_at': '2021-10-19T02:12:10.000000Z',
                    'updated_at': '2021-10-19T02:12:10.000000Z',
                },
                'to': {
                    'id': 7,
                    'name': '人事奉行',
                    'deleted_at': null,
                    'created_at': '2021-10-19T02:12:10.000000Z',
                    'updated_at': '2021-10-19T02:12:10.000000Z',
                },
                'created_at': 1633406812,
                'updated_at': 1633406812,
                'deleted_at': null,
                'remark': '',
            },
        ];

        await wrapper.setData({ vItems: DATA });

        const TableDataList = wrapper.find('#table-data-list');
        expect(TableDataList.exists()).toBe(true);

        const HeaderTable = wrapper.find('thead');
        const ListHeader = HeaderTable.findAll('th');
        expect(ListHeader.length).toEqual(6);

        const ListHeaderText = [
            'DATA_LIST_DATA_ID (Click to sort ascending)',
            'DATA_LIST_DATA_NAME (Click to sort ascending)',
            'DATA_LIST_FROM (Click to sort ascending)',
            'DATA_LIST_TO',
            'DATA_LIST_REMARK',
            'DRIVER_RECORDER.TABLE_DETAIL',
        ];

        for (let th = 0; th < ListHeader.length; th++) {
            expect(ListHeader.at(th).text()).toEqual(ListHeaderText[th]);
        }

        const TableBody = wrapper.find('tbody');
        const ListRow = TableBody.findAll('tr');
        expect(ListRow.length).toEqual(3);

        for (let tr = 0; tr < ListRow.length; tr++) {
            const TR = ListRow.at(tr);
            const ListTd = TR.findAll('td');

            expect(ListTd.length).toEqual(6);
            expect(ListTd.at(0).text()).toEqual(DATA[tr].id);
            expect(ListTd.at(1).text()).toEqual(DATA[tr].name);
            expect(ListTd.at(2).text()).toContain(DATA[tr].from.name);
            expect(ListTd.at(3).text()).toContain(DATA[tr].to.name);
            expect(ListTd.at(4).text()).toEqual(DATA[tr].remark);
            expect(ListTd.at(5).text()).toEqual('');
        }

        await wrapper.setData({ vItems: [] });
        expect(wrapper.find('#table-data-list tbody tr').text()).toEqual('TABLE_EMPTY');

        wrapper.destroy();
    });

    test('Test component function click to Detail in Table Data List', async() => {
        const FIELDS = [
            { key: 'id', sortable: true, label: 'DATA_LIST_DATA_ID', class: 'data_id' },
            { key: 'name', sortable: true, label: 'DATA_LIST_DATA_NAME', class: 'data_name' },
            { key: 'from', sortable: true, label: 'DATA_LIST_FROM', class: 'from' },
            { key: 'to', sortable: false, label: 'DATA_LIST_TO', class: 'to' },
            { key: 'remark', sortable: false, label: 'DATA_LIST_REMARK', class: 'remark' },
            { key: 'result', sortable: false, label: '', class: 'result' },
        ];

        const DATA = [
            {
                'id': 1,
                'name': 'Employee list',
                'from': 1,
                'to': 2,
                'created_at': 1633406812,
                'updated_at': 1633406812,
                'deleted_at': null,
                'remark': '',
            },
            {
                'id': 2,
                'name': 'Salary data get',
                'from': 1,
                'to': 2,
                'created_at': 1633406812,
                'updated_at': 1633406812,
                'deleted_at': null,
                'remark': '',
            },
            {
                'id': 3,
                'name': 'Salary data import',
                'from': 1,
                'to': 2,
                'created_at': 1633406812,
                'updated_at': 1633406812,
                'deleted_at': null,
                'remark': '',
            },
        ];

        const localVue = createLocalVue();
        const wrapper = mount(TableDataList, {
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

        const goToDetail = jest.spyOn(wrapper.vm, 'goToDetail');
        const BodyTable = wrapper.find('tbody');
        const ListTr = BodyTable.findAll('tr');
        ListTr.at(0).findAll('td').at(5).find('i').trigger('click');
        expect(goToDetail).toHaveBeenCalled();

        wrapper.destroy();
    });
});
