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

				<div v-if="false" class="route-master__filter">
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
							<vButton class="button-back pr-3" :text-button="$t('ROUTE_MASTER_RETURN')" @click.native="returnToIndex()" />
							<vButton class="button-save" :text-button="$t('ROUTE_MASTER_SAVE')" @click.native="doUpdateMany()" />
						</b-col>
					</b-row>
				</div>

				<div class="route-master__table">

					<div ref="tableRouteMasterHeader" class="table-route-master-header" @scroll="handleScrollTableRouteMasterHeader">
						<b-table-simple id="table-route" bordered class="table-route-master">
							<b-thead>
								<b-tr>
									<b-th class="route-master-table-th department-th colored-th" rowspan="2">
										<span>{{ $t('ROUTE_MASTER_DEPARTMENT') }}</span>
									</b-th>

									<b-th class="route-master-table-th route-id-th colored-th" rowspan="2">
										<span>{{ $t('ROUTE_MASTER_ROUTE_ID') }}</span>
									</b-th>

									<b-th class="route-master-table-th route-name-th colored-th" rowspan="2">
										<span>{{ $t('ROUTE_MASTER_ROUTE_NAME') }}</span>
									</b-th>

									<b-th class="route-master-table-th customer-th colored-th" rowspan="2">
										<span>{{ $t('ROUTE_MASTER_CUSTOMER') }}</span>
									</b-th>

									<b-th class="route-master-table-th fare-type-th colored-th" rowspan="2">
										<span>{{ $t('ROUTE_MASTER_FARE_TYPE') }}</span>
									</b-th>

									<b-th class="route-master-table-th fare-th colored-th" rowspan="2">
										<span>{{ $t('ROUTE_MASTER_FARE') }}</span>
									</b-th>

									<b-th class="route-master-table-th highway-fee-th colored-th" rowspan="2">
										<span>{{ $t('ROUTE_MASTER_HIGHWAY_FEE') }}</span>
									</b-th>

									<b-th class="route-master-table-th highway-fee-holiday-th colored-th" rowspan="2">
										<span>{{ $t('ROUTE_MASTER_HIGHWAY_FEE_HOLIDAY') }}</span>
									</b-th>

									<b-th class="route-master-table-th the-number-of-store-th colored-th" rowspan="2">
										<span>{{ $t('ROUTE_MASTER_THE_NUMBER_OF_STORE') }}</span>
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
								</b-tr>

								<b-tr>
									<b-th :class="['route-master-table-th tsuki-th colored-th weekday-th', isShowFullDate === true ? 'suspension-of-service-full' : 'suspension-of-service-collapse']" rowspan="1">
										<span>{{ $t('ROUTE_MASTER_TSUKI') }}</span>
									</b-th>
									<b-th :class="['route-master-table-th fire-hi-th colored-th weekday-th', isShowFullDate === true ? 'suspension-of-service-full' : 'suspension-of-service-collapse']" rowspan="1">
										<span>{{ $t('ROUTE_MASTER_FIRE_HI') }}</span>
									</b-th>
									<b-th :class="['route-master-table-th mizu-th colored-th weekday-th', isShowFullDate === true ? 'suspension-of-service-full' : 'suspension-of-service-collapse']" rowspan="1">
										<span>{{ $t('ROUTE_MASTER_MIZU') }}</span>
									</b-th>
									<b-th :class="['route-master-table-th ki-th colored-th weekday-th', isShowFullDate === true ? 'suspension-of-service-full' : 'suspension-of-service-collapse']" rowspan="1">
										<span>{{ $t('ROUTE_MASTER_KI') }}</span>
									</b-th>
									<b-th :class="['route-master-table-th kin-th colored-th weekday-th', isShowFullDate === true ? 'suspension-of-service-full' : 'suspension-of-service-collapse']" rowspan="1">
										<span>{{ $t('ROUTE_MASTER_KIN') }}</span>
									</b-th>
									<b-th :class="['route-master-table-th tsuchi-th colored-th weekday-th', isShowFullDate === true ? 'suspension-of-service-full' : 'suspension-of-service-collapse']" rowspan="1">
										<span>{{ $t('ROUTE_MASTER_TSUCHI') }}</span>
									</b-th>
									<b-th :class="['route-master-table-th day-hi-th colored-th weekday-th', isShowFullDate === true ? 'suspension-of-service-full' : 'suspension-of-service-collapse']" rowspan="1">
										<span>{{ $t('ROUTE_MASTER_DAY_HI') }}</span>
									</b-th>
									<b-th :class="['route-master-table-th holiday-th colored-th weekday-th', isShowFullDate === true ? 'suspension-of-service-full' : 'suspension-of-service-collapse']" rowspan="1">
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
									<b-th class="route-master-table-th department-th colored-th" rowspan="2">
										<span>{{ $t('ROUTE_MASTER_DEPARTMENT') }}</span>
									</b-th>

									<b-th class="route-master-table-th route-id-th colored-th" rowspan="2">
										<span>{{ $t('ROUTE_MASTER_ROUTE_ID') }}</span>
									</b-th>

									<b-th class="route-master-table-th route-name-th colored-th" rowspan="2">
										<span>{{ $t('ROUTE_MASTER_ROUTE_NAME') }}</span>
									</b-th>

									<b-th class="route-master-table-th customer-th colored-th" rowspan="2">
										<span>{{ $t('ROUTE_MASTER_CUSTOMER') }}</span>
									</b-th>

									<b-th class="route-master-table-th fare-type-th colored-th" rowspan="2">
										<span>{{ $t('ROUTE_MASTER_FARE_TYPE') }}</span>
									</b-th>

									<b-th class="route-master-table-th fare-th colored-th" rowspan="2">
										<span>{{ $t('ROUTE_MASTER_FARE') }}</span>
									</b-th>

									<b-th class="route-master-table-th highway-fee-th colored-th" rowspan="2">
										<span>{{ $t('ROUTE_MASTER_HIGHWAY_FEE') }}</span>
									</b-th>

									<b-th class="route-master-table-th highway-fee-holiday-th colored-th" rowspan="2">
										<span>{{ $t('ROUTE_MASTER_HIGHWAY_FEE_HOLIDAY') }}</span>
									</b-th>

									<b-th class="route-master-table-th the-number-of-store-th colored-th" rowspan="2">
										<span>{{ $t('ROUTE_MASTER_THE_NUMBER_OF_STORE') }}</span>
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
								</b-tr>

								<b-tr>
									<b-th :class="['route-master-table-th tsuki-th colored-th weekday-th', isShowFullDate === true ? 'suspension-of-service-full' : 'suspension-of-service-collapse']" rowspan="1">
										<span>{{ $t('ROUTE_MASTER_TSUKI') }}</span>
									</b-th>

									<b-th :class="['route-master-table-th fire-hi-th colored-th weekday-th', isShowFullDate === true ? 'suspension-of-service-full' : 'suspension-of-service-collapse']" rowspan="1">
										<span>{{ $t('ROUTE_MASTER_FIRE_HI') }}</span>
									</b-th>

									<b-th :class="['route-master-table-th mizu-th colored-th weekday-th', isShowFullDate === true ? 'suspension-of-service-full' : 'suspension-of-service-collapse']" rowspan="1">
										<span>{{ $t('ROUTE_MASTER_MIZU') }}</span>
									</b-th>

									<b-th :class="['route-master-table-th ki-th colored-th weekday-th', isShowFullDate === true ? 'suspension-of-service-full' : 'suspension-of-service-collapse']" rowspan="1">
										<span>{{ $t('ROUTE_MASTER_KI') }}</span>
									</b-th>

									<b-th :class="['route-master-table-th kin-th colored-th weekday-th', isShowFullDate === true ? 'suspension-of-service-full' : 'suspension-of-service-collapse']" rowspan="1">
										<span>{{ $t('ROUTE_MASTER_KIN') }}</span>
									</b-th>

									<b-th :class="['route-master-table-th tsuchi-th colored-th weekday-th', isShowFullDate === true ? 'suspension-of-service-full' : 'suspension-of-service-collapse']" rowspan="1">
										<span>{{ $t('ROUTE_MASTER_TSUCHI') }}</span>
									</b-th>

									<b-th :class="['route-master-table-th day-hi-th colored-th weekday-th', isShowFullDate === true ? 'suspension-of-service-full' : 'suspension-of-service-collapse']" rowspan="1">
										<span>{{ $t('ROUTE_MASTER_DAY_HI') }}</span>
									</b-th>

									<b-th :class="['route-master-table-th holiday-th colored-th weekday-th', isShowFullDate === true ? 'suspension-of-service-full' : 'suspension-of-service-collapse']" rowspan="1">
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
										<b-form-input id="select-department" v-model="item.department" type="text" disabled />
									</b-td>
									<b-td class="route-master-table-td">
										<b-form-input id="select-route-id" v-model="item.id" type="text" disabled />
									</b-td>
									<b-td class="route-master-table-td route-name-td">
										<b-form-input
											id="input-route-name"
											v-model="item.route_name"
											type="text"
										/>
									</b-td>
									<b-td class="route-master-table-td">
										<b-form-select
											id="select-customer"
											v-model="item.customer_id"
											:options="customer_list"
										/>
									</b-td>
									<b-td class="route-master-table-td">
										<b-form-select
											id="select-fare-type"
											v-model="item.fare_type"
											:options="fare_type_list"
										/>
									</b-td>
									<b-td class="route-master-table-td">
										<b-form-input
											id="input-fare"
											v-model="item.fare"
											type="text"
											:placeholder="$t('ROUTE_MASTER_INPUT_PLACE_HOLDER')"
											onkeydown="javascript: return event.keyCode === 8 || event.keyCode === 46 ? true : !isNaN(Number(event.key))"
										/>
									</b-td>
									<b-td class="route-master-table-td">
										<b-form-input
											id="input-highway-fee"
											v-model="item.highway_fee"
											type="text"
											:placeholder="$t('ROUTE_MASTER_INPUT_PLACE_HOLDER')"
											onkeydown="javascript: return event.keyCode === 8 || event.keyCode === 46 ? true : !isNaN(Number(event.key))"
										/>
									</b-td>
									<b-td class="route-master-table-td">
										<b-form-input
											id="input-highway-fee-holiday"
											v-model="item.highway_fee_holiday"
											type="text"
											:placeholder="$t('ROUTE_MASTER_INPUT_PLACE_HOLDER')"
											onkeydown="javascript: return event.keyCode === 8 || event.keyCode === 46 ? true : !isNaN(Number(event.key))"
										/>
									</b-td>
									<b-td class="route-master-table-td store-td">
										<vMultiselect
											ref="the_number_of_store"
											v-model="item.the_number_of_store"
											:options="item.the_number_of_store_list"
											:searchable="true"
											:close-on-select="false"
											:show-labels="false"
											:multiple="true"
											:max-height="200"
											track-by="id"
											label="text"
											open-direction="bottom"
											:placeholder="'店舗名検索'"
											@select="handleSelectCheckbox($event, item.the_number_of_store_list, item.id, item.the_number_of_store)"
											@remove="handleRemoveCheckbox($event, item.the_number_of_store_list, item.id, item.the_number_of_store)"
										>
											<template slot="option" slot-scope="props">
												<b-form-checkbox
													:id="`checkbox-${props.option.value}`"
													v-model="props.option.value"
													class="route-master-table-checkbox"
													size="lg"
													:name="`checkbox-${props.option.value}`"
													value="true"
													unchecked-value="false"
												>
													<span class="dropdown-checkbox-text d-flex">{{ props.option.text }}</span>
												</b-form-checkbox>
											</template>

											<template slot="noResult">
												<span>一致する店舗が存在しません。</span>
											</template>

											<template slot="noOptions">
												<span>リストが空です。</span>
											</template>

											<template slot="selection" slot-scope="{ values }">
												<span v-if="values.length > 0">{{ `${item.the_number_of_store.length} 選択` }}</span>
											</template>
										</vMultiselect>
									</b-td>
									<b-td v-for="(service, serviceIndex) in item.suspension_of_service" :key="`suspension-of-service-${serviceIndex}`" class="route-master-table-td" @click="handleSelectSuspensionCheckbox(item, service.id)">
										<b-form-checkbox
											:id="`checkbox-${serviceIndex}`"
											v-model="service.value"
											class="suspension-of-service-checkbox"
											size="lg"
											:name="`checkbox-${serviceIndex}`"
											value="true"
											unchecked-value="false"
										/>
									</b-td>
									<template v-if="isShowFullDate === true">
										<template v-for="(date, dateIndex) in item.schedule">
											<template v-if="date.value === true">
												<b-td :key="`schedule-date-${dateIndex}`" class="route-master-table-td scheudle day-off-date" @click="handleSelectSchedule(item, date.id)" />
											</template>
											<template v-else>
												<b-td :key="`schedule-date-${dateIndex}`" class="route-master-table-td" @click="handleSelectSchedule(item, date.id)" />
											</template>
										</template>
									</template>
									<template v-else>
										<b-td class="route-master-table-td" />
									</template>
									<b-td class="route-master-table-td remark-td">
										<b-form-input
											id="input-remark"
											v-model="item.remark"
											type="text"
											:placeholder="$t('ROUTE_MASTER_INPUT_PLACE_HOLDER')"
										/>
									</b-td>
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
			</div>
		</b-col>
	</b-overlay>
