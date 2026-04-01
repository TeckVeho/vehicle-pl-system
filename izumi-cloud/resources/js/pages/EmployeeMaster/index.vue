<template>
	<b-overlay
		:show="overlay.show"
		:variant="overlay.variant"
		:opacity="overlay.opacity"
		:blur="overlay.blur"
		:rounded="overlay.sm"
	>
		<!-- Template overlay -->
		<template #overlay>
			<div class="text-center">
				<b-icon icon="arrow-clockwise" font-scale="3" animation="spin" />
				<p class="text-loading">{{ $t('PLEASE_WAIT') }}</p>
			</div>
		</template>

		<div class="employee-master-list">
			<vHeaderPage class="employee-master-list__title-header">
				{{ $t('PAGE_TITLE.EMPLOYEE_MASTER') }}
			</vHeaderPage>

			<vFilterEmployeeMaster
				:list-affiliation-base="listAffiliationBase"
				:list-support-base="listSupportBase"
			/>

			<div class="employee-master-list__picker-year-month">
				<b-row>
					<b-col class="col-sm-6 mb-3">
						<div class="date-selector">
							<b-button-group>
								<b-button class="minus-btn" @click="minus()">
									<i class="fas fa-caret-left" />
								</b-button>

								<b-button class="date">
									<span>{{ date }}</span>
								</b-button>

								<b-button class="plus-btn" @click="plus()">
									<i class="fas fa-caret-right" />
								</b-button>

								<date-picker
									v-model="year_month_picker"
									type="month"
									format="YYYY-MM"
									:lang="ja_locale"
									value-type="format"
									:disabled-date="disabledDate"
									:clearable="false"
									@input="handleChangeInput"
								/>
							</b-button-group>
						</div>
					</b-col>
					<b-col class="text-right d-flex justify-content-end mr-5">
						<div class="download-file mr-3">
							<vButton
								:class="'btn-export'"
								@click.native="handleNextUploadPDF()"
							>
								<i class="far fa-file icon-history" />
								{{ $t('PDF_STORAGE') }}
							</vButton>
						</div>
						<div class="download-file mr-3">
							<vButton
								:class="'btn-export'"
								@click.native="exportCSV()"
							>
								<i class="far fa-download icon-history" />
								{{ $t('CSV_EXPORT') }}
							</vButton>
						</div>

						<div class="upload-file">
							<input
								id="fileUpload"
								ref="selectFileInput"
								type="file"
								accept=".csv,.CSV,.xlsx,.XLSX,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
								name="File Upload"
								style="display: none;"
								@change="getFileCSVInput"
							>
							<vButton
								:class="'btn-import'"
								@click.native="triggerSelectFileInput()"
							>
								<i class="far fa-upload icon_upload" />
								{{ $t('CSV_IMPORT') }}
							</vButton>
						</div>
					</b-col>
				</b-row>
			</div>

			<div class="employee-master-list__table">
				<div class="table-employee-master-header" ref="tableEmployeeMasterHeader" @scroll="handleScrollTableEmployeeMasterHeader">
					<b-table
						id="table-employee-master-list-header"
						striped
						hover
						bordered
						no-local-sorting
						no-sort-reset
						:items="[]"
						:fields="fields"
					>
						<template #empty>
							<span />
						</template>
					</b-table>
				</div>

				<div class="table-employee-master-content" ref="tableEmployeeMasterContent" @scroll="handleScrollTableEmployeeMasterContent">
					<b-table
						id="table-employee-master-list"
						show-empty
						striped
						hover
						bordered
						no-local-sorting
						no-sort-reset
						:items="items"
						:fields="fields"
						@sort-changed="handleSort"
					>
						<template #cell(detail)="scope">
							<i class="fas fa-eye icon-detail" @click="onClickDetail(scope)" />
						</template>

						<!-- <template #cell(document)="scope">
							<i class="far fa-file-invoice icon-document" @click="onClickDocument(scope)" />
							</template> -->
						<template #cell(driver_license_upload_file_flag)="scope">
							<i v-if="scope.item.driver_license_upload_file_flag === 1" class="far fa-check-circle icon-status" />
							<i v-else class="far fa-times-circle icon-xmart" />
						</template>
						<template #cell(driving_record_certificate_upload_file_flag)="scope">
							<i v-if="scope.item.driving_record_certificate_upload_file_flag === 1" class="far fa-check-circle icon-status" />
							<i v-else class="far fa-times-circle icon-xmart" />
						</template>
						<template #cell(aptitude_assessment_form_upload_file_flag)="scope">
							<i v-if="scope.item.aptitude_assessment_form_upload_file_flag === 1" class="far fa-check-circle icon-status" />
							<i v-else class="far fa-times-circle icon-xmart" />
						</template>
						<template #cell(health_examination_results_upload_file_flag)="scope">
							<i v-if="scope.item.health_examination_results_upload_file_flag === 1" class="far fa-check-circle icon-status" />
							<i v-else class="far fa-times-circle icon-xmart" />
						</template>
						<template #cell(beginner_driver_training_classroom)="scope">
							<div
								v-if="scope.item.beginner_driver_training_classroom === 1"
								:class="'btn-complete'"
							>
								{{ $t('COMPLETED') }}
							</div>
							<div
								v-else
								:class="'btn-incomplete'"
							>
								{{ $t('NOT_COMPLETED') }}
							</div>
						</template>
						<template #cell(beginner_driver_training_practical)="scope">
							<div
								v-if="scope.item.beginner_driver_training_practical === 1"
								:class="'btn-complete'"
							>
								{{ $t('COMPLETED') }}
							</div>
							<div
								v-else
								:class="'btn-incomplete'"
							>
								{{ $t('NOT_COMPLETED') }}
							</div>
						</template>

						<template #empty>
							<span>{{ $t('TABLE_EMPTY') }}</span>
						</template>
					</b-table>
				</div>
			</div>

			<div v-if="pagination.vTotalRows > 20" class="employee-master-list__pagination">
				<div class="select-per-page text-left">
					<div>
						<label for="per-page">1ページ毎の表示数</label>
					</div>
					<b-form-select
						id="per-page"
						v-model="pagination.vPerPage"
						:options="isPerPage.options"
						size="sm"
						@change="handleChangePerPage()"
					/>
				</div>

				<div class="show-pagination">
					<vPagination
						:aria-controls="'table-employee-master-list'"
						:current-page="pagination.vCurrentPage"
						:per-page="pagination.vPerPage"
						:total-rows="pagination.vTotalRows"
						:next-class="'next'"
						:prev-class="'prev'"
						@currentPageChange="getCurrentPage"
					/>
				</div>
			</div>
		</div>
	</b-overlay>
