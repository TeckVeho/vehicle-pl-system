<template>
	<div class="organisms-data-list-filter">
		<vHeaderFilter>
			<template #zone-filter>
				<b-col>
					<b-row>
						<span class="text-clear-all" @click="doClearFilter()">{{ $t('USER_MANAGEMENT.CLEAR_ALL') }}</span>
					</b-row>

					<b-row>
						<b-col cols="12" sm="12" md="12" lg="6" style="padding-left: 0;">
							<div class="zone-input">
								<vInputGroup
									v-model="data"
									:type="'text'"
									:text-prepend="$t('DATA_NAME')"
									:placeholder="''"
									:is-check="isCheck"
									@isChecked="getIsCheckFilter"
								/>
							</div>
						</b-col>
					</b-row>

					<b-row>
						<div class="zone-btn-apply">
							<vButton class="btn-summit-filter" :text-button="$t('APPLY')" @click.native="doApply()" />
						</div>
					</b-row>
				</b-col>
			</template>
		</vHeaderFilter>
	</div>
</template>

<script>
import vHeaderFilter from '@/components/atoms/vHeaderFilter';
import vInputGroup from '@/components/atoms/vInputGroup';
import vButton from '@/components/atoms/vButton';

export default {
    name: 'DataListFilter',
    components: {
        vHeaderFilter,
        vInputGroup,
        vButton,
    },
    data() {
        return {
            data: this.$store.getters.filterDataList.value || '',
            isCheck: this.$store.getters.filterDataList.status || false,
        };
    },
    computed: {
        dataChange() {
            return this.data;
        },
    },
    watch: {
        dataChange() {
            this.setEmitInputDataName();
        },
    },
    methods: {
        doApply() {
            this.$bus.emit('doApplyDataListFilter');
        },
        setEmitInputDataName() {
            this.$bus.emit('inputDataNameInDataListFilterChange', this.data);
        },
        doClearFilter() {
            this.isCheck = false;
            this.data = '';
        },
        getIsCheckFilter(value) {
            if (!value) {
                this.data = '';
            }

            this.isCheck = value;
            this.$bus.emit('filterDataList', value);
        },
    },
};
</script>

<style lang="scss" scoped>
    @import '@/scss/variables.scss';

    .organisms-data-list-filter {
        span.text-clear-all {
            border-top: 1px solid $black;
            border-bottom: 1px solid $black;

            font-weight: 500;

            margin-bottom: 10px;

            cursor: pointer;
        }

        button {
            margin-top: 10px;
        }
    }
</style>
