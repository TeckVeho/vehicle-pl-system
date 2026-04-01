<template>
	<vHeaderFilter>
		<template #zone-filter>
			<b-col>
				<b-row>
					<span class="text-clear-all" @click="doClearFilter()">{{ $t('CLEAR_ALL') }}</span>
				</b-row>

				<b-col cols="12" sm="12" md="12" lg="6" style="padding-left: 0;">
					<b-row>
						<b-input-group class="filter-final-transfer-time">
							<b-input-group-prepend is-text>
								<input v-model="isFilter.final_transfer_time.status" class="chk_filter_date" type="checkbox" @change="handleChangeFinalTransferTime">
							</b-input-group-prepend>
							<b-input-group-prepend is-text>
								{{ $t('DATA_CONNECTION_LIST_FINAL_TRANSFER_TIME') }}
							</b-input-group-prepend>
							<b-form-datepicker
								v-model="isFilter.final_transfer_time.from"
								class="filter_date_from"
								:label-no-date-selected="$t('NO_DATE_SELECTED')"
								:label-help="$t('DATE_PICKER_LABEL_HELP')"
								:calendar-width="`290px`"
								:date-format-options="{ year: 'numeric', month: '2-digit', day: '2-digit' }"
								:class="{ 'date_picker': true }"
								type="text"
								placeholder=""
								:locale="language"
								:disabled="!isFilter.final_transfer_time.status"
								:max="isFilter.final_transfer_time.to"
								style="border-right: none;"
							>
								// Icon input
								<template #button-content>
									<i class="fad fa-calendar-day" />
								</template>

								// Month
								<template #nav-prev-month>
									<i class="fas fa-angle-left" />
								</template>

								<template #nav-next-month>
									<i class="fas fa-angle-right" />
								</template>

								// Year
								<template #nav-prev-year>
									<i class="fad fa-angle-double-left" />
								</template>

								<template #nav-next-year>
									<i class="fad fa-angle-double-right" />
								</template>
							</b-form-datepicker>
							<b-input-group-prepend is-text>
								<i class="far fa-tilde" />
							</b-input-group-prepend>
							<b-form-datepicker
								v-model="isFilter.final_transfer_time.to"
								class="filter_date_to"
								:class="{ 'date_picker': true }"
								:label-no-date-selected="$t('NO_DATE_SELECTED')"
								:label-help="$t('DATE_PICKER_LABEL_HELP')"
								:calendar-width="`290px`"
								:date-format-options="{ year: 'numeric', month: '2-digit', day: '2-digit' }"
								type="text"
								placeholder=""
								:min="isFilter.final_transfer_time.from"
								:locale="language"
								:disabled="!isFilter.final_transfer_time.status"
							>
								// Icon input
								<template #button-content>
									<i class="fad fa-calendar-day" />
								</template>

								// Month
								<template #nav-prev-month>
									<i class="fas fa-angle-left" />
								</template>

								<template #nav-next-month>
									<i class="fas fa-angle-right" />
								</template>

								// Year
								<template #nav-prev-year>
									<i class="fad fa-angle-double-left" />
								</template>

								<template #nav-next-year>
									<i class="fad fa-angle-double-right" />
								</template>
							</b-form-datepicker>
						</b-input-group>
					</b-row>
					<b-row>
						<b-input-group class="filter-connection-date-name">
							<b-input-group-prepend is-text>
								<input v-model="isFilter.connection_data_name.status" class="chk_filter_name" type="checkbox" @change="handleChangeConnectionDataName">
							</b-input-group-prepend>
							<b-input-group-prepend is-text>
								{{ $t('DATA_CONNECTION_LIST_CONNECTION_DATA_NAME') }}
							</b-input-group-prepend>
							<b-form-input v-model="isFilter.connection_data_name.value" class="filter_by_name" placeholder="" :disabled="!isFilter.connection_data_name.status" />
						</b-input-group>
					</b-row>
				</b-col>

				<b-row>
					<b-button class="v-button-default btn-summit-filter" @click="doApply()">{{ $t('APPLY') }}</b-button>
				</b-row>
			</b-col>
		</template>
	</vHeaderFilter>
