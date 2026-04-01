<template>
	<vHeaderFilter>
		<template #zone-filter>
			<!-- Button Clear Filter -->
			<b-col>
				<b-row>
					<span class="text-clear-all" @click="onClickClearFilter()">{{ $t('BUTTON.CLEAR_ALL') }}</span>
				</b-row>
			</b-col>

			<!-- Filter Accident Date + Department -->
			<b-row>
				<b-col class="reset-padding-left" cols="12" sm="12" md="12" lg="6">
					<div class="item-filter">
						<b-input-group>
							<b-input-group-prepend>
								<b-input-group-text>
									<input
										id="filter-accident-date"
										v-model="isFilter.accidentDate.status"
										type="checkbox"
										name="filter-accident-date"
										@change="onRemoveCheckboxValue($event, 1)"
									>
								</b-input-group-text>
							</b-input-group-prepend>

							<b-input-group-prepend>
								<b-input-group-text class="fixed-group-text">
									<span>{{ $t('DRIVER_RECORDER.FILTER_ACCIDENT_DATE') }}</span>
								</b-input-group-text>
							</b-input-group-prepend>

							<b-form-datepicker
								v-model="isFilter.accidentDate.value"
								:locale="lang"
								:calendar-width="`290px`"
								:label-no-date-selected="$t('NO_DATE_SELECTED')"
								:label-help="$t('DATE_PICKER_LABEL_HELP')"
								:date-format-options="{ month: '2-digit', day: '2-digit' }"
								:disabled="!isFilter.accidentDate.status"
							/>
						</b-input-group>
					</div>
				</b-col>

				<b-col class="reset-padding-left" cols="12" sm="12" md="12" lg="6">
					<div class="item-filter">
						<b-input-group>
							<b-input-group-prepend>
								<b-input-group-text>
									<input
										id="filter-department"
										v-model="isFilter.department.status"
										type="checkbox"
										name="filter-department"
										@change="onRemoveCheckboxValue($event, 2)"
									>
								</b-input-group-text>
							</b-input-group-prepend>

							<b-input-group-prepend>
								<b-input-group-text class="fixed-group-text">
									<span>{{ $t('DRIVER_RECORDER.FILTER_DEPARTMENT') }}</span>
								</b-input-group-text>
							</b-input-group-prepend>

							<b-form-select
								v-model="isFilter.department.value"
								:options="listDepartment"
								:disabled="!isFilter.department.status"
								:value-field="`id`"
								:text-field="`department_name`"
							>
								<template #first>
									<b-form-select-option :value="null">
										{{ $t('PLEASE_SELECT') }}
									</b-form-select-option>
								</template>
							</b-form-select>
						</b-input-group>
					</div>
				</b-col>
			</b-row>

			<!-- Filter Type One + Type Two -->
			<b-row>
				<b-col class="reset-padding-left" cols="12" sm="12" md="12" lg="6">
					<div class="item-filter">
						<b-input-group>
							<b-input-group-prepend>
								<b-input-group-text>
									<input
										id="filter-type"
										v-model="isFilter.type_one.status"
										type="checkbox"
										name="filter-type"
										@change="onRemoveCheckboxValue($event, 5)"
									>
								</b-input-group-text>
							</b-input-group-prepend>

							<b-input-group-prepend>
								<b-input-group-text class="fixed-group-text">
									<span>事故GP</span>
								</b-input-group-text>
							</b-input-group-prepend>

							<b-form-select
								v-model="isFilter.type_one.value"
								:options="type_one_options"
								:disabled="!isFilter.type_one.status"
								:value-field="`value`"
								:text-field="`text`"
							>
								<template #first>
									<b-form-select-option :value="null">
										{{ $t('PLEASE_SELECT') }}
									</b-form-select-option>
								</template>
							</b-form-select>
						</b-input-group>
					</div>
				</b-col>

				<b-col class="reset-padding-left" cols="12" sm="12" md="12" lg="6">
					<div class="item-filter">
						<b-input-group>
							<b-input-group-prepend>
								<b-input-group-text>
									<input
										id="filter-type"
										v-model="isFilter.type_two.status"
										type="checkbox"
										name="filter-type"
										@change="onRemoveCheckboxValue($event, 6)"
									>
								</b-input-group-text>
							</b-input-group-prepend>

							<b-input-group-prepend>
								<b-input-group-text class="fixed-group-text">
									<span>有責無責</span>
								</b-input-group-text>
							</b-input-group-prepend>

							<b-form-select
								v-model="isFilter.type_two.value"
								:options="type_two_options"
								:disabled="!isFilter.type_two.status"
								:value-field="`value`"
								:text-field="`text`"
							>
								<template #first>
									<b-form-select-option :value="null">
										{{ $t('PLEASE_SELECT') }}
									</b-form-select-option>
								</template>
							</b-form-select>
						</b-input-group>
					</div>
				</b-col>
			</b-row>

			<!-- Filter Shipper + Accident Classification -->
			<b-row>
				<b-col class="reset-padding-left" cols="12" sm="12" md="12" lg="6">
					<div class="item-filter">
						<b-input-group>
							<b-input-group-prepend>
								<b-input-group-text>
									<input
										id="filter-type"
										v-model="isFilter.shipper.status"
										type="checkbox"
										name="filter-type"
										@change="onRemoveCheckboxValue($event, 7)"
									>
								</b-input-group-text>
							</b-input-group-prepend>

							<b-input-group-prepend>
								<b-input-group-text class="fixed-group-text">
									<span>荷主</span>
								</b-input-group-text>
							</b-input-group-prepend>

							<b-form-select
								v-model="isFilter.shipper.value"
								:options="shipper_options"
								:disabled="!isFilter.shipper.status"
								:value-field="`value`"
								:text-field="`text`"
							>
								<template #first>
									<b-form-select-option :value="null">
										{{ $t('PLEASE_SELECT') }}
									</b-form-select-option>
								</template>
							</b-form-select>
						</b-input-group>
					</div>
				</b-col>

				<b-col class="reset-padding-left" cols="12" sm="12" md="12" lg="6">
					<div class="item-filter">
						<b-input-group>
							<b-input-group-prepend>
								<b-input-group-text>
									<input
										id="filter-type"
										v-model="isFilter.accident_classification.status"
										type="checkbox"
										name="filter-type"
										@change="onRemoveCheckboxValue($event, 8)"
									>
								</b-input-group-text>
							</b-input-group-prepend>

							<b-input-group-prepend>
								<b-input-group-text class="fixed-group-text">
									<span>事故区分</span>
								</b-input-group-text>
							</b-input-group-prepend>

							<b-form-select
								v-model="isFilter.accident_classification.value"
								:options="accident_classification_options"
								:disabled="!isFilter.accident_classification.status"
								:value-field="`value`"
								:text-field="`text`"
							>
								<template #first>
									<b-form-select-option :value="null">
										{{ $t('PLEASE_SELECT') }}
									</b-form-select-option>
								</template>
							</b-form-select>
						</b-input-group>
					</div>
				</b-col>
			</b-row>

			<!-- Filter Title + Place of Occurrence -->
			<b-row>
				<b-col class="reset-padding-left" cols="12" sm="12" md="12" lg="6">
					<div class="item-filter">
						<b-input-group>
							<b-input-group-prepend>
								<b-input-group-text>
									<input
										id="filter-type"
										v-model="isFilter.place_of_occurrence.status"
										type="checkbox"
										name="filter-type"
										@change="onRemoveCheckboxValue($event, 9)"
									>
								</b-input-group-text>
							</b-input-group-prepend>

							<b-input-group-prepend>
								<b-input-group-text class="fixed-group-text">
									<span>発生場所</span>
								</b-input-group-text>
							</b-input-group-prepend>

							<b-form-select
								v-model="isFilter.place_of_occurrence.value"
								:options="place_of_occurrence_options"
								:disabled="!isFilter.place_of_occurrence.status"
								:value-field="`value`"
								:text-field="`text`"
							>
								<template #first>
									<b-form-select-option :value="null">
										{{ $t('PLEASE_SELECT') }}
									</b-form-select-option>
								</template>
							</b-form-select>
						</b-input-group>
					</div>
				</b-col>

				<b-col class="reset-padding-left" cols="12" sm="12" md="12" lg="6">
					<div class="item-filter">
						<b-input-group>
							<b-input-group-prepend>
								<b-input-group-text>
									<input
										id="filter-title"
										v-model="isFilter.title.status"
										type="checkbox"
										name="filter-title"
										@change="onRemoveCheckboxValue($event, 3)"
									>
								</b-input-group-text>
							</b-input-group-prepend>

							<b-input-group-prepend>
								<b-input-group-text class="fixed-group-text">
									<span>{{ 'タイトル' }}</span>
								</b-input-group-text>
							</b-input-group-prepend>

							<b-form-input v-model="isFilter.title.value" :disabled="!isFilter.title.status" />
						</b-input-group>
					</div>
				</b-col>
			</b-row>

			<b-row>
				<b-col class="reset-padding-left">
					<div class="zone-btn-apply">
						<vButton
							:class="'btn-summit-filter'"
							@click.native="onClickApply()"
						>
							{{ $t('BUTTON.APPLY') }}
						</vButton>
					</div>
				</b-col>
			</b-row>
		</template>
	</vHeaderFilter>
