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

		<div class="course-master-detail">
			<b-col>
				<div class="course-master-detail__header">
					<vHeaderPage>
						{{ $t("ROUTER_COURSE_MASTER") }}
					</vHeaderPage>
				</div>

				<div class="course-master-detail__basic-information">
					<TitleHeader :label="'COURSE_MASTER_CREATE_TITLE_HEADER_BASIC_INFORMATION'" />

					<b-row>
						<b-col>
							<div class="zone-form">
								<div class="item-form">
									<label for="select-base">{{ $t('COURSE_MASTER_FORM_LABLE_DEPARTMENT') }}</label>
									<b-form-select
										id="select-base"
										v-model="isForm.base"
										:options="listBase"
										disabled
									/>
								</div>
							</div>
						</b-col>
					</b-row>

					<b-row>
						<b-col>
							<div class="zone-form">
								<div class="item-form">
									<label for="input-course-id">{{ $t('COURSE_MASTER_FORM_LABLE_COURSE_ID') }}</label>
									<b-form-input id="input-course-id" v-model="isForm.course_id" :placeholder="$t('COURSE_MASTER_CREATE_PLEASE_INPUT')" disabled />
								</div>
							</div>
						</b-col>
					</b-row>

					<b-row>
						<b-col>
							<div class="zone-form">
								<div class="item-form">
									<label for="input-course-id">{{ $t('COURSE_MASTER_FORM_LABLE_COURSE_ADDRESS') }}</label>
									<b-form-input id="input-course-id" v-model="isForm.course_address" :placeholder="$t('COURSE_MASTER_CREATE_PLEASE_COURSE_ADDRESS')" disabled />
								</div>
							</div>
						</b-col>
					</b-row>

					<b-row>
						<b-col cols="12" sm="12" md="12" lg="6" xl="6">
							<div class="zone-form">
								<div class="item-form">
									<label
										for="select-date-shipping-start-date"
									>
										{{ $t('COURSE_MASTER_FORM_LABLE_DELIVERY_START_DATE') }}
									</label>
									<b-form-datepicker
										id="select-date-shipping-start-date"
										v-model="isForm.start_date"
										class="mb-2"
										:placeholder="$t('COURSE_MASTER_CREATE_PLEASE_SELECT')"
										:locale="lang"
										:date-format-options="{ month: '2-digit', day: '2-digit' }"
										:max="isForm.end_date"
										disabled
									/>
								</div>
							</div>
						</b-col>

						<b-col cols="12" sm="12" md="12" lg="6" xl="6">
							<div class="zone-form">
								<div class="item-form">
									<label for="select-date-delivery-end-date">
										{{ $t('COURSE_MASTER_FORM_LABLE_DELIVERY_END_DATE') }}
									</label>
									<b-form-datepicker
										id="select-date-delivery-end-date"
										v-model="isForm.end_date"
										class="mb-2"
										:placeholder="$t('COURSE_MASTER_CREATE_PLEASE_SELECT')"
										:locale="lang"
										:date-format-options="{ month: '2-digit', day: '2-digit' }"
										:min="isForm.start_date"
										disabled
									/>
								</div>
							</div>
						</b-col>
					</b-row>

					<b-row>
						<b-col>
							<div class="zone-form">
								<div class="item-form">
									<label for="select-irregular-course">
										{{ $t("COURSE_MASTER_FORM_LABLE_COURSE_FLAG") }}
									</label>
									<b-form-select
										id="select-irregular-course"
										v-model="isForm.course_flag"
										:options="listCourseFlag"
										disabled
									/>
								</div>
							</div>
						</b-col>
					</b-row>

					<b-row>
						<b-col>
							<div class="zone-form">
								<div class="item-form">
									<label for="select-course-type">
										{{ $t('COURSE_MASTER_FORM_LABLE_COURSE_TYPE') }}
									</label>
									<b-form-select
										id="select-course-type"
										v-model="isForm.course_type"
										:options="listCourseType"
										disabled
									/>
								</div>
							</div>
						</b-col>
					</b-row>

					<b-row>
						<b-col>
							<div class="zone-form">
								<div class="item-form">
									<label for="select-flight-type">{{ $t('COURSE_MASTER_FORM_LABLE_BIN_TYPE') }}</label>
									<b-form-select
										id="select-flight-type"
										v-model="isForm.bin_type"
										:options="listFightType"
										disabled
									/>
								</div>
							</div>
						</b-col>
					</b-row>

					<b-row>
						<b-col>
							<div class="zone-form">
								<div class="item-form">
									<label for="select-delivery-type">{{ $t('COURSE_MASTER_FORM_LABLE_DELIVERY_TYPE') }}</label>
									<b-form-select
										id="select-delivery-type"
										v-model="isForm.delivery_type"
										:options="listDeliveryType"
										disabled
									/>
								</div>
							</div>
						</b-col>
					</b-row>

					<b-row>
						<b-col>
							<div class="zone-form">
								<div class="item-form">
									<label for="select-number">{{ $t('COURSE_MASTER_FORM_LABLE_QUANTITY') }}</label>
									<b-form-input
										id="input-quanity"
										v-model="isForm.quantity"
										type="number"
										:placeholder="$t('COURSE_MASTER_CREATE_PLEASE_INPUT')"
										disabled
									/>
								</div>
							</div>
						</b-col>
					</b-row>

					<b-row>
						<b-col>
							<label for="select-route-start-time-hour">{{ $t('COURSE_MASTER_FORM_LABLE_COUSE_START_TIME') }}</label>
						</b-col>
					</b-row>

					<b-row>
						<b-col cols="12" sm="12" md="12" lg="6" xl="6">
							<div class="zone-form">
								<div class="item-form">
									<b-input-group append="時">
										<b-form-select
											id="select-route-start-time-hour"
											v-model="isForm.route_start_time_hour"
											:options="listRouteStartTimeHour"
											disabled
										/>
									</b-input-group>
								</div>
							</div>
						</b-col>

						<b-col cols="12" sm="12" md="12" lg="6" xl="6">
							<div class="zone-form">
								<div class="item-form">
									<b-input-group append="分">
										<b-form-select
											id="select-route-start-time-min"
											v-model="isForm.route_start_time_min"
											:options="listRouteStartTimeMin"
											disabled
										/>
									</b-input-group>
								</div>
							</div>
						</b-col>
					</b-row>

					<b-row>
						<b-col>
							<div class="zone-form">
								<div class="item-form">
									<label for="select-route-allowance">{{ $t('COURSE_MASTER_FORM_LABLE_COUSE_ALLOWANCE') }}</label>
									<b-form-input
										id="input-course-allowance"
										:value="isForm.course_allowance"
										:placeholder="$t('COURSE_MASTER_CREATE_PLEASE_INPUT')"
										disabled
									/>
								</div>
							</div>
						</b-col>
					</b-row>

					<b-row>
						<b-col>
							<div class="zone-form">
								<div class="item-form">
									<label for="select-gate">{{ $t('COURSE_MASTER_FORM_LABLE_GATE') }}</label>
									<b-form-select
										id="select-gate"
										v-model="isForm.gate"
										:options="listGate"
										disabled
									/>
								</div>
							</div>
						</b-col>
					</b-row>

					<b-row>
						<b-col>
							<div class="zone-form">
								<div class="item-form">
									<label for="select-wing">{{ $t('COURSE_MASTER_FORM_LABLE_WING') }}</label>
									<b-form-select
										id="select-wing"
										v-model="isForm.wing"
										:options="listWing"
										disabled
									/>
								</div>
							</div>
						</b-col>
					</b-row>

					<b-row>
						<b-col>
							<div class="zone-form">
								<div class="item-form">
									<label for="select-tonnage">{{ $t('COURSE_MASTER_FORM_LABLE_TONNAGE') }}</label>
									<b-form-select
										id="select-tonnage"
										v-model="isForm.tonnage"
										:options="listTonnage"
										disabled
									/>
								</div>
							</div>
						</b-col>
					</b-row>

					<!-- <b-row>
						<b-col>
						<div class="zone-form">
						<div class="item-form">
						<label for="select-shipper">荷主</label>
						<b-form-select
						id="select-shipper"
						v-model="isForm.shipper"
						:options="listShipper"
						disabled
						/>
						</div>
						</div>
						</b-col>
						</b-row>

						<b-row>
						<b-col>
						<div class="zone-form">
						<div class="item-form">
						<label for="select-delivery-store">配送店舗</label>
						<b-form-select
						id="select-delivery-store"
						v-model="isForm.delivery_store"
						:options="listDeliveryStore"
						disabled
						/>
						</div>
						</div>
						</b-col>
						</b-row> -->

					<TitleHeader :label="'COURSE_MASTER_CREATE_TITLE_HEADER_CREATE_COURSE'" />

					<b-row>
						<b-col>
							<div class="zone-form">
								<div class="item-form">
									<label for="">
										{{ $t('COURSE_MASTER_FORM_LABLE_ROUTE_ID') }}
									</label>
									<DeliveryCourseCreation
										:items="listRouteMaster"
										:list-selected="listSelected"
										:is-edit="false"
										@add="handleAddDeliveryCourseCreation"
										@delete="handleDeleteDeliveryCourseCreation"
									/>
								</div>
							</div>
						</b-col>
					</b-row>
				</div>

				<div class="course-master-detail__handle">
					<b-row>
						<b-col cols="12" sm="12" md="12" lg="6" xl="6" class="text-left">
							<vButton
								:text-button="$t('BUTTON.BACK')"
								:class-name="'v-button-default'"
								@click.native="onClickBack()"
							/>
						</b-col>

						<b-col cols="12" sm="12" md="12" lg="6" xl="6" class="text-right">
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
								], role )"
							>
								<vButton
									:text-button="$t('BUTTON.EDIT')"
									:class-name="'v-button-default'"
									@click.native="onClickEdit()"
								/>
							</template>
						</b-col>
					</b-row>
				</div>
			</b-col>
		</div>
	</b-overlay>
