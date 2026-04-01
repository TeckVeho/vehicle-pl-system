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
				<p class="text-loading">{{ $t('PLEASE_WAIT') }}</p>
			</div>
		</template>

		<div class="employee-master-detail">
			<!-- <vHeaderPage class="employee-master-detail__title-header">
				{{ $t('PAGE_TITLE.EMPLOYEE_MASTER') }}
				</vHeaderPage> -->

			<div class="employee-master-detail__basic-data">
				<vTitleHeader :label="'EMPLOYEE_MASTER_DETAIL_TITLE_BASIC_DATA'" />

				<b-row>
					<b-col>
						<div class="item-data">
							<label for="employee-id">
								{{ $t('EMPLOYEE_MASTER_DETAIL_LABEL_EMPLOYEE_ID') }}
							</label>
							<b-form-input id="employee-id" v-model="employee.id" disabled />
						</div>
					</b-col>
					<b-col>
						<div class="item-data">
							<label for="employee-name">
								{{ $t('EMPLOYEE_MASTER_DETAIL_LABEL_EMPLOYEE_NAME') }}
							</label>
							<b-form-input id="employee-name" v-model="employee.name" disabled />
						</div>
					</b-col>
				</b-row>
				<b-row>
					<b-col>
						<div class="item-data">
							<label for="name-phonetic">
								{{ $t('EMPLAYEE_MASTER_DETAIL_LABEL_NAME_PHONETIC') }}
							</label>
							<b-form-input id="name-phonetic" v-model="employee.name_phonetic" disabled />
						</div>
					</b-col>
					<b-col>
						<div class="item-data">
							<label for="gender">
								{{ $t('EMPLOYEE_MASTER_DETAIL_LABEL_GENDER') }}
							</label>
							<b-form-input id="gender" v-model="employee.gender" disabled />
						</div>
					</b-col>
				</b-row>
				<b-row>
					<b-col>
						<div class="item-data">
							<label for="birthday">
								{{ $t('EMPLOYEE_MASTER_DETAIL_LABEL_BIRTHDAY') }}
							</label>
							<b-form-input id="birthday" v-model="employee.birthday" disabled />
						</div>
					</b-col>
					<b-col>
						<div class="item-data">
							<label for="hire-start-date">
								{{ $t('EMPLOYEE_MASTER_DETAIL_LABEL_HIRE_START_DATE') }}
							</label>
							<b-form-input id="hire-start-date" v-model="employee.hireStartDate" disabled />
						</div>
					</b-col>
				</b-row>

				<b-row>
					<b-col>
						<div class="item-data">
							<label for="date_of_appointment">
								{{ $t('EMPLAYEE_MASTER_DETAIL_LABEL_DATE_OF_APPOINTMENT') }}
							</label>
							<b-form-datepicker v-model="employee.date_of_appointment" :locale="lang" :calendar-width="`290px`" :label-no-date-selected="'選択してください'" :label-help="'カーソルキーを使用してカレンダーの日付をナビゲートする'" :date-format-options="{ month: '2-digit', day: '2-digit' }" class="inspection_expiration_date" disabled />

							<!-- <b-form-input id="date_of_appointment" v-model="employee.date_of_appointment" disabled /> -->
						</div>
					</b-col>
					<b-col>
						<div class="item-data">
							<label for="address">
								{{ $t('EMPLAYEE_MASTER_DETAIL_LABEL_ADDRESS') }}
							</label>
							<b-form-input id="address" v-model="employee.address" disabled />
						</div>
					</b-col>
				</b-row>

				<b-row>
					<b-col>
						<div class="item-data">
							<label for="contact-phone-number">
								{{ $t('EMPLAYEE_MASTER_DETAIL_LABEL_CONTEACT_PHONE_NUMBER') }}
							</label>
							<b-form-input id="contact-phone-number" v-model="employee.contact_phone_number" disabled />
						</div>
					</b-col>
					<b-col>
						<div class="item-data">
							<label for="working-type">
								{{ $t('EMPLOYEE_MASTER_DETAIL_LABEL_WORKING_TYPE') }}
							</label>
							<b-form-input id="working-type" v-model="employee.workingType" disabled />
						</div>
					</b-col>
				</b-row>
				<b-row>
					<b-col>
						<div class="item-data">
							<label for="employee-type">
								{{ $t('EMPLOYEE_MASTER_DETAIL_LABEL_EMPLOYEE_TYPE') }}
							</label>
							<b-form-input id="employee-type" v-model="employee.employeeType" disabled />
						</div>
					</b-col>
					<b-col>
						<div class="item-data">
							<label for="role-type">役職</label>
							<b-form-select
								id="employee-role"
								v-model="equipmentData.employee_role"
								disabled
								:options="employee_role_options"
							/>
						</div>
					</b-col>
				</b-row>

				<b-row>
					<b-col>
						<div class="item-data">
							<label for="license-type">
								{{ $t('EMPLOYEE_MASTER_DETAIL_LABEL_LICENSE_TYPE') }}
							</label>
							<b-form-input id="license-type" v-model="employee.licenseType" disabled />
						</div>
					</b-col>
					<b-col>
						<div class="item-data">
							<label for="retirement-date">
								{{ $t('EMPLOYEE_MASTER_DETAIL_LABEL_RETIREMENT_DATE') }}
							</label>
							<b-form-input id="retirement-date" v-model="employee.retirementDate" disabled />
						</div>
					</b-col>
				</b-row>

				<b-row>
					<b-col>
						<div class="item-data title_new-driver-training">
							<label for="contact-phone-number">
								{{ $t('EMPLAYEE_MASTER_DETAIL_DRIVER_LICENSE_INFORMATION') }}
							</label>
						</div>
					</b-col>
				</b-row>

				<b-row>
					<b-col>
						<div class="item-data">
							<label for="previous-employment-history">
								{{ $t('EMPLAYEE_MASTER_DETAIL_LABEL_PREVIOUS_EMPLOYMENT_HISTORY') }}
							</label>
							<b-form-input id="previous-employment-history" v-model="employee.previous_employment_history" disabled />
						</div>
					</b-col>
				</b-row>

				<b-row>
					<b-col>
						<div class="item-data mt-3">
							<span style="text-decoration: underline; cursor: pointer;" @click="handleShowModalHistoryPDF()">{{ $t('EMPLAYEE_MASTER_DETAIL_LABEL_APTITUDE_TEST_DATE') }}</span>
						</div>
					</b-col>
				</b-row>

				<b-row>
					<b-col>
						<div class="item-data">
							<label for="previous-employment-history">
								{{ $t('EMPLAYEE_MASTER_DETAIL_LABEL_FIRST_TIMER') }}
							</label>
							<b-form-input id="previous-employment-history" v-model="employee.first_time" disabled />
						</div>
					</b-col>
					<b-col>
						<div class="item-data">
							<label for="aptitude-test-date">
								{{ $t('EMPLAYEE_MASTER_DETAIL_LABEL_ELIGIBLE_AGE') }}
							</label>
							<b-form-input id="aptitude-test-date" v-model="employee.aligible_age" disabled />
						</div>
					</b-col>
				</b-row>

				<b-row>
					<b-col>
						<div class="item-data">
							<label for="previous-employment-history">
								{{ $t('EMPLAYEE_MASTER_DETAIL_LABEL_SPECIAL') }}
							</label>
							<b-form-input id="previous-employment-history" v-model="employee.special" disabled />
						</div>
					</b-col>
					<b-col>
						<div class="item-data">
							<label for="aptitude-test-date">
								{{ $t('EMPLAYEE_MASTER_DETAIL_LABEL_GENERAL') }}
							</label>
							<b-form-input id="aptitude-test-date" v-model="employee.general" disabled />
						</div>
					</b-col>
				</b-row>

				<b-row>
					<b-col>
						<div class="item-data mt-3">
							<span style="text-decoration: underline; cursor: pointer;" @click="handleShowModalHealthExamHistoryPDF()">{{ $t('EMPLAYEE_MASTER_DETAIL_LABEL_TITLE_SHOW_MODAL') }}</span>
						</div>
					</b-col>
				</b-row>

				<b-row class="mt-3">
					<b-col>
						<div class="item-data">
							<label for="medical-examination-date">
								{{ $t('EMPLAYEE_MASTER_DETAIL_LABEL_MEDICAL_EXAMINATION_DATE') }}
							</label>
							<b-form-input id="medical-examination-date" v-model="employee.medical_examination_date" disabled />
						</div>
					</b-col>
					<b-col>
						<div class="item-data">
							<label for="welfare-express">
								法定福利費
							</label>
							<b-form-input id="welfare-express" :value="formatNumber(employee.welfareExpense)" disabled />
						</div>
					</b-col>
				</b-row>
				<b-row>
					<b-col>
						<div class="item-data">
							<span style="text-decoration: underline; cursor: pointer;" @click="modalWelfareExpense = true">履歴</span>
						</div>
					</b-col>
				</b-row>
				<b-row>
					<b-col>
						<!-- <div class="item-data title_new-driver-training">
							<label for="contact-phone-number">
							{{ $t('EMPLAYEE_MASTER_DETAIL_LABEL_NEWDRIVER_TRAINING') }}
							</label>
							</div> -->

						<div class="item-data">
							<p>{{ $t('EMPLAYEE_MASTER_DETAIL_LABEL_NEWDRIVER_TRANING_CLASSROOM') }}</p>
							<b-form-radio-group
								v-model="employee.selected_classroom"
								:options="options"
								class="mb-3"
								value-field="item"
								text-field="name"
								disabled-field="notEnabled"
							/>
						</div>

						<div class="item-data">
							<p>{{ $t('EMPLAYEE_MASTER_DETAIL_LABEL_NEWDRIVER_TRANING_PRACTICAL') }}</p>
							<b-form-radio-group
								v-model="employee.selected_practical"
								:options="options_practical"
								class="mb-3"
								value-field="item"
								text-field="name"
								disabled-field="notEnabled"
							/>
						</div>

					</b-col>
				</b-row>

				<b-row>
					<b-col>
						<div class="item-data">
							<p>{{ $t('EMPLAYEE_MASTER_DETAIL_LABEL_AGE_APPROPRIATE_INTERVIEW') }}</p>
							<b-form-radio-group
								v-model="employee.age_appropriate_interview"
								:options="options"
								class="mb-3"
								value-field="item"
								text-field="name"
								disabled-field="notEnabled"
							/>
						</div>
					</b-col>
				</b-row>

			</div>

			<div class="employee-master-detail__working-data">
				<b-col>
					<vTitleHeader :label="'EMPLOYEE_MASTER_DETAIL_TITLE_WORKING_DATA'" />

					<div class="item-data">
						<label for="affiliation-base-support-base">
							{{ $t('EMPLOYEE_MASTER_DETAIL_LABEL_AFFILIATION_BASE_SUPPORT_BASE') }}
						</label>

						<div class="show-working-data">
							<b-row>
								<b-col v-for="item in working_data" :key="`node-${item.id}`" cols="6" sm="6" md="4" lg="2" xl="2" class="zone-node">
									<vNode :item="item" />
								</b-col>
							</b-row>
						</div>
					</div>

					<div class="item-data">
						<span class="text-link" @click="onClickChangeHistory()">
							{{ $t('EMPLOYEE_MASTER_DETAIL_LABEL_CHANGING_HISTORY') }}
						</span>
					</div>
				</b-col>
			</div>

			<div class="employee-master-detail__device-data">
				<b-col>
					<vTitleHeader :label="'EMPLOYEE_MASTER_DETAIL_TITLE_DEVICE_DATA'" />

					<div class="item-data">
						<label for="device-type">デバイス種類</label>
						<b-form-input
							id="device-type"
							:value="deviceHistoryData[0] ? deviceHistoryData[0].device_type : ''"
							disabled
						/>
					</div>

					<div class="item-data">
						<label for="tel">電話番号</label>
						<b-form-input id="tel" :value="deviceHistoryData[0] ? deviceHistoryData[0].tel : ''" disabled />
					</div>

					<div class="item-data">
						<label for="email">メールアドレス</label>
						<b-form-input id="email" :value="employee.email" disabled />
					</div>

					<div class="item-data">
						<label for="android-id">アンドロイドID</label>
						<b-form-input id="android-id" :value="deviceHistoryData[0] ? deviceHistoryData[0].android_id : ''" disabled />
					</div>

					<div class="item-data">
						<label for="imei-number">IMEI番号</label>
						<b-form-input
							id="imei-number"
							:value="deviceHistoryData[0] ? deviceHistoryData[0].imei_number : ''"
							disabled
						/>
					</div>

					<div class="item-data">
						<label for="model-name">モデル名</label>
						<b-form-input id="model-name" :value="deviceHistoryData[0] ? deviceHistoryData[0].model_name : ''" disabled />
					</div>

					<div class="item-data">
						<b-row class="mt-3">
							<b-col cols="12" class="text-left">
								<span role="button" class="d-inline-flex history-text" @click="handleOpenModalHistory()">{{
									$t('BUTTON.HISTORY') }}</span>
							</b-col>
						</b-row>
					</div>
				</b-col>
			</div>

			<div class="employee-master-detail__equipment-data">
				<b-col>
					<vTitleHeader label="備品情報" />

					<div class="item-data d-flex justify-content-end">
						<b-button class="history-button" @click="handleShowModalEquipmentDataHistory()">
							<i class="fas fa-history" />
							<span>履歴</span>
						</b-button>
					</div>

					<div class="item-data">
						<label for="company-car">社用車</label>
						<b-form-input id="company-car" :value="equipmentData.company_car" disabled />
					</div>

					<div class="item-data">
						<label for="etc-card">ETC カード</label>
						<b-form-input id="etc-card" :value="equipmentData.etc_card" disabled />
					</div>

					<div class="item-data">
						<label for="fuel-card">燃料カード</label>
						<b-form-input id="fuel-card" :value="equipmentData.fuel_card" disabled />
					</div>

					<div class="item-data">
						<label for="other">その他</label>
						<b-form-input id="other" :value="equipmentData.other" disabled />
					</div>
				</b-col>
			</div>

			<div class="employee-master-detail__list-file-pdf">
				<b-row>
					<b-col>
						<vTitleHeader :label="$t('DETAIL_PDF')" />
					</b-col>
				</b-row>
				<div class="title_history">
					<vButton
						:class="'btn-summit-filter'"
						@click.native="handleShowModalPDF()"
					>
						<i class="far fa-upload icon_upload" />
						{{ $t('BUTTON.UPLOAD') }}
					</vButton>
				</div>

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
									<span>表示</span>
								</b-button>
								<b-button class="modal-btn btn-cancel ml-2" @click="handleShowModalAllocation(scope)">
									<i class="far fa-file-import" />
									<span>振り分け</span>
								</b-button>
								<b-button class="modal-btn btn-cancel ml-2" @click="handleShowModalDelete(scope)">
									<i class="far fa-trash" />
									<span>削除</span>
								</b-button>
							</div>
						</template>

						<template #empty>
							<span>{{ $t('TABLE_EMPTY') }}</span>
						</template>
					</b-table>
				</div>
			</div>

			<b-row>
				<b-col cols="6">
					<b-button class="button-return-to-list" @click="handleButtonReturnToListClicked()">
						<span>戻る</span>
					</b-button>
				</b-col>

				<b-col cols="6" class="d-flex justify-content-end" style="padding-right: 30px;">
					<b-button class="button-navigate-to-edit" @click="handleNavigateToEditScreen()">
						<span>編集</span>
					</b-button>
				</b-col>
			</b-row>
		</div>

		<ModalViewPDF
			:pdf-title="$t('MODAL_HEALTH_EXAMINATION_RESULT_NOTIFICATION')"
			:pdf-url="detail_url_pdf"
			:is-show-modal.sync="isShowPDFView"
		/>

		<ModalViewPDF
			:pdf-title="$t('MODAL_HEALTH_EXAMINATION_RESULT_NOTIFICATION')"
			:pdf-url="history_health_pdf"
			:is-show-modal.sync="isShowHistoryPDF"
		/>

		<b-modal
			id="modal-show-history-pdf"
			v-model="modalShowHistoryPDF"
			static
			size="xl"
			hide-footer
			no-close-on-esc
			no-close-on-backdrop
		>
			<template #modal-title>
				<div class="modal-title">{{ $t('EDIT_HISTORY') }}</div>
			</template>

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
					:items="itemsHistory"
					:fields="fieldsHistory"
				>
					<template #cell(operation)="scope">
						<div class="d-flex justify-content-center">
							<b-button class="modal-btn btn-cancel" @click="onClickDisplayHistoryPDF(scope)">
								<i class="far fa-eye" />
							</b-button>
							<b-button class="modal-btn btn-cancel ml-2" @click="handleShowModalDeleteAptitude(scope)">
								<i class="far fa-trash" />
							</b-button>
						</div>
					</template>

					<template #empty>
						<span>{{ $t('TABLE_EMPTY') }}</span>
					</template>
				</b-table>
			</div>

			<b-row>
				<b-col cols="12" class="d-flex justify-content-end mt-5 mr-5">
					<vButton
						:class="'btn-history'"
						@click.native="handleCloseModalHistoryPDF()"
					>
						{{ $t('CLOSE') }}
					</vButton>
				</b-col>
			</b-row>
		</b-modal>

		<b-modal
			id="modal-show--table-history-pdf"
			v-model="modalShowHealthExamHistoryPDF"
			static
			size="xl"
			hide-footer
			no-close-on-esc
			no-close-on-backdrop
		>
			<template #modal-title>
				<div class="modal-title">{{ $t('EDIT_HISTORY') }}</div>
			</template>

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
					:items="itemsHistoryHealthExam"
					:fields="fieldsHistoryHealthExam"
				>
					<template #cell(operation)="scope">
						<div class="d-flex justify-content-center">
							<b-button class="modal-btn btn-cancel" @click="onClickDisplayHistoryPDF(scope)">
								<i class="far fa-eye" />
							</b-button>
							<b-button class="modal-btn btn-cancel ml-2" @click="handleShowModalDeleteHealthExam(scope)">
								<i class="far fa-trash" />
							</b-button>
						</div>
					</template>

					<template #empty>
						<span>{{ $t('TABLE_EMPTY') }}</span>
					</template>
				</b-table>
			</div>

			<b-row>
				<b-col cols="12" class="d-flex justify-content-end mt-5 mr-5">
					<vButton
						:class="'btn-history'"
						@click.native="handleCloseHealthExam()"
					>
						{{ $t('CLOSE') }}
					</vButton>
				</b-col>
			</b-row>
		</b-modal>

		<b-modal
			id="modal-delete-aptitude"
			v-model="isShowModalDeleteAptitude"
			static
			size="md"
			hide-footer
			no-close-on-esc
			no-close-on-backdrop
		>
			<template #modal-title>
				<div class="modal-title">削除確認</div>
			</template>

			<div>
				<span>
					以下のファイルを削除してもよろしいですか？
				</span>
				<div>
					<span class="font-weight-bold mt-3">
						{{ fileNameDeleteAptitude }}
					</span>
				</div>
			</div>

			<b-row>
				<b-col cols="12" class="d-flex justify-content-end mt-5 mr-5">
					<vButton
						:class="'btn-history'"
						@click.native="handleCloseModalDeleteAptitude()"
					>
						{{ $t('CLOSE') }}
					</vButton>
					<vButton
						:class="'btn-summit-filter ml-2'"
						@click.native="handleDeleteAptitude()"
					>
						{{ $t('BUTTON.DELETE') }}
					</vButton>
				</b-col>
			</b-row>
		</b-modal>

		<b-modal
			id="modal-delete-health"
			v-model="isShowModalDeleteHealthExam"
			static
			size="md"
			hide-footer
			no-close-on-esc
			no-close-on-backdrop
		>
			<template #modal-title>
				<div class="modal-title">削除確認</div>
			</template>

			<div>
				<span>
					以下のファイルを削除してもよろしいですか？
				</span>
				<div>
					<span class="font-weight-bold mt-3">
						{{ fileNameDeleteHealthExam }}
					</span>
				</div>
			</div>

			<b-row>
				<b-col cols="12" class="d-flex justify-content-end mt-5 mr-5">
					<vButton
						:class="'btn-history'"
						@click.native="handleCloseModalDeleteHealthExam()"
					>
						{{ $t('CLOSE') }}
					</vButton>
					<vButton
						:class="'btn-summit-filter ml-2'"
						@click.native="handleDeleteHealthExam()"
					>
						{{ $t('BUTTON.DELETE') }}
					</vButton>
				</b-col>
			</b-row>
		</b-modal>

		<b-modal
			id="modal-add-pdf"
			v-model="modalAddPDF"
			static
			size="md"
			hide-footer
			no-close-on-esc
			no-close-on-backdrop
		>
			<template #modal-title>
				<div class="modal-title">ファイル分類</div>
			</template>

			<div class="section_type">
				<b-row class="justify-content-center">
					<b-col cols="10">
						<span>{{ $t('FILE_NAME') }}</span>

					</b-col>
					<b-col cols="10">
						<b-input v-model="file_name_pdf" type="text" disabled />
					</b-col>
				</b-row>
			</div>

			<div class="section_type">
				<b-row class="justify-content-center">
					<b-col cols="10" class="mt-3">
						<span>{{ $t('TYPE_APTITUDE_TEST') }}</span>
						<b-form-select
							id="filter-affiliation-base-value"
							v-model="select_file_classification"
							:options="ListFileClassification"
							:value-field="'id'"
							:text-field="'type'"
						>
							<template #first>
								<b-form-select-option :value="null">
									{{ $t('PLEASE_SELECT') }}
								</b-form-select-option>
							</template>
						</b-form-select>
					</b-col>
					<b-col v-if="select_file_classification === 3" cols="10" class="mt-3">
						<span>{{ $t('TYPE_APTITUDE_TEST') }}</span>
						<b-form-select
							id="filter-affiliation-base-value"
							v-model="select_type_aptitudeTest"
							:options="ListTypeAptitudeTest"
							:value-field="'id'"
							:text-field="'type'"
						>
							<template #first>
								<b-form-select-option :value="null">
									{{ $t('PLEASE_SELECT') }}
								</b-form-select-option>
							</template>
						</b-form-select>
					</b-col>
					<b-col v-if="select_file_classification === 3" cols="10" class="mt-3">
						<span>{{ $t('DATE_APTITUDE_TEST') }}</span>
						<b-input v-model="date_visit_aptitude_test" type="date" />
					</b-col>
					<b-col v-if="select_file_classification === 4" cols="10" class="mt-3">
						<span>{{ $t('DATE_APTITUDE_TEST') }}</span>
						<b-input v-model="date_visit_health_exam" type="date" />
					</b-col>
					<b-col v-if="select_file_classification === 1" cols="10" class="mt-3">
						<span>{{ $t('TYPE_DRIVING_LICENSE') }}</span>
						<b-form-select
							id="filter-affiliation-base-value"
							v-model="select_file_Driving"
							:options="ListTypeFileDriving"
							:value-field="'id'"
							:text-field="'type'"
						>
							<template #first>
								<b-form-select-option :value="null">
									{{ $t('PLEASE_SELECT') }}
								</b-form-select-option>
							</template>
						</b-form-select>
					</b-col>
				</b-row>
			</div>

			<b-row>
				<b-col cols="12" class="d-flex justify-content-end mt-5 mr-5">
					<vButton
						:class="'btn-history'"
						@click.native="handleCloseModalPDF()"
					>
						{{ $t('CLOSE') }}
					</vButton>
					<vButton
						:class="'btn-summit-filter ml-2'"
						@click.native="handleFileClassification()"
					>
						{{ $t('BUTTON.SAVE') }}
					</vButton>
				</b-col>
			</b-row>
		</b-modal>

		<b-modal
			id="modal-add-pdf"
			v-model="isShowModalUploadPDF"
			static
			size="lg"
			hide-footer
			no-close-on-esc
			no-close-on-backdrop
		>
			<template #modal-title>
				<div class="modal-title">適性診断票アップロード</div>
				<div class="description-title">PDFファイルを選択してアップロードしてください</div>
			</template>

			<b-row class="justify-content-center">
				<b-col cols="8" class="mt-3">
					<p>{{ $t('PDF_FILE') }}</p>
					<div
						class="update_first"
						:class="{ 'update_first--dragging': isDragging }"
						@dragover.prevent
						@dragenter.prevent="onDragEnter"
						@dragleave.prevent="onDragLeave"
						@drop.prevent="onDrop"
					>
						<input
							id="fileUpload"
							ref="selectFileInput"
							type="file"
							accept=".pdf"
							name="File Upload"
							style="display: none;"
							@change="getFileCSVInput"
						>
						<i class="far fa-upload icon_upload" />
						<div class="content_upload">クリックしてファイルを選択、またはドラッグ&ドロップ</div>
						<div class="content_or">PDF形式のみ対応</div>
						<vButton
							:class="'btn-history'"
							@click.native="triggerSelectFileInput()"
						>
							{{ $t('SELECT_FILE') }}
						</vButton>
						<div class="dropzone__file-name">{{ fileNameCSVInput }}</div>
					</div>
				</b-col>
			</b-row>

			<b-row>
				<b-col cols="12" class="d-flex justify-content-end mt-5 mr-5">
					<vButton
						:class="'btn-history'"
						@click.native="handleCloseModalUploadPDF()"
					>
						{{ $t('CLOSE') }}
					</vButton>
					<vButton
						:class="'btn-summit-filter ml-2'"
						@click.native="handlePostPDFEmployee()"
					>
						{{ $t('BUTTON.SAVE') }}
					</vButton>
				</b-col>
			</b-row>
		</b-modal>

		<b-modal
			id="modal-delete-pdf"
			v-model="isShowModalDelete"
			static
			size="md"
			hide-footer
			no-close-on-esc
			no-close-on-backdrop
		>
			<template #modal-title>
				<div class="modal-title">削除確認</div>
			</template>

			<div>
				<span>
					以下のファイルを削除してもよろしいですか？
				</span>
				<div>
					<span class="font-weight-bold mt-3">
						{{ fileNameDelete }}
					</span>
				</div>
			</div>

			<b-row>
				<b-col cols="12" class="d-flex justify-content-end mt-5 mr-5">
					<vButton
						:class="'btn-history'"
						@click.native="handleCloseModalDelete()"
					>
						{{ $t('CLOSE') }}
					</vButton>
					<vButton
						:class="'btn-summit-filter ml-2'"
						@click.native="handleDelete()"
					>
						{{ $t('BUTTON.DELETE') }}
					</vButton>
				</b-col>
			</b-row>
		</b-modal>

		<b-modal
			id="modal-change-history"
			v-model="modalChangeHistory"
			static
			size="xl"
			hide-footer
			no-close-on-esc
			no-close-on-backdrop
			:header-bg-variant="headerBgVariant"
			:header-text-variant="headerTextVariant"
		>
			<template #modal-title>
				<span>拠点異動履歴 - {{ handleTransformName(employee.name) }}</span>
			</template>

			<b-table-simple class="table-history" bordered responsive="sm">
				<b-thead>
					<b-th class="table-history-th department-date-th">
						<span>拠点異動日</span>
					</b-th>

					<b-th class="table-history-th department-name-th">
						<span>異動先拠点</span>
					</b-th>
				</b-thead>

				<b-tbody>
					<b-tr v-for="(item, itemIndex) in history" :key="itemIndex">
						<b-td class="table-history-td department-date-td">
							<span>{{ item.start_date }}</span>
						</b-td>

						<b-td class="table-history-td department-name-td">
							<span>{{ item.department_name }}</span>
						</b-td>
					</b-tr>
				</b-tbody>
			</b-table-simple>
		</b-modal>

		<b-modal
			id="modal-other-base"
			v-model="modalOtherBase"
			static
			size="xl"
			hide-footer
			no-close-on-esc
			no-close-on-backdrop
			:header-bg-variant="headerBgVariant"
			:header-text-variant="headerTextVariant"
		>
			<template #modal-title>
				<span>勤務情報 - {{ handleTransformName(employee.name) }} - {{ detailModalData.department_working_name }}</span>
			</template>

			<div class="other-base-content">
				<p class="other-base-content-text">この拠点には勤務情報が未登録です。</p>
				<p class="other-base-content-text second-line">「次へ」を押して登録をお願いします。</p>
			</div>

			<b-button
				v-if="hasRole([CONST_ROLE.CLERKS, CONST_ROLE.TL, CONST_ROLE.DEPARTMENT_OFFICE_STAFF, CONST_ROLE.PERSONNEL_LABOR, CONST_ROLE.AM_SM, CONST_ROLE.DIRECTOR, CONST_ROLE.DX_USER, CONST_ROLE.DX_MANAGER], roles)"
				class="button-to-edit-screen"
				@click="handleButtonCloseOtherBaseClicked()"
			>
				<span>次へ</span>
			</b-button>
		</b-modal>

		<b-modal
			id="modal-affiliation-support-base-detail"
			v-model="modalAffiliationSupportBaseDetail"
			static
			size="xl"
			hide-footer
			no-close-on-esc
			no-close-on-backdrop
			:header-bg-variant="headerBgVariant"
			:header-text-variant="headerTextVariant"
			@close="handleCloseModalDetail()"
		>
			<template #modal-title>
				<span>勤務情報 - {{ handleTransformName(employee.name) }} - {{ detailModalData.department_working_name }}</span>
			</template>

			<div class="affiliation-support-base-detail-content">
				<b-row v-for="(item, itemIndex) in detailModalData.support_date_list" :key="itemIndex" class="mt-3">
					<b-col cols="6">
						<label for="support-start-date">{{ $t('SUPPORT_START_DATE') }}</label>
						<b-form-input id="support-start-date" :value="item.start_date" disabled />
					</b-col>

					<b-col cols="6">
						<label for="support-end-date">{{ $t('SUPPORT_END_DATE') }}</label>
						<b-form-input id="support-end-date" :value="item.end_date" disabled />
					</b-col>
				</b-row>

				<b-row class="mt-3">
					<b-col cols="6">
						<label for="employee-grade">{{ $t('EMPLOYEE_GRADE') }}</label>
						<b-form-input id="employee-grade" :value="detailModalData.employee_grade" disabled />
					</b-col>

					<b-col cols="6">
						<label for="employee-grade-2">{{ $t('EMPLOYEE_GRADE_2') }}</label>
						<b-form-input id="employee-grade-2" :value="detailModalData.employee_grade_2" disabled />
					</b-col>
				</b-row>

				<b-row class="mt-3">
					<b-col cols="6">
						<label for="boarding-employee-grade">{{ $t('BOARDING_EMPLOYEE_GRADE') }}</label>
						<b-form-input id="boarding-employee-grade" :value="detailModalData.boarding_employee_grade" disabled />
					</b-col>

					<b-col cols="6">
						<label for="boarding-employee-grade-2">{{ $t('BOARDING_EMPLOYEE_GRADE_2') }}</label>
						<b-form-input id="boarding-employee-grade-2" :value="detailModalData.boarding_employee_grade_2" disabled />
					</b-col>
				</b-row>

				<b-row class="mt-3">
					<b-col cols="12">
						<label for="transportation-compensation">{{ $t('TRANSPORTATION_COMPENSATION') }}</label>
						<b-form-input
							id="transportation-compensation"
							:value="formatNumberWithCommas(detailModalData.transportation_compensation)"
							disabled
						/>
					</b-col>
				</b-row>

				<b-row class="mt-3">
					<b-col cols="12">
						<label for="daily-transportation-compensation">{{ $t('DAILY_TRANSPORTATION_COMPENSATION') }}</label>
						<b-form-input
							id="daily-transportation-compensation"
							:value="formatNumberWithCommas(detailModalData.daily_transportation_compensation)"
							disabled
						/>
					</b-col>
				</b-row>

				<b-row class="mt-3">
					<b-col cols="6">
						<label for="midnight-working-time-hour">{{ $t('MIDNIGHT_WORKING_TIME') }} (時)</label>
						<b-form-input
							id="midnight-working-time-hour"
							:value="detailModalData.midnight_working_time_hour"
							:placeholder="'(時)'"
							disabled
						/>
					</b-col>

					<b-col cols="6">
						<label for="midnight-working-time-minute">{{ $t('MIDNIGHT_WORKING_TIME') }} (分)</label>
						<b-form-input
							id="midnight-working-time-minute"
							:value="detailModalData.midnight_working_time_minute"
							:placeholder="'(分)'"
							disabled
						/>
					</b-col>
				</b-row>

				<b-row class="mt-3">
					<b-col cols="6">
						<label for="scheduled-labor-table-hour">{{ $t('SCHEDULED_LABOR_TABLE') }} (時)</label>
						<b-form-input
							id="scheduled-labor-table-hour"
							:value="detailModalData.scheduled_labor_table_hour"
							:placeholder="'(時)'"
							disabled
						/>
					</b-col>

					<b-col cols="6">
						<label for="scheduled-labor-table-minute">{{ $t('SCHEDULED_LABOR_TABLE') }} (分)</label>
						<b-form-input
							id="scheduled-labor-table-minute"
							:value="detailModalData.scheduled_labor_table_minute"
							:placeholder="'(分)'"
							disabled
						/>
					</b-col>
				</b-row>

				<b-row class="mt-3">
					<b-col cols="6">
						<label for="scheduled-work-start-time">所定労働時間（始）</label>
						<b-form-input
							id="scheduled-work-start-time"
							:value="detailModalData.scheduled_work_start_time || '--:--'"
							:placeholder="'--:--'"
							disabled
						/>
					</b-col>

					<b-col cols="6">
						<label for="scheduled-work-end-time">所定労働時間（終）</label>
						<b-form-input
							id="scheduled-work-end-time"
							:value="detailModalData.scheduled_work_end_time || '--:--'"
							:placeholder="'--:--'"
							disabled
						/>
					</b-col>
				</b-row>

				<b-row v-if="[1, 3].includes(showTempWage)" class="mt-3">
					<b-col cols="12">
						<label v-if="showTempWage === 3" for="temp-wage">{{ $t('TEMP_WAGE') }}</label>
						<label v-else-if="showTempWage === 1" for="temp-wage">{{ 'アルバイト基本給' }}</label>

						<b-form-input
							id="temp-wage"
							type="number"
							:value="detailModalData.temp_wage"
							class="mt-3"
							disabled
							@keydown.native="onlyNumberInput"
						/>
					</b-col>
				</b-row>

				<b-row class="mt-3">
					<b-col cols="12">
						<label for="driveable-route">{{ $t('DRIVEABLE_ROUTE') }}</label>

						<b-button v-if="detailModalData.driveable_route.length" class="button-driveable-route ml-3">
							<span>{{ detailModalData.driveable_route.length }}</span>
						</b-button>

						<b-form-input v-for="(item, itemIndex) in detailModalData.driveable_route" id="driveable-route" :key="itemIndex" :value="item.course_code" class="mt-3" disabled />
					</b-col>
				</b-row>
			</div>

			<b-button
				v-if="hasRole([CONST_ROLE.CLERKS, CONST_ROLE.TL, CONST_ROLE.DEPARTMENT_OFFICE_STAFF, CONST_ROLE.PERSONNEL_LABOR, CONST_ROLE.AM_SM, CONST_ROLE.DIRECTOR, CONST_ROLE.DX_USER, CONST_ROLE.DX_MANAGER], roles)"
				class="button-to-edit-screen mt-3"
				@click="handleDisplayEditModal()"
			>
				<span>編集</span>
			</b-button>
		</b-modal>

		<b-modal
			id="modal-affiliation-support-base-edit"
			v-model="modalAffiliationSupportBaseEdit"
			static
			size="xl"
			hide-footer
			no-close-on-esc
			no-close-on-backdrop
			:header-bg-variant="headerBgVariant"
			:header-text-variant="headerTextVariant"
			@close="handleCloseModalEdit()"
		>
			<template #modal-title>
				<span>勤務情報 - {{ handleTransformName(employee.name) }} - {{ detailModalData.department_working_name }}</span>
			</template>

			<div class="affiliation-support-base-edit-content">
				<b-row>
					<b-col cols="6">
						<label for="support-start-date">{{ $t('SUPPORT_START_DATE') }}</label>

						<b-input-group>
							<b-form-datepicker
								id="support-start-date"
								v-model="support_start_date"
								:locale="lang"
								:date-format-options="{ month: '2-digit', day: '2-digit' }"
								:max="support_end_date"
								placeholder="入力してください"
							/>

							<b-input-group-append class="btn-reset" @click="resetSupportStartDate()">
								<b-input-group-text>
									<i class="fas fa-trash" />
								</b-input-group-text>
							</b-input-group-append>
						</b-input-group>
					</b-col>

					<b-col cols="5">
						<label for="support-end-date">{{ $t('SUPPORT_END_DATE') }}</label>

						<b-input-group>
							<b-form-datepicker
								id="support-end-date"
								v-model="support_end_date"
								:locale="lang"
								:date-format-options="{ month: '2-digit', day: '2-digit' }"
								:min="support_start_date"
								placeholder="入力してください"
							/>

							<b-input-group-append class="btn-reset" @click="resetSupportEndDate()">
								<b-input-group-text>
									<i class="fas fa-trash" />
								</b-input-group-text>
							</b-input-group-append>
						</b-input-group>
					</b-col>

					<b-col cols="1">
						<b-button variant="success" class="button-support-addition" @click="addSupportDate()">
							<i class="far fa-plus-circle icon-handle" />
						</b-button>
					</b-col>
				</b-row>

				<b-row v-for="(supportDate, supportDateIndex) in listSupportDate" :key="supportDateIndex" class="mt-3">
					<b-col cols="6">
						<label for="support-start-date">{{ $t('SUPPORT_START_DATE') }}</label>

						<b-form-datepicker
							:id="`support-start-date-${supportDateIndex}`"
							v-model="listSupportDate[supportDateIndex].start_date"
							:locale="lang"
							:date-format-options="{ month: '2-digit', day: '2-digit' }"
							:max="listSupportDate[supportDateIndex].end_date"
							placeholder="入力してください"
						/>
					</b-col>

					<b-col cols="5">
						<label for="support-end-date">{{ $t('SUPPORT_END_DATE') }}</label>

						<b-form-datepicker
							:id="`support-end-date-${supportDateIndex}`"
							v-model="listSupportDate[supportDateIndex].end_date"
							:locale="lang"
							:date-format-options="{ month: '2-digit', day: '2-digit' }"
							:min="listSupportDate[supportDateIndex].start_date"
							placeholder="入力してください"
						/>
					</b-col>

					<b-col cols="1">
						<b-button variant="danger" class="button-support-remove" @click="removeSupportDate(supportDate.start_date)">
							<i class="far fa-minus-circle icon-handle" />
						</b-button>
					</b-col>
				</b-row>

				<b-row class="mt-3">
					<b-col cols="6">
						<label for="employee-grade">{{ $t('EMPLOYEE_GRADE') }}</label>
						<b-form-input
							id="employee-grade"
							v-model="editModalData.employee_grade"
							placeholder="入力してください"
							type="number"
							:formatter="formatInputNumber"
							@keydown.native="validInputNumber"
						/>
					</b-col>

					<b-col cols="6">
						<label for="employee-grade-2">{{ $t('EMPLOYEE_GRADE_2') }}</label>
						<b-form-input
							id="employee-grade-2"
							v-model="editModalData.employee_grade_2"
							placeholder="入力してください"
							type="number"
							:formatter="formatInputNumber"
							@keydown.native="validInputNumber"
						/>
					</b-col>
				</b-row>

				<b-row class="mt-3">
					<b-col cols="6">
						<label for="boarding-employee-grade">{{ $t('BOARDING_EMPLOYEE_GRADE') }}</label>
						<b-form-input
							id="boarding-employee-grade"
							v-model="editModalData.boarding_employee_grade"
							placeholder="入力してください"
							type="number"
							:formatter="formatInputNumber"
							@keydown.native="validInputNumber"
						/>
					</b-col>

					<b-col cols="6">
						<label for="boarding-employee-grade-2">{{ $t('BOARDING_EMPLOYEE_GRADE_2') }}</label>
						<b-form-input
							id="boarding-employee-grade-2"
							v-model="editModalData.boarding_employee_grade_2"
							placeholder="入力してください"
							type="number"
							:formatter="formatInputNumber"
							@keydown.native="validInputNumber"
						/>
					</b-col>
				</b-row>

				<b-row class="mt-3">
					<b-col cols="12">
						<label for="transportation-compensation">{{ $t('TRANSPORTATION_COMPENSATION') }}</label>
						<b-form-input
							id="transportation-compensation"
							v-model="editModalData.transportation_compensation"
							placeholder="入力してください"
							type="number"
							@keydown.native="validInputNumber"
						/>
					</b-col>
				</b-row>

				<b-row class="mt-3">
					<b-col cols="12">
						<label for="daily-transportation-compensation">{{ $t('DAILY_TRANSPORTATION_COMPENSATION') }}</label>
						<b-form-input
							id="daily-transportation-compensation"
							v-model="editModalData.daily_transportation_compensation"
							placeholder="入力してください"
							type="number"
							@keydown.native="validInputNumber"
						/>
					</b-col>
				</b-row>

				<b-row class="mt-3">
					<b-col cols="6">
						<label for="select-route-start-time-hour">{{ $t('MIDNIGHT_WORKING_TIME') }} (時)</label>

						<!-- <b-form-input v-model="editModalData.midnight_working_time_hour" :placeholder="'(時)'" /> -->

						<b-input-group append="時">
							<b-form-select
								id="select-route-start-time-hour"
								v-model="editModalData.midnight_working_time_hour"
								:options="listTimeHour"
								placeholder="選択してください"
							/>
						</b-input-group>
					</b-col>

					<b-col cols="6">
						<label for="select-midnight-working-time-minute">{{ $t('MIDNIGHT_WORKING_TIME') }} (分)</label>

						<!-- <b-form-input v-model="editModalData.midnight_working_time_minute" :placeholder="'(分)'" /> -->

						<b-input-group append="分">
							<b-form-select
								id="select-midnight-working-time-minute"
								v-model="editModalData.midnight_working_time_minute"
								:options="listTimeMinute"
								placeholder="選択してください"
							/>
						</b-input-group>
					</b-col>
				</b-row>

				<b-row class="mt-3">
					<b-col cols="6">
						<label for="select-scheduled-labor-table-hour">{{ $t('SCHEDULED_LABOR_TABLE') }} (時)</label>

						<!-- <b-form-input v-model="editModalData.scheduled_labor_table_hour" :placeholder="'(時)'" /> -->

						<b-input-group append="時">
							<b-form-select
								id="select-scheduled-labor-table-hour"
								v-model="editModalData.scheduled_labor_table_hour"
								:options="listTimeHour"
								placeholder="選択してください"
							/>
						</b-input-group>
					</b-col>

					<b-col cols="6">
						<label for="select-scheduled-labor-table-minute">{{ $t('SCHEDULED_LABOR_TABLE') }} (分)</label>

						<!-- <b-form-input v-model="editModalData.scheduled_labor_table_minute" :placeholder="'(分)'" /> -->

						<b-input-group append="分">
							<b-form-select
								id="select-scheduled-labor-table-minute"
								v-model="editModalData.scheduled_labor_table_minute"
								:options="listTimeMinute"
								placeholder="選択してください"
							/>
						</b-input-group>
					</b-col>
				</b-row>

				<b-row v-if="showTempWage === 3" class="mt-3">
					<b-col cols="12">
						<label for="temp-wage">{{ $t('TEMP_WAGE') }}</label>

						<b-form-input
							id="temp-wage"
							v-model="editModalData.temp_wage"
							type="number"
							class="mt-3"
							@keydown.native="onlyNumberInput"
						/>
					</b-col>
				</b-row>

				<b-row class="mt-3">
					<b-col cols="12">
						<div class="zone-form">
							<div class="item-form">
								<label for="driveable-route">{{ $t('DRIVEABLE_ROUTE') }}</label>

								<b-button v-if="editModalData.driveable_route.length" class="button-driveable-route ml-3">
									<span>{{ editModalData.driveable_route.length }}</span>
								</b-button>

								<div class="mt-3">
									<vDeliveryCourseCreation
										:items="listRouteMaster"
										:list-selected="listSelected"
										:is-edit="true"
										@add="handleAddCourse"
										@delete="handleDeleteCourse"
									/>
								</div>
							</div>
						</div>
					</b-col>
				</b-row>
			</div>

			<b-row>
				<b-col cols="6">
					<b-button class="button-return mt-3" @click="handleReturnButtonClicked()">
						<span>戻る</span>
					</b-button>
				</b-col>

				<b-col cols="6">
					<b-button class="button-save mt-3" @click="handleSaveButtonClicked()">
						<span>保存</span>
					</b-button>
				</b-col>
			</b-row>
		</b-modal>

		<b-modal
			id="modal-history"
			v-model="isShowModalHistory"
			static
			centered
			size="lg"
			hide-footer
			no-close-on-esc
			hide-header-close
			no-close-on-backdrop
			:header-class="'modal-history-header'"
		>
			<template #modal-header>
				<b-row class="w-100 m-auto">
					<b-col cols="12" class="d-flex justify-content-center align-items-center">
						<i
							v-if="deviceHistoryData.length > 0"
							class="fas fa-caret-left fa-caret"
							style="font-size: 30px; margin-right: 80px;"
							@click="handlePrevIndex()"
						/>
						<span style="font-size: 20px; font-weight: bold;">
							{{ deviceHistoryData[currentTabIndex] ?
								handleTransformDateToISO(deviceHistoryData[currentTabIndex].created_at) : $t('TABLE_EMPTY') }}
						</span>
						<i
							v-if="deviceHistoryData.length > 0"
							class="fas fa-caret-right fa-caret"
							style="font-size: 30px; margin-left: 80px;"
							@click="handleNextIndex()"
						/>

						<b-button class="button-close-modal-history" @click="() => { isShowModalHistory = false }">
							<i class="fas fa-times-circle" />
						</b-button>
					</b-col>
				</b-row>
			</template>

			<div class="main-content mt-3">
				<b-row class="mt-3">
					<b-col cols="12">
						<label for="">デバイス種類</label>
					</b-col>

					<b-col cols="12">
						<b-form-input
							:value="deviceHistoryData[currentTabIndex] ? deviceHistoryData[currentTabIndex].device_type : ''"
							disabled
						/>
					</b-col>
				</b-row>

				<b-row class="mt-3">
					<b-col cols="12">
						<label for="">電話番号</label>
					</b-col>

					<b-col cols="12">
						<b-form-input
							:value="deviceHistoryData[currentTabIndex] ? deviceHistoryData[currentTabIndex].tel : ''"
							disabled
						/>
					</b-col>
				</b-row>

				<b-row class="mt-3">
					<b-col cols="12">
						<label for="">メールアドレス</label>
					</b-col>

					<b-col cols="12">
						<b-form-input :value="employee.email" disabled />
					</b-col>
				</b-row>

				<b-row class="mt-3">
					<b-col cols="12">
						<label for="">アンドロイドID</label>
					</b-col>

					<b-col cols="12">
						<b-form-input
							:value="deviceHistoryData[currentTabIndex] ? deviceHistoryData[currentTabIndex].android_id : ''"
							disabled
						/>
					</b-col>
				</b-row>

				<b-row class="mt-3">
					<b-col cols="12">
						<label for="">IMEI番号</label>
					</b-col>

					<b-col cols="12">
						<b-form-input
							:value="deviceHistoryData[currentTabIndex] ? deviceHistoryData[currentTabIndex].imei_number : ''"
							disabled
						/>
					</b-col>
				</b-row>

				<b-row class="mt-3">
					<b-col cols="12">
						<label for="">モデル名</label>
					</b-col>

					<b-col cols="12">
						<b-form-input
							:value="deviceHistoryData[currentTabIndex] ? deviceHistoryData[currentTabIndex].model_name : ''"
							disabled
						/>
					</b-col>
				</b-row>
			</div>
		</b-modal>

		<b-modal
			id="modal-welfare-expense-history"
			v-model="modalWelfareExpense"
			static
			size="xl"
			hide-footer
			no-close-on-esc
			no-close-on-backdrop
			:header-bg-variant="headerBgVariant"
			:header-text-variant="headerTextVariant"
		>
			<!-- <template #modal-title>
				<span>拠点異動履歴 -  {{ handleTransformName(employee.name) }}</span>
				</template> -->

			<b-table-simple class="table-history" bordered responsive="sm">
				<b-thead>
					<b-th class="table-history-th department-date-th">
						<span>更新日</span>
					</b-th>

					<b-th class="table-history-th department-name-th">
						<span>法定福利費</span>
					</b-th>
				</b-thead>

				<b-tbody>
					<b-tr v-for="(item, itemIndex) in welfare_expense_history" :key="itemIndex">
						<b-td class="table-history-td department-date-td">
							<span>{{ item.start_date }}</span>
						</b-td>

						<b-td class="table-history-td department-name-td">
							<span>{{ item.welfare_expense }}</span>
						</b-td>
					</b-tr>
				</b-tbody>
			</b-table-simple>
		</b-modal>

		<b-modal
			id="modal-equipment-data-history"
			v-model="modalEquipmentData"
			static
			size="xl"
			hide-footer
			scrollable
			no-close-on-esc
			no-close-on-backdrop
			:header-bg-variant="'light'"
			:header-text-variant="'dark'"
		>
			<b-table-simple class="table-history" bordered responsive="sm">
				<b-thead>
					<b-th class="table-history-th department-date-th">
						<span>社用車</span>
					</b-th>

					<b-th class="table-history-th department-name-th">
						<span>ETC カード</span>
					</b-th>

					<b-th class="table-history-th department-name-th">
						<span>燃料カード</span>
					</b-th>

					<b-th class="table-history-th department-name-th">
						<span>その他</span>
					</b-th>
				</b-thead>

				<b-tbody>
					<b-tr v-for="(item, itemIndex) in equipment_data_change_history" :key="itemIndex">
						<b-td class="table-history-td department-date-td">
							<span>{{ item.company_car }}</span>
						</b-td>

						<b-td class="table-history-td department-name-td">
							<span>{{ item.etc_card }}</span>
						</b-td>

						<b-td class="table-history-td department-name-td">
							<span>{{ item.fuel_card }}</span>
						</b-td>

						<b-td class="table-history-td department-name-td">
							<span>{{ item.other }}</span>
						</b-td>
					</b-tr>
				</b-tbody>
			</b-table-simple>
		</b-modal>
	</b-overlay>
