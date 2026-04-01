<!-- eslint-disable vue/html-indent -->
<template>
    <b-overlay :show="overlay.show" :variant="overlay.variant" :opacity="overlay.opacity" :blur="overlay.blur" :rounded="overlay.sm">
        <template #overlay>
            <div class="text-center">
                <b-icon icon="arrow-clockwise" font-scale="3" animation="spin" />
                <p style="margin-top: 10px;">{{ $t('PLEASE_WAIT') }}</p>
            </div>
        </template>

        <div class="main-content">
            <div class="vehicle-master__header mb-3">
                <vHeaderPage>
                    {{ $t("ROUTER_VEHICLE_MASTER") }}
                </vHeaderPage>
            </div>

            <div class="tab-navigator">
                <b-row class="tabs-holder w-100">
                    <b-col :class="[currentTab === 1 ? 'tab-active' : '', 'tab']" @click="handleChangeCurrentTab(1)">
                        <span class="tab-text">{{ $t('VEHICLE_MASTER.BASIC_INFORMATION.TITLE') }}</span>
                    </b-col>

                    <b-col :class="[currentTab === 2 ? 'tab-active' : '', 'tab']" @click="handleChangeCurrentTab(2)">
                        <span class="tab-text">{{ $t('VEHICLE_MASTER.COMPULSORY_AUTOMOBILE_LIABILITY_INSURANCE.TITLE')
                        }}</span>
                    </b-col>

                    <b-col :class="[currentTab === 3 ? 'tab-active' : '', 'tab']" @click="handleChangeCurrentTab(3)">
                        <span class="tab-text">{{ $t('VEHICLE_MASTER.VEHICLE_INSPECTION_CERTIFICATE.TITLE') }}</span>
                    </b-col>

                    <b-col :class="[currentTab === 4 ? 'tab-active' : '', 'tab']" @click="handleChangeCurrentTab(4)">
                        <span class="tab-text">{{ $t('VEHICLE_COST') }}</span>
                    </b-col>

                    <b-col :class="[currentTab === 5 ? 'tab-active' : '', 'tab']" @click="handleChangeCurrentTab(5)">
                        <span class="tab-text">{{ $t('VEHICLE_DEPRECIATION') }}</span>
                    </b-col>

                    <b-col :class="[currentTab === 6 ? 'tab-active' : '', 'tab']" @click="handleChangeCurrentTab(6)">
                        <span class="tab-text">{{ $t('PERIODIC_INSPECTION_AND_MAINTENANCE') }}</span>
                    </b-col>
                </b-row>
            </div>

            <template v-if="currentTab === 1">
                <vDropdownView :dropdown-title="listDropdownTitle">
                    <template #dropdown-content>
                        <b-row>
                            <b-col cols="12" class="mt-3">
                                <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.VEHICLE_IDENTIFICATION_NUMBER'" />

                                <label for="vehicle_identification_number_2" class="d-flex align-items-center justify-content-center" style="float: right;">
                                    <span>任意の車両番号を設置する場合はこちらをチェック</span>
                                    <b-form-checkbox v-model="vin2_checkbox_status" size="xl" class="ml-3" disabled />
                                </label>

                                <vInput v-model="formData.vehicle_identification_number" :type="'text'" placeholder="" disabled />
                            </b-col>

                            <b-col v-if="vin2_checkbox_status" cols="12" class="mt-3">
                                <label for="vehicle_identification_number_2">
                                    <span>車体番号(任意)</span>
                                </label>

                                <vInput v-model="formData.vehicle_identification_number_2" :type="'text'" disabled placeholder="入力してください" :class-name="'vehicle_identification_number_2'" />
                            </b-col>

                            <b-col cols="12" class="mt-3">
                                <b-row>
                                    <b-col cols="12" class="text-left">
                                        <label for="door_number">ドアナンバー</label>
                                    </b-col>
                                </b-row>

                                <vInput v-model="formData.door_number" :type="'text'" placeholder="" disabled :class-name="'door_number'" />
                            </b-col>

                            <b-col cols="12" class="mt-3">
                                <b-row>
                                    <b-col cols="12" class="text-left">
                                        <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.NO_NUMBER_PLATE'" />
                                        <span>/</span>
                                        <i class="fas fa-history" @click="handleOpenHistoryModal(0)">
                                            <span class="d-inline-flex history-text">{{ $t('BUTTON.HISTORY') }}</span>
                                        </i>
                                    </b-col>
                                </b-row>

                                <vInput v-model="formData.no_number_plate" :type="'text'" placeholder="" disabled />
                            </b-col>

                            <b-col cols="12" class="mt-3">
                                <b-row>
                                    <b-col cols="6">
                                        <b-row>
                                            <b-col cols="12" class="text-left">
                                                <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.DEPARTMENT_ID'" />
                                                <span>/</span>
                                                <i class="fas fa-history" @click="handleOpenHistoryModal(1)">
                                                    <span class="d-inline-flex history-text">{{ $t('BUTTON.HISTORY') }}</span>
                                                </i>
                                            </b-col>
                                        </b-row>

                                        <vInput v-model="formData.department_id" :type="'text'" placeholder="" disabled />
                                    </b-col>

                                    <b-col cols="6">
                                        <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.DRIVING_CLASSIFICATION'" />
                                        <vInput v-model="formData.driving_classification" :type="'text'" placeholder="" disabled />
                                    </b-col>
                                </b-row>
                            </b-col>

                            <b-col cols="12" class="mt-3">
                                <b-row>
                                    <b-col cols="6">
                                        <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.TONNAGE'" />
                                        <vInput v-model="formData.tonnage" :type="'text'" placeholder="" disabled />
                                    </b-col>

                                    <b-col cols="6">
                                        <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.TRUCK_CLASSIFICATION'" />
                                        <vInput v-model="formData.truck_classification" :type="'text'" placeholder="" disabled />
                                    </b-col>
                                </b-row>
                            </b-col>

                            <b-col cols="12" class="mt-3">
                                <b-row>
                                    <b-col cols="6">
                                        <label for="voluntary_premium">任意保険料</label>
                                        <vInput v-model="formData.voluntary_premium" :type="'number'" placeholder="" disabled />
                                    </b-col>

                                    <b-col cols="6">
                                        <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.CERTIFICATION'" />
                                        <vInput v-model="formData.certification" :type="'text'" placeholder="" disabled />
                                    </b-col>
                                </b-row>
                            </b-col>

                            <b-col cols="12" class="mt-3">
                                <b-row>
                                    <b-col cols="12">
                                        <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.MANUFACTOR'" />
                                        <vInput v-model="formData.manufactor" :type="'text'" placeholder="" disabled />
                                    </b-col>
                                </b-row>
                            </b-col>

                            <b-col cols="12" class="mt-3">
                                <b-row>
                                    <b-col cols="4">
                                        <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.FIRST_REGISTRATION'" />
                                        <vInput v-model="formData.first_registration" :type="'text'" placeholder="" disabled />
                                    </b-col>

                                    <b-col cols="4">
                                        <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.INSPECTION_EXPIRATION_DATE'" />
                                        <vInput v-model="formData.inspection_expiration_date" :type="'text'" placeholder="" disabled :class-name="'no-border-right fixed-height-input'" />
                                    </b-col>

                                    <b-col cols="4">
                                        <VI18NLabel :text-label="'車両納入日'" />
                                        <vInput v-model="formData.vehicle_delivery_date" :type="'text'" placeholder="" disabled :class-name="'no-border-right fixed-height-input'" />
                                    </b-col>
                                </b-row>
                            </b-col>

                            <b-col cols="12" class="mt-3">
                                <b-row>
                                    <b-col cols="6">
                                        <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.BOX_DISTINCTION'" />
                                        <vInput v-model="formData.box_distinction" :type="'text'" placeholder="" disabled />
                                    </b-col>

                                    <b-col cols="6">
                                        <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.OWNER'" />
                                        <vInput v-model="formData.owner" :type="'text'" placeholder="" disabled />
                                    </b-col>
                                </b-row>
                            </b-col>

                            <b-col cols="12" class="mt-3">
                                <b-row>
                                    <b-col cols="6">
                                        <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.ETC_CERTIFICATION_NUMBER'" />
                                        <vInput v-model="formData.etc_certification_number" :type="'text'" placeholder="" disabled />
                                    </b-col>

                                    <b-col cols="6">
                                        <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.ETC_NUMBER'" />
                                        <vInput v-model="formData.etc_number" :type="'text'" placeholder="" disabled />
                                    </b-col>
                                </b-row>
                            </b-col>

                            <b-col cols="12" class="mt-3">
                                <b-row>
                                    <b-col cols="6">
                                        <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.FUEL_CARD_NUMBER_1'" />
                                        <vInput v-model="formData.fuel_card_number_1" :type="'text'" placeholder="" disabled />
                                    </b-col>

                                    <b-col cols="6">
                                        <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.FUEL_CARD_NUMBER_2'" />
                                        <vInput v-model="formData.fuel_card_number_2" :type="'text'" placeholder="" disabled />
                                    </b-col>
                                </b-row>
                            </b-col>

                            <b-col cols="12" class="mt-3">
                                <b-row>
                                    <b-col cols="6">
                                        <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.BOX_SHAPE'" />
                                        <vInput v-model="formData.box_shape" :type="'text'" placeholder="" disabled />
                                    </b-col>

                                    <b-col cols="6">
                                        <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.MOUNT'" />
                                        <vInput v-model="formData.mount" :type="'text'" placeholder="" disabled />
                                    </b-col>
                                </b-row>
                            </b-col>

                            <b-col cols="12" class="mt-3">
                                <b-row>
                                    <b-col cols="6">
                                        <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.REFRIGERATOR'" />
                                        <vInput v-model="formData.refrigerator" :type="'text'" placeholder="" disabled />
                                    </b-col>

                                    <b-col cols="6">
                                        <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.EVA_TYPE'" />
                                        <vInput v-model="formData.eva_type" :type="'text'" placeholder="" disabled />
                                    </b-col>
                                </b-row>
                            </b-col>

                            <b-col cols="12" class="mt-3">
                                <b-row>
                                    <b-col cols="6">
                                        <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.GATE'" />
                                        <b-form-select v-model="formData.gate" :options="optionsGate" class="gate" disabled>
                                            <template #first>
                                                <b-form-select-option :value="null" disabled>
                                                    {{ $t('PLEASE_SELECT') }}
                                                </b-form-select-option>
                                            </template>
                                        </b-form-select>
                                    </b-col>

                                    <b-col cols="6">
                                        <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.HUMIDIFIER'" />
                                        <b-form-select v-model="formData.humidifier" :options="optionsHumidifier" class="humidifier" disabled>
                                            <template #first>
                                                <b-form-select-option :value="null" disabled>
                                                    {{ $t('PLEASE_SELECT') }}
                                                </b-form-select-option>
                                            </template>
                                        </b-form-select>
                                    </b-col>
                                </b-row>
                            </b-col>

                            <b-col cols="12" class="mt-3">
                                <b-row>
                                    <b-col cols="6">
                                        <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.TYPE'" />
                                        <vInput v-model="formData.type" :type="'text'" placeholder="" disabled />
                                    </b-col>

                                    <b-col cols="6">
                                        <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.MOTOR'" />
                                        <vInput v-model="formData.motor" :type="'text'" placeholder="" disabled />
                                    </b-col>
                                </b-row>
                            </b-col>

                            <b-col cols="12" class="mt-3">
                                <b-row>
                                    <b-col cols="6">
                                        <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.DISPLACEMENT'" />
                                        <vInput v-model="formData.displacement" :type="'text'" placeholder="" disabled />
                                    </b-col>

                                    <b-col cols="6">
                                        <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.LENGTH'" />
                                        <vInput v-model="formData._length" :type="'text'" placeholder="" disabled />
                                    </b-col>
                                </b-row>
                            </b-col>

                            <b-col cols="12" class="mt-3">
                                <b-row>
                                    <b-col cols="6">
                                        <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.WIDTH'" />
                                        <vInput v-model="formData.width" :type="'text'" placeholder="" disabled />
                                    </b-col>

                                    <b-col cols="6">
                                        <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.HEIGHT'" />
                                        <vInput v-model="formData.height" :type="'text'" placeholder="" disabled />
                                    </b-col>
                                </b-row>
                            </b-col>

                            <b-col cols="12" class="mt-3">
                                <b-row>
                                    <b-col cols="6">
                                        <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.MAXIMUM_LOADING_CAPACITY'" />
                                        <vInput v-model="formData.maximum_loading_capacity" :type="'text'" placeholder="" disabled />
                                    </b-col>

                                    <b-col cols="6">
                                        <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.VEHICLE_TOTAL_WEIGHT'" />
                                        <vInput v-model="formData.vehicle_total_weight" :type="'text'" placeholder="" disabled />
                                    </b-col>
                                </b-row>
                            </b-col>

                            <b-col cols="12" class="mt-3">
                                <b-row>
                                    <b-col cols="6">
                                        <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.IN_BOX_LENGTH'" />
                                        <vInput v-model="formData.in_box_length" :type="'text'" placeholder="" disabled />
                                    </b-col>

                                    <b-col cols="6">
                                        <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.IN_BOX_WIDTH'" />
                                        <vInput v-model="formData.in_box_width" :type="'text'" placeholder="" disabled />
                                    </b-col>
                                </b-row>
                            </b-col>

                            <b-col cols="12" class="mt-3">
                                <b-row>
                                    <b-col cols="6">
                                        <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.IN_BOX_HEIGHT'" />
                                        <vInput v-model="formData.in_box_height" :type="'text'" placeholder="" disabled />
                                    </b-col>

                                    <b-col cols="6">
                                        <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.SCRAP_DATE'" />
                                        <b-form-datepicker v-model="formData.scrap_date" :date-format-options="{ year: 'numeric', month: '2-digit', day: '2-digit' }" :locale="lang" :calendar-width="`290px`" :label-no-date-selected="'選択してください'" :label-help="'カーソルキーを使用してカレンダーの日付をナビゲートする'" placeholder="入力してください" disabled />
                                    </b-col>
                                </b-row>
                            </b-col>

                            <b-col cols="12" class="mt-3">
                                <b-row>
                                    <b-col cols="6">
                                        <VI18NLabel :text-label="'期中登録'" />
                                        <b-form-checkbox id="early-registration-checkb≥ox" v-model="formData.early_registration" size="lg" :value="true" :unchecked-value="false" disabled />
                                    </b-col>

                                    <b-col cols="6">
                                        <VI18NLabel :text-label="'D1D非搭載'" />
                                        <b-form-checkbox id="d1d-not-installed" v-model="formData.d1d_not_installed" size="lg" :value="1" :unchecked-value="0" disabled />
                                    </b-col>
                                </b-row>
                            </b-col>
                        </b-row>
                    </template>

                    <template #dropdown-content-1>
                        <b-row>
                            <b-col cols="12" class="mt-3">
                                <b-row>
                                    <b-col cols="6">
                                        <VI18NLabel :text-label="'VEHICLE_MASTER.INSURANCE_INFORMATION.OPTION_DETAIL'" />
                                        <vInput v-model="formData.optional_detail" :type="'text'" placeholder="" disabled />
                                    </b-col>

                                    <b-col cols="6">
                                        <VI18NLabel :text-label="'VEHICLE_MASTER.INSURANCE_INFORMATION.LIABILITY_INSURANCE_PERIOD'" />
                                        <vInput v-model="formData.liability_insurance_period" :type="'text'" placeholder="" disabled />
                                    </b-col>
                                </b-row>
                            </b-col>

                            <b-col cols="12" class="mt-3">
                                <b-row>
                                    <b-col cols="6">
                                        <VI18NLabel :text-label="'VEHICLE_MASTER.INSURANCE_INFORMATION.INSURANCE_COMPANY'" />
                                        <vInput v-model="formData.insurance_company" :type="'text'" placeholder="" disabled />
                                    </b-col>

                                    <b-col cols="6">
                                        <VI18NLabel :text-label="'VEHICLE_MASTER.INSURANCE_INFORMATION.AGENT'" />
                                        <vInput v-model="formData.agent" :type="'text'" placeholder="" disabled />
                                    </b-col>
                                </b-row>
                            </b-col>

                            <b-col cols="12" class="mt-3">
                                <b-row>
                                    <b-col cols="6">
                                        <VI18NLabel :text-label="'VEHICLE_MASTER.INSURANCE_INFORMATION.MILEAGE'" />
                                        <vInput v-model="formData.mileage" :type="'text'" placeholder="" disabled />
                                    </b-col>

                                    <b-col cols="6">
                                        <VI18NLabel :text-label="'VEHICLE_MASTER.INSURANCE_INFORMATION.MONTHLY_MILEAGE'" />
                                        <vInput v-model="formData.monthly_mileage" :type="'text'" placeholder="" disabled />
                                    </b-col>
                                </b-row>
                            </b-col>

                            <b-col cols="12" class="mt-3">
                                <b-row>
                                    <b-col cols="6">
                                        <VI18NLabel :text-label="'VEHICLE_MASTER.INSURANCE_INFORMATION.TIRE_SIZE'" />
                                        <vInput v-model="formData.tire_size" :type="'text'" placeholder="" disabled />
                                    </b-col>

                                    <b-col cols="6">
                                        <VI18NLabel :text-label="'VEHICLE_MASTER.INSURANCE_INFORMATION.BATTERY_SIZE'" />
                                        <vInput v-model="formData.battery_size" :type="'text'" placeholder="" disabled />
                                    </b-col>
                                </b-row>
                            </b-col>
                        </b-row>
                    </template>

                    <template #dropdown-content-2>
                        <b-row>
                            <b-col cols="12" class="mt-3">
                                <b-row>
                                    <b-col cols="6">
                                        <VI18NLabel :text-label="'VEHICLE_MASTER.MAINTENANCE_LEASE.START_OF_LEASING'" />
                                        <vInput v-model="formData.start_of_leasing" :type="'text'" placeholder="" disabled :class-name="'no-border-right fixed-height-input'" />
                                    </b-col>

                                    <b-col cols="6">
                                        <VI18NLabel :text-label="'VEHICLE_MASTER.MAINTENANCE_LEASE.END_OF_LEASING'" />
                                        <vInput v-model="formData.end_of_leasing" :type="'text'" placeholder="" disabled :class-name="'no-border-right fixed-height-input'" />
                                    </b-col>
                                </b-row>
                            </b-col>

                            <b-col cols="12" class="mt-3">
                                <b-row>
                                    <b-col cols="6">
                                        <VI18NLabel :text-label="'VEHICLE_MASTER.MAINTENANCE_LEASE.LEASING_PERIOD'" />
                                        <vInput v-model="formData.leasing_period" :type="'text'" placeholder="" disabled />
                                    </b-col>

                                    <b-col cols="6">
                                        <VI18NLabel :text-label="'VEHICLE_MASTER.MAINTENANCE_LEASE.LEASING_COMANY'" />
                                        <vInput v-model="formData.leasing_company" :type="'text'" placeholder="" disabled />
                                    </b-col>
                                </b-row>
                            </b-col>

                            <b-col cols="12" class="mt-3">
                                <b-row>
                                    <b-col cols="6">
                                        <label for="maintenance_lease_fee">メンテナンスリース料</label>
                                        <vInput v-model="formData.maintenance_lease_fee" placeholder="" disabled />
                                    </b-col>

                                    <b-col cols="6">
                                        <VI18NLabel :text-label="'VEHICLE_MASTER.MAINTENANCE_LEASE.GARAGE'" />
                                        <vInput v-model="formData.garage" :type="'text'" placeholder="" disabled />
                                    </b-col>
                                </b-row>
                            </b-col>

                            <b-col cols="12" class="mt-3">
                                <b-row>
                                    <b-col cols="12">
                                        <VI18NLabel :text-label="'VEHICLE_MASTER.MAINTENANCE_LEASE.TEL'" />
                                        <vInput v-model="formData.tel" :type="'text'" placeholder="" disabled />
                                    </b-col>
                                </b-row>
                            </b-col>
                        </b-row>
                    </template>

                    <template #dropdown-content-3>
                        <b-row>
                            <b-col cols="12" class="mt-3">
                                <b-row>
                                    <b-col cols="12" class="text-left">
                                        <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_CERTIFICATION.VERIFY_CERTIFICATE'" />
                                        <b-button class="history-button" @click="handleOpenHistoryModal(2)">
                                            <i class="fas fa-history"> {{ $t('BUTTON.HISTORY') }}</i>
                                        </b-button>
                                    </b-col>
                                </b-row>

                                <div id="preview" class="mt-3">
                                    <template v-if="formData.vehicle_pdf_history.length > 0">
                                        <vue-pdf-embed :width="'500'" :height="'400'" :page="1" :source="formData.vehicle_pdf_history[0]['file_url']" />
                                    </template>
                                </div>
                            </b-col>

                            <b-col cols="12" class="mt-3">
                                <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_CERTIFICATION.REMARK'" />
                                <b-form-textarea v-model="formData.old_car_1" rows="4" placeholder="" disabled />
                            </b-col>
                        </b-row>
                    </template>
                </vDropdownView>

                <b-row class="footer">
                    <b-col cols="6" class="p-0 text-left">
                        <b-button class="functional-button btn-back" @click="onClickBackButton()">{{ $t('BUTTON.BACK') }}</b-button>
                    </b-col>

                    <b-col v-if="hasAccessEdit.includes(role)" cols="6" class="p-0 text-right">
                        <b-button class="functional-button btn-edit" @click="onClickEditButton()">{{ $t('BUTTON.EDIT') }}</b-button>
                    </b-col>
                </b-row>
            </template>

            <template v-else-if="currentTab === 2">
                <div class="compulsory-automobile-liability-insurance">
                    <div class="image-holder" :style="formData.data_orc_ai[compulsory_pagination.current_page - 1] && formData.data_orc_ai[compulsory_pagination.current_page - 1].file_path ? 'height: 1600px;' : 'height: 500px;'">
                        <span v-if="!formData.data_orc_ai[compulsory_pagination.current_page - 1]" style="position: relative; top: 50%; transform: translateY(-50%); font-size: 30px; left: 50%; transform: translateX(-50%);">
                            PDF
                        </span>

                        <img v-else style="width: 100%; height: 100%; object-fit: fill;" :src="formData.data_orc_ai[compulsory_pagination.current_page - 1] && formData.data_orc_ai[compulsory_pagination.current_page - 1].file_path" :alt="formData.data_orc_ai[compulsory_pagination.current_page - 1] && formData.data_orc_ai[compulsory_pagination.current_page - 1].file_name">
                    </div>

                    <b-row>
                        <b-col class="col-input" cols="12">
                            <label>{{ $t('VEHICLE_MASTER.COMPULSORY_AUTOMOBILE_LIABILITY_INSURANCE.CERTIFICATE_NUMBER') }}</label>
                            <vInput :value="formData.data_orc_ai[compulsory_pagination.current_page - 1] ? formData.data_orc_ai[compulsory_pagination.current_page - 1].certificate_number : ''" placeholder="" disabled />
                        </b-col>

                        <b-col class="col-input" cols="12">
                            <label>{{ $t('VEHICLE_MASTER.COMPULSORY_AUTOMOBILE_LIABILITY_INSURANCE.ISSUE_DATE') }}</label>
                            <vInput :value="formData.data_orc_ai[compulsory_pagination.current_page - 1] ? formData.data_orc_ai[compulsory_pagination.current_page - 1].issue_date : ''" placeholder="" disabled />
                        </b-col>

                        <b-col class="col-input" cols="12">
                            <label>{{ $t('VEHICLE_MASTER.COMPULSORY_AUTOMOBILE_LIABILITY_INSURANCE.VEHICLE_IDENTIFICATION_NUMBER') }}</label>
                            <vInput :value="formData.data_orc_ai[compulsory_pagination.current_page - 1] ? formData.data_orc_ai[compulsory_pagination.current_page - 1].vehicle_identification_number : ''" placeholder="" disabled />
                        </b-col>

                        <b-col class="col-input" cols="12">
                            <label>{{ $t('VEHICLE_MASTER.COMPULSORY_AUTOMOBILE_LIABILITY_INSURANCE.INSURANCE_PERIOD_1') }}</label>
                            <vInput :value="formData.data_orc_ai[compulsory_pagination.current_page - 1] ? formData.data_orc_ai[compulsory_pagination.current_page - 1].insurance_period_1 : ''" placeholder="" disabled />
                        </b-col>

                        <b-col class="col-input" cols="12">
                            <label>{{ $t('VEHICLE_MASTER.COMPULSORY_AUTOMOBILE_LIABILITY_INSURANCE.INSURANCE_PERIOD_2') }}</label>
                            <vInput :value="formData.data_orc_ai[compulsory_pagination.current_page - 1] ? formData.data_orc_ai[compulsory_pagination.current_page - 1].insurance_period_2 : ''" placeholder="" disabled />
                        </b-col>

                        <b-col class="col-input" cols="12">
                            <label>{{ $t('VEHICLE_MASTER.COMPULSORY_AUTOMOBILE_LIABILITY_INSURANCE.ADDRESS') }}</label>
                            <vInput :value="formData.data_orc_ai[compulsory_pagination.current_page - 1] ? formData.data_orc_ai[compulsory_pagination.current_page - 1].address : ''" placeholder="" disabled />
                        </b-col>

                        <b-col class="col-input" cols="12">
                            <label>{{ $t('VEHICLE_MASTER.COMPULSORY_AUTOMOBILE_LIABILITY_INSURANCE.POLICY_HOLDER') }}</label>
                            <vInput :value="formData.data_orc_ai[compulsory_pagination.current_page - 1] ? formData.data_orc_ai[compulsory_pagination.current_page - 1].policyholder : ''" placeholder="" disabled />
                        </b-col>

                        <b-col class="col-input" cols="12">
                            <label>{{ $t('VEHICLE_MASTER.COMPULSORY_AUTOMOBILE_LIABILITY_INSURANCE.CHANGE_ITEM') }}</label>
                            <vInput :value="formData.data_orc_ai[compulsory_pagination.current_page - 1] ? formData.data_orc_ai[compulsory_pagination.current_page - 1].change_item : ''" placeholder="" disabled />
                        </b-col>

                        <b-col class="col-input" cols="12">
                            <label>{{ $t('VEHICLE_MASTER.COMPULSORY_AUTOMOBILE_LIABILITY_INSURANCE.JURISDICTION_STORE_NAME_AND_LOCATION') }}</label>
                            <vInput :value="formData.data_orc_ai[compulsory_pagination.current_page - 1] ? formData.data_orc_ai[compulsory_pagination.current_page - 1].jurisdiction_store_name_and_location : ''" placeholder="" disabled />
                        </b-col>

                        <b-col class="col-input" cols="12">
                            <label>{{ $t('VEHICLE_MASTER.COMPULSORY_AUTOMOBILE_LIABILITY_INSURANCE.VEHICLE_TYPE') }}</label>
                            <vInput :value="formData.data_orc_ai[compulsory_pagination.current_page - 1] ? formData.data_orc_ai[compulsory_pagination.current_page - 1].vehicle_type : ''" placeholder="" disabled />
                        </b-col>

                        <b-col class="col-input" cols="12">
                            <label>{{ $t('VEHICLE_MASTER.COMPULSORY_AUTOMOBILE_LIABILITY_INSURANCE.LOCATION') }}</label>
                            <vInput :value="formData.data_orc_ai[compulsory_pagination.current_page - 1] ? formData.data_orc_ai[compulsory_pagination.current_page - 1].location : ''" placeholder="" disabled />
                        </b-col>

                        <b-col class="col-input" cols="12">
                            <label>{{ $t('VEHICLE_MASTER.COMPULSORY_AUTOMOBILE_LIABILITY_INSURANCE.INSURANCE_FEE') }}</label>
                            <vInput :value="formData.data_orc_ai[compulsory_pagination.current_page - 1] ? formData.data_orc_ai[compulsory_pagination.current_page - 1].insurance_fee : ''" placeholder="" disabled />
                        </b-col>

                        <b-col class="col-input" cols="12">
                            <label>{{ $t('VEHICLE_MASTER.COMPULSORY_AUTOMOBILE_LIABILITY_INSURANCE.FINANCIAL_INSTITUTION_NAME') }}</label>
                            <vInput :value="formData.data_orc_ai[compulsory_pagination.current_page - 1] ? formData.data_orc_ai[compulsory_pagination.current_page - 1].financial_institution_name : ''" placeholder="" disabled />
                        </b-col>

                        <b-col class="col-input" cols="12">
                            <label>{{ $t('VEHICLE_MASTER.COMPULSORY_AUTOMOBILE_LIABILITY_INSURANCE.SEAL') }}</label>
                            <vInput :value="formData.data_orc_ai[compulsory_pagination.current_page - 1] ? formData.data_orc_ai[compulsory_pagination.current_page - 1].seal : ''" placeholder="" disabled />
                        </b-col>

                        <b-col cols="12">
                            <div class="pagination w-100">
                                <div class="show-pagination" style="width: 100% !important;">
                                    <vPagination :aria-controls="'table-vehicle-master'" :current-page="compulsory_pagination.current_page" :per-page="compulsory_pagination.per_page" :total-rows="compulsory_pagination.total_rows" :next-class="'next'" :prev-class="'prev'" @currentPageChange="getCurrentPageSecondTab" />
                                </div>
                            </div>
                        </b-col>

                        <b-col class="mb-3" cols="12">
                            <b-row style="margin: 0 auto; width: 100% !important;">
                                <b-col cols="12" class="p-0 text-left">
                                    <b-button class="functional-button btn-back" @click="onClickBackButton()">{{ $t('BUTTON.BACK') }}</b-button>
                                </b-col>
                            </b-row>
                        </b-col>
                    </b-row>
                </div>
            </template>

            <template v-else-if="currentTab === 3">
                <div class="new-ui">
                    <div class="sub-card">
                        <b-row class="w-100">
                            <b-col cols="12" class="d-flex flex-row" style="padding-left: 68%;">
                                <div class="card-one">
                                    <span>記録年月日</span>
                                </div>

                                <div class="card-two">
                                    <span>{{ handleRenderData('GrantdateE') }}</span>
                                    <!-- <span>{{ handleRenderData('created_at') }}</span> -->
                                </div>
                            </b-col>
                        </b-row>

                        <b-row style="width: 97%; margin: 0 auto;">
                            <b-col cols="6">
                                <span style="font-size: 45px; display: inline-flex; margin-left: -15px;">自動車検査証記録事項</span>
                                <span style="font-size: 45px; margin-left: 25px;">{{ `(${handleRenderData('FormType')})` }}</span>
                            </b-col>

                            <b-col cols="6">
                                <span style="display: flex; position: absolute; right: 6px; bottom: 10px;">{{ handleRenderData('ElectCertMgNo') }}</span>
                            </b-col>
                        </b-row>

                        <b-row class="card-content">
                            <b-col cols="12" class="col-1-prime">
                                <span style="font-weight: bold; font-size: 14px !important;">1. 基本情報</span>
                            </b-col>

                            <b-col cols="12" class="col-2">
                                <b-row class="w-100">
                                    <b-col cols="3" class="part-1">
                                        <span>自動車登録番号又は車両番号</span>
                                    </b-col>

                                    <b-col cols="9" class="part-2">
                                        <span>{{ handleRenderData('EntryNoCarNo') }}</span>
                                    </b-col>
                                </b-row>
                            </b-col>

                            <b-col cols="12" class="col-2">
                                <b-row class="w-100">
                                    <b-col cols="1" class="part-1">
                                        <span>車台番号</span>
                                    </b-col>

                                    <b-col cols="11" class="part-2">
                                        <span>{{ handleRenderData('CarNo') }}</span>
                                    </b-col>
                                </b-row>
                            </b-col>

                            <b-col cols="12" class="col-3">
                                <b-row class="w-100">
                                    <b-col cols="2" class="part-1">
                                        <span>登録年月日/交付年月日</span>
                                    </b-col>

                                    <b-col cols="2" class="part-2">
                                        <span>{{ handleRenderData('RegGrantDateE') }}</span>
                                    </b-col>

                                    <b-col cols="2" class="part-3">
                                        <span>初年度登録年月</span>
                                    </b-col>

                                    <b-col cols="2" class="part-4">
                                        <span>{{ handleRenderData('FirstRegDateE') }}</span>
                                    </b-col>

                                    <b-col cols="2" class="part-5">
                                        <span>有効期限の満了する日</span>
                                    </b-col>

                                    <b-col cols="2" class="part-6">
                                        <span>{{ handleRenderData('ValidPeriodExpDateE') }}</span>
                                    </b-col>
                                </b-row>
                            </b-col>

                            <b-col cols="12" class="col-4">
                                <b-row class="w-100">
                                    <b-col cols="12">
                                        <span style="font-weight: bold; font-size: 14px !important;">2. 使用者情報</span>
                                    </b-col>
                                </b-row>
                            </b-col>

                            <b-col v-if=" (handleRenderData('FormType')) === 'Ａ' || (handleRenderData('FormType')) === 'A' " cols="12" class="col-type-a-1">
                                <b-row class="w-100">
                                    <b-col cols="3" class="part-1">
                                        <span>所有者の氏名又は名称</span>
                                    </b-col>

                                    <b-col cols="9" class="part-2">
                                        <span>{{ handleRenderData('OwnerNameLowLevelChar') }}</span>
                                        <span class="d-inline-flex" style="margin-left: 10px;">{{
                                            handleRenderData('OwnerNameHighLevelChar') }}</span>
                                    </b-col>
                                </b-row>
                            </b-col>

                            <b-col v-if="(handleRenderData('FormType')) === 'Ａ' || (handleRenderData('FormType')) === 'A'" cols="12" class="col-type-a-2">
                                <b-row class="w-100">
                                    <b-col cols="3" class="part-1">
                                        <span>所有者の住所</span>
                                    </b-col>

                                    <b-col cols="9" class="part-2">
                                        <span>{{ handleRenderData('OwnerAddressChar') }}</span>
                                        <span>{{ handleRenderData('OwnerAddressNumValue') }}</span>
                                        <span class="d-inline-flex" style="float: right;">{{
                                            handleRenderData('OwnerAddressCode') }}</span>
                                    </b-col>
                                </b-row>
                            </b-col>

                            <b-col cols="12" class="col-5">
                                <b-row class="w-100">
                                    <b-col cols="3" class="part-1">
                                        <span>使用者の氏名又は名称</span>
                                    </b-col>

                                    <b-col cols="9" class="part-2">
                                        <span>{{ handleRenderData('UsernameLowLevelChar') }}</span>
                                    </b-col>
                                </b-row>
                            </b-col>

                            <b-col cols="12" class="col-6-prime">
                                <b-row class="w-100">
                                    <b-col cols="3" class="part-1">
                                        <span>使用者の住所</span>
                                    </b-col>

                                    <b-col cols="9" class="part-2">
                                        <span>{{ handleRenderData('UserAddressChar') }}</span>
                                        <span>{{ handleRenderData('UserAddressNumValue') }}</span>
                                        <span class="float-right">{{ handleRenderData('UserAddressCode') }}</span>
                                    </b-col>
                                </b-row>
                            </b-col>

                            <b-col cols="12" class="col-6-prime">
                                <b-row class="w-100">
                                    <b-col cols="3" class="part-1">
                                        <span>使用者の本拠の位置</span>
                                    </b-col>

                                    <b-col cols="9" class="part-2">
                                        <span>{{ handleRenderData('UseHeadquarterChar') }}</span>
                                        <span>{{ handleRenderData('UseHeadquarterNumValue') }}</span>
                                        <span class="float-right">{{ handleRenderData('UseHeadquarterCode') }}</span>
                                    </b-col>
                                </b-row>
                            </b-col>

                            <b-col cols="12" class="col-7">
                                <b-row class="w-100">
                                    <b-col cols="12">
                                        <span style="font-weight: bold; font-size: 14px !important;">3. 車両詳細情報</span>
                                    </b-col>
                                </b-row>
                            </b-col>

                            <b-col cols="12" class="col-8">
                                <b-row class="w-100">
                                    <b-col cols="1" class="part-1">
                                        <span>車名</span>
                                    </b-col>

                                    <b-col cols="11" class="part-2">
                                        <span>{{ handleRenderData('CarName') }}</span>
                                        <span class="float-right">{{ handleRenderData('CarNameCode') }}</span>
                                    </b-col>
                                </b-row>
                            </b-col>

                            <b-col cols="12" class="col-9">
                                <b-row class="w-100">
                                    <b-col cols="1" class="part-1">
                                        <span>型式</span>
                                    </b-col>

                                    <b-col cols="5" class="part-2">
                                        <span>{{ handleRenderData('Model') }}</span>
                                    </b-col>

                                    <b-col cols="2" class="part-3">
                                        <span>原動機の型式</span>
                                    </b-col>

                                    <b-col cols="4" class="part-4">
                                        <span>{{ handleRenderData('EngineModel') }}</span>
                                    </b-col>
                                </b-row>
                            </b-col>

                            <b-col cols="12" class="col-10">
                                <b-row class="w-100">
                                    <b-col cols="2" class="part-1">
                                        <span>自動車の種別</span>
                                    </b-col>

                                    <b-col cols="2" class="part-2">
                                        <span>{{ handleRenderData('CarKind') }}</span>
                                    </b-col>

                                    <b-col cols="2" class="part-3">
                                        <span>用途</span>
                                    </b-col>

                                    <b-col cols="2" class="part-4">
                                        <span>{{ handleRenderData('Use') }}</span>
                                    </b-col>

                                    <b-col cols="2" class="part-5">
                                        <span>自家用・事業用の別</span>
                                    </b-col>

                                    <b-col cols="2" class="part-6">
                                        <span>{{ handleRenderData('PrivateBusiness') }}</span>
                                    </b-col>
                                </b-row>
                            </b-col>

                            <b-col cols="12" class="col-11-prime">
                                <b-row class="w-100">
                                    <b-col cols="2" class="part-1">
                                        <span>車体の形状</span>
                                    </b-col>

                                    <b-col cols="4" class="part-2">
                                        <span>{{ handleRenderData('CarShape') }}</span>

                                        <span class="float-right">
                                            {{ handleRenderData('CarShapeCode') }}
                                        </span>
                                    </b-col>

                                    <b-col cols="1" class="part-3">
                                        <span>乗車定員</span>
                                    </b-col>

                                    <b-col cols="2" class="part-4">
                                        <span>{{ handleRenderData('Cap') }}</span>
                                        <span class="sub-description-text">人</span>
                                    </b-col>

                                    <b-col cols="1" class="part-5">
                                        <span>最大積載量</span>
                                    </b-col>

                                    <b-col cols="2" class="part-6">
                                        <span>{{ handleRenderData('MaxLoadAge') }}</span>
                                        <span class="sub-description-text">kg</span>
                                    </b-col>
                                </b-row>
                            </b-col>

                            <b-col cols="12" class="col-12-prime">
                                <b-row class="w-100">
                                    <b-col cols="1" class="part-1">
                                        <span>車両重量</span>
                                    </b-col>

                                    <b-col cols="2" class="part-2">
                                        <span>{{ handleRenderData('CarWgt') }}</span>
                                        <span class="sub-description-text">kg</span>
                                    </b-col>

                                    <b-col cols="1" class="part-3">
                                        <span>車両総重量</span>
                                    </b-col>

                                    <b-col cols="2" class="part-4">
                                        <span>{{ handleRenderData('CarTotalWgt') }}</span>
                                        <span class="sub-description-text">kg</span>
                                    </b-col>

                                    <b-col cols="1" class="part-5">
                                        <span>長さ</span>
                                    </b-col>

                                    <b-col cols="1" class="part-6">
                                        <span>{{ handleRenderData('Length') }}</span>
                                        <span class="sub-description-text">cm</span>
                                    </b-col>

                                    <b-col cols="1" class="part-7">
                                        <span>幅</span>
                                    </b-col>

                                    <b-col cols="1" class="part-8">
                                        <span>{{ handleRenderData('Width') }}</span>
                                        <span class="sub-description-text">cm</span>
                                    </b-col>

                                    <b-col cols="1" class="part-9">
                                        <span>高さ</span>
                                    </b-col>

                                    <b-col cols="1" class="part-10">
                                        <span>{{ handleRenderData('Height') }}</span>
                                        <span class="sub-description-text">cm</span>
                                    </b-col>
                                </b-row>
                            </b-col>

                            <b-col cols="12" class="col-13">
                                <b-row class="w-100">
                                    <b-col cols="1" class="part-1">
                                        <span>前前軸重</span>
                                    </b-col>

                                    <b-col cols="1" class="part-2">
                                        <span>{{ handleRenderData('FfAxWgt') }}</span>
                                        <span class="sub-description-text">kg</span>
                                    </b-col>

                                    <b-col cols="1" class="part-3">
                                        <span>前後軸重</span>
                                    </b-col>

                                    <b-col cols="1" class="part-4">
                                        <span>{{ handleRenderData('FrAxWgt') }}</span>
                                        <span class="sub-description-text">kg</span>
                                    </b-col>

                                    <b-col cols="1" class="part-5">
                                        <span>前後軸重</span>
                                    </b-col>

                                    <b-col cols="1" class="part-6">
                                        <span>{{ handleRenderData('RfAxWgt') }}</span>
                                        <span class="sub-description-text">kg</span>
                                    </b-col>

                                    <b-col cols="1" class="part-7">
                                        <span>前後軸重</span>
                                    </b-col>

                                    <b-col cols="1" class="part-8">
                                        <span>{{ handleRenderData('RrAxWgt') }}</span>
                                        <span class="sub-description-text">kg</span>
                                    </b-col>

                                    <b-col cols="2" class="part-9">
                                        <span>総排気量又は定格出力</span>
                                    </b-col>

                                    <b-col cols="2" class="part-10">
                                        <span>{{ handleRenderData('Displacement') }}</span>

                                        <div class="d-inline-flex" style="position: absolute; right: -30px; bottom: -1px;">
                                            <b-row class="w-100">
                                                <b-col cols="12">
                                                    <span class="d-flex">kW</span>
                                                </b-col>

                                                <b-col cols="12">
                                                    <span class="d-flex" style="margin-left: 5px;">L</span>
                                                </b-col>
                                            </b-row>
                                        </div>
                                    </b-col>
                                </b-row>
                            </b-col>

                            <b-col cols="12" class="col-10-adjustment">
                                <b-row class="w-100">
                                    <b-col cols="2" class="part-1">
                                        <span>燃料の種類</span>
                                    </b-col>

                                    <b-col cols="2" class="part-2">
                                        <span>{{ handleRenderData('FuelClass') }}</span>
                                    </b-col>

                                    <b-col cols="2" class="part-3">
                                        <span>型式指定番号</span>
                                    </b-col>

                                    <b-col cols="2" class="part-4">
                                        <span>{{ handleRenderData('ModelSpecifyNo') }}</span>
                                    </b-col>

                                    <b-col cols="2" class="part-5">
                                        <span>類別区別番号</span>
                                    </b-col>

                                    <b-col cols="2" class="part-6">
                                        <span>{{ handleRenderData('ClassifyAroundNo') }}</span>
                                    </b-col>
                                </b-row>
                            </b-col>

                            <b-col cols="12" class="col-14">
                                <b-row class="w-100">
                                    <b-col cols="12">
                                        <span style="font-weight: bold; font-size: 14px !important;">4. 備考</span>
                                    </b-col>
                                </b-row>
                            </b-col>

                            <b-col cols="12" class="col-15">
                                <b-row class="w-100" style="min-height: 260px;">
                                    <b-col cols="6" class="part-1">
                                        <span style="white-space: pre-line;">
                                            {{ handleRenderData('NoteInfo') }}
                                        </span>
                                    </b-col>

                                    <b-col cols="6" class="part-2">
                                        <span>以下余白</span>
                                    </b-col>
                                </b-row>
                            </b-col>
                        </b-row>

                        <b-row class="mt-3" style="width: 97%; margin: 0 auto;">
                            <b-col cols="12">
                                <span style="display: flex; margin-left: -15px;">【注意事項】</span>
                            </b-col>

                            <b-col cols="12" class="mt-3">
                                <span style="display: flex; margin-left: 5px;">記録事項はシステム登録時点の情報となります</span>
                            </b-col>

                            <b-col cols="12" class="mt-3 d-flex-inline">
                                <b-row class="last-card">
                                    <b-col cols="4" class="part-1">
                                        <span>車両ID</span>
                                    </b-col>

                                    <b-col cols="8" class="part-2">
                                        <span>{{ handleRenderData('CarId') }}</span>
                                    </b-col>
                                </b-row>
                            </b-col>
                        </b-row>
                    </div>
                </div>

                <b-col class="mt-3" cols="12">
                    <div class="pagination w-100">
                        <div class="show-pagination" style="width: 100% !important;">
                            <vPagination :aria-controls="'table-vehicle-master'" :current-page="vehicle_pagination.current_page" :per-page="vehicle_pagination.per_page" :total-rows="vehicle_pagination.total_rows" :next-class="'next'" :prev-class="'prev'" @currentPageChange="getCurrentPageThirdTab" />
                        </div>
                    </div>
                </b-col>

                <b-col class="mb-3" cols="12">
                    <b-row style="margin: 0 auto; width: 91.5% !important;">
                        <b-col cols="12" class="p-0 text-left">
                            <b-button class="functional-button btn-back" @click="onClickBackButton()">{{
                                $t('BUTTON.BACK')
                            }}</b-button>
                        </b-col>
                    </b-row>
                </b-col>
            </template>

            <template v-else-if="currentTab === 4">
                <div :class="vehicle_cost.length > 0 ? 'vehicle-cost-holder' : 'vehicle-cost-holder-empty'">
                    <b-table
                        show-empty
                        hover
                        responsive
                        bordered
                        no-local-sorting
                        no-sort-reset
                        no-border-collapse
                        :current-page="vehicle_cost_pagination.current_page"
                        :per-page="vehicle_cost_pagination.per_page"
                        :total-rows="vehicle_cost_pagination.total_rows"
                        :fields="vehicleCostFields"
                        :items="vehicle_cost"
                        class="table-vehicle-cost"
                    >
                        <template #cell(lease_depreciation)="data">
                            <span>{{ handleToLocalString(data.item.lease_depreciation) }}</span>
                        </template>

                        <template #cell(car_tax)="data">
                            <span>{{ handleToLocalString(data.item.car_tax) }}</span>
                        </template>

                        <template #cell(maintenance_lease)="data">
                            <span>{{ handleToLocalString(data.item.maintenance_lease) }}</span>
                        </template>

                        <template #empty="">
                            <span>{{ $t('TABLE_EMPTY') }}</span>
                        </template>
                    </b-table>
                </div>

                <div v-if="vehicle_cost_pagination.total_rows > 20">
                    <div class="pagination-advanced">
                        <div class="show-pagination">
                            <vPagination :aria-controls="'table-vehicle-cost'" :current-page="vehicle_cost_pagination.current_page" :per-page="vehicle_cost_pagination.per_page" :total-rows="vehicle_cost_pagination.total_rows" :next-class="'next'" :prev-class="'prev'" @currentPageChange="getCurrentPageFourthTab" />
                        </div>
                    </div>
                </div>

                <b-row style="margin: 0 auto; width: 90% !important;">
                    <b-col cols="12" class="p-0 text-left">
                        <b-button class="functional-button btn-back" @click="onClickBackButton()">
                            <span>{{ $t('BUTTON.BACK') }}</span>
                        </b-button>
                    </b-col>
                </b-row>
            </template>

            <template v-else-if="currentTab === 5">
                <div class="vehicle-depreciation-holder">
                    <b-table
                        show-empty
                        hover
                        bordered
                        no-local-sorting
                        no-sort-reset
                        no-border-collapse
                        :current-page="vehicle_depreciation_pagination.current_page"
                        :per-page="vehicle_depreciation_pagination.per_page"
                        :total-rows="vehicle_depreciation_pagination.total_rows"
                        :fields="vehicleDepreciationFields"
                        :items="vehicle_depreciation"
                        class="table-vehicle-depreciation"
                    >
                        <template #cell(vehicle_cost)="data">
                            <span>{{ handleToLocalString(data.item.vehicle_cost) }}</span>
                        </template>

                        <template #cell(lease_cost)="data">
                            <span>{{ handleToLocalString(data.item.lease_cost) }}</span>
                        </template>

                        <template #empty="">
                            <span>{{ $t('TABLE_EMPTY') }}</span>
                        </template>
                    </b-table>
                </div>

                <div v-if="vehicle_cost_pagination.total_rows > 20">
                    <div class="pagination-advanced">
                        <div class="show-pagination">
                            <vPagination :aria-controls="'table-vehicle-depreciation'" :current-page="vehicle_depreciation_pagination.current_page" :per-page="vehicle_depreciation_pagination.per_page" :total-rows="vehicle_depreciation_pagination.total_rows" :next-class="'next'" :prev-class="'prev'" @currentPageChange="getCurrentPageFifthTab" />
                        </div>
                    </div>
                </div>

                <b-row class="mt-3" style="margin: 0 auto; width: 90% !important;">
                    <b-col cols="12" class="p-0 text-left">
                        <b-button class="functional-button btn-back" @click="onClickBackButton()">
                            <span>{{ $t('BUTTON.BACK') }}</span>
                        </b-button>
                    </b-col>
                </b-row>
            </template>

            <template v-else-if="currentTab === 6">
                <b-row>
                    <b-col cols="12" class="mt-3">
                        <b-row>
                            <b-col cols="12" class="text-left">
                                <label for="door_number">整備工場</label>
                            </b-col>
                        </b-row>

                        <vInput v-model="formData.maintenance" :type="'text'" disabled placeholder="入力してください" :class-name="'vehicle_identification_number_2'" />
                    </b-col>

                    <b-col cols="12" class="mt-3">
                        <b-row>
                            <b-col cols="12" class="text-left">
                                <label for="door_number">整備種別</label>
                            </b-col>
                        </b-row>

                        <vInput v-model="formData.maintenance_type" :type="'text'" placeholder="" disabled :class-name="'door_number'" />
                    </b-col>
                    <b-col cols="12" class="mt-3">
                        <b-row>
                            <b-col cols="12" class="text-left">
                                <label for="door_number">整備予定日</label>
                            </b-col>
                        </b-row>

                        <vInput v-model="formData.scheduled_maintenance_date" :type="'text'" placeholder="" disabled :class-name="'door_number'" />
                    </b-col>

                    <b-col cols="12" class="mt-3">
                        <b-row>
                            <b-col cols="12" class="text-left">
                                <label for="door_number">整備実施日</label>
                            </b-col>
                        </b-row>

                        <vInput v-model="formData.maintenance_date" :type="'text'" placeholder="" disabled :class-name="'door_number'" />
                    </b-col>
                    <b-col cols="12" class="mt-3">
                        <b-row>
                            <b-col cols="12" class="text-left">
                                <label for="door_number">最終更新日</label>
                            </b-col>
                        </b-row>

                        <vInput v-model="formData.last_update_date" :type="'text'" placeholder="" disabled :class-name="'door_number'" />
                    </b-col>

                </b-row>
                <b-row>
                    <b-col cols="12" class="mt-5">
                        <div class="display_pdf">
								<iframe
									v-if="pdfUrl"
									:src="pdfUrl + '#toolbar=0&navpanes=0&zoom=page-width'"
									frameborder="0"
									class="pdf-iframe"
								/>
							</div>
                            <vButton
								:class="'btn-dowload'"
								@click.native="downloadFromUrl(selectedFile)"
							>
								<i class="far fa-download icon-history" />
								{{ $t('DATA_LIST_DETAIL_DOWNLOAD') }}
							</vButton>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col cols="12" class="mt-5">
                        <div class="table_history">
                            <b-table
                                id="table_history-list"
                                show-empty
                                striped
                                hover
                                responsive
                                bordered
                                no-local-sorting
                                no-sort-reset
                                :items="items"
                                :fields="fields"
                            >
                                <template #cell(operation)="scope">
                                    <div class="d-flex">
                                        <b-button class="modal-btn btn-cancel" @click="onClickDisplay(scope)">
                                            <i class="far fa-eye" />
                                        </b-button>
                                        <b-button class="modal-btn btn-cancel ml-2" @click="downloadFromUrl(scope.item.file)">
                                            <i class="far fa-download" />
                                        </b-button>
                                    </div>
                                </template>

                                <template #empty>
                                    <span>{{ $t('TABLE_EMPTY') }}</span>
                                </template>
                            </b-table>
                        </div>
                    </b-col>
                </b-row>
            </template>
        </div>

        <ModalViewPDF
			:pdf-title="$t('MODAL_DRIVING_RECORD_CERTIFICATE')"
			:pdf-url="driving_record"
			:is-show-modal="isShowLiscense"
		/>

        <b-modal id="modal-history-no-number-plate" v-model="modal.isShowNoNumberPlateHistory" size="lg" no-close-on-backdrop no-close-on-esc hide-footer :static="true" header-class="modal-custom-header" content-class="modal-custom-body" footer-class="modal-custom-footer">
            <template #modal-header>
                <b-row class="ml-3 w-100">
                    <b-col cols="12" class="text-right">
                        <i class="fas fa-times-circle button-custom-modal-header" @click="modal.isShowNoNumberPlateHistory = false" />
                    </b-col>
                </b-row>
            </template>

            <template #default>
                <b-table-simple>
                    <b-thead class="history-table-header">
                        <b-th>#</b-th>
                        <b-th>{{ $t('VEHICLE_MASTER.NO_NUMBER_PLATE_HISTORY.UPDATE_DATE') }}</b-th>
                        <b-th>{{ $t('VEHICLE_MASTER.NO_NUMBER_PLATE_HISTORY.NO_NUMBER_PLATE') }}</b-th>
                    </b-thead>

                    <b-tbody v-if="noNumberPlateHistoryData.length > 0">
                        <b-tr v-for="(item, index) in noNumberPlateHistoryData" :key="index">
                            <b-td>{{ noNumberPlateHistoryData.length - index }}</b-td>
                            <b-td>{{ item.date }}</b-td>
                            <b-td>{{ item.no_number_plate }}</b-td>
                        </b-tr>
                    </b-tbody>

                    <b-tbody v-else>
                        <b-tr>
                            <b-td colspan="3" class="empty-table-td">{{ $t('TABLE_EMPTY') }}</b-td>
                        </b-tr>
                    </b-tbody>
                </b-table-simple>
            </template>
        </b-modal>

        <b-modal id="modal-history-department" v-model="modal.isShowDepartmentHistory" size="lg" no-close-on-backdrop no-close-on-esc hide-footer :static="true" header-class="modal-custom-header" content-class="modal-custom-body" footer-class="modal-custom-footer">
            <template #modal-header>
                <b-row class="ml-3 w-100">
                    <b-col cols="12" class="text-right">
                        <i class="fas fa-times-circle button-custom-modal-header" @click="modal.isShowDepartmentHistory = false" />
                    </b-col>
                </b-row>
            </template>

            <template #default>
                <b-table-simple>
                    <b-thead class="history-table-header">
                        <b-th>#</b-th>
                        <b-th>{{ $t('VEHICLE_MASTER.DEPARTMENT_HISTORY.MOVED_DATE') }}</b-th>
                        <b-th>{{ $t('VEHICLE_MASTER.DEPARTMENT_HISTORY.DEPARTMENT') }}</b-th>
                    </b-thead>

                    <b-tbody v-if="departmentHistoryData.length > 0">
                        <b-tr v-for="(item, index) in departmentHistoryData" :key="index">
                            <b-td>{{ departmentHistoryData.length - index }}</b-td>
                            <b-td>{{ item.date }}</b-td>
                            <b-td>{{ handleGetDepartmentName(item.department_id) }}</b-td>
                        </b-tr>
                    </b-tbody>

                    <b-tbody v-else>
                        <b-tr>
                            <b-td colspan="3" class="empty-table-td">{{ $t('TABLE_EMPTY') }}</b-td>
                        </b-tr>
                    </b-tbody>
                </b-table-simple>
            </template>
        </b-modal>

        <b-modal id="modal-history-vehicle-certification" v-model="modal.isShowVehicleCertificationHistory" size="lg" no-close-on-backdrop no-close-on-esc hide-footer :static="true" header-class="modal-custom-header" content-class="modal-custom-body" footer-class="modal-custom-footer">
            <template #modal-header>
                <b-row class="ml-3 w-100">
                    <b-col cols="12" class="text-right">
                        <i class="fas fa-times-circle button-custom-modal-header" @click="modal.isShowVehicleCertificationHistory = false" />
                    </b-col>
                </b-row>
            </template>

            <template #default>
                <b-table-simple>
                    <b-thead class="history-table-header">
                        <b-th>
                            <template v-if="formData.vehicle_pdf_history_list_dates.length > 0">
                                <b-row class="w-100 justify-content-center">
                                    <b-col cols="4" class="d-flex justify-content-center">
                                        <b-button class="button-control-datetime" :disabled="formData.current_list_date_index === formData.vehicle_pdf_history_list_dates.length - 1" @click="handleProcessPrevious(formData.current_list_date_index)">
                                            <i class="fas fa-chevron-circle-left" />
                                        </b-button>
                                    </b-col>

                                    <b-col cols="4" class="d-flex justify-content-center align-items-center">
                                        <span>{{ formData.vehicle_pdf_history_list_dates[formData.current_list_date_index] }}</span>
                                    </b-col>

                                    <b-col cols="4" class="d-flex justify-content-center">
                                        <b-button class="button-control-datetime" :disabled="formData.current_list_date_index === 0" @click="handleProcessNext(formData.current_list_date_index)">
                                            <i class="fas fa-chevron-circle-right" />
                                        </b-button>
                                    </b-col>
                                </b-row>
                            </template>
                        </b-th>
                    </b-thead>

                    <b-tbody v-if="formData.vehicle_pdf_history_list_dates.length > 0">
                        <b-tr>
                            <b-td class="text-center">
                                <template v-if="formData.vehicle_pdf_history[formData.current_list_date_index]['file_extension'] === 'pdf'">
                                    <!-- <span class="custom-link" @click="handleDownloadFile()">〖{{ formData.vehicle_pdf_history[formData.current_list_date_index]['file_name'] }}〗</span> -->
                                    <vue-pdf-embed :page="1" :source="formData.vehicle_pdf_history[formData.current_list_date_index]['file_url']" />
                                </template>

                                <template v-else>
                                    <!-- <span class="custom-link" @click="handleDownloadFile()">〖{{ formData.vehicle_pdf_history[formData.current_list_date_index]['file_name'] }}〗</span> -->
                                    <img :src="formData.vehicle_pdf_history[formData.current_list_date_index]['file_url']">
                                </template>
                            </b-td>
                        </b-tr>
                    </b-tbody>

                    <b-tbody v-else>
                        <b-tr>
                            <b-td colspan="3" class="text-center empty-table-td">{{ $t('TABLE_EMPTY') }}</b-td>
                        </b-tr>
                    </b-tbody>
                </b-table-simple>
            </template>
        </b-modal>
    </b-overlay>