</template>

<script>
import vButton from '@/components/atoms/vButton';
import vHeaderFilter from '@/components/atoms/vHeaderFilter';

export default {
    name: 'FilterListDriverRecorder',
    components: {
        vHeaderFilter,
        vButton,
    },
    props: {
        listDepartment: {
            type: Array,
            required: false,
            default: function() {
                return [];
            },
        },
        listType: {
            type: Array,
            required: false,
            default: function() {
                return [];
            },
        },
    },
    data() {
        return {
            isFilter: {
                accidentDate: {
                    status: false,
                    value: null,
                },
                department: {
                    status: false,
                    value: null,
                },
                title: {
                    status: false,
                    value: null,
                },
                type_one: {
                    status: false,
                    value: null,
                },
                type_two: {
                    status: false,
                    value: null,
                },
                shipper: {
                    status: false,
                    value: null,
                },
                accident_classification: {
                    status: false,
                    value: null,
                },
                place_of_occurrence: {
                    status: false,
                    value: null,
                },
            },

            type_one_options: [
                { value: 1, text: '事故', disabled: false },
                { value: 2, text: 'GP', disabled: false },
                { value: 3, text: 'その他', disabled: false },
            ],

            type_two_options: [
                { value: 1, text: '有責', disabled: false },
                { value: 2, text: '無責', disabled: false },
                { value: 3, text: 'その他', disabled: false },
            ],

            shipper_options: [
                { value: 1, text: '山崎製パン', disabled: false },
                { value: 2, text: 'ヤマ物', disabled: false },
                { value: 3, text: 'サンロジ', disabled: false },
                { value: 4, text: '富士エコー', disabled: false },
                { value: 5, text: 'パスコ', disabled: false },
                { value: 6, text: 'ロジネット', disabled: false },
                { value: 7, text: 'FR', disabled: false },
                { value: 8, text: 'その他', disabled: false },
            ],

            accident_classification_options: [
                { value: 1, text: '接触(物)', disabled: false },
                { value: 2, text: '接触(車)', disabled: false },
                { value: 3, text: '接触(人)', disabled: false },
                { value: 4, text: '追突', disabled: false },
                { value: 5, text: 'バック', disabled: false },
                { value: 6, text: '自損横転', disabled: false },
                { value: 7, text: 'オーバーハング', disabled: false },
                { value: 8, text: '巻込み', disabled: false },
                { value: 9, text: '衝突', disabled: false },
                { value: 10, text: '不明・その他', disabled: false },
            ],

            place_of_occurrence_options: [
                { value: 1, text: '店舗敷地', disabled: false },
                { value: 2, text: '構内', disabled: false },
                { value: 3, text: '一般道路', disabled: false },
                { value: 4, text: '交差点', disabled: false },
                { value: 5, text: '高速道路', disabled: false },
                { value: 6, text: '納品口', disabled: false },
                { value: 7, text: '駐車場', disabled: false },
                { value: 8, text: '不明・その他', disabled: false },
            ],
        };
    },
    computed: {
        lang() {
            return this.$store.getters.language;
        },
    },
    watch: {
        isFilter: {
            handler: function(event) {
                this.handleFilterChange();
            },
            deep: true,
        },
    },
    methods: {
        onClickClearFilter() {
            const DEFAULT_FILTER = {
                accidentDate: {
                    status: false,
                    value: null,
                },
                department: {
                    status: false,
                    value: null,
                },
                title: {
                    status: false,
                    value: null,
                },
                type_one: {
                    status: false,
                    value: null,
                },
                type_two: {
                    status: false,
                    value: null,
                },
                shipper: {
                    status: false,
                    value: null,
                },
                accident_classification: {
                    status: false,
                    value: null,
                },
                place_of_occurrence: {
                    status: false,
                    value: null,
                },
            };

            this.isFilter = DEFAULT_FILTER;
        },
        onClickApply() {
            this.$bus.emit('DRIVER_RECORDER_FILTER_APPLY');
        },
        async onRemoveCheckboxValue(event, index) {
            if (!event.target.checked) {
                if (index === 1) {
                    this.isFilter.accidentDate.status = false;
                    this.isFilter.accidentDate.value = null;
                } else if (index === 2) {
                    this.isFilter.department.status = false;
                    this.isFilter.department.value = null;
                } else if (index === 3) {
                    this.isFilter.title.status = false;
                    this.isFilter.title.value = null;
                } else if (index === 5) {
                    this.isFilter.type_one.status = false;
                    this.isFilter.type_one.value = null;
                } else if (index === 6) {
                    this.isFilter.type_two.status = false;
                    this.isFilter.type_two.value = null;
                } else if (index === 7) {
                    this.isFilter.shipper.status = false;
                    this.isFilter.shipper.value = null;
                } else if (index === 8) {
                    this.isFilter.accident_classification.status = false;
                    this.isFilter.accident_classification.value = null;
                } else if (index === 9) {
                    this.isFilter.place_of_occurrence.status = false;
                    this.isFilter.place_of_occurrence.value = null;
                }
            }
        },
        handleFilterChange() {
            this.$bus.emit('DRIVER_RECORDER_FILTER_DATA', this.isFilter);
        },
    },
};
</script>

<style lang="scss" scoped>
@import '@/scss/variables';

.custom-input-group-text {
  width: 120px;
  display: flex;
  justify-content: center;
}

::v-deep .fixed-group-text {
  display: flex;
  min-width: 150px;
  justify-content: center;
}

.item-filter {
  margin-bottom: 10px;
}
</style>
