<template>
	<div class="department-master-edit">
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
					<p style="margin-top: 10px">{{ $t('PLEASE_WAIT') }}</p>
				</div>
			</template>

			<div class="header">
				<vHeaderPage>{{ '拠点マスタ' }}</vHeaderPage>
			</div>

			<div class="department-master-edit-content">
				<div v-b-toggle="'basic-information'" class="d-flex flex-row align-items-center w-100">
					<span class="label-line" />
					<span style="text-wrap: nowrap;" class="label-text">基本情報</span>
					<span class="label-line" />
				</div>

				<b-collapse id="basic-information" v-model="basic_information_dropdown" class="mt-2">
					<div class="d-flex w-90 h-100 flex-column justify-content-center">
						<label for="name-input">拠点名</label>
						<b-form-input v-model="name" class="name-input" disabled />

						<label for="prefecture-input" class="mt-3">都道府県</label>
						<b-form-input v-model="prefecture" class="prefecture-input" disabled />

						<label for="post-code" class="mt-3">郵便番号</label>
						<b-form-input v-model="post_code" class="post-code" type="number" />

						<label for="address-input" class="mt-3">住所</label>
						<b-form-textarea v-model="address" placeholder="選択してください" rows="6" max-rows="20" class="address-input" />

						<!-- <label for="tel-number" class="mt-3">電話番号</label>
							<b-form-input v-model="tel" class="tel-number" type="number" /> -->
					</div>
					<div class="d-flex w-90 h-100 justify-content-between">
						<div class="mt-1 w-50 mr-5">
							<label for="tel">電話番号</label>
							<b-form-input
								v-model="tel"
								class="tel"
								@input="handleFormatPhoneNumber($event, 'tel')"
								@keypress="handlePhoneKeyPress"
							/>
						</div>
						<div class="mt-1 w-50">
							<label for="fax-number">FAX番号</label>
							<b-form-input
								v-model="fax_number"
								class="fax-number"
								@input="handleFormatPhoneNumber($event, 'fax_number')"
								@keypress="handlePhoneKeyPress"
							/>
						</div>
					</div>
					<!-- <b-row class="col-12">
						<b-col>
						<div class="mt-1">
						<label for="telephone_number">電話番号</label>
						<b-form-input
						v-model="telephone_number"
						class="telephone_number"
						@input="handleFormatPhoneNumber($event, 'telephone_number')"
						@keypress="handlePhoneKeyPress"
						/>
						</div>
						</b-col>
						<b-col>
						<div class="mt-1">
						<label for="fax-number">FAX番号</label>
						<b-form-input
						v-model="fax_number"
						class="fax-number"
						@input="handleFormatPhoneNumber($event, 'fax_number')"
						@keypress="handlePhoneKeyPress"
						/>
						</div>
						</b-col>
						</b-row> -->
				</b-collapse>

				<div v-b-toggle="'recruitment-information'" class="d-flex flex-row align-items-center w-100 mt-3">
					<span class="label-line" />
					<span style="text-wrap: nowrap;" class="label-text">採用関連情報</span>
					<span class="label-line" />
				</div>

				<b-collapse id="recruitment-information" class="mt-2">
					<label for="interview-address" class="mt-3">面接住所</label>
					<b-form-textarea v-model="interview_address" placeholder="選択してください" rows="6" max-rows="20" class="interview-address" />

					<label for="interview-address-url" class="mt-3">面接住所URL</label>
					<b-form-input v-model="interview_address_url" placeholder="選択してください" class="interview-address-url" />

					<label for="path-for-interview-address" class="mt-3">面接住所までの経路</label>
					<b-form-textarea v-model="path_for_interview_address" placeholder="選択してください" rows="6" max-rows="20" class="path-for-interview-address" />

					<label for="interview-pic" class="mt-3">採用担当者</label>
					<vMultiselect
						v-model="interview_pic"
						label="name_code"
						track-by="code"
						:searchable="true"
						:show-labels="false"
						:placeholder="'選択してください'"
						open-direction="top"
						:close-on-select="true"
						:options="search_results"
					>
						<template slot="noResult">
							<span>要素が見つかりませんでした。検索クエリを変更することを検討してください。</span>
						</template>

						<template slot="noOptions">
							<span>リストは空です。</span>
						</template>
					</vMultiselect>

					<label for="line-work-pic" class="mt-3">Line Works アカウント名</label>
					<vMultiselect
						v-model="line_work_pic"
						label="full_name"
						track-by="code"
						:searchable="true"
						:show-labels="false"
						:placeholder="'選択してください'"
						open-direction="top"
						:close-on-select="false"
						:multiple="true"
						:options="list_line_work_pic"
					>
						<template slot="noResult">
							<span>要素が見つかりませんでした。検索クエリを変更することを検討してください。</span>
						</template>

						<template slot="noOptions">
							<span>リストは空です。</span>
						</template>
					</vMultiselect>
				</b-collapse>

				<!-- 施設情報グループ -->
				<div v-b-toggle="'facility-information'" class="d-flex flex-row align-items-center w-100 mt-3">
					<span class="label-line" />
					<span style="text-wrap: nowrap;" class="label-text">施設情報</span>
					<span class="label-line" />
				</div>

				<b-collapse id="facility-information" v-model="facility_information_dropdown" class="mt-2">
					<div class="d-flex w-90 h-100 flex-column justify-content-center">

						<b-row class="col-12">
							<b-col>
								<div class="mt-3">
									<label for="name-sales" class="font-label">営業所</label>
								</div>
							</b-col>
						</b-row>
						<b-row class="col-12">
							<b-col>
								<div class="mt-1">
									<label for="name-sales">名称</label>
									<b-form-input v-model="name_sales" class="name-sales" />
								</div>
							</b-col>
							<b-col>
								<div class="mt-1">
									<label for="location-input">位置</label>
									<b-form-input v-model="location" class="location-input" />
								</div>
							</b-col>
						</b-row>

						<b-row class="col-12">
							<b-col>
								<div class="mt-4">
									<label for="name-sales" class="font-label">事務室</label>
								</div>
								<div class="mt-1">
									<label for="area">面積(㎡)</label>
									<b-form-input
										type="text"
										v-model="area_office"
										class="area"
										@keypress="handleAreaKeyPress"
										@blur="handleAreaInput('area_office', $event)"
									/>
								</div>
							</b-col>
							<b-col>
								<div class="mt-4">
									<label for="name-sales" class="font-label">休憩室</label>
								</div>
								<div class="mt-1">
									<label for="area-break-room">面積(㎡)</label>
									<b-form-input
										type="text"
										v-model="name_break_room"
										class="area-break-room"
										@keypress="handleAreaKeyPress"
										@blur="handleAreaInput('name_break_room', $event)"
									/>
								</div>
							</b-col>
						</b-row>

						<b-row class="col-12">
							<b-col>
								<div class="mt-4">
									<label for="name-sales" class="font-label">車庫①</label>
								</div>
								<div class="mt-1">
									<label for="position">位置</label>
									<b-form-input v-model="position" class="position" />
								</div>

								<div class="mt-1">
									<label for="area-garage">面積(㎡)</label>
									<b-form-input
										type="text"
										v-model="area_garage"
										class="area-garage"
										@keypress="handleAreaKeyPress"
										@blur="handleAreaInput('area_garage', $event)"
									/>
								</div>
							</b-col>
							<b-col>
								<div class="mt-4">
									<label for="name-sales" class="font-label">車庫②</label>
								</div>
								<div class="mt-1">
									<label for="position-last">位置</label>
									<b-form-input v-model="position_last" class="position-last" />
								</div>

								<div class="mt-1">
									<label for="area-garage-last">面積(㎡)</label>
									<b-form-input
										type="text"
										v-model="area_garage_last"
										class="area-garage-last"
										@keypress="handleAreaKeyPress"
										@blur="handleAreaInput('area_garage_last', $event)"
									/>
								</div>
							</b-col>
						</b-row>
					</div>
				</b-collapse>

				<!-- 管理・認証情報グループ -->
				<div v-b-toggle="'management-certification'" class="d-flex flex-row align-items-center w-100 mt-3">
					<span class="label-line" />
					<span style="text-wrap: nowrap;" class="label-text">管理・認証情報</span>
					<span class="label-line" />
				</div>

				<b-collapse id="management-certification" v-model="management_certification_dropdown" class="mt-2">
					<div class="d-flex w-90 h-100 flex-column justify-content-center">

						<b-row class="col-12">
							<b-col>
								<div class="mt-3">
									<label for="name-sales" class="font-label">統括運行管理者</label>
								</div>
							</b-col>
						</b-row>
						<b-row class="col-12">
							<b-col>
								<div class="mt-1">
									<label for="general-operations-manager">選任</label>
									<vMultiselect
										v-model="general_operations_manager"
										label="name_code"
										track-by="code"
										:searchable="true"
										:show-labels="false"
										:placeholder="'選択してください'"
										open-direction="top"
										:close-on-select="true"
										:options="appointment"
									>
										<template slot="noResult">
											<span>要素が見つかりませんでした。検索クエリを変更することを検討してください。</span>
										</template>

										<template slot="noOptions">
											<span>リストは空です。</span>
										</template>
									</vMultiselect>
								</div>
							</b-col>
						</b-row>
						<b-row class="col-12">
							<b-col>
								<div class="mt-3">
									<label for="name-sales" class="font-label">運行管理者</label>
								</div>
							</b-col>
						</b-row>
						<b-row class="col-12">
							<b-col>
								<div class="mt-1">
									<label for="appointment">選任</label>
									<!-- <b-form-input v-model="appointment" class="appointment" /> -->

									<vMultiselect
										v-model="appointment"
										label="name_code"
										track-by="code"
										:searchable="true"
										:show-labels="false"
										:placeholder="'選択してください'"
										open-direction="top"
										:close-on-select="true"
										:multiple="true"
										:options="search_management_certification"
										@input="handleAppointmentInput"
									>
										<template slot="noResult">
											<span>要素が見つかりませんでした。検索クエリを変更することを検討してください。</span>
										</template>

										<template slot="noOptions">
											<span>リストは空です。</span>
										</template>
									</vMultiselect>
								</div>
							</b-col>
						</b-row>
						<b-row class="col-12">
							<b-col>
								<div class="mt-1">
									<label for="assistant">補助者</label>
									<vMultiselect
										v-model="assistant"
										label="name_code"
										track-by="code"
										:searchable="true"
										:show-labels="false"
										:placeholder="'選択してください'"
										open-direction="top"
										:close-on-select="true"
										:multiple="true"
										:options="search_assistant"
										@input="handleAssistantInput"
									>
										<template slot="noResult">
											<span>要素が見つかりませんでした。検索クエリを変更することを検討してください。</span>
										</template>

										<template slot="noOptions">
											<span>リストは空です。</span>
										</template>
									</vMultiselect>
								</div>
							</b-col>
						</b-row>

						<b-row class="col-12">
							<b-col>
								<div class="mt-4">
									<label for="name-sales" class="font-label">整備管理者</label>
								</div>
							</b-col>
						</b-row>

						<b-row class="col-12">
							<b-col>
								<div class="mt-1">
									<label for="appointment_maintenance">選任</label>
									<!-- <b-form-input v-model="appointment_maintenance" class="appointment_maintenance" /> -->
									<vMultiselect
										v-model="appointment_maintenance"
										label="name_code"
										track-by="code"
										:searchable="true"
										:show-labels="false"
										:placeholder="'選択してください'"
										open-direction="top"
										:close-on-select="true"
										:multiple="true"
										:options="search_appointment_maintenance"
										@input="handleAppointmentMaintenanceInput"
									>
										<template slot="noResult">
											<span>要素が見つかりませんでした。検索クエリを変更することを検討してください。</span>
										</template>

										<template slot="noOptions">
											<span>リストは空です。</span>
										</template>
									</vMultiselect>
								</div>
							</b-col>
						</b-row>

						<b-row class="col-12">
							<b-col>
								<div class="mt-1">
									<label for="assistant-maintenace">補助者</label>
									<!-- <b-form-input v-model="assistant_maintenace" class="assistant-maintenace" /> -->
									<vMultiselect
										v-model="assistant_maintenace"
										label="name_code"
										track-by="code"
										:searchable="true"
										:show-labels="false"
										:placeholder="'選択してください'"
										open-direction="top"
										:close-on-select="true"
										:multiple="true"
										:options="search_assistant_maintenace"
										@input="handleAssistantMaintenanceInput"
									>
										<template slot="noResult">
											<span>要素が見つかりませんでした。検索クエリを変更することを検討してください。</span>
										</template>

										<template slot="noOptions">
											<span>リストは空です。</span>
										</template>
									</vMultiselect>
								</div>
							</b-col>
						</b-row>

						<!-- <b-row class="col-12">
							<b-col>
							<div class="mt-1">
							<label for="telephone_number">電話番号</label>
							<b-form-input
							v-model="telephone_number"
							class="telephone_number"
							@input="handleFormatPhoneNumber($event, 'telephone_number')"
							@keypress="handlePhoneKeyPress"
							/>
							</div>
							</b-col>
							<b-col>
							<div class="mt-1">
							<label for="fax-number">FAX番号</label>
							<b-form-input
							v-model="fax_number"
							class="fax-number"
							@input="handleFormatPhoneNumber($event, 'fax_number')"
							@keypress="handlePhoneKeyPress"
							/>
							</div>
							</b-col>
							</b-row> -->

						<b-row class="col-12">
							<b-col>
								<div class="mt-4">
									<label for="name-sales" class="font-label">トラック協会</label>
								</div>
							</b-col>
						</b-row>
						<b-row class="col-12">
							<b-col>
								<div class="mt-1">
									<label for="member-no">会員No.</label>
									<b-form-input v-model="member_no" class="member-no" />
								</div>
							</b-col>
						</b-row>

						<b-row class="col-12">
							<b-col>
								<div class="mt-4">
									<label for="name-sales" class="font-label">Ｇマーク</label>
								</div>
							</b-col>
						</b-row>
						<b-row class="col-12">
							<b-col>
								<div class="mt-1">
									<label for="number-mark">番号</label>
									<b-form-input v-model="number_mark" class="number-mark" @keypress="handleGMarkNumberKeyPress" />
								</div>
							</b-col>
							<b-col>
								<div class="mt-1">
									<label for="expiry-date">期限</label>
									<b-input v-model="expiry_date" type="date" class="expiry-date" />
								</div>
							</b-col>
						</b-row>

						<b-row class="col-12">
							<b-col>
								<div class="mt-4">
									<label for="name-sales" class="font-label">遠隔点呼</label>
								</div>
							</b-col>
						</b-row>

						<b-row class="col-6">
							<b-col>
								<div class="mt-2">
									<!-- <label for="number-roll-call"></label> -->
									<b-row>
										<b-col>
											<b-form-radio-group
												v-model="roll_call_radio"
												:options="options_roll_call"
												class="mb-3"
												value-field="item"
												text-field="name"
												disabled-field="notEnabled"
											/>
										</b-col>
										<b-col>
											<b-form-checkbox-group
												v-model="select_g_mark"
												:options="options_g_mark"
												class="mb-3"
												value-field="item"
												text-field="name"
												disabled-field="notEnabled"
											/>
										</b-col>
									</b-row>
								</div>
							</b-col>
						</b-row>

						<!-- <div class="mt-3">
							<label for="name-sales" class="font-label">Ｇマーク</label>
							</div> -->

					</div>
				</b-collapse>

				<div class="d-flex flex-row w-100 footer-control">
					<div class="d-flex w-100 h-100 justify-content-start align-items-center">
						<b-button class="back-button" @click="handleNavigateToListScreen()">
							<span>戻る</span>
						</b-button>
					</div>

					<div class="d-flex w-100 h-100 justify-content-end align-items-center">
						<b-button class="save-button" @click="handleSaveDepartmentInfo()">
							<span>保存</span>
						</b-button>
					</div>
				</div>
			</div>
		</b-overlay>
	</div>