</template>

<script>
import CONST_ROLE from '@/const/role';
import vInput from '@/components/atoms/vInput';
import VI18NLabel from '@/components/atoms/i18nLabel';
import vPagination from '@/components/atoms/vPagination';
import vHeaderPage from '@/components/atoms/vHeaderPage.vue';
import vDropdownView from '@/components/atoms/vDropdownView';
import sampleImgUrl from '@/assets/images/vehicle_image.png';
import defaultImageUrl from '@/assets/images/default_image.png';
import { getDetailVehicle, getListDepartment } from '@/api/modules/vehicleMaster';
import { handleGetCertificateByVehicleTotalWeight, formatDateDisplay } from './helper/helper';
import vButton from '@/components/atoms/vButton';
import VuePdfEmbed from 'vue-pdf-embed/dist/vue2-pdf-embed.js';
// import vButton from '@/components/atoms/vButton';

import ModalViewPDF from '@/components/template/ModalViewPDF.vue';

const urlAPI = {
    apiGetDetailVehicle: '/vehicle',
    apiGetListDepartment: '/department/list-all',
};

export default {
    name: 'VehicleMasterDetail',
    components: {
        vInput,
        vButton,
        VI18NLabel,
        vHeaderPage,
        vPagination,
        vDropdownView,
        VuePdfEmbed,
        ModalViewPDF,
        // vButton,
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
            listDepartment: [],

            listDropdownTitle: [
                'VEHICLE_MASTER.VEHICLE_INFORMATION.TITLE',
                'VEHICLE_MASTER.INSURANCE_INFORMATION.TITLE',
                'VEHICLE_MASTER.MAINTENANCE_LEASE.TITLE',
                'VEHICLE_MASTER.VEHICLE_CERTIFICATION.TITLE',
            ],

            isShowLiscense: false,
            driving_record: '',
            pdfUrl: '',
            selectedFile: null,

            items: [
            ],

            formData: {
                department_id: '',
                no_number_plate: '',
                vehicle_identification_number: '',
                vehicle_identification_number_2: '',
                door_number: '',
                driving_classification: '',
                tonnage: '',
                voluntary_premium: '',
                truck_classification: '',
                manufactor: '',

                first_registration: '',

                inspection_expiration_date: '',

                vehicle_delivery_date: '',

                box_distinction: '',
                owner: '',
                etc_certification_number: '',
                etc_number: '',
                fuel_card_number_1: '',
                fuel_card_number_2: '',
                box_shape: '',
                mount: '',
                refrigerator: '',
                eva_type: '',
                gate: null,
                humidifier: null,
                type: '',
                motor: '',
                displacement: '',
                _length: '',
                width: '',
                height: '',
                maximum_loading_capacity: '',
                vehicle_total_weight: '',
                in_box_length: '',
                in_box_width: '',
                in_box_height: '',

                optional_detail: '',

                liability_insurance_period: '',

                insurance_company: '',
                agent: '',
                mileage: '',
                monthly_mileage: '',
                tire_size: '',
                battery_size: '',

                start_of_leasing: '',

                end_of_leasing: '',

                leasing_period: '',
                leasing_company: '',
                garage: '',
                tel: '',

                old_car_1: '',
                old_car_2: '',
                old_car_3: '',
                old_car_4: '',

                verify_certificate: '',
                verify_certificate_url: '',
                verify_certificate_remark: '',

                default_image_url: '',

                data_orc_ai: [],

                vehicle_inspection_cert: [],

                GrantdateE: '',
                RegGrantDateE: '',
                FirstRegDateE: '',
                ValidPeriodExpDateE: '',
                early_registration: false,
                d1d_not_installed: 0,

                // 2708
                maintenance_lease_fee: '',
                pdf_detail: '',
                selectedFileUrl: '',

                last_update_date: '',
                maintenance_date: '',
                scheduled_maintenance_date: '',
                maintenance_type: '',
                maintenance: '',

                vehicle_pdf_history: [],
                current_list_date_index: 0,
                vehicle_pdf_history_list_dates: [],
            },

            modal: {
                isShowNoNumberPlateHistory: false,
                isShowDepartmentHistory: false,
                isShowVehicleCertificationHistory: false,
            },

            datePicker: {
                year: 2022,
                month: 12,
                day: 31,
            },

            noNumberPlateHistoryData: [],

            departmentHistoryData: [],

            sampleImgUrl: sampleImgUrl,

            defaultImageUrl: defaultImageUrl,

            role: this.$store.getters.profile.roles[0],

            hasAccessEdit: [
                CONST_ROLE.CLERKS,
                CONST_ROLE.TL,
                CONST_ROLE.DEPARTMENT_OFFICE_STAFF,
                CONST_ROLE.GENERAL_AFFAIRS,
                CONST_ROLE.ACCOUNTING,
                CONST_ROLE.AM_SM,
                CONST_ROLE.QUALITY_CONTROL,
                CONST_ROLE.SITE_MANAGER,
                CONST_ROLE.HQ_MANAGER,
                CONST_ROLE.DEPARTMENT_MANAGER,
                CONST_ROLE.EXECUTIVE_OFFICER,
                CONST_ROLE.DIRECTOR,
                CONST_ROLE.DX_USER,
                CONST_ROLE.DX_MANAGER,
            ],

            optionsGate: [
                { value: 1, text: 'o' },
                { value: 0, text: 'x' },
            ],

            optionsHumidifier: [
                { value: 1, text: 'o' },
                { value: 0, text: 'x' },
            ],

            currentTab: 1,

            compulsory_pagination: {
                current_page: 1,
                per_page: 1,
                total_rows: 0,
            },

            vehicle_pagination: {
                current_page: 1,
                per_page: 20,
                total_rows: 0,
            },

            vehicle_cost_pagination: {
                current_page: 1,
                per_page: 20,
                total_rows: 0,
            },

            vehicle_depreciation_pagination: {
                current_page: 1,
                per_page: 20,
                total_rows: 0,
            },

            vehicle_cost: [],

            vehicle_depreciation: [],

            vin2_checkbox_status: false,
        };
    },
    computed: {
        lang() {
            return this.$store.getters.language;
        },

        fields() {
            return [
                {
                    key: 'date',
                    sortable: false,
                    label: this.$t('DATE_TIME'),
                    tdClass: 'td-affilitation-base',
                    thClass: 'th-affilitation-base',
                },
                {
                    key: 'registered_by',
                    sortable: false,
                    label: this.$t('REGISTERED_BY'),
                    tdClass: 'td-affilitation-base',
                    thClass: 'th-affilitation-base',
                },
                {
                    key: 'file_name',
                    sortable: false,
                    label: this.$t('FILE_NAME'),
                    tdClass: 'td-affilitation-base',
                    thClass: 'th-affilitation-base',
                },
                {
                    key: 'maintenance_type',
                    sortable: false,
                    label: '整備種別',
                    tdClass: 'td-affilitation-base',
                    thClass: 'th-affilitation-base',
                },
                {
                    key: 'scheduled_maintenance_date',
                    sortable: false,
                    label: '整備予定日',
                    tdClass: 'td-affilitation-base',
                    thClass: 'th-affilitation-base',
                },
                {
                    key: 'maintenance_date',
                    sortable: false,
                    label: '整備実施日',
                    tdClass: 'td-affilitation-base',
                    thClass: 'th-affilitation-base',
                },
                {
                    key: 'operation',
                    sortable: false,
                    label: this.$t('OPERATION'),
                    tdClass: 'td-affilitation-base',
                    thClass: 'th-support-base',
                },
            ];
        },
        vehicleTotalWeight() {
            return this.formData.vehicle_total_weight;
        },
        APP_URL() {
            return process.env.MIX_APP_URL;
        },
        vehicleCostFields() {
            return [
                { key: 'date', sortable: false, label: this.$t('YEAR_MONTH'), thClass: 'th th-date' },
                { key: 'lease_depreciation', sortable: false, label: this.$t('VEHICLE_LEASE'), thClass: 'th th-lease-depreciation' },
                { key: 'car_tax', sortable: false, label: this.$t('AUTOMOBILE_TAX'), thClass: 'th th-car-tax' },
                { key: 'maintenance_lease', sortable: false, label: this.$t('MAINTENANCE_LEASE'), thClass: 'th th-maintenance-lease' },
            ];
        },
        vehicleDepreciationFields() {
            return [
                { key: 'year_month', sortable: false, label: this.$t('YEAR_MONTH'), thClass: 'th th-date' },
                { key: 'vehicle_cost', sortable: false, label: this.$t('VEHICLE_DEPRECIATION'), thClass: 'th th-lease-depreciation' },
                { key: 'lease_cost', sortable: false, label: this.$t('LEASE_AMORTIZATION_EXPENSE'), thClass: 'th th-car-tax' },
            ];
        },
    },
    watch: {
        vehicleTotalWeight() {
            this.formData.certification = handleGetCertificateByVehicleTotalWeight(this.formData.vehicle_total_weight);
        },
    },
    created() {
        this.onCreatedComponent();
    },
    methods: {

        onClickDisplay(scope) {
            this.isShowLiscense = true;
            this.driving_record = scope.item.file_url;
            console.log('Display clicked', scope);
        },
        downloadFromUrl(file) {
            if (!file?.file_url) {
                this.$toast?.danger?.({ content: this.$t('NO_FILE_SELECTED') });
                return;
            }

            const a = document.createElement('a');
            a.href = file.file_url;
            a.download = file.file_name || 'document.pdf';
            a.target = '_blank';
            document.body.appendChild(a);
            a.click();
            a.remove();
        },
        handleRenderData(string) {
            const FIRST_CONDITION = this.formData.vehicle_inspection_cert[this.vehicle_pagination.current_page - 1];

            let result = '';

            if (FIRST_CONDITION) {
                if (FIRST_CONDITION[string] === null) {
                    result = '';
                } else {
                    if (string === 'created_at') {
                        result = this.formatDateToJapanese(FIRST_CONDITION[string]);
                    } else {
                        result = FIRST_CONDITION[string];
                    }
                }
            }

            return result;
        },
        formatDateToJapanese(dateString) {
            if (!dateString) {
                return '';
            }

            const date = new Date(dateString);
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');

            return `${year}年${month}月${day}日`;
        },
        async onCreatedComponent() {
            await this.handleGetListDepartment();
            await this.handleGetVehicleInformation();
        },
        getYear(yearn_number, year) {
            let result;

            if (yearn_number === '平成') {
                result = `${1988 + parseInt(year)}年`;
            } else {
                result = `${2018 + parseInt(year)}年`;
            }

            return result;
        },
        async handleGetVehicleInformation() {
            this.overlay.show = true;

            try {
                this.formData.vehicle_pdf_history = [];
                this.formData.vehicle_pdf_history_list_dates = [];

                const ID = this.$route.params.id;

                const URL = `${urlAPI.apiGetDetailVehicle}/${ID}`;

                const response = await getDetailVehicle(URL);

                if (response.code === 200) {
                    const DATA = response.data;

                    this.formData.vehicle_identification_number = DATA.vehicle_identification_number;

                    this.formData.vehicle_identification_number_2 = DATA.vehicle_identification_number_2;
                    if (this.formData.vehicle_identification_number_2) {
                        this.vin2_checkbox_status = true;
                    }

                    this.formData.door_number = DATA.door_number;
                    this.formData.department_id = this.handleGetDepartmentName(DATA.vehicle_department_history[0].department_id) || '';
                    this.formData.no_number_plate = DATA.plate_history[0] ? DATA.plate_history[0].no_number_plate : '';
                    this.formData.driving_classification = DATA.driving_classification;
                    this.formData.tonnage = DATA.tonnage;
                    this.formData.voluntary_premium = DATA.voluntary_premium;
                    this.formData.truck_classification = DATA.truck_classification;
                    this.formData.certification = null;
                    this.formData.manufactor = DATA.manufactor;
                    this.formData.first_registration = formatDateDisplay(DATA.first_registration);
                    this.formData.inspection_expiration_date = formatDateDisplay(DATA.inspection_expiration_date);
                    this.formData.vehicle_delivery_date = formatDateDisplay(DATA.vehicle_delivery_date);
                    this.formData.box_distinction = DATA.box_distinction;
                    this.formData.owner = DATA.owner;
                    this.formData.etc_certification_number = DATA.etc_certification_number;
                    this.formData.etc_number = DATA.etc_number;
                    this.formData.fuel_card_number_1 = DATA.fuel_card_number_1;
                    this.formData.fuel_card_number_2 = DATA.fuel_card_number_2;
                    this.formData.box_shape = DATA.box_shape;
                    this.formData.mount = DATA.mount;
                    this.formData.refrigerator = DATA.refrigerator;
                    this.formData.eva_type = DATA.eva_type;
                    this.formData.gate = [0, 1].includes(DATA.gate) ? DATA.gate : null;
                    this.formData.humidifier = [0, 1].includes(DATA.humidifier) ? DATA.humidifier : null;
                    this.formData.type = DATA.type;
                    this.formData.motor = DATA.motor;
                    this.formData.displacement = DATA.displacement;
                    this.formData._length = DATA['length'];
                    this.formData.width = DATA.width;
                    this.formData.height = DATA.height;
                    this.formData.maximum_loading_capacity = DATA.maximum_loading_capacity;
                    this.formData.vehicle_total_weight = DATA.vehicle_total_weight;
                    this.formData.in_box_length = DATA.in_box_length;
                    this.formData.in_box_width = DATA.in_box_width;
                    this.formData.in_box_height = DATA.in_box_height;
                    this.formData.scrap_date = formatDateDisplay(DATA.scrap_date);

                    this.formData.optional_detail = DATA.optional_detail;

                    this.formData.liability_insurance_period = DATA.liability_insurance_period;

                    this.formData.insurance_company = DATA.insurance_company;
                    this.formData.agent = DATA.agent;
                    this.formData.mileage = DATA.mileage;
                    this.formData.monthly_mileage = DATA.monthly_mileage;
                    this.formData.tire_size = DATA.tire_size;
                    this.formData.battery_size = DATA.battery_size;

                    this.formData.maintenance = DATA.maintenance_lease.length > 0 ? DATA.maintenance_lease[0].garage : '';
                    this.formData.maintenance_type = DATA.vehicle_maintenance_cost[0].type_text;
                    this.formData.scheduled_maintenance_date = formatDateDisplay(DATA.vehicle_maintenance_cost[0].scheduled_date);
                    this.formData.maintenance_date = formatDateDisplay(DATA.vehicle_maintenance_cost[0].maintained_date);
                    this.formData.last_update_date = formatDateDisplay(DATA.vehicle_maintenance_cost[0].updated_at);
                    this.pdfUrl = DATA.maintenance_vehicle_pdf_history.length > 0 ? DATA.maintenance_vehicle_pdf_history[0]?.file?.file_url : '';
                    this.selectedFile = DATA.maintenance_vehicle_pdf_history.length > 0 ? DATA.maintenance_vehicle_pdf_history[0]?.file : null;
                    this.items = DATA.maintenance_vehicle_pdf_history.map(item => {
                        return {
                            date: item?.file?.created_at,
                            registered_by: item?.user?.name || '',
                            file_name: item?.file?.file_name || '',
                            file_url: item?.file?.file_url || '',
                            file: item?.file || null,
                            maintenance_type: item?.vehicle_maintenance_cost.type_text || '',
                            scheduled_maintenance_date: formatDateDisplay(item?.vehicle_maintenance_cost.scheduled_date) || '',
                            maintenance_date: formatDateDisplay(item?.vehicle_maintenance_cost.maintained_date) || '',
                        };
                    });

                    if (DATA.maintenance_lease.length > 0) {
                        this.formData.start_of_leasing = formatDateDisplay(DATA.maintenance_lease[0].start_of_leasing);
                        this.formData.end_of_leasing = formatDateDisplay(DATA.maintenance_lease[0].end_of_leasing);
                        this.formData.leasing_period = DATA.maintenance_lease[0].leasing_period;
                        this.formData.leasing_company = DATA.maintenance_lease[0].leasing_company;
                        this.formData.garage = DATA.maintenance_lease[0].garage;
                        this.formData.tel = DATA.maintenance_lease[0].tel;
                    } else {
                        this.formData.start_of_leasing = '';
                        this.formData.end_of_leasing = '';
                        this.formData.leasing_period = '';
                        this.formData.leasing_company = '';
                        this.formData.garage = '';
                        this.formData.tel = '';
                    }

                    this.formData.old_car_1 = DATA.remark_old_car_1;
                    this.formData.old_car_2 = DATA.remark_old_car_2;
                    this.formData.old_car_3 = DATA.remark_old_car_3;
                    this.formData.old_car_4 = DATA.remark_old_car_4;

                    this.formData.mileage = DATA.mileage;

                    this.formData.verify_certificate = '';
                    this.formData.verify_certificate_remark = '';

                    this.formData.default_image_url = defaultImageUrl;
                    this.formData.pdf_detail = DATA.file_pdf?.file_url;

                    if (DATA.vehicle_pdf_history.length > 0) {
                        this.formData.vehicle_pdf_history = DATA.vehicle_pdf_history.map(item => {
                            this.formData.vehicle_pdf_history_list_dates.push(item.created_at);

                            return {
                                date: item.created_at,
                                file_id: item.file?.id,
                                file_url: item.file?.file_url,
                                file_path: item.file?.file_path,
                                file_name: item.file?.file_name,
                                file_size: item.file?.file_size,
                                file_extension: item.file?.file_extension,
                            };
                        });
                    } else {
                        this.formData.vehicle_pdf_history = [];
                        this.formData.vehicle_pdf_history_list_dates = [];
                    }

                    this.noNumberPlateHistoryData = DATA.plate_history;
                    this.departmentHistoryData = DATA.vehicle_department_history;

                    this.formData.data_orc_ai = DATA.data_orc_ai;

                    if (DATA.file_pdf && DATA.file_pdf.created_at) {
                        const createdAt = new Date(DATA.file_pdf.created_at);
                        this.datePicker.year = createdAt.getFullYear();
                        this.datePicker.month = createdAt.getMonth() + 1; // Tháng trong Date bắt đầu từ 0
                        this.datePicker.day = createdAt.getDate();
                    }

                    if (DATA.early_registration === 'その他') {
                        this.formData.early_registration = true;
                    } else {
                        this.formData.early_registration = false;
                    }

                    this.formData.d1d_not_installed = DATA.d1d_not_installed || 0;

                    for (let i = 0; i < DATA.data_orc_ai.length; i++) {
                        this.formData.data_orc_ai[i].file_path = `${process.env.MIX_LARAVEL_PATH}/storage/${this.formData.data_orc_ai[i].file_path}`;
                    }

                    this.compulsory_pagination.total_rows = DATA.data_orc_ai.length;

                    this.formData.vehicle_inspection_cert = DATA.vehicle_inspection_cert;

                    for (let i = 0; i < DATA.vehicle_inspection_cert.length; i++) {
                        this.formData.vehicle_inspection_cert[i].GrantdateE = `${this.handleRenderData('GrantdateE')} ${this.handleRenderData('GrantdateY')}年 ${this.handleRenderData('GrantdateM')}月  ${this.handleRenderData('GrantdateD')}日`;

                        this.formData.vehicle_inspection_cert[i].RegGrantDateE = `${this.handleRenderData('RegGrantDateE')} ${this.handleRenderData('RegGrantDateY')}年 ${this.handleRenderData('RegGrantDateM')}月  ${this.handleRenderData('RegGrantDateD')}日`;

                        this.formData.vehicle_inspection_cert[i].FirstRegDateE = `${this.handleRenderData('FirstRegDateE')} ${this.handleRenderData('FirstRegDateY')}年 ${this.handleRenderData('FirstRegDateM')}月`;

                        this.formData.vehicle_inspection_cert[i].ValidPeriodExpDateE = `${this.handleRenderData('ValidPeriodExpDateE')} ${this.handleRenderData('ValidPeriodExpDateY')}年 ${this.handleRenderData('ValidPeriodExpDateM')}月  ${this.handleRenderData('ValidPeriodExpDateD')}日`;
                    }

                    this.vehicle_pagination.total_rows = DATA.vehicle_inspection_cert.length;

                    this.vehicle_cost = DATA.vehicle_cost;

                    this.vehicle_cost_pagination.total_rows = DATA.vehicle_cost.length;

                    this.vehicle_depreciation = DATA.mahoujin;

                    this.vehicle_depreciation_pagination.total_rows = DATA.mahoujin.length;

                    for (let i = 0; i < this.vehicle_depreciation.length; i++) {
                        this.vehicle_depreciation[i]['month'] = this.formatMonthDay(this.vehicle_depreciation[i]['month']);
                        this.vehicle_depreciation[i]['year_month'] = `${this.vehicle_depreciation[i]['year']}-${this.vehicle_depreciation[i]['month']}`;
                    }

                    this.formData.maintenance_lease_fee = DATA.maintenance_lease_fee || '';
                }
            } catch (error) {
                console.log('error', error);
            }

            this.overlay.show = false;
        },
        async handleGetListDepartment() {
            try {
                const { code, data } = await getListDepartment(urlAPI.apiGetListDepartment);

                if (code === 200) {
                    this.listDepartment = data;
                } else {
                    this.listDepartment = [];
                }
            } catch (err) {
                this.listDepartment = [];
                console.log(err);
            }
        },
        onClickBackButton() {
            this.$router.push({ name: 'VehicleMasterList' });
        },
        onClickEditButton() {
            // eslint-disable-next-line object-curly-spacing
            this.$router.push({ name: 'VehicleMasterEdit', params: { id: this.$route.params.id } });
        },
        handleOpenHistoryModal(_option) {
            switch (_option) {
            case 0:
                this.modal.isShowNoNumberPlateHistory = true;
                break;
            case 1:
                this.modal.isShowDepartmentHistory = true;
                break;
            case 2:
                if (this.formData.vehicle_pdf_history && this.formData.vehicle_pdf_history.length > 0) {
                    // code goes here
                } else {
                    this.formData.selectedFileUrl = sampleImgUrl;
                }

                this.modal.isShowVehicleCertificationHistory = true;
                break;
            default:
                break;
            }
        },
        handleProcessNext(index) {
            if (index >= 0) {
                this.formData.current_list_date_index = index - 1;
            }
        },
        handleProcessPrevious(index) {
            if (index >= 0) {
                this.formData.current_list_date_index = index + 1;
            }
        },
        formatMonthDay(string) {
            if (string.toString().length < 2) {
                return '0' + string;
            } else {
                return string;
            }
        },
        handleGetDepartmentName(department_id) {
            let result = null;

            if (department_id) {
                this.listDepartment.filter(item => {
                    if (item.id === department_id) {
                        result = item.department_name;
                    }
                });
            }

            return result;
        },
        handleChangeCurrentTab(tab) {
            this.currentTab = tab;
        },
        getCurrentPageSecondTab(value) {
            if (value) {
                this.compulsory_pagination.current_page = value;

                this.handleGetVehicleInformation();
            }
        },
        getCurrentPageThirdTab(value) {
            if (value) {
                this.vehicle_pagination.current_page = value;

                this.handleGetVehicleInformation();
            }
        },
        getCurrentPageFourthTab(value) {
            if (value) {
                this.vehicle_cost_pagination.current_page = value;

                this.handleGetVehicleInformation();
            }
        },
        getCurrentPageFifthTab(value) {
            if (value) {
                this.vehicle_depreciation_pagination.current_page = value;

                this.handleGetVehicleInformation();
            }
        },
        handleToLocalString(string) {
            let result;

            if (string) {
                result = string.toLocaleString();
            } else {
                result = 0;
            }

            return result;
        },
        handleDownloadFile() {
            const file = this.formData.vehicle_pdf_history[this.formData.current_list_date_index];

            if (file) {
                const link = document.createElement('a');
                link.href = file.file_url;
                link.download = file.file_name;
                link.click();
            }
        },
    },
};
</script>

