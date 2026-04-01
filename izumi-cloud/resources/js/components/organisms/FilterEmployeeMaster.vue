<template>
	<vHeaderFilter>
		<template #zone-filter>
			<b-col>
				<b-row>
					<span
						class="text-clear-all"
						@click="onClickClearFilter()"
					>
						{{ $t('BUTTON.CLEAR_ALL') }}
					</span>
				</b-row>

				<!-- FILTER AFFILIATION BASE -->
				<b-row v-if="!hasRole(roles, roleCanNotEdit)">
					<b-col cols="12" sm="12" md="12" lg="6" class="reset-padding-left">
						<div class="item-filter">
							<b-input-group>
								<b-input-group-prepend>
									<b-input-group-text>
										<input
											id="filter-affiliation-base"
											v-model="isFilter.affiliationBase.status"
											type="checkbox"
											@change="onRemoveCheckboxValue($event, 1)"
										>
									</b-input-group-text>
								</b-input-group-prepend>

								<b-input-group-prepend>
									<b-input-group-text class="fixed-group-text">
										<span>{{ $t('EMPLOYEE_MASTER_FILTER_AFFILIATION_BASE') }}</span>
									</b-input-group-text>
								</b-input-group-prepend>

								<b-form-select
									id="filter-affiliation-base-value"
									v-model="isFilter.affiliationBase.value"
									:options="listAffiliationBase"
									:disabled="!isFilter.affiliationBase.status"
									:value-field="'id'"
									:text-field="'department_name'"
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

				<!-- FILTER SUPPORT BASE -->
				<b-row v-if="!hasRole(roles, roleCanNotEdit)">
					<b-col cols="12" sm="12" md="12" lg="6" class="reset-padding-left">
						<div class="item-filter">
							<b-input-group>
								<b-input-group-prepend>
									<b-input-group-text>
										<input
											id="filter-support-base"
											v-model="isFilter.supportBase.status"
											type="checkbox"
											@change="onRemoveCheckboxValue($event, 2)"
										>
									</b-input-group-text>
								</b-input-group-prepend>

								<b-input-group-prepend>
									<b-input-group-text class="fixed-group-text">
										<span>{{ $t('EMPLOYEE_MASTER_FILTER_SUPPORT_BASE') }}</span>
									</b-input-group-text>
								</b-input-group-prepend>

								<b-form-select
									id="filter-support-base-value"
									v-model="isFilter.supportBase.value"
									:options="listSupportBase"
									:disabled="!isFilter.supportBase.status"
									:value-field="'id'"
									:text-field="'department_name'"
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

				<!-- FILTER EMPLOYEE ID -->
				<b-row>
					<b-col cols="12" sm="12" md="12" lg="6" class="reset-padding-left">
						<div class="item-filter">
							<b-input-group>
								<b-input-group-prepend>
									<b-input-group-text>
										<input
											id="filter-employee-id"
											v-model="isFilter.employeeId.status"
											type="checkbox"
											@change="onRemoveCheckboxValue($event, 3)"
										>
									</b-input-group-text>
								</b-input-group-prepend>

								<b-input-group-prepend>
									<b-input-group-text class="fixed-group-text">
										<span>{{ $t('EMPLOYEE_MASTER_FILTER_EMPLOYEE_ID') }}</span>
									</b-input-group-text>
								</b-input-group-prepend>

								<b-form-input
									id="filter-employee-id-value"
									v-model="isFilter.employeeId.value"
									type="number"
									:placeholder="$t('PLACEHOLDER_INPUT')"
									:disabled="!isFilter.employeeId.status"
									@keydown.native="validInputNumber"
								/>
							</b-input-group>
						</div>
					</b-col>
				</b-row>

				<!-- FILTER EMPLOYEE NAME -->
				<b-row>
					<b-col cols="12" sm="12" md="12" lg="6" class="reset-padding-left">
						<div class="item-filter">
							<b-input-group>
								<b-input-group-prepend>
									<b-input-group-text>
										<input
											id="filter-employee-name"
											v-model="isFilter.employeeName.status"
											type="checkbox"
											@change="onRemoveCheckboxValue($event, 4)"
										>
									</b-input-group-text>
								</b-input-group-prepend>

								<b-input-group-prepend>
									<b-input-group-text class="fixed-group-text">
										<span>{{ $t('EMPLOYEE_MASTER_FILTER_EMPLOYEE_NAME') }}</span>
									</b-input-group-text>
								</b-input-group-prepend>

								<b-form-input
									id="filter-employee-name-value"
									v-model="isFilter.employeeName.value"
									type="text"
									:placeholder="$t('PLACEHOLDER_INPUT')"
									:disabled="!isFilter.employeeName.status"
								/>
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
			</b-col>
		</template>
	</vHeaderFilter>
