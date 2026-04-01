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
				<p class="text-overlay">{{ $t("PLEASE_WAIT") }}</p>
			</div>
		</template>

		<div class="store-master">
			<b-col>
				<div class="store-master__header">
					<vHeaderPage>{{ $t("ROUTER_STORE_MASTER") }}</vHeaderPage>
				</div>

				<div class="store-master__filter">
					<vHeaderFilter>
						<template #zone-filter>
							<b-col>
								<b-row>
									<span class="text-clear-all" @click="onClickClearAll()">{{ $t("CLEAR_ALL") }}</span>
								</b-row>

								<div class="filter-item">
									<b-row>
										<b-col cols="12" sm="12" md="12" lg="6" class="reset-padding-b-col">
											<b-input-group>
												<b-input-group-prepend is-text>
													<input v-model="isFilter.store_name.status" class="status-filter-store-name" type="checkbox" @change="handleChangeStoreName">
												</b-input-group-prepend>

												<b-input-group-prepend>
													<b-input-group-text class="fixed-group-text">
														<span> {{ $t( "STORE_MASTER_TABLE_HEADER_STORE_NAME" ) }}</span>
													</b-input-group-text>
												</b-input-group-prepend>

												<b-form-input
													id="filter-store-name"
													v-model="isFilter.store_name.value"
													:disabled="!isFilter.store_name.status"
													placeholder=""
												/>
											</b-input-group>
										</b-col>
									</b-row>
								</div>
							</b-col>

							<div class="zone-btn-apply">
								<vButton
									:class="'btn-summit-filter'"
									:text-button="$t('BUTTON.APPLY')"
									@click.native="onClickApply()"
								/>
							</div>
						</template>
					</vHeaderFilter>
				</div>

				<div class="store-master__handle">
					<b-row>
						<b-col cols="12">
							<template
								v-if="hasRole([CONST_ROLE.CLERKS, CONST_ROLE.TL,
									CONST_ROLE.DEPARTMENT_OFFICE_STAFF, CONST_ROLE.DEPARTMENT_OFFICE_STAFF, CONST_ROLE.AM_SM, CONST_ROLE.DIRECTOR, CONST_ROLE.DX_USER, CONST_ROLE.DX_MANAGER], role)"
							>
								<vButton
									:text-button="$t('BUTTON.REGISTRATION')"
									:class-name="'btn-radius v-button-default btn-registration'"
									@click.native="onClickRegister()"
								/>
							</template>
						</b-col>
					</b-row>
				</div>

				<div class="store-master__table">
					<b-table
						id="table-store-master"
						striped
						bordered
						show-empty
						responsive
						sticky-header
						:items="items"
						:fields="fields"
						@sort-changed="handleSort"
					>
						<template #cell(edit)="data">
							<i class="fas fa-eye" @click="onClickDetail(data.item.id)" />
						</template>

						<template #cell(delete)="data">
							<i class="fas fa-trash" @click="onClickDelete(data.item.id)" />
						</template>

						<template #empty="">
							<div class="text-center">
								<span>{{ $t("TABLE_EMPTY") }}</span>
							</div>
						</template>
					</b-table>
				</div>

				<div class="store-master__pagination">
					<div class="select-per-page">
						<div>
							<label for="per-page">1ページ毎の表示数</label>
						</div>

						<b-form-select
							id="per-page"
							v-model="pagination.per_page"
							:options="optionsPerPage"
							size="sm"
							@change="handleChangePerPage()"
						/>
					</div>

					<div v-if="pagination.total_rows > 20" class="show-pagination">
						<vPagination
							:aria-controls="'table-store-master'"
							:current-page="pagination.current_page"
							:per-page="pagination.per_page"
							:total-rows="pagination.total_rows"
							:next-class="'next'"
							:prev-class="'prev'"
							@currentPageChange="getCurrentPage"
						/>
					</div>
				</div>
			</b-col>
		</div>

		<b-modal
			id="modal-cf"
			v-model="showModal"
			no-close-on-backdrop
			no-close-on-esc
			hide-header
			:static="true"
			header-class="modal-custom-header"
			content-class="modal-custom-body"
			footer-class="modal-custom-footer"
		>
			<template #default>
				<span>この店舗を削除してもよろしいですか？</span>
			</template>

			<template #modal-footer>
				<b-button class="modal-btn btn-cancel" :disabled="overlay.show" @click="showModal = false">
					{{ $t("NO") }}
				</b-button>

				<b-button class="modal-btn btn-apply" :disabled="overlay.show" @click="handleDelete()">
					{{ $t("YES") }}
				</b-button>
			</template>
		</b-modal>
	</b-overlay>
</template>

<script>
import CONST_ROLE from '@/const/role';
import vButton from '@/components/atoms/vButton';
import vPagination from '@/components/atoms/vPagination';
import vHeaderPage from '@/components/atoms/vHeaderPage.vue';
import vHeaderFilter from '@/components/atoms/vHeaderFilter';

