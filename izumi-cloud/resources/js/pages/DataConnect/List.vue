<template>
	<b-overlay
		:show="overlay.show"
		:variant="overlay.variant"
		:opacity="overlay.opacity"
		:blur="overlay.blur"
		:rounded="overlay.sm"
	>
		<!-- Template overlay -->
		<template #overlay>
			<div class="text-center">
				<b-icon icon="arrow-clockwise" font-scale="3" animation="spin" />
				<p style="margin-top: 10px;">{{ $t('PLEASE_WAIT') }}</p>
			</div>
		</template>
		<b-col>
			<DataConnectListTemplate
				:fields="fields"
				:items="items"
				:current-page="pagination.currentPage"
				:per-page="pagination.perPage"
				:total-rows="pagination.totalRows"
			/>
		</b-col>
	</b-overlay>
</template>

<script>
const urlApi = {
    getDataConnection: '/data_connection',
};
import { getDataConnection } from '@/api/modules/dataConnection';
import { cleanObj } from '@/utils/handleObj';
import { obj2Path } from '@/utils/obj2Path';
import DataConnectListTemplate from '@/components/template/DataConnectionList';

export default {
    name: 'DataConnectIndex',
    components: {
        DataConnectListTemplate,
    },
    data() {
        return {
            isFilter: this.$store.getters.filterDataConnect || {
                final_transfer_time: {
                    status: false,
                    from: '',
                    to: '',
                },
                connection_data_name: {
                    status: false,
                    value: '',
                },
            },

            items: [],

            pagination: {
                currentPage: 1,
                perPage: 20,
                totalRows: 0,
            },

            overlay: {
                show: false,
                variant: 'light',
                opacity: 1,
                blur: '1rem',
                rounded: 'sm',
            },

            filterQuery: {
                order_column: '',
                order_type: '',
            },
        };
    },
    computed: {
        fields() {
            return [
                { key: 'final_connect_time', sortable: true, label: this.$t('DATA_CONNECTION_LIST_FINAL_TRANSFER_TIME'), class: 'final_transfer_time' },
                { key: 'name', sortable: false, label: this.$t('DATA_CONNECTION_LIST_CONNECTION_DATA_NAME'), class: 'connection_data_name' },
                { key: 'from', sortable: true, label: this.$t('DATA_CONNECTION_LIST_FROM'), class: 'from' },
                { key: 'to', sortable: false, label: this.$t('DATA_CONNECTION_LIST_TO'), class: 'to' },
                { key: 'type', sortable: true, label: this.$t('DATA_CONNECTION_LIST_ACTIVE_PASSIVE'), class: 'active_passive' },
                { key: 'connection_frequency', sortable: true, label: this.$t('DATA_CONNECTION_LIST_CONNECTION_FREQUENCY'), class: 'connection_frequency' },
                { key: 'connection_timing', sortable: true, label: this.$t('DATA_CONNECTION_LIST_CONNECTION_TIMING'), class: 'connection_timing' },
                { key: 'final_status', sortable: true, label: this.$t('DATA_CONNECTION_LIST_STATUS'), class: 'status' },
                { key: 'result', sortable: false, label: '', class: 'result' },
            ];
        },
        environment() {
            return process.env.MIX_APP_ENV;
        },
    },
    watch: {
        filterQuery: {
            handler() {
                this.handleGetDataConnection();
            },
            deep: true,
        },
    },
    created() {
        this.handleListenPusher();
        this.handleGetDataConnection();

        this.$bus.on('filterDataConnectionList', (value) => {
            this.isFilter = value;
        });

        this.$bus.on('clickButtonApplyFilterDataConnectionList', () => {
            this.handleSaveFilter(this.isFilter);
            this.pagination.currentPage = 1;
            this.handleGetDataConnection();
        });

        this.$bus.on('pageDataConnectionListChange', (value) => {
            this.pagination.currentPage = value;
            this.handleGetDataConnection();
        });

        this.$bus.on('sendFilterQueryDataConnection', value => {
            this.filterQuery = value;
        });
    },
    destroyed() {
        this.$bus.off('filterDataConnectionList');
        this.$bus.off('clickButtonApplyFilterDataConnectionList');
        this.$bus.off('pageDataConnectionListChange');
        this.$bus.off('sendFilterQueryDataConnection');
    },
    methods: {
        handleListenPusher() {
            window.Echo.channel('data_connection_channel').listen('.data_connection_event', (message) => {
                const DATA = message.content;
                const APP_ENV = message.app_env;

                if (APP_ENV === this.environment) {
                    if (DATA) {
                        if (DATA['id'] !== undefined) {
                            if (DATA['id']) {
                                const ID = DATA['id'];

                                for (let index = 0; index < this.items.length; index++) {
                                    if (this.items[index]['id'] === ID) {
                                        this.items[index]['final_connect_time'] = DATA['final_connect_time'];
                                        this.items[index]['final_status'] = DATA['final_status'];
                                    }
                                }
                            } else {
                                this.$toast.danger({
                                    content: this.$t('TOAST_HAVE_ERROR'),
                                });
                            }
                        } else {
                            this.$toast.danger({
                                content: this.$t('TOAST_HAVE_ERROR'),
                            });
                        }
                    } else {
                        this.$toast.danger({
                            content: this.$t('TOAST_HAVE_ERROR'),
                        });
                    }
                }
            });
        },

        async handleGetDataConnection() {
            this.overlay.show = true;

            let URL = {
                page: this.pagination.currentPage,
                per_page: this.pagination.perPage,
                sortby: this.filterQuery.order_column,
                sorttype: this.filterQuery.order_type,
            };

            if (this.isFilter.final_transfer_time.status) {
                URL.start_date = this.isFilter.final_transfer_time.from;
                URL.end_date = this.isFilter.final_transfer_time.to;
            }

            if (this.isFilter.connection_data_name.status) {
                URL.name = this.isFilter.connection_data_name.value;
            }

            URL = cleanObj(URL);

            URL = `${urlApi.getDataConnection}?${obj2Path(URL)}`;

            await getDataConnection(URL)
                .then((res) => {
                    this.items = res.data.result;
                    this.pagination.vCurrentPage = res.data.pagination.current_page;
                    this.pagination.perPage = parseInt(res.data.pagination.per_page);
                    this.pagination.totalRows = res.data.pagination.total_records;
                })
                .catch(() => {
                    this.items = [];

                    try {
                        this.$toast.danger({
                            content: this.$t('TOAST_HAVE_ERROR'),
                        });
                    } catch (e) {
                        // JSDOM teardown / async sau khi test kết thúc
                    }
                });

            this.overlay.show = false;
        },
        handleSaveFilter(filter) {
            this.$store.dispatch('filter/setFilterDataConnect', filter);
        },
    },
};
</script>
