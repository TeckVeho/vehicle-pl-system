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

		<div class="customer-master">
			<b-col>
				<div class="customer-master__header">
					<vHeaderPage>
						{{ $t("ROUTER_CUSTOMER_MASTER") }}
					</vHeaderPage>
				</div>
				<div class="customer-master__handle">
					<b-row>
						<b-col cols="12">
							<template
								v-if="hasRole([
									CONST_ROLE.ACCOUNTING,
									CONST_ROLE.SITE_MANAGER,
									CONST_ROLE.HQ_MANAGER,
									CONST_ROLE.DEPARTMENT_MANAGER,
									CONST_ROLE.EXECUTIVE_OFFICER,
									CONST_ROLE.DIRECTOR,
									CONST_ROLE.DX_USER,
									CONST_ROLE.DX_MANAGER,
								], role)"
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
				<div class="customer-master__table">
					<b-table
						id="table-customer-master"
						striped
						show-empty
						responsive
						bordered
						:fields="fields"
						:items="items"
						sticky-header
					>
						<template #cell(edit)="data">
							<i class="fas fa-pen" @click="onClickEdit(data.item.id)" />
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
				<div
					v-if="pagination.vTotalRows > 20"
					class="customer-master__pagination"
				>
					<div class="select-per-page">
						<div>
							<label for="per-page">1ページ毎の表示数</label>
						</div>
						<b-form-select
							id="per-page"
							v-model="pagination.vPerPage"
							:options="optionsPerPage"
							size="sm"
							@change="handleChangePerPage()"
						/>
					</div>

					<div class="show-pagination">
						<vPagination
							:aria-controls="'table-customer-master'"
							:current-page="pagination.vCurrentPage"
							:per-page="pagination.vPerPage"
							:total-rows="pagination.vTotalRows"
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
				<span>この荷主を削除してもよろしいですか？</span>
			</template>

			<template #modal-footer>
				<b-button class="modal-btn btn-cancel" @click="showModal = false">
					{{ $t("NO") }}
				</b-button>

				<b-button class="modal-btn btn-apply" @click="handleDelete()">
					{{ $t("YES") }}
				</b-button>
			</template>
		</b-modal>
	</b-overlay>
</template>

<script>
import vHeaderPage from '@/components/atoms/vHeaderPage.vue';
import vButton from '@/components/atoms/vButton';
import vPagination from '@/components/atoms/vPagination';

import CONST_ROLE from '@/const/role';
import { hasRole } from '@/utils/hasRole';

import { getListCustomer, deleteCustomer } from '@/api/modules/customerMaster';

const urlAPI = {
    getList: '/customer',
    delete: '/customer',
};