</template>

<script>
import { hasRole } from '@/utils/hasRole';
import { MakeToast } from '@/utils/MakeToast';
import { validInputNumber } from '@/utils/handleInput';
import { onlyNumberInput } from '@/utils/onlyNumberInput';
import { formatDateDisplay } from '@/pages/VehicleMaster/helper/helper.js';
import ModalViewPDF from '@/components/template/ModalViewPDF.vue';
// import vTitle from '@/components/atoms/vTitle.vue';
import vButton from '@/components/atoms/vButton';

import { getDetailEmployee, getDepartmentWorking, postEmployee, getListCourse, postPDFEmployeeDetail, updateDriverLicense, updateDrivingRecordCertificate, updateAptitudeAssessmentForm, updateHealthExaminationResults, deletePDF, deleteHealthExamPDF, deleteAptitude } from '@/api/modules/employeeMaster';

import CONST_ROLE from '@/const/role';
// import vHeaderPage from '@/components/atoms/vHeaderPage';
import TitleHeader from '@/components/atoms/vTitleHeader';
import vNodeBaseEmployeeMaster from '@/components/atoms/vNodeBaseEmployeeMaster';
import DeliveryCourseCreation from '@/components/organisms/DeliveryCourseCreation';
import { postPDF } from '@/api/modules/employeeMaster';

