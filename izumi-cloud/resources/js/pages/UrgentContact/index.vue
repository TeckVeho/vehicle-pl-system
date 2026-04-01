<template>
	<b-overlay
		:show="overlay.show"
		:blur="overlay.blur"
		:rounded="overlay.sm"
		:variant="overlay.variant"
		:opacity="overlay.opacity"
	>
		<template #overlay>
			<div class="text-center">
				<b-icon icon="arrow-clockwise" font-scale="3" animation="spin" />
				<p style="margin-top: 10px">{{ $t("PLEASE_WAIT") }}</p>
			</div>
		</template>

		<div class="urgent-contact-global-wrapper">
			<vHeaderPage>{{ $t('ROUTER_URGENT_CONTACT') }}</vHeaderPage>

			<div class="w-100" style="margin-top: 50px; margin-bottom: 50px;">
				<vUrgentContactMasterListFilter :list-department="listDepartment" />
			</div>

			<div class="d-flex justify-content-end align-items-center mt-3">
				<b-button class="export-csv-button" @click="handleExportCSV()">
					<span>エクスポート</span>
				</b-button>
			</div>

			<div class="table-urgent-contact-number mt-3">
				<div ref="tableHeader" class="table-header" @scroll="handleScrollTableHeader">
					<b-table-simple id="table-urgent-contact-number-header" bordered>
						<b-thead>
							<b-tr>
								<b-th class="table-urgent-contact-number-th th-sticky" colspan="3">所属情報</b-th>

								<b-th
									:colspan="toggle_states[0] ? 4 : 1"
									:rowspan="toggle_states[0] ? 1 : 2"
									:class="toggle_states[0] ? 'table-urgent-contact-number-th' : 'table-urgent-contact-number-th th-lg'"
								>
									<div class="d-flex justify-content-center align-items-center">
										<b-button class="button-toggle-table-header" @click="handleChangeToggleState(0)">
											<template v-if="toggle_states[0]">
												<i class="fas fa-minus-square toggle-icon" />
											</template>

											<template v-else>
												<i class="fas fa-plus-square toggle-icon" />
											</template>
										</b-button>

										<span>個人連絡先情報</span>
									</div>
								</b-th>

								<b-th
									:colspan="toggle_states[1] ? 3 : 1"
									:rowspan="toggle_states[1] ? 1 : 2"
									:class="toggle_states[1] ? 'table-urgent-contact-number-th' : 'table-urgent-contact-number-th th-lg'"
								>
									<div class="d-flex justify-content-center align-items-center">
										<b-button class="button-toggle-table-header" @click="handleChangeToggleState(1)">
											<template v-if="toggle_states[1]">
												<i class="fas fa-minus-square toggle-icon" />
											</template>

											<template v-else>
												<i class="fas fa-plus-square toggle-icon" />
											</template>
										</b-button>

										<span>緊急連絡先  情報①</span>
									</div>
								</b-th>

								<b-th
									:colspan="toggle_states[2] ? 3 : 1"
									:rowspan="toggle_states[2] ? 1 : 2"
									:class="toggle_states[2] ? 'table-urgent-contact-number-th' : 'table-urgent-contact-number-th th-lg'"
								>
									<div class="d-flex justify-content-center align-items-center">
										<b-button class="button-toggle-table-header" @click="handleChangeToggleState(2)">
											<template v-if="toggle_states[2]">
												<i class="fas fa-minus-square toggle-icon" />
											</template>

											<template v-else>
												<i class="fas fa-plus-square toggle-icon" />
											</template>
										</b-button>

										<span>緊急連絡先  情報②</span>
									</div>
								</b-th>
							</b-tr>

							<b-tr>
								<b-th class="table-urgent-contact-number-th th-md th-sticky">社員番号</b-th>
								<b-th class="table-urgent-contact-number-th th-md th-sticky-b">氏名</b-th>
								<b-th class="table-urgent-contact-number-th th-lg th-sticky-c">勤務地</b-th>

								<template v-if="toggle_states[0]">
									<b-th class="table-urgent-contact-number-th th-md">郵便番号</b-th>
									<b-th class="table-urgent-contact-number-th th-xl">住所</b-th>
									<b-th class="table-urgent-contact-number-th th-md">電話番号①</b-th>
									<b-th class="table-urgent-contact-number-th th-lg">電話番号②</b-th>
								</template>

								<template v-if="toggle_states[1]">
									<b-th class="table-urgent-contact-number-th th-lg">氏名</b-th>
									<b-th class="table-urgent-contact-number-th th-lg">統柄</b-th>
									<b-th class="table-urgent-contact-number-th th-lg">電話番号</b-th>
								</template>

								<template v-if="toggle_states[2]">
									<b-th class="table-urgent-contact-number-th th-lg">压名</b-th>
									<b-th class="table-urgent-contact-number-th th-lg">続柄</b-th>
									<b-th class="table-urgent-contact-number-th th-lg">電話番号</b-th>
								</template>
							</b-tr>
						</b-thead>
					</b-table-simple>
				</div>

				<div ref="tableContent" class="table-content" @scroll="handleScrollTableContent">
					<b-table-simple id="table-urgent-contact-number-content" bordered>
						<b-thead>
							<b-tr>
								<b-th class="table-urgent-contact-number-th" colspan="3">所属情報</b-th>

								<b-th
									:colspan="toggle_states[0] ? 4 : 1"
									:rowspan="toggle_states[0] ? 1 : 2"
									:class="toggle_states[0] ? 'table-urgent-contact-number-th' : 'table-urgent-contact-number-th th-lg'"
								>
									<div class="d-flex justify-content-center align-items-center">
										<b-button class="button-toggle-table-header">
											<template v-if="toggle_states[0]">
												<i class="fas fa-minus-square toggle-icon" />
											</template>

											<template v-else>
												<i class="fas fa-plus-square toggle-icon" />
											</template>
										</b-button>

										<span>個人連絡先情報</span>
									</div>
								</b-th>

								<b-th
									:colspan="toggle_states[1] ? 3 : 1"
									:rowspan="toggle_states[1] ? 1 : 2"
									:class="toggle_states[1] ? 'table-urgent-contact-number-th' : 'table-urgent-contact-number-th th-lg'"
								>
									<div class="d-flex justify-content-center align-items-center">
										<b-button class="button-toggle-table-header">
											<template v-if="toggle_states[1]">
												<i class="fas fa-minus-square toggle-icon" />
											</template>

											<template v-else>
												<i class="fas fa-plus-square toggle-icon" />
											</template>
										</b-button>

										<span>緊急連絡先  情報①</span>
									</div>
								</b-th>

								<b-th
									:colspan="toggle_states[2] ? 3 : 1"
									:rowspan="toggle_states[2] ? 1 : 2"
									:class="toggle_states[2] ? 'table-urgent-contact-number-th' : 'table-urgent-contact-number-th th-lg'"
								>
									<div class="d-flex justify-content-center align-items-center">
										<b-button class="button-toggle-table-header">
											<template v-if="toggle_states[2]">
												<i class="fas fa-minus-square toggle-icon" />
											</template>

											<template v-else>
												<i class="fas fa-plus-square toggle-icon" />
											</template>
										</b-button>

										<span>緊急連絡先  情報②</span>
									</div>
								</b-th>
							</b-tr>

							<b-tr>
								<b-th class="table-urgent-contact-number-th th-md th-sticky">社員番号</b-th>
								<b-th class="table-urgent-contact-number-th th-md th-sticky-b">氏名</b-th>
								<b-th class="table-urgent-contact-number-th th-lg th-sticky-c">勤務地</b-th>

								<template v-if="toggle_states[0]">
									<b-th class="table-urgent-contact-number-th th-md">郵便番号</b-th>
									<b-th class="table-urgent-contact-number-th th-xl">住所</b-th>
									<b-th class="table-urgent-contact-number-th th-md">電話番号①</b-th>
									<b-th class="table-urgent-contact-number-th th-lg">電話番号②</b-th>
								</template>

								<template v-if="toggle_states[1]">
									<b-th class="table-urgent-contact-number-th th-lg">氏名</b-th>
									<b-th class="table-urgent-contact-number-th th-lg">統柄</b-th>
									<b-th class="table-urgent-contact-number-th th-lg">電話番号</b-th>
								</template>

								<template v-if="toggle_states[2]">
									<b-th class="table-urgent-contact-number-th th-lg">压名</b-th>
									<b-th class="table-urgent-contact-number-th th-lg">続柄</b-th>
									<b-th class="table-urgent-contact-number-th th-lg">電話番号</b-th>
								</template>
							</b-tr>
						</b-thead>

						<b-tbody>
							<template v-if="urgent_contact.length === 0">
								<b-tr>
									<b-td colspan="16" class="text-center">データなし</b-td>
								</b-tr>
							</template>

							<template v-else>
								<b-tr v-for="(item, index) in urgent_contact" :key="index">
									<b-td class="table-urgent-contact-number-td td-sticky">{{ item.user?.id }}</b-td>
									<b-td class="table-urgent-contact-number-td td-sticky-b">{{ item.user?.name }}</b-td>
									<b-td class="table-urgent-contact-number-td td-sticky-c">{{ item.user?.department?.name }}</b-td>

									<template v-if="toggle_states[0]">
										<b-td class="table-urgent-contact-number-td">{{ item.post_code }}</b-td>
										<b-td class="table-urgent-contact-number-td">{{ item.address }}</b-td>
										<b-td class="table-urgent-contact-number-td">{{ item.tel }}</b-td>
										<b-td class="table-urgent-contact-number-td">{{ item.personal_tel }}</b-td>
									</template>

									<template v-else>
										<b-td class="table-urgent-contact-number-td" colspan="1" />
									</template>

									<template v-if="toggle_states[1]">
										<b-td class="table-urgent-contact-number-td">{{ item.user_contact_infos[0]?.urgent_contact_name }}</b-td>
										<b-td class="table-urgent-contact-number-td">{{ item.user_contact_infos[0]?.urgent_contact_relation }}</b-td>
										<b-td class="table-urgent-contact-number-td">{{ item.user_contact_infos[0]?.urgent_contact_tel }}</b-td>
									</template>

									<template v-else>
										<b-td class="table-urgent-contact-number-td" colspan="1" />
									</template>

									<template v-if="toggle_states[2]">
										<b-td class="table-urgent-contact-number-td">{{ item.user_contact_infos[1]?.urgent_contact_name }}</b-td>
										<b-td class="table-urgent-contact-number-td">{{ item.user_contact_infos[1]?.urgent_contact_relation }}</b-td>
										<b-td class="table-urgent-contact-number-td">{{ item.user_contact_infos[1]?.urgent_contact_tel }}</b-td>
									</template>

									<template v-else>
										<b-td class="table-urgent-contact-number-td" colspan="1" />
									</template>
								</b-tr>
							</template>
						</b-tbody>
					</b-table-simple>
				</div>
			</div>

			<div class="pagination">
				<div class="select-per-page">
					<div>
						<label for="per-page">1ページ毎の表示数</label>
					</div>

					<b-form-select
						size="sm"
						id="per-page"
						:options="optionsPerPage"
						v-model="pagination.per_page"
						@change="handleChangePerPage()"
					/>
				</div>

				<div v-if="pagination.total_rows > 20" class="show-pagination">
					<vPagination
						:next-class="'next'"
						:prev-class="'prev'"
						:per-page="pagination.per_page"
						:total-rows="pagination.total_rows"
						@currentPageChange="getCurrentPage"
						:aria-controls="'table-store-master'"
						:current-page="pagination.current_page"
					/>
				</div>
			</div>
		</div>
	</b-overlay>
