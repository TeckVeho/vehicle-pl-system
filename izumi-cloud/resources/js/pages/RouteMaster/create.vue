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

				<div class="route-master__functional-button-header">
					<b-row>
						<b-col cols="12" class="text-right">
							<vButton class="button-save" :text-button="$t('ROUTE_MASTER_CREATE')" @click.native="doCreate()" />
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
									<b-th class="route-master-table-th tsuki-th colored-th weekday-th" rowspan="1">
										<span>{{ $t('ROUTE_MASTER_TSUKI') }}</span>
									</b-th>

									<b-th class="route-master-table-th fire-hi-th colored-th weekday-th" rowspan="1">
										<span>{{ $t('ROUTE_MASTER_FIRE_HI') }}</span>
									</b-th>

									<b-th class="route-master-table-th mizu-th colored-th weekday-th" rowspan="1">
										<span>{{ $t('ROUTE_MASTER_MIZU') }}</span>
									</b-th>

									<b-th class="route-master-table-th ki-th colored-th weekday-th" rowspan="1">
										<span>{{ $t('ROUTE_MASTER_KI') }}</span>
									</b-th>

									<b-th class="route-master-table-th kin-th colored-th weekday-th" rowspan="1">
										<span>{{ $t('ROUTE_MASTER_KIN') }}</span>
									</b-th>

									<b-th class="route-master-table-th tsuchi-th colored-th weekday-th" rowspan="1">
										<span>{{ $t('ROUTE_MASTER_TSUCHI') }}</span>
									</b-th>

									<b-th class="route-master-table-th day-hi-th colored-th weekday-th" rowspan="1">
										<span>{{ $t('ROUTE_MASTER_DAY_HI') }}</span>
									</b-th>

									<b-th class="route-master-table-th holiday-th colored-th weekday-th" rowspan="1">
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
									<b-th class="route-master-table-th tsuki-th colored-th weekday-th" rowspan="1">
										<span>{{ $t('ROUTE_MASTER_TSUKI') }}</span>
									</b-th>

									<b-th class="route-master-table-th fire-hi-th colored-th weekday-th" rowspan="1">
										<span>{{ $t('ROUTE_MASTER_FIRE_HI') }}</span>
									</b-th>

									<b-th class="route-master-table-th mizu-th colored-th weekday-th" rowspan="1">
										<span>{{ $t('ROUTE_MASTER_MIZU') }}</span>
									</b-th>

									<b-th class="route-master-table-th ki-th colored-th weekday-th" rowspan="1">
										<span>{{ $t('ROUTE_MASTER_KI') }}</span>
									</b-th>

									<b-th class="route-master-table-th kin-th colored-th weekday-th" rowspan="1">
										<span>{{ $t('ROUTE_MASTER_KIN') }}</span>
									</b-th>

									<b-th class="route-master-table-th tsuchi-th colored-th weekday-th" rowspan="1">
										<span>{{ $t('ROUTE_MASTER_TSUCHI') }}</span>
									</b-th>

									<b-th class="route-master-table-th day-hi-th colored-th weekday-th" rowspan="1">
										<span>{{ $t('ROUTE_MASTER_DAY_HI') }}</span>
									</b-th>

									<b-th class="route-master-table-th holiday-th colored-th weekday-th" rowspan="1">
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

							<b-tbody>
								<b-tr>
									<b-td class="route-master-table-td">
										<b-form-select id="select-department" v-model="DATA.department" :options="department_list" />
									</b-td>

									<b-td class="route-master-table-td">
										<b-form-input id="input-route-name" v-model="DATA.route_name" type="text" :placeholder="$t('ROUTE_MASTER_INPUT_PLACE_HOLDER')" />
									</b-td>

									<b-td class="route-master-table-td">
										<b-form-select id="select-customer" v-model="DATA.customer" :options="customer_list" />
									</b-td>

									<b-td class="route-master-table-td">
										<b-form-select id="select-fare-type" v-model="DATA.fare_type" :options="fare_type_list" />
									</b-td>

									<b-td class="route-master-table-td">
										<b-form-input
											id="input-fare"
											v-model="DATA.fare"
											type="text"
											:placeholder="$t('ROUTE_MASTER_INPUT_PLACE_HOLDER')"
											onkeydown="javascript: return event.keyCode === 8 || event.keyCode === 46 ? true : !isNaN(Number(event.key))"
										/>
									</b-td>

									<b-td class="route-master-table-td">
										<b-form-input
											id="input-highway-fee"
											v-model="DATA.highway_fee"
											type="text"
											:placeholder="$t('ROUTE_MASTER_INPUT_PLACE_HOLDER')"
											onkeydown="javascript: return event.keyCode === 8 || event.keyCode === 46 ? true : !isNaN(Number(event.key))"
										/>
									</b-td>

									<b-td class="route-master-table-td">
										<b-form-input
											id="input-highway-fee-holiday"
											v-model="DATA.highway_fee_holiday"
											type="text"
											:placeholder="$t('ROUTE_MASTER_INPUT_PLACE_HOLDER')"
											onkeydown="javascript: return event.keyCode === 8 || event.keyCode === 46 ? true : !isNaN(Number(event.key))"
										/>
									</b-td>

									<b-td class="route-master-table-td store-td">
										<vMultiselect
											ref="the_number_of_store"
											v-model="DATA.the_number_of_store"
											:options="the_number_of_store_list"
											:searchable="true"
											:close-on-select="false"
											:show-labels="false"
											:multiple="true"
											track-by="text"
											label="text"
											:max-height="200"
											open-direction="bottom"
											:placeholder="'店舗名検索'"
											@select="handleSelectCheckbox"
											@remove="handleRemoveCheckbox"
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
												<span v-if="values.length > 0">{{ `${values.length} 選択` }}</span>
											</template>
										</vMultiselect>
									</b-td>

									<b-td v-for="(service, serviceIndex) in DATA.suspension_of_service" :key="`suspension-of-service-${serviceIndex}`" class="route-master-table-td checkbox-td" @click="handleSelectSuspensionCheckbox(service.id)">
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
										<template v-for="(date, dateIndex) in DATA.schedule">
											<template v-if="date.value === true">
												<b-td :key="`schedule-date-${dateIndex}`" class="route-master-table-td schedule-td day-off-date" @click="handleSelectSchedule(date.id)" />
											</template>

											<template v-else>
												<b-td :key="`schedule-date-${dateIndex}`" class="route-master-table-td schedule-td" @click="handleSelectSchedule(date.id)" />
											</template>
										</template>
									</template>

									<template v-else>
										<b-td class="route-master-table-td checkbox-td" />
									</template>

									<b-td class="route-master-table-td remark-td">
										<b-form-input
											id="input-remark"
											v-model="DATA.remark"
											type="text"
											:placeholder="$t('ROUTE_MASTER_INPUT_PLACE_HOLDER')"
										/>
									</b-td>
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
import vButton from '@/components/atoms/vButton';
import vMultiselect from 'vue-multiselect';