const urlAPIs = {
    apiGetEmployeeDetail: '/employee',
    apiGetDepartmentWorking: '/employee/dp-working',
    apiPostEmployee: '/employee',
    apiGetListCourse: '/employee/dp-working/list-course',
    urlUploadData: '/employee/upload-file',
    apiUploadPDFEmployee: '/employee/upload-employee-pdf',
    urlUpdate: '/employee/driver-license',
    urlDrivingRecord: '/employee/driving-record-certificate',
    urlAptitude: '/employee/aptitude-assessment-form',
    urlHealthExam: '/employee/health-examination-results',
    urlDelete: '/employee/delete-employee-pdf',
    urlDeleteHealthExam: '/employee/delete-health-examination-file-history',
    urlDeleteAptitude: '/employee/delete-aptitude-assessment-form',
};

export default {
    name: 'DetailEmployeeMaster',
    components: {
        // vHeaderPage,
        // vTitle,
        vButton,
        ModalViewPDF,
        vTitleHeader: TitleHeader,
        vNode: vNodeBaseEmployeeMaster,
        vDeliveryCourseCreation: DeliveryCourseCreation,
    },
    data() {
        return {
            hasRole,
            CONST_ROLE,

            overlay: {
                opacity: 1,
                show: false,
                blur: '1rem',
                rounded: 'sm',
                variant: 'light',
            },
            detail_url_pdf: '',
            isShowPDFView: false,
            modalAddPDF: false,
            modalShowHistoryPDF: false,
            modalShowHealthExamHistoryPDF: false,
            itemsHistoryHealthExam: [],
            itemsHistory: [],
            history_health_pdf: '',
            isShowHistoryPDF: false,

            isShowModalDeleteHealthExam: false,
            fileNameDeleteHealthExam: '',
            ID_delete_health: '',

            isShowModalDeleteAptitude: false,
            fileNameDeleteAptitude: '',
            ID_delete_aptitude: '',

            file_name_pdf: '',
            select_file_classification: null,
            ListFileClassification: [
                { id: 1, type: '運転免許証' },
                { id: 2, type: '運転記録証明書' },
                { id: 3, type: '適性診断票' },
                { id: 4, type: '健康診断結果通知書' },
            ],
            select_type_aptitudeTest: null,
            ListTypeAptitudeTest: [
                { id: 1, type: '初任' },
                { id: 2, type: '適齢' },
                { id: 3, type: '特定' },
                { id: 4, type: '一般' },
            ],
            date_visit_aptitude_test: '',
            date_visit_health_exam: '',

            employee: {
                id_upload: null,
                id: '',
                name: '',
                name_phonetic: '',
                date_of_appointment: '',
                address: '',
                contact_phone_number: '',
                previous_employment_history: '',
                aptitude_test_date: '',
                medical_examination_date: '',
                age_appropriate_interview: null,
                selected_classroom: null,
                selected_practical: null,
                email: '',
                gender: '',
                birthday: '',
                workingType: '',
                licenseType: '',
                employeeType: '',
                employeeRole: '',
                hireStartDate: '',
                retirementDate: '',
                welfareExpense: '',
                first_time: '',
                aligible_age: '',
                special: '',
                general: '',
            },

            showTempWage: null,

            modalOtherBase: false,
            modalChangeHistory: false,
            modalAffiliationSupportBaseEdit: false,
            modalAffiliationSupportBaseDetail: false,
            modalWelfareExpense: false,
            modalEquipmentData: false,

            headerBgVariant: 'dark',
            headerTextVariant: 'light',

            detailModalData: {
                department_working_id: '',
                department_working_name: '',
                employee_grade: '',
                driveable_route: [],
                support_end_date: '',
                employee_grade_2: '',
                support_start_date: '',
                boarding_employee_grade: '',
                boarding_employee_grade_2: '',
                midnight_working_time_hour: '',
                scheduled_labor_table_hour: '',
                transportation_compensation: '',
                midnight_working_time_minute: '',
                scheduled_labor_table_minute: '',
                daily_transportation_compensation: '',
                support_date_list: [],
                temp_wage: null,
                scheduled_work_start_time: '',
                scheduled_work_end_time: '',
            },
            options: [
                { name: this.$t('COMPLETED'), item: '1', notEnabled: true },
                { name: this.$t('NOT_COMPLETED'), item: '2', notEnabled: true },
            ],
            options_practical: [
                { name: this.$t('COMPLETED'), item: '1', notEnabled: true },
                { name: this.$t('NOT_COMPLETED'), item: '2', notEnabled: true },
            ],

            editModalData: {
                department_working_id: '',
                department_working_name: '',
                employee_grade: '',
                driveable_route: [],
                support_end_date: '',
                employee_grade_2: '',
                support_start_date: '',
                boarding_employee_grade: '',
                boarding_employee_grade_2: '',
                midnight_working_time_hour: '',
                scheduled_labor_table_hour: '',
                transportation_compensation: '',
                midnight_working_time_minute: '',
                scheduled_labor_table_minute: '',
                daily_transportation_compensation: '',
                support_date_list: [],
                temp_wage: null,
                scheduled_work_start_time: '',
                scheduled_work_end_time: '',
            },

            listSelected: [],

            listRouteMaster: [],

            listSupportDate: [],

            listCourse: [],

            support_start_date: '',
            support_end_date: '',

            lang: this.$store.getters.language,

            history: [],

            working_data: [],

            welfare_expense_history: [],

            listTimeHour: [
                { value: null, text: '選択してください' },
            ],

            listTimeMinute: [
                { value: null, text: '選択してください' },
            ],

            deviceHistoryData: [],

            currentTabIndex: 0,

            isShowModalHistory: false,

            equipment_data_change_history: [],

            equipmentData: {
                company_car: '',
                etc_card: '',
                fuel_card: '',
                other: '',
                employee_role: null,
            },

            employee_role_options: [
                { value: null, text: '選択してください' },
                { value: 1, text: '部長' },
                { value: 2, text: '本部長' },
                { value: 3, text: '常務' },
                { value: 4, text: '社長' },
            ],
            ListType: [
                { id: 1, type: '初任' },
                { id: 2, type: '適齢' },
                { id: 3, type: '特定' },
                { id: 4, type: '一般' },
            ],
            items: [],
            isDragging: false,
            fileNameCSVInput: null,
            selectedFile: null,
            isShowModalUploadPDF: false,
            id_file: null,
            file_upload: null,

            select_file_Driving: null,
            ListTypeFileDriving: [
                // { id: null, type: '面を選択してください' },
                { id: 1, type: '表面' },
                { id: 2, type: '裏面' },
            ],
            isShowModalDelete: false,
            fileNameDelete: '',
            ID_delete: null,
            dataPDF: {},

        };
    },
    computed: {
        roles() {
            return this.$store.getters.profile.roles;
        },
        fieldsHistoryHealthExam() {
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
                    key: 'date_of_visit',
                    sortable: false,
                    label: this.$t('DATE_OF_CONSULTATION'),
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
                    key: 'operation',
                    sortable: false,
                    label: this.$t('OPERATION'),
                    tdClass: 'td-affilitation-base',
                    thClass: 'th-support-base-history-pdf',
                },
            ];
        },
        fieldsHistory() {
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
                    key: 'type',
                    sortable: false,
                    label: this.$t('CATEGORY'),
                    tdClass: 'td-affilitation-base',
                    thClass: 'th-affilitation-base',
                },
                {
                    key: 'date_of_visit',
                    sortable: false,
                    label: this.$t('DATE_OF_CONSULTATION'),
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
                    key: 'operation',
                    sortable: false,
                    label: this.$t('OPERATION'),
                    tdClass: 'td-affilitation-base',
                    thClass: 'th-support-base',
                },
            ];
        },
        fields() {
            return [
                {
                    key: 'file_name',
                    sortable: false,
                    label: this.$t('FILE_NAME'),
                    tdClass: 'td-affilitation-base',
                    thClass: 'th-affilitation-base',
                },
                {
                    key: 'date_upload',
                    sortable: false,
                    label: this.$t('DATE_OF_UPLOAD'),
                    tdClass: 'td-affilitation-base',
                    thClass: 'th-affilitation-base',
                },
                {
                    key: 'operation',
                    sortable: false,
                    label: this.$t('OPERATION'),
                    tdClass: 'td-option',
                    thClass: 'th-support-base',
                },
            ];
        },
    },
    created() {
        this.getEmployeeDetailData();

        this.handleGetListTime();

        this.$bus.on('NODE_CLICK_EVENT', (CLASS_LIST, ID, DEPARTMENT) => {
            this.handleModalDisplay(CLASS_LIST, ID, DEPARTMENT);
        });
    },
    destroyed() {
        this.$bus.off('NODE_CLICK_EVENT');
    },
    methods: {
        validInputNumber,
        onlyNumberInput,
        handleShowModalAllocation(scope) {
            this.modalAddPDF = true;
            this.select_file_classification = null;
            this.date_visit_aptitude_test = '';
            this.select_type_aptitudeTest = null;
            this.date_visit_health_exam = '';
            this.file_name_pdf = scope.item.file_name;
            this.dataPDF = scope.item;
        },
        handleCloseModalDeleteAptitude() {
            this.isShowModalDeleteAptitude = false;
            this.fileNameDeleteAptitude = '';
        },
        handleShowModalDeleteAptitude(scope) {
            this.isShowModalDeleteAptitude = true;
            this.fileNameDeleteAptitude = scope.item.file_name;
            this.ID_delete_aptitude = scope.item.id;
        },
        async handleDeleteAptitude() {
            try {
                const res = await deleteAptitude(`${urlAPIs.urlDeleteAptitude}/${this.ID_delete_aptitude}`);

                if (res['code'] === 200) {
                    this.$toast.success({
                        title: '成功',
                        content: this.$t('MESSAGE_DELETE_FILE_SUCCESS'),
                    });
                    this.handleCloseModalDeleteAptitude();
                    this.$emit('update-success');
                    this.getEmployeeDetailData();
                } else {
                    this.$toast.danger({
                        content: res.message,
                    });
                }
            } catch (error) {
                console.error('Unexpected error:', error);
            }
        },
        handleCloseModalDeleteHealthExam() {
            this.isShowModalDeleteHealthExam = false;
            this.fileNameDeleteHealthExam = '';
        },
        handleShowModalDeleteHealthExam(scope) {
            this.isShowModalDeleteHealthExam = true;
            this.fileNameDeleteHealthExam = scope.item.file_name;
            this.ID_delete_health = scope.item.id;
        },
        async handleDeleteHealthExam() {
            try {
                const res = await deleteHealthExamPDF(`${urlAPIs.urlDeleteHealthExam}/${this.ID_delete_health}`);

                if (res['code'] === 200) {
                    this.$toast.success({
                        title: '成功',
                        content: this.$t('MESSAGE_DELETE_FILE_SUCCESS'),
                    });
                    this.handleCloseModalDeleteHealthExam();
                    this.$emit('update-success');
                    this.getEmployeeDetailData();
                } else {
                    this.$toast.danger({
                        content: res.message,
                    });
                }
            } catch (error) {
                console.error('Unexpected error:', error);
            }
        },
        handleShowModalHistoryPDF() {
            this.modalShowHistoryPDF = true;
        },
        handleShowModalHealthExamHistoryPDF() {
            this.modalShowHealthExamHistoryPDF = true;
        },
        handleCloseHealthExam() {
            this.modalShowHealthExamHistoryPDF = false;
        },
        handleCloseModalHistoryPDF() {
            this.modalShowHistoryPDF = false;
        },
        onClickDisplayHistoryPDF(scope) {
            this.isShowHistoryPDF = true;
            this.history_health_pdf = scope.item.file_url;
        },
        setLoading(status = true) {
            if ([true, false].includes(status)) {
                this.overlay.show = status;
            }
        },
        onClickDisplay(scope) {
            this.isShowPDFView = true;
            this.detail_url_pdf = scope.item.file_url;
        },
        handleShowModalDelete(scope) {
            this.isShowModalDelete = true;
            this.fileNameDelete = scope.item.file_name;
            this.ID_delete = scope.item.id;
        },
        handleCloseModalDelete() {
            this.isShowModalDelete = false;
            this.fileNameDelete = '';
        },
        async handleUploadPDF(file) {
            // this.setLoading(true);
            try {
                const formData = new FormData();
                formData.append('file', file);

                const res = await postPDF(urlAPIs.urlUploadData, formData);

                if (res['file_name']) {
                    this.file_upload = res['id'];
                    console.log('PDF uploaded successfully:', res);
                } else {
                    this.$toast?.danger?.({
                        content: this.$t('MESSAGE_IPLOAD_FILE_FAILE'),
                    });
                }
            } catch (error) {
                console.error('Unexpected error:', error);
                this.$toast?.danger?.({
                    content: this.$t('MESSAGE_IPLOAD_FILE_FAILE'),
                });
            }
            // this.setLoading(false);
        },
        async handlePostPDFEmployee() {
            this.setLoading(true);
            try {
                const payload = {
                    employee_id: this.employee.id_upload,
                    file_id: this.id_file,
                };

                const res = await postPDFEmployeeDetail(urlAPIs.apiUploadPDFEmployee, payload);

                if (res.code === 200) {
                    this.$toast?.success?.({
                        content: this.$t('MESSAGE_UPLOAD_FILE_SUCCESS'),
                    });
                    this.handleCloseModalUploadPDF();
                    this.getEmployeeDetailData();
                } else {
                    this.$toast?.danger?.({
                        content: this.$t('MESSAGE_IPLOAD_FILE_FAILE'),
                    });
                }
            } catch (error) {
                console.error('Unexpected error:', error);
                this.$toast?.danger?.({
                    content: this.$t('MESSAGE_IPLOAD_FILE_FAILE'),
                });
            }
            this.setLoading(false);
        },
        async handleFileClassification() {
            this.setLoading(true);
            switch (this.select_file_classification) {
            case 1:
                this.handleUpdateDriversLicense();
                break;
            case 2:
                this.handleUpdateDrivingRecord();
                break;
            case 3:
                this.handleUpdateAptitude();
                break;
            case 4:
                this.handleUpdateHealthExam();
                break;
            default:
                break;
            }
            this.modalAddPDF = false;
            this.setLoading(false);
        },
        async handleUpdateDriversLicense() {
            try {
                const params = {
                    employee_id: this.employee.id_upload,
                };
                if (this.select_file_Driving === 1) {
                    params.surface_file_id = this.dataPDF.file.id;
                    params.back_file_id = null;
                } else if (this.select_file_Driving === 2) {
                    params.back_file_id = this.dataPDF.file.id;
                    params.surface_file_id = null;
                }

                const res = await updateDriverLicense(urlAPIs.urlUpdate, params);

                if (res['code'] === 200) {
                    this.$toast.success({
                        title: '成功',
                        content: this.$t('MESSAGE_DRIVER_LICENSE'),
                    });
                    this.$emit('update-success');
                    this.getEmployeeDetailData();
                } else {
                    this.$toast.danger({
                        content: res.message,
                    });
                }
            } catch (error) {
                console.error('Unexpected error:', error);
            }
        },
        async handleUpdateDrivingRecord() {
            try {
                const params = {
                    employee_id: this.employee.id_upload,
                    file_id: this.dataPDF.file.id,
                };

                const res = await updateDrivingRecordCertificate(urlAPIs.urlDrivingRecord, params);

                if (res['code'] === 200) {
                    this.$toast.success({
                        title: '成功',
                        content: this.$t('MESSAGE_DRIVING_RECORD'),
                    });
                    this.$emit('update-success');
                    this.getEmployeeDetailData();
                } else {
                    this.$toast.danger({
                        content: res.message,
                    });
                }
            } catch (error) {
                console.error('Unexpected error:', error);
            }
        },
        async handleUpdateAptitude() {
            try {
                const params = {
                    employee_id: this.employee.id_upload,
                    file_id: this.dataPDF.file.id,
                    type: this.select_type_aptitudeTest,
                    date_of_visit: this.date_visit_aptitude_test,
                };

                const res = await updateAptitudeAssessmentForm(urlAPIs.urlAptitude, params);

                if (res['code'] === 200) {
                    this.$toast.success({
                        title: '成功',
                        content: this.$t('MESSAGE_APTITUDE_TEST'),
                    });
                    this.$emit('update-success');
                    this.getEmployeeDetailData();
                } else {
                    this.$toast.danger({
                        content: res.message,
                    });
                }
            } catch (error) {
                console.error('Unexpected error:', error);
            }
        },
        async handleUpdateHealthExam() {
            try {
                const params = {
                    employee_id: this.employee.id_upload,
                    file_id: this.dataPDF.file.id,
                    date_of_visit: this.date_visit_health_exam,
                };

                const res = await updateHealthExaminationResults(urlAPIs.urlHealthExam, params);

                if (res['code'] === 200) {
                    this.$toast.success({
                        title: '成功',
                        content: this.$t('MESSAGE_HEALTH_EXAMINATION_RESULT_NOTIFICATION'),
                    });
                    this.$emit('update-success');
                    this.getEmployeeDetailData();
                } else {
                    this.$toast.danger({
                        content: res.message,
                    });
                }
            } catch (error) {
                console.error('Unexpected error:', error);
            }
        },
        async handleDelete() {
            try {
                const res = await deletePDF(`${urlAPIs.urlDelete}/${this.ID_delete}`, { id: this.ID_delete });

                if (res['code'] === 200) {
                    this.$toast.success({
                        title: '成功',
                        content: this.$t('MESSAGE_DELETE_FILE_SUCCESS'),
                    });
                    this.$emit('update-success');
                    this.handleCloseModalDelete();
                    this.getEmployeeDetailData();
                } else {
                    this.$toast.danger({
                        content: res.message,
                    });
                }
            } catch (error) {
                console.error('Unexpected error:', error);
            }
        },
        handleShowModalPDF() {
            this.isShowModalUploadPDF = true;
            this.fileNameCSVInput = null;
            this.$refs.selectFileInput.value = null;
        },
        handleCloseModalUploadPDF() {
            this.isShowModalUploadPDF = false;
        },
        onDragEnter(event) {
            event.preventDefault();
            event.stopPropagation();
            this.isDragging = true;
        },
        onDragLeave(event) {
            event.preventDefault();
            event.stopPropagation();
            // if (this.disableInput_surface) {
            //     return;
            // }

            // tránh flicker khi drag qua child (icon, text)
            const rect = event.currentTarget.getBoundingClientRect();
            const { clientX, clientY } = event;

            const isOutside =
                clientX < rect.left ||
                clientX > rect.right ||
                clientY < rect.top ||
                clientY > rect.bottom;

            if (isOutside) {
                this.isDragging = false;
            }
        },
        async onDrop(event) {
            event.preventDefault();
            event.stopPropagation();
            this.isDragging = false;

            const files = event.dataTransfer?.files || [];
            if (!files.length){
                return;
            }

            const file = files[0];

            if (!this.isValidPdf(file)) {
                this.$toast?.danger?.({
                    content: this.$t('MESSAGE_ONLY_PDF_ALLOWED') || 'Only PDF file is allowed',
                });
                return;
            }

            this.selectedFile = file;
            this.fileNameCSVInput = file.name;
            await this.handleUploadPDF(file);
            this.id_file = this.file_upload;
        },
        triggerSelectFileInput() {
            this.$refs.selectFileInput.click();
        },
        async getFileCSVInput() {
            const fileInput = document.getElementById('fileUpload');
            this.fileNameCSVInput = fileInput.files[0].name;
            this.selectedFile = fileInput.files[0];
            await this.handleUploadPDF(this.selectedFile);
            this.id_file = this.file_upload;
        },
        formatNumber(number) {
            let result = '';

            if (number) {
                result = number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }

            return result;
        },
        handleOpenModalHistory() {
            this.isShowModalHistory = true;
        },
        handleCloseModalPDF() {
            this.modalAddPDF = false;
        },
        handleNextIndex() {
            if (this.currentTabIndex < this.deviceHistoryData.length - 1) {
                this.currentTabIndex += 1;
            }
        },
        handlePrevIndex() {
            if (this.currentTabIndex > 0) {
                this.currentTabIndex -= 1;
            }
        },
        formatNumberWithCommas(number) {
            if (number) {
                return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            } else {
                return 0;
            }
        },
        formatInputNumber(value) {
            if (parseInt(value) < 1) {
                return 1;
            }

            return value;
        },
        async getEmployeeDetailData() {
            try {
                const URL = `${urlAPIs.apiGetEmployeeDetail}/${this.$route.params.id}`;

                const response = await getDetailEmployee(URL);

                if (response.code === 200) {
                    const EMPLOYEE_DATA = response.data.employee;

                    this.employee.id = EMPLOYEE_DATA?.employee_code;
                    this.employee.id_upload = EMPLOYEE_DATA?.id;
                    this.employee.name = this.handleTransformName(EMPLOYEE_DATA?.name);
                    this.employee.email = EMPLOYEE_DATA?.email;
                    this.employee.gender = this.handleTransformSex(EMPLOYEE_DATA?.sex);
                    this.employee.birthday = EMPLOYEE_DATA?.birthday;
                    this.employee.workingType = this.handleTransformWorkingType(EMPLOYEE_DATA?.job_type);
                    this.employee.employeeType = this.handleTransformEmployeeType(EMPLOYEE_DATA?.employee_type);
                    this.showTempWage = EMPLOYEE_DATA?.employee_type;
                    this.employee.licenseType = this.handleTransformLicenseType(EMPLOYEE_DATA?.license_type);
                    this.employee.hireStartDate = EMPLOYEE_DATA?.hire_start_date;
                    this.employee.retirementDate = EMPLOYEE_DATA?.retirement_date;
                    this.employee.welfareExpense = EMPLOYEE_DATA?.welfare_expense;
                    this.welfare_expense_history = EMPLOYEE_DATA?.employee_welfare_expenses;
                    this.equipmentData.employee_role = EMPLOYEE_DATA?.employee_role;

                    this.employee.name_phonetic = EMPLOYEE_DATA?.name_in_furigana;
                    this.employee.date_of_appointment = formatDateDisplay(EMPLOYEE_DATA?.date_of_election);
                    this.employee.address = EMPLOYEE_DATA?.address;
                    this.employee.contact_phone_number = EMPLOYEE_DATA?.user_contacts?.personal_tel;
                    this.employee.previous_employment_history = EMPLOYEE_DATA?.previous_employment_history;
                    this.employee.aptitude_test_date = EMPLOYEE_DATA.aptitude_assessment_forms.length > 0 ? EMPLOYEE_DATA.aptitude_assessment_forms[0]?.date_of_visit : '';
                    this.employee.medical_examination_date = EMPLOYEE_DATA.health_examination_results.length > 0 ? EMPLOYEE_DATA.health_examination_results[0]?.date_of_visit : '';
                    this.employee.age_appropriate_interview = EMPLOYEE_DATA.age_appropriate_interview;
                    const getDateFirstTime = (EMPLOYEE_DATA.aptitude_assessment_forms[0]?.file_history.find(item => item.type === 1));
                    const getDateAligible = (EMPLOYEE_DATA.aptitude_assessment_forms[0]?.file_history.find(item => item.type === 2));
                    const getDateSpecial = (EMPLOYEE_DATA.aptitude_assessment_forms[0]?.file_history.find(item => item.type === 3));
                    const getDateGeneral = (EMPLOYEE_DATA.aptitude_assessment_forms[0]?.file_history.find(item => item.type === 4));
                    this.employee.first_time = EMPLOYEE_DATA.aptitude_assessment_forms.length > 0 ? getDateFirstTime?.date_of_visit : '';
                    this.employee.aligible_age = EMPLOYEE_DATA.aptitude_assessment_forms.length > 0 ? getDateAligible?.date_of_visit : '';
                    this.employee.special = EMPLOYEE_DATA.aptitude_assessment_forms.length > 0 ? getDateSpecial?.date_of_visit : '';
                    this.employee.general = EMPLOYEE_DATA.aptitude_assessment_forms.length > 0 ? getDateGeneral?.date_of_visit : '';
                    this.employee.selected_classroom = EMPLOYEE_DATA?.beginner_driver_training_classroom;
                    this.employee.selected_practical = EMPLOYEE_DATA?.beginner_driver_training_practical;

                    this.items = EMPLOYEE_DATA.employee_pdf_uploads.map((item) => ({
                        id: item.id,
                        date_upload: item?.file?.created_at,
                        registered_by: item?.user?.name || '',
                        file_name: item?.file?.file_name || '',
                        file_url: item?.file?.file_url || '',
                        file: item?.file || null,
                    }));
                    this.itemsHistoryHealthExam = EMPLOYEE_DATA.health_examination_results[0]?.file_history.map((item) => {
                        return {
                            id: item?.id,
                            date: item?.updated_at,
                            registered_by: item?.user?.name || '',
                            file_name: item?.file?.file_name || '',
                            file_url: item?.file?.file_url || '',
                            file: item?.file || null,
                            date_of_visit: item?.date_of_visit || '',
                        };
                    });

                    this.itemsHistory = EMPLOYEE_DATA.aptitude_assessment_forms[0]?.file_history.map((item) => {
                        return {
                            id: item?.id,
                            date: item?.updated_at,
                            registered_by: item?.user?.name || '',
                            file_name: item?.file?.file_name || '',
                            file_url: item?.file?.file_url || '',
                            file: item?.file || null,
                            type: this.ListType.filter((type) => type.id === item?.type)[0]?.type || '',
                            date_of_visit: item?.date_of_visit || '',
                        };
                    });

                    console.log('this.employee.selected_practical', this.itemsHistory);

                    if (EMPLOYEE_DATA['employee_content'].length > 0) {
                        const EQUIPMENT_DATA = EMPLOYEE_DATA['employee_content'];

                        this.equipmentData.company_car = EQUIPMENT_DATA[0]['company_car'] || '';
                        this.equipmentData.etc_card = EQUIPMENT_DATA[0]['etc_card'] || '';
                        this.equipmentData.fuel_card = EQUIPMENT_DATA[0]['fuel_card'] || '';
                        this.equipmentData.other = EQUIPMENT_DATA[0]['other'] || '';

                        EQUIPMENT_DATA.forEach((item) => {
                            this.equipment_data_change_history.push(item);
                        });
                    }

                    this.history = response.data.department_history;

                    this.working_data = response.data.department_workings;

                    for (let i = 0; i < this.working_data.length; i++) {
                        if (this.working_data[i].color === 'orange') {
                            this.working_data[i].color = 'affiliation-base';
                        } else if (this.working_data[i].color === 'yellow') {
                            this.working_data[i].color = 'support-base';
                        } else if (this.working_data[i].color === 'gray') {
                            this.working_data[i].color = 'other-base';
                        } else {
                            console.log('[Error Working Data]');
                        }
                    }

                    if (EMPLOYEE_DATA.employee_mobile_info) {
                        for (let i = 0; i < EMPLOYEE_DATA.employee_mobile_info.length; i++) {
                            this.deviceHistoryData.push(EMPLOYEE_DATA.employee_mobile_info[i]);
                        }
                    } else {
                        this.deviceHistoryData = [
                            {
                                device_type: '',
                                tel: '',
                                android_id: '',
                                imei_number: '',
                                model_name: '',
                            },
                        ];
                    }
                }
            } catch (error) {
                console.log('[ERROR]', error);
            }
        },
        handleTransformDateToISO(date) {
            let result = '';

            if (date) {
                result = date.replace('T', ' ').slice(0, 19);
            }

            return result;
        },
        handleTransformName(string) {
            if (string.length > 0) {
                return string.replaceAll('/', '');
            } else {
                return '';
            }
        },
        handleTransformSex(gender) {
            if (gender === 0) {
                return this.$t('MALE');
            } else if (gender === 1) {
                return this.$t('FEMALE');
            } else {
                return '[Error Trasnform Sex]';
            }
        },
        handleTransformWorkingType(job_type) {
            if (job_type === 0) {
                return this.$t('DRIVER');
            } else if (job_type === 1) {
                return this.$t('DESKWORKER');
            } else if (job_type === 2) {
                return this.$t('OPERATOR');
            } else {
                return '[Error Transform Working Type]';
            }
        },
        handleTransformEmployeeType(employee_type) {
            if (employee_type === 0) {
                return this.$t('FULL_TIME');
            } else if (employee_type === 1) {
                return this.$t('PART_TIME');
            } else if (employee_type === 3) {
                return this.$t('TEMPORARY_STAFF');
            } else {
                return '[Error Transform Employee Type]';
            }
        },
        handleTransformEmployeeRole(employee_role) {
            if (employee_role === 1) {
                return this.$t('USER_MANAGEMENT.ROLE.CREW');
            } else if (employee_role === 2) {
                return this.$t('USER_MANAGEMENT.ROLE.CLERKS');
            } else if (employee_role === 3) {
                return this.$t('USER_MANAGEMENT.ROLE.TL');
            } else if (employee_role === 4) {
                return this.$t('USER_MANAGEMENT.ROLE.ACCOUNTING');
            } else if (employee_role === 5) {
                return this.$t('USER_MANAGEMENT.ROLE.GENERAL_AFFAIR');
            } else if (employee_role === 6) {
                return this.$t('USER_MANAGEMENT.ROLE.PERSONNEL_LABOR');
            } else if (employee_role === 7) {
                return this.$t('USER_MANAGEMENT.ROLE.AM_SM');
            } else if (employee_role === 8) {
                return this.$t('USER_MANAGEMENT.ROLE.DIRECTOR');
            } else if (employee_role === 9) {
                return this.$t('USER_MANAGEMENT.ROLE.DX_USER');
            } else if (employee_role === 10) {
                return this.$t('USER_MANAGEMENT.ROLE.DX_MANAGER');
            } else if (employee_role === 17) {
                return this.$t('USER_MANAGEMENT.ROLE.DEPARTMENT_OFFICE_STAFF');
            } else {
                console.log('[Error Transform Employee Type]');
                return '';
            }
        },
        handleTransformLicenseType(license_type) {
            if (license_type === 0) {
                return this.$t('ORDINARY');
            } else if (license_type === 1) {
                return this.$t('SEMI_MEDIUM_5T');
            } else if (license_type === 2) {
                return this.$t('SEMI_MEDIUM');
            } else if (license_type === 3) {
                return this.$t('MEDIUM_8T');
            } else if (license_type === 4) {
                return this.$t('MEDIUM');
            } else if (license_type === 5) {
                return this.$t('LARGE');
            } else if (license_type === 6) {
                return this.$t('TRACTION');
            } else {
                return '[Error Transform License Type]';
            }
        },
        setModalChangeHistory(status = true) {
            if ([true, false].includes(status)) {
                this.modalChangeHistory = status;
            } else {
                this.modalChangeHistory = true;
            }
        },
        onClickChangeHistory() {
            this.setModalChangeHistory(true);
        },
        async handleModalDisplay(CLASS_LIST, ID, DEPARTMENT) {
            const BASE_ID = ID;

            this.editModalData.department_working_id = BASE_ID;
            this.editModalData.department_working_name = DEPARTMENT;

            if (CLASS_LIST === 'other-base') {
                this.detailModalData.department_working_name = DEPARTMENT;

                await this.handleGetListCourse(this.editModalData.department_working_id);
                await this.handleGetDepartmentWorkingEdit(this.editModalData.department_working_id);

                this.modalOtherBase = true;
            } else {
                this.detailModalData = {
                    department_working_id: '',
                    department_working_name: '',
                    employee_grade: '',
                    driveable_route: [],
                    support_end_date: '',
                    employee_grade_2: '',
                    support_start_date: '',
                    boarding_employee_grade: '',
                    boarding_employee_grade_2: '',
                    midnight_working_time_hour: '',
                    scheduled_labor_table_hour: '',
                    transportation_compensation: '',
                    midnight_working_time_minute: '',
                    scheduled_labor_table_minute: '',
                    daily_transportation_compensation: '',
                    support_date_list: [],
                    temp_wage: null,
                    scheduled_work_start_time: '',
                    scheduled_work_end_time: '',
                };

                this.handleGetDepartmentWorkingDetail(BASE_ID);

                this.detailModalData.department_working_name = DEPARTMENT;

                this.modalAffiliationSupportBaseDetail = true;
            }
        },
        async handleGetDepartmentWorkingDetail(ID) {
            try {
                const URL = `${urlAPIs.apiGetDepartmentWorking}`;

                const PARAMS = {
                    employee_id: this.$route.params.id,
                    department_working_id: ID,
                };

                const response = await getDepartmentWorking(URL, PARAMS);

                if (response.code === 200) {
                    const EMPLOYEE_DATA_DETAIL = response.data.employee_data;

                    const EMPLOYEE_ROUTES_DETAIL = response.data.employee_courses || [];

                    const EMPLOYEE_WORKING_DEPARTMENT_DETAIL = response.data.employee_working_departments || [];

                    if (EMPLOYEE_DATA_DETAIL) {
                        this.detailModalData.employee_grade = EMPLOYEE_DATA_DETAIL.grade || '';
                        this.detailModalData.employee_grade_2 = EMPLOYEE_DATA_DETAIL.employee_grade_2 || '';
                        this.detailModalData.boarding_employee_grade = EMPLOYEE_DATA_DETAIL.boarding_employee_grade || '';
                        this.detailModalData.boarding_employee_grade_2 = EMPLOYEE_DATA_DETAIL.boarding_employee_grade_2 || '';
                        this.detailModalData.transportation_compensation = EMPLOYEE_DATA_DETAIL.transportation_compensation || 0;
                        this.detailModalData.daily_transportation_compensation = EMPLOYEE_DATA_DETAIL.daily_transportation_cp || 0;
                        this.detailModalData.midnight_working_time_hour = EMPLOYEE_DATA_DETAIL.midnight_worktime_hour || 0;
                        this.detailModalData.midnight_working_time_minute = EMPLOYEE_DATA_DETAIL.midnight_worktime_minutes || 0;
                        this.detailModalData.scheduled_labor_table_hour = EMPLOYEE_DATA_DETAIL.scheduled_labor_hour || 0;
                        this.detailModalData.scheduled_labor_table_minute = EMPLOYEE_DATA_DETAIL.scheduled_labor_minutes || 0;
                        this.detailModalData.scheduled_work_start_time = EMPLOYEE_DATA_DETAIL.scheduled_work_start_time || '';
                        this.detailModalData.scheduled_work_end_time = EMPLOYEE_DATA_DETAIL.scheduled_work_end_time || '';

                        this.detailModalData.driveable_route = EMPLOYEE_ROUTES_DETAIL || [];

                        this.detailModalData.support_date_list = EMPLOYEE_WORKING_DEPARTMENT_DETAIL || [];

                        this.detailModalData.temp_wage = parseInt(EMPLOYEE_DATA_DETAIL.temp_wage) || null;
                    }
                }
            } catch (error) {
                console.log(error);
            }
        },
        handleDisplayEditModal() {
            this.modalAffiliationSupportBaseDetail = false;

            this.handleGetListCourse(this.editModalData.department_working_id);

            this.handleGetDepartmentWorkingEdit(this.editModalData.department_working_id);

            this.modalAffiliationSupportBaseEdit = true;
        },
        async handleGetListCourse(ID) {
            try {
                const URL = `${urlAPIs.apiGetListCourse}/${ID}`;

                const response = await getListCourse(URL);

                if (response.code === 200) {
                    this.listCourse = response.data;
                }
            } catch (error) {
                console.log(error);
            }
        },
        async handleGetDepartmentWorkingEdit(ID) {
            try {
                const URL = `${urlAPIs.apiGetDepartmentWorking}`;

                const PARAMS = {
                    employee_id: this.$route.params.id,
                    department_working_id: ID,
                };

                const response = await getDepartmentWorking(URL, PARAMS);

                if (response.code === 200) {
                    const EMPLOYEE_DATA = response.data.employee_data;

                    const LIST_SELECTED_EMPLOYEE_COURSE = response.data.employee_courses || [];

                    const EMPLOYEE_WORKING_DEPARTMENT = response.data.employee_working_departments || [];

                    if (EMPLOYEE_DATA) {
                        this.editModalData.employee_grade = EMPLOYEE_DATA.grade;
                        this.editModalData.employee_grade_2 = EMPLOYEE_DATA.employee_grade_2;
                        this.editModalData.boarding_employee_grade = EMPLOYEE_DATA.boarding_employee_grade;
                        this.editModalData.boarding_employee_grade_2 = EMPLOYEE_DATA.boarding_employee_grade_2;
                        this.editModalData.transportation_compensation = EMPLOYEE_DATA.transportation_compensation || 0;
                        this.editModalData.daily_transportation_compensation = EMPLOYEE_DATA.daily_transportation_cp || 0;
                        this.editModalData.midnight_working_time_hour = EMPLOYEE_DATA.midnight_worktime_hour || 0;
                        this.editModalData.midnight_working_time_minute = EMPLOYEE_DATA.midnight_worktime_minutes || 0;
                        this.editModalData.scheduled_labor_table_hour = EMPLOYEE_DATA.scheduled_labor_hour || 0;
                        this.editModalData.scheduled_labor_table_minute = EMPLOYEE_DATA.scheduled_labor_minutes || 0;
                        this.editModalData.temp_wage = parseInt(EMPLOYEE_DATA.temp_wage) || null;
                    }

                    for (let i = 0; i < this.listCourse.length; i++) {
                        this.listRouteMaster.push(
                            { value: this.listCourse[i].id, text: this.listCourse[i].course_code }
                        );
                    }

                    for (let j = 0; j < LIST_SELECTED_EMPLOYEE_COURSE.length; j++) {
                        this.listSelected.push(LIST_SELECTED_EMPLOYEE_COURSE[j].id);

                        this.editModalData.driveable_route.push(
                            { value: LIST_SELECTED_EMPLOYEE_COURSE[j].id, text: LIST_SELECTED_EMPLOYEE_COURSE[j].course_code }
                        );

                        this.handleUpdateListDeliveryCourseCreation();
                    }

                    this.editModalData.support_date_list = EMPLOYEE_WORKING_DEPARTMENT;

                    this.listSupportDate = EMPLOYEE_WORKING_DEPARTMENT;
                }
            } catch (error) {
                console.log(error);
            }
        },
        handleReturnButtonClicked() {
            this.modalAffiliationSupportBaseEdit = false;

            this.handleCloseModalEdit();
        },
        handleButtonCloseOtherBaseClicked() {
            this.modalOtherBase = false;
            this.modalAffiliationSupportBaseEdit = true;

            // this.handleCloseModalEdit();
        },
        handleValidateEdit(data) {
            if (isNaN(data.grade)) {
                return this.$t('VALIDATE_EMPLOYEE_HALF_NUMBER', { section: this.$t('EMPLOYEE_GRADE') });
            }

            if (isNaN(data.employee_grade_2)) {
                return this.$t('VALIDATE_EMPLOYEE_HALF_NUMBER', { section: this.$t('EMPLOYEE_GRADE_2') });
            }

            if (isNaN(data.boarding_employee_grade)) {
                return this.$t('VALIDATE_EMPLOYEE_HALF_NUMBER', { section: this.$t('BOARDING_EMPLOYEE_GRADE') });
            }

            if (isNaN(data.boarding_employee_grade_2)) {
                return this.$t('VALIDATE_EMPLOYEE_HALF_NUMBER', { section: this.$t('BOARDING_EMPLOYEE_GRADE_2') });
            }

            if (isNaN(data.transportation_compensation)) {
                return this.$t('VALIDATE_EMPLOYEE_HALF_NUMBER', { section: this.$t('TRANSPORTATION_COMPENSATION') });
            }

            if (isNaN(data.daily_transportation_cp)) {
                return this.$t('VALIDATE_EMPLOYEE_HALF_NUMBER', { section: this.$t('DAILY_TRANSPORTATION_COMPENSATION') });
            }

            return null;
        },
        async handleSaveButtonClicked() {
            const URL = `${urlAPIs.apiPostEmployee}/${this.$route.params.id}`;

            const UPDATE_DATA = {
                department_working_id: this.editModalData.department_working_id,
                grade: this.editModalData.employee_grade,
                employee_grade_2: this.editModalData.employee_grade_2,
                boarding_employee_grade: this.editModalData.boarding_employee_grade,
                boarding_employee_grade_2: this.editModalData.boarding_employee_grade_2,
                transportation_compensation: this.editModalData.transportation_compensation || 0,
                daily_transportation_cp: this.editModalData.daily_transportation_compensation || 0,
                midnight_worktime_hour: this.editModalData.midnight_working_time_hour || 0,
                midnight_worktime_minutes: this.editModalData.midnight_working_time_minute || 0,
                scheduled_labor_hour: this.editModalData.scheduled_labor_table_hour || 0,
                scheduled_labor_minutes: this.editModalData.scheduled_labor_table_minute || 0,
                working_date: this.listSupportDate,
                employee_courses: this.listSelected,
                temp_wage: parseInt(this.editModalData.temp_wage) || null,
            };

            const validate = this.handleValidateEdit(UPDATE_DATA);

            if (validate === null) {
                try {
                    const response = await postEmployee(URL, UPDATE_DATA);

                    if (response.code === 200) {
                        MakeToast({
                            variant: 'success',
                            title: this.$t('SUCCESS'),
                            content: '編集が完了しました',
                        });

                        this.modalAffiliationSupportBaseEdit = false;

                        this.getEmployeeDetailData();
                        this.handleCloseModalEdit();
                    }
                } catch (error) {
                    console.log(error);

                    MakeToast({
                        variant: 'danger',
                        title: this.$t('DANGER'),
                        content: '勤務期間は既に利用されているため登録できません',
                    });
                }
            } else {
                MakeToast({
                    variant: 'warning',
                    title: this.$t('WARNING'),
                    content: validate,
                });
            }
        },
        handleAddCourse(id) {
            this.listSelected.push(id);
            this.handleUpdateListDeliveryCourseCreation();
        },
        handleDeleteCourse(id) {
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
        handleCloseModalEdit() {
            this.editModalData = {
                department_working_id: '',
                department_working_name: '',
                employee_grade: '',
                driveable_route: [],
                support_end_date: '',
                employee_grade_2: '',
                support_start_date: '',
                boarding_employee_grade: '',
                boarding_employee_grade_2: '',
                midnight_working_time_hour: '',
                scheduled_labor_table_hour: '',
                transportation_compensation: '',
                midnight_working_time_minute: '',
                scheduled_labor_table_minute: '',
                daily_transportation_compensation: '',
                support_date_list: [],
                temp_wage: null,
            };

            this.support_start_date = '';
            this.support_end_date = '';

            this.listSelected = [];
            this.listSupportDate = [];
            this.listRouteMaster = [];
        },
        handleCloseModalDetail() {
            this.detailModalData = {
                employee_grade: '',
                driveable_route: [],
                support_end_date: '',
                employee_grade_2: '',
                support_start_date: '',
                boarding_employee_grade: '',
                boarding_employee_grade_2: '',
                midnight_working_time_hour: '',
                scheduled_labor_table_hour: '',
                transportation_compensation: '',
                midnight_working_time_minute: '',
                scheduled_labor_table_minute: '',
                daily_transportation_compensation: '',
                support_date_list: [],
                temp_wage: null,
                scheduled_work_start_time: '',
                scheduled_work_end_time: '',
            };
        },
        resetSupportStartDate() {
            this.support_start_date = '';
        },
        resetSupportEndDate() {
            this.support_end_date = '';
        },
        addSupportDate() {
            if (this.support_start_date) {
                this.listSupportDate.push(
                    { start_date: this.support_start_date, end_date: this.support_end_date, is_support: 1 }
                );

                this.support_start_date = '';
                this.support_end_date = '';
            } else {
                MakeToast({
                    variant: 'warning',
                    title: this.$t('WARNING'),
                    content: 'Start date is required.',
                });
            }
        },
        removeSupportDate(start_date) {
            const idx = this.listSupportDate.findIndex(item => item.start_date === start_date);

            if (idx > -1) {
                this.listSupportDate.splice(idx, 1);
            }
        },
        handleGetListTime() {
            const START_HOUR = 0;
            const END_HOUR = 23;

            for (let i = START_HOUR; i <= END_HOUR; i++) {
                this.listTimeHour.push({
                    value: i,
                    text: i + '',
                });
            }

            const LIST_MIN = [0, 10, 20, 30, 40, 50];

            for (let i = 0; i < LIST_MIN.length; i++) {
                this.listTimeMinute.push({
                    value: LIST_MIN[i],
                    text: LIST_MIN[i] + '',
                });
            }
        },
        handleButtonReturnToListClicked() {
            this.$router.push({ path: '/master-manager/employee-master' });
        },
        handleNavigateToEditScreen() {
            this.$router.push({ name: 'EmployeeMasterEdit', params: { id: this.$route.params.id }});
        },
        handleShowModalEquipmentDataHistory() {
            this.modalEquipmentData = true;
        },
    },
};
</script>