</template>

<script>
const urlAPIs = {
    apiGetListUrgentContact: '/user-contacts',
    apiExportCSV: '/user-contacts/download',
    getListDepartment: '/department/list-all',
};

import { obj2Path } from '@/utils/obj2Path';
import { cleanObj } from '@/utils/handleObj';
import { getListDepartment } from '@/api/modules/employeeMaster';
import { getListUrgentContact } from '@/api/modules/urgentContact';

import vPagination from '@/components/atoms/vPagination';
import vHeaderPage from '@/components/atoms/vHeaderPage.vue';
import vUrgentContactMasterListFilter from '@/components/organisms/UrgentContactMasterListFilter';

export default {
    name: 'UrgentContactIndex',
    components: {
        vHeaderPage,
        vPagination,
        vUrgentContactMasterListFilter,
    },
    data() {
        return {
            urgent_contact: [],

            toggle_states: [true, true, true],

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

            overlay: {
                opacity: 1,
                show: false,
                blur: '1rem',
                rounded: 'sm',
                variant: 'light',
            },

            pagination: {
                per_page: 20,
                total_rows: 0,
                current_page: 1,
            },

            file: null,

            listDepartment: [],
        };
    },
    computed: {
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
        this.handleInitData();
    },
    methods: {
        async handleInitData() {
            await this.handleCreatedEventBus();
            await this.handleGetListDepartment();
            this.handleGetUrgentContactData(1);
        },
        async handleGetListDepartment() {
            try {
                this.listDepartment = [];

                const { code, data } = await getListDepartment(urlAPIs.getListDepartment);

                if (code === 200) {
                    this.listDepartment = data;
                }
            } catch (error) {
                console.log(error);
            }
        },
        handleSaveFilter(filter) {
            this.$store.dispatch('filter/setFilterUrgentContactMaster', filter);
        },
        handleCreatedEventBus() {
            this.$bus.on('URGENT_CONTACT_MASTER_FILTER_DATA', (filter) => {
                this.isFilter = filter;
            });

            this.$bus.on('URGENT_CONTACT_MASTER_ON_CLICK_APPLY', async() => {
                await this.handleSaveFilter(this.isFilter);
                this.handleGetUrgentContactData(1, true);
            });
        },
        handleDestroyedEventBus() {
            this.$bus.off('URGENT_CONTACT_MASTER_FILTER_DATA');
            this.$bus.off('URGENT_CONTACT_MASTER_ON_CLICK_APPLY');
        },
        async handleChangePerPage() {
            this.handleGetUrgentContactData(1);
        },
        async getCurrentPage(current_page) {
            if (current_page) {
                this.pagination.current_page = current_page;
                this.handleGetUrgentContactData(current_page);
            }
        },
        async handleGetUrgentContactData(current_page, is_force_reset_current_page) {
            try {
                this.overlay.show = true;

                this.urgent_contact = [];

                let current_page;

                if (is_force_reset_current_page) {
                    current_page = 1;
                } else {
                    current_page = this.pagination.current_page;
                }

                const perPage = this.pagination.per_page;

                let params = {
                    page: current_page,
                    per_page: perPage,
                    user_id: this.isFilter.userId.status ? this.isFilter.userId.value : null,
                    user_name: this.isFilter.userName.status ? this.isFilter.userName.value : '',
                    department_name: this.isFilter.departmentName.status ? this.isFilter.departmentName.value : null,
                };

                params = cleanObj(params);

                const url = `${urlAPIs.apiGetListUrgentContact}?${obj2Path(params)}`;

                const response = await getListUrgentContact(url);

                const { code, data } = response;

                if (code === 200) {
                    const { result, pagination } = data;

                    result.forEach(element => {
                        this.urgent_contact.push(element);
                    });

                    this.pagination.per_page = pagination.per_page;
                    this.pagination.total_rows = pagination.total_records;
                    this.pagination.current_page = pagination.current_page;
                }
            } catch (error) {
                this.overlay.show = false;
                console.error('[handleGetUrgentContactData] ===>', error);
            } finally {
                this.overlay.show = false;
            }
        },
        handleScrollTableHeader() {
            const source = this.$refs.tableHeader;
            const targer = this.$refs.tableContent;

            this.$nextTick(() => {
                targer.scrollLeft = source.scrollLeft;
            });
        },
        handleScrollTableContent() {
            const source = this.$refs.tableContent;
            const targer = this.$refs.tableHeader;

            this.$nextTick(() => {
                targer.scrollLeft = source.scrollLeft;
            });
        },
        handleChangeToggleState(index) {
            const value = !this.toggle_states[index];
            this.$set(this.toggle_states, index, value);
        },
        async handleExportCSV() {
            const url = `/api${urlAPIs.apiExportCSV}`;

            try {
                await fetch(url, {
                    headers: {
                        'Accept-Language': this.$store.getters.language,
                        'Authorization': this.$store.getters.token,
                        'accept': 'application/json',
                    },
                }).then(async(response) => {
                    let filename = `緊急連絡先マスタ.xlsx`;
                    filename = filename.replaceAll('"', '');

                    await response.blob().then((res) => {
                        this.file = res;
                    });

                    const fileURL = window.URL.createObjectURL(this.file);
                    const fileLink = document.createElement('a');

                    fileLink.href = fileURL;
                    fileLink.setAttribute('download', filename);
                    document.body.appendChild(fileLink);

                    fileLink.click();
                }).catch((error) => {
                    console.log(error);

                    this.$toast.danger({
                        content: this.$t('TOAST_HAVE_ERROR'),
                    });
                });

                this.file = null;
            } catch (error) {
                console.error('[handleExportCSV] ===>', error);
            }
        },
    },
};
</script>