<style lang='scss' scoped>
@import "@/scss/variables";

::-webkit-scrollbar {
    width: 5px;
    height: 5px;
    border-radius: 45px;
}

.pagination-advanced {
    justify-content: center;
}

.show-pagination {
    display: flex;
    justify-content: center;
}

.vehicle-cost-holder {
    width: 90%;
    height: 650px;
    margin: 0 auto;
    overflow-y: auto;

    ::v-deep table {
        border-spacing: 0px;
        border-collapse: separate;
        margin-bottom: 0px !important;

        th {
            top: 0px;
            color: #FFFFFF;
            position: sticky;
            text-align: center;
            background-color: #0E0049;
            border: 1px solid #FFFFFF;
        }

        td {
            color: #000000;
            text-align: center;
            border: 1px solid #DDDDDD;
        }
    }
}

.vehicle-cost-holder-empty {
    width: 90%;
    margin: 0 auto;
    overflow-y: auto;

    ::v-deep table {
        border-spacing: 0px;
        border-collapse: separate;
        margin-bottom: 0px !important;

        th {
            top: 0px;
            color: #FFFFFF;
            position: sticky;
            text-align: center;
            background-color: #0E0049;
            border: 1px solid #FFFFFF;
        }

        td {
            color: #000000;
            text-align: center;
            border: 1px solid #DDDDDD;
        }
    }
}

