<template>
	<b-overlay
		:show="overlay.show"
		:variant="overlay.variant"
		:opacity="overlay.opacity"
		:blur="overlay.blur"
		:z-index="1000"
	>
		<template #overlay>
			<div class="text-center">
				<b-icon icon="arrow-clockwise" font-scale="3" animation="spin" />
				<p class="text-overlay">{{ $t("PLEASE_WAIT") }}</p>
			</div>
		</template>

		<b-col>
			<div class="route-master">
				<div class="route-master__header">
					<vHeaderPage>
						{{ $t("ROUTER_ROUTE_MASTER") }}
					</vHeaderPage>
				</div>

				<div class="route-master__filter">
					<vHeaderFilter>
						<template #zone-filter>
							<b-row class="pb-3">
								<b-col cols="12">
									<span class="text-clear-all" @click="doClearFilter()">{{ $t('CLEAR_ALL') }}</span>
								</b-col>
							</b-row>
							<b-row class="pb-3">
								<b-col cols="12" class="pb-3">
									<vSelectGroup
										:id="'filter-by-department'"
										v-model="filter.department.department_option"
										:data-options="department_options"
										:text-prepend="$t('ROUTE_MASTER_DEPARTMENT')"
										:is-check="filter.department.is_check"
										:checkbox-size="'lg'"
										@isChecked="getIsCheckDepartmentFilter"
									/>
								</b-col>
								<b-col cols="12" class="pb-3">
									<vInputGroup
										:id="'filter-by-route-name'"
										v-model="filter.routeName.route_name_option"
										:text-prepend="$t('ROUTE_MASTER_ROUTE_NAME')"
										:is-check="filter.routeName.is_check"
										:checkbox-size="'lg'"
										@isChecked="getIsCheckRouteNameFilter"
									/>
								</b-col>
								<b-col cols="12">
									<vSelectGroup
										:id="'filter-by-customer'"
										v-model="filter.customer.customer_option"
										:data-options="customer_options"
										:text-prepend="$t('ROUTE_MASTER_CUSTOMER')"
										:is-check="filter.customer.is_check"
										:checkbox-size="'lg'"
										@isChecked="getIsCheckCustomerFilter"
									/>
								</b-col>
							</b-row>
							<b-row>
								<b-col cols="12">
									<vButton class="apply-filter-button" :text-button="$t('APPLY')" @click.native="doApply()" />
								</b-col>
							</b-row>
						</template>
					</vHeaderFilter>
				</div>

				<div class="route-master__functional-button-header">
					<b-row>
						<b-col cols="12" class="text-right">
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
								<vButton class="button-csv-register" :text-button="$t('ROUTE_MASTER_CSV_REGISTER')" @click.native="handleShowModalImportCSV()" />
							</template>
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
								<vButton class="button-register" :text-button="$t('ROUTE_MASTER_REGISTER')" @click.native="doRegister()" />
							</template>
							<template
								v-if="false"
							>
								<vButton class="button-multi-editing" :text-button="$t('ROUTE_MASTER_MULTI_EDITING')" @click.native="doMultiEditing()" />
							</template>
						</b-col>
					</b-row>
				</div>

				<div class="route-master__table">
					<div ref="tableRouteMasterHeader" class="table-route-master-header" @scroll="handleScrollTableRouteMasterHeader">
						<b-table-simple id="table-route" bordered class="table-route-master">
							<b-thead>
								<b-tr>
									<b-th class="route-master-table-th department-th colored-th sort" rowspan="2" @click="onSortTable('department_name')">
										<div class="d-flex justify-content-center">
											<span>{{ $t('ROUTE_MASTER_DEPARTMENT') }}</span>
											<div>
												<i v-if="sortTable.sortBy === 'department_name' && sortTable.sortType === true" class="fad fa-sort-up icon-sort" />
												<i v-else-if="sortTable.sortBy === 'department_name' && sortTable.sortType === false" class="fad fa-sort-down icon-sort" />
												<i v-else class="fad fa-sort icon-sort" />
											</div>
										</div>
									</b-th>
									<b-th class="route-master-table-th route-id-th colored-th sort" rowspan="2" @click="onSortTable('route-id')">
										<div class="d-flex justify-content-center">
											<span>{{ $t('ROUTE_MASTER_ROUTE_ID') }}</span>
											<div>
												<i v-if="sortTable.sortBy === 'route_id' && sortTable.sortType === true" class="fad fa-sort-up icon-sort" />
												<i v-else-if="sortTable.sortBy === 'route_id' && sortTable.sortType === false" class="fad fa-sort-down icon-sort" />
												<i v-else class="fad fa-sort icon-sort" />
											</div>
										</div>
									</b-th>
									<b-th class="route-master-table-th route-name-th colored-th sort" rowspan="2" @click="onSortTable('route-name')">
										<div class="d-flex justify-content-center">
											<span>{{ $t('ROUTE_MASTER_ROUTE_NAME') }}</span>
											<div>
												<i v-if="sortTable.sortBy === 'name' && sortTable.sortType === true" class="fad fa-sort-up icon-sort" />
												<i v-else-if="sortTable.sortBy === 'name' && sortTable.sortType === false" class="fad fa-sort-down icon-sort" />
												<i v-else class="fad fa-sort icon-sort" />
											</div>
										</div>
									</b-th>
									<b-th class="route-master-table-th customer-th colored-th sort" rowspan="2" @click="onSortTable('customer_name')">
										<div class="d-flex justify-content-center">
											<span>{{ $t('ROUTE_MASTER_CUSTOMER') }}</span>
											<div>
												<i v-if="sortTable.sortBy === 'customer_name' && sortTable.sortType === true" class="fad fa-sort-up icon-sort" />
												<i v-else-if="sortTable.sortBy === 'customer_name' && sortTable.sortType === false" class="fad fa-sort-down icon-sort" />
												<i v-else class="fad fa-sort icon-sort" />
											</div>
										</div>
									</b-th>
									<b-th class="route-master-table-th fare-type-th colored-th sort" rowspan="2" @click="onSortTable('fare-type')">
										<div class="d-flex justify-content-center">
											<span>{{ $t('ROUTE_MASTER_FARE_TYPE') }}</span>
											<div>
												<i v-if="sortTable.sortBy === 'route_fare_type' && sortTable.sortType === true" class="fad fa-sort-up icon-sort" />
												<i v-else-if="sortTable.sortBy === 'route_fare_type' && sortTable.sortType === false" class="fad fa-sort-down icon-sort" />
												<i v-else class="fad fa-sort icon-sort" />
											</div>
										</div>
									</b-th>
									<b-th class="route-master-table-th fare-th colored-th sort" rowspan="2" @click="onSortTable('fare')">
										<div class="d-flex justify-content-center">
											<span>{{ $t('ROUTE_MASTER_FARE') }}</span>
											<div>
												<i v-if="sortTable.sortBy === 'fare' && sortTable.sortType === true" class="fad fa-sort-up icon-sort" />
												<i v-else-if="sortTable.sortBy === 'fare' && sortTable.sortType === false" class="fad fa-sort-down icon-sort" />
												<i v-else class="fad fa-sort icon-sort" />
											</div>
										</div>
									</b-th>
									<b-th class="route-master-table-th highway-fee-th colored-th sort" rowspan="2" @click="onSortTable('highway-fee')">
										<div class="d-flex justify-content-center">
											<span>{{ $t('ROUTE_MASTER_HIGHWAY_FEE') }}</span>
											<div>
												<i v-if="sortTable.sortBy === 'highway_fee' && sortTable.sortType === true" class="fad fa-sort-up icon-sort" />
												<i v-else-if="sortTable.sortBy === 'highway_fee' && sortTable.sortType === false" class="fad fa-sort-down icon-sort" />
												<i v-else class="fad fa-sort icon-sort" />
											</div>
										</div>
									</b-th>
									<b-th class="route-master-table-th highway-fee-holiday-th colored-th sort" rowspan="2" @click="onSortTable('highway-fee-holiday')">
										<div class="d-flex justify-content-center">
											<span>{{ $t('ROUTE_MASTER_HIGHWAY_FEE_HOLIDAY') }}</span>
											<div>
												<i v-if="sortTable.sortBy === 'highway_fee_holiday' && sortTable.sortType === true" class="fad fa-sort-up icon-sort" />
												<i v-else-if="sortTable.sortBy === 'highway_fee_holiday' && sortTable.sortType === false" class="fad fa-sort-down icon-sort" />
												<i v-else class="fad fa-sort icon-sort" />
											</div>
										</div>
									</b-th>
									<b-th class="route-master-table-th the-number-of-store-th colored-th sort" rowspan="2" @click="onSortTable('the-number-of-store')">
										<div class="d-flex justify-content-center">
											<span>{{ $t('ROUTE_MASTER_THE_NUMBER_OF_STORE') }}</span>
											<div>
												<i v-if="sortTable.sortBy === 'store_name' && sortTable.sortType === true" class="fad fa-sort-up icon-sort" />
												<i v-else-if="sortTable.sortBy === 'store_name' && sortTable.sortType === false" class="fad fa-sort-down icon-sort" />
												<i v-else class="fad fa-sort icon-sort" />
											</div>
										</div>
									</b-th>
									<b-th class="route-master-table-th suspension-of-service-th colored-th" rowspan="1" colspan="8">
										<span>{{ $t('ROUTE_MASTER_SUSPENSION_OF_SERVICE') }}</span>
									</b-th>
									<b-th v-if="isShowFullDate === true" class="route-master-table-th suspension-of-service-date-th colored-th" rowspan="1" :colspan="numberDate">
										<i class="fas fa-minus-square icon-minus-schedule" @click="handleChangeShowState()" />
										<span>{{ $t('ROUTE_MASTER_SCHEDULE_TABLE') }}</span>
									</b-th>
									<b-th v-else class="route-master-table-th suspension-of-service-date-th-collapsed colored-th" rowspan="2" colspan="1">
										<i class="fas fa-plus-square icon-plus-schedule" @click="handleChangeShowState()" />
									</b-th>
									<b-th class="route-master-table-th remark-th colored-th" rowspan="2" colspan="1">
										<span>{{ $t('ROUTE_MASTER_REMARK') }}</span>
									</b-th>
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
										<b-th class="route-master-table-th delete-th colored-th" rowspan="2">
											<span>{{ $t('ROUTE_MASTER_BUTTON_EDIT') }}</span>
										</b-th>
									</template>
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
										<b-th class="route-master-table-th delete-th colored-th" rowspan="2">
											<span>{{ $t('ROUTE_MASTER_BUTTON_DELETE') }}</span>
										</b-th>
									</template>
								</b-tr>
								<b-tr>
									<b-th :class="['route-master-table-th tsuki-th colored-th', isShowFullDate === true ? 'suspension-of-service-full' : 'suspension-of-service-collapse']" rowspan="1">
										<span>{{ $t('ROUTE_MASTER_TSUKI') }}</span>
									</b-th>
									<b-th :class="['route-master-table-th fire-hi-th colored-th', isShowFullDate === true ? 'suspension-of-service-full' : 'suspension-of-service-collapse']" rowspan="1">
										<span>{{ $t('ROUTE_MASTER_FIRE_HI') }}</span>
									</b-th>
									<b-th :class="['route-master-table-th mizu-th colored-th', isShowFullDate === true ? 'suspension-of-service-full' : 'suspension-of-service-collapse']" rowspan="1">
										<span>{{ $t('ROUTE_MASTER_MIZU') }}</span>
									</b-th>
									<b-th :class="['route-master-table-th ki-th colored-th', isShowFullDate === true ? 'suspension-of-service-full' : 'suspension-of-service-collapse']" rowspan="1">
										<span>{{ $t('ROUTE_MASTER_KI') }}</span>
									</b-th>
									<b-th :class="['route-master-table-th kin-th colored-th', isShowFullDate === true ? 'suspension-of-service-full' : 'suspension-of-service-collapse']" rowspan="1">
										<span>{{ $t('ROUTE_MASTER_KIN') }}</span>
									</b-th>
									<b-th :class="['route-master-table-th tsuchi-th colored-th', isShowFullDate === true ? 'suspension-of-service-full' : 'suspension-of-service-collapse']" rowspan="1">
										<span>{{ $t('ROUTE_MASTER_TSUCHI') }}</span>
									</b-th>
									<b-th :class="['route-master-table-th day-hi-th colored-th', isShowFullDate === true ? 'suspension-of-service-full' : 'suspension-of-service-collapse']" rowspan="1">
										<span>{{ $t('ROUTE_MASTER_DAY_HI') }}</span>
									</b-th>
									<b-th :class="['route-master-table-th holiday-th colored-th', isShowFullDate === true ? 'suspension-of-service-full' : 'suspension-of-service-collapse']" rowspan="1">
										<span>{{ $t('ROUTE_MASTER_HOLIDAY') }}</span>
									</b-th>
									<template v-if="isShowFullDate === true">
										<b-th v-for="date in numberDate" :key="`route-master-table-th-${date}`" class="route-master-table-th date-th colored-th schedule" rowspan="1">
											<span>{{ date }}</span>
										</b-th>
									</template>
									<template v-else />
								</b-tr>
							</b-thead>
						</b-table-simple>
					</div>

					<div ref="tableRouteMasterContent" class="table-route-master-content" @scroll="handleScrollTableRouteMasterContent">
						<b-table-simple id="table-route" bordered class="table-route-master">
							<b-thead>
								<b-tr>
									<b-th class="route-master-table-th department-th colored-th sort" rowspan="2" @click="onSortTable('department_name')">
										<div class="d-flex justify-content-center">
											<span>{{ $t('ROUTE_MASTER_DEPARTMENT') }}</span>
											<div>
												<i v-if="sortTable.sortBy === 'department_name' && sortTable.sortType === true" class="fad fa-sort-up icon-sort" />
												<i v-else-if="sortTable.sortBy === 'department_name' && sortTable.sortType === false" class="fad fa-sort-down icon-sort" />
												<i v-else class="fad fa-sort icon-sort" />
											</div>
										</div>
									</b-th>
									<b-th class="route-master-table-th route-id-th colored-th sort" rowspan="2" @click="onSortTable('route-id')">
										<div class="d-flex justify-content-center">
											<span>{{ $t('ROUTE_MASTER_ROUTE_ID') }}</span>
											<div>
												<i v-if="sortTable.sortBy === 'route_id' && sortTable.sortType === true" class="fad fa-sort-up icon-sort" />
												<i v-else-if="sortTable.sortBy === 'route_id' && sortTable.sortType === false" class="fad fa-sort-down icon-sort" />
												<i v-else class="fad fa-sort icon-sort" />
											</div>
										</div>
									</b-th>
									<b-th class="route-master-table-th route-name-th colored-th sort" rowspan="2" @click="onSortTable('route-name')">
										<div class="d-flex justify-content-center">
											<span>{{ $t('ROUTE_MASTER_ROUTE_NAME') }}</span>
											<div>
												<i v-if="sortTable.sortBy === 'name' && sortTable.sortType === true" class="fad fa-sort-up icon-sort" />
												<i v-else-if="sortTable.sortBy === 'name' && sortTable.sortType === false" class="fad fa-sort-down icon-sort" />
												<i v-else class="fad fa-sort icon-sort" />
											</div>
										</div>
									</b-th>
									<b-th class="route-master-table-th customer-th colored-th sort" rowspan="2" @click="onSortTable('customer_name')">
										<div class="d-flex justify-content-center">
											<span>{{ $t('ROUTE_MASTER_CUSTOMER') }}</span>
											<div>
												<i v-if="sortTable.sortBy === 'customer_name' && sortTable.sortType === true" class="fad fa-sort-up icon-sort" />
												<i v-else-if="sortTable.sortBy === 'customer_name' && sortTable.sortType === false" class="fad fa-sort-down icon-sort" />
												<i v-else class="fad fa-sort icon-sort" />
											</div>
										</div>
									</b-th>
									<b-th class="route-master-table-th fare-type-th colored-th sort" rowspan="2" @click="onSortTable('fare-type')">
										<div class="d-flex justify-content-center">
											<span>{{ $t('ROUTE_MASTER_FARE_TYPE') }}</span>
											<div>
												<i v-if="sortTable.sortBy === 'route_fare_type' && sortTable.sortType === true" class="fad fa-sort-up icon-sort" />
												<i v-else-if="sortTable.sortBy === 'route_fare_type' && sortTable.sortType === false" class="fad fa-sort-down icon-sort" />
												<i v-else class="fad fa-sort icon-sort" />
											</div>
										</div>
									</b-th>
									<b-th class="route-master-table-th fare-th colored-th sort" rowspan="2" @click="onSortTable('fare')">
										<div class="d-flex justify-content-center">
											<span>{{ $t('ROUTE_MASTER_FARE') }}</span>
											<div>
												<i v-if="sortTable.sortBy === 'fare' && sortTable.sortType === true" class="fad fa-sort-up icon-sort" />
												<i v-else-if="sortTable.sortBy === 'fare' && sortTable.sortType === false" class="fad fa-sort-down icon-sort" />
												<i v-else class="fad fa-sort icon-sort" />
											</div>
										</div>
									</b-th>
									<b-th class="route-master-table-th highway-fee-th colored-th sort" rowspan="2" @click="onSortTable('highway-fee')">
										<div class="d-flex justify-content-center">
											<span>{{ $t('ROUTE_MASTER_HIGHWAY_FEE') }}</span>
											<div>
												<i v-if="sortTable.sortBy === 'highway_fee' && sortTable.sortType === true" class="fad fa-sort-up icon-sort" />
												<i v-else-if="sortTable.sortBy === 'highway_fee' && sortTable.sortType === false" class="fad fa-sort-down icon-sort" />
												<i v-else class="fad fa-sort icon-sort" />
											</div>
										</div>
									</b-th>
									<b-th class="route-master-table-th highway-fee-holiday-th colored-th sort" rowspan="2" @click="onSortTable('highway-fee-holiday')">
										<div class="d-flex justify-content-center">
											<span>{{ $t('ROUTE_MASTER_HIGHWAY_FEE_HOLIDAY') }}</span>
											<div>
												<i v-if="sortTable.sortBy === 'highway_fee_holiday' && sortTable.sortType === true" class="fad fa-sort-up icon-sort" />
												<i v-else-if="sortTable.sortBy === 'highway_fee_holiday' && sortTable.sortType === false" class="fad fa-sort-down icon-sort" />
												<i v-else class="fad fa-sort icon-sort" />
											</div>
										</div>
									</b-th>
									<b-th class="route-master-table-th the-number-of-store-th colored-th sort" rowspan="2" @click="onSortTable('the-number-of-store')">
										<div class="d-flex justify-content-center">
											<span>{{ $t('ROUTE_MASTER_THE_NUMBER_OF_STORE') }}</span>
											<div>
												<i v-if="sortTable.sortBy === 'store_name' && sortTable.sortType === true" class="fad fa-sort-up icon-sort" />
												<i v-else-if="sortTable.sortBy === 'store_name' && sortTable.sortType === false" class="fad fa-sort-down icon-sort" />
												<i v-else class="fad fa-sort icon-sort" />
											</div>
										</div>
									</b-th>
									<b-th class="route-master-table-th suspension-of-service-th colored-th" rowspan="1" colspan="8">
										<span>{{ $t('ROUTE_MASTER_SUSPENSION_OF_SERVICE') }}</span>
									</b-th>
									<b-th v-if="isShowFullDate === true" class="route-master-table-th suspension-of-service-date-th colored-th" rowspan="1" :colspan="numberDate">
										<i class="fas fa-minus-square icon-minus-schedule" @click="handleChangeShowState()" />
										<span>{{ $t('ROUTE_MASTER_SCHEDULE_TABLE') }}</span>
									</b-th>
									<b-th v-else class="route-master-table-th suspension-of-service-date-th colored-th" rowspan="2" colspan="1">
										<i class="fas fa-plus-square icon-plus-schedule" @click="handleChangeShowState()" />
									</b-th>
									<b-th class="route-master-table-th remark-th colored-th" rowspan="2" colspan="1">
										<span>{{ $t('ROUTE_MASTER_REMARK') }}</span>
									</b-th>
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
										<b-th class="route-master-table-th delete-th colored-th" rowspan="2">
											<span>{{ $t('ROUTE_MASTER_BUTTON_EDIT') }}</span>
										</b-th>
									</template>
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
										<b-th class="route-master-table-th delete-th colored-th" rowspan="2">
											<span>{{ $t('ROUTE_MASTER_BUTTON_DELETE') }}</span>
										</b-th>
									</template>
								</b-tr>
								<b-tr>
									<b-th :class="['route-master-table-th tsuki-th colored-th', isShowFullDate === true ? 'suspension-of-service-full' : 'suspension-of-service-collapse']" rowspan="1">
										<span>{{ $t('ROUTE_MASTER_TSUKI') }}</span>
									</b-th>
									<b-th :class="['route-master-table-th fire-hi-th colored-th', isShowFullDate === true ? 'suspension-of-service-full' : 'suspension-of-service-collapse']" rowspan="1">
										<span>{{ $t('ROUTE_MASTER_FIRE_HI') }}</span>
									</b-th>
									<b-th :class="['route-master-table-th mizu-th colored-th', isShowFullDate === true ? 'suspension-of-service-full' : 'suspension-of-service-collapse']" rowspan="1">
										<span>{{ $t('ROUTE_MASTER_MIZU') }}</span>
									</b-th>
									<b-th :class="['route-master-table-th ki-th colored-th', isShowFullDate === true ? 'suspension-of-service-full' : 'suspension-of-service-collapse']" rowspan="1">
										<span>{{ $t('ROUTE_MASTER_KI') }}</span>
									</b-th>
									<b-th :class="['route-master-table-th kin-th colored-th', isShowFullDate === true ? 'suspension-of-service-full' : 'suspension-of-service-collapse']" rowspan="1">
										<span>{{ $t('ROUTE_MASTER_KIN') }}</span>
									</b-th>
									<b-th :class="['route-master-table-th tsuchi-th colored-th', isShowFullDate === true ? 'suspension-of-service-full' : 'suspension-of-service-collapse']" rowspan="1">
										<span>{{ $t('ROUTE_MASTER_TSUCHI') }}</span>
									</b-th>
									<b-th :class="['route-master-table-th day-hi-th colored-th', isShowFullDate === true ? 'suspension-of-service-full' : 'suspension-of-service-collapse']" rowspan="1">
										<span>{{ $t('ROUTE_MASTER_DAY_HI') }}</span>
									</b-th>
									<b-th :class="['route-master-table-th holiday-th colored-th', isShowFullDate === true ? 'suspension-of-service-full' : 'suspension-of-service-collapse']" rowspan="1">
										<span>{{ $t('ROUTE_MASTER_HOLIDAY') }}</span>
									</b-th>
									<template v-if="isShowFullDate === true">
										<b-th v-for="date in numberDate" :key="`route-master-table-th-${date}`" class="route-master-table-th date-th colored-th schedule" rowspan="1">
											<span>{{ date }}</span>
										</b-th>
									</template>
									<template v-else />
								</b-tr>
							</b-thead>

							<b-tbody v-if="vItems.length > 0">
								<b-tr v-for="(item, index) in vItems" :key="`row-${index + 1}`">
									<b-td class="route-master-table-td department-td">
										<span>{{ item.department }}</span>
									</b-td>
									<b-td class="route-master-table-td route-id-td">
										<span>{{ item.route_id }}</span>
									</b-td>
									<b-td class="route-master-table-td route-name-td">
										<span>{{ item.route_name }}</span>
									</b-td>
									<b-td class="route-master-table-td">
										<span>{{ item.customer }}</span>
									</b-td>
									<b-td class="route-master-table-td">
										<span v-if="item.fare_type === 1">{{ $t('ROUTE_MASTER_REGISTER_FARE_TYPE_DAILY') }}</span>
										<span v-else-if="item.fare_type === 2">{{ $t('ROUTE_MASTER_REGISTER_FARE_TYPE_MONTHLY') }}</span>
									</b-td>
									<b-td class="route-master-table-td">
										<span>{{ formatNumberWithCommas(item.fare) }}</span>
									</b-td>
									<b-td class="route-master-table-td">
										<span>{{ formatNumberWithCommas(item.highway_fee) }}</span>
									</b-td>
									<b-td class="route-master-table-td">
										<span>{{ formatNumberWithCommas(item.highway_fee_holiday) }}</span>
									</b-td>
									<b-td class="route-master-table-td store-td">
										<b-dropdown :text="`${item.store_count.toString()} 選択`" class="w-100 pt-1" style="position: relative;" no-caret>
											<b-dropdown-item v-for="(store, storeIndex) in item.stores" :key="`store-${storeIndex}`" class="route-master-table-dropdown-item">
												<span>{{ store.store_name }}</span>
											</b-dropdown-item>
										</b-dropdown>
									</b-td>
									<b-td v-for="(service, serviceIndex) in item.suspension_of_service" :key="`suspension-of-service-${serviceIndex}`" class="route-master-table-td">
										<b-form-checkbox
											:id="`checkbox-${index}-${serviceIndex}`"
											v-model="service.value"
											class="route-master-table-checkbox"
											size="lg"
											:name="`checkbox-${index}-${serviceIndex}`"
											value="true"
											unchecked-value="false"
										/>
									</b-td>
									<template v-if="isShowFullDate === true">
										<template v-for="(date, dateIndex) in item.schedule">
											<template v-if="date.value === true">
												<b-td :key="`schedule-date-${index}-${dateIndex}`" class="route-master-table-td day-off-date" />
											</template>
											<template v-else>
												<b-td :key="`schedule-date-${index}-${dateIndex}`" class="route-master-table-td" />
											</template>
										</template>
									</template>
									<template v-else>
										<b-td class="route-master-table-td" />
									</template>
									<b-td class="route-master-table-td">
										<span>{{ item.remark }}</span>
									</b-td>
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
										<b-td class="route-master-table-td">
											<i
												class="fas fa-pen icon-delete"
												@click="onClickEdit(item.id)"
											/>
										</b-td>
									</template>
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
										<b-td class="route-master-table-td">
											<i class="fas fa-trash icon-delete" @click="onClickDelete(item.id)" />
										</b-td>
									</template>
								</b-tr>
							</b-tbody>

							<b-tbody v-else>
								<b-tr>
									<b-td :colspan="19 + numberDate" class="route-master-table-td">{{ $t('ROUTER_MASTER_TABLE_NO_DATA') }}</b-td>
								</b-tr>
							</b-tbody>
						</b-table-simple>
					</div>

				</div>

				<div v-if="pagination.total_rows > 10" class="route-master__custom-per-page mt-3">
					<b-row>
						<b-col cols="2">
							<b-form-group :label="$t('ROUTE_MASTER_DISPLAY_PER_PAGE')" label-for="custom-per-page">
								<b-form-select
									id="custom-per-page"
									v-model="pagination.per_page"
									:options="pagination_options"
									@change="getPerPage()"
								/>
							</b-form-group>
						</b-col>
					</b-row>
				</div>

				<div v-if="pagination.total_rows > 10" class="route-master__pagination">
					<vPagination
						:aria-controls="'route-master-list'"
						:current-page="pagination.current_page"
						:per-page="pagination.per_page"
						:total-rows="pagination.total_rows"
						:next-class="'next'"
						:prev-class="'prev'"
						@currentPageChange="getCurrentPage"
					/>
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
						<span>{{ $t('ROUTE_MASTER_DELETE_CONFIRMATION') }}</span>
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

				<b-modal
					id="modal-csv-import-register"
					v-model="showModalImportRegister"
					no-close-on-backdrop
					no-close-on-esc
					size="lg"
					:static="true"
					header-class="modal-custom-header"
					content-class="modal-custom-body"
					footer-class="modal-custom-footer"
				>
					<template #modal-header>
						<b-row class="w-100">
							<b-col cols="12" class="text-right">
								<i
									class="fas fa-times"
									@click="handleTurnOffImportModal()"
								/>
							</b-col>
						</b-row>
					</template>

					<template #default>
						<b-row lg="2" md="1" sm="1">
							<b-col cols="6">
								<span class="d-flex pt-3 float-right">{{ $t('ROUTE_MASTER_IMPORT_DATA_CSV') }}</span>
							</b-col>
							<b-col cols="6">
								<input
									id="fileUpload"
									ref="selectFileInput"
									type="file"
									name="File Upload"
									accept=".csv"
									style="display: none;"
									@click="handleClickFileInput()"
									@change="handleCSVFile"
								>

								<b-button class="btn-select-file" @click="triggerSelectFileInput()">
									<span class="d-flex pt-2">{{ convertStringToDot(fileNameCSVInput) }}</span>
								</b-button>
							</b-col>
						</b-row>

						<b-row class="mt-3">
							<b-col cols="12">
								<hr>
							</b-col>
						</b-row>

						<b-row v-if="errorImportModalData.length > 0" class="mt-3">
							<b-col cols="12">
								<span class="font-weight-bold">{{ 'インポートしたファイルにエラーが存在します。' }}</span>
							</b-col>
							<b-col cols="12">
								<span class="font-weight-bold">{{ '以下の行のエラーを修正して再度インポートください。' }}</span>
							</b-col>
							<b-col cols="12" class="mb-3">
								<hr>
							</b-col>
							<b-col v-for="(item, indexMessage) in errorImportModalData" :key="indexMessage" cols="12">
								<span>{{ item.message }}</span>
							</b-col>
						</b-row>
					</template>

					<template #modal-footer>
						<b-button class="modal-btn btn-cancel" @click="doCSVRegister()">
							{{ $t('ROUTE_MASTER_IMPORT_CSV_BUTTON') }}
						</b-button>
					</template>
				</b-modal>
			</div>
		</b-col>
	</b-overlay>