</template>

<script>
import vMultiselect from 'vue-multiselect';
import vHeaderPage from '@/components/atoms/vHeaderPage.vue';

import { getOneDepartment, putDepartment, searchUser, getLineWorkPIC, getEmployeeAll } from '@/api/modules/department_master';

const urlAPI = {
    apiGetOneDepartment: '/department',
    apiUpdateDepartment: '/department',
    apiSearchUser: '/user-interview-pic',
    apiGetLineWork: '/line-works-list-pic',
    apiGetAll: '/employee/all',
};

export default {
    name: 'DepartmentMasterEdit',
    components: {
        vHeaderPage,
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

            name: '',
            prefecture: '',
            post_code: '',
            address: '',
            tel: '',

            interview_address: '',
            interview_address_url: '',
            interview_pic: null,
            path_for_interview_address: '',

            line_work_pic: [],
            list_line_work_pic: [],

            search_results: [],
            search_management_certification: [],
            search_assistant: [],
            search_appointment_maintenance: [],
            search_assistant_maintenace: [],

            basic_information_dropdown: true,
            facility_information_dropdown: false,
            management_certification_dropdown: false,
            sales_office_dropdown: false,
            office_dropdown: false,
            break_room_dropdown: false,
            garage_dropdown: false,
            garage_last_dropdown: false,
            operation_manager_dropdown: false,
            maintenance_manager_dropdown: false,
            truck_association_dropdown: false,
            g_mark_dropdown: false,
            roll_call_dropdown: false,

            name_sales: '',
            location: '',
            area_office: '',
            name_break_room: '',
            position: '',
            area_garage: '',
            position_last: '',
            area_garage_last: '',
            general_operations_manager: [],
            appointment: [],
            assistant: [],
            appointment_maintenance: [],
            assistant_maintenace: [],
            telephone_number: '',
            fax_number: '',
            member_no: '',
            number_mark: '',
            expiry_date: null,
            number_roll_call: '',

            roll_call_radio: null,
            options_roll_call: [
                { name: '可', item: 1 },
                { name: '否', item: 2 },
            ],
            select_g_mark: [],
            options_g_mark: [
                { name: '実施', item: 1 },
                { name: '受け', item: 2 },
            ],

            MAX_APPOINTMENT: 5,
        };
    },
    created() {
        this.handleInitData();
    },
    methods: {
        async handleInitData() {
            this.overlay.show = true;

            await this.handleSearchUser();
            await this.handleGetLineWorkPIC();
            await this.handleGetAllEmpleyee();
            await this.handleGetDepartmentInfo();

            this.overlay.show = false;
        },

        handleAppointmentInput(val) {
            if (Array.isArray(val) && val.length > this.MAX_APPOINTMENT) {
                console.log('val.slice', val.slice(0, this.MAX_APPOINTMENT));
                this.appointment = val.slice(0, this.MAX_APPOINTMENT);
                this.$toast.warning({
                    content: `選択できるのは最大 ${this.MAX_APPOINTMENT} 人までです。`,
                });
                return;
            }
            this.appointment = val;
        },

        handleAssistantInput(val) {
            if (Array.isArray(val) && val.length > this.MAX_APPOINTMENT) {
                console.log('val.slice', val.slice(0, this.MAX_APPOINTMENT));
                this.assistant = val.slice(0, this.MAX_APPOINTMENT);
                this.$toast.warning({
                    content: `選択できるのは最大 ${this.MAX_APPOINTMENT} 人までです。`,
                });
                return;
            }
            this.assistant = val;
        },

        handleAppointmentMaintenanceInput(val) {
            if (Array.isArray(val) && val.length > this.MAX_APPOINTMENT) {
                console.log('val.slice', val.slice(0, this.MAX_APPOINTMENT));
                this.appointment_maintenance = val.slice(0, this.MAX_APPOINTMENT);
                this.$toast.warning({
                    content: `選択できるのは最大 ${this.MAX_APPOINTMENT} 人までです。`,
                });
                return;
            }
            this.appointment_maintenance = val;
        },

        handleAssistantMaintenanceInput(val) {
            if (Array.isArray(val) && val.length > this.MAX_APPOINTMENT) {
                console.log('val.slice', val.slice(0, this.MAX_APPOINTMENT));
                this.assistant_maintenace = val.slice(0, this.MAX_APPOINTMENT);
                this.$toast.warning({
                    content: `選択できるのは最大 ${this.MAX_APPOINTMENT} 人までです。`,
                });
                return;
            }
            this.assistant_maintenace = val;
        },

        async handleGetAllEmpleyee() {
            try {
                const url = `${urlAPI.apiGetAll}`;
                const response = await getEmployeeAll(url);
                if (response['code'] === 200) {
                    this.search_management_certification = response['data'].map(item => {
                        return {
                            code: `${item.id}`,
                            name: item.name,
                            name_code: item.name_code || item.name,
                        };
                    });
                    this.search_assistant = response['data'].map(item => {
                        return {
                            code: `${item.id}`,
                            name: item.name,
                            name_code: item.name_code || item.name,
                        };
                    });
                    this.search_appointment_maintenance = response['data'].map(item => {
                        return {
                            code: `${item.id}`,
                            name: item.name,
                            name_code: item.name_code || item.name,
                        };
                    });
                    this.search_assistant_maintenace = response['data'].map(item => {
                        return {
                            code: `${item.id}`,
                            name: item.name,
                            name_code: item.name_code || item.name,
                        };
                    });
                    console.log('this.search_management_certification', this.search_management_certification);
                } else {
                    this.$toast.warning({
                        content: response['message'] || 'Server error.',
                    });
                }
            } catch (error) {
                console.log(error);
            }
        },
        async handleGetLineWorkPIC() {
            try {
                const url = `${urlAPI.apiGetLineWork}`;

                const response = await getLineWorkPIC(url);

                if (response['code'] === 200) {
                    this.list_line_work_pic = response['data'];
                } else {
                    this.$toast.warning({
                        content: response['message'] || 'Server error.',
                    });
                }
            } catch (error) {
                console.log(error);

                this.$toast.error({
                    content: error.response?.data?.message || error.message || 'Server error.',
                });
            }
        },
        async handleSearchUser() {
            try {
                const URL = `${urlAPI.apiSearchUser}?search=${this.search_results}`;

                const response = await searchUser(URL);

                if (response['code'] === 200) {
                    this.search_results = response['data'];
                } else {
                    this.$toast.warning({
                        content: response['message'] || 'Server error.',
                    });
                }
            } catch (error) {
                console.log('[ERROR]', error);

                this.$toast.warning({
                    content: error.response?.data?.message || error.message || 'Server error.',
                });
            }
        },
        async handleGetDepartmentInfo() {
            const DEPARTMENT_ID = this.$route.params.id;

            if (DEPARTMENT_ID) {
                this.overlay.show = true;

                try {
                    const URL = `${urlAPI.apiGetOneDepartment}/${DEPARTMENT_ID}`;

                    const PARAMS = [];

                    const response = await getOneDepartment(URL, PARAMS);

                    if (response['code'] === 200) {
                        const DATA = response['data'];

                        this.name = DATA['name'];
                        this.prefecture = DATA['province_name'];
                        this.post_code = DATA['post_code'];
                        this.address = DATA['address'];
                        this.tel = DATA['tel'];

                        this.interview_address = DATA['interview_address'];
                        this.interview_address_url = DATA['interview_address_url'];
                        this.path_for_interview_address = DATA['path_for_interview_address'];

                        this.name_sales = DATA['office_name'];
                        this.location = DATA['office_location'];
                        this.area_office = DATA['office_area'];
                        this.name_break_room = DATA['rest_room_area'];
                        this.position = DATA['garage_location_1'];
                        this.area_garage = DATA['garage_area_1'];
                        this.position_last = DATA['garage_location_2'];
                        this.area_garage_last = DATA['garage_area_2'];

                        // Map appointment (single select)
                        if (DATA['operations_manager_appointment'] && Array.isArray(DATA['operations_manager_appointment'])) {
                            // this.appointment = this.search_management_certification.find((item) => item['code'] === DATA['operations_manager_appointment']) || null;
                            this.appointment = this.search_management_certification.map((id, index) => {
                                return this.search_management_certification.find((item) => item['code'] === DATA['operations_manager_appointment'][index]) || null;
                            }).filter(item => item !== null);
                        } else if (DATA['operations_manager_appointment']){
                            const found = this.appointment.find((item) => item['code'] === DATA['operations_manager_assistant']);
                            this.appointment = found ? [found] : [];
                        } else {
                            this.appointment = [];
                        }
                        console.log('appointment', this.appointment);

                        // Map general_operations_manager (multiple select, options from appointment list)
                        this.general_operations_manager = this.appointment.find((item) => item['code'] === DATA['chief_operations_manager']) || null;
                        console.log('general_operations_manager', this.general_operations_manager);

                        // Map assistant (multiple select)
                        if (DATA['operations_manager_assistant'] && Array.isArray(DATA['operations_manager_assistant'])) {
                            // this.assistant = this.search_assistant.find((item) => item['code'] === DATA['operations_manager_assistant']) || null;
                            this.assistant = this.search_assistant.map((id, index) => {
                                return this.search_assistant.find((item) => item['code'] === DATA['operations_manager_assistant'][index]) || null;
                            }).filter(item => item !== null);
                        } else if (DATA['operations_manager_assistant']){
                            const found = this.assistant.find((item) => item['code'] === DATA['operations_manager_assistant']);
                            this.assistant = found ? [found] : [];
                        } else {
                            this.assistant = [];
                        }
                        // Map appointment_maintenance (single select)
                        if (DATA['maintenance_manager_appointment'] && Array.isArray(DATA['maintenance_manager_appointment'])) {
                            // this.appointment_maintenance = this.search_appointment_maintenance.find((item) => item['code'] === DATA['maintenance_manager_appointment']) || null;
                            this.appointment_maintenance = this.search_appointment_maintenance.map((id, index) => {
                                return this.search_appointment_maintenance.find((item) => item['code'] === DATA['maintenance_manager_appointment'][index]) || null;
                            }).filter(item => item !== null);
                        } else if (DATA['maintenance_manager_appointment']){
                            const found = this.appointment_maintenance.find((item) => item['code'] === DATA['maintenance_manager_appointment']);
                            this.appointment_maintenance = found ? [found] : [];
                        } else {
                            this.appointment_maintenance = [];
                        }

                        // Map assistant_maintenace (single select based on template)
                        if (DATA['maintenance_manager_assistant'] && Array.isArray(DATA['maintenance_manager_assistant'])) {
                            this.assistant_maintenace = this.search_assistant_maintenace.map((id, index) => {
                                return this.search_assistant_maintenace.find((item) => item['code'] === DATA['maintenance_manager_assistant'][index]) || null;
                            }).filter(item => item !== null);
                        } else if (DATA['maintenance_manager_assistant']){
                            const found = this.assistant_maintenace.find((item) => item['code'] === DATA['maintenance_manager_assistant']);
                            this.assistant_maintenace = found ? [found] : [];
                        } else {
                            this.assistant_maintenace = [];
                        }

                        this.telephone_number = DATA['maintenance_manager_phone_number'];
                        this.fax_number = DATA['maintenance_manager_fax_number'];
                        this.member_no = DATA['truck_association_membership_number'];
                        this.number_mark = DATA['g_mark_number'];
                        this.expiry_date = DATA['g_mark_expiration_date'];
                        // this.number_roll_call = DATA['name_sales'];
                        this.roll_call_radio = DATA['it_roll_call'];

                        const gMarkAction = DATA['g_mark_action_radio'];
                        this.select_g_mark = Array.isArray(gMarkAction) ? gMarkAction : [];

                        const inforInterviewPIC = [];

                        if (DATA['interview_pic_line_work'] && DATA['interview_pic_line_work'].length > 0) {
                            DATA['interview_pic_line_work'].forEach((item) => {
                                this.list_line_work_pic.forEach((subItem) => {
                                    if (item === subItem.code) {
                                        inforInterviewPIC.push(subItem);
                                    }
                                });
                            });
                        }

                        console.log(inforInterviewPIC);

                        this.interview_pic = this.search_results.find((item) => item['code'] === DATA['interview_pic']) || null;
                        this.line_work_pic = inforInterviewPIC;
                    } else {
                        this.$toast.warning({
                            content: response['message'],
                        });
                    }
                } catch (error) {
                    console.log('[ERROR]', error);
                }

                this.overlay.show = false;
            }
        },
        handleNavigateToListScreen() {
            this.$router.push({ path: '/master-manager/department-master/index' });
        },
        async handleSaveDepartmentInfo() {
            const DEPARTMENT_ID = this.$route.params.id;

            if (DEPARTMENT_ID) {
                this.overlay.show = true;

                if (this.handleVailidateFormData()) {
                    try {
                        const URL = `${urlAPI.apiUpdateDepartment}/${DEPARTMENT_ID}`;

                        const interviewLineworkPIC = [];

                        if (this.line_work_pic.length > 0) {
                            this.line_work_pic.forEach(element => {
                                interviewLineworkPIC.push(element['code']);
                            });
                        }

                        console.log('this.select_g_mark', this.select_g_mark);

                        const DATA = {
                            post_code: this.post_code,
                            address: this.address,
                            tel: this.tel,
                            interview_address: this.interview_address,
                            interview_address_url: this.interview_address_url,
                            interview_pic: this.interview_pic ? this.interview_pic['code'] : '',
                            path_for_interview_address: this.path_for_interview_address,
                            interview_pic_line_work: interviewLineworkPIC,

                            office_name: this.name_sales,
                            office_location: this.location,
                            office_area: this.area_office,
                            rest_room_area: this.name_break_room,
                            garage_location_1: this.position,
                            garage_area_1: this.area_garage,
                            garage_location_2: this.position_last,
                            garage_area_2: this.area_garage_last,
                            chief_operations_manager: this.general_operations_manager ? this.general_operations_manager['code'] : '',
                            operations_manager_appointment: Array.isArray(this.appointment) ? this.appointment.map(item => item ? item['code'] : null).filter(code => code !== null) : (this.appointment ? [this.appointment['code']] : []),
                            operations_manager_assistant: Array.isArray(this.assistant) ? this.assistant.map(item => item ? item['code'] : null).filter(code => code !== null) : (this.assistant ? [this.assistant['code']] : []),
                            maintenance_manager_appointment: Array.isArray(this.appointment_maintenance) ? this.appointment_maintenance.map(item => item ? item['code'] : null).filter(code => code !== null) : (this.appointment_maintenance ? [this.appointment_maintenance['code']] : []),
                            maintenance_manager_assistant: Array.isArray(this.assistant_maintenace) ? this.assistant_maintenace.map(item => item ? item['code'] : null).filter(code => code !== null) : (this.assistant_maintenace ? [this.assistant_maintenace['code']] : []),
                            maintenance_manager_phone_number: this.telephone_number,
                            maintenance_manager_fax_number: this.fax_number,
                            truck_association_membership_number: this.member_no,
                            g_mark_number: this.number_mark,
                            g_mark_expiration_date: this.expiry_date,
                            it_roll_call: this.roll_call_radio,
                            g_mark_action_radio: this.select_g_mark,
                        };

                        const response = await putDepartment(URL, DATA);

                        if (response['code'] === 200) {
                            this.overlay.show = false;

                            this.$toast.success({
                                content: '拠点データを更新しました',
                            });

                            this.handleNavigateToListScreen();
                        } else {
                            this.$toast.warning({
                                content: response['message'],
                            });
                        }
                    } catch (error) {
                        console.log('[ERROR]', error);
                    }
                }

                this.overlay.show = false;
            }
        },
        handleFormatPhoneNumber(event, fieldName) {
            let value = event.target ? event.target.value : event;

            value = value.replace(/[^0-9-]/g, '');

            const numbers = value.replace(/-/g, '');

            let formatted = '';
            if (numbers.length > 0) {
                formatted = numbers.substring(0, 3);
                if (numbers.length > 3) {
                    formatted += '-' + numbers.substring(3, 6);
                }
                if (numbers.length > 6) {
                    formatted += '-' + numbers.substring(6, 10);
                }
            }

            this.$nextTick(() => {
                this[fieldName] = formatted;
            });
        },
        handlePhoneKeyPress(event) {
            const char = String.fromCharCode(event.which);
            const allowedChars = /[0-9-]/;
            const controlKeys = [8, 9, 27, 13, 46, 37, 38, 39, 40]; // Backspace, Tab, Esc, Enter, Delete, Arrow keys

            if (!controlKeys.includes(event.which) && !allowedChars.test(char)) {
                event.preventDefault();
                return false;
            }
        },
        handleGMarkNumberKeyPress(event) {
            const charCode = event.which || event.keyCode;
            const char = String.fromCharCode(charCode);
            const controlKeys = [8, 9, 27, 13, 46, 37, 38, 39, 40]; // Backspace, Tab, Esc, Enter, Delete, Arrow keys

            // Cho phép các phím điều hướng / control
            if (controlKeys.includes(charCode)) {
                return;
            }

            // Chặn chỉ các ký tự chữ cái (a-z, A-Z), cho phép số và ký tự đặc biệt
            const isLetter = /[A-Za-z]/.test(char);
            if (isLetter) {
                event.preventDefault();
                return false;
            }
        },
        handleAreaKeyPress(event) {
            const charCode = event.which || event.keyCode;
            const char = String.fromCharCode(charCode);
            const controlKeys = [8, 9, 27, 13, 46, 37, 38, 39, 40]; // Backspace, Tab, Esc, Enter, Delete, Arrow keys

            // Cho phép các phím điều hướng / control
            if (controlKeys.includes(charCode)) {
                return;
            }

            // Chỉ xử lý số, dấu chấm và dấu phẩy, chặn các ký tự khác ngay lập tức
            if (!/[0-9.,]/.test(char)) {
                event.preventDefault();
                return false;
            }

            const input = event.target;
            const value = input.value || '';
            const selectionStart = input.selectionStart != null ? input.selectionStart : value.length;
            const selectionEnd = input.selectionEnd != null ? input.selectionEnd : value.length;

            const currentValueNoCommas = value.replace(/,/g, '');

            // Không cho phép nhập thêm dấu chấm nếu trong value (bỏ qua dấu phẩy) đã có dấu chấm
            if (char === '.' && currentValueNoCommas.includes('.')) {
                event.preventDefault();
                return false;
            }

            // Giá trị giả định sau khi gõ ký tự này (có tính tới selection)
            const nextValue = value.slice(0, selectionStart) + char + value.slice(selectionEnd);
            const nextValueNoCommas = nextValue.replace(/,/g, '');

            // Không cho phép bắt đầu bằng dấu chấm
            if (nextValueNoCommas.startsWith('.')) {
                event.preventDefault();
                return false;
            }

            // Regex: số nguyên hoặc số thập phân với tối đa 2 chữ số sau dấu chấm
            const pattern = /^\d+(\.\d{0,2})?$/;

            if (!pattern.test(nextValueNoCommas)) {
                // Ví dụ: 123.23.45, 123.32456, hoặc nhiều dấu chấm, hoặc >2 số thập phân
                event.preventDefault();
                return false;
            }
        },
        handleAreaInput(fieldName, event) {
            let value = event && event.target ? event.target.value : event;

            if (typeof value !== 'string') {
                value = value != null ? String(value) : '';
            }

            // Chỉ giữ lại số, dấu chấm và dấu phẩy
            value = value.replace(/[^0-9.,]/g, '');

            // Loại bỏ tất cả dấu phẩy để kiểm tra định dạng số
            const noCommas = value.replace(/,/g, '');

            if (noCommas === '') {
                this[fieldName] = '';
                return;
            }

            // Kiểm tra: chỉ 1 dấu chấm, tối đa 2 số sau dấu chấm
            const pattern = /^(\d+)(\.\d{0,2})?$/;
            const match = noCommas.match(pattern);

            if (!match) {
                // Nếu không khớp pattern, quay lại giá trị hiện tại (đã format) trong model
                event.target.value = this[fieldName] || '';
                return;
            }

            const intPart = match[1];
            const decimalPart = match[2] || '';

            // Thêm dấu phẩy phân tách hàng nghìn cho phần nguyên
            const intWithCommas = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            const formatted = intWithCommas + decimalPart;

            this.$nextTick(() => {
                this[fieldName] = formatted;
            });
        },
        handleVailidateFormData() {
            let result = false;

            if (this.interview_address === null) {
                this.$toast.warning({
                    content: '面接住所を入力してください',
                });
            } else if (this.interview_address.length > 1000) {
                this.$toast.warning({
                    content: '面接住所は1000以内で入力してください',
                });
            } else if (this.interview_address_url === null) {
                this.$toast.warning({
                    content: '面接住所URLを入力してください',
                });
            } else if (this.path_for_interview_address && this.path_for_interview_address.length > 1000) {
                this.$toast.warning({
                    content: '面接住所までの経路は1000字以内で入力してください',
                });
            } else if (this.interview_pic === null) {
                this.$toast.warning({
                    content: '採用担当者を入力してください',
                });
            } else {
                result = true;
            }

            return result;
        },
    },
};
</script>