.vehicle-depreciation-holder {
    width: 90%;
    margin: 0 auto;
    overflow-y: auto;
    max-height: 650px;

    ::v-deep table {
        border-spacing: 0px;
        border-collapse: separate;
        margin-bottom: 0px !important;

        th {
            top: 0px;
            color: #FFFFFF;
            position: sticky;
            text-align: center;
            background-color: #0E0049;
            border: 1px solid #FFFFFF;
        }

        td {
            color: #000000;
            text-align: center;
            border: 1px solid #DDDDDD;
        }
    }
}

.btn-history {
    font-weight: 400 !important;
    background-color: #fff !important;
    color: black;
    border: 1px solid #C4C4C4 !important;
    &:hover {
        background-color: #f5f5f5 !important;
    }
}

.tab-navigator {
    width: 90%;
    height: 40px;
    margin: 0 auto;
    margin-bottom: 40px;
}

.tabs-holder {
    // height: 100%;
    margin-left: 1px;
    border: 1px solid #018EAD;
}

.tab {
    text-align: center;
    background-color: #E9ECEF;
    display: flex;
    justify-content: center;
    align-items: center;

    &:hover {
        cursor: pointer;
        background-color: #ff8b1f89;
    }

    &:nth-child(2) {
        border-left: 1px solid #018EAD;
        border-right: 1px solid #018EAD;
    }

    &:nth-child(3) {
        border-right: 1px solid #018EAD;
    }

    &:nth-child(4) {
        border-right: 1px solid #018EAD;
    }
    &:nth-child(5) {
        border-right: 1px solid #018EAD;
    }
}

