<template>
	<div class="data-list-detail">
		<!-- PAGE HEADER -->
		<div class="data-list-detail__page-header">
			<vHeaderPage>{{ $t('PAGE_TITLE.DATA_LIST') }}</vHeaderPage>
		</div>

		<!-- FORM INPUT -->
		<div class="data-list-detail__form-input">
			<dataListDetailForm :data-detail="dataDetail" />
		</div>

		<!-- TABLE SAVED DATA LIST -->
		<div class="data-list-detail__saved-data-list">
			<vLabel :text-label="$t('DATA_LIST_SAVED_DATA_LIST')" />
			<TableSavedDataList
				id="table-saved-data-list"
				:fields="fields"
				:items="savedDataList"
				:current-page="savedDataListPagination.page"
			/>
		</div>

		<div v-if="savedDataListPagination.total > 20" class="data-list-detail__pagination">
			<vPagination
				:aria-controls="'table-saved-data-list'"
				:current-page="savedDataListPagination.page"
				:per-page="savedDataListPagination.per_page"
				:total-rows="savedDataListPagination.total"
				@currentPageChange="getCurrentPage"
			/>
		</div>

		<!-- BUTTON BACK -->
		<div class="data-list-detail__btn-back">
			<vButton
				:text-button="$t('DATA_LIST_DETAIL_BACK')"
				:class-name="'v-button-default'"
				@click.native="handleClickButtonBack()"
			/>
		</div>
	</div>
</template>

<script>
import vHeaderPage from '@/components/atoms/vHeaderPage';
import dataListDetailForm from '@/components/organisms/DataListDetailForm';
import vLabel from '@/components/atoms/vLabel';
import TableSavedDataList from '@/components/organisms/TableSavedDataList';
import vPagination from '@/components/atoms/vPagination';
import vButton from '@/components/atoms/vButton';

export default {
    name: 'DataListDetailTemplate',
    components: {
        vHeaderPage,
        dataListDetailForm,
        vLabel,
        TableSavedDataList,
        vButton,
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
                value.every(e => typeof e === 'object');
            },
        },
        dataDetail: {
            type: Object,
            require: true,
            default: function() {
                return {
                    id: '',
                    name: '',
                    from: '',
                    to: '',
                    remark: '',
                };
            },
        },
        savedDataList: {
            type: Array,
            require: true,
            default: function() {
                return [];
            },
            validate: value => {
                value.every(e => typeof e === 'object');
            },
        },
        savedDataListPagination: {
            type: Object,
            require: true,
            default: function() {
                return {
                    page: 1,
                    per_page: 20,
                    total: 0,
                };
            },
        },
    },
    methods: {
        handleClickButtonBack() {
            this.$bus.emit('clickButtonBackDataListDetail');
        },
        getCurrentPage(value) {
            this.$bus.emit('pageDataListDetailChange', value);
        },
    },
};
</script>

<style lang="scss" scoped>
.data-list-detail {
  min-height: calc(100vh - 89px);

  &__page-header {
    margin-bottom: 10px;
  }

  &__form-input {
    margin-bottom: 10px;
  }

  &__pagination {
    display: flex;
    justify-content: center;
  }
}
</style>