<style src="vue-multiselect/dist/vue-multiselect.min.css" />

<style lang="scss" scoped>
.department-master-edit-content {
  margin: 40px 5% 0px 5%;

  label {
    /* color: #a5a5a5; */
    font-size: 18px;
    /* font-weight: 500; */
  }

  .font-label {
    font-weight: 600;
  }

  .label-text {
    color: #018EAD;
    font-size: 22px;
    font-weight: 700;
    word-wrap: normal;
    margin-left: 10px;
    margin-right: 10px;
  }

  .label-line:nth-child(1) {
    width: 50px;
    height: 2px;
    background-color: #018EAD;
  }

  .label-line {
    width: 100%;
    height: 2px;
    background-color: #018EAD;
  }

  .footer-control {
    margin-top: 50px;
  }

  .back-button {
    height: 36px;
    width: 150px;
    outline: none;
    color: #FFFFFF;
    background-color: #FF8A1F;

    &:hover {
      opacity: .6;
      background-color: #FF8A1F;
    }
  }

  .save-button {
    height: 36px;
    width: 150px;
    outline: none;
    color: #FFFFFF;
    background-color: #FF8A1F;

    &:hover {
      opacity: .6;
      background-color: #FF8A1F;
    }
  }
}

@media screen and (max-width: 667px) {
  .back-button {
    width: 135px !important;
  }

  .save-button {
    width: 135px !important;
  }
}
</style>