.tab-active {
    text-align: center;
    background-color: #FFFFFF;
    display: flex;
    justify-content: center;
    align-items: center;
}

.tab-text {
    font-size: 18px;
    margin-top: 5px;
    color: #018EAD;
    display: inline-flex;
}

.tab-text-active {
    font-size: 18px;
    font-weight: bold;
    color: #018EAD;
}

.compulsory-automobile-liability-insurance {
    width: 90%;
    margin: 0 auto;
    margin-bottom: 40px;
}

.vehicle-inspection-certificate {
    width: 90%;
    margin: 0 auto;
    margin-bottom: 40px;
}

.new-ui {
    width: 90%;
    margin: 0 auto;
    height: 1750px;
    padding-top: 50px;
    padding-bottom: 50px;
    border: 1px solid #DDDDDD;

    .sub-card {

        .sub-description-text {
            display: flex;
            float: right;
            margin-top: 10px;
            margin-right: 10px;
        }

        .card-one {
            width: 200px;
            height: 60px;
            margin-top: 20px;
            padding-top: 20px;
            text-align: center;
            border-top: 1px solid #DDDDDD;
            border-left: 1px solid #DDDDDD;
            border-bottom: 1px solid #DDDDDD;
        }

        .card-two {
            width: 300px;
            height: 60px;
            margin-top: 20px;
            padding-top: 20px;
            text-align: center;
            border: 1px solid #DDDDDD;
        }

        .card-content {
            width: 96%;
            margin: 0 auto;
            border: 1px solid #DDDDDD;

            span {
                font-size: 12px;
            }

            .col-1-prime {
                padding: 10px 10px;
                height: 45px;
                border-bottom: 1px solid #DDDDDD;
            }

            .col-2 {
                height: 45px;
                border-bottom: 1px solid #DDDDDD;

                & .part-1 {
                    height: 45px;
                    padding: 13px 10px;
                    border-right: 1px solid #DDDDDD;
                }

                & .part-2 {
                    text-align: left;
                    padding: 13px 0 0 10px;
                }
            }

            .col-3 {
                height: 45px;
                border-bottom: 1px solid #DDDDDD;

                & .part-1 {
                    padding: 13px 10px;
                    border-right: 1px solid #DDDDDD;
                }

                & .part-2 {
                    text-align: left;
                    padding: 13px 0 0 10px;
                    border-right: 1px solid #DDDDDD;
                }

                & .part-3 {
                    padding: 13px 10px;
                    border-right: 1px solid #DDDDDD;
                }

                & .part-4 {
                    text-align: left;
                    padding: 13px 0 0 10px;
                    border-right: 1px solid #DDDDDD;
                }

                & .part-5 {
                    padding: 13px 10px;
                    border-right: 1px solid #DDDDDD;
                }

                & .part-6 {
                    text-align: left;
                    padding: 13px 0 0 10px;
                }
            }

            .col-4 {
                padding: 10px 10px;
                height: 45px;
                border-bottom: 1px solid #DDDDDD;
            }

            .col-5 {
                height: 45px;
                border-bottom: 1px solid #DDDDDD;

                & .part-1 {
                    padding: 13px 10px;
                    border-right: 1px solid #DDDDDD;
                }

                & .part-2 {
                    text-align: left;
                    padding: 13px 0 0 10px;
                }
            }

            .col-type-a-1 {
                height: 45px;
                border-bottom: 1px solid #DDDDDD;

                & .part-1 {
                    padding: 13px 10px;
                    border-right: 1px solid #DDDDDD;
                }

                & .part-2 {
                    text-align: left;
                    padding: 13px 0 0 10px;
                }
            }

            .col-type-a-2 {
                height: 45px;
                border-bottom: 1px solid #DDDDDD;

                & .part-1 {
                    padding: 13px 10px;
                    border-right: 1px solid #DDDDDD;
                }

                & .part-2 {
                    text-align: left;
                    padding: 13px 0 0 10px;
                }
            }

            .col-6-prime {
                height: 45px;
                border-bottom: 1px solid #DDDDDD;

                & .part-1 {
                    padding: 13px 10px;
                    border-right: 1px solid #DDDDDD;
                }

                & .part-2 {
                    text-align: left;
                    padding: 13px 0 0 10px;

                    .float-right {
                        display: inline-flex;
                        float: right !important;
                    }
                }
            }

            .col-7 {
                padding: 10px 10px;
                height: 45px;
                border-bottom: 1px solid #DDDDDD;
            }

            .col-8 {
                height: 45px;
                border-bottom: 1px solid #DDDDDD;

                & .part-1 {
                    padding: 13px 10px;
                    border-right: 1px solid #DDDDDD;
                }

                & .part-2 {
                    text-align: left;
                    padding: 13px 0 0 10px;

                    .float-right {
                        display: inline-flex;
                        float: right !important;
                    }
                }
            }

            .col-9 {
                height: 45px;
                border-bottom: 1px solid #DDDDDD;

                & .part-1 {
                    height: 45px;
                    padding: 13px 10px;
                    border-right: 1px solid #DDDDDD;
                }

                & .part-2 {
                    text-align: left;
                    padding: 13px 0 0 10px;
                    border-right: 1px solid #DDDDDD;

                    .float-right {
                        display: inline-flex;
                        float: right !important;
                    }
                }

                & .part-3 {
                    padding: 13px 10px;
                    border-right: 1px solid #DDDDDD;
                }

                & .part-4 {
                    padding: 13px 10px;
                }
            }

            .col-10 {
                height: 45px;
                border-bottom: 1px solid #DDDDDD;

                & .part-1 {
                    padding: 13px 10px;
                    border-right: 1px solid #DDDDDD;
                }

                & .part-2 {
                    text-align: left;
                    padding: 13px 0 0 10px;
                    border-right: 1px solid #DDDDDD;
                }

                & .part-3 {
                    padding: 13px 10px;
                    border-right: 1px solid #DDDDDD;
                }

                & .part-4 {
                    text-align: left;
                    padding: 13px 0 0 10px;
                    border-right: 1px solid #DDDDDD;
                }

                & .part-5 {
                    padding: 13px 10px;
                    border-right: 1px solid #DDDDDD;
                }

                & .part-6 {
                    text-align: left;
                    padding: 13px 0 0 10px;
                }
            }

            .col-10-adjustment {
                height: 45px;
                border-bottom: 1px solid #DDDDDD;

                & .part-1 {
                    padding: 13px 10px;
                    border-right: 1px solid #DDDDDD;
                }

                & .part-2 {
                    text-align: left;
                    padding: 13px 0 0 10px;
                    border-right: 1px solid #DDDDDD;
                }

                & .part-3 {
                    padding: 13px 10px;
                    border-right: 1px solid #DDDDDD;
                }

                & .part-4 {
                    text-align: left;
                    padding: 13px 0 0 10px;
                    border-right: 1px solid #DDDDDD;
                }

                & .part-5 {
                    padding: 13px 10px;
                    border-right: 1px solid #DDDDDD;
                }

                & .part-6 {
                    text-align: left;
                    padding: 13px 0 0 10px;
                }
            }

            .col-11-prime {
                height: 45px;
                border-bottom: 1px solid #DDDDDD;

                & .part-1 {
                    padding: 13px 10px;
                    border-right: 1px solid #DDDDDD;
                }

                & .part-2 {
                    text-align: left;
                    padding: 13px 0 0 10px;
                    border-right: 1px solid #DDDDDD;

                    .float-right {
                        display: inline-flex;
                        float: right !important;
                        margin-right: 10px;
                    }
                }

                & .part-3 {
                    padding: 13px 10px;
                    border-right: 1px solid #DDDDDD;
                }

                & .part-4 {
                    text-align: left;
                    padding: 13px 0 0 10px;
                    border-right: 1px solid #DDDDDD;
                }

                & .part-5 {
                    padding: 13px 10px;
                    border-right: 1px solid #DDDDDD;
                }

                & .part-6 {
                    text-align: left;
                    padding: 13px 0 0 10px;
                }
            }

            .col-12-prime {
                height: 45px;
                border-bottom: 1px solid #DDDDDD;

                & .part-1 {
                    padding: 13px 10px;
                    border-right: 1px solid #DDDDDD;
                }

                & .part-2 {
                    text-align: left;
                    padding: 13px 0 0 10px;
                    border-right: 1px solid #DDDDDD;
                }

                & .part-3 {
                    padding: 13px 0 0 10px;
                    border-right: 1px solid #DDDDDD;
                }

                & .part-4 {
                    text-align: left;
                    padding: 13px 0 0 10px;
                    border-right: 1px solid #DDDDDD;
                }

                & .part-5 {
                    padding: 13px 10px;
                    border-right: 1px solid #DDDDDD;
                }

                & .part-6 {
                    text-align: left;
                    padding: 13px 0 0 10px;
                    border-right: 1px solid #DDDDDD;
                }

                & .part-7 {
                    height: 45px;
                    padding: 13px 10px;
                    border-right: 1px solid #DDDDDD;
                }

                & .part-8 {
                    text-align: left;
                    padding: 13px 0 0 10px;
                    border-right: 1px solid #DDDDDD;
                }

                & .part-9 {
                    height: 45px;
                    padding: 13px 10px;
                    border-right: 1px solid #DDDDDD;
                }

                & .part-10 {
                    text-align: left;
                    padding: 13px 0 0 10px;
                }
            }

            .col-13 {
                height: 45px;
                border-bottom: 1px solid #DDDDDD;

                & .part-1 {
                    height: 45px;
                    padding: 13px 10px;
                    border-right: 1px solid #DDDDDD;
                }

                & .part-2 {
                    height: 45px;
                    text-align: left;
                    padding: 13px 0 0 10px;
                    border-right: 1px solid #DDDDDD;
                }

                & .part-3 {
                    height: 45px;
                    padding: 13px 10px;
                    border-right: 1px solid #DDDDDD;
                }

                & .part-4 {
                    height: 45px;
                    text-align: left;
                    padding: 13px 0 0 10px;
                    border-right: 1px solid #DDDDDD;
                }

                & .part-5 {
                    height: 45px;
                    padding: 13px 10px;
                    border-right: 1px solid #DDDDDD;
                }

                & .part-6 {
                    height: 45px;
                    text-align: left;
                    padding: 13px 0 0 10px;
                    border-right: 1px solid #DDDDDD;
                }

                & .part-7 {
                    height: 45px;
                    padding: 13px 10px;
                    border-right: 1px solid #DDDDDD;
                }

                & .part-8 {
                    height: 45px;
                    text-align: left;
                    padding: 13px 0 0 10px;
                    border-right: 1px solid #DDDDDD;
                }

                & .part-9 {
                    height: 45px;
                    padding: 13px 10px;
                    border-right: 1px solid #DDDDDD;
                }

                & .part-10 {
                    height: 45px;
                    text-align: left;
                    padding: 13px 0 0 10px;
                }
            }

            .col-14 {
                padding: 10px 10px;
                height: 45px;
                border-bottom: 1px solid #DDDDDD;
            }

            .col-15 {
                padding: 10px 10px;

                & .part-1 {
                    height: 100%;
                    border-right: 3px dashed #DDDDDD;
                }
            }
        }

        .last-card {
            border: 1px solid #DDDDDD;
            width: 300px;
            height: 60px;
            margin-left: 5px;

            & .part-1 {
                padding: 18px 20px;
                border-right: 1px solid #DDDDDD;
            }

            & .part-2 {
                padding: 18px 20px;
            }
        }
    }
}

