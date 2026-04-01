<template>
	<div class="page-connection-detail">
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
				<DataConnectionDetailTemplate :data-form="dataForm" :fields="fields" :items="items" :pagination="pagination" />
			</b-col>
		</b-overlay>
	</div>
</template>

<script>
const urlApi = {
    detailDataConnection: '/data_connection',
    execQueue: '/data_connection/exec-queue',
    downloadFile: '/download-file',
};
import { getDetailDataConnection, execQueue } from '@/api/modules/dataConnection';
import { handlePaginate } from '@/utils/handlePagination';
import DataConnectionDetailTemplate from '@/components/template/DataConnectionDetail';
import { obj2Path } from '@/utils/obj2Path';

export default {
    name: 'DataConnectDetail',
    components: {
        DataConnectionDetailTemplate,
    },
    data() {
        return {
            dataForm: {
                final_data_connection: '',
                connection_data_name: '',
                from: '',
                to: '',
                status_final: '',
                connection_fequency: '',
                connection_timing: '',
                status: '',
            },

            savedItems: [],
            items: [],

            pagination: {
                page: 1,
                per_page: 20,
                total: 0,
            },

            overlay: {
                show: false,
                variant: 'light',
                opacity: 1,
                blur: '1rem',
                rounded: 'sm',
            },

            id: this.$router.currentRoute.params.id,

            file: '',
        };
    },
    computed: {
        fields() {
            return [
                { key: 'created_at', sortable: false, label: this.$t('DATA_CONNECTION_LIST_CONNECTION_DATE'), class: 'saved_date' },
                { key: 'status', sortable: false, label: this.$t('DATA_CONNECTION_LIST_STATUS'), class: 'status' },
                { key: 'file', sortable: false, label: '', class: 'path' },
            ];
        },
        environment() {
            return process.env.MIX_APP_ENV;
        },
    },
    watch: {
        pagination: {
            handler() {
                this.overlay.show = true;
                this.items = handlePaginate(this.savedItems, this.pagination.per_page)[this.pagination.page - 1];
                this.overlay.show = false;
            },
            deep: true,
        },
    },
    created() {
        this.handleListenPusher();
        this.getDetail();

        this.$bus.on('DataConnectionDetailClickModalConfirm', async() => {
            this.handleExecQueue();
        });

        this.$bus.on('pageDataConnectionDetailChange', (value) => {
            this.pagination.page = value;
        });

        this.$bus.on('clickButtonBackDataConnectionDetail', () => {
            this.$router.push({ path: '/data-manager/data-connect/list' });
        });

        this.$bus.on('doDownloadFile', (id) => {
            if (id === -1) {
                this.$toast.warning({
                    content: this.$t('TOAST_DOWNLOAD_FILE_NOT_EXITS'),
                });
            } else {
                this.handleDownload(id);
            }
        });
    },
    destroyed() {
        this.$bus.off('pageDataConnectionDetailChange');
        this.$bus.off('clickButtonBackDataConnectionDetail');
        this.$bus.off('DataConnectionDetailClickModalConfirm');
        this.$bus.off('doDownloadFile');
    },
    methods: {
        handleListenPusher() {
            window.Echo.channel('data_connection_channel').listen('.data_connection_event', (message) => {
                const CONTENT = message.content;
                const DATA_LOG = message.data_log;
                const APP_ENV = message.app_env;

                if (APP_ENV === this.environment) {
                    if (CONTENT) {
                        if (CONTENT['id'] !== undefined) {
                            if (CONTENT.id) {
                                if (CONTENT.id === parseInt(this.id)) {
                                    this.dataForm.final_data_connection = CONTENT['final_connect_time'] || '';
                                    this.dataForm.status = CONTENT['final_status'] || '';

                                    if (DATA_LOG) {
                                        const INDEX_SAVED_ITEMS = this.savedItems.findIndex(item => item['id'] === DATA_LOG['id']);

                                        if (INDEX_SAVED_ITEMS !== -1) {
                                            this.savedItems[INDEX_SAVED_ITEMS]['created_at'] = DATA_LOG['created_at'];
                                            this.savedItems[INDEX_SAVED_ITEMS]['status'] = DATA_LOG['status'];
                                        } else {
                                            this.savedItems.unshift(DATA_LOG);
                                            this.items = handlePaginate(this.savedItems, this.pagination.per_page)[this.pagination.page - 1];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            });
        },

        async getDetail() {
            this.overlay.show = true;
            const URL = `${urlApi.detailDataConnection}/${this.id}`;

            await getDetailDataConnection(URL)
                .then((res) => {
                    this.dataForm.final_data_connection = res.data.data_connection.final_connect_time || '';
                    this.dataForm.connection_data_name = res.data.data_connection.name || '';
                    this.dataForm.from = res.data.data_connection.from || '';
                    this.dataForm.to = res.data.data_connection.to || '';
                    this.dataForm.status_final = res.data.data_connection.type || '';
                    this.dataForm.connection_fequency = res.data.data_connection.connection_frequency || '';
                    this.dataForm.connection_timing = res.data.data_connection.connection_timing || '';
                    this.dataForm.status = res.data.data_connection.final_status || '';

                    this.savedItems = res.data.data_log || [];
                    this.pagination.total = this.savedItems.length;
                })
                .catch(() => {
                    this.$toast.danger({
                        content: this.$t('TOAST_HAVE_ERROR'),
                    });
                });

            this.overlay.show = false;
        },

        async handleExecQueue() {
            this.overlay.show = false;

            const URL = `${urlApi.execQueue}/${this.id}`;

            await execQueue(URL)
                .then(() => {

                })
                .catch(() => {
                    this.$toast.danger({
                        content: this.$t('TOAST_HAVE_ERROR'),
                    });
                });
        },

        async handleDownload(id) {
            const PARAM = {
                item_id: id,
                is_history: 'true',
            };

            const URL = `/api${urlApi.downloadFile}?${obj2Path(PARAM)}`;

            await fetch(URL, {
                headers: {
                    'Accept-Language': this.$store.getters.language,
                    'Authorization': this.$store.getters.token,
                    'accept': 'application/json',
                },
            }).then(async(res) => {
                let filename = res.headers.get('content-disposition').split('filename=')[1] || 'DataList';
                filename = filename.replaceAll('"', '');
                await res.blob().then((res) => {
                    this.file = res;
                });
                const fileURL = window.URL.createObjectURL(this.file);
                const fileLink = document.createElement('a');

                fileLink.href = fileURL;
                fileLink.setAttribute('download', filename);
                document.body.appendChild(fileLink);

                fileLink.click();
            })
                .catch(() => {
                    this.$toast.danger({
                        content: this.$t('TOAST_HAVE_ERROR'),
                    });
                });

            this.file = '';
        },
    },
};
</script>