<style lang="scss" scoped>
@import '@/scss/variables';

::v-deep .modal-history-header {
  color: #FFFFFF !important;
  background-color: #0F0448 !important;
}

.fa-caret {
  cursor: pointer;
}

.button-close-modal-history {
  right: 0;
  font-size: 28px;
  position: absolute;
  border-radius: 45px;
  color: #FFFFFF !important;
  background-color: #0F0448;
}

.text-loading {
  margin-top: 10px;
}

.history-text {
  text-decoration: underline;
}

.employee-master-detail {
  overflow: hidden;
  min-height: calc(100vh - 89px);

  &__title-header,
  &__basic-data,
  &__working-data,
  &__device-data,
  &__equipment-data {
    margin-bottom: 20px;
  }

  &__basic-data,
  &__working-data,
  &__device-data,
  &__equipment-data {

    .item-data {
      margin-bottom: 10px;

      label {
        font-weight: 500;
      }

      span.text-link {
        font-weight: 500;
        text-decoration: underline;
        cursor: pointer;
      }

      .zone-node {
        margin-bottom: 25px;
      }

      .history-button {
        min-width: 150px;
        color: #FFFFFF;
        background-color: #FF8A1F;
      }

    }
	.title_new-driver-training {
		margin-top: 20px;
	}
  }
}

