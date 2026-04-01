<template>
	<div class="organisms-user-management-filter">
		<vHeaderFilter>
			<template #zone-filter>
				<b-col>
					<b-row>
						<span class="text-clear-all" @click="doClearFilter()">{{
							$t("USER_MANAGEMENT.CLEAR_ALL")
						}}</span>
					</b-row>

					<b-row>
						<b-col cols="12" sm="12" md="12" lg="6" style="padding-left: 0">
							<div class="zone-input">
								<vInputGroup
									:id="'filter-by-name'"
									v-model="userName"
									:type="'text'"
									:text-prepend="$t('USER_NAME')"
									:placeholder="''"
									:is-check="isCheckUserName"
									@isChecked="getIsCheckFilterUserName"
								/>
							</div>
						</b-col>
					</b-row>

					<b-row class="mt-3">
						<b-col cols="12" sm="12" md="12" lg="6" style="padding-left: 0">
							<div class="zone-input">
								<vSelectGroup
									:id="'filter-by-role'"
									v-model="role"
									:data-options="ROLE_LIST"
									:text-prepend="'ユーザ権限'"
									:is-check="isCheckRole"
									:checkbox-size="'lg'"
									@isChecked="getIsCheckFilterRole"
								/>
							</div>
						</b-col>
					</b-row>

					<b-row class="mt-3">
						<b-col cols="12" sm="12" md="12" lg="6" style="padding-left: 0">
							<div class="zone-input">
								<vInputGroup
									:id="'filter-by-user-id'"
									v-model="userID"
									:type="'text'"
									:text-prepend="'ユーザID'"
									:placeholder="''"
									:is-check="isCheckUserID"
									@isChecked="getIsCheckFilterUserID"
								/>
							</div>
						</b-col>
					</b-row>

					<b-row>
						<div class="zone-btn-apply">
							<vButton
								:class="'btn-summit-filter'"
								:text-button="$t('BUTTON.APPLY')"
								@click.native="doApply()"
							/>
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
import vSelectGroup from '@/components/atoms/vSelectGroup';
import vButton from '@/components/atoms/vButton';

export default {
    name: 'UserManagementFilter',
    components: {
        vHeaderFilter,
        vInputGroup,
        vSelectGroup,
        vButton,
    },
    data() {
        return {
            userName: this.$store.getters.filterUserMaster.userName.value || '',
            isCheckUserName: this.$store.getters.filterUserMaster.userName.status || false,

            userID: this.$store.getters.filterUserMaster.userID.value || '',
            isCheckUserID: this.$store.getters.filterUserMaster.userID.status || false,

            role: this.$store.getters.filterUserMaster.role.value || '',
            isCheckRole: this.$store.getters.filterUserMaster.role.status || false,

            ROLE_LIST: [
                { value: 1, text: this.$t('USER_MANAGEMENT.ROLE.CREW') },
                { value: 2, text: this.$t('USER_MANAGEMENT.ROLE.CLERKS') },
                { value: 3, text: this.$t('USER_MANAGEMENT.ROLE.TL') },
                { value: 17, text: this.$t('USER_MANAGEMENT.ROLE.DEPARTMENT_OFFICE_STAFF') },
                { value: 6, text: this.$t('USER_MANAGEMENT.ROLE.PERSONNEL_LABOR') },
                { value: 5, text: this.$t('USER_MANAGEMENT.ROLE.GENERAL_AFFAIR') },
                { value: 4, text: this.$t('USER_MANAGEMENT.ROLE.ACCOUNTING') },
                { value: 11, text: this.$t('USER_MANAGEMENT.ROLE.QUALITY_CONTROL') },
                { value: 12, text: this.$t('USER_MANAGEMENT.ROLE.SALES') },
                { value: 13, text: this.$t('USER_MANAGEMENT.ROLE.SITE_MANAGER') },
                { value: 14, text: this.$t('USER_MANAGEMENT.ROLE.HQ_MANAGER') },
                { value: 15, text: this.$t('USER_MANAGEMENT.ROLE.DEPARTMENT_MANAGER') },
                { value: 16, text: this.$t('USER_MANAGEMENT.ROLE.EXECUTIVE_OFFICER') },
                { value: 8, text: this.$t('USER_MANAGEMENT.ROLE.DIRECTOR') },
                { value: 9, text: this.$t('USER_MANAGEMENT.ROLE.DX_USER') },
                { value: 10, text: this.$t('USER_MANAGEMENT.ROLE.DX_MANAGER') },
            ],
        };
    },
    computed: {
        userNameChange() {
            return this.userName;
        },
        userIdChange() {
            return this.userID;
        },
        roleChange() {
            return this.role;
        },
    },
    watch: {
        userNameChange() {
            this.setEmitInputUserName();
        },
        userIdChange() {
            this.setEmitInputUserID();
        },
        roleChange() {
            this.setEmitInputRole();
        },
    },
    methods: {
        doApply() {
            this.$bus.emit('doApplyUserManagementFilter');
        },
        setEmitInputUserName() {
            this.$bus.emit('userNameFilterChange', this.userName);
        },
        setEmitInputUserID() {
            this.$bus.emit('userIdFilterChange', this.userID);
        },
        setEmitInputRole() {
            this.$bus.emit('roleFilterChange', this.role);
        },
        doClearFilter() {
            this.isCheckUserName = false;
            this.userName = '';

            this.isCheckUserID = false;
            this.userID = '';

            this.isCheckRole = false;
            this.role = '';
        },
        getIsCheckFilterUserName(value) {
            if (!value) {
                this.userName = '';
                this.$bus.emit('userNameFilterChange', this.userName);
            }

            this.isCheckUserName = value;
            this.$bus.emit('filterUserNameUserManagement', value);
        },
        getIsCheckFilterUserID(value) {
            if (!value) {
                this.userID = '';
                this.$bus.emit('userIdFilterChange', this.userID);
            }

            this.isCheckUserID = value;
            this.$bus.emit('filterUserIdUserManagement', value);
        },
        getIsCheckFilterRole(value) {
            if (!value) {
                this.role = '';
                this.$bus.emit('roleFilterChange', this.role);
            }

            this.isCheckRole = value;
            this.$bus.emit('filterRoleUserManagement', value);
        },
    },
};
</script>

<style lang="scss" scoped>
@import "@/scss/variables.scss";

.organisms-user-management-filter {
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
