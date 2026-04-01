<template>
	<div class="zone-table-course">
		<div class="show-table">
			<div ref="tableCourseHeader" class="table-course-header" @scroll="handleScrollTableHeader">
				<b-table-simple id="table-course" class="table-course" bordered>
					<b-thead>
						<b-tr>
							<b-th :rowspan="2" :class="['department', 'sort', visibleData ? '' : 'pading-header' ]" @click="onSortTable('department_id')">
								<div class="d-flex justify-content-center">
									<div>
										{{ $t('COURSE_MASTER_TABLE_HEADER_DEPARTMENT') }}
									</div>
									<div>
										<i v-if="sortTable.sortBy === 'department_id' && sortTable.sortType === true" class="fad fa-sort-up icon-sort" />
										<i v-else-if="sortTable.sortBy === 'department_id' && sortTable.sortType === false" class="fad fa-sort-down icon-sort" />
										<i v-else class="fad fa-sort icon-sort" />
									</div>
								</div>
							</b-th>

							<b-th :rowspan="2" :class="['course-id', 'sort', visibleData ? '' : 'pading-header']" @click="onSortTable('course_code')">
								<div class="d-flex justify-content-center">
									<div>
										{{ $t('COURSE_MASTER_TABLE_HEADER_COURSE_ID') }}
									</div>
									<div>
										<i v-if="sortTable.sortBy === 'course_code' && sortTable.sortType === true" class="fad fa-sort-up icon-sort" />
										<i v-else-if="sortTable.sortBy === 'course_code' && sortTable.sortType === false" class="fad fa-sort-down icon-sort" />
										<i v-else class="fad fa-sort icon-sort" />
									</div>
								</div>
							</b-th>

							<b-th :rowspan="2" :class="['total-fare', 'sort', visibleData ? '' : 'pading-header']" @click="onSortTable('fare')">
								<div class="d-flex justify-content-center">
									<div>
										{{ $t('COURSE_MASTER_TABLE_HEADER_TOTAL_FARE') }}
									</div>
									<div>
										<i v-if="sortTable.sortBy === 'fare' && sortTable.sortType === true" class="fad fa-sort-up icon-sort" />
										<i v-else-if="sortTable.sortBy === 'fare' && sortTable.sortType === false" class="fad fa-sort-down icon-sort" />
										<i v-else class="fad fa-sort icon-sort" />
									</div>
								</div>
							</b-th>

							<b-th :rowspan="2" :class="['highway-fee-total', 'sort', visibleData ? '' : 'pading-header']" @click="onSortTable('highway_fare')">
								<div class="d-flex justify-content-center">
									<div>
										{{ $t('COURSE_MASTER_TABLE_HEADER_HIGHWAY_FEE_TOTAL') }}
									</div>
									<div>
										<i v-if="sortTable.sortBy === 'highway_fare' && sortTable.sortType === true" class="fad fa-sort-up icon-sort" />
										<i v-else-if="sortTable.sortBy === 'highway_fare' && sortTable.sortType === false" class="fad fa-sort-down icon-sort" />
										<i v-else class="fad fa-sort icon-sort" />
									</div>
								</div>
							</b-th>

							<b-th :rowspan="2" class="delivery-route">
								{{ $t('COURSE_MASTER_TABLE_HEADER_DELIVERY_ROUTE') }}
							</b-th>

							<b-th :rowspan="2" class="delivery-route">
								{{ $t('COURSE_MASTER_FORM_LABLE_COURSE_ADDRESS') }}
							</b-th>

							<b-th v-if="visibleData === false" :rowspan="2" class="show-col">
								<i class="fas fa-plus-square" @click="toggleShowCol()" />
							</b-th>

							<b-th v-if="visibleData === true" :colspan="numberDate + 1" class="hidden-col">
								<i class="fas fa-minus-square" @click="toggleShowCol()" />
								{{ $t('COURSE_MASTER_TABLE_HEADER_DAILY_FARE_TABLE') }}
							</b-th>

							<b-th :rowspan="2" class="handle">
								{{ $t('COURSE_MASTER_TABLE_HEADER_EDIT') }}
							</b-th>

							<template
								v-if="hasRole(
									[
										CONST_ROLE.CLERKS,
										CONST_ROLE.TL,
										CONST_ROLE.DEPARTMENT_OFFICE_STAFF,
										CONST_ROLE.AM_SM,
										CONST_ROLE.DIRECTOR,
										CONST_ROLE.DX_USER,
										CONST_ROLE.DX_MANAGER,
									],
									role)
								"
							>
								<b-th :rowspan="2" class="handle">
									{{ $t('COURSE_MASTER_TABLE_HEADER_DELETE') }}
								</b-th>
							</template>
						</b-tr>

						<b-tr v-if="visibleData === true">
							<b-th class="total-operating-days">
								{{ $t('COURSE_MASTER_TABLE_HEADER_TOTAL_OPERATING_DAYS') }}
							</b-th>

							<b-th
								v-for="date in numberDate"
								:key="`table-course-date-th-${date}`"
								:class="
									[
										'show-date', gorvernmentHoliday[`${date}`] ?
											'gorvernment-holiday' :
											'',
										['DAY.SAT', 'DAY.SUN'].includes(getDayInWeek(pickerYearMonth.year, pickerYearMonth.month, date)) ?
											'gorvernment-holiday' :
											''
									]"
							>
								<span>{{ $t(getDayInWeek(pickerYearMonth.year, pickerYearMonth.month, date)) }}</span>
								<br>
								<span>{{ date }}</span>
							</b-th>
						</b-tr>
					</b-thead>
				</b-table-simple>
			</div>

			<div ref="tableCourseContent" :class="[visibleData ? 'type-2' : 'type-1' , `table-course-content`]" @scroll="handleScrollTableContent">
				<b-table-simple id="table-course" class="table-course" bordered>
					<b-thead>
						<b-tr>
							<b-th :rowspan="2" :class="['department', 'sort', visibleData ? '' : 'pading-header' ]" @click="onSortTable('department_id')">
								<div class="d-flex justify-content-center">
									<div>
										{{ $t('COURSE_MASTER_TABLE_HEADER_DEPARTMENT') }}
									</div>
									<div>
										<i v-if="sortTable.sortBy === 'department_id' && sortTable.sortType === true" class="fad fa-sort-up icon-sort" />
										<i v-else-if="sortTable.sortBy === 'department_id' && sortTable.sortType === false" class="fad fa-sort-down icon-sort" />
										<i v-else class="fad fa-sort icon-sort" />
									</div>
								</div>
							</b-th>

							<b-th :rowspan="2" :class="['course-id', 'sort', visibleData ? '' : 'pading-header']" @click="onSortTable('course_code')">
								<div class="d-flex justify-content-center">
									<div>
										{{ $t('COURSE_MASTER_TABLE_HEADER_COURSE_ID') }}
									</div>
									<div>
										<i v-if="sortTable.sortBy === 'course_code' && sortTable.sortType === true" class="fad fa-sort-up icon-sort" />
										<i v-else-if="sortTable.sortBy === 'course_code' && sortTable.sortType === false" class="fad fa-sort-down icon-sort" />
										<i v-else class="fad fa-sort icon-sort" />
									</div>
								</div>
							</b-th>

							<b-th :rowspan="2" :class="['total-fare', 'sort', visibleData ? '' : 'pading-header']" @click="onSortTable('fare')">
								<div class="d-flex justify-content-center">
									<div>
										{{ $t('COURSE_MASTER_TABLE_HEADER_TOTAL_FARE') }}
									</div>
									<div>
										<i v-if="sortTable.sortBy === 'fare' && sortTable.sortType === true" class="fad fa-sort-up icon-sort" />
										<i v-else-if="sortTable.sortBy === 'fare' && sortTable.sortType === false" class="fad fa-sort-down icon-sort" />
										<i v-else class="fad fa-sort icon-sort" />
									</div>
								</div>
							</b-th>

							<b-th :rowspan="2" :class="['highway-fee-total', 'sort', visibleData ? '' : 'pading-header']" @click="onSortTable('highway_fare')">
								<div class="d-flex justify-content-center">
									<div>
										{{ $t('COURSE_MASTER_TABLE_HEADER_HIGHWAY_FEE_TOTAL') }}
									</div>
									<div>
										<i v-if="sortTable.sortBy === 'highway_fare' && sortTable.sortType === true" class="fad fa-sort-up icon-sort" />
										<i v-else-if="sortTable.sortBy === 'highway_fare' && sortTable.sortType === false" class="fad fa-sort-down icon-sort" />
										<i v-else class="fad fa-sort icon-sort" />
									</div>
								</div>
							</b-th>

							<b-th :rowspan="2" class="delivery-route">
								{{ $t('COURSE_MASTER_TABLE_HEADER_DELIVERY_ROUTE') }}
							</b-th>

							<b-th :rowspan="2" class="delivery-route">
								{{ $t('COURSE_MASTER_FORM_LABLE_COURSE_ADDRESS') }}
							</b-th>

							<b-th v-if="visibleData === false" :rowspan="2" class="show-col">
								<i class="fas fa-plus-square" @click="toggleShowCol()" />
							</b-th>

							<b-th v-if="visibleData === true" :colspan="numberDate + 1" class="hidden-col">
								<i class="fas fa-minus-square" @click="toggleShowCol()" />
								{{ $t('COURSE_MASTER_TABLE_HEADER_DAILY_FARE_TABLE') }}
							</b-th>

							<b-th :rowspan="2" class="handle">
								{{ $t('COURSE_MASTER_TABLE_HEADER_EDIT') }}
							</b-th>

							<template
								v-if="hasRole(
									[
										CONST_ROLE.CLERKS,
										CONST_ROLE.TL,
										CONST_ROLE.DEPARTMENT_OFFICE_STAFF,
										CONST_ROLE.AM_SM,
										CONST_ROLE.DIRECTOR,
										CONST_ROLE.DX_USER,
										CONST_ROLE.DX_MANAGER,
									],
									role)
								"
							>
								<b-th :rowspan="2" class="handle">
									{{ $t('COURSE_MASTER_TABLE_HEADER_DELETE') }}
								</b-th>
							</template>
						</b-tr>

						<b-tr v-if="visibleData === true">
							<b-th class="total-operating-days">
								{{ $t('COURSE_MASTER_TABLE_HEADER_TOTAL_OPERATING_DAYS') }}
							</b-th>

							<b-th
								v-for="date in numberDate"
								:key="`table-course-date-th-${date}`"
								:class="
									[
										'show-date', gorvernmentHoliday[`${date}`] ?
											'gorvernment-holiday' :
											'',
										['DAY.SAT', 'DAY.SUN'].includes(getDayInWeek(pickerYearMonth.year, pickerYearMonth.month, date)) ?
											'gorvernment-holiday' :
											''
									]"
							>
								<span>{{ $t(getDayInWeek(pickerYearMonth.year, pickerYearMonth.month, date)) }}</span>
								<br>
								<span>{{ date }}</span>
							</b-th>
						</b-tr>
					</b-thead>

					<b-tbody v-if="items.length > 0">
						<b-tr v-for="(item, idx) in items" :key="`row-${idx + 1}`">
							<b-td class="department">
								{{ getNameDepartment(listDepartment, item.department_id) }}
							</b-td>

							<b-td class="course-id">
								{{ item.course_code }}
							</b-td>

							<b-td>
								{{ formatNumberWithCommas(item.fare) }}
							</b-td>

							<b-td>
								{{ formatNumberWithCommas(item.highway_fare) }}
							</b-td>

							<b-td class="list-route">
								<div class="zone-fare">
									<template v-if="Array.isArray(item.routes)">
										<template v-if="item.routes.length > 2">
											<div class="show-route">{{ item.routes[0].name }}</div>
											<div class="show-route">{{ item.routes[1].name }}</div>
											<div class="show-all-route">
												<i :id="`show-route-${item.id}`" class="far fa-ellipsis-h" />
											</div>
											<b-popover :target="`show-route-${item.id}`" triggers="hover">
												<template #title>
													<b>ルート一覧</b>
												</template>
												<ul class="list-group list-group-flush">
													<li v-for="(route, idxListGroup) in item.routes" :key="`list-route-${idxListGroup + 1}`" class="list-group-item">
														{{ route.name }}
													</li>
												</ul>
											</b-popover>
										</template>
										<template v-else>
											<div v-for="(route, idxRoute) in item.routes" :key="`route-${idxRoute + 1}`" class="show-route">
												{{ route.name }}
											</div>
										</template>
									</template>
								</div>
							</b-td>

							<b-td class="course-id">
								{{ item.address }}
							</b-td>

							<b-td v-if="visibleData === false" />

							<b-td v-if="visibleData === true">
								{{ item.operating_day }}
							</b-td>

							<template v-if="visibleData === true">
								<template v-for="date in numberDate">
									<template v-if="Array.isArray(item.schedule[`${date}`])">
										<b-td :key="`table-course-date-td-${date}`" :class="['show-fare', item.schedule[`${date}`].length === 0 ? 'no-route' : '']">
											<div class="zone-fare">
												<template v-if="item.schedule[`${date}`].length > 2">
													<div class="show-route">{{ item.schedule[`${date}`][0].route_name }}</div>
													<div class="show-route">{{ item.schedule[`${date}`][1].route_name }}</div>
													<div class="show-all-route">
														<i :id="`show-route-${item.id}-${date}`" class="far fa-ellipsis-h" />
													</div>
													<b-popover :target="`show-route-${item.id}-${date}`" triggers="hover">
														<template #title>
															<b>ルート一覧</b>
														</template>
														<ul class="list-group list-group-flush">
															<li v-for="(route, idxListGroup) in item.schedule[`${date}`]" :key="`list-route-${idxListGroup + 1}`" class="list-group-item">
																{{ route.route_name }}
															</li>
														</ul>
													</b-popover>
												</template>
												<template v-else>
													<div v-for="(route, idxRoute) in item.schedule[`${date}`]" :key="`route-${idxRoute + 1}`" class="show-route">
														{{ route.route_name }}
													</div>
												</template>
											</div>
										</b-td>
									</template>
								</template>
							</template>

							<b-td class="handle">
								<i class="fas fa-eye" @click="onClickEdit(item.id)" />
								<!-- <i class="fas fa-pen" /> -->
							</b-td>

							<template
								v-if="hasRole(
									[
										CONST_ROLE.CLERKS,
										CONST_ROLE.TL,
										CONST_ROLE.DEPARTMENT_OFFICE_STAFF,
										CONST_ROLE.AM_SM,
										CONST_ROLE.DIRECTOR,
										CONST_ROLE.DX_USER,
										CONST_ROLE.DX_MANAGER,
									],
									role
								)"
							>
								<b-td class="handle">
									<i class="fas fa-trash" @click="onClickDelete(item.id)" />
								</b-td>
							</template>
						</b-tr>
					</b-tbody>

					<b-tbody v-else>
						<b-tr>
							<b-td :colspan="visibleData === true ? 39 : 8">
								<div class="text-center">
									<span>{{ 'テーブルは空です' }}</span>
								</div>
							</b-td>
						</b-tr>
					</b-tbody>
				</b-table-simple>
			</div>
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
				<span>このコースを削除してもよろしいですか？</span>
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
	</div>
