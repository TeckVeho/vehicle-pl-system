<template>
	<div class="data-list-detail-form">
		<div class="form-item">
			<vLabel :text-label="$t('DATA_LIST_DETAIL_DATA_ID')" />
			<vInput :value="dataDetail.id ? (dataDetail.id + '') : ''" :type="'text'" :placeholder="''" :readonly="true" />
		</div>

		<div class="form-item">
			<vLabel :text-label="$t('DATA_LIST_DETAIL_DATA_NAME')" />
			<vInput :value="dataDetail.name ? (dataDetail.name + '') : ''" :type="'text'" :placeholder="''" :readonly="true" />
		</div>

		<div class="form-item">
			<vLabel :text-label="$t('DATA_LIST_DETAIL_FROM')" />
			<vInput :value="handleShowFromTo('from')" :type="'text'" :placeholder="''" :readonly="true" />
		</div>

		<div class="form-item">
			<vLabel :text-label="$t('DATA_LIST_DETAIL_TO')" />
			<vInput :value="handleShowFromTo('to')" :type="'text'" :placeholder="''" :readonly="true" />
		</div>

		<div class="form-item">
			<vLabel :text-label="$t('DATA_LIST_DETAIL_REMARK')" />
			<vTextarea :value="dataDetail.remark ? (dataDetail.remark + '') : ''" :type="'text'" :placeholder="''" :readonly="true" />
		</div>
	</div>
</template>

<script>
import vLabel from '@/components/atoms/vLabel';
import vInput from '@/components/atoms/vInput';
import vTextarea from '@/components/atoms/vTextarea';

export default {
    name: 'DataListDetailForm',
    components: {
        vLabel,
        vInput,
        vTextarea,
    },
    props: {
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
    },
    methods: {
        handleShowFromTo(type) {
            switch (type) {
            case 'from': {
                return this.dataDetail.from ? this.dataDetail.from.name : '';
            }

            case 'to': {
                return this.dataDetail.to ? this.dataDetail.to.name : '';
            }

            default :{
                return '';
            }
            }
        },
    },
};
</script>

<style lang="scss" scoped>
    .data-list-detail-form {
        .form-item {
            margin-bottom: 10px;
        }
    }
</style>
