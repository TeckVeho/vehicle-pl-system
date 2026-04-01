<template>
	<div class="data-connection-detail">
		<div class="data-connection-detail__header">
			<vHeaderPage>{{ $t('PAGE_TITLE.DATA_CONNECTION') }}</vHeaderPage>
		</div>

		<div class="data-connection-detail__form">
			<FormDataConnection :data-form="dataForm" />
		</div>

		<div class="data-connection-detail__saved-data-list">
			<vLabel :text-label="$t('DATA_CONNECTION_LIST_LOG_DATA')" />
			<TableSavedDataConnection
				id="table-log-data"
				:fields="fields"
				:items="items"
				:current-page="pagination.page"
			/>
		</div>

		<div
			v-if="pagination.total > 20"
			class="data-connection-detail__pagination"
		>
			<vPagination
				:aria-controls="'table-log-data'"
				:current-page="pagination.page"
				:per-page="pagination.per_page"
				:total-rows="pagination.total"
				@currentPageChange="getCurrentPage"
			/>
		</div>

		<div class="data-connection-detail__btn-back">
			<vButton
				:text-button="$t('DATA_CONNECTION_LIST_DETAIL_BACK')"
				:class-name="'v-button-default'"
				@click.native="handleClickButtonBack()"
			/>
		</div>
	</div>
</template>

<script>
import vHeaderPage from '@/components/atoms/vHeaderPage';
import FormDataConnection from '@/components/organisms/FormDataConnection';
import vLabel from '@/components/atoms/vLabel';
import TableSavedDataConnection from '@/components/organisms/TableSavedDataConnection';
import vPagination from '@/components/atoms/vPagination';
import vButton from '@/components/atoms/vButton';

export default {
    name: 'DataConnectionDetailTemplate',
    components: {
        vHeaderPage,
        FormDataConnection,
        vLabel,
        TableSavedDataConnection,
        vPagination,
        vButton,
    },
    props: {
        dataForm: {
            type: Object,
            require: true,
            default: function() {
                return {
                    final_data_connection: '',
                    connection_data_name: '',
                    from: '',
                    to: '',
                    status_final: '',
                    connection_fequency: '',
                    connection_timing: '',
                    status: '',
                };
            },
        },
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
        items: {
            type: Array,
            require: true,
            default: function() {
                return [];
            },
            validate: value => {
                value.every(e => typeof e === 'object');
            },
        },
        pagination: {
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
        showModal: {
            type: Boolean,
            require: true,
            default: false,
        },
    },
    methods: {
        handleClickButtonBack() {
            this.$bus.emit('clickButtonBackDataConnectionDetail');
        },
        getCurrentPage(value) {
            this.$bus.emit('pageDataConnectionDetailChange', value);
        },
    },
};
</script>

<style lang="scss" scoped>
.data-connection-detail {
    min-height: calc(100vh - 89px);

    &__header {
        margin-bottom: 10px;
    }

    &__pagination {
        display: flex;
        justify-content: center;
    }
}
</style>
