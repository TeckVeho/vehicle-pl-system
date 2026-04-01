<!-- eslint-disable no-unused-vars -->
<template>
	<vHeaderFilter>
		<template #zone-filter>
			<b-col>
				<b-row>
					<span class="text-clear-all" @click="onClickClearFilter()">
						{{ $t('BUTTON.CLEAR_ALL') }}
					</span>
				</b-row>

				<!-- FILTER USER ID -->
				<b-row>
					<b-col cols="12" sm="12" md="12" lg="6" class="reset-padding-left">
						<div class="item-filter">
							<b-input-group>
								<b-input-group-prepend>
									<b-input-group-text>
										<input
											id="filter-user-id"
											v-model="isFilter.userId.status"
											type="checkbox"
											@change="onRemoveCheckboxValue($event, 1)"
										>
									</b-input-group-text>
								</b-input-group-prepend>

								<b-input-group-prepend>
									<b-input-group-text class="fixed-group-text">
										<span>社員番号</span>
									</b-input-group-text>
								</b-input-group-prepend>

								<b-form-input
									id="filter-user-id-value"
									v-model="isFilter.userId.value"
									type="number"
									:placeholder="$t('PLACEHOLDER_INPUT')"
									:disabled="!isFilter.userId.status"
									@keydown.native="validInputNumber"
								/>
							</b-input-group>
						</div>
					</b-col>
				</b-row>

				<!-- FILTER USER NAME -->
				<b-row>
					<b-col cols="12" sm="12" md="12" lg="6" class="reset-padding-left">
						<div class="item-filter">
							<b-input-group>
								<b-input-group-prepend>
									<b-input-group-text>
										<input
											id="filter-user-name"
											v-model="isFilter.userName.status"
											type="checkbox"
											@change="onRemoveCheckboxValue($event, 2)"
										>
									</b-input-group-text>
								</b-input-group-prepend>

								<b-input-group-prepend>
									<b-input-group-text class="fixed-group-text">
										<span>社員名</span>
									</b-input-group-text>
								</b-input-group-prepend>

								<b-form-input
									id="filter-user-name-value"
									v-model="isFilter.userName.value"
									type="text"
									:placeholder="$t('PLACEHOLDER_INPUT')"
									:disabled="!isFilter.userName.status"
								/>
							</b-input-group>
						</div>
					</b-col>
				</b-row>

				<!-- FILTER DEPARTMENT NAME -->
				<b-row>
					<b-col cols="12" sm="12" md="12" lg="6" class="reset-padding-left">
						<div class="item-filter">
							<b-input-group>
								<b-input-group-prepend>
									<b-input-group-text>
										<input
											id="filter-user-name"
											v-model="isFilter.departmentName.status"
											type="checkbox"
											@change="onRemoveCheckboxValue($event, 3)"
										>
									</b-input-group-text>
								</b-input-group-prepend>

								<b-input-group-prepend>
									<b-input-group-text class="fixed-group-text">
										<span>所属チーム</span>
									</b-input-group-text>
								</b-input-group-prepend>

								<b-form-select
									id="filter-user-name-value"
									v-model="isFilter.departmentName.value"
									:options="listDepartment"
									:disabled="!isFilter.departmentName.status"
									:value-field="'department_name'"
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
import { hasRole } from '@/utils/hasRole';
import { validInputNumber } from '@/utils/handleInput';

// eslint-disable-next-line no-unused-vars
import CONST_ROLE from '@/const/role';
import vButton from '@/components/atoms/vButton';
import vHeaderFilter from '@/components/atoms/vHeaderFilter';

export default {
    name: 'VUrgentContactMasterListFilter',
    components: {
        vHeaderFilter,
        vButton,
    },
    props: {
        listDepartment: {
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
            isFilter: this.$store.getters.filterUrgentContactMaster || {
                userId: {
                    status: false,
                    value: '',
                },
                userName: {
                    status: false,
                    value: '',
                },
                departmentName: {
                    status: false,
                    value: null,
                },
            },

            roles: this.$store.getters.profile.roles,
            roleCanNotEdit: [],
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
            this.$bus.emit('URGENT_CONTACT_MASTER_FILTER_DATA', this.isFilter);
        },
        emitOnClickApply() {
            this.$bus.emit('URGENT_CONTACT_MASTER_ON_CLICK_APPLY');
        },
        onClickClearFilter() {
            const DEFAULT = {
                userId: {
                    status: false,
                    value: '',
                },
                userName: {
                    status: false,
                    value: '',
                },
                departmentName: {
                    status: false,
                    value: null,
                },
            };

            this.isFilter = DEFAULT;
        },
        onClickApply() {
            this.emitOnClickApply();
        },
        async onRemoveCheckboxValue(event, index) {
            if (!event.target.checked) {
                if (index === 1) {
                    this.isFilter.userId.status = false;
                    this.isFilter.userId.value = null;
                } else if (index === 2) {
                    this.isFilter.userName.status = false;
                    this.isFilter.userName.value = null;
                } else if (index === 3) {
                    this.isFilter.departmentName.status = false;
                    this.isFilter.departmentName.value = null;
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

::v-deep #filter-user-name-value {
    height: 40px;
}
</style>