export default {
    name: 'CustomerMaster',
    components: {
        vHeaderPage,
        vButton,
        vPagination,
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
            selectedPerPage: 20,
            pagination: {
                vCurrentPage: 1,
                vPerPage: 20,
                vTotalRows: 0,
            },
            showModal: false,
            idHandle: null,
        };
    },
    computed: {
        role() {
            return this.$store.getters.profile.roles;
        },
        fields() {
            return [
                {
                    key: 'customer_name',
                    label: this.$t('CUSTOMER_MASTER_TABLE_HEADER_CUSTOMER_NAME'),
                    tdClass: 'text-center',
                    thClass: 'text-center table-customer-th',
                },
                hasRole([
                    CONST_ROLE.ACCOUNTING,
                    CONST_ROLE.SITE_MANAGER,
                    CONST_ROLE.HQ_MANAGER,
                    CONST_ROLE.DEPARTMENT_MANAGER,
                    CONST_ROLE.EXECUTIVE_OFFICER,
                    CONST_ROLE.DIRECTOR,
                    CONST_ROLE.DX_USER,
                    CONST_ROLE.DX_MANAGER,
                ], this.role)
                    ? {
                        key: 'edit',
                        label: this.$t('CUSTOMER_MASTER_TABLE_HEADER_EDIT'),
                        thClass: 'text-center th-edit table-customer-th',
                        tdClass: 'text-center td-edit',
                    } : {},
                hasRole([
                    CONST_ROLE.ACCOUNTING,
                    CONST_ROLE.SITE_MANAGER,
                    CONST_ROLE.HQ_MANAGER,
                    CONST_ROLE.DEPARTMENT_MANAGER,
                    CONST_ROLE.EXECUTIVE_OFFICER,
                    CONST_ROLE.DIRECTOR,
                    CONST_ROLE.DX_USER,
                    CONST_ROLE.DX_MANAGER,
                ], this.role)
                    ? {
                        key: 'delete',
                        label: this.$t('CUSTOMER_MASTER_TABLE_HEADER_DELETE'),
                        thClass: 'text-center th-delete table-customer-th',
                        tdClass: 'text-center td-delete',
                    } : {},
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
        this.handleGetListCustomer(1);
    },
    methods: {
        async handleChangePerPage() {
            await this.$store.dispatch('pagination/setCustomerMasterCP', 1);
            await this.$store.dispatch('pagination/setCustomerMasterPerPage', this.pagination.vPerPage);
            this.handleGetListCustomer(1);
        },
        async handleGetListCustomer(page) {
            this.overlay.show = true;

            const CUSTOMER_MASTER_PAGINATION = this.$store.getters.customerMasterCP;

            let current_page = 1;

            if (CUSTOMER_MASTER_PAGINATION) {
                current_page = CUSTOMER_MASTER_PAGINATION;
            } else {
                current_page = page;
            }

            const CUSTOMER_MASTER_PER_PAGE = this.$store.getters.customer_master_per_page;

            let per_page = 20;

            if (CUSTOMER_MASTER_PER_PAGE) {
                per_page = CUSTOMER_MASTER_PER_PAGE;
            } else {
                per_page = this.pagination.vPerPage;
            }

            try {
                const URL = urlAPI.getList || '';
                const PARAMS = {
                    page: current_page,
                    per_page: per_page,
                };

                const res = await getListCustomer(URL, PARAMS);

                if (res.code === 200) {
                    const DATA = res.data;

                    this.items = DATA.result;

                    this.pagination.vTotalRows = DATA.pagination.total_records;
                    this.pagination.vCurrentPage = DATA.pagination.current_page;
                    this.pagination.vPerPage = DATA.pagination.per_page;

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
            this.$router.push({ name: 'CustomerMasterCreate' });
        },
        onClickEdit(id = 0) {
            if (id) {
                this.$router.push({ name: 'CustomerMasterEdit', params: { id: id }});
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
        async handleDelete() {
            if (this.idHandle) {
                try {
                    this.overlay.show = true;

                    const URL = urlAPI.delete;

                    const res = await deleteCustomer(`${URL}/${this.idHandle}`);

                    if (res.code === 200) {
                        this.$toast.success({
                            content: '荷主を削除しました',
                        });

                        this.handleGetListCustomer();
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
                this.pagination.vCurrentPage = value;
                await this.$store.dispatch('pagination/setCustomerMasterCP', value);
                this.handleGetListCustomer(value);
            }
        },
    },
};
</script>

<style lang="scss" scoped>
@import "@/scss/variables";

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
  text-align: center !important;
  background-color: $tolopea !important;
  color: $white !important;
  min-width: 150px !important;
}

::v-deep .b-table-sticky-header {
  overflow-y: scroll !important;
  max-height: 850px !important;
}

.customer-master {

  &__header,
  &__handle,
  &__table,
  &__pagination {
    margin-bottom: 20px;
  }

  &__table {
    height: 850px;

    ::v-deep table {
      thead {
        th {
          text-align: center;
          background-color: $tolopea;
          color: $white;
          min-width: 150px;
        }

        th.th-edit,
        th.th-delete {
          width: 100px;
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
