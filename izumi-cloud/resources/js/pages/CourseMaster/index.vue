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

		<div class="course-master">
			<b-col>
				<div class="course-master__header">
					<vHeaderPage>
						{{ $t("ROUTER_COURSE_MASTER") }}
					</vHeaderPage>
				</div>

				<div class="course-master__filter">
					<vHeaderFilter>
						<template #zone-filter>
							<b-col>
								<b-row>
									<span class="text-clear-all" @click="onClickClearAll()">
										{{ $t('CLEAR_ALL') }}
									</span>
								</b-row>

								<div class="filter-item">
									<b-row>
										<b-col cols="12" sm="12" md="12" lg="6" class="reset-padding-b-col">
											<b-input-group>
												<b-input-group-prepend is-text>
													<input
														v-model="isFilter.department.status"
														class="status-filter-department"
														type="checkbox"
														@change="handleChangeDepartment"
													>
												</b-input-group-prepend>

												<b-input-group-prepend>
													<b-input-group-text class="fixed-group-text">
														<span> {{ $t('COURSE_MASTER_FILTER_LABLE_DEPARTMENT') }}</span>
													</b-input-group-text>
												</b-input-group-prepend>

												<b-form-select
													id="filter-department"
													v-model="isFilter.department.value"
													:disabled="!isFilter.department.status"
													:options="listDepartment"
												/>
											</b-input-group>
										</b-col>
									</b-row>
								</div>

								<div class="filter-item">
									<b-row>
										<b-col cols="12" sm="12" md="12" lg="6" class="reset-padding-b-col">
											<b-input-group>
												<b-input-group-prepend is-text>
													<input
														v-model="isFilter.course_id.status"
														class="status-filter-course-id"
														type="checkbox"
														@change="handleChangeCourseID"
													>
												</b-input-group-prepend>

												<b-input-group-prepend>
													<b-input-group-text class="fixed-group-text">
														<span> {{ $t('COURSE_MASTER_FILTER_LABLE_COURSE_ID') }}</span>
													</b-input-group-text>
												</b-input-group-prepend>

												<b-form-input
													id="filter-course-id"
													v-model="isFilter.course_id.value"
													:disabled="!isFilter.course_id.status"
													:placeholder="$t('COURSE_MASTER_FILTER_PLACEHOLDER_COURSE_ID')"
												/>
											</b-input-group>
										</b-col>
									</b-row>
								</div>
							</b-col>
							<div class="zone-btn-apply">
								<vButton :class="'btn-summit-filter'" :text-button="$t('BUTTON.APPLY')" @click.native="onClickApply()" />
							</div>
						</template>
					</vHeaderFilter>
				</div>

				<div class="course-master__picker-month-year">
					<b-row>
						<b-col cols="12" sm="12" md="12" lg="6" xl="6" class="mt-3">
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
						<b-col cols="12" sm="12" md="12" lg="6" xl="6" class="mt-3">
							<div class="text-right">
								<template
									v-if="hasRole([
										CONST_ROLE.CLERKS,
										CONST_ROLE.TL,
										CONST_ROLE.DEPARTMENT_OFFICE_STAFF,
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
										:class-name="'v-button-default'"
										@click.native="onClickCreate()"
									/>
								</template>

								<vButton
									:text-button="$t('BUTTON.EXPORT')"
									:class-name="'v-button-default'"
									@click.native="onClickExport()"
								/>
							</div>
						</b-col>
					</b-row>
				</div>

				<div class="course-master__table-course">
					<vTableCourse
						:picker-year-month="pickerYearMonth"
						:items="items"
						:gorvernment-holiday="gorvernmentHoliday"
						:list-department="listDepartment"
					/>
				</div>

				<div v-if="pagination.vTotalRows > 20" class="course-master__pagination">
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
							:aria-controls="'table-store-master'"
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
	</b-overlay>
</template>

<script>
import CONST_ROLE from '@/const/role';
import vButton from '@/components/atoms/vButton';
import vHeaderPage from '@/components/atoms/vHeaderPage';
import vPagination from '@/components/atoms/vPagination';
import vHeaderFilter from '@/components/atoms/vHeaderFilter';
import vTableCourse from '@/components/organisms/TableCourse';

import { hasRole } from '@/utils/hasRole';
import { cleanObj } from '@/utils/handleObj';
import { convertMonth, convertYear, getFullText } from '@/utils/convertTime';

import { changeKey } from '@/utils/changeKey';
// import { generateMonth } from '@/utils/generateTime';
import { getListDepartment, getListCourse } from '@/api/modules/courseMaster';
import 'vue2-datepicker/index.css';
import DatePicker from 'vue2-datepicker';
const urlAPI = {
    getListDepartment: '/department/list-all',
    getListCourse: '/course/schedule',
};