</template>

<script>
import vHeaderPage from '@/components/atoms/vHeaderPage.vue';
import vHeaderFilter from '@/components/atoms/vHeaderFilter';
import vButton from '@/components/atoms/vButton';
import vMultiselect from 'vue-multiselect';
import vSelectGroup from '@/components/atoms/vSelectGroup';
import vInputGroup from '@/components/atoms/vInputGroup';

import { getListRoute, getFilterCustomer, getFilterDepartment, postManyRoute, getListStore } from '@/api/modules/routeMaster';

import { handleTransformDataFormat } from '@/utils/handleTransformDataFormat';

import { cleanObj } from '@/utils/handleObj';
import { obj2Path } from '@/utils/obj2Path';

const urlAPI = {
    apiGetOneRoute: '/route',
    apiGetListRoute: '/route',
    apiGetFilterCustomer: '/customer/list-all',
    apiGetFilterDepartment: '/department/list-all',
    apiPostManyRoute: '/route/update-many',
};

export default {
    name: 'RouteMasterEdit',
    components: {
        vHeaderPage,
        vHeaderFilter,
        vButton,
        vMultiselect,
        vSelectGroup,
        vInputGroup,
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

            department_options: [
                { value: null, text: this.$t('PLEASE_SELECT') },
            ],

            route_list: [
                { value: null, text: this.$t('ROUTE_MASTER_SELECT_PLACE_HOLDER') },
                { value: 1, text: 'Không cho đâu bé ơi' },
                { value: 2, text: 'Anh không cho đâu' },
            ],

            customer_options: [
                { value: null, text: this.$t('PLEASE_SELECT') },
            ],

            department_list: [
                { value: null, text: this.$t('ROUTE_MASTER_SELECT_PLACE_HOLDER') },
            ],

            customer_list: [
                { value: null, text: this.$t('ROUTE_MASTER_SELECT_PLACE_HOLDER') },
            ],

            fare_type_list: [
                { value: null, text: this.$t('ROUTE_MASTER_SELECT_PLACE_HOLDER') },
                { value: 1, text: this.$t('ROUTE_MASTER_REGISTER_FARE_TYPE_DAILY') },
                { value: 2, text: this.$t('ROUTE_MASTER_REGISTER_FARE_TYPE_MONTHLY') },
            ],

            vItems: [],

            filter: {
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

            numberDate: 31,

            isShowFullDate: false,
            listStore: [],
        };
    },
    computed: {
        LC_PAGINATION() {
            return JSON.parse(window.localStorage.getItem('route_master_edit_pagination'));
        },

        LC_IS_SHOW_FULL() {
            return JSON.parse(window.localStorage.getItem('route_master_edit_is_show_full'));
        },
    },
    created() {
        this.initData();
    },
    methods: {
        async initData() {
            await this.handleGetListStore();
            await this.handleOverrideIsShowFullStatus();
            await this.getDataTableRoute();
            await this.getDataFilter();
        },
        async handleGetListStore() {
            try {
                const URL = '/store/list-all';

                const { code, data } = await getListStore(URL);

                if (code === 200) {
                    this.listStore = data;
                } else {
                    this.listStore = [];
                }
            } catch (err) {
                this.listStore = [];
                console.log(err);
            }
        },
        handleOverrideIsShowFullStatus() {
            this.isShowFullDate = this.LC_IS_SHOW_FULL || true;
        },

        handleOverrideTableItem(DATA) {
            const RESULT = handleTransformDataFormat(DATA, this.listStore);
            return RESULT;
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

                        this.customer_list.push({
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

                        this.department_list.push({
                            value: DATA_DEPARTMENT_FILTER.data[i].id,
                            text: DATA_DEPARTMENT_FILTER.data[i].department_name,
                        });
                    }
                }
            } catch (error) {
                console.log(error);
            }
        },
        async getDataTableRoute() {
            this.overlay.show = true;

            const ID = this.$route.params.id;

            let QUERY = {
                route_id: ID,
            };

            QUERY = cleanObj(QUERY);

            const URL = `${urlAPI.apiGetListRoute}?${obj2Path(QUERY)}`;

            let DATA = [];

            try {
                const response = await getListRoute(URL);

                if (response.code === 200) {
                    // DATA = this.handleOverrideTableItem(response.data.result);

                    DATA = this.handleOverrideTableItem(response.data.result);

                    this.vItems = DATA;
                }
            } catch (error) {
                console.log(error);
            }

            this.overlay.show = false;
        },
        handleChangeShowState() {
            this.isShowFullDate = !this.isShowFullDate;

            this.$store.dispatch('route_master/setIsShowFull', this.isShowFullDate);
        },
        async doUpdateMany() {
            this.overlay.show = true;

            try {
                const UPDATE_DATA = JSON.parse(JSON.stringify(this.vItems));

                for (let i = 0; i < UPDATE_DATA.length; i++) {
                    UPDATE_DATA[i].route_fare_type = UPDATE_DATA[i]['fare_type'];
                    delete UPDATE_DATA[i].fare_type;

                    UPDATE_DATA[i].name = UPDATE_DATA[i]['route_name'];
                    delete UPDATE_DATA[i].route_name;

                    UPDATE_DATA[i].list_week = [];

                    UPDATE_DATA[i].list_month = [];

                    UPDATE_DATA[i].list_store = [];

                    for (let j = 0; j < UPDATE_DATA[i].suspension_of_service.length - 1; j++) {
                        if (UPDATE_DATA[i].suspension_of_service[j].value === true) {
                            UPDATE_DATA[i].list_week.push(UPDATE_DATA[i].suspension_of_service[j].id);
                        }
                    }

                    for (let k = 0; k < UPDATE_DATA[i].schedule.length; k++) {
                        if (UPDATE_DATA[i].schedule[k].value === true) {
                            UPDATE_DATA[i].list_month.push(UPDATE_DATA[i].schedule[k].id);
                        }
                    }

                    for (let l = 0; l < UPDATE_DATA[i].the_number_of_store.length; l++) {
                        if (UPDATE_DATA[i].the_number_of_store[l].value === true) {
                            UPDATE_DATA[i].list_store.push(UPDATE_DATA[i].the_number_of_store[l].id);
                        }
                    }

                    const length = UPDATE_DATA[i].suspension_of_service.length;

                    if (UPDATE_DATA[i].suspension_of_service[length - 1].value === true) {
                        UPDATE_DATA[i].is_government_holiday = 1;
                    } else {
                        UPDATE_DATA[i].is_government_holiday = 0;
                    }

                    delete UPDATE_DATA[i].schedule;
                    delete UPDATE_DATA[i].suspension_of_service;
                    delete UPDATE_DATA[i].the_number_of_store;
                    delete UPDATE_DATA[i].store_with_status;
                    delete UPDATE_DATA[i].stores;
                    delete UPDATE_DATA[i].the_number_of_store_list;
                }

                const response = await postManyRoute(urlAPI.apiPostManyRoute, UPDATE_DATA);

                if (response.code === 200) {
                    this.$toast.success({
                        content: this.$t('ROUTE_MASTER_EDIT_SUCCESS'),
                    });

                    await localStorage.clear();
                    await localStorage.removeItem('route_master_edit_data');
                    await localStorage.removeItem('route_master_edit_pagination');

                    this.$router.push({ path: '/master-manager/route-master' });
                } else {
                    this.$toast.warning({
                        content: this.$t('ROUTE_MASTER_EDIT_FAILED'),
                    });
                }
            } catch (error) {
                console.log(error);

                this.$toast.warning({
                    content: error.response.data.message || this.$t('ROUTE_MASTER_EDIT_FAILED'),
                });
            }

            this.overlay.show = false;
        },
        handleSelectCheckbox(event, item, id, selection) {
            for (let i = 0; i < item.length; i++) {
                if (item[i].id === event.id) {
                    item[i].value = !item[i].value;
                    // console.log(selection[0]);
                    // selection[0] += 1;
                    // console.log(selection[0]);
                }
            }
        },
        handleRemoveCheckbox(event, item, id, selection) {
            for (let i = 0; i < item.length; i++) {
                if (item[i].id === event.id) {
                    item[i].value = !item[i].value;
                    // selection[0] -= 1;
                }
            }
        },
        handleSelectSchedule(DATA, id) {
            for (let i = 0; i < DATA.schedule.length; i++) {
                if (DATA.schedule[i].id === id) {
                    DATA.schedule[i].value = !DATA.schedule[i].value;
                }
            }
        },
        doApply() {
            this.getDataTableRoute();
        },
        returnToIndex() {
            this.$router.push({ path: '/master-manager/route-master' });
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

            this.getDataTableRoute();
        },
        getIsCheckDepartmentFilter(value) {
            this.filter.department.is_check = value;
        },
        getIsCheckRouteNameFilter(value) {
            this.filter.routeName.is_check = value;
        },
        getIsCheckCustomerFilter(value) {
            this.filter.customer.is_check = value;
        },
        handleSelectSuspensionCheckbox(DATA, id) {
            for (let i = 0; i < DATA.suspension_of_service.length; i++) {
                if (DATA.suspension_of_service[i].id === id) {
                    DATA.suspension_of_service[i].value = !DATA.suspension_of_service[i].value;
                }
            }
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

<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>

<style lang="scss" scoped>
  @import "@/scss/variables.scss";

  ::v-deep .multiselect__content-wrapper {
    position: relative !important;
  }

  .multiselect {
    margin-top: 10px;
  }

  .form-control {
    margin-top: 10px;
    min-height: 40px;
  }

  .custom-select {
    margin-top: 10px;
    min-height: 40px;
  }

	.text-overlay {
		margin-top: 10px;
	}

	.route-master {
		&__header,
    &__functional-button-header,
    &__table,
    &__filter,
    &__custom-per-page,
    &__pagination {
			margin-bottom: 20px;
		}

    &__table {
      width: 100%;

      .table-route-master-header {
        z-index: 2;
        position: relative;
        overflow: scroll !important;
        overscroll-behavior: auto !important;

        &::-webkit-scrollbar {
          width: 8px !important;
          height: 12px !important;
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

            th.weekday-th {
              width: 80px !important;
              min-width: 80px !important;
            }

            th.suspension-of-service-date-th {
              width: 80px !important;
              min-width: 80px !important;
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
        top: -70px;
        position: relative;
        overflow: scroll !important;
        overscroll-behavior: auto !important;

        &::-webkit-scrollbar {
          width: 8px !important;
          height: 0px !important;
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

            th.weekday-th {
              width: 80px !important;
              min-width: 80px !important;
            }

            th.suspension-of-service-date-th {
              width: 80px !important;
              min-width: 80px !important;
            }
          }

          tbody {
            tr {
              td {
                font-size: 14px;
                text-align: center;
                padding-top: 0.25rem;
                vertical-align: middle;
              }

              td.checkbox-td {
                width: 80px !important;
                min-width: 80px !important;
              }

              td.schedule-td {
                width: 80px !important;
                min-width: 80px !important;
              }
            }
          }
        }
      };
    }

    .colored-th {
      background-color: $tolopea !important;
      color: $white !important;
    }

    .route-master-table-td {
      padding-top: 0.25rem;
      text-align: center;
      vertical-align:middle;
      font-size: 14px;
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

    #custom-per-page {
      width: 130px;
    }

    &__pagination {
      display: flex;
      justify-content: center;
    }

    .date-th {
      min-width: 80px;
    }

    .department-th,
    .route-id-th,
    .route-name-th,
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

    .route-name-th {
      position: sticky;
      position: -webkit-sticky;
      z-index: 30 !important;
      top: 0 !important;
      left: 150px !important;
    }

    .department-td {
      position: sticky;
      position: -webkit-sticky;
      z-index: 20 !important;
      left: 0 !important;
      background-color: #FFFFFF !important;
    }

    .route-name-td {
      position: sticky;
      position: -webkit-sticky;
      z-index: 20 !important;
      left: 150px !important;
      background-color: #FFFFFF !important;
    }

    .the-number-of-store-th,
    .remark-th {
      min-width: 350px;
    }

    .store-td,
    .remark-td {
      min-width: 350px;
    }

    .holiday-th {
      min-width: 60px;
    }

    .route-master-table-checkbox {
      padding-left: 2.8rem !important;
      pointer-events: none !important;
    }

    .suspension-of-service-checkbox {
      padding-top: 0.5rem !important;
      padding-left: 2.6rem !important;
      pointer-events: none !important;
    }

    .day-off-date {
      background-color: $rolling-stone !important;
    }

    .route-master-table-dropdown-item {
      background-color: $sorbus !important;
      color: $black !important;
    }

    .suspension-of-service-collapse {
      position: sticky;
      position: -webkit-sticky;
      top: 30.5px !important;
    }

    .suspension-of-service-full,
    .schedule {
      position: sticky;
      position: -webkit-sticky;
      top: 35px !important;
    }

    ::v-deep .dropdown-menu {
      background-color: $sorbus !important;
    }

    .dropdown-checkbox-text {
      font-size: 12px;
      padding-top: 5px;
    }

    ::v-deep .custom-control-input:checked ~ .custom-control-label::before {
      color: #FFFFFF !important;
      border-color: $tolopea !important;
      background-color: $tolopea !important;
    }

    ::-webkit-scrollbar {
      width: 8px;
      height: 12px;
    }

    ::-webkit-scrollbar-track {
      background: #FFFFFF;
    }

    ::-webkit-scrollbar-thumb {
      background: #3e3c47;
      border-radius: 45px;
    }

    ::-webkit-scrollbar-thumb:hover {
      background: #555;
    }

    ::v-deep table {
      border-spacing: 0px !important;
      border-collapse: separate !important;
    }
	}
</style>
