import { mount, createLocalVue } from '@vue/test-utils';
import store from '@/store';
import router from '@/router';
import DataConnectionDetail from '@/pages/DataConnect/Detail';
import DataConnectionDetailTemplate from '@/components/template/DataConnectionDetail';
import TableDataConnectionList from '@/components/organisms/TableDataConnectionList';

describe('TEST COMPONENT DATA CONNECTION - DETAIL', () => {
    afterEach(() => {
        jest.clearAllMocks();
    });

    const mocks = {
        $bus: {
            on: jest.fn(),
            once: jest.fn(),
            off: jest.fn(),
            emit: jest.fn(),
        },
    };

    test('Test component render data correct', () => {
        const localVue = createLocalVue();
        const wrapper = mount(DataConnectionDetail, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
        });

        const DATA_FORM = {
            final_data_connection: '',
            connection_data_name: '',
            from: '',
            to: '',
            status_final: '',
            connection_fequency: '',
            connection_timing: '',
            status: '',
        };
        expect(wrapper.vm.dataForm).toEqual(DATA_FORM);

        const SAVED_ITEMS = [];
        expect(wrapper.vm.savedItems).toEqual(SAVED_ITEMS);

        const ITEMS = [];
        expect(wrapper.vm.items).toEqual(ITEMS);

        const PAGINATION = {
            page: 1,
            per_page: 20,
            total: 0,
        };
        expect(wrapper.vm.pagination).toEqual(PAGINATION);

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

    test('Test component render Header Page', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(DataConnectionDetail, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
        });

        const CONTAINER = wrapper.find('.data-connection-detail__header');
        expect(CONTAINER.exists()).toBe(true);
        expect(CONTAINER.text()).toEqual('PAGE_TITLE.DATA_CONNECTION');

        wrapper.destroy();
    });

    test('Test component render Form Data', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(DataConnectionDetail, {
            localVue,
            router,
            store,
            mocks,
            stubs: {
                BIcon: true,
            },
        });

        const CONTAINER = wrapper.find('.data-connection-detail__form');
        expect(CONTAINER.exists()).toBe(true);

        const LIST_FORM_ITEM = CONTAINER.findAll('.form-item');
        expect(LIST_FORM_ITEM.length).toEqual(8);

        const LIST_LABEL_TEXT = [
            'DATA_CONNECTION_LIST_FINAL_TRANSFER_TIME',
            'DATA_CONNECTION_LIST_CONNECTION_DATA_NAME',
            'DATA_CONNECTION_LIST_FROM',
            'DATA_CONNECTION_LIST_TO',
            'DATA_CONNECTION_LIST_ACTIVE_PASSIVE',
            'DATA_CONNECTION_LIST_CONNECTION_FREQUENCY',
            'DATA_CONNECTION_LIST_CONNECTION_TIMING',
            'DATA_CONNECTION_LIST_STATUS',
        ];

        for (let zone = 0; zone < LIST_FORM_ITEM.length; zone++) {
            const LABEL = LIST_FORM_ITEM.at(zone).find('label');
            expect(LABEL.text()).toEqual(LIST_LABEL_TEXT[zone]);

            const INPUT = LIST_FORM_ITEM.at(zone).find('input');
            expect(INPUT.exists()).toBe(true);
            expect(INPUT.props('type')).toEqual('text');
            expect(INPUT.props('placeholder')).toEqual('');
            expect(INPUT.props('readonly')).toEqual(true);
        }

        wrapper.destroy();
    });

    test('Test component render table', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(TableDataConnectionList, {
            localVue,
            router,
            store,
            mocks,
            stubs: {
                BIcon: true,
            },
        });

        const FIELDS = [
            { key: 'created_at', sortable: false, label: 'DATA_CONNECTION_LIST_CONNECTION_DATE', class: 'saved_date' },
            { key: 'status', sortable: false, label: 'DATA_CONNECTION_LIST_STATUS', class: 'status' },
            { key: 'file', sortable: false, label: '', class: 'path' },
        ];
        const TABLE = wrapper.find('table');
        expect(TABLE.exists()).toBe(true);

        await wrapper.setProps({
            fields: FIELDS,
        });

        const TABLE_HEADER = TABLE.find('thead');
        expect(TABLE_HEADER.exists()).toBe(true);

        const LIST_TH = TABLE_HEADER.findAll('th');
        expect(LIST_TH.length).toEqual(3);
        const LIST_TH_TEXT = [
            'DATA_CONNECTION_LIST_CONNECTION_DATE',
            'DATA_CONNECTION_LIST_STATUS',
            '',
        ];
        for (let th = 0; th < LIST_TH.length; th++) {
            expect(LIST_TH.at(th).text()).toEqual(LIST_TH_TEXT[th]);
        }

        let ITEMS = [

        ];

        await wrapper.setProps({
            items: ITEMS,
        });
        expect(wrapper.find('tbody tr').text()).toEqual('TABLE_EMPTY');

        const TABLE_BODY = TABLE.find('tbody');
        expect(TABLE_BODY.exists()).toBe(true);
        ITEMS = [
            {
                'id': 26,
                'name': 'Mr. Payton DuBuque PhD',
                'data_connection_id': 1,
                'data_id': 1,
                'status': '1',
                'file': '{"file_path": "/download","file_name": "File.csv" }',
                'type': 'automation',
                'file_id': 1,
                'who_uploaded': 111111,
                'created_at': '2021-10-12T16:29:31.000000Z',
                'updated_at': '2021-10-12T16:29:32.000000Z',
            },
            {
                'id': 27,
                'name': 'Isac Witting',
                'data_connection_id': 1,
                'data_id': 1,
                'status': '2',
                'file': '{"file_path": "/download","file_name": "File.csv" }',
                'type': 'automation',
                'file_id': 2,
                'who_uploaded': 111111,
                'created_at': '2021-10-12T16:29:31.000000Z',
                'updated_at': '2021-10-12T16:29:32.000000Z',
            },
        ];
        await wrapper.setProps({
            items: ITEMS,
        });
        const LIST_TR = TABLE_BODY.findAll('tr');
        expect(LIST_TR.length).toEqual(2);
        for (let tr = 0; tr < LIST_TR.length; tr++) {
            const LIST_TD = LIST_TR.at(tr).findAll('td');
            expect(LIST_TD.length).toEqual(3);

            expect(LIST_TD.at(0).text()).toEqual(ITEMS[tr].created_at);
        }

        wrapper.destroy();
    });

    test('Test component render pagination < 20', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(DataConnectionDetail, {
            localVue,
            router,
            store,
            mocks,
            stubs: {
                BIcon: true,
            },
        });

        const CONTAINER = wrapper.find('.data-connection-detail__pagination');
        expect(CONTAINER.exists()).toBe(false);

        wrapper.destroy();
    });

    test('Test component render pagination > 20', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(DataConnectionDetailTemplate, {
            localVue,
            router,
            store,
            mocks,
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
            pagination: Pagination,
        });

        const CONTAINER = wrapper.find('.data-connection-detail__pagination');
        expect(CONTAINER.exists()).toBe(true);

        wrapper.destroy();
    });

    test('Test component render button back', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(DataConnectionDetail, {
            localVue,
            router,
            store,
            mocks,
            stubs: {
                BIcon: true,
            },
        });

        const CONTAINER = wrapper.find('.data-connection-detail__btn-back');
        expect(CONTAINER.exists()).toBe(true);

        const BTN_BACK = CONTAINER.find('button.v-button-default');
        expect(BTN_BACK.exists()).toBe(true);
        expect(BTN_BACK.text()).toEqual('DATA_CONNECTION_LIST_DETAIL_BACK');
        await BTN_BACK.trigger('click');
        expect(wrapper.vm.$bus.emit).toHaveBeenCalledWith('clickButtonBackDataConnectionDetail');

        wrapper.destroy();
    });

    test('Test component run function getDetail in hook Created', async() => {
        const getDetail = jest.fn();
        const localVue = createLocalVue();
        const wrapper = mount(DataConnectionDetail, {
            localVue,
            router,
            store,
            mocks,
            methods: {
                getDetail,
            },
            stubs: {
                BIcon: true,
            },
        });

        expect(getDetail).toHaveBeenCalled();

        wrapper.destroy();
    });

    test('Test function setup event bus', () => {
        const localVue = createLocalVue();
        const wrapper = mount(DataConnectionDetail, {
            localVue,
            router,
            store,
            mocks,
            stubs: {
                BIcon: true,
            },
        });

        expect(wrapper.vm.$bus.on).toHaveBeenCalledTimes(4);

        wrapper.destroy();
        expect(wrapper.vm.$bus.off).toHaveBeenCalledWith('pageDataConnectionDetailChange');
        expect(wrapper.vm.$bus.off).toHaveBeenCalledWith('clickButtonBackDataConnectionDetail');
        expect(wrapper.vm.$bus.off).toHaveBeenCalledWith('DataConnectionDetailClickModalConfirm');
        expect(wrapper.vm.$bus.off).toHaveBeenCalledWith('doDownloadFile');
    });
});