</template>

<script>
import vButton from '@/components/atoms/vButton';
import { validInputNumber } from '@/utils/handleInput';
import vHeaderFilter from '@/components/atoms/vHeaderFilter';
import CONST_ROLE from '@/const/role';
import { hasRole } from '@/utils/hasRole';

export default {
    name: 'VFilterEmployeeMaster',
    components: {
        vHeaderFilter,
        vButton,
    },
    props: {
        listAffiliationBase: {
            type: Array,
            default: function() {
                return [
                    {
                        value: null,
                        text: this.$t('PLEASE_SELECT'),
                    },
                ];
            },
        },
        listSupportBase: {
            type: Array,
            default: function() {
                return [
                    {
                        value: null,
                        text: this.$t('PLEASE_SELECT'),
                    },
                ];
            },
        },
    },
    data() {
        return {
            isFilter: this.$store.getters.filterEmployeeMaster || {
                affiliationBase: {
                    status: false,
                    value: null,
                },
                supportBase: {
                    status: false,
                    value: null,
                },
                employeeId: {
                    status: false,
                    value: '',
                },
                employeeName: {
                    status: false,
                    value: '',
                },
            },

            roles: this.$store.getters.profile.roles,
            roleCanNotEdit: [CONST_ROLE.CLERKS, CONST_ROLE.TL, CONST_ROLE.DEPARTMENT_OFFICE_STAFF],
        };
    },
    watch: {
        isFilter: {
            handler: function() {
                this.emitDataFilter();
            },
            deep: true,
        },
    },
    methods: {
        hasRole,
        validInputNumber,
        emitDataFilter() {
            this.$bus.emit('EMPLOYEE_MASTER_FILTER_DATA', this.isFilter);
        },
        emitOnClickApply() {
            this.$bus.emit('EMPOLYEE_MASTER_ON_CLICK_APPLY');
        },
        onClickClearFilter() {
            const DEPARTMENT = this.$store.getters.profile.department;

            const DEFAULT = {
                affiliationBase: {
                    status: false,
                    value: null,
                },
                supportBase: {
                    status: false,
                    value: null,
                },
                employeeId: {
                    status: false,
                    value: '',
                },
                employeeName: {
                    status: false,
                    value: '',
                },
            };

            if (hasRole(this.roles, this.roleCanNotEdit)) {
                DEFAULT.affiliationBase = {
                    status: true,
                    value: DEPARTMENT.id,
                };

                DEFAULT.supportBase = {
                    status: true,
                    value: DEPARTMENT.id,
                };
            }

            this.isFilter = DEFAULT;
        },
        onClickApply() {
            this.emitOnClickApply();
        },
        async onRemoveCheckboxValue(event, index) {
            if (!event.target.checked) {
                if (index === 1) {
                    this.isFilter.affiliationBase.status = false;
                    this.isFilter.affiliationBase.value = null;
                } else if (index === 2) {
                    this.isFilter.supportBase.status = false;
                    this.isFilter.supportBase.value = null;
                } else if (index === 3) {
                    this.isFilter.employeeId.status = false;
                    this.isFilter.employeeId.value = null;
                } else if (index === 4) {
                    this.isFilter.employeeName.status = false;
                    this.isFilter.employeeName.value = null;
                }
            }
        },
    },
};
</script>

<style lang="scss" scoped>
.reset-padding-left {
  padding-left: 0;
}

.item-filter {
  margin-top: 10px;
}

.btn-summit-filter {
  margin-top: 10px;
}

::v-deep .fixed-group-text {
  display: flex;
  min-width: 150px;
  justify-content: center;
}
</style>