</template>

<script>
import CONST_ROLE from '@/const/role';

import { hasRole } from '@/utils/hasRole';
import { fakeDataFare } from '@/const/fake';
import { deleteCourse } from '@/api/modules/courseMaster';
import { getNameDepartment, getNameRoutes, getDayInWeek } from '@/utils/getNameSelect';

const urlAPI = {
    deleteCourse: '/course',
};

export default {
    name: 'TableCourse',
    props: {
        pickerYearMonth: {
            type: Object,
            default: () => {
                return {
                    month: null,
                    year: null,
                    textMonth: '',
                    textYear: '',
                    textFull: '',
                };
            },
            required: true,
        },
        items: {
            type: Array,
            default: () => {
                return [];
            },
            required: true,
        },
        listDepartment: {
            type: Array,
            default: () => {
                return [];
            },
            required: true,
        },
        gorvernmentHoliday: {
            type: [Object, Array],
            default: () => {
                return {};
            },
            required: true,
        },
    },
    data() {
        return {
            CONST_ROLE,
            hasRole,
            getNameDepartment,
            getNameRoutes,
            getDayInWeek,

            visibleData: true,

            numberDate: 0,
            fakeDataFare,
            showModal: false,

            sortTable: {
                sortBy: '',
                sortType: null,
            },

            idHandle: null,
        };
    },
    computed: {
        role() {
            return this.$store.getters.profile.roles;
        },
        course_master_index_is_show_full() {
            return JSON.parse(window.localStorage.getItem('course_master_index_is_show_full'));
        },
    },
    watch: {
        pickerYearMonth: {
            // handler() {
            //   console.log('update: ', this.pickerYearMonth);
            //     this.numberDate = this.getNumberDate();
            // },
            handler: function handler(props_data) {
                if (props_data) {
                    this.numberDate = this.getNumberDate();
                }
            },
            deep: true,
            immediate: true,
        },
        sortTable: {
            handler() {
                this.$bus.emit('COURSE_MASTER_TABLE_SORT_CHANGE', this.sortTable);
            },
            deep: true,
        },
        pagination: {
            handler() {
                this.vPagination = this.pagination;
            },
            deep: true,
        },
    },
    created() {
        this.handleOverrideIsShowFullStatus();
    },
    methods: {
        formatNumberWithCommas(number) {
            if (number) {
                return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            } else {
                return 0;
            }
        },
        handleOverrideIsShowFullStatus() {
            if (this.course_master_index_is_show_full === null || this.course_master_index_is_show_full === false) {
                this.visibleData = false;
            } else {
                this.visibleData = true;
            }
        },
        toggleShowCol() {
            this.visibleData = !this.visibleData;
            window.localStorage.setItem('course_master_index_is_show_full', JSON.stringify(this.visibleData));
        },
        onSortTable(col) {
            switch (col) {
            case 'department_id':
                if (this.sortTable.sortBy === 'department_id') {
                    if (this.sortTable.sortType) {
                        this.sortTable.sortType = !this.sortTable.sortType;
                    } else {
                        this.sortTable.sortType = true;
                    }
                } else {
                    this.sortTable.sortBy = 'department_id';
                    this.sortTable.sortType = true;
                }

                break;

            case 'course_code':
                if (this.sortTable.sortBy === 'course_code') {
                    if (this.sortTable.sortType) {
                        this.sortTable.sortType = !this.sortTable.sortType;
                    } else {
                        this.sortTable.sortType = true;
                    }
                } else {
                    this.sortTable.sortBy = 'course_code';
                    this.sortTable.sortType = true;
                }

                break;

            case 'fare':
                if (this.sortTable.sortBy === 'fare') {
                    if (this.sortTable.sortType) {
                        this.sortTable.sortType = !this.sortTable.sortType;
                    } else {
                        this.sortTable.sortType = true;
                    }
                } else {
                    this.sortTable.sortBy = 'fare';
                    this.sortTable.sortType = true;
                }

                break;

            case 'highway_fare':
                if (this.sortTable.sortBy === 'highway_fare') {
                    if (this.sortTable.sortType) {
                        this.sortTable.sortType = !this.sortTable.sortType;
                    } else {
                        this.sortTable.sortType = true;
                    }
                } else {
                    this.sortTable.sortBy = 'highway_fare';
                    this.sortTable.sortType = true;
                }

                break;

            default:
                console.log('Handle sort table faild');

                break;
            }
        },
        getNumberDate() {
            if (this.pickerYearMonth.month && this.pickerYearMonth.year) {
                const d = new Date(this.pickerYearMonth.year, this.pickerYearMonth.month, 0);
                console.log(': ', d);

                return d.getDate();
            }

            return 0;
        },
        onClickEdit(id) {
            this.$router.push({
                name: 'CourseMasterDetail',
                params: {
                    id: id,
                },
            });
        },
        onClickDelete(id) {
            if (id) {
                this.idHandle = id;
                this.showModal = true;
            }
        },
        async handleDelete() {
            if (this.idHandle) {
                try {
                    this.showModal = false;

                    const URL = `${urlAPI.deleteCourse}/${this.idHandle}`;

                    const response = await deleteCourse(URL);

                    if (response.code === 200) {
                        this.$bus.emit('COURSE_MASTER_TABLE_DELETE_SUCCESS', this.idHandle);

                        this.$toast.success({
                            content: 'コースが削除しました',
                        });
                    }
                } catch (error) {
                    this.$toast.danger({
                        content: error.response.data.message || 'UNDEFINED_ERROR',
                    });
                }
            }
        },
        getCurrentPage(value) {
            if (value) {
                this.pagination.vCurrentPage = value;
                this.$bus.emit('COURSE_MASTER_TABLE_PER_PAGE_CHANGE', this.pagination);
            }
        },
        handleScrollTableHeader() {
            const source = this.$refs.tableCourseHeader;
            const target = this.$refs.tableCourseContent;

            this.$nextTick(() => {
                target.scrollLeft = source.scrollLeft;
            });
        },
        handleScrollTableContent() {
            const source = this.$refs.tableCourseContent;
            const target = this.$refs.tableCourseHeader;

            this.$nextTick(() => {
                target.scrollLeft = source.scrollLeft;
            });
        },
    },
};
</script>