.image-holder {
    width: 100%;
    margin-bottom: 40px;
    background-color: #E9ECEF;
}

.table_history {
        ::v-deep #table_history-list {
            background-color: #fff !important;
            thead {
                tr {
                    th {
                        background-color: $tolopea;
                        color: $white;

                        text-align: center;
                        vertical-align: middle;
                    }

                    th.th-affilitation-base,
                    th.th-employee-id,
                    th.th-employee-name,
                    th.th-retirement-date {
                        min-width: 170px;
                    }
                    th.th-support-base {
                        width: 100px !important;
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
                    }
                }
            }
        }
}
.btn-dowload {
    width: 100%;
    margin-top: 10px;
    font-weight: 400 !important;
    background-color: #fff !important;
    color: black;
    border: 1px solid #C4C4C4 !important;
    &:hover {
        background-color: #f5f5f5 !important;
    }

    &:active {
            background-color: #fff !important;
        }

    &:focus {
            background-color: #fff !important;
        }
    &:disabled {
            background-color: #e9ecef !important;
        }
}
.display_pdf {
  width: 100%;
  height: 100vh; // hoặc 500px tùy layout
  border: 2px dashed #C4C4C4;
  border-radius: 5px;
  overflow: hidden;
}
.pdf-iframe {
  width: 100%;
  height: 100%;
}