import { hasRole } from '@/utils/hasRole';
import { getListStore, deleteStore } from '@/api/modules/storeMaster';

const urlAPI = {
    getList: '/store',
    delete: '/store',
};

export default {
    name: 'StoreMaster',
    components: {
        vHeaderPage,
        vButton,
        vPagination,
        vHeaderFilter,
    },
    data() {
        return {
            CONST_ROLE,

            hasRole,

            overlay: {
                show: false,
                variant: 'light',
                opacity: 1,
                blur: '1rem',
                rounded: 'sm',
            },

            items: [],

            pagination: {
                current_page: 1,
                per_page: 20,
                total_rows: 0,
            },

            isFilter: this.$store.getters.filterStoreMaster || {
                store_name: {
                    status: false,
                    value: '',
                },
            },

            filterQuery: {
                sort_by: null,
                sort_type: null,
            },

            showModal: false,

            idHandle: null,

            hasAccessEdit: [
                CONST_ROLE.CLERKS,
                CONST_ROLE.TL,
                CONST_ROLE.DEPARTMENT_OFFICE_STAFF,
                CONST_ROLE.AM_SM,
                CONST_ROLE.QUALITY_CONTROL,
                CONST_ROLE.SITE_MANAGER,
                CONST_ROLE.HQ_MANAGER,
                CONST_ROLE.DEPARTMENT_MANAGER,
                CONST_ROLE.EXECUTIVE_OFFICER,
                CONST_ROLE.DIRECTOR,
                CONST_ROLE.DX_USER,
                CONST_ROLE.DX_MANAGER,
            ],

            hasAccessDelete: [
                CONST_ROLE.CLERKS,
                CONST_ROLE.TL,
                CONST_ROLE.DEPARTMENT_OFFICE_STAFF,
                CONST_ROLE.AM_SM,
                CONST_ROLE.QUALITY_CONTROL,
                CONST_ROLE.SITE_MANAGER,
                CONST_ROLE.HQ_MANAGER,
                CONST_ROLE.DEPARTMENT_MANAGER,
                CONST_ROLE.EXECUTIVE_OFFICER,
                CONST_ROLE.DIRECTOR,
                CONST_ROLE.DX_USER,
                CONST_ROLE.DX_MANAGER,
            ],
        };
    },
    computed: {
        role() {
            return this.$store.getters.profile.roles;
        },
        fields() {
            return [
                {
                    label: '店舗名',
                    key: 'store_name',
                    sortable: true,
                    tdClass: 'text-center',
                    thClass: 'text-center table-customer-th th-first-column',
                },
                hasRole(this.hasAccessEdit, this.role)
                    ? {
                        key: 'edit',
                        tdClass: 'text-center td-edit',
                        label: this.$t('STORE_MASTER_TABLE_HEADER_EDIT'),
                        thClass: 'text-center th-edit table-customer-th',
                    }
                    : {},
                hasRole(this.hasAccessDelete, this.role)
                    ? {
                        key: 'delete',
                        tdClass: 'text-center td-delete',
                        label: this.$t('STORE_MASTER_TABLE_HEADER_DELETE'),
                        thClass: 'text-center th-delete table-customer-th',
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
    created() {
        this.handleGetListStore(1);
    },
    methods: {
        async handleChangePerPage() {
            await this.$store.dispatch('pagination/setStoreMasterCP', 1);
            await this.$store.dispatch('pagination/setStoreMasterPerPage', this.pagination.per_page);
            this.handleGetListStore(1);
        },
        async handleGetListStore(page, is_force_reset_current_page) {
            this.overlay.show = true;

            const STORE_MASTER_PAGINATION = this.$store.getters.storeMasterCP;

            let current_page = 1;

            if (is_force_reset_current_page) {
                current_page = 1;
            } else {
                if (STORE_MASTER_PAGINATION) {
                    current_page = STORE_MASTER_PAGINATION;
                } else {
                    current_page = page;
                }
            }

            const STORE_MASTER_PER_PAGE = this.$store.getters.store_master_per_page;

            let per_page = 20;

            if (STORE_MASTER_PER_PAGE) {
                per_page = STORE_MASTER_PER_PAGE;
            } else {
                per_page = this.pagination.per_page;
            }

            try {
                const URL = urlAPI.getList || '';

                const PARAMS = {
                    store_name: this.isFilter.store_name.status ? this.isFilter.store_name.value : null,
                    sort_by: this.filterQuery.sort_by,
                    sort_type: this.filterQuery.sort_type,
                    page: current_page,
                    per_page: per_page,
                };

                const res = await getListStore(URL, PARAMS);

                if (res.code === 200) {
                    const DATA = res.data;

                    this.items = DATA.result;

                    this.pagination.total_rows = res.data.pagination.total_records;
                    this.pagination.current_page = res.data.pagination.current_page;
                    this.pagination.per_page = res.data.pagination.per_page;

                    this.overlay.show = false;
                }
            } catch (error) {
                this.overlay.show = false;
                this.$toast.danger({
                    content: error.response.data.message,
                });
            }
        },
        onClickRegister() {
            this.$router.push({ name: 'StoreMasterCreate' });
        },
        onClickDetail(id = 0) {
            if (id) {
                this.$router.push({
                    name: 'StoreMasterDetail',
                    params: { id: id },
                });
            }
        },
        onClickDelete(id = 0) {
            if (id) {
                this.idHandle = id;
                this.showModal = true;
            } else {
                this.idHandle = null;

                this.$toast.warning({
                    content: 'ストアIDが正しくありません',
                });
            }
        },
        onClickClearAll() {
            const IS_FILER = {
                store_name: {
                    status: false,
                    value: '',
                },
            };

            this.isFilter = IS_FILER;

            this.handleGetListStore(1, true);
        },
        onClickApply() {
            this.handleSaveFilter(this.isFilter);
            this.handleGetListStore(1, true);
        },
        handleSaveFilter(filter) {
            this.$store.dispatch('filter/setFilterStoreMaster', filter);
        },
        handleSort(ctx) {
            this.filterQuery.sort_by =
        ctx.sortBy === 'type' ? 'type' : ctx.sortBy;

            this.filterQuery.sort_type = ctx.sortDesc === true ? 'asc' : 'desc';

            this.handleGetListStore(1);
        },
        async handleDelete() {
            if (this.idHandle) {
                try {
                    this.overlay.show = true;

                    const URL = urlAPI.delete;

                    const res = await deleteStore(`${URL}/${this.idHandle}`);

                    if (res.code === 200) {
                        this.$toast.success({
                            content: '店舗を削除しました',
                        });

                        this.handleGetListStore(1);
                    }

                    this.showModal = false;
                    this.overlay.show = false;
                } catch (error) {
                    this.showModal = false;
                    this.overlay.show = false;
                    this.$toast.danger({
                        content: error.response.data.message,
                    });
                }
            } else {
                this.$toast.warning({
                    content: 'ストアIDが正しくありません',
                });
            }
        },
        async getCurrentPage(value) {
            if (value) {
                this.pagination.current_page = value;
                await this.$store.dispatch('pagination/setStoreMasterCP', value);
                this.handleGetListStore(value);
            }
        },
        handleChangeStoreName(event) {
            if (!event.target.checked) {
                this.isFilter.store_name.value = null;
            }

            this.isFilter.store_name.status = event.target.checked;
        },
    },
};
</script>

<style lang="scss" scoped>
@import "@/scss/variables";

::v-deep .fixed-group-text {
  display: flex;
  min-width: 150px;
  justify-content: center;
}

::-webkit-scrollbar {
    height: 3px;
    width: 3px;
}

::-webkit-scrollbar-thumb {
    border-radius: 45px;
}

.text-overlay {
    margin-top: 10px;
}

::v-deep .table-customer-th {
    color: $white !important;
    min-width: 150px !important;
    text-align: center !important;
    background-color: $tolopea !important;
}

::v-deep .b-table-sticky-header {
    overflow-y: scroll !important;
    max-height: 850px !important;
}

.store-master {
    &__header,
    &__handle,
    &__table,
    &__filter,
    &__pagination {
        margin-bottom: 20px;
    }

    &__table {
        max-height: 850px;

        ::v-deep table {
            border-spacing: 0;
            border-collapse: separate;

            thead {
                th {
                    text-align: center;
                    color: $white;
                    min-width: 150px;
                    background-color: $tolopea;
                }

                th.th-edit,
                th.th-delete {
                    width: 100px;
                }

                th.th-first-column {
                    top: 0;
                    z-index: 2;
                    position: sticky !important;
                }
            }

            tbody {
                tr {
                    &:hover {
                        background-color: $west-side;

                        td {
                            color: $white;
                        }
                    }
                }

                td.td-edit,
                td.td-delete {
                    width: 100px;

                    i {
                        cursor: pointer;
                    }
                }
            }
        }
    }

    &__filter {
        span.text-clear-all {
            cursor: pointer;
            font-weight: 500;
            margin-bottom: 20px;
            border-top: 1px solid $black;
            border-bottom: 1px solid $black;
        }
    }

    .filter-item {
        margin-bottom: 10px;
    }

    .reset-padding-b-col {
        padding-left: 0;
    }

    &__pagination {
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
}

::v-deep #modal-cf {
    .modal-custom-header {
        border-bottom: 0 none;
    }

    .modal-custom-body {
        text-align: center;
        padding-top: 60px;

        span {
            font-weight: 500;
        }
    }

    .modal-custom-footer {
        border-top: 0 none;
        justify-content: center;
        padding-top: 50px;

        button {
            border: none;
            min-width: 150px;
            font-weight: 500;
            margin: 0 15px;

            &:hover {
                opacity: 0.8;
            }

            &:focus {
                opacity: 0.8;
            }
        }

        .modal-btn {
            background-color: $west-side;
            color: $white;

            &:focus {
                background-color: $west-side;
                color: $white;
            }
        }
    }
}
</style>