</template>

<script>

const URL_API = {
    getList: '/employee',
    getListDepartment: '/department/list-all',
    importCsvFile: '/employee/import-detail',
    exportCsvFile: '/employee/export-all',
};

import { cleanObj } from '@/utils/handleObj';
import vHeaderPage from '@/components/atoms/vHeaderPage';
import vPagination from '@/components/atoms/vPagination';
import vButton from '@/components/atoms/vButton';

import { getList, getListDepartment, importCSV } from '@/api/modules/employeeMaster';
import vFilterEmployeeMaster from '@/components/organisms/FilterEmployeeMaster';
import 'vue2-datepicker/index.css';
import DatePicker from 'vue2-datepicker';
import { obj2Path } from '@/utils/obj2Path';
export default {
    name: 'EmployeeMaster',
    components: {
        vHeaderPage,
        vFilterEmployeeMaster,
        vPagination,
        vButton,
        DatePicker,
    },
    data() {
        return {
            overlay: {
                show: false,
                variant: 'light',
                opacity: 1,
                blur: '1rem',
                rounded: 'sm',
            },

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

            listAffiliationBase: [],
            listSupportBase: [],

            eventEmitPickerYearMonth: 'EMPLOYEE_MASTER_PICKER_YEAR_MONTH_CHANGE',

            date: this.$store.getters.yearMonthPickerDriverRecorder['date'],
            year: this.$store.getters.yearMonthPickerDriverRecorder['year'],
            month: this.$store.getters.yearMonthPickerDriverRecorder['month'],

            filterQuery: {
                sort_by: null,
                sort_type: null,
            },

            items: [],

            pagination: {
                vCurrentPage: 1,
                vPerPage: 20,
                vTotalRows: 0,
            },

            isPerPage: {
                value: 20,
                options: [
                    { value: 20, text: '20' },
                    { value: 50, text: '50' },
                    { value: 100, text: '100' },
                    { value: 250, text: '250' },
                    { value: 500, text: '500' },
                ],
            },
            year_month_picker: '',
            ja_locale: {
                months: [
                    '1月',
                    '2月',
                    '3月',
                    '4月',
                    '5月',
                    '6月',
                    '7月',
                    '8月',
                    '9月',
                    '10月',
                    '11月',
                    '12月',
                ],
            },

            min_year: 2020,
            max_year: 2070,
            file: '',
        };
    },
    computed: {
        fields() {
            return [
                {
                    key: 'department_base',
                    sortable: true,
                    label: this.$t('EMPLOYEE_MASTER_TABLE_AFFILIATION_BASE'),
                    tdClass: this.handleRenderCellClass,
                    thClass: 'th-affilitation-base',
                },
                {
                    key: 'working_base',
                    sortable: true,
                    label: this.$t('EMPLOYEE_MASTER_TABLE_SUPPORT_BASE'),
                    tdClass: this.handleRenderCellClass,
                    thClass: 'th-support-base',
                },
                {
                    key: 'employee_code',
                    sortable: true,
                    label: this.$t('EMPLOYEE_MASTER_TABLE_EMPLOYEE_ID'),
                    tdClass: this.handleRenderCellClass,
                    thClass: 'th-employee-id',
                },
                {
                    key: 'name',
                    sortable: true,
                    label: this.$t('EMPLOYEE_MASTER_TABLE_EMPLOYEE_NAME'),
                    tdClass: this.handleRenderCellClass,
                    thClass: 'th-employee-name',
                },
                {
                    key: 'retirement_date',
                    sortable: true,
                    label: this.$t('EMPLOYEE_MASTER_TABLE_RETIREMENT_DATE'),
                    tdClass: this.handleRenderCellClass,
                    thClass: 'th-retirement-date',
                },
                {
                    key: 'detail',
                    sortable: false,
                    label: this.$t('EMPLOYEE_MASTER_TABLE_DETAIL'),
                    tdClass: this.handleRenderCellClass,
                    thClass: 'th-detail',
                },
                // {
                //     key: 'document',
                //     sortable: false,
                //     label: this.$t('EMPLOYEE_MASTER_TABLE_DOCUMENT'),
                //     tdClass: this.handleRenderCellClass,
                //     thClass: 'th-detail',
                // },
                {
                    key: 'driver_license_upload_file_flag',
                    sortable: false,
                    label: this.$t('DRIVERS_LICENSE'),
                    tdClass: this.handleRenderCellClass,
                    thClass: 'th-detail',
                },
                {
                    key: 'driving_record_certificate_upload_file_flag',
                    sortable: false,
                    label: this.$t('DRIVING_RECORD_CERTIFICATE'),
                    tdClass: this.handleRenderCellClass,
                    thClass: 'th-detail',
                },
                {
                    key: 'aptitude_assessment_form_upload_file_flag',
                    sortable: false,
                    label: this.$t('APTITUDE_TEST_CERTIFICATE'),
                    tdClass: this.handleRenderCellClass,
                    thClass: 'th-detail',
                },
                {
                    key: 'health_examination_results_upload_file_flag',
                    sortable: false,
                    label: this.$t('HEALTH_EXAMINATION_RESULT_NOTIFICATION'),
                    tdClass: this.handleRenderCellClass,
                    thClass: 'th-detail',
                },
                {
                    key: 'beginner_driver_training_classroom',
                    sortable: false,
                    label: this.$t('NEW_DRIVER_TRAINING_CLASSROOM'),
                    tdClass: this.handleRenderCellClass,
                    thClass: 'th-detail',
                },
                {
                    key: 'beginner_driver_training_practical',
                    sortable: false,
                    label: this.$t('NEW_DRIVER_TRAINING_PRACTICAL'),
                    tdClass: this.handleRenderCellClass,
                    thClass: 'th-detail',
                },
            ];
        },
        dateChange() {
            return this.$store.getters.yearMonthPickerDriverRecorder;
        },

        lang() {
            return this.$store.getters.language;
        },
    },
    watch: {
        filterQuery: {
            handler: async function() {
                await this.handleGetList(1, true);
            },
            deep: true,
        },

        dateChange() {
            this.date = this.$store.getters.yearMonthPickerDriverRecorder['date'];
            this.year = this.$store.getters.yearMonthPickerDriverRecorder['year'];
            this.month = this.$store.getters.yearMonthPickerDriverRecorder['month'];
        },
    },
    created() {
        this.handleCreatedEventBus();
        this.initData();
    },
    mounted() {
        this.$nextTick(() => {
            this.syncTableColumnWidths();
        });
    },
    updated() {
        this.$nextTick(() => {
            this.syncTableColumnWidths();
        });
    },
    destroyed() {
        this.handleDestroyedEventBus();
    },
    methods: {
        async handleChangePerPage() {
            await this.$store.dispatch('pagination/setEmployeeMasterCP', 1);
            await this.$store.dispatch('pagination/setEmployeeMasterPerPage', this.pagination.vPerPage);
            this.handleGetList(1);
        },
        handleRenderCellClass(value, key, item) {
            if (item['retirement_date'] !== null) {
                return 'text-center darker-bg-td';
            } else {
                return 'text-center';
            }
        },
        triggerSelectFileInput() {
            this.$refs.selectFileInput.click();
        },
        async getFileCSVInput(e) {
            const file = e?.target?.files?.[0];
            if (!file) {
                return;
            }

            const name = (file.name || '').toLowerCase();

            // chỉ cho CSV
            const isCsv = name.endsWith('.csv') || file.type === 'text/csv';

            if (!isCsv) {
                this.$toast?.danger?.({
                    content: 'File đang là Excel (.xlsx). Vui lòng mở file và Save As -> CSV rồi import lại.',
                });
                e.target.value = ''; // reset để chọn lại
                return;
            }

            await this.onClickImport(file);
        },
        async onClickImport(file) {
            try {
                const formData = new FormData();
                formData.append('file', file);
                const res = await importCSV(URL_API.importCsvFile, formData);
                if (res['code'] === 200) {
                    this.$toast.success({
                        content: this.$t('MESSAGE_IMPORT_CSV_SUCCESS'),
                    });
                    await this.handleGetList(1);
                } else {
                    console.error('Error uploading CSV:', res);
                }
            } catch (error) {
                this.$toast.danger({
                    content: error.response.data.message,
                });
                console.error('Unexpected error:', error);
            }
        },
        handleNextUploadPDF() {
            this.$router.push({ name: 'EmployeeMasterUploadPDF' });
        },

        async exportCSV() {
            const month = this.$store.getters.yearMonthPickerDriverRecorder;
            let PARAMS = {
                month: month['date'],
                employee_id: this.isFilter.employeeId.status ? this.isFilter.employeeId.value : '',
                employee_name: this.isFilter.employeeName.status ? this.isFilter.employeeName.value : '',
                department_base_id: this.isFilter.affiliationBase.status ? this.isFilter.affiliationBase.value : '',
                working_base_id: this.isFilter.supportBase.status ? this.isFilter.supportBase.value : '',
            };

            PARAMS = cleanObj(PARAMS);

            const URL = `/api${URL_API.exportCsvFile}?${obj2Path(PARAMS)}`;

            await fetch(URL, {
                headers: {
                    'Accept-Language': this.$store.getters.language,
                    'Authorization': this.$store.getters.token,
                    Accept: 'text/csv',
                },
            }).then(async(res) => {
                let filename = `従業員マスタ情報_${month['year']}年${month['month']}月.csv`;
                filename = filename.replaceAll('"', '');
                await res.blob().then((res) => {
                    this.file = res;
                });
                const fileURL = window.URL.createObjectURL(this.file);
                const fileLink = document.createElement('a');
                fileLink.href = fileURL;
                fileLink.setAttribute('download', filename);
                document.body.appendChild(fileLink);
                fileLink.click();
            })
                .catch(() => {
                    this.$toast?.danger?.({ content: this.$t('ERROR_EXPORT_CSV') });
                });
            this.file = '';
        },

        async initData() {
            await this.handleGetListDepartment();
            await this.handleGetList(1);
        },

        async minus() {
            this.month = parseInt(this.month);
            this.year = parseInt(this.year);
            if (this.month > 1) {
                this.month = this.month - 1;
            } else if (this.month === 1) {
                this.month = 12;
                this.year = this.year - 1;
            }
            this.emitData();
        },
        async plus() {
            this.month = parseInt(this.month);
            this.year = parseInt(this.year);
            if (this.month < 12) {
                this.month = this.month + 1;
            } else if (this.month === 12) {
                this.month = 1;
                this.year = this.year + 1;
            }
            this.emitData();
        },
        async emitData() {
            const DATA = {
                date: `${this.year}-${this.handleFormatMonth(this.month)}`,
                year: this.year,
                month: this.handleFormatMonth(this.month),
            };

            await this.$store.dispatch('driverRecorder/setDriverRecorderYearMonth', DATA);

            this.handleGetList(1);
        },

        handleFormatMonth(_month) {
            return _month < 10 ? `0${_month}` : _month;
        },

        disabledDate(date) {
            return date.getFullYear() < this.min_year || date.getFullYear() > this.max_year;
        },
        async handleChangeInput(event) {
            if (event){
                const DATA = {
                    date: event,
                    year: event.split('-')[0],
                    month: event.split('-')[1],
                };
                await this.$store.dispatch('driverRecorder/setDriverRecorderYearMonth', DATA);
                this.handleGetList();
            }
        },

        async handleGetListDepartment() {
            try {
                const { code, data } = await getListDepartment(URL_API.getListDepartment);

                if (code === 200) {
                    this.listAffiliationBase = data;
                    this.listSupportBase = data;
                } else {
                    this.listAffiliationBase.length = 0;
                    this.listSupportBase.length = 0;
                }
            } catch (error) {
                console.log(error);
            }
        },
        async handleGetList(page, is_force_reset_current_page) {
            try {
                this.setLoading(true);

                const EMPLOYEE_MASTER_PAGINATION = this.$store.getters.employeeMasterCP;

                let current_page = 1;

                if (is_force_reset_current_page) {
                    current_page = 1;
                } else {
                    if (EMPLOYEE_MASTER_PAGINATION) {
                        current_page = EMPLOYEE_MASTER_PAGINATION;
                    } else {
                        current_page = page;
                    }
                }

                const EMPLOYEE_MASTER_PER_PAGE = this.$store.getters.employee_master_per_page;

                let per_page = 20;

                if (EMPLOYEE_MASTER_PER_PAGE) {
                    per_page = EMPLOYEE_MASTER_PER_PAGE;
                } else {
                    per_page = this.pagination.vPerPage;
                }
                const month = this.$store.getters.yearMonthPickerDriverRecorder;
                let PARAMS = {
                    page: current_page,
                    per_page: per_page,
                    month: month['date'],
                    employee_id: this.isFilter.employeeId.status ? this.isFilter.employeeId.value : null,
                    employee_name: this.isFilter.employeeName.status ? this.isFilter.employeeName.value : '',
                    department_base_id: this.isFilter.affiliationBase.status ? this.isFilter.affiliationBase.value : null,
                    working_base_id: this.isFilter.supportBase.status ? this.isFilter.supportBase.value : null,
                    sort_by: this.filterQuery.sort_by,
                    sort_type: this.filterQuery.sort_type,
                };

                PARAMS = cleanObj(PARAMS);

                const { code, data } = await getList(URL_API.getList, PARAMS);

                if (code === 200) {
                    const { result, pagination } = data;

                    this.items = result;

                    for (let i = 0; i < this.items.length; i++) {
                        this.items[i].name = this.handleTransformName(this.items[i].name);
                    }

                    this.pagination.vTotalRows = pagination['total_records'];
                    this.pagination.vCurrentPage = pagination['current_page'];
                    this.pagination.vPerPage = pagination['per_page'];
                } else {
                    this.items.length = 0;
                }

                this.setLoading(false);
            } catch (error) {
                console.log(error);
                this.setLoading(false);
            }
        },
        handleTransformName(string) {
            if (string.length > 0) {
                return string.replaceAll('/', '');
            } else {
                return '';
            }
        },
        handleSort(ctx) {
            const SORT = {
                sort_by: null,
                sort_type: null,
            };

            const MAP_SORT_BY = {
                department_base: 'department_base',
                working_base: 'working_base',
                id: 'employee_id',
                name: 'employee_name',
                retirement_date: 'retirement_date',
            };

            SORT.sort_by = MAP_SORT_BY[ctx.sortBy] ? MAP_SORT_BY[ctx.sortBy] : null;
            SORT.sort_type = ctx.sortDesc;

            this.filterQuery = SORT;
        },
        handleCreatedEventBus() {
            this.$bus.on(this.eventEmitPickerYearMonth, async(value) => {
                this.pickerYearMonth = value;

                this.handleGetList(1);
            });

            this.$bus.on('EMPLOYEE_MASTER_FILTER_DATA', (filter) => {
                this.isFilter = filter;
            });

            this.$bus.on('EMPOLYEE_MASTER_ON_CLICK_APPLY', async() => {
                this.handleSaveFilter(this.isFilter);
                this.handleGetList(1, true);
            });
        },
        handleDestroyedEventBus() {
            this.$bus.off(this.eventEmitPickerYearMonth);
            this.$bus.off('EMPLOYEE_MASTER_FILTER_DATA');
            this.$bus.off('EMPOLYEE_MASTER_ON_CLICK_APPLY');
        },
        setLoading(status = true) {
            if ([true, false].includes(status)) {
                this.overlay.show = status;
            }
        },
        onClickDetail(scope) {
            const DATA = scope.item;

            this.$router.push({ name: 'EmployeeMasterDocument', params: { id: DATA.id }});
        },
        // onClickDocument(scope) {
        //     const DATA = scope.item;

        //     this.$router.push({ name: 'EmployeeMasterDocument', params: { id: DATA.id }});
        // },
        async getCurrentPage(value) {
            if (value) {
                this.pagination.vCurrentPage = value;
                await this.$store.dispatch('pagination/setEmployeeMasterCP', value);
                this.handleGetList(value);
            }
        },
        format2Digit(number) {
            if (!number) {
                return number;
            }

            return number >= 10 ? `${number}` : `0${number}`;
        },
        handleSaveFilter(filter) {
            this.$store.dispatch('filter/setFilterEmployeeMaster', filter);
        },
        handleScrollTableEmployeeMasterHeader() {
            const source = this.$refs.tableEmployeeMasterHeader;
            const target = this.$refs.tableEmployeeMasterContent;

            if (source && target) {
                this.$nextTick(() => {
                    target.scrollLeft = source.scrollLeft;
                });
            }
        },
        handleScrollTableEmployeeMasterContent() {
            const source = this.$refs.tableEmployeeMasterContent;
            const target = this.$refs.tableEmployeeMasterHeader;

            if (source && target) {
                this.$nextTick(() => {
                    target.scrollLeft = source.scrollLeft;
                });
            }
        },
        syncTableColumnWidths() {
            const headerTable = document.getElementById('table-employee-master-list-header');
            const contentTable = document.getElementById('table-employee-master-list');

            if (headerTable && contentTable) {
                const headerThs = headerTable.querySelectorAll('thead th');
                const contentThs = contentTable.querySelectorAll('thead th');

                if (headerThs.length === contentThs.length) {
                    headerThs.forEach((headerTh, index) => {
                        if (contentThs[index]) {
                            const contentWidth = contentThs[index].offsetWidth;
                            headerTh.style.width = `${contentWidth}px`;
                            headerTh.style.minWidth = `${contentWidth}px`;
                        }
                    });
                }
            }
        },
    },
};
</script>