.table-history {
  width: 100%;
  border-spacing: 0;
  border: 1px solid #ddd;
  border-collapse: collapse;

  .table-history-th {
    color: $white;
    text-align: center;
    vertical-align: middle;
    background-color: $tolopea;
  }

  .table-history-td {
    text-align: center;
    vertical-align: middle;
  }
}

.other-base-content {
  height: 600px;

  .other-base-content-text {
    top: 40%;
    font-size: 28px;
    text-align: center;
    position: relative;
    transform: translateY(-50%);
    -ms-transform: translateY(-40%);
    -webkit-transform: translateY(-40%);
  }

  .second-line {
    white-space: pre;
  }
}

.button-to-edit-screen {
  width: 150px;
  float: right;
  color: $white;
  font-weight: bold;
  background-color: $west-side;
}

.button-to-edit-screen:hover {
  opacity: .8;
  color: $white;
}

.button-driveable-route {
  width: 100px;
  color: #000000;
  border-radius: 10px;
  background-color: #E9ECEF;
  border: 1px solid #7C7C7C !important;
}

.button-driveable-route:hover {
  pointer-events: none;
}

label {
  font-weight: bold;
}

.button-return {
  width: 150px;
  float: left;
  color: $white;
  font-weight: bold;
  background-color: $west-side;
}

