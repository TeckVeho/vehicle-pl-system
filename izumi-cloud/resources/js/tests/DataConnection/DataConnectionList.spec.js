import { mount, createLocalVue } from '@vue/test-utils';
import store from '@/store';
import router from '@/router';
import DataConnectionList from '@/pages/DataConnect/List';

describe('TEST COMPONENT DATA LIST - LIST', () => {
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

    test('Test component render data component correct', () => {
        const localVue = createLocalVue();
        const wrapper = mount(DataConnectionList, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
        });

        const isFilter = {
            final_transfer_time: {
                status: false,
                from: '',
                to: '',
            },
            connection_data_name: {
                status: false,
                value: '',
            },
        };
        expect(wrapper.vm.isFilter).toEqual(isFilter);

        const items = [];
        expect(wrapper.vm.items).toEqual(items);

        const pagination = {
            currentPage: 1,
            perPage: 20,
            totalRows: 0,
        };
        expect(wrapper.vm.pagination).toEqual(pagination);

        const overlay = {
            show: true,
            variant: 'light',
            opacity: 1,
            blur: '1rem',
            rounded: 'sm',
        };
        expect(wrapper.vm.overlay).toEqual(overlay);

        const filterQuery = {
            order_column: '',
            order_type: '',
        };
        expect(wrapper.vm.filterQuery).toEqual(filterQuery);

        wrapper.destroy();
    });

    test('Test component render Title page', () => {
        const localVue = createLocalVue();
        const wrapper = mount(DataConnectionList, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
        });

        const container = wrapper.find('.data-connect-list__header-page');
        expect(container.exists()).toBe(true);
        expect(container.text()).toEqual('PAGE_TITLE.DATA_CONNECTION');

        wrapper.destroy();
    });

    test('Test component render Overlay', () => {
        const localVue = createLocalVue();
        const wrapper = mount(DataConnectionList, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
        });

        const overlay = wrapper.find('.b-overlay-wrap');
        expect(overlay.exists()).toBe(true);

        wrapper.destroy();
    });

    test('Test component render filter table', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(DataConnectionList, {
            localVue,
            router,
            store,
            mocks,
            stubs: {
                BIcon: true,
            },
        });

        const Container = wrapper.find('.data-connect-list__zone-filter');
        expect(Container.exists()).toBe(true);

        const btnClear = Container.find('.text-clear-all');
        expect(btnClear.exists()).toBe(true);
        expect(btnClear.text()).toEqual('CLEAR_ALL');

        await btnClear.trigger('click');
        const IS_FILTER = {
            final_transfer_time: {
                status: false,
                from: '',
                to: '',
            },
            connection_data_name: {
                status: false,
                value: '',
            },
        };
        expect(wrapper.vm.$bus.emit).toHaveBeenCalledWith('filterDataConnectionList', IS_FILTER);

        const inputFinalTransferTime = Container.find('.filter-final-transfer-time');
        expect(inputFinalTransferTime.exists()).toBe(true);

        const listInputGroupPrepend = inputFinalTransferTime.findAll('.input-group-prepend');
        expect(listInputGroupPrepend.length).toEqual(3);
        expect(listInputGroupPrepend.at(0).find('input[type="checkbox"]').exists()).toBe(true);
        expect(listInputGroupPrepend.at(1).find('.input-group-text').exists()).toBe(true);
        expect(listInputGroupPrepend.at(1).find('.input-group-text').text()).toEqual('DATA_CONNECTION_LIST_FINAL_TRANSFER_TIME');
        expect(listInputGroupPrepend.at(2).find('i').exists()).toBe(true);
        expect(listInputGroupPrepend.at(2).find('i').classes('far')).toBe(true);
        expect(listInputGroupPrepend.at(2).find('i').classes('fa-tilde')).toBe(true);

        const listInputDate = inputFinalTransferTime.findAll('.b-form-datepicker');
        expect(listInputDate.length).toEqual(2);

        const inputConnectionDataName = Container.find('.filter-connection-date-name');
        expect(inputConnectionDataName.exists()).toBe(true);

        const listInputGroupPrependDataName = inputConnectionDataName.findAll('.input-group-prepend');
        expect(listInputGroupPrependDataName.length).toEqual(2);
        expect(listInputGroupPrependDataName.at(0).find('input[type="checkbox"]').exists()).toBe(true);
        expect(listInputGroupPrependDataName.at(1).find('.input-group-text').exists()).toBe(true);
        expect(listInputGroupPrependDataName.at(1).find('.input-group-text').text()).toEqual('DATA_CONNECTION_LIST_CONNECTION_DATA_NAME');

        const inputDataName = inputConnectionDataName.find('input[type="text"]');
        expect(inputDataName.exists()).toBe(true);

        const btnApply = Container.find('button.v-button-default');
        expect(btnApply.exists()).toBe(true);
        expect(btnApply.text()).toEqual('APPLY');

        await btnApply.trigger('click');
        expect(wrapper.vm.$bus.emit).toHaveBeenCalledWith('clickButtonApplyFilterDataConnectionList');

        wrapper.destroy();
    });

    test('Test component render table', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(DataConnectionList, {
            localVue,
            router,
            store,
            mocks,
            stubs: {
                BIcon: true,
            },
        });

        const Container = wrapper.find('.data-connect-list__table');
        expect(Container.exists()).toBe(true);

        const DATA = [
            {
                'id': 1,
                'type': 'active',
                'frequency': 'everyMinute',
                'frequency_between': null,
                'connection_frequency': 'Every Minute',
                'connection_timing': '1St/10:00',
                'final_connect_time': null,
                'final_status': null,
                'data_id': 1,
                'name': 'Dr. Nico Bayer',
                'from': 'ES survey',
                'to': 'ES survey',
            },
        ];
        await wrapper.setData({
            items: DATA,
        });

        const TABLE = Container.find('table');
        expect(TABLE.exists()).toBe(true);

        const TABLE_HEADER = TABLE.find('thead');
        const LIST_TABLE_HEADER = TABLE_HEADER.findAll('th');
        expect(LIST_TABLE_HEADER.length).toEqual(9);

        const LIST_HEADER_TEXT = [
            'DATA_CONNECTION_LIST_FINAL_TRANSFER_TIME (Click to sort ascending)',
            'DATA_CONNECTION_LIST_CONNECTION_DATA_NAME',
            'DATA_CONNECTION_LIST_FROM (Click to sort ascending)',
            'DATA_CONNECTION_LIST_TO',
            'DATA_CONNECTION_LIST_ACTIVE_PASSIVE (Click to sort ascending)',
            'DATA_CONNECTION_LIST_CONNECTION_FREQUENCY (Click to sort ascending)',
            'DATA_CONNECTION_LIST_CONNECTION_TIMING (Click to sort ascending)',
            'DATA_CONNECTION_LIST_STATUS (Click to sort ascending)',
            'DRIVER_RECORDER.TABLE_DETAIL',
        ];
        for (let th = 0; th < LIST_TABLE_HEADER.length; th++) {
            expect(LIST_TABLE_HEADER.at(th).text()).toEqual(LIST_HEADER_TEXT[th]);
        }

        const TABLE_BODY = TABLE.find('tbody');
        const LIST_TABLE_TR = TABLE_BODY.findAll('tr');
        const DATA_DETAIL = [
            '',
            'Dr. Nico Bayer',
            'ES survey',
            'ES survey',
            'ACTIVE.active',
            'Every Minute',
            '1St/10:00',
            '',
            '',
        ];
        for (let tr = 0; tr < LIST_TABLE_TR.length; tr++) {
            const LIST_TD = LIST_TABLE_TR.at(tr).findAll('td');

            for (let td = 0; td < LIST_TD.length; td++) {
                expect(LIST_TD.at(td).text()).toEqual(DATA_DETAIL[td]);
            }
        }

        await wrapper.setData({
            items: [],
        });
        expect(wrapper.find('tbody tr').text()).toEqual('TABLE_EMPTY');

        wrapper.destroy();
    });

    test('Test function handleGetDataConnection call in hook created', async() => {
        const handleGetDataConnection = jest.fn();

        const localVue = createLocalVue();
        const wrapper = mount(DataConnectionList, {
            localVue,
            router,
            store,
            mocks,
            methods: {
                handleGetDataConnection,
            },
            stubs: {
                BIcon: true,
            },
        });

        expect(handleGetDataConnection).toHaveBeenCalled();

        wrapper.destroy();
    });

    test('Test component setup Event bus', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(DataConnectionList, {
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

        expect(wrapper.vm.$bus.off).toHaveBeenCalledWith('filterDataConnectionList');
        expect(wrapper.vm.$bus.off).toHaveBeenCalledWith('clickButtonApplyFilterDataConnectionList');
        expect(wrapper.vm.$bus.off).toHaveBeenCalledWith('pageDataConnectionListChange');
        expect(wrapper.vm.$bus.off).toHaveBeenCalledWith('sendFilterQueryDataConnection');
    });

    test('Test component call function handleGetDataConnection when change page', async() => {
        const localVue = createLocalVue();
        const wrapper = mount(DataConnectionList, {
            localVue,
            router,
            store,
            stubs: {
                BIcon: true,
            },
        });

        const handleGetDataConnection = jest.spyOn(wrapper.vm, 'handleGetDataConnection');
        await wrapper.vm.$bus.emit('pageDataConnectionListChange', 10);
        expect(handleGetDataConnection).toHaveBeenCalled();

        wrapper.destroy();
    });
});
