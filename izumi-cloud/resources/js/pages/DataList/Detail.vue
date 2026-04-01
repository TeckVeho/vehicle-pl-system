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
			<DataListDetailTemplate
				:fields="fields"
				:data-detail="dataDetail"
				:saved-data-list="savedDataList"
				:saved-data-list-pagination="savedDataListPagination"
			/>
		</b-col>
	</b-overlay>
</template>

<script>
const urlApi = {
    getDataListDetail: '/data',
    downloadFile: '/download-file',
};
import { getDataListDetail } from '@/api/modules/dataList';
import DataListDetailTemplate from '@/components/template/DataListDetail';
import { handlePaginate } from '@/utils/handlePagination';
import { obj2Path } from '@/utils/obj2Path';

export default {
    name: 'DataListDetail',
    components: {
        DataListDetailTemplate,
    },
    data() {
        return {
            dataDetail: {},

            savedDataList: [],

            savedDataListPagination: {
                page: 1,
                per_page: 20,
                total: 0,
            },

            id: this.$router.currentRoute.params.id,

            DataList: [],

            overlay: {
                show: false,
                variant: 'light',
                opacity: 1,
                blur: '1rem',
                rounded: 'sm',
            },

            file: null,
        };
    },
    computed: {
        fields() {
            return [
                { key: 'created_at', sortable: false, label: this.$t('DATA_LIST_SAVED_DATE'), class: 'saved_date' },
                { key: 'file', sortable: false, label: '', class: 'path' },
            ];
        },
    },
    watch: {
        savedDataListPagination: {
            handler() {
                this.overlay.show = true;
                this.savedDataList = handlePaginate(this.DataList, this.savedDataListPagination.per_page)[this.savedDataListPagination.page - 1];
                this.overlay.show = false;
            },
            deep: true,
        },
    },
    created() {
        this.handleGetDetail();

        this.$bus.on('clickButtonBackDataListDetail', () => {
            this.$router.push({ path: '/data-manager/data-list/list' });
        });

        this.$bus.on('pageDataListDetailChange', (value) => {
            this.savedDataListPagination.page = value;
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
        this.$bus.off('clickButtonBackDataListDetail');
        this.$bus.off('pageDataListDetailChange');
        this.$bus.off('doDownloadFile');
    },
    methods: {
        async handleGetDetail() {
            this.overlay.show = true;
            const URL = `${urlApi.getDataListDetail}/${this.id}`;

            await getDataListDetail(URL)
                .then((res) => {
                    this.dataDetail = {
                        id: res.data.data.id,
                        name: res.data.data.name,
                        from: res.data.data.from,
                        to: res.data.data.to,
                        remark: res.data.data.remark,
                    };

                    this.DataList = res.data.data_item;

                    this.savedDataListPagination.total = this.DataList.length;
                })
                .catch(() => {
                    this.$toast.danger({
                        content: this.$t('TOAST_HAVE_ERROR'),
                    });
                });

            this.overlay.show = false;
        },

        async handleDownload(id) {
            const PARAM = {
                item_id: id,
                is_history: 'false',
            };

            const URL = `/api${urlApi.downloadFile}?${obj2Path(PARAM)}`;

            await fetch(URL, {
                headers: {
                    'Accept-Language': this.$store.getters.language,
                    'Authorization': this.$store.getters.token,
                    'accept': 'application/json',
                },
            }).then(async(res) => {
                const CONTENT_DISPOSITION = res.headers.get('content-disposition');

                const regex = /filename[^;=utf\n]*=utf-8''(2|[^;\n]*)/;

                const CHECK_NAME = CONTENT_DISPOSITION.match(regex);

                let filename;

                if (CHECK_NAME !== null) {
                    filename = decodeURIComponent(CHECK_NAME[1]);
                } else {
                    filename = res.headers.get('content-disposition').split('filename=')[1] || 'DataList';
                    filename = filename.replaceAll('"', '');
                }

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