import { getFilterCustomer, getFilterStore, getFilterDepartment, postRoute } from '@/api/modules/routeMaster';

const urlAPI = {
    apiGetFilterCustomer: '/customer/list-all',
    apiGetFilterStore: '/store/list-all',
    apiGetFilterDepartment: '/department/list-all',
    apiPostRoute: '/route',
};

export default {
    name: 'RouteMasterCreate',
    components: {
        vHeaderPage,
        vButton,
        vMultiselect,
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

            department_list: [
                { value: null, text: this.$t('ROUTE_MASTER_SELECT_PLACE_HOLDER') },
            ],

            route_list: [
                { value: null, text: this.$t('ROUTE_MASTER_SELECT_PLACE_HOLDER') },
                { value: 1, text: 'Không cho đâu bé ơi' },
                { value: 2, text: 'Anh không cho đâu' },
            ],

            customer_list: [
                { value: null, text: this.$t('ROUTE_MASTER_SELECT_PLACE_HOLDER') },
            ],

            fare_type_list: [
                { value: null, text: this.$t('ROUTE_MASTER_SELECT_PLACE_HOLDER') },
                { value: 1, text: this.$t('ROUTE_MASTER_REGISTER_FARE_TYPE_DAILY') },
                { value: 2, text: this.$t('ROUTE_MASTER_REGISTER_FARE_TYPE_MONTHLY') },
            ],

            the_number_of_store_list: [],

            DATA: {
                department: null,
                route_id: null,
                route_name: '',
                customer: null,
                fare_type: null,
                fare: '',
                highway_fee: '',
                highway_fee_holiday: '',
                the_number_of_store: '',
                suspension_of_service: [
                    {
                        id: 1,
                        value: false,
                    },
                    {
                        id: 2,
                        value: false,
                    },
                    {
                        id: 3,
                        value: false,
                    },
                    {
                        id: 4,
                        value: false,
                    },
                    {
                        id: 5,
                        value: false,
                    },
                    {
                        id: 6,
                        value: false,
                    },
                    {
                        id: 7,
                        value: false,
                    },
                    {
                        id: 8,
                        value: false,
                    },
                ],
                schedule: [
                    {
                        id: 1,
                        value: false,
                    },
                    {
                        id: 2,
                        value: false,
                    },
                    {
                        id: 3,
                        value: false,
                    },
                    {
                        id: 4,
                        value: false,
                    },
                    {
                        id: 5,
                        value: false,
                    },
                    {
                        id: 6,
                        value: false,
                    },
                    {
                        id: 7,
                        value: false,
                    },
                    {
                        id: 8,
                        value: false,
                    },
                    {
                        id: 9,
                        value: false,
                    },
                    {
                        id: 10,
                        value: false,
                    },
                    {
                        id: 11,
                        value: false,
                    },
                    {
                        id: 12,
                        value: false,
                    },
                    {
                        id: 13,
                        value: false,
                    },
                    {
                        id: 14,
                        value: false,
                    },
                    {
                        id: 15,
                        value: false,
                    },
                    {
                        id: 16,
                        value: false,
                    },
                    {
                        id: 17,
                        value: false,
                    },
                    {
                        id: 18,
                        value: false,
                    },
                    {
                        id: 19,
                        value: false,
                    },
                    {
                        id: 20,
                        value: false,
                    },
                    {
                        id: 21,
                        value: false,
                    },
                    {
                        id: 22,
                        value: false,
                    },
                    {
                        id: 23,
                        value: false,
                    },
                    {
                        id: 24,
                        value: false,
                    },
                    {
                        id: 25,
                        value: false,
                    },
                    {
                        id: 26,
                        value: false,
                    },
                    {
                        id: 27,
                        value: false,
                    },
                    {
                        id: 28,
                        value: false,
                    },
                    {
                        id: 29,
                        value: false,
                    },
                    {
                        id: 30,
                        value: false,
                    },
                    {
                        id: 31,
                        value: false,
                    },
                ],
                remark: '',
            },

            numberDate: 31,

            isShowFullDate: true,

            isPassValidation: false,
        };
    },
    created() {
        this.getDataFilter();
    },
    methods: {
        async getDataFilter() {
            this.overlay.show = true;

            try {
                const DATA_CUSTOMER_FILTER = await getFilterCustomer(urlAPI.apiGetFilterCustomer);

                DATA_CUSTOMER_FILTER.data = Object.values(DATA_CUSTOMER_FILTER.data);

                if (DATA_CUSTOMER_FILTER.code === 200) {
                    for (let i = 0; i < DATA_CUSTOMER_FILTER.data.length; i++) {
                        this.customer_list.push({
                            value: DATA_CUSTOMER_FILTER.data[i].id,
                            text: DATA_CUSTOMER_FILTER.data[i].customer_name,
                        });
                    }
                }

                const DATA_STORE_FILTER = await getFilterStore(urlAPI.apiGetFilterStore);

                DATA_STORE_FILTER.data = Object.values(DATA_STORE_FILTER.data);

                if (DATA_STORE_FILTER.code === 200) {
                    for (let i = 0; i < DATA_STORE_FILTER.data.length; i++) {
                        this.the_number_of_store_list.push({
                            id: DATA_STORE_FILTER.data[i].id,
                            value: false,
                            text: DATA_STORE_FILTER.data[i].store_name,
                        });
                    }
                }

                const DATA_DEPARTMENT_FILTER = await getFilterDepartment(urlAPI.apiGetFilterDepartment);

                DATA_DEPARTMENT_FILTER.data = Object.values(DATA_DEPARTMENT_FILTER.data);

                if (DATA_DEPARTMENT_FILTER.code === 200) {
                    for (let i = 0; i < DATA_DEPARTMENT_FILTER.data.length; i++) {
                        this.department_list.push({
                            value: DATA_DEPARTMENT_FILTER.data[i].id,
                            text: DATA_DEPARTMENT_FILTER.data[i].department_name,
                        });
                    }
                }
            } catch (error) {
                console.log(error);
            }

            this.overlay.show = false;
        },
        handleChangeShowState() {
            this.isShowFullDate = !this.isShowFullDate;
        },
        async doCreate() {
            this.overlay.show = true;

            const LIST_WEEK = [];

            let IS_GOVERMENT_HOLIDAY = 0;

            const SUSPENSION_OF_SERVICE_LENGTH = this.DATA.suspension_of_service.length;

            for (let i = 0; i < SUSPENSION_OF_SERVICE_LENGTH; i++) {
                if (this.DATA.suspension_of_service[i].value === true) {
                    if (this.DATA.suspension_of_service[i].id === this.DATA.suspension_of_service[SUSPENSION_OF_SERVICE_LENGTH - 1].id) {
                        IS_GOVERMENT_HOLIDAY = 1;
                    } else {
                        LIST_WEEK.push(this.DATA.suspension_of_service[i].id);
                    }
                }
            }

            const LIST_MONTH = [];

            const SCHEDULE_LENGTH = this.DATA.schedule.length;

            for (let i = 0; i < SCHEDULE_LENGTH; i++) {
                if (this.DATA.schedule[i].value === true) {
                    LIST_MONTH.push(this.DATA.schedule[i].id);
                }
            }

            const LIST_STORE = [];

            const CHOSEN_STORE_LIST_LEGTH = this.DATA.the_number_of_store.length;

            for (let i = 0; i < CHOSEN_STORE_LIST_LEGTH; i++) {
                if (this.DATA.the_number_of_store[i].value === true) {
                    LIST_STORE.push(this.DATA.the_number_of_store[i].id);
                }
            }

            const ROUTE_DATA = {
                department_id: this.DATA.department,
                name: this.DATA.route_name,
                customer_id: this.DATA.customer,
                route_fare_type: this.DATA.fare_type,
                fare: this.DATA.fare ? parseInt(this.DATA.fare) : null,
                highway_fee: this.DATA.highway_fee ? parseInt(this.DATA.highway_fee) : null,
                highway_fee_holiday: this.DATA.highway_fee_holiday ? parseInt(this.DATA.highway_fee_holiday) : null,
                store_count: this.DATA.the_number_of_store.length,
                is_government_holiday: IS_GOVERMENT_HOLIDAY,
                list_week: LIST_WEEK,
                list_month: LIST_MONTH,
                list_store: LIST_STORE,
                remark: this.DATA.remark,
            };

            this.doValidation();

            if (this.isPassValidation) {
                try {
                    const response = await postRoute(urlAPI.apiPostRoute, ROUTE_DATA);

                    if (response.code === 200) {
                        this.$toast.success({
                            content: this.$t('ROUTE_MASTER_REGISTER_SUCCESS'),
                        });

                        this.$router.push({ path: '/master-manager/route-master' });
                    }
                } catch (error) {
                    console.log(error);

                    this.$toast.warning({
                        content: error.response.data.message || this.$t('ROUTE_MASTER_REGISTER_FAILED'),
                    });
                }
            }

            this.overlay.show = false;
        },
        doValidation() {
            this.isPassValidation = false;

            if (this.DATA.department === null) {
                this.$toast.warning({
                    content: this.$t('ROUTE_MASTER_REQUIRE_DEPARTMENT'),
                });
            } else if (this.DATA.route_name === '') {
                this.$toast.warning({
                    content: this.$t('ROUTE_MASTER_REQUIRE_ROUTE_NAME'),
                });
            } else if (this.DATA.customer === null) {
                this.$toast.warning({
                    content: this.$t('ROUTE_MASTER_REQUIRE_CUSTOMER'),
                });
            } else if (this.DATA.fare_type === null) {
                this.$toast.warning({
                    content: this.$t('ROUTE_MASTER_REQUIRE_FARE_TYPE'),
                });
            } else if (this.DATA.fare === '') {
                this.$toast.warning({
                    content: this.$t('ROUTE_MASTER_REQUIRE_FARE'),
                });
            } else if (this.DATA.highway_fee === '') {
                this.$toast.warning({
                    content: this.$t('ROUTE_MASTER_REQUIRE_HIGHWAY_FEE'),
                });
            } else if (this.DATA.highway_fee_holiday === '') {
                this.$toast.warning({
                    content: this.$t('ROUTE_MASTER_REQUIRE_HIGHWAY_FEE_HOLIDAY'),
                });
            } else {
                this.isPassValidation = true;
            }
        },
        handleSelectCheckbox(option) {
            for (let i = 0; i < this.the_number_of_store_list.length; i++) {
                if (this.the_number_of_store_list[i].id === option.id) {
                    this.the_number_of_store_list[i].value = !this.the_number_of_store_list[i].value;
                }
            }
        },
        handleRemoveCheckbox(option) {
            for (let i = 0; i < this.the_number_of_store_list.length; i++) {
                if (this.the_number_of_store_list[i].id === option.id) {
                    this.the_number_of_store_list[i].value = !this.the_number_of_store_list[i].value;
                }
            }
        },
        handleSelectSchedule(id) {
            for (let i = 0; i < this.DATA.schedule.length; i++) {
                if (this.DATA.schedule[i].id === id) {
                    this.DATA.schedule[i].value = !this.DATA.schedule[i].value;
                }
            }
        },
        handleSelectSuspensionCheckbox(id) {
            for (let i = 0; i < this.DATA.suspension_of_service.length; i++) {
                if (this.DATA.suspension_of_service[i].id === id) {
                    this.DATA.suspension_of_service[i].value = !this.DATA.suspension_of_service[i].value;
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

  ::placeholder {
    opacity: 1;
    color: #ADADAD !important;
  }

  :-ms-input-placeholder {
    color: #ADADAD !important;
  }

  ::-ms-input-placeholder {
    color: #ADADAD !important;
  }

	.text-overlay {
		margin-top: 10px;
	}

	.route-master {
		&__header,
    &__functional-button-header,
    &__table {
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

              th.weekday-th {
                width: 80px !important;
                min-width: 80px !important;
              }

              th.suspension-of-service-date-th {
                width: 80px !important;
                min-width: 80px !important;
              }
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

    .date-th {
      min-width: 80px;
    }

    .department-th,
    .route-name-th,
    .customer-th,
    .fare-type-th,
    .fare-th,
    .highway-fee-th,
    .highway-fee-holiday-th {
      min-width: 150px;
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
      border-spacing: 0 !important;
      border-collapse: separate !important;
    }
	}
</style>
