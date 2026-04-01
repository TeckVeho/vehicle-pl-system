<template>
	<div>
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
				<DataList
					:v-fields="vFields"
					:v-items="vItems"
					:v-current-page="pagination.vCurrentPage"
					:v-per-page="pagination.vPerPage"
					:v-total-rows="pagination.vTotalRows"
				/>
			</b-col>
		</b-overlay>
	</div>
</template>

<script>
const urlApi = {
    gestDataList: '/data',
};

import { getDataList } from '@/api/modules/dataList';
import DataList from '@/components/template/DataList';
import { cleanObj } from '@/utils/handleObj';
import { obj2Path } from '@/utils/obj2Path';

export default {
    name: 'DataListIndex',
    components: {
        DataList,
    },
    data() {
        return {
            vItems: [],
            pagination: {
                vCurrentPage: 1,
                vPerPage: 20,
                vTotalRows: 0,
            },
            isFilter: this.$store.getters.filterDataList || {
                status: false,
                value: '',
            },
            filterQuery: {
                order_column: '',
                order_type: '',
            },
            overlay: {
                show: false,
                variant: 'light',
                opacity: 1,
                blur: '1rem',
                rounded: 'sm',
            },
        };
    },
    computed: {
        vFields() {
            return [
                { key: 'id', sortable: true, label: this.$t('DATA_LIST_DATA_ID'), class: 'data_id' },
                { key: 'name', sortable: true, label: this.$t('DATA_LIST_DATA_NAME'), class: 'data_name' },
                { key: 'from', sortable: true, label: this.$t('DATA_LIST_FROM'), class: 'from' },
                { key: 'to', sortable: false, label: this.$t('DATA_LIST_TO'), class: 'to' },
                { key: 'remark', sortable: false, label: this.$t('DATA_LIST_REMARK'), class: 'remark' },
                { key: 'result', sortable: false, label: '', class: 'result' },
            ];
        },
    },
    watch: {
        filterQuery: {
            handler() {
                this.handleGetDataList();
            },
            deep: true,
        },
    },
    created() {
        this.handleGetDataList();

        this.$bus.on('inputDataNameInDataListFilterChange', (value) => {
            this.isFilter.value = value;
        });

        this.$bus.on('doApplyDataListFilter', () => {
            this.handleSaveFilter(this.isFilter);
            this.pagination.vCurrentPage = 1;
            this.handleGetDataList();
        });

        this.$bus.on('filterDataList', (value) => {
            this.isFilter.status = value;
        });

        this.$bus.on('pageDataListChange', (value) => {
            this.pagination.vCurrentPage = value;
            this.handleGetDataList();
        });

        this.$bus.on('sendFilterQueryDataList', (value) => {
            this.filterQuery = value;
        });
    },
    destroyed() {
        this.$bus.off('inputDataNameInDataListFilterChange');
        this.$bus.off('doApplyDataListFilter');
        this.$bus.off('filterDataList');
        this.$bus.off('pageDataListChange');
        this.$bus.off('sendFilterQueryDataList');
    },
    methods: {
        async handleGetDataList() {
            this.overlay.show = true;

            let URL = {
                page: this.pagination.vCurrentPage,
                per_page: this.pagination.vPerPage,
                sortby: this.filterQuery.order_column,
                sorttype: this.filterQuery.order_type.toLowerCase(),
            };

            if (this.isFilter.status) {
                URL.search = this.isFilter.value;
            }

            URL = cleanObj(URL);

            URL = `${urlApi.gestDataList}?${obj2Path(URL)}`;

            await getDataList(URL)
                .then((res) => {
                    this.vItems = res.data.result;
                    this.pagination.vCurrentPage = res.data.pagination.current_page;
                    this.pagination.vPerPage = res.data.pagination.per_page;
                    this.pagination.vTotalRows = res.data.pagination.total_records;
                })
                .catch(() => {
                    this.vItems = [];

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
            this.$store.dispatch('filter/setFilterDataList', filter);
        },
    },
};
</script>

<style lang="scss" scoped>
    ::v-deep th.data_name {
        min-width: 350px !important;
    }

    ::v-deep th.from {
        min-width: 150px !important;
    }

    ::v-deep th.to {
        min-width: 150px !important;
    }

    ::v-deep th.remark {
        width: 350px !important;
    }
</style>
