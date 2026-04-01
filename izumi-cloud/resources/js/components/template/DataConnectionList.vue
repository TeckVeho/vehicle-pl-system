<template>
	<div class="data-connect-list">
		<!-- HEADER PAGE -->
		<div class="data-connect-list__header-page">
			<vHeaderPage>{{ $t('PAGE_TITLE.DATA_CONNECTION') }}</vHeaderPage>
		</div>

		<!-- ZONE FILTER -->
		<div class="data-connect-list__zone-filter">
			<DataConnectionListFilter />
		</div>

		<!-- ZONE TABLE  -->
		<div class="data-connect-list__table">
			<TableDataConnectionList :fields="fields" :items="items" :current-page="currentPage" />
		</div>

		<div v-if="totalRows > 20" class="data-connect-list__pagination">
			<vPagination
				:aria-controls="'table-data-list'"
				:current-page="currentPage"
				:per-page="perPage"
				:total-rows="totalRows"
				:next-class="'next'"
				:prev-class="'prev'"
				@currentPageChange="getCurrentPage"
			/>
		</div>
	</div>
</template>

<script>
import vHeaderPage from '@/components/atoms/vHeaderPage';
import DataConnectionListFilter from '@/components/organisms/DataConnectionListFilter';
import TableDataConnectionList from '@/components/organisms/TableDataConnectionList';
import vPagination from '@/components/atoms/vPagination';

export default {
    name: 'DataConnectListTemplate',
    components: {
        vHeaderPage,
        DataConnectionListFilter,
        TableDataConnectionList,
        vPagination,
    },
    props: {
        fields: {
            type: Array,
            require: true,
            default: function() {
                return [];
            },
            validate: value => {
                return typeof value === 'object';
            },
        },
        items: {
            type: Array,
            require: true,
            default: function() {
                return [];
            },
            validate: value => {
                return typeof value === 'object';
            },
        },
        currentPage: {
            type: Number,
            require: true,
            default: 1,
            validate: value => {
                return value > 0;
            },
        },
        perPage: {
            type: Number,
            require: true,
            default: 20,
            validate: value => {
                return value > 0;
            },
        },
        totalRows: {
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
            this.$bus.emit('pageDataConnectionListChange', value);
        },
    },
};
</script>

<style lang="scss" scoped>
.data-connect-list {
  min-height: calc(100vh - 89px);

  &__header-page {
    margin-bottom: 20px;
  }

  &__zone-filter {
    margin-bottom: 10px;
  }

  &__pagination {
    display: flex;
    justify-content: center;
  }
}
</style>