<style lang="scss" scoped>
@import '@/scss/variables';

::v-deep .mx-datepicker {
  max-width: 42px !important;
}

::v-deep .mx-input {
  margin-left: 5px;
  border-radius: 5px;
  height: 39px !important;
  background-color: #0F0448;
}

::v-deep .mx-icon-calendar {
  color: #FFFFFF !important;
}

.date-selector {
  button {
    background-color: #0F0448;

    &:active {
      background-color: #0F0448;
    }

    &:focus {
      background-color: #0F0448;
    }
  }

  button.date {
    cursor: default;
    min-width: 90px;
    font-weight: 600;
    border-right: 1px solid gainsboro !important;
    border-left: 1px solid gainsboro !important;
    padding: 0 4px;
  }

  button.minus-btn,
  button.plus-btn {
    &:hover {
      opacity: .8 !important;
      background-color: #0F0448;
    }
  }

  button.plus-btn {
    border-top-right-radius: 6px !important;
    border-bottom-right-radius: 6px !important;
    border-left: 1px solid gainsboro !important;
  }
  button.minus-btn {
    border-top-left-radius: 6px !important;
    border-bottom-left-radius: 6px !important;
    border-right: 1px solid gainsboro !important;
  }
}

::v-deep .darker-bg-td {
  color: #FFFFFF !important;
  background-color: #000000 !important;
}