export default {
    name: 'CourseMaster',
    components: {
        vHeaderPage,
        vHeaderFilter,
        vButton,
        DatePicker,
        vTableCourse,
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

            isFilter: this.$store.getters.filterCourseMaster || {
                department: {
                    status: false,
                    value: null,
                },
                course_id: {
                    status: false,
                    value: '',
                },
            },
            listDepartment: [
                {
                    value: null,
                    text: this.$t('PLEASE_SELECT'),
                },
            ],
            date: this.$store.getters.yearMonthPickerDriverRecorder['date'],
            year: this.$store.getters.yearMonthPickerDriverRecorder['year'],
            month: this.$store.getters.yearMonthPickerDriverRecorder['month'],
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
            pickerYearMonth: {
                month: null,
                year: null,
                textMonth: '',
                textYear: '',
                textFull: '',
            },
            min_year: 2020,
            max_year: 2070,
            items: [],
            gorvernmentHoliday: {},

            selectedPerPage: 20,

            pagination: {
                vCurrentPage: 1,
                vPerPage: 20,
                vTotalRows: 0,
            },

            sortTable: {
                sortBy: '',
                sortType: null,
            },

            file: '',

            isProcess: false,
        };
    },
    computed: {
        role() {
            return this.$store.getters.profile.roles;
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

        dateChange() {
            return this.$store.getters.yearMonthPickerDriverRecorder;
        },

        lang() {
            return this.$store.getters.language;
        },

    },
    watch: {
        pickerYearMonth: {
            handler() {
                this.handleGetListCourse(1);
            },
            deep: true,
        },
        sortTable: {
            handler() {
                this.handleGetListCourse(1);
            },
            deep: true,
        },

        dateChange() {
            this.date = this.$store.getters.yearMonthPickerDriverRecorder['date'];
            this.year = this.$store.getters.yearMonthPickerDriverRecorder['year'];
            this.month = this.$store.getters.yearMonthPickerDriverRecorder['month'];
        },

        '$store.getters.yearMonthPickerDriverRecorder': {
            handler(newVal) {
                this.initPickerYearMonth();
            },
            deep: true,
            immediate: true,
        },
    },
    created() {
        this.initData();
        this.createEmit();
    },
    destroyed() {
        this.destroyEmit();
    },
    methods: {
        async initData() {
            await this.handleGetListDepartment();
        },
        initPickerYearMonth() {
            const month = parseInt(this.$store.getters.yearMonthPickerDriverRecorder['month']);
            const year = this.$store.getters.yearMonthPickerDriverRecorder['year'];
            this.pickerYearMonth = {
                month: month,
                year: year,
                textMonth: convertMonth(month, this.lang),
                textYear: convertYear(year, this.lang),
                textFull: getFullText(month, year, this.lang),
            };
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

            this.pickerYearMonth = {
                month: this.month,
                year: this.year,
                textMonth: convertMonth(this.month, this.lang),
                textYear: convertYear(this.year, this.lang),
                textFull: getFullText(this.month, this.year, this.lang),
            };

            await this.$store.dispatch('driverRecorder/setDriverRecorderYearMonth', DATA);

            this.handleGetListCourse(1);
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
                this.handleGetListCourse(1);
            }
        },
        async handleGetListDepartment() {
            try {
                const URL = urlAPI.getListDepartment;

                const response = await getListDepartment(URL);

                if (response.code === 200) {
                    let DATA = response.data;

                    DATA = changeKey(DATA, 'id', 'department_name');

                    this.listDepartment = [...this.listDepartment, ...DATA];
                }
            } catch (error) {
                console.log(error);
            }
        },
        createEmit() {
            this.$bus.on('COURSE_MASTER_PICKER_MONTH_YEAR_CHANGE', (value) => {
                this.pickerYearMonth = value;
            });

            this.$bus.on('COURSE_MASTER_TABLE_SORT_CHANGE', (value) => {
                this.sortTable = value;
            });

            this.$bus.on('COURSE_MASTER_TABLE_DELETE_SUCCESS', () => {
                this.handleGetListCourse(1);
            });
        },
        destroyEmit() {
            this.$bus.off('COURSE_MASTER_PICKER_MONTH_YEAR_CHANGE');
            this.$bus.off('COURSE_MASTER_TABLE_SORT_CHANGE');
            this.$bus.off('COURSE_MASTER_TABLE_DELETE_SUCCESS');
        },
        async handleGetListCourse(page, is_force_reset_current_page) {
            this.isProcess = true;

            const COURSE_MASTER_PAGINATION = this.$store.getters.courseMasterCP;

            let current_page = 1;

            if (is_force_reset_current_page) {
                current_page = 1;
            } else {
                if (COURSE_MASTER_PAGINATION) {
                    current_page = COURSE_MASTER_PAGINATION;
                } else {
                    current_page = page;
                }
            }

            const COURSE_MASTER_PER_PAGE = this.$store.getters.course_master_per_page;

            let per_page = 20;

            if (COURSE_MASTER_PER_PAGE) {
                per_page = COURSE_MASTER_PER_PAGE;
            } else {
                per_page = this.pagination.vPerPage;
            }

            try {
                this.overlay.show = true;

                const URL = urlAPI.getListCourse;
                const month = this.$store.getters.yearMonthPickerDriverRecorder;
                let PARAMS = {
                    page: current_page,
                    per_page: per_page,
                    month: month['date'],
                    course_code: this.isFilter.course_id.status ? this.isFilter.course_id.value : null,
                    department: this.isFilter.department.status ? this.isFilter.department.value : null,
                };

                if (this.sortTable.sortBy) {
                    PARAMS.order_by = this.sortTable.sortBy;
                    PARAMS.order_type = this.sortTable.sortType ? 'asc' : 'desc';
                }

                PARAMS = cleanObj(PARAMS);

                const response = await getListCourse(URL, PARAMS);

                if (response.code === 200) {
                    const DATA = response.data;

                    this.items = DATA.schedule.data;
                    this.gorvernmentHoliday = DATA.gorvernment_holiday;

                    this.pagination.vTotalRows = DATA.schedule.total;
                    this.pagination.vCurrentPage = DATA.schedule.current_page;
                    this.pagination.vPerPage = parseInt(DATA.schedule.per_page);
                } else {
                    this.items = [];
                    this.gorvernmentHoliday = {};
                }

                this.overlay.show = false;
            } catch (error) {
                this.overlay.show = false;

                this.$toast.danger({
                    content: error.response.data.message || 'UNDEFINED_ERROR',
                });
            }

            this.isProcess = false;
        },
        onClickClearAll() {
            const IS_FILER = {
                department: {
                    status: false,
                    value: null,
                },
                course_id: {
                    status: false,
                    value: '',
                },
            };

            this.isFilter = IS_FILER;
        },
        onClickApply() {
            this.handleSaveFilter(this.isFilter);
            this.handleGetListCourse(1, true);
        },
        onClickCreate() {
            this.$router.push({ name: 'CourseMasterCreate' });
        },
        async onClickExport() {
            const month = this.$store.getters.yearMonthPickerDriverRecorder;
            const PARAMS = {
                department: this.isFilter.department.status ? this.isFilter.department.value : null,
                course_code: this.isFilter.course_id.status ? this.isFilter.course_id.value : null,
                month: month['date'],
            };

            let URL = '/api/course/schedule?is_export=true';

            if (PARAMS.department) {
                URL += `&department=${PARAMS.department}`;
            }

            if (PARAMS.course_code) {
                URL += `&course_code=${PARAMS.course_code}`;
            }

            if (PARAMS.month) {
                URL += `&month=${PARAMS.month}`;
            }

            await fetch(URL, {
                headers: {
                    'Accept-Language': this.$store.getters.language,
                    'Authorization': this.$store.getters.token,
                    'accept': 'application/json',
                },
            }).then(async(res) => {
                let filename = res.headers.get('content-disposition').split('filename=')[1] || 'CourseMaster';
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
                    this.$toast.danger({
                        content: this.$t('TOAST_HAVE_ERROR'),
                    });
                });

            this.file = '';
        },
        async getCurrentPage(value) {
            if (value) {
                this.pagination.vCurrentPage = value;
                await this.$store.dispatch('pagination/setCourseMasterCP', value);
                this.handleGetListCourse(value);
            }
        },
        async handleChangePerPage() {
            await this.$store.dispatch('pagination/setCourseMasterCP', 1);
            await this.$store.dispatch('pagination/setCourseMasterPerPage', this.pagination.vPerPage);
            this.handleGetListCourse(1);
        },
        handleSaveFilter(filter) {
            this.$store.dispatch('filter/setFilterCourseMaster', filter);
        },
        handleChangeDepartment() {
            if (!this.isFilter.department.status) {
                this.isFilter.department.value = null;
            }
        },
        handleChangeCourseID() {
            if (!this.isFilter.course_id.status) {
                this.isFilter.course_id.value = null;
            }
        },
    },
};
</script>

<style lang="scss" scoped>
  @import '@/scss/variables.scss';

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

  ::v-deep .fixed-group-text {
    display: flex;
    min-width: 150px;
    justify-content: center;
  }

	.text-overlay {
		margin-top: 10px;
	}

	.course-master {
		overflow: hidden;
		min-height: calc(100vh - 89px);

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

		&__header,
		&__filter,
    &__picker-month-year,
    &__handle,
    &__table-course {
			margin-bottom: 20px;
		}

    &__filter {
      span.text-clear-all {
      border-top: 1px solid $black;
      border-bottom: 1px solid $black;
      font-weight: 500;
      margin-bottom: 20px;
      cursor: pointer;
      }

      .filter-item {
        margin-bottom: 10px;
      }

      .reset-padding-b-col {
        padding-left: 0;
      }
    }

    &__handle {
      text-align: right;

      .v-button-default:not(:last-child) {
        margin-right: 10px;
      }
    }
	}
</style>