<style scoped lang="scss">
.urgent-contact-global-wrapper {
	background-color: #FFFFFF;

	.table-urgent-contact-number {
		width: 100%;
		max-height: 650px;

		.table-header {
			#table-urgent-contact-number-header {
				max-height: 650px;
				margin: 0 !important;
				margin-bottom: 0 !important;
				border-spacing: 0 !important;
				border-collapse: separate !important;
			}

			z-index: 2;
			position: relative;
			overflow: scroll !important;
			overscroll-behavior: auto !important;

			&::-webkit-scrollbar {
				width: 8px !important;
				height: 12px !important;
			}

			&::-webkit-scrollbar-thumb:hover {
				background-color: #FF8A1F !important;
			}
		}

		.table-content {
			#table-urgent-contact-number-content {
				max-height: 650px;
				margin: 0 !important;
				border-spacing: 0 !important;
				border-collapse: separate !important;
			}

			z-index: 1;
			top: -101px;
			position: relative;
			overflow: scroll !important;
			overscroll-behavior: auto !important;
			max-height: 650px;

			&::-webkit-scrollbar {
				width: 8px !important;
				height: 0px !important;
			}
		}

		.table-urgent-contact-number-th {
			color: #FFFFFF;
			font-weight: bold;
			position: relative;
			text-align: center;
			vertical-align: middle;
			background-color: #0f0448;
		}

		.table-urgent-contact-number-td {
			color: #333333;
			text-align: center;
			vertical-align: middle;
		}

		.th-sm {
			width: 100px;
			min-width: 100px;
		}

		.th-md {
			width: 150px;
			min-width: 150px;
		}

		.th-lg {
			width: 250px;
			min-width: 250px;
		}

		.th-xl {
			width: 500px;
			min-width: 500px;
		}

		.th-sticky {
			position: sticky;
			top: 0 !important;
			left: 0 !important;
			z-index: 30 !important;
			position: -webkit-sticky;
		}

		.th-sticky-b {
			position: sticky;
			top: 0 !important;
			left: 150px !important;
			z-index: 30 !important;
			position: -webkit-sticky;
		}

		.th-sticky-c {
			position: sticky;
			top: 0 !important;
			left: 300px !important;
			z-index: 30 !important;
			position: -webkit-sticky;
		}

		.td-sticky {
			position: sticky;
			left: 0 !important;
			top: 101px !important;
			z-index: 30 !important;
			position: -webkit-sticky;
			background-color: #FFFFFF;
		}

		.td-sticky-b {
			position: sticky;
			top: 101px !important;
			left: 150px !important;
			z-index: 30 !important;
			position: -webkit-sticky;
			background-color: #FFFFFF;
		}

		.td-sticky-c {
			position: sticky;
			top: 101px !important;
			left: 300px !important;
			z-index: 30 !important;
			position: -webkit-sticky;
			background-color: #FFFFFF;
		}

		.button-toggle-table-header {
			left: 5px;
			position: absolute;
			background-color: transparent !important;

			.toggle-icon {
				font-size: 25px;
				cursor: pointer;
			}
		}
	}

	.export-csv-button {
		width: 150px;
		color: #FFFFFF;
		min-width: 150px;
		font-weight: bold;
		border-radius: 5px;
		text-align: center;
		vertical-align: middle;
		background-color: #FF8A1F !important;

		&:hover {
			opacity: .6;
		}
	}
}

.pagination {
	display: flex;
	margin-top: 50px;
	position: relative;
	align-items: center;
	justify-content: center;

	.select-per-page {
		left: 0px;
		position: absolute;

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