</template>

<script>
import vHeaderPage from '@/components/atoms/vHeaderPage.vue';
import vHeaderFilter from '@/components/atoms/vHeaderFilter';
import vButton from '@/components/atoms/vButton';
import vSelectGroup from '@/components/atoms/vSelectGroup';
import vInputGroup from '@/components/atoms/vInputGroup';
import vPagination from '@/components/atoms/vPagination';

import CONST_ROLE from '@/const/role';
import { hasRole } from '@/utils/hasRole';

import { getListRoute, getFilterCustomer, getFilterDepartment, deleteRoute, importCSV } from '@/api/modules/routeMaster';

import { handleTransformDataFormat } from '@/utils/handleTransformDataFormat';

import { cleanObj } from '@/utils/handleObj';
import { obj2Path } from '@/utils/obj2Path';
import { convertStringToDot } from '@/utils/convertStringToDot';

const urlAPI = {
    apiGetListRoute: '/route',
    apiGetFilterCustomer: '/customer/list-all',
    apiGetFilterDepartment: '/department/list-all',
    apiRemoveRoute: '/route',
    apiImportCSV: '/route/import',
};

export default {
    name: 'RouteMaster',
    components: {
        vHeaderPage,
        vHeaderFilter,
        vButton,
        vSelectGroup,
        vInputGroup,
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

            department_options: [
                { value: null, text: this.$t('PLEASE_SELECT') },
            ],

            customer_options: [
                { value: null, text: this.$t('PLEASE_SELECT') },
            ],

            filter: this.$store.getters.filterRouteMaster || {
                department: {
                    is_check: false,
                    department_option: null,
                },
                routeName: {
                    is_check: false,
                    route_name_option: null,
                },
                customer: {
                    is_check: false,
                    customer_option: null,
                },
            },

            pagination_options: [
                { value: 10, text: '10' },
                { value: 20, text: '20' },
                { value: 50, text: '50' },
                { value: 100, text: '100' },
                { value: 250, text: '250' },
                { value: 500, text: '500' },
            ],

            pagination: {
                current_page: 1,
                per_page: 10,
                total_rows: 0,
            },

            vItems: [],

            numberDate: 31,

            isShowFullDate: false,

            showModal: false,

            showModalImportRegister: false,

            sortTable: {
                sortBy: '',
                sortType: null,
            },

            delete_id: null,

            convertStringToDot,

            fileNameCSVInput: this.$t('DATA_IMPORT.SELECT_FILES'),

            formData: null,

            errorImportModalData: [],
        };
    },
    computed: {
        role() {
            return this.$store.getters.profile.roles;
        },

        route_master_index_is_show_full() {
            return JSON.parse(window.localStorage.getItem('route_master_index_is_show_full'));
        },
    },
    created() {
        this.initData();
    },
    methods: {
        formatNumberWithCommas(number) {
            if (number) {
                return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            } else {
                return 0;
            }
        },
        async initData() {
            await this.handleOverrideIsShowFullStatus();
            await this.getDataFilter();
            await this.getDataTableRoute(1);
        },
        handleOverrideIsShowFullStatus() {
            if (this.route_master_index_is_show_full === null || this.route_master_index_is_show_full === false) {
                this.isShowFullDate = false;
            } else {
                this.isShowFullDate = true;
            }
        },
        async getDataFilter() {
            try {
                const DATA_CUSTOMER_FILTER = await getFilterCustomer(urlAPI.apiGetFilterCustomer);

                DATA_CUSTOMER_FILTER.data = Object.values(DATA_CUSTOMER_FILTER.data);

                if (DATA_CUSTOMER_FILTER.code === 200) {
                    for (let i = 0; i < DATA_CUSTOMER_FILTER.data.length; i++) {
                        this.customer_options.push({
                            value: DATA_CUSTOMER_FILTER.data[i].id,
                            text: DATA_CUSTOMER_FILTER.data[i].customer_name,
                        });
                    }
                }

                const DATA_DEPARTMENT_FILTER = await getFilterDepartment(urlAPI.apiGetFilterDepartment);

                DATA_DEPARTMENT_FILTER.data = Object.values(DATA_DEPARTMENT_FILTER.data);

                if (DATA_DEPARTMENT_FILTER.code === 200) {
                    for (let i = 0; i < DATA_DEPARTMENT_FILTER.data.length; i++) {
                        this.department_options.push({
                            value: DATA_DEPARTMENT_FILTER.data[i].id,
                            text: DATA_DEPARTMENT_FILTER.data[i].department_name,
                        });
                    }
                }
            } catch (error) {
                console.log(error);
            }
        },
        async getDataTableRoute(page, is_force_reset_current_page) {
            this.overlay.show = true;

            const ROUTE_MASTER_PAGINATION = this.$store.getters.routeMasterCP;

            let current_page = 1;

            if (is_force_reset_current_page) {
                current_page = 1;
            } else {
                if (ROUTE_MASTER_PAGINATION) {
                    current_page = ROUTE_MASTER_PAGINATION;
                } else {
                    current_page = page;
                }
            }

            const ROUTE_MASTER_PER_PAGE = this.$store.getters.route_master_per_page;

            let per_page = 20;

            if (ROUTE_MASTER_PER_PAGE) {
                per_page = ROUTE_MASTER_PER_PAGE;
            } else {
                per_page = this.pagination.per_page;
            }

            let QUERY = {
                page: current_page,
                per_page: per_page,
                department_id: this.filter.department.is_check ? this.filter.department.department_option : '',
                name: this.filter.routeName.is_check ? this.filter.routeName.route_name_option : '',
                customer_id: this.filter.customer.is_check ? this.filter.customer.customer_option : '',
                sort_by: this.sortTable.sortBy,
                sort_type: this.sortTable.sortType,
            };

            QUERY = cleanObj(QUERY);

            const URL = `${urlAPI.apiGetListRoute}?${obj2Path(QUERY)}`;

            let DATA = [];
            let PAGINATION = [];

            try {
                const response = await getListRoute(URL);

                if (response.code === 200) {
                    DATA = response.data.result;

                    this.vItems = handleTransformDataFormat(DATA);

                    PAGINATION = response.data.pagination;

                    this.pagination.total_rows = PAGINATION.total_records;
                    this.pagination.current_page = PAGINATION.current_page;
                    this.pagination.per_page = PAGINATION.per_page;
                }
            } catch (error) {
                console.log(error);
            }

            this.overlay.show = false;
        },
        doApply() {
            this.handleSaveFilter(this.filter);
            this.getDataTableRoute(1, true);
        },
        doClearFilter() {
            const FILTER = {
                department: {
                    is_check: false,
                    department_option: null,
                },
                routeName: {
                    is_check: false,
                    route_name_option: null,
                },
                customer: {
                    is_check: false,
                    customer_option: null,
                },
            };

            this.filter = FILTER;

            this.getDataTableRoute(1, true);
        },
        onSortTable(col) {
            switch (col) {
            case 'department_name':
                if (this.sortTable.sortBy === 'department_name') {
                    if (this.sortTable.sortType) {
                        this.sortTable.sortType = !this.sortTable.sortType;
                    } else {
                        this.sortTable.sortType = true;
                    }
                } else {
                    this.sortTable.sortBy = 'department_name';
                    this.sortTable.sortType = true;
                }

                break;

            case 'route-id':
                if (this.sortTable.sortBy === 'route_id') {
                    if (this.sortTable.sortType) {
                        this.sortTable.sortType = !this.sortTable.sortType;
                    } else {
                        this.sortTable.sortType = true;
                    }
                } else {
                    this.sortTable.sortBy = 'route_id';
                    this.sortTable.sortType = true;
                }

                break;

            case 'route-name':
                if (this.sortTable.sortBy === 'name') {
                    if (this.sortTable.sortType) {
                        this.sortTable.sortType = !this.sortTable.sortType;
                    } else {
                        this.sortTable.sortType = true;
                    }
                } else {
                    this.sortTable.sortBy = 'name';
                    this.sortTable.sortType = true;
                }

                break;

            case 'customer_name':
                if (this.sortTable.sortBy === 'customer_name') {
                    if (this.sortTable.sortType) {
                        this.sortTable.sortType = !this.sortTable.sortType;
                    } else {
                        this.sortTable.sortType = true;
                    }
                } else {
                    this.sortTable.sortBy = 'customer_name';
                    this.sortTable.sortType = true;
                }

                break;

            case 'fare-type':
                if (this.sortTable.sortBy === 'route_fare_type') {
                    if (this.sortTable.sortType) {
                        this.sortTable.sortType = !this.sortTable.sortType;
                    } else {
                        this.sortTable.sortType = true;
                    }
                } else {
                    this.sortTable.sortBy = 'route_fare_type';
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

            case 'highway-fee':
                if (this.sortTable.sortBy === 'highway_fee') {
                    if (this.sortTable.sortType) {
                        this.sortTable.sortType = !this.sortTable.sortType;
                    } else {
                        this.sortTable.sortType = true;
                    }
                } else {
                    this.sortTable.sortBy = 'highway_fee';
                    this.sortTable.sortType = true;
                }

                break;

            case 'highway-fee-holiday':
                if (this.sortTable.sortBy === 'highway_fee_holiday') {
                    if (this.sortTable.sortType) {
                        this.sortTable.sortType = !this.sortTable.sortType;
                    } else {
                        this.sortTable.sortType = true;
                    }
                } else {
                    this.sortTable.sortBy = 'highway_fee_holiday';
                    this.sortTable.sortType = true;
                }

                break;

            case 'the-number-of-store':
                if (this.sortTable.sortBy === 'store') {
                    if (this.sortTable.sortType) {
                        this.sortTable.sortType = !this.sortTable.sortType;
                    } else {
                        this.sortTable.sortType = true;
                    }
                } else {
                    this.sortTable.sortBy = 'store';
                    this.sortTable.sortType = true;
                }

                break;

            default:
                console.log('Handle sort table faild');

                break;
            }

            this.getDataTableRoute(1);
        },
        handleChangeShowState() {
            this.isShowFullDate = !this.isShowFullDate;

            window.localStorage.setItem('route_master_index_is_show_full', JSON.stringify(this.isShowFullDate));
        },
        doRegister() {
            this.$router.push({ path: '/master-manager/route-master-create' });
        },
        async doCSVRegister() {
            if (this.formData) {
                this.errorImportModalData = [];

                try {
                    const response = await importCSV(urlAPI.apiImportCSV, this.formData);

                    if (response.code === 200) {
                        this.$toast.success({
                            content: 'インポートに成功しています。',
                        });

                        this.formData = null;

                        this.fileNameCSVInput = this.$t('DATA_IMPORT.SELECT_FILES');

                        this.showModalImportRegister = false;

                        this.getDataTableRoute(1);
                    } else {
                        this.$toast.warning({
                            content: response.message,
                        });
                    }
                } catch (error) {
                    for (let i = 0; i < error.response.data.message_content.length; i++) {
                        this.errorImportModalData.push(
                            { message: error.response.data.message_content[i] }
                        );
                    }
                }
            } else {
                this.$toast.warning({
                    content: 'ファイルを選択してください',
                });
            }
        },
        triggerSelectFileInput() {
            this.$refs.selectFileInput.click();
        },
        handleTurnOffImportModal() {
            document.getElementById('fileUpload').value = '';
            this.formData = null;

            this.showModalImportRegister = false;
            this.fileNameCSVInput = this.$t('DATA_IMPORT.SELECT_FILES');
            this.errorImportModalData = [];
        },
        handleClickFileInput() {
            document.getElementById('fileUpload').value = '';
        },
        handleShowModalImportCSV() {
            this.showModalImportRegister = !this.showModalImportRegister;
        },
        handleCSVFile() {
            const fileInput = document.getElementById('fileUpload');

            if (fileInput.files.length > 0) {
                this.fileNameCSVInput = fileInput.files[0].name;

                const formData = new FormData();

                formData.append('file', fileInput.files[0]);

                this.formData = formData;
            } else {
                this.formData = null;
                this.fileNameCSVInput = this.$t('DATA_IMPORT.SELECT_FILES');
            }
        },
        doMultiEditing() {
            this.$router.push({ path: '/master-manager/route-master-edit' });
        },
        onClickEdit(id) {
            if (id) {
                this.$router.push({ path: `/master-manager/route-master-edit/${id}` });
            }
        },
        async getCurrentPage(value) {
            if (value) {
                this.pagination.current_page = value;
                await this.$store.dispatch('pagination/setRouteMasterCP', value);
                this.getDataTableRoute(value);
            }
        },
        async getPerPage() {
            await this.$store.dispatch('pagination/setRouteMasterCP', 1);
            await this.$store.dispatch('pagination/setRouteMasterPerPage', this.pagination.per_page);
            this.getDataTableRoute(1);
        },
        onClickDelete(id) {
            if (id) {
                this.showModal = true;
                this.delete_id = id;
            }
        },
        async handleDelete() {
            try {
                const response = await deleteRoute(`${urlAPI.apiRemoveRoute}/${this.delete_id}`);

                if (response.code === 200) {
                    this.getDataTableRoute(1);

                    this.$toast.success({
                        content: 'ルートを削除しました',
                    });
                }
            } catch (error) {
                console.log(error.response);

                this.$toast.danger({
                    content: error.response.data.message || '[Error]',
                });
            }

            this.showModal = false;
        },
        getIsCheckDepartmentFilter(value) {
            if (!value) {
                this.filter.department.department_option = null;
            }

            this.filter.department.is_check = value;
        },
        getIsCheckRouteNameFilter(value) {
            if (!value) {
                this.filter.routeName.route_name_option = null;
            }

            this.filter.routeName.is_check = value;
        },
        getIsCheckCustomerFilter(value) {
            if (!value) {
                this.filter.customer.customer_option = null;
            }

            this.filter.customer.is_check = value;
        },
        handleSaveFilter(filter) {
            this.$store.dispatch('filter/setFilterRouteMaster', filter);
        },
        handleScrollTableRouteMasterHeader() {
            const source = this.$refs.tableRouteMasterHeader;
            const targer = this.$refs.tableRouteMasterContent;

            this.$nextTick(() => {
                targer.scrollLeft = source.scrollLeft;
            });
        },
        handleScrollTableRouteMasterContent() {
            const source = this.$refs.tableRouteMasterContent;
            const targer = this.$refs.tableRouteMasterHeader;

            this.$nextTick(() => {
                targer.scrollLeft = source.scrollLeft;
            });
        },
    },
};
</script>