.button-return:hover {
  opacity: .8;
  color: $white;
}

.button-save {
  width: 150px;
  float: right;
  color: $white;
  font-weight: bold;
  background-color: $west-side;
}

.button-save:hover {
  opacity: .8;
  color: $white;
}

.button-support-addition {
  margin-top: 30px;
}

.button-support-remove {
  margin-top: 30px;
}

.btn-reset {
  cursor: pointer;
}

.button-return-to-list {
  float: left;
  width: 150px;
  color: $white;
  margin-left: 10px;
  font-weight: bold;
  background-color: $west-side;
}

.button-return-to-list:hover {
  opacity: .8;
  color: $white;
}

.button-navigate-to-edit {
  float: left;
  width: 150px;
  color: $white;
  margin-left: 10px;
  font-weight: bold;
  background-color: $west-side;
}

.button-navigate-to-edit :hover {
  opacity: .8;
  color: $white;
}

.title_history {
    display: flex;
    justify-content: flex-end;
}

.table_history {
        ::v-deep #table_history-list {
			margin-top: 20px;
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
                        width: 30% !important;
                    }
					th.th-support-base-history-pdf {
						width: 10% !important;
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
					td.td-option {
						display: flex;
						justify-content: center;
						align-items: center;
					}
                }
            }
        }
}
.modal-btn {
    // margin-top: 10px;
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
.update_first {
    border: 2px dashed #C4C4C4;
    border-radius: 5px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 20px 0;
    transition: border-color 0.2s ease, background-color 0.2s ease, box-shadow 0.2s ease, transform 0.1s ease;

    .content_upload {
        margin-top: 10px;
        font-size: 13px;
        font-weight: 200;
        color: #7A7A7A;
        transition: color 0.2s ease;
    }
    .content_or {
        margin: 10px 0;
        font-size: 13px;
        font-weight: 200;
        color: #7A7A7A;
    }

    .icon_upload {
        padding: 10px;
        font-size: 50px;
        color: #7A7A7A;
        transition: transform 0.2s ease, color 0.2s ease;
    }

    // 👉 trạng thái đang drag vào
    &.update_first--dragging {
        border-color: #989898;
        background-color: rgba(215, 215, 215, 0.06);
        box-shadow: 0 0 0 2px rgba(163, 163, 163, 0.25);
        transform: translateY(-1px);

        .icon_upload {
            color: #626060;
            transform: scale(1.1);
        }

        .content_upload {
            color: #626060;
            font-weight: 400;
        }
    }
}
.dropzone__file-name {
  font-size: 14px;
  margin-top: 10px;
  color: #333;
  word-break: break-all;
}
.btn-history {
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
</style>