</template>

<script>
import vHeaderFilter from '@/components/atoms/vHeaderFilter';
import { validateYYYYMMDD } from '@/utils/validate';
import { handleChooseDate } from '@/utils/handleDate';

export default {
    name: 'DataConnectionListFilter',
    components: {
        vHeaderFilter,
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

            typeDateChange: this.$store.getters.filterTypeDateChange || '',
        };
    },
    computed: {
        language() {
            return this.$store.getters.language;
        },
        dateFromChange() {
            return this.isFilter.final_transfer_time.from;
        },
        dateToChange() {
            return this.isFilter.final_transfer_time.to;
        },
    },
    watch: {
        isFilter: {
            handler() {
                const DATA = this.handleRemoveDate(
                    this.isFilter.final_transfer_time.from,
                    this.isFilter.final_transfer_time.to,
                    this.typeDateChange
                );

                this.isFilter.final_transfer_time.from = DATA.from;
                this.isFilter.final_transfer_time.to = DATA.to;

                this.$bus.emit('filterDataConnectionList', this.isFilter);
            },
            deep: true,
        },
        dateFromChange() {
            if (validateYYYYMMDD(this.isFilter.final_transfer_time.from)) {
                this.$store.dispatch('filter/setTypeDateChange', 'FROM');
                this.typeDateChange = 'FROM';
            }
        },
        dateToChange() {
            if (validateYYYYMMDD(this.isFilter.final_transfer_time.to)) {
                this.$store.dispatch('filter/setTypeDateChange', 'TO');
                this.typeDateChange = 'TO';
            }
        },
    },
    methods: {
        doClearFilter() {
            const IS_FILTER = {
                final_transfer_time: {
                    status: false,
                    from: '',
                    to: '',
                },
                connection_data_name: {
                    status: false,
                    value: '',
                },
            };

            this.isFilter = IS_FILTER;
        },

        handleRemoveDate(from, to, type) {
            const isCheck = handleChooseDate(
                from,
                to,
                type
            );

            const data = {
                from,
                to,
            };

            switch (isCheck) {
            case -1: {
                data.from = '';
                data.to = '';

                return data;
            }

            case 0: {
                return data;
            }

            case 1: {
                data.from = '';

                return data;
            }

            case 2: {
                data.to = '';

                return data;
            }

            default: {
                data.from = '';
                data.to = '';

                return data;
            }
            }
        },

        doApply() {
            this.$bus.emit('clickButtonApplyFilterDataConnectionList');
        },

        handleChangeFinalTransferTime(event) {
            if (!event.target.checked) {
                this.isFilter.final_transfer_time.from = '';
                this.isFilter.final_transfer_time.to = '';
            }

            this.isFilter.final_transfer_time.status = event.target.checked;
        },

        handleChangeConnectionDataName(event) {
            if (!event.target.checked) {
                this.isFilter.connection_data_name.value = '';
            }

            this.isFilter.connection_data_name.status = event.target.checked;
        },
    },
};
</script>

<style lang="scss" scoped>
    @import '@/scss/variables.scss';

    span.text-clear-all {
        border-top: 1px solid $black;
        border-bottom: 1px solid $black;

        font-weight: 500;

        margin-bottom: 10px;

        cursor: pointer;
    }

    .filter-final-transfer-time {
        margin-bottom: 10px;
    }

    .filter-connection-date-name {
        margin-bottom: 10px;
    }

    .btn {
        min-width: 150px;
        border: none;
    }

    .v-button-default {
        background-color: $west-side !important;

        &:hover {
            opacity: .8;
            background-color: $west-side !important;
        }

        &:active {
            background-color: $west-side !important;
        }

        &:focus {
            background-color: $west-side !important;
        }
    }
</style>
