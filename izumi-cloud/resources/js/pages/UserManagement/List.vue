<template>
	<b-overlay
		:show="overlay.show"
		:variant="overlay.variant"
		:opacity="overlay.opacity"
		:blur="overlay.blur"
		:rounded="overlay.sm"
	>
		<template #overlay>
			<div class="text-center">
				<b-icon icon="arrow-clockwise" font-scale="3" animation="spin" />
				<p style="margin-top: 10px">{{ $t("PLEASE_WAIT") }}</p>
			</div>
		</template>

		<b-col>
			<UserManagement :v-fields="vFields" :v-items="vItems" />
		</b-col>

		<div class="user-list-pagination">
			<div class="ml-3 select-per-page text-left">
				<div>
					<label for="per-page">1ページ毎の表示数</label>
				</div>
				<b-form-select id="per-page" v-model="pagination.per_page" :options="optionsPerPage" size="sm" @change="handleChangePerPage()" />
			</div>

			<div v-if="pagination.total_rows > 20" class="show-pagination">
				<vPagination
					:aria-controls="'table-user-list'"
					:current-page="pagination.current_page"
					:per-page="pagination.per_page"
					:total-rows="pagination.total_rows"
					:next-class="'next'"
					:prev-class="'prev'"
					@currentPageChange="getCurrentPage"
				/>
			</div>
		</div>
	</b-overlay>
</template>

<script>
import CONST_ROLE from '@/const/role';
import vPagination from '@/components/atoms/vPagination';
import UserManagement from '@/components/template/UserManagement';

import { hasRole } from '@/utils/hasRole';
import { obj2Path } from '@/utils/obj2Path';
import { cleanObj } from '@/utils/handleObj';
import { getUserList } from '@/api/modules/user';

const API_URLS = {
    urlGetUserList: `/user`,
};