.col-input {
    margin-bottom: 20px;
}

.pagination {
    margin-top: 30px;

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

::v-deep .input-group-text {
    font-weight: bolder;
}

.inline-attach-text {
    opacity: .6;
}

.footer {
    width: 90%;
    margin: 0 auto;
}

.functional-button {
    min-width: 150px;
    color: #FFFFFF;
    background-color: #FD8A32;
}

.functional-button:hover {
    opacity: .6;
    color: #FFFFFF;
    background-color: #FD8A32;
}

.fixed-height-input {
    height: 39px !important;
}

.no-border-right {
    border-right: none !important;
}

.no-border-left {
    border-left: none !important;
}

.no-border-radius-left {
    border-top-left-radius: 0px !important;
    border-bottom-left-radius: 0px !important;
}

.no-border-radius-right {
    border-top-right-radius: 0px !important;
    border-bottom-right-radius: 0px !important;
}

.history-button {
    color: #FFFFFF;
    min-width: 150px;
    margin-left: 10px;
    background-color: #FF8A1F;
    box-shadow: rgba(149, 157, 165, 0.2) 0px 8px 24px;
}

.history-button:hover {
    opacity: .6;
    color: #FFFFFF;
    cursor: pointer;
    background-color: #FF8A1F;
}

#preview {
    display: flex;
    justify-content: flex-start;
    align-items: center;
}