::-webkit-scrollbar {
  height: 3px;
}

::-webkit-scrollbar-thumb {
  border-radius: 45px;
}

.text-loading {
    margin-top: 10px;
}

.btn-complete {
    padding: 5px;
    background-color: rgb(5, 199, 5) !important;
    color: #FFFFFF !important;
    border: 1px solid rgb(5, 199, 5) !important;
    border-radius: 4px;
    // &:hover {
    //     background-color: rgb(92, 247, 92) !important;
    // }
}

.btn-incomplete {
    padding: 5px;
    background-color: rgb(243, 38, 38) !important;
    padding: 5px 10px;
    color: #FFFFFF !important;
    border: 1px solid rgb(243, 38, 38) !important;
    border-radius: 4px;
    // &:hover {
    //     background-color: rgb(243, 81, 81) !important;
    // }
}

.employee-master-list {
    overflow: hidden;
    min-height: calc(100vh - 89px);

    &__title-header,
    &__filter,
    &__picker-year-month,
    &__table  {
        margin-bottom: 20px;
    }

    &__picker-year-month {
        margin-top: 20px;
    }

    &__table {
        width: 100%;
        max-height: 850px;

        .table-employee-master-header {
            z-index: 2;
            position: relative;
            overflow-x: scroll !important;
            overflow-y: hidden !important;
            overscroll-behavior: auto !important;
            max-height: 80px;
            padding: 0;
            margin: 0;

            &::-webkit-scrollbar {
                width: 8px !important;
                height: 12px !important;
            }

            &::-webkit-scrollbar-thumb {
                background-color: #888;
                border-radius: 4px;
            }

            &::-webkit-scrollbar-thumb:hover {
                background-color: #FF8A1F !important;
            }

            &::-webkit-scrollbar-track {
                background-color: #f1f1f1;
            }

            ::v-deep #table-employee-master-list-header {
                margin: 0 !important;
                margin-bottom: 0 !important;
                border-spacing: 0;
                border-collapse: separate;
                width: 100%;

                thead {
                    tr {
                        th {
                            background-color: $tolopea;
                            color: $white;
                            min-width: 180px;

                            text-align: center;
                            vertical-align: middle;
                        }

                        th.th-affilitation-base,
                        th.th-support-base,
                        th.th-employee-id,
                        th.th-employee-name,
                        th.th-retirement-date {
                            min-width: 170px;
                        }
                    }
                }

                tbody {
                    display: none;
                }
            }
        }

        .table-employee-master-content {
            z-index: 1;
            top: -80px;
            position: relative;
            overflow-x: scroll !important;
            overflow-y: auto !important;
            overscroll-behavior: auto !important;
            max-height: 850px;

            &::-webkit-scrollbar {
                width: 8px !important;
                height: 0px !important;
            }

            &::-webkit-scrollbar-thumb {
                background-color: transparent;
            }

            &::-webkit-scrollbar-track {
                background-color: transparent;
            }

            ::v-deep #table-employee-master-list {
                margin: 0 !important;
                border-spacing: 0;
                border-collapse: separate;
                width: 100%;

                thead {
                    tr {
                        th {
                            background-color: $tolopea;
                            color: $white;
                            min-width: 180px;

                            text-align: center;
                            vertical-align: middle;
                        }

                        th.th-affilitation-base,
                        th.th-support-base,
                        th.th-employee-id,
                        th.th-employee-name,
                        th.th-retirement-date {
                            min-width: 170px;
                        }
                    }
                }

                tbody {
                    tr {
                        td {
                            text-align: center;
                            vertical-align: middle;

                            .icon-detail {
                                cursor: pointer;
                            }

                            .icon-document {
                                padding: 5px;
                                cursor: pointer;

                                &:hover {
                                    background-color: red;
                                    color: #FFFFFF
                                }
                            }

                            .icon-status {
                                margin-right: 10px;
                                color: green;
                            }

                            .icon-xmart {
                                margin-right: 10px;
                                color: red;
                            }
                        }
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

</style>