</template>

<script>
import vHeaderPage from '@/components/atoms/vHeaderPage';
import TitleHeader from '@/components/atoms/vTitleHeader';
import DeliveryCourseCreation from '@/components/organisms/DeliveryCourseCreation';
import vButton from '@/components/atoms/vButton';

import { changeKey } from '@/utils/changeKey';

import CONST_ROLE from '@/const/role';
import { hasRole } from '@/utils/hasRole';

import {
    getCrouseType,
    getDeliveryType,
    getBinType,
    getGate,
    getWing,
    getKeyInList,
    getHMS,
} from '@/utils/getNameSelect';

const urlAPI = {
    getCourse: '/course',
    getListDepartment: '/department/list-all',
    getListRoute: '/course/selected/list-all',
};
import {
    getCourse,
    getListDepartment,
    getListRoute,
} from '@/api/modules/courseMaster';

export default {
    name: 'CourseMasterDetail',
    components: {
        TitleHeader,
        vHeaderPage,
        DeliveryCourseCreation,
        vButton,
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

            isForm: {
                base: null,
                course_id: '',
                course_address: '',
                start_date: '',
                end_date: '',
                course_flag: null,
                course_type: null,
                bin_type: null,
                delivery_type: null,
                quantity: '',
                route_start_time_hour: null,
                route_start_time_min: null,
                course_allowance: '',
                gate: null,
                wing: null,
                tonnage: null,
                shipper: null,
                delivery_store: null,
            },

            listBase: [{ value: null, text: this.$t('COURSE_MASTER_CREATE_PLEASE_SELECT') }],
            listCourseFlag: [
                { value: null, text: this.$t('COURSE_MASTER_CREATE_PLEASE_SELECT') },
                { value: 0, text: this.$t('COURSE_MASTER_FORM_LABLE_REGULAR_COURSE') },
                { value: 1, text: this.$t('COURSE_MASTER_FORM_LABLE_IRREGULAR_COURSE') },
            ],
            listCourseType: [{ value: null, text: this.$t('COURSE_MASTER_CREATE_PLEASE_SELECT') }],
            listFightType: [{ value: null, text: this.$t('COURSE_MASTER_CREATE_PLEASE_SELECT') }],
            listDeliveryType: [{ value: null, text: this.$t('COURSE_MASTER_CREATE_PLEASE_SELECT') }],
            listRouteStartTimeHour: [{ value: null, text: this.$t('COURSE_MASTER_CREATE_PLEASE_SELECT') }],
            listRouteStartTimeMin: [{ value: null, text: this.$t('COURSE_MASTER_CREATE_PLEASE_SELECT') }],
            listGate: [{ value: null, text: this.$t('COURSE_MASTER_CREATE_PLEASE_SELECT') }],
            listWing: [{ value: null, text: this.$t('COURSE_MASTER_CREATE_PLEASE_SELECT') }],
            listTonnage: [{ value: null, text: this.$t('COURSE_MASTER_CREATE_PLEASE_SELECT') }],
            listShipper: [{ value: null, text: this.$t('COURSE_MASTER_CREATE_PLEASE_SELECT') }],
            listDeliveryStore: [{ value: null, text: this.$t('COURSE_MASTER_CREATE_PLEASE_SELECT') }],

            listRouteMaster: [],
            listSelected: [],
        };
    },
    computed: {
        lang() {
            return this.$store.getters.language;
        },
        role() {
            return this.$store.getters.profile.roles;
        },
        id() {
            return this.$route.params.id;
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
            this.overlay.show = true;

            await this.handleGetListDepartment();
            this.handleGetListCourseType();
            this.handleGetListBinType();
            this.handleGetListDeliveryType();
            this.handleGetListCourseTime();
            this.handleGetListGate();
            this.handleGetListWing();
            this.handleGetListTonnage();
            await this.handleGetCourse();

            this.overlay.show = false;
        },
        async handleGetCourse() {
            try {
                this.overlay.show = true;

                const URL = `${urlAPI.getCourse}/${this.id}`;

                const response = await getCourse(URL);

                if (response.code === 200) {
                    const DATA = response.data;

                    this.isForm.base = DATA.department_id;
                    this.isForm.course_id = DATA.course_code;
                    this.isForm.course_address = DATA.address;
                    this.isForm.start_date = DATA.start_date;
                    this.isForm.end_date = DATA.end_date;
                    this.isForm.course_flag = DATA.course_flag;
                    this.isForm.course_type = DATA.course_type;
                    this.isForm.bin_type = DATA.bin_type;
                    this.isForm.delivery_type = DATA.delivery_type;
                    this.isForm.quantity = DATA.quantity;
                    this.isForm.route_start_time_hour = DATA.route_start_time_hour;
                    this.isForm.route_start_time_min = DATA.route_start_time_min;
                    this.isForm.course_allowance = this.formatNumberWithCommas(DATA.allowance);
                    this.isForm.gate = DATA.gate;
                    this.isForm.wing = DATA.wing;
                    this.isForm.tonnage = DATA.tonnage;

                    const TIME = getHMS(DATA.start_time);

                    this.isForm.route_start_time_hour = TIME.hour;
                    this.isForm.route_start_time_min = TIME.min;

                    await this.handleGetListRoute();

                    this.listSelected = getKeyInList(DATA.routes, 'id');
                    this.handleUpdateListDeliveryCourseCreation();
                }

                this.overlay.show = false;
            } catch (error) {
                this.overlay.show = false;

                const content = error.response?.data?.message || 'UNDEFINED_ERROR';

                try {
                    this.$toast.warning({ content });
                } catch (e) {
                    // JSDOM teardown / async sau khi test kết thúc
                }
            }
        },
        async handleGetListDepartment() {
            const KEY_ID = 'id';
            const KEY_DEPARTMENT_NAME = 'department_name';

            try {
                const URL = urlAPI.getListDepartment;

                const response = await getListDepartment(URL);

                if (response.code === 200) {
                    let DATA = response.data;
                    DATA = changeKey(DATA, KEY_ID, KEY_DEPARTMENT_NAME);

                    this.listBase = [...this.listBase, ...DATA];
                }
            } catch (error) {
                console.log(error);
            }
        },
        handleGetListCourseType() {
            const START_TYPE = 1;
            const END_TYPE = 9;

            for (let i = START_TYPE; i <= END_TYPE; i++) {
                this.listCourseType.push({
                    value: i,
                    text: this.$t(getCrouseType(i)),
                });
            }
        },
        handleGetListBinType() {
            const START_TYPE = 1;
            const END_TYPE = 3;

            for (let i = START_TYPE; i <= END_TYPE; i++) {
                this.listFightType.push({
                    value: i,
                    text: this.$t(getBinType(i)),
                });
            }
        },
        handleGetListDeliveryType() {
            const START_TYPE = 0;
            const END_TYPE = 2;

            for (let i = START_TYPE; i <= END_TYPE; i++) {
                this.listDeliveryType.push({
                    value: i,
                    text: this.$t(getDeliveryType(i)),
                });
            }
        },
        handleGetListCourseTime() {
            const START_HOUR = 0;
            const END_HOUR = 23;

            for (let i = START_HOUR; i <= END_HOUR; i++) {
                this.listRouteStartTimeHour.push({
                    value: i,
                    text: i + '',
                });
            }

            const LIST_MIN = [0, 10, 20, 30, 40, 50];

            for (let i = 0; i < LIST_MIN.length; i++) {
                this.listRouteStartTimeMin.push({
                    value: LIST_MIN[i],
                    text: LIST_MIN[i] + '',
                });
            }
        },
        handleGetListGate() {
            const START_TYPE = 0;
            const END_TYPE = 1;

            for (let i = START_TYPE; i <= END_TYPE; i++) {
                this.listGate.push({
                    value: i,
                    text: this.$t(getGate(i)),
                });
            }
        },
        handleGetListWing() {
            const START_TYPE = 0;
            const END_TYPE = 1;

            for (let i = START_TYPE; i <= END_TYPE; i++) {
                this.listWing.push({
                    value: i,
                    text: this.$t(getWing(i)),
                });
            }
        },
        handleGetListTonnage() {
            const LIST_TONNAGE = [2, 3, 4, 6, 10];

            for (let i = 0; i < LIST_TONNAGE.length; i++) {
                this.listTonnage.push({
                    value: LIST_TONNAGE[i],
                    text: LIST_TONNAGE[i],
                });
            }
        },
        async handleGetListRoute() {
            try {
                this.overlay.show = true;

                const URL = urlAPI.getListRoute;
                const PARAMS = {
                    department: this.isForm.base,
                };

                const response = await getListRoute(URL, PARAMS);

                let listRoute = [];

                listRoute = changeKey(response, 'id', 'name') || [];
                listRoute = this.formatNameRoute(listRoute) || [];

                this.listRouteMaster = listRoute;

                this.overlay.show = false;
            } catch (error) {
                this.overlay.show = false;
                console.log(error);
            }
        },
        formatNameRoute(list = []) {
            const len = list.length;
            let idx = 0;

            while (idx < len) {
                list[idx].text = `${list[idx].value} - ${list[idx].text} ${list[idx].remark ? '-' : ''} ${list[idx].remark || ''}`;

                idx++;
            }

            return list;
        },
        onClickBack() {
            this.$router.push({ name: 'CourseMaster' });
        },
        onClickEdit() {
            this.$router.push({ name: 'CourseMasterEdit', params: { id: this.id }});
        },
        handleAddDeliveryCourseCreation(id) {
            this.listSelected.push(id);
            this.handleUpdateListDeliveryCourseCreation();
        },
        handleDeleteDeliveryCourseCreation(id) {
            const idx = this.listSelected.indexOf(id);

            if (idx > -1) {
                this.listSelected.splice(idx, 1);
            }

            this.handleUpdateListDeliveryCourseCreation();
        },
        handleUpdateListDeliveryCourseCreation() {
            const len = this.listRouteMaster.length;
            let idx = 0;

            while (idx < len) {
                if (this.listSelected.includes(this.listRouteMaster[idx].value)) {
                    this.listRouteMaster[idx].disabled = true;
                } else {
                    this.listRouteMaster[idx].disabled = false;
                }

                idx++;
            }
        },
    },
};
</script>

<style lang="scss" scoped>
	@import "@/scss/variables.scss";

  ::v-deep .form-control:disabled {
    background-color: #e9ecef;
    opacity: .7 !important;
  }

  ::v-deep .b-form-btn-label-control.form-control[aria-disabled=true] {
    background-color: #e9ecef;
    opacity: .7 !important;
  }

	.text-overlay {
		margin-top: 10px;
	}

	.course-master-detail {
		overflow: hidden;
		min-height: calc(100vh - 89px);

		&__header,
		&__basic-information {
			margin-bottom: 20px;
		}

		&__basic-information {
			label {
				font-weight: bold;
			}
			.zone-form {
				.item-form {
					margin-bottom: 10px;
				}
			}
		}
	}
</style>