#preview img {
    max-width: 100%;
    max-height: 500px;
}

#preview-modal {
    display: flex;
    justify-content: center;
    align-items: center;
}

#preview-modal img {
    max-width: 100%;
    max-height: 500px;
}

.history-table-header {
    color: #FFFFFF;
    background-color: #0F0448;
}

#modal-history-no-number-plate {

    th,
    td {
        text-align: center;
    }
}

#modal-history-department {

    th,
    td {
        text-align: center;
    }
}

.button-custom-modal-header {
    font-size: 28px;
}

.button-custom-modal-header:hover {
    opacity: .6;
    cursor: pointer;
}

.button-change-year-month-day {
    font-size: 26px;
}

.button-change-year-month-day:hover {
    opacity: .6;
    cursor: pointer;
}

.empty-table-td {
    border-left: 1px solid #eeeeee;
    border-right: 1px solid #eeeeee;
    border-bottom: 1px solid #eeeeee;
}

.fa-history {
    margin-left: 10px;
    padding-right: 10px;
}

.fa-history:hover {
    cursor: pointer;
}

.history-text {
    margin-left: 10px;
    text-decoration: underline;
}

.history-text:hover {
    cursor: pointer;
}

::v-deep .vue-pdf-embed__page {
    &>canvas {
        width: 500px !important;
        height: 400px !important;
        object-fit: cover;
        object-position: center;
    }
}

.custom-link {
    &:hover {
        cursor: pointer;
        color: #0F0448;
        text-decoration: underline;
    }
}

.button-control-datetime {
    width: 30px;
    height: 30px;
    display: flex;
    border-radius: 45px;
    align-items: center;
    justify-content: center;
    background-color: #0E0049;

    i {
        font-size: 28px;
        color: #FFFFFF;
    }

    &:hover {
        opacity: 0.6;
    }
}

.custom-link {
    &:hover {
        cursor: pointer;
        color: #0F0448;
        text-decoration: underline;
    }
}
</style>
