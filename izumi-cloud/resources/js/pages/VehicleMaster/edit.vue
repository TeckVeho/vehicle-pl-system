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

            <vDropdownView :dropdown-title="listDropdownTitle">
                <template #dropdown-content>
                    <b-row>
                        <b-col cols="12" class="mt-3">
                            <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.VEHICLE_IDENTIFICATION_NUMBER'" />

                            <span style="color: red;">*</span>

                            <label for="vehicle_identification_number_2" class="d-flex align-items-center justify-content-center" style="float: right;">
                                <span>任意の車両番号を設置する場合はこちらをチェック</span>
                                <b-form-checkbox v-model="vin2_checkbox_status" size="xl" class="ml-3" />
                            </label>

                            <vInput v-model="formData.vehicle_identification_number" :type="'text'" placeholder="入力してください" disabled :class-name="'vehicle_identification_number'" />
                        </b-col>

                        <b-col v-if="vin2_checkbox_status" cols="12" class="mt-3">
                            <label for="vehicle_identification_number_2">
                                <span>車体番号(任意)</span>
                            </label>

                            <span style="color: red;">*</span>

                            <vInput v-model="formData.vehicle_identification_number_2" :type="'text'" placeholder="入力してください" :class-name="'vehicle_identification_number_2'" />
                        </b-col>

                        <b-col cols="12" class="mt-3">
                            <b-row>
                                <b-col cols="12" class="text-left">
                                    <label for="door_number">ドアナンバー</label>
                                </b-col>
                            </b-row>

                            <vInput v-model="formData.door_number" :type="'number'" placeholder="入力してください" :class-name="'door_number'" @keydown.native="validInputHalfWidthNumber" @paste.native="validInputHalfWidthNumberPaste" />
                        </b-col>

                        <b-col cols="12" class="mt-3">
                            <b-row>
                                <b-col cols="12" class="text-left">
                                    <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.NO_NUMBER_PLATE'" />
                                    <span style="color: red;">*</span>
                                </b-col>
                            </b-row>

                            <vInput v-model="formData.no_number_plate" :type="'text'" placeholder="入力してください" :class-name="'no_number_plate'" />
                        </b-col>

                        <b-col cols="12" class="mt-3">
                            <b-row>
                                <b-col cols="6">
                                    <b-row>
                                        <b-col cols="12" class="text-left">
                                            <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.DEPARTMENT_ID'" />
                                            <span style="color: red;">*</span>
                                        </b-col>
                                    </b-row>

                                    <vSelect v-model="formData.department_id" :type="'text'" :data-options="listDepartment" :class-name="'department_id'" />
                                </b-col>

                                <b-col cols="6">
                                    <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.DRIVING_CLASSIFICATION'" />
                                    <vSelect v-model="formData.driving_classification" :type="'text'" :data-options="listDrivingClassification" :class-name="'driving_classification'" />
                                </b-col>
                            </b-row>
                        </b-col>

                        <b-col cols="12" class="mt-3">
                            <b-row>
                                <b-col cols="6">
                                    <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.TONNAGE'" />
                                    <vSelect v-model="formData.tonnage" :type="'text'" :data-options="listTonnage" :class-name="'tonnage'" />
                                </b-col>

                                <b-col cols="6">
                                    <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.TRUCK_CLASSIFICATION'" />

                                    <span style="color: red;">*</span>

                                    <vSelect v-model="formData.truck_classification" :type="'text'" :data-options="listTruckClassification" :class-name="'truck_classification'" />
                                </b-col>
                            </b-row>
                        </b-col>

                        <b-col cols="12" class="mt-3">
                            <b-row>
                                <b-col cols="6">
                                    <label for="voluntary_premium">任意保険料</label>
                                    <vInput v-model="formData.voluntary_premium" :type="'number'" placeholder="入力してください" :class-name="'voluntary_premium'" />
                                </b-col>

                                <b-col cols="6">
                                    <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.CERTIFICATION'" />
                                    <vSelect v-model="formData.certification" :type="'text'" :data-options="listCertification" :class-name="'certification'" :disabled="true" />
                                </b-col>
                            </b-row>
                        </b-col>

                        <b-col cols="12" class="mt-3">
                            <b-row>
                                <b-col cols="12">
                                    <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.MANUFACTOR'" />
                                    <vInput v-model="formData.manufactor" :type="'text'" placeholder="入力してください" :class-name="'manufactor'" />
                                </b-col>
                            </b-row>
                        </b-col>

                        <b-col cols="12" class="mt-3">
                            <b-row>
                                <b-col cols="4">
                                    <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.FIRST_REGISTRATION'" />

                                    <span style="color: red;">*</span>

                                    <div class="custom-year-month-picker d-flex">
                                        <button type="button" aria-haspopup="dialog" aria-expanded="false" style="position: absolute;" class="btn h-auto">
                                            <svg viewBox="0 0 16 16" width="1em" height="1em" focusable="false" role="img" aria-label="calendar" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi-calendar b-icon bi">
                                                <g>
                                                    <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z" />
                                                </g>
                                            </svg>
                                        </button>

                                        <div class="custome-year-month-input" @click="handleYearMonthSelect()">
                                            <span v-if="formData.first_registration" class="d-inline-flex" style="color: #495057; margin-top: 8px;">
                                                {{ formData.first_registration }}
                                            </span>
                                            <span v-else class="d-inline-flex" style="color: #6C757D; margin-top: 8px;">選択してください</span>
                                        </div>
                                    </div>

                                    <template v-if="formData.first_registration">
                                        <MonthPickerInput v-show="false" no-default :lang="lang" style="z-index: 999 !important;" :default-year="formData.default_year" :default-month="formData.default_month" @change="handleAssignYearMonth" />
                                    </template>

                                    <template v-else>
                                        <MonthPickerInput v-show="false" no-default :lang="lang" style="z-index: 999 !important;" @change="handleAssignYearMonth" />
                                    </template>
                                </b-col>

                                <b-col cols="4">
                                    <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.INSPECTION_EXPIRATION_DATE'" />
                                    <span style="color: red;">*</span>
                                    <b-form-datepicker v-model="formData.inspection_expiration_date" :locale="lang" :calendar-width="`290px`" :label-no-date-selected="'選択してください'" :label-help="'カーソルキーを使用してカレンダーの日付をナビゲートする'" :date-format-options="{ month: '2-digit', day: '2-digit' }" class="inspection_expiration_date" />
                                </b-col>

                                <b-col cols="4">
                                    <VI18NLabel :text-label="'車両納入日'" />
                                    <span style="color: red;">*</span>
                                    <b-form-datepicker v-model="formData.vehicle_delivery_date" :locale="lang" :calendar-width="`290px`" :label-no-date-selected="'選択してください'" :label-help="'カーソルキーを使用してカレンダーの日付をナビゲートする'" :date-format-options="{ month: '2-digit', day: '2-digit' }" class="vehicle_delivery_date" />
                                </b-col>
                            </b-row>
                        </b-col>

                        <b-col cols="12" class="mt-3">
                            <b-row>
                                <b-col cols="6">
                                    <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.BOX_DISTINCTION'" />
                                    <vSelect v-model="formData.box_distinction" :type="'text'" :data-options="listBoxDistinction" />
                                </b-col>

                                <b-col cols="6">
                                    <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.OWNER'" />
                                    <vInput v-model="formData.owner" :type="'text'" placeholder="入力してください" :class-name="'owner'" />
                                </b-col>
                            </b-row>
                        </b-col>

                        <b-col cols="12" class="mt-3">
                            <b-row>
                                <b-col cols="6">
                                    <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.ETC_CERTIFICATION_NUMBER'" />
                                    <vInput v-model="formData.etc_certification_number" :type="'text'" placeholder="入力してください" :class-name="'etc_certification_number'" />
                                </b-col>

                                <b-col cols="6">
                                    <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.ETC_NUMBER'" />
                                    <vInput v-model="formData.etc_number" :type="'text'" placeholder="入力してください" :class-name="'etc_number'" />
                                </b-col>
                            </b-row>
                        </b-col>

                        <b-col cols="12" class="mt-3">
                            <b-row>
                                <b-col cols="6">
                                    <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.FUEL_CARD_NUMBER_1'" />
                                    <vInput v-model="formData.fuel_card_number_1" :type="'text'" placeholder="入力してください" :class-name="'fuel_card_number_1'" />
                                </b-col>

                                <b-col cols="6">
                                    <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.FUEL_CARD_NUMBER_2'" />
                                    <vInput v-model="formData.fuel_card_number_2" :type="'text'" placeholder="入力してください" :class-name="'fuel_card_number_2'" />
                                </b-col>
                            </b-row>
                        </b-col>

                        <b-col cols="12" class="mt-3">
                            <b-row>
                                <b-col cols="6">
                                    <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.BOX_SHAPE'" />
                                    <vInput v-model="formData.box_shape" :type="'text'" placeholder="入力してください" :class-name="'box_shape'" />
                                </b-col>

                                <b-col cols="6">
                                    <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.MOUNT'" />
                                    <vInput v-model="formData.mount" :type="'text'" placeholder="入力してください" :class-name="'mount'" />
                                </b-col>
                            </b-row>
                        </b-col>

                        <b-col cols="12" class="mt-3">
                            <b-row>
                                <b-col cols="6">
                                    <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.REFRIGERATOR'" />
                                    <vInput v-model="formData.refrigerator" :type="'text'" placeholder="入力してください" :class-name="'refrigerator'" />
                                </b-col>

                                <b-col cols="6">
                                    <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.EVA_TYPE'" />
                                    <vInput v-model="formData.eva_type" :type="'text'" placeholder="入力してください" :class-name="'eva_type'" />
                                </b-col>
                            </b-row>
                        </b-col>

                        <b-col cols="12" class="mt-3">
                            <b-row>
                                <b-col cols="6">
                                    <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.GATE'" />
                                    <b-form-select v-model="formData.gate" :options="optionsGate" class="gate">
                                        <template #first>
                                            <b-form-select-option :value="null" disabled>
                                                {{ $t('PLEASE_SELECT') }}
                                            </b-form-select-option>
                                        </template>
                                    </b-form-select>
                                </b-col>

                                <b-col cols="6">
                                    <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.HUMIDIFIER'" />
                                    <b-form-select v-model="formData.humidifier" :options="optionsHumidifier" class="humidifier">
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
                                    <vInput v-model="formData.type" :type="'text'" placeholder="入力してください" :class-name="'type'" />
                                </b-col>

                                <b-col cols="6">
                                    <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.MOTOR'" />
                                    <vInput v-model="formData.motor" :type="'text'" placeholder="入力してください" :class-name="'motor'" />
                                </b-col>
                            </b-row>
                        </b-col>

                        <b-col cols="12" class="mt-3">
                            <b-row>
                                <b-col cols="6">
                                    <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.DISPLACEMENT'" />
                                    <vInput v-model="formData.displacement" :type="'text'" placeholder="入力してください" :class-name="'displacement'" />
                                </b-col>

                                <b-col cols="6">
                                    <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.LENGTH'" />
                                    <vInput v-model="formData._length" :type="'text'" placeholder="入力してください" :class-name="'_length'" />
                                </b-col>
                            </b-row>
                        </b-col>

                        <b-col cols="12" class="mt-3">
                            <b-row>
                                <b-col cols="6">
                                    <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.WIDTH'" />
                                    <vInput v-model="formData.width" :type="'text'" placeholder="入力してください" :class-name="'width'" />
                                </b-col>

                                <b-col cols="6">
                                    <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.HEIGHT'" />
                                    <vInput v-model="formData.height" :type="'text'" placeholder="入力してください" :class-name="'height'" />
                                </b-col>
                            </b-row>
                        </b-col>

                        <b-col cols="12" class="mt-3">
                            <b-row>
                                <b-col cols="6">
                                    <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.MAXIMUM_LOADING_CAPACITY'" />
                                    <vInput v-model="formData.maximum_loading_capacity" :type="'text'" placeholder="入力してください" :class-name="'maximum_loading_capacity'" />
                                </b-col>

                                <b-col cols="6">
                                    <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.VEHICLE_TOTAL_WEIGHT'" />
                                    <vInput v-model="formData.vehicle_total_weight" :type="'number'" placeholder="入力してください" :class-name="'vehicle_total_weight'" @keydown.native="validInputNumber" />
                                </b-col>
                            </b-row>
                        </b-col>

                        <b-col cols="12" class="mt-3">
                            <b-row>
                                <b-col cols="6">
                                    <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.IN_BOX_LENGTH'" />
                                    <vInput v-model="formData.in_box_length" :type="'text'" placeholder="入力してください" :class-name="'in_box_length'" />
                                </b-col>

                                <b-col cols="6">
                                    <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.IN_BOX_WIDTH'" />
                                    <vInput v-model="formData.in_box_width" :type="'text'" placeholder="入力してください" :class-name="'in_box_width'" />
                                </b-col>
                            </b-row>
                        </b-col>

                        <b-col cols="12" class="mt-3">
                            <b-row>
                                <b-col cols="6">
                                    <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.IN_BOX_HEIGHT'" />
                                    <vInput v-model="formData.in_box_height" :type="'text'" placeholder="入力してください" :class-name="'in_box_height'" />
                                </b-col>

                                <b-col cols="6">
                                    <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.SCRAP_DATE'" />
                                    <b-form-datepicker v-model="formData.scrap_date" :date-format-options="{ year: 'numeric', month: '2-digit', day: '2-digit' }" :locale="lang" :calendar-width="`290px`" :label-no-date-selected="'選択してください'" :label-help="'カーソルキーを使用してカレンダーの日付をナビゲートする'" placeholder="入力してください" :class-name="'scrap_date'" />
                                </b-col>
                            </b-row>
                        </b-col>

                        <b-col cols="12" class="mt-3">
                            <b-row>
                                <b-col cols="6">
                                    <VI18NLabel :text-label="'期中登録'" />
                                    <b-form-checkbox id="early-registration-checkb≥ox" v-model="formData.early_registration" size="lg" :value="true" :unchecked-value="false" />
                                </b-col>

                                <b-col cols="6">
                                    <VI18NLabel :text-label="'D1D非搭載'" />
                                    <b-form-checkbox id="d1d-not-installed" v-model="formData.d1d_not_installed" size="lg" :value="1" :unchecked-value="0" />
                                </b-col>
                            </b-row>
                        </b-col>
                    </b-row>
                </template>

                <template #dropdown-content-1>
                    <b-row>
                        <b-col cols="12">
                            <b-row>
                                <b-col cols="6">
                                    <VI18NLabel :text-label="'VEHICLE_MASTER.INSURANCE_INFORMATION.OPTION_DETAIL'" />
                                    <vInput v-model="formData.optional_detail" :type="'text'" placeholder="入力してください" :class-name="'optional_detail'" />
                                </b-col>

                                <b-col cols="6">
                                    <VI18NLabel :text-label="'VEHICLE_MASTER.INSURANCE_INFORMATION.LIABILITY_INSURANCE_PERIOD'" />
                                    <vInput v-model="formData.liability_insurance_period" :type="'text'" placeholder="入力してください" :class-name="'liability_insurance_period'" />
                                </b-col>
                            </b-row>
                        </b-col>

                        <b-col cols="12" class="mt-3">
                            <b-row>
                                <b-col cols="6">
                                    <VI18NLabel :text-label="'VEHICLE_MASTER.INSURANCE_INFORMATION.INSURANCE_COMPANY'" />
                                    <vInput v-model="formData.insurance_company" :type="'text'" placeholder="入力してください" :class-name="'insurance_company'" />
                                </b-col>

                                <b-col cols="6">
                                    <VI18NLabel :text-label="'VEHICLE_MASTER.INSURANCE_INFORMATION.AGENT'" />
                                    <vInput v-model="formData.agent" :type="'text'" placeholder="入力してください" :class-name="'agent'" />
                                </b-col>
                            </b-row>
                        </b-col>

                        <b-col cols="12" class="mt-3">
                            <b-row>
                                <b-col cols="6">
                                    <VI18NLabel :text-label="'VEHICLE_MASTER.INSURANCE_INFORMATION.MILEAGE'" />
                                    <span style="color: red;">*</span>

                                    <vInput v-model="formData.mileage" :type="'text'" placeholder="入力してください" :class-name="'mileage'" />
                                </b-col>

                                <b-col cols="6">
                                    <VI18NLabel :text-label="'VEHICLE_MASTER.INSURANCE_INFORMATION.MONTHLY_MILEAGE'" />
                                    <vInput v-model="formData.monthly_mileage" :type="'text'" placeholder="入力してください" disabled :class-name="'monthly_mileage'" />
                                </b-col>
                            </b-row>
                        </b-col>

                        <b-col cols="12" class="mt-3">
                            <b-row>
                                <b-col cols="6">
                                    <VI18NLabel :text-label="'VEHICLE_MASTER.INSURANCE_INFORMATION.TIRE_SIZE'" />
                                    <vInput v-model="formData.tire_size" :type="'text'" placeholder="入力してください" :class-name="'tire_size'" />
                                </b-col>

                                <b-col cols="6">
                                    <VI18NLabel :text-label="'VEHICLE_MASTER.INSURANCE_INFORMATION.BATTERY_SIZE'" />
                                    <vInput v-model="formData.battery_size" :type="'text'" placeholder="入力してください" :class-name="'battery_size'" />
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
                                    <b-form-datepicker v-model="formData.start_of_leasing" :locale="lang" :calendar-width="`290px`" :label-no-date-selected="'選択してください'" :label-help="'カーソルキーを使用してカレンダーの日付をナビゲートする'" :date-format-options="{ month: '2-digit', day: '2-digit' }" class="start_of_leasing" />
                                </b-col>

                                <b-col cols="6">
                                    <VI18NLabel :text-label="'VEHICLE_MASTER.MAINTENANCE_LEASE.END_OF_LEASING'" />
                                    <b-form-datepicker v-model="formData.end_of_leasing" :locale="lang" :calendar-width="`290px`" :label-no-date-selected="'選択してください'" :label-help="'カーソルキーを使用してカレンダーの日付をナビゲートする'" :date-format-options="{ month: '2-digit', day: '2-digit' }" class="end_of_leasing" />
                                </b-col>
                            </b-row>
                        </b-col>

                        <b-col cols="12" class="mt-3">
                            <b-row>
                                <b-col cols="6">
                                    <VI18NLabel :text-label="'VEHICLE_MASTER.MAINTENANCE_LEASE.LEASING_PERIOD'" />
                                    <vInput v-model="formData.leasing_period" :type="'text'" placeholder="入力してください" :class-name="'leasing_period'" />
                                </b-col>

                                <b-col cols="6">
                                    <VI18NLabel :text-label="'VEHICLE_MASTER.MAINTENANCE_LEASE.LEASING_COMANY'" />
                                    <vInput v-model="formData.leasing_company" :type="'text'" placeholder="入力してください" :class-name="'leasing_company'" />
                                </b-col>
                            </b-row>
                        </b-col>

                        <b-col cols="12" class="mt-3">
                            <b-row>
                                <b-col cols="6">
                                    <label for="maintenance_lease_fee">メンテナンスリース料</label>
                                    <vInput v-model="formData.maintenance_lease_fee" :type="'number'" placeholder="入力してください" :class-name="'maintenance_lease_fee'" />
                                </b-col>

                                <b-col cols="6">
                                    <VI18NLabel :text-label="'VEHICLE_MASTER.MAINTENANCE_LEASE.GARAGE'" />
                                    <vInput v-model="formData.garage" :type="'text'" placeholder="入力してください" :class-name="'garage'" />
                                </b-col>
                            </b-row>
                        </b-col>

                        <b-col cols="12" class="mt-3">
                            <b-row>
                                <b-col cols="12">
                                    <VI18NLabel :text-label="'VEHICLE_MASTER.MAINTENANCE_LEASE.TEL'" />
                                    <vInput v-model="formData.tel" :type="'number'" placeholder="入力してください" :class-name="'tel'" />
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
                            <b-form-textarea v-model="formData.old_car_1" rows="4" placeholder="" />
                        </b-col>
                    </b-row>
                </template>
            </vDropdownView>

            <b-row class="footer">
                <b-col cols="6" class="p-0 text-left">
                    <b-button class="functional-button btn-back-main" @click="onClickBackButton()">{{ $t('BUTTON.BACK')
                        }}</b-button>
                </b-col>

                <b-col cols="6" class="p-0 text-right">
                    <b-button class="functional-button btn-save-main" @click="onClickSaveButton()">{{ $t('BUTTON.SAVE')
                        }}</b-button>
                </b-col>
            </b-row>

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
        </div>
    </b-overlay>
