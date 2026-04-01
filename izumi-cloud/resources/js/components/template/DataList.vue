<template>
	<div class="data-list">
		<!-- PAGE HEADER -->
		<vHeaderPage class="data-list__title-header">{{ $t('PAGE_TITLE.DATA_LIST') }}</vHeaderPage>

		<!-- PAGE FILTER -->
		<DataListFilter class="data-list__filter" />

		<!-- PAGE TABLE -->
		<TableDataList
			:id="'table-data-list'"
			:fields="vFields"
			:items="vItems"
			:current-page="vCurrentPage"
			:path="'/data-manager/data-list/detail'"
		/>

		<!-- PAGE PAGINATION -->
		<div v-if="vTotalRows > 20" class="data-list__pagination">
			<vPagination
				:aria-controls="'table-data-list'"
				:current-page="vCurrentPage"
				:per-page="vPerPage"
				:total-rows="vTotalRows"
				:next-class="'next'"
				:prev-class="'prev'"
				@currentPageChange="getCurrentPage"
			/>
		</div>
	</div>
</template>

<script>
import vHeaderPage from '@/components/atoms/vHeaderPage';
import DataListFilter from '@/components/organisms/DataListFilter';
import TableDataList from '@/components/organisms/TableDataList';
import vPagination from '@/components/atoms/vPagination';

export default {
    name: 'DataListTemplate',
    components: {
        vHeaderPage,
        DataListFilter,
        TableDataList,
        vPagination,
    },
    props: {
        vFields: {
            type: Array,
            require: true,
            default: function() {
                return [];
            },
            validate: value => {
                return typeof value === 'object';
            },
        },
        vItems: {
            type: Array,
            require: true,
            default: function() {
                return [];
            },
            validate: value => {
                return typeof value === 'object';
            },
        },
        vCurrentPage: {
            type: Number,
            require: true,
            default: 1,
            validate: value => {
                return value > 0;
            },
        },
        vPerPage: {
            type: Number,
            require: true,
            default: 20,
            validate: value => {
                return value > 0;
            },
        },
        vTotalRows: {
            type: Number,
            require: true,
            default: 0,
            validate: value => {
                return value >= 0;
            },
        },
    },
    methods: {
        getCurrentPage(value) {
            this.$bus.emit('pageDataListChange', value);
        },
    },
};
</script>

<style lang="scss" scoped>
.data-list {
	overflow: hidden;

	min-height: calc(100vh - 89px);

	&__title-header,
	&__filter {
		margin-bottom: 20px;
	}

	&__pagination {
		display: flex;
		justify-content: center;
	}
}
</style>
