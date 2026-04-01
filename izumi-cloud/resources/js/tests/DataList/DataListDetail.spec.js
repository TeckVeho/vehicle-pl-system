import { mount, createLocalVue } from '@vue/test-utils';
import store from '@/store';
import router from '@/router';
import DataListDetail from '@/pages/DataList/Detail';
import TableSavedDataList from '@/components/organisms/TableSavedDataList';
import DataListDetailTemplate from '@/components/template/DataListDetail';

describe('TEST COMPONENT DATA LIST - DETAIL', () => {
    test('Test component render data  component correct', () => {
        const localVue = createLocalVue();
        const wrapper = mount(DataListDetail, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
        });

        const DATA_DETAIL = {};
        expect(wrapper.vm.dataDetail).toEqual(DATA_DETAIL);

        const SAVED_DATA_LIST = [];
        expect(wrapper.vm.savedDataList).toEqual(SAVED_DATA_LIST);

        const SAVED_DATA_LIST_PAGINATION = {
            page: 1,
            per_page: 20,
            total: 0,
        };
        expect(wrapper.vm.savedDataListPagination).toEqual(SAVED_DATA_LIST_PAGINATION);

        const DATA_LIST = [];
        expect(wrapper.vm.DataList).toEqual(DATA_LIST);

        const OVERLAY = {
            show: true,
            variant: 'light',
            opacity: 1,
            blur: '1rem',
            rounded: 'sm',
        };
        expect(wrapper.vm.overlay).toEqual(OVERLAY);

        wrapper.destroy();
    });

    test('Test component render Header Page', () => {
        const localVue = createLocalVue();
        const wrapper = mount(DataListDetail, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
        });

        const CONTAINER = wrapper.find('.data-list-detail__page-header');
        expect(CONTAINER.exists()).toBe(true);
        expect(CONTAINER.text()).toEqual('PAGE_TITLE.DATA_LIST');

        wrapper.destroy();
    });

    test('Test component render Form Data', () => {
        const localVue = createLocalVue();
        const wrapper = mount(DataListDetail, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
        });

        const CONTAINER = wrapper.find('.data-list-detail-form');
        expect(CONTAINER.exists()).toBe(true);

        const ListFormItem = CONTAINER.findAll('.form-item');
        expect(ListFormItem.length).toEqual(5);

        const ListLabel = [
            'DATA_LIST_DETAIL_DATA_ID',
            'DATA_LIST_DETAIL_DATA_NAME',
            'DATA_LIST_DETAIL_FROM',
            'DATA_LIST_DETAIL_TO',
            'DATA_LIST_DETAIL_REMARK',
        ];

        const ListComponent = [
            'input',
            'input',
            'input',
            'input',
            'textarea',
        ];

        const ListType = [
            'text',
            'text',
            'text',
            'text',
            'text',
        ];

        for (let item = 0; item < ListFormItem.length; item++) {
            expect(ListFormItem.at(item).find('label').text()).toEqual(ListLabel[item]);
            expect(ListFormItem.at(item).find(ListComponent[item]).exists()).toBe(true);
            expect(ListFormItem.at(item).find(ListComponent[item]).attributes('type')).toBe(ListType[item]);
            expect(ListFormItem.at(item).find(ListComponent[item]).props('placeholder')).toBe('');
            expect(ListFormItem.at(item).find(ListComponent[item]).props('readonly')).toBe(true);
        }

        wrapper.destroy();
    });

    test('Test component render Table', async() => {
        const mocks = {
            $bus: {
                on: jest.fn(),
                once: jest.fn(),
                off: jest.fn(),
                emit: jest.fn(),
            },
        };

        const localVue = createLocalVue();
        const wrapper = mount(TableSavedDataList, {
            localVue,
            router,
            store,
            mocks,
            stubs: {
                BIcon: true,
            },
        });

        const HEADER = [
            { key: 'created_at', sortable: false, label: 'DATA_LIST_DETAIL_SAVED_DATE', class: 'saved_date' },
            { key: 'file', sortable: false, label: '', class: 'path' },
        ];

        const DATA = [
            {
                'id': 26,
                'name': 'Florine Cruickshank',
                'data_connection_id': 1,
                'data_id': 1,
                'status': '3',
                'content': '{"field_01":339,"field_02":"Brannon Hahn","field_03":"Numquam aliquid quae ex odio quibusdam ex. Repellendus totam enim vero. Vel dolores porro ea ab rerum quis. Commodi et nihil est cum atque possimus aperiam.","field_04":"2021-10-13","field_05":"Fake Json Data"}',
                'type': 'automation',
                'file_id': 1,
                'who_uploaded': 111111,
                'created_at': '2021-10-13T10:40:08.000000Z',
                'updated_at': '2021-10-13T10:40:08.000000Z',
                'file': {
                    'id': 1,
                    'file_name': 'demo_test.csv',
                    'file_extension': 'csv',
                    'file_path': 'data_item/20211013_26/demo_test.csv',
                    'file_size': '1048576',
                    'created_at': '2021-10-13T10:40:08.000000Z',
                    'updated_at': '2021-10-13T10:40:08.000000Z',
                },
            },
            {
                'id': 27,
                'name': 'Stephany Moore',
                'data_connection_id': 1,
                'data_id': 1,
                'status': '3',
                'content': '{"field_01":916,"field_02":"Kellen Kunze","field_03":"Consectetur dolor quia et. Dolorem eligendi fuga accusantium aperiam molestias id quibusdam. Iusto repudiandae rerum soluta. Aut omnis autem neque sit.","field_04":"2021-10-13","field_05":"Fake Json Data"}',
                'type': 'automation',
                'file_id': 2,
                'who_uploaded': 111111,
                'created_at': '2021-10-13T10:40:08.000000Z',
                'updated_at': '2021-10-13T10:40:09.000000Z',
                'file': {
                    'id': 2,
                    'file_name': 'demo_test.csv',
                    'file_extension': 'csv',
                    'file_path': 'data_item/20211013_27/demo_test.csv',
                    'file_size': '1048576',
                    'created_at': '2021-10-13T10:40:08.000000Z',
                    'updated_at': '2021-10-13T10:40:08.000000Z',
                },
            },
        ];

        await wrapper.setProps({
            id: 'table-saved-data-list',
            fields: HEADER,
            items: DATA,
            currentPage: 1,
        });

        const TABLE = wrapper.find('#table-saved-data-list');
        expect(TABLE.exists()).toBe(true);

        const TABLE_HEADER = TABLE.find('thead');
        expect(TABLE_HEADER.exists()).toBe(true);

        const LIST_TH = TABLE_HEADER.findAll('th');
        expect(LIST_TH.length).toEqual(2);
        expect(LIST_TH.at(0).text()).toEqual('DATA_LIST_DETAIL_SAVED_DATE');
        expect(LIST_TH.at(1).text()).toEqual('');

        const TABLE_BODY = TABLE.find('tbody');
        expect(TABLE_BODY.exists()).toBe(true);

        const LIST_TR = TABLE_BODY.findAll('tr');
        expect(LIST_TR.length).toEqual(2);

        for (let tr = 0; tr < LIST_TR.length; tr++) {
            const LIST_TD = LIST_TR.at(tr).findAll('td');
            expect(LIST_TD.length).toEqual(2);

            expect(LIST_TD.at(0).text()).toEqual(DATA[tr]['created_at']);
            expect(LIST_TD.at(1).text()).toEqual('DATA_LIST_DETAIL_DOWNLOAD');

            const ButtonDownload = LIST_TD.at(1).find('span');
            await ButtonDownload.trigger('click');

            expect(wrapper.vm.$bus.emit).toHaveBeenCalled();
            expect(wrapper.vm.$bus.emit).toHaveBeenCalledWith('doDownloadFile', 26);
        }

        await wrapper.setProps({
            items: [],
        });
        expect(wrapper.find('#table-saved-data-list tbody tr').text()).toEqual('TABLE_EMPTY');

        wrapper.destroy();
    });

    test('Test component render Pagination has total < 20', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(DataListDetailTemplate, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
        });

        const CONTAINER = wrapper.find('.data-list-detail__pagination');
        expect(CONTAINER.exists()).toBe(false);

        wrapper.destroy();
    });

    test('Test component render Pagination has total > 20', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(DataListDetailTemplate, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
        });

        const Pagination = {
            page: 2,
            per_page: 20,
            total: 45,
        };

        await wrapper.setProps({
            savedDataListPagination: Pagination,
        });
        const CONTAINER = wrapper.find('.data-list-detail__pagination');
        expect(CONTAINER.exists()).toBe(true);

        wrapper.destroy();
    });

    test('Test component run function handleGetDetail in hook Created', async() => {
        const handleGetDetail = jest.fn();
        const localVue = createLocalVue();
        const wrapper = mount(DataListDetail, {
            localVue,
            router,
            store,
            methods: {
                handleGetDetail,
            },
            stubs: {
                BIcon: true,
            },
        });

        expect(handleGetDetail).toHaveBeenCalled();

        wrapper.destroy();
    });

    test('Test component run bus on in hook created and destory', async() => {
        const mocks = {
            $bus: {
                on: jest.fn(),
                off: jest.fn(),
            },
        };

        const localVue = createLocalVue();
        const wrapper = mount(DataListDetail, {
            localVue,
            router,
            store,
            mocks,
            stubs: {
                BIcon: true,
            },
        });

        expect(wrapper.vm.$bus.on).toHaveBeenCalledTimes(3);
        wrapper.destroy();
        expect(wrapper.vm.$bus.off).toHaveBeenCalledTimes(3);
    });
});