export default {
    name: 'UserManagementIndex',
    components: {
        UserManagement,
        vPagination,
    },
    data() {
        return {
            vItems: [],

            pagination: {
                current_page: 1,
                per_page: 20,
                total_rows: 0,
            },

            isFilter: this.$store.getters.filterUserMaster || {
                userName: {
                    status: false,
                    value: '',
                },
                userID: {
                    status: false,
                    value: '',
                },
                role: {
                    status: false,
                    value: '',
                },
            },

            filterQuery: {
                order_column: '',
                order_type: '',
            },

            overlay: {
                show: false,
                variant: 'light',
                opacity: 1,
                blur: '1rem',
                rounded: 'sm',
            },

            isRefreshUserList: false,

            isApplyFilterAction: false,
        };
    },
    computed: {
        role() {
            return this.$store.getters.profile.roles;
        },
        vFields() {
            return [
                {
                    key: 'role_name',
                    sortable: true,
                    label: this.$t('USER_MANAGEMENT.USER_ROLE'),
                    tdClass: 'text-center',
                    thClass: 'text-center',
                },
                {
                    key: 'name',
                    sortable: true,
                    label: this.$t('USER_MANAGEMENT.EMPLOYEE_NAME'),
                    tdClass: 'text-center',
                    thClass: 'text-center',
                },
                {
                    key: 'id',
                    sortable: true,
                    label: this.$t('USER_MANAGEMENT.USER_ID'),
                    tdClass: 'text-center',
                    thClass: 'text-center',
                },
                hasRole(
                    [
                        CONST_ROLE.ACCOUNTING,
                        CONST_ROLE.PERSONNEL_LABOR,
                        CONST_ROLE.DX_USER,
                        CONST_ROLE.DX_MANAGER,
                    ],
                    this.role
                )
                    ? {
                        key: 'edit',
                        sortable: false,
                        label: '編集',
                        tdClass: 'text-center',
                        thClass: 'text-center',
                    }
                    : {},
            ];
        },
        optionsPerPage() {
            return [
                { value: 20, text: '20' },
                { value: 50, text: '50' },
                { value: 100, text: '100' },
                { value: 250, text: '250' },
                { value: 500, text: '500' },
            ];
        },
    },
    watch: {
        filterQuery: {
            handler() {
                this.getListUserData(1, true);
            },
            deep: true,
        },
    },
    created() {
        this.getListUserData(1);

        this.$bus.on('userNameFilterChange', (value) => {
            this.isFilter.userName.value = value;
        });

        this.$bus.on('userIdFilterChange', (value) => {
            this.isFilter.userID.value = value;
        });

        this.$bus.on('roleFilterChange', (value) => {
            this.isFilter.role.value = value;
        });

        this.$bus.on('doApplyUserManagementFilter', () => {
            this.handleSaveFilter(this.isFilter);
            this.getListUserData(1, true);
        });

        this.$bus.on('filterUserNameUserManagement', (value) => {
            this.isFilter.userName.status = value;
        });

        this.$bus.on('filterUserIdUserManagement', (value) => {
            this.isFilter.userID.status = value;
        });

        this.$bus.on('filterRoleUserManagement', (value) => {
            this.isFilter.role.status = value;
        });

        this.$bus.on('refreshUserList', () => {
            this.getListUserData(1);
        });

        this.$bus.on('sendFilterQueryUserManagement', (value) => {
            this.filterQuery = value;
        });

        this.$bus.on('USER_MANAGEMENT_PER_PAGE', (value) => {
            this.getListUserData(1);
        });
    },
    destroyed() {
        this.$bus.off('userNameFilterChange');
        this.$bus.off('userIdFilterChange');
        this.$bus.off('roleFilterChange');
        this.$bus.off('doApplyUserManagementFilter');
        this.$bus.off('filterUserNameUserManagement');
        this.$bus.off('filterUserIdUserManagement');
        this.$bus.off('filterRoleUserManagement');
        this.$bus.off('sendFilterQueryDataList');
        this.$bus.off('refreshUserList');
    },
    methods: {
        async handleChangePerPage() {
            this.getListUserData(1);
            await this.$store.dispatch('pagination/setUserManagementCP', 1);
            await this.$store.dispatch('pagination/setUserManagementPerPage', this.pagination.per_page);
        },
        async getListUserData(page, is_force_reset_current_page) {
            this.overlay.show = true;

            const STORED_CURRENT_PAGE = this.$store.getters.userManagementCP;

            let current_page = 1;

            if (is_force_reset_current_page) {
                current_page = 1;
            } else {
                if (STORED_CURRENT_PAGE) {
                    current_page = STORED_CURRENT_PAGE;
                } else {
                    current_page = page;
                }
            }

            const STORED_PER_PAGE = this.$store.getters.user_management_per_page;

            let per_page = 20;

            if (STORED_PER_PAGE) {
                per_page = STORED_PER_PAGE;
            } else {
                per_page = this.pagination.per_page;
            }

            try {
                let URL = {
                    page: current_page,
                    per_page: per_page,
                    sortby: this.filterQuery.order_column,
                    sorttype: this.filterQuery.order_type.toLowerCase(),
                };

                if (this.isFilter.userName.status) {
                    URL.name = this.isFilter.userName.value;
                }

                if (this.isFilter.userID.status) {
                    URL.id = this.isFilter.userID.value;
                }

                if (this.isFilter.role.status) {
                    URL.role = this.isFilter.role.value;
                }

                if (this.isFilter.userName.status) {
                    URL.search = this.isFilter.userName.value;
                }

                URL = cleanObj(URL);

                URL = `${API_URLS.urlGetUserList}?${obj2Path(URL)}`;

                const response = await getUserList(URL);

                if (response.code === 200) {
                    const DATA = response.data.result;

                    this.vItems = DATA;

                    this.pagination.total_rows = response['data']['pagination']['total_records'];
                    this.pagination.current_page = response['data']['pagination']['current_page'];
                    this.pagination.per_page = response['data']['pagination']['per_page'];
                } else {
                    this.vItems = [];
                }
            } catch (error) {
                console.log(error);
            }

            this.overlay.show = false;
        },
        handleSaveFilter(filter) {
            this.$store.dispatch('filter/setFilterUserMaster', filter);
        },
        async getCurrentPage(value) {
            if (value) {
                this.pagination.current_page = value;
                await this.$store.dispatch('pagination/setUserManagementCP', value);
                this.getListUserData(value);
            }
        },
    },
};
</script>

<style lang="scss" scoped>
@import "@/scss/variables.scss";

::-webkit-scrollbar-thumb {
    border-radius: 45px;
}

.user-list-pagination {
  .select-per-page {
    #per-page {
      width: 100px;
    }
  }

  .show-pagination {
    display: flex;
    justify-content: center;
  }
}
</style>