</template>

<script>
import vInput from '@/components/atoms/vInput';
import vSelect from '@/components/atoms/vSelect';
import VI18NLabel from '@/components/atoms/i18nLabel';
import vHeaderPage from '@/components/atoms/vHeaderPage.vue';
import vDropdownView from '@/components/atoms/vDropdownView';
import sampleImgUrl from '@/assets/images/vehicle_image.png';
import defaultImageUrl from '@/assets/images/default_image.png';
import VuePdfEmbed from 'vue-pdf-embed/dist/vue2-pdf-embed.js';

import { MakeToast } from '@/utils/MakeToast';
import { MonthPickerInput } from 'vue-month-picker';
import { getDetailVehicle, getListDepartment, updateVehicle } from '@/api/modules/vehicleMaster';

import { handleGetCertificateByVehicleTotalWeight, formatDateDisplay } from './helper/helper';

import { validInputNumber, validInputHalfWidthNumber, validInputHalfWidthNumberPaste } from '@/utils/handleInput';

const urlAPI = {
    apiGetDetailVehicle: '/vehicle',
    apiUpdateVehicle: '/vehicle',
    apiGetListDepartment: '/department/list-all',
};

export default {
    name: 'VehicleMasterEdit',
    components: {
        vInput,
        vSelect,
        VI18NLabel,
        vHeaderPage,
        vDropdownView,
        MonthPickerInput,
        VuePdfEmbed,
    },
    data() {
        return {
            validInputNumber,
            validInputHalfWidthNumber,
            validInputHalfWidthNumberPaste,

            overlay: {
                show: false,
                variant: 'light',
                opacity: 1,
                blur: '1rem',
                rounded: 'sm',
            },

            listDepartment: [
                { value: null, text: '選択してください', disabled: true },
            ],

            listDrivingClassification: [
                { value: null, text: '選択してください', disabled: true },
                { value: 'CVS', text: 'CVS' },
                { value: 'SBJ', text: 'SBJ' },
                { value: 'パスコ', text: 'パスコ' },
                { value: 'レカミエ', text: 'レカミエ' },
                { value: '一般', text: '一般' },
                { value: '共配', text: '共配' },
                { value: '受け倉', text: '受け倉' },
                { value: '富士エコー', text: '富士エコー' },
                { value: '食品', text: '食品' },
                { value: '菱倉', text: '菱倉' },
            ],

            listTonnage: [
                { value: null, text: '選択してください', disabled: true },
                { value: '2トン超', text: '2トン超' },
                { value: '2トン以下', text: '2トン以下' },
            ],

            listTruckClassification: [
                { value: null, text: '選択してください', disabled: true },
                { value: '1トン以下', text: '1トン以下' },
                { value: '2トン以下', text: '2トン以下' },
                { value: '3トン（ＣＶＳ）', text: '3トン（ＣＶＳ）' },
                { value: '3トン（ゲート）', text: '3トン（ゲート）' },
                { value: '4トン', text: '4トン' },
                { value: '4トン（ＣＶＳ）', text: '4トン（ＣＶＳ）' },
                { value: '4トン（ゲート）', text: '4トン（ゲート）' },
                { value: '6トン（増ｔ）', text: '6トン（増ｔ）' },
                { value: '13トン', text: '13トン' },
            ],

            listBoxDistinction: [
                { value: null, text: '選択してください', disabled: true },
                { value: 'ドライ', text: 'ドライ' },
                { value: 'バン', text: 'バン' },
                { value: '冷蔵', text: '冷蔵' },
                { value: '冷凍', text: '冷凍' },
            ],

            listCertification: [
                { value: null, text: '自動入力', disabled: true },
                { value: '普通', text: '普通' },
                { value: '準中型', text: '準中型' },
                { value: '中型', text: '中型' },
                { value: '大型', text: '大型' },
            ],

            listDropdownTitle: [
                'VEHICLE_MASTER.VEHICLE_INFORMATION.TITLE',
                'VEHICLE_MASTER.INSURANCE_INFORMATION.TITLE',
                'VEHICLE_MASTER.MAINTENANCE_LEASE.TITLE',
                'VEHICLE_MASTER.VEHICLE_CERTIFICATION.TITLE',
            ],

            formData: {
                department_id: null,
                no_number_plate: '',
                vehicle_identification_number: '',
                vehicle_identification_number_2: '',
                door_number: '',
                driving_classification: null,
                tonnage: null,
                voluntary_premium: '',
                truck_classification: null,
                certification: null,
                manufactor: '',
                first_registration: '',
                inspection_expiration_date: '',
                vehicle_delivery_date: '',

                box_distinction: null,
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

                default_year: 2023,
                default_month: 1,

                early_registration: false,
                d1d_not_installed: 0,

                // 2708
                maintenance_lease_fee: '',

                vehicle_pdf_history: [],
                current_list_date_index: 0,
                vehicle_pdf_history_list_dates: [],
            },

            modal: {
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

            optionsGate: [
                { value: 1, text: 'o' },
                { value: 0, text: 'x' },
            ],

            optionsHumidifier: [
                { value: 1, text: 'o' },
                { value: 0, text: 'x' },
            ],

            vin2_checkbox_status: false,
        };
    },
    computed: {
        lang() {
            return this.$store.getters.language;
        },
        vehicleTotalWeight() {
            return this.formData.vehicle_total_weight;
        },
    },
    watch: {
        vehicleTotalWeight() {
            this.formData.certification = handleGetCertificateByVehicleTotalWeight(this.formData.vehicle_total_weight);
        },
    },
    created() {
        document.body.addEventListener('click', (event) => {
            const monthPickerInput = document.getElementsByClassName('month-picker-input')[0];
            const monthPickerDefault = document.getElementsByClassName('month-picker--default')[0];
            const monthPickerInputContainer = document.getElementsByClassName('month-picker-input-container')[0];
            const monthPicker = document.getElementsByClassName('month-picker')[0];

            if (monthPickerInputContainer && monthPicker) {
                if (!monthPickerInputContainer.contains(event.target)) {
                    monthPickerDefault.style.top = '10px';
                    monthPickerInput.style.display = 'none';
                    monthPickerDefault.style.display = 'none';
                    monthPickerInputContainer.style.display = 'none';
                } else if (monthPicker.contains(event.target)) {
                    monthPickerDefault.style.top = '10px';
                    monthPickerInput.style.display = 'none';
                    monthPickerDefault.style.display = 'none';
                    monthPickerInputContainer.style.display = 'none';
                }
            }
        }, true);

        window.scrollTo(0, 0);

        this.onCreatedComponent();
    },
    methods: {
        handleAssignYearMonth(date) {
            let result = '';

            if (date) {
                result = `${date.year}-${this.formatMonth(date.monthIndex)}`;
            }

            this.formData.first_registration = result;
        },
        formatMonth(month) {
            if (month < 10) {
                return `0${month}`;
            } else {
                return month;
            }
        },
        async onCreatedComponent() {
            await this.handleGetListDepartment();
            await this.handleGetVehicleInformation();
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
                    this.formData.no_number_plate = DATA.plate_history[0] ? DATA.plate_history[0].no_number_plate : '';
                    this.formData.department_id = DATA.vehicle_department_history[0].department_id || '';
                    this.formData.driving_classification = DATA.driving_classification;
                    this.formData.tonnage = DATA.tonnage;
                    this.formData.voluntary_premium = DATA.voluntary_premium;
                    this.formData.truck_classification = DATA.truck_classification;
                    this.formData.certification = null;
                    this.formData.manufactor = DATA.manufactor;
                    this.formData.first_registration = formatDateDisplay(DATA.first_registration);

                    if (this.formData.first_registration) {
                        const date = this.formData.first_registration.split('-');

                        let year = date[0];
                        let month = date[1];

                        year = parseInt(year);
                        month = parseInt(month);

                        this.formData.default_year = year;
                        this.formData.default_month = month;
                    }

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

                    this.formData.verify_certificate = '';
                    this.formData.verify_certificate_remark = '';

                    this.formData.default_image_url = defaultImageUrl;

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

                    if (DATA.early_registration === 'その他') {
                        this.formData.early_registration = true;
                    } else {
                        this.formData.early_registration = false;
                    }

                    this.formData.d1d_not_installed = DATA.d1d_not_installed || 0;

                    // 2708
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
                    for (let i = 0; i < data.length; i++) {
                        this.listDepartment.push(
                            { value: data[i].id, text: data[i].department_name }
                        );
                    }
                }
            } catch (err) {
                console.log(err);
            }
        },
        async onClickSaveButton() {
            this.overlay.show = true;

            if (this.handleValidateData(this.formData)) {
                try {
                    const ID = this.$route.params.id;

                    const URL = `${urlAPI.apiUpdateVehicle}/${ID}`;

                    const DATA = {
                        vehicle_identification_number: this.formData.vehicle_identification_number,
                        door_number: this.formData.door_number,
                        no_number_plate: this.formData.no_number_plate,
                        department_id: this.formData.department_id,
                        driving_classification: this.formData.driving_classification,
                        tonnage: this.formData.tonnage,
                        voluntary_premium: this.formData.voluntary_premium,
                        truck_classification: this.formData.truck_classification,
                        truck_classification_number: this.formData.truck_classification_number,
                        truck_classification_2: this.formData.truck_classification_2,
                        manufactor: this.formData.manufactor,
                        first_registration: this.handleFormatDate(this.formData.first_registration),
                        inspection_expiration_date: this.handleFormatFullDate(this.formData.inspection_expiration_date),
                        vehicle_delivery_date: this.handleFormatFullDate(this.formData.vehicle_delivery_date),
                        box_distinction: this.formData.box_distinction,
                        owner: this.formData.owner,
                        etc_certification_number: this.formData.etc_certification_number,
                        etc_number: this.formData.etc_number,
                        fuel_card_number_1: this.formData.fuel_card_number_1,
                        fuel_card_number_2: this.formData.fuel_card_number_2,
                        box_shape: this.formData.box_shape,
                        mount: this.formData.mount,
                        refrigerator: this.formData.refrigerator,
                        eva_type: this.formData.eva_type,
                        gate: this.formData.gate,
                        humidifier: this.formData.humidifier,
                        type: this.formData.type,
                        motor: this.formData.motor,
                        displacement: this.formData.displacement,
                        length: this.formData._length,
                        width: this.formData.width,
                        height: this.formData.height,
                        maximum_loading_capacity: this.formData.maximum_loading_capacity,
                        vehicle_total_weight: this.formData.vehicle_total_weight,
                        in_box_length: this.formData.in_box_length,
                        in_box_width: this.formData.in_box_width,
                        in_box_height: this.formData.in_box_height,
                        scrap_date: this.handleFormatFullDate(this.formData.scrap_date),

                        optional_detail: this.formData.optional_detail,
                        liability_insurance_period: this.formData.liability_insurance_period,
                        insurance_company: this.formData.insurance_company,
                        agent: this.formData.agent,

                        voluntary_insurance: this.formData.voluntary_insurance,

                        monthly_mileage: this.formData.monthly_mileage,
                        tire_size: this.formData.tire_size,
                        battery_size: this.formData.battery_size,

                        remark_old_car_1: this.formData.old_car_1,
                        remark_old_car_2: this.formData.old_car_2,
                        remark_old_car_3: this.formData.old_car_3,
                        remark_old_car_4: this.formData.old_car_4,

                        leasing: {
                            start_of_leasing: this.handleFormatFullDate(this.formData.start_of_leasing),
                            end_of_leasing: this.handleFormatFullDate(this.formData.end_of_leasing),
                            leasing_period: this.formData.leasing_period,
                            leasing_company: this.formData.leasing_company,
                            garage: this.formData.garage,
                            tel: this.formData.tel,
                        },

                        mileage: this.formData.mileage,

                        driving_recorder: this.formData.driving_recorder,

                        early_registration: null,
                        d1d_not_installed: this.formData.d1d_not_installed || 0,

                        // 2708
                        maintenance_lease_fee: this.formData.maintenance_lease_fee,
                    };

                    if (this.vin2_checkbox_status) {
                        DATA['vehicle_identification_number_2'] = this.formData.vehicle_identification_number_2;
                    }

                    if (this.formData.early_registration) {
                        DATA['early_registration'] = 'その他';
                    } else {
                        DATA['early_registration'] = null;
                    }

                    const response = await updateVehicle(URL, DATA);

                    if (response.code === 200) {
                        MakeToast({
                            variant: 'success',
                            title: this.$t('SUCCESS'),
                            content: '編集が完了しました',
                        });

                        this.$router.push({ name: 'VehicleMasterList' });
                    } else {
                        MakeToast({
                            variant: 'warning',
                            title: this.$t('WARNING'),
                            content: response.message || 'Update Vehicle Failed',
                        });
                    }
                } catch (error) {
                    console.log(error);
                }
            }

            this.overlay.show = false;
        },
        async onClickBackButton() {
            this.$router.push({ name: 'VehicleMasterList' });
        },
        checkLeapYear(year) {
            if ((year % 4 === 0) && (year % 100 !== 0) || (year % 400 === 0)) {
                return true;
            } else {
                return false;
            }
        },
        formatMonthDay(string) {
            if (string.toString().length < 2) {
                return '0' + string;
            } else {
                return string;
            }
        },
        handleValidateData(DATA) {
            let isPass = false;

            if (DATA.no_number_plate === '') {
                MakeToast({
                    variant: 'warning',
                    title: this.$t('WARNING'),
                    content: '車両Noを入力してください',
                });
            } else if (DATA.department_id === null) {
                MakeToast({
                    variant: 'warning',
                    title: this.$t('WARNING'),
                    content: '拠点を選択してください',
                });
            } else if (DATA.truck_classification === null) {
                MakeToast({
                    variant: 'warning',
                    title: this.$t('WARNING'),
                    content: 't 車を選択してください',
                });
            } else if (DATA.first_registration === '') {
                MakeToast({
                    variant: 'warning',
                    title: this.$t('WARNING'),
                    content: '初年度登録を選択してください',
                });
            } else if (DATA.inspection_expiration_date === '') {
                MakeToast({
                    variant: 'warning',
                    title: this.$t('WARNING'),
                    content: '車検満了日を選択してください',
                });
            } else if (this.vin2_checkbox_status && DATA.vehicle_identification_number_2 === '') {
                MakeToast({
                    variant: 'warning',
                    title: this.$t('WARNING'),
                    content: '車体番号2を入力してください',
                });
            } else {
                isPass = true;
            }

            return isPass;
        },
        handleYearMonthSelect() {
            const monthPickerInputContainer = document.getElementsByClassName('month-picker-input-container')[0];

            const monthPickerInput = document.getElementsByClassName('month-picker-input')[0];

            monthPickerInputContainer.style.display = 'block';

            monthPickerInput.style.display = 'none';

            const monthPickerDefault = document.getElementsByClassName('month-picker--default')[0];

            monthPickerDefault.style.display = 'block';
            monthPickerDefault.style.top = '10px';

            const monthPickerYear = document.getElementsByClassName('month-picker__year')[0];

            const buttonOne = monthPickerYear.getElementsByTagName('button')[0];
            buttonOne.setAttribute('style', 'padding-bottom: 52px !important;');
            buttonOne.setAttribute('class', 'previousButton');

            const buttonTwo = monthPickerYear.getElementsByTagName('button')[1];
            buttonTwo.setAttribute('style', 'padding-bottom: 52px !important;');
            buttonOne.setAttribute('class', 'nextButton');

            const p = monthPickerYear.getElementsByTagName('p')[0];
            p.setAttribute('style', 'margin-bottom: 30px !important;');
        },
        handleFormatFullDate(date) {
            if (date) {
                const result = new Date(date);
                const year = result.getFullYear();
                let month = result.getMonth() + 1;
                let day = result.getDate();

                if (month < 10) {
                    month = '0' + month;
                }

                if (day < 10) {
                    day = '0' + day;
                }

                return year + '-' + month + '-' + day;
            }

            return date;
        },
        handleFormatDate(date) {
            if (date) {
                const result = new Date(date);
                const year = result.getFullYear();
                let month = result.getMonth() + 1;

                if (month < 10) {
                    month = '0' + month;
                }

                return year + '-' + month;
            }

            return date;
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
    },
};
</script>

<style lang='scss' scoped>
@import "@/scss/variables";

::v-deep .month-picker-input-container {
    width: 100% !important;
}

::v-deep .month-picker-input {
    width: 100% !important;
    height: 38px !important;
}

::v-deep .month-picker--default .month-picker__container {
    z-index: 999 !important;
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

.custome-year-month-input {
    height: 40px;
    padding-left: 50px;
    border: 1px solid rgb(206, 212, 218);
    border-radius: 4px;
    width: 100%;
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