<style lang="scss" scoped>
@import "@/scss/variables.scss";

::v-deep .dropdown-menu {
  max-height: 200px;
  overflow-y: auto;
}

.text-overlay {
  margin-top: 10px;
}

.route-master {
  &__header,
  &__filter,
  &__functional-button-header,
  &__table,
  &__custom-per-page,
  &__pagination {
    margin-bottom: 20px;
  }

  .apply-filter-button {
    min-width: 190px !important;
  }

  &__table {
    width: 100%;
    max-height: 850px;

    .table-route-master-header {
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

      table#table-route {
        margin: 0 !important;

        thead {
          th {
            top: 0;
            z-index: 10;
            font-size: 14px;
            position: sticky;
            text-align: center;
            padding-top: 0.25rem;
            vertical-align:middle;
            padding-bottom: 0.25rem;
            position: -webkit-sticky;
          }
        }

        tbody {
          tr {
            td {
              font-size: 14px;
              text-align: center;
              padding-top: 0.25rem;
              vertical-align: middle;
              width: 80px !important;
              min-width: 80px !important;
            }
          }
        }
      }
    };

    .table-route-master-content {
      z-index: 1;
      top: -64px;
      position: relative;
      overflow: scroll !important;
      overscroll-behavior: auto !important;
      max-height: 850px;

      &::-webkit-scrollbar {
        width: 8px !important;
        height: 0px !important;
      }

      table#table-route {
        max-height: 850px;
        margin: 0 !important;

        thead {
          th {
            top: 0;
            z-index: 10;
            font-size: 14px;
            position: sticky;
            text-align: center;
            padding-top: 0.25rem;
            vertical-align:middle;
            padding-bottom: 0.25rem;
            position: -webkit-sticky;
          }
        }

        tbody {
          tr {
            td {
              font-size: 14px;
              text-align: center;
              padding-top: 0.25rem;
              vertical-align: middle;
              width: 80px !important;
              min-width: 80px !important;
            }
          }
        }
      }
    };
  }

  .sort {
    cursor: pointer;

    i {
      margin-left: 10px;
      font-size: 15px;
    }
  }

  .colored-th {
    background-color: $tolopea !important;
    color: $white !important;
  }

  .fixed-width {
    vertical-align: middle;
    width: 40px;
  }

  .icon-plus-schedule {
    font-size: 25px;
    cursor: pointer;
  }

  .icon-minus-schedule {
    float: left;
    font-size: 25px;
    cursor: pointer;
  }

  .icon-delete {
    cursor: pointer;
  }

  #custom-per-page {
    width: 130px;
  }

  &__pagination {
    display: flex;
    justify-content: center;
  }

  .department-th,
  .route-name-th,
  .route-id-th,
  .customer-th,
  .fare-type-th,
  .fare-th,
  .highway-fee-th,
  .remark-th,
  .highway-fee-holiday-th {
    min-width: 150px;
  }

  .department-th {
    position: sticky;
    position: -webkit-sticky;
    z-index: 30 !important;
    top: 0 !important;
    left: 0 !important;
  }

  .route-id-th {
    position: sticky;
    position: -webkit-sticky;
    z-index: 30 !important;
    top: 0 !important;
    left: 150px !important;
  }

  .route-name-th {
    position: sticky;
    position: -webkit-sticky;
    z-index: 30 !important;
    top: 0 !important;
    left: 300px !important;
  }

  .the-number-of-store-th {
    min-width: 350px;
  }

  .store-td {
    min-width: 350px;
  }

  .department-td {
    position: sticky;
    position: -webkit-sticky;
    z-index: 20 !important;
    left: 0 !important;
    background-color: #FFFFFF !important;
  }

  .route-id-td {
    position: sticky;
    position: -webkit-sticky;
    z-index: 20 !important;
    left: 150px !important;
    background-color: #FFFFFF !important;
  }

  .route-name-td {
    position: sticky;
    position: -webkit-sticky;
    z-index: 20 !important;
    left: 300px !important;
    background-color: #FFFFFF !important;
  }

  .date-th {
    min-width: 50px;
  }

  .delete-th,
  .holiday-th {
    min-width: 80px;
  }

  .route-master-table-checkbox {
    pointer-events: none;
    padding-top: 10px !important;
    padding-left: 40px !important;
  }

  .day-off-date {
    background-color: $rolling-stone !important;
  }

  .modal-btn {
    min-width: 120px;
  }

  .route-master-table-dropdown-item {
    background-color: $white !important;
    color: $black !important;
  }

  .suspension-of-service-collapse {
    position: sticky;
    position: -webkit-sticky;
    top: 30.5px !important;
    width: 80px !important;
    min-width: 80px !important;
  }

  .suspension-of-service-date-th-collapsed {
    width: 80px !important;
    min-width: 80px !important;
  }

  .suspension-of-service-full,
  .schedule {
    position: sticky;
    top: 35px !important;
    width: 80px !important;
    position: -webkit-sticky;
    min-width: 80px !important;
  }

  ::v-deep .custom-control-input:checked ~ .custom-control-label::before {
    color: #FFFFFF !important;
    border-color: $tolopea !important;
    background-color: $tolopea !important;
  }

  ::-webkit-scrollbar {
    height: 8px;
    width: 12px;
  }

  ::-webkit-scrollbar-thumb {
    border-radius: 45px;
  }

  ::v-deep .dropdown-toggle {
    background-color: darkslateblue !important;
  }

  ::v-deep .dropdown-menu.show {
    left: -1px !important;
    min-width: 327px;
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

  ::v-deep #modal-csv-import-register {
    .modal-custom-header {
      border-bottom: 0 none;
    }

    .modal-custom-body {
      text-align: center;

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
        min-width: 250px;
        margin: 0 15px;

        &:hover {
          opacity: 0.8;
        }

        &:focus {
          opacity: 0.8;
        }
      }

      .btn-cancel {
        border-radius: 45px;
      }

      .modal-btn {
        font-weight: bold;

        background-color: $west-side;
        color: $white;

        &:focus {
          background-color: $west-side;
          color: $white;
        }
      }
    }
  }

  .btn-select-file {
    display: flex;
    justify-content: center;
    background: $white;
    min-width: 300px;
    color: $black;
    min-height: 50px;
    box-shadow: rgba(0, 0, 0, 0.24) 0px 3px 8px;
  }

  ::v-deep .fas:hover {
    cursor: pointer;
  }

  ::v-deep table {
    border-spacing: 0 !important;
    border-collapse: separate !important;
  }
}
</style>