<style lang="scss" scoped>
@import '@/scss/variables.scss';

::-webkit-scrollbar {
  width: 8px;
  height: 12px;
}

::-webkit-scrollbar-thumb {
    border-radius: 45px;
}

.course-master {
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

.show-table {
  height: 650px;

  .table-course-header {
    position: relative;
    z-index: 2 !important;
    overflow: scroll !important;
    overscroll-behavior: auto !important;

    &::-webkit-scrollbar {
      width: 8px;
      height: 12px;
    }

    &::-webkit-scrollbar-thumb:hover {
      background-color: #FF8A1F !important;
    }

    table#table-course {
      border-spacing: 0;
      margin: 0 !important;
      border-collapse: separate;

      thead {
        tr {
          th {
            top: 0;
            color: $white;
            min-width: 50px;
            font-size: 14px;
            position: sticky;
            padding: 0.25rem;
            text-align: center;
            vertical-align: middle;
            background-color: $tolopea;
          }

          th.total-operating-days {
            top: 30.5px;
          }

          th.show-date {
            top: 30.5px;
            width: 120px !important;
            min-width: 120px !important;
          }

          th.sort {
            cursor: pointer;

            i {
                margin-left: 10px;
                font-size: 15px;
            }
          }

          th.department,
          th.course-id,
          th.total-fare,
          th.highway-fee-total,
          th.total-operating-days  {
            min-width: 150px;
            width: 150px !important;
          }

          th.delivery-route {
            min-width: 150px;
            width: 150px !important;
          }

          th.show-col,
          th.hidden-col {
            width: 20px;

            i {
                font-size: 20px;
                cursor: pointer;
            }
          }

          th.hidden-col {
            i {
                float: left;
            }
          }

          th.handle {
            width: 50px;
          }

          th.pading-header {
            height: 61.5px;
          }

          th.department {
            position: sticky;
            top: 0;
            left: 0;

            z-index: 2;
          }

          th.course-id {
            position: sticky;
            top: 0;
            left: 150px;

            z-index: 2;
          }

          th.gorvernment-holiday {
            background-color: red;
          }
        }
      }

      tbody {
        td {
          background-color: $white;
        }

        td:not(.show-fare) {
          padding: 0.25rem;

          text-align: center;
          vertical-align: middle;

          font-size: 14px;
        }

        td.department {
          position: sticky;
          left: 0;

          z-index: 1;
        }

        td.course-id {
          position: sticky;
          left: 150px;

          z-index: 1;
        }

        td.list-route {
          padding: 0;
          height: 100%;

          .zone-fare {
            height: 80px;
            // width: 150px;

            div.show-route {
              height: 40%;
              font-size: 12px;

              display: flex;
              justify-content: center;
              align-items: center;
            }

            div.show-all-route {
              height: 20%;
              font-size: 15px;

              display: flex;
              justify-content: center;
              align-items: center;

              i {
                margin-right: 10px;
              }
            }
          }
        }

        td.show-fare {
          padding: 0;
          height: 100%;

          .zone-fare {
            height: 80px;
            width: 100px;

            div.show-route {
              height: 40%;
              font-size: 12px;

              display: flex;
              justify-content: center;
              align-items: center;
            }

            div.show-all-route {
              height: 20%;
              font-size: 15px;

              display: flex;
              justify-content: center;
              align-items: center;

              i {
                margin-right: 10px;
              }
            }
          }
        }

        td.handle {
          i {
              cursor: pointer;
          }
        }

        td.no-route {
          background-color: $rolling-stone;
        }
      }
    }
  }

  .table-course-content {
    height: 650px;
    position: relative;
    z-index: 1 !important;
    overflow: scroll !important;
    overscroll-behavior: auto !important;

    &::-webkit-scrollbar {
      width: 8px !important;
      height: 0px !important;
    }

    table#table-course {
      border-spacing: 0;
      border-collapse: separate;

      thead {
        tr {
          th {
            top: 0;
            color: $white;
            min-width: 50px;
            font-size: 14px;
            position: sticky;
            padding: 0.25rem;
            text-align: center;
            vertical-align: middle;
            background-color: $tolopea;
          }

          th.total-operating-days {
            top: 30.5px;
          }

          th.show-date {
            top: 30.5px;
            width: 120px !important;
            min-width: 120px !important;
          }

          th.sort {
            cursor: pointer;

            i {
                margin-left: 10px;
                font-size: 15px;
            }
          }

          th.department,
          th.course-id,
          th.total-fare,
          th.highway-fee-total,
          th.total-operating-days  {
            min-width: 150px;
            width: 150px !important;
          }

          th.delivery-route {
            min-width: 150px;
            width: 150px !important;
          }

          th.show-col,
          th.hidden-col {
            width: 20px;

            i {
                font-size: 20px;
                cursor: pointer;
            }
          }

          th.hidden-col {
            i {
                float: left;
            }
          }

          th.handle {
            width: 50px;
          }

          th.pading-header {
            height: 61.5px;
          }

          th.department {
            position: sticky;
            top: 0;
            left: 0;

            z-index: 2;
          }

          th.course-id {
            position: sticky;
            top: 0;
            left: 150px;

            z-index: 2;
          }

          th.gorvernment-holiday {
            background-color: red;
          }
        }
      }

      tbody {
        td {
          background-color: $white;
        }

        td:not(.show-fare) {
          padding: 0.25rem;

          text-align: center;
          vertical-align: middle;

          font-size: 14px;
        }

        td.department {
          position: sticky;
          left: 0;

          z-index: 1;
        }

        td.course-id {
          position: sticky;
          left: 150px;

          z-index: 1;
        }

        td.list-route {
          padding: 0;
          height: 100%;

          .zone-fare {
            height: 80px;
            padding: 10px;
            // width: 150px;

            div.show-route {
              height: 40%;
              font-size: 12px;

              display: flex;
              justify-content: center;
              align-items: center;
            }

            div.show-all-route {
              height: 20%;
              font-size: 15px;

              display: flex;
              justify-content: center;
              align-items: center;

              i {
                margin-right: 10px;
              }
            }
          }
        }

        td.show-fare {
          padding: 0;
          height: 100%;
          width: 120px !important;
          min-width: 120px !important;

          .zone-fare {
            width: 100%;
            height: 80px;
            padding: 10px;

            div.show-route {
              height: 40%;
              font-size: 12px;

              display: flex;
              justify-content: center;
              align-items: center;
            }

            div.show-all-route {
              height: 20%;
              font-size: 15px;

              display: flex;
              justify-content: center;
              align-items: center;

              i {
                margin-right: 10px;
              }
            }
          }
        }

        td.handle {
          i {
              cursor: pointer;
          }
        }

        td.no-route {
          background-color: $rolling-stone;
        }
      }
    }
  }

  .type-1 {
    top: -64px;
  }

  .type-2 {
    top: -86px;
  }

  margin-bottom: 20px;
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
