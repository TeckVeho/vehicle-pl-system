<template>
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
				<p class="text-overlay">{{ $t("PLEASE_WAIT") }}</p>
			</div>
		</template>

		<div class="vehicle-master">
			<b-col>
				<!-- Header -->
				<div class="vehicle-master__header">
					<vHeaderPage>
						{{ $t("ROUTER_VEHICLE_MASTER") }}
					</vHeaderPage>
				</div>

				<!-- Filter -->
				<div class="vehicle-master__filter">
					<vHeaderFilter>
						<template #zone-filter>
							<b-col>
								<b-row>
									<span class="text-clear-all" @click="onClickClearAll()">
										{{ $t('CLEAR_ALL') }}
									</span>
								</b-row>

								<div class="filter-item">
									<b-row v-if="role !== 'crew'" class="mb-3">
										<b-col cols="12" sm="12" md="12" lg="6" class="reset-padding-b-col">
											<b-input-group class="my-input-group">
												<b-input-group-prepend>
													<b-input-group-text>
														<input v-model="filter.department_id.status" class="status-filter-department-id" type="checkbox" @change="handleChangeDepartmentID" :disabled="role === 'tl'">
													</b-input-group-text>
												</b-input-group-prepend>

												<b-input-group-prepend>
													<b-input-group-text>
														<span class="prepend-filter-text">{{ $t('VEHICLE_MASTER.FILTERS.DEPARTMENT') }}</span>
													</b-input-group-text>
												</b-input-group-prepend>

												<vMultiselect
													v-model="filter.department_id.value"
													:options="filteredDepartmentOptions"
													:disabled="!filter.department_id.status || role === 'tl'"
													track-by="id"
													label="department_name"
													:searchable="true"
													:show-labels="false"
													:placeholder="$t('PLEASE_SELECT')"
													:close-on-select="false"
													:multiple="true"
												>
													<template slot="noResult">
														<span>結果が見つかりません</span>
													</template>

													<template slot="noOptions">
														<span>選択肢がありません</span>
													</template>
												</vMultiselect>
											</b-input-group>
										</b-col>
									</b-row>

									<b-row v-if="role !== 'crew' && role !== 'tl'" class="mb-3">
										<b-col cols="12" sm="12" md="12" lg="6" class="reset-padding-b-col">
											<div class="d-flex flex-row w-100">
												<b-button
													@click="handleChangeDepartmentQueryType(1)"
													variant="link"
													:class="['btn-department-query-type', { 'active': activeDepartmentQueryType === 1 }]"
												>
													<span>全拠点一括選択</span>
												</b-button>

												<b-button
													@click="handleChangeDepartmentQueryType(2)"
													variant="link"
													:class="['btn-department-query-type ml-3', { 'active': activeDepartmentQueryType === 2 }]"
												>
													<span>第1事業部一括選択 {{ getDivisionInfo(2) }}</span>
												</b-button>

												<b-button
													@click="handleChangeDepartmentQueryType(3)"
													variant="link"
													:class="['btn-department-query-type ml-3', { 'active': activeDepartmentQueryType === 3 }]"
												>
													<span>第2事業部一括選択 {{ getDivisionInfo(3) }}</span>
												</b-button>
											</div>
										</b-col>
									</b-row>

									<b-row v-if="role !== 'crew' && getSelectedDepartmentCount() > 0" class="mb-2">
										<b-col cols="12" sm="12" md="12" lg="6" class="reset-padding-b-col">
											<div class="selected-departments-info">
												<span class="text-info">
													選択された拠点数: {{ getSelectedDepartmentCount() }} 拠点
												</span>
											</div>
										</b-col>
									</b-row>

									<b-row class="mb-3">
										<b-col cols="12" sm="12" md="12" lg="6" class="reset-padding-b-col">
											<b-input-group>
												<b-input-group-prepend is-text class="checkbox-holder">
													<input v-model="filter.vehicle_no.status" class="status-filter-vehicle-no" type="checkbox" @change="handleChangeVehicleNo">
												</b-input-group-prepend>

												<b-input-group-prepend is-text class="prepend-filter">
													<span class="prepend-filter-text">{{ $t('VEHICLE_MASTER.FILTERS.VEHICLE_NO') }}</span>
												</b-input-group-prepend>

												<b-form-input id="filter-vehicle-no" v-model="filter.vehicle_no.value" :disabled="!filter.vehicle_no.status" placeholder="" />
											</b-input-group>
										</b-col>
									</b-row>

									<b-row class="mb-3">
										<b-col cols="12" sm="12" md="12" lg="6" class="reset-padding-b-col">
											<b-input-group>
												<b-input-group-prepend is-text class="checkbox-holder">
													<input v-model="filter.number_plate.status" class="status-filter-number-plate" type="checkbox" @change="handleChangeNumberPlate">
												</b-input-group-prepend>

												<b-input-group-prepend is-text class="prepend-filter">
													<span class="prepend-filter-text">{{ $t('VEHICLE_MASTER.FILTERS.NUMBER_PLATE') }}</span>
												</b-input-group-prepend>

												<b-form-input id="filter-number-plate" v-model="filter.number_plate.value" :disabled="!filter.number_plate.status" placeholder="" />
											</b-input-group>
										</b-col>
									</b-row>

									<b-row class="mb-3">
										<b-col cols="12" sm="12" md="12" lg="6" class="reset-padding-b-col">
											<b-input-group>
												<b-input-group-prepend is-text class="checkbox-holder">
													<input v-model="filter.vehicle_inspection_expiry_date.status" class="status-filter-number-plate" type="checkbox" @change="handleChangeVehicleInspectionExpiryDate">
												</b-input-group-prepend>

												<b-input-group-prepend is-text class="prepend-filter">
													<span class="prepend-filter-text">{{ '車検月' }}</span>
												</b-input-group-prepend>

												<date-picker
													v-model="filter.vehicle_inspection_expiry_date.value"
													type="month"
													format="YYYY-MM"
													value-type="format"
													:lang="japaneseLang"
													:disabled="!filter.vehicle_inspection_expiry_date.status"
												/>
											</b-input-group>
										</b-col>
									</b-row>
									<b-row class="mb-3">
										<b-col cols="12" sm="12" md="12" lg="6" class="reset-padding-b-col">
											<b-form-checkbox
												id="checkbox-1"
												v-model="filter.vehicle_scrapped.status"
												name="checkbox-1"
												value="1"
												unchecked-value="0"
												@change="updateVehicleScrappedStatus"
											>
												廃車を非表示にする
											</b-form-checkbox>
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

				<!-- Dashboard and Actions -->
				<div class="vehicle-master__dashboard-actions d-flex justify-content-between align-items-end">
					<!-- Dashboard -->
					<div class="vehicle-dashboard">
						<div class="dashboard-title">
							<h5>車両統計</h5>
						</div>
						<div class="dashboard-cards">
							<div class="dashboard-card">
								<div class="card-icon">
									<i class="fas fa-car" />
								</div>
								<div class="card-content">
									<div class="card-number">{{ dashboard.totalFilter }}</div>
									<div class="card-label">総台数</div>
								</div>
							</div>
							<div class="dashboard-card warning" :class="{ 'active': activeDashboardCard === 'current' }" @click="handleClickCurrentMonthCard">
								<div class="card-icon">
									<i class="fas fa-exclamation-triangle" />
								</div>
								<div class="card-content">
									<div class="card-number">{{ dashboard.totalNow }}</div>
									<div class="card-label">当月車検期限台数</div>
								</div>
							</div>
							<div class="dashboard-card info" :class="{ 'active': activeDashboardCard === 'next' }" @click="handleClickNextMonthCard">
								<div class="card-icon">
									<i class="fas fa-calendar-alt" />
								</div>
								<div class="card-content">
									<div class="card-number">{{ dashboard.totalNext }}</div>
									<div class="card-label">来月車検期限台数</div>
								</div>
							</div>
						</div>
					</div>

					<!-- Actions -->
					<div class="vehicle-master__actions d-flex">
						<b-button class="btn-setting-table mr-2" @click="openNotificationRecipientsModal">
							<i class="fas fa-bell mr-1" />
							<span>{{ $t('BUTTON.NOTIFICATION_RECIPIENTS_SETTING') }}</span>
						</b-button>
						<b-button class="btn-setting-table mr-2" @click="handleExportCSV()">
							<span>CSVエクスポート</span>
						</b-button>

						<b-button class="btn-setting-table mr-2" @click="showModalSettingTable = true">
							<span>テーブル列の設定</span>
						</b-button>

						<b-button v-if="hasAccessCreate.includes(role)" class="btn-registration" @click="onClickRegister()">
							<span>{{ $t('BUTTON.REGISTRATION') }}</span>
						</b-button>
					</div>
				</div>

				<!-- Table -->
				<div class="vehicle-master__table position-relative" ref="tableContainer">
					<template v-if="fields.length > 0">
						<!-- Table Fixed -->
						<b-table-simple
							striped
							bordered
							responsive
							:class="['table-fixed', fields.length <= 6 ? 'overflow-y-scroll' : '']"
							id="table-vehicle-master"
							@scroll.native="handleScrollFirstTable"
						>
							<thead>
								<tr>
									<th
										:key="field.key"
										v-for="(field, fieldIndex) in displayedFields"
										:class="[field.thClass, field.key, field.is_locked ? 'locked-th' : '']"
										@click="field.sortable ? handleSort({ sortBy: field.key, sortDesc: false }) : null"
										:style="[{ cursor: field.sortable ? 'pointer' : 'default' }, calculateLockedColumnStyle(field, fieldIndex, 1)]"
									>
										<span>{{ field.label }}</span>
										<i v-if="field.sortable && currentSort.field !== field.key" class="fas fa-sort" />
										<i v-if="field.sortable && currentSort.field === field.key && currentSort.direction === 'asc'" class="fas fa-sort-up" />
										<i v-if="field.sortable && currentSort.field === field.key && currentSort.direction === 'desc'" class="fas fa-sort-down" />
									</th>
								</tr>
							</thead>
						</b-table-simple>

						<!-- Table Scroll -->
						<b-table-simple
							striped
							bordered
							responsive
							class="table-scroll"
							id="table-vehicle-master"
							@scroll.native="handleScrollSecondTable"
						>
							<thead>
								<tr>
									<th
										:key="field.key"
										v-for="(field, fieldIndex) in displayedFields"
										:class="[field.thClass, field.key, field.is_locked ? 'locked-th' : '']"
										@click="field.sortable ? handleSort({ sortBy: field.key, sortDesc: false }) : null"
										:style="[{ cursor: field.sortable ? 'pointer' : 'default' }, calculateLockedColumnStyle(field, fieldIndex, 2)]"
									>
										<span>{{ field.label }}</span>
										<i v-if="field.sortable && currentSort.field !== field.key" class="fas fa-sort" />
										<i v-if="field.sortable && currentSort.field === field.key && currentSort.direction === 'asc'" class="fas fa-sort-up" />
										<i v-if="field.sortable && currentSort.field === field.key && currentSort.direction === 'desc'" class="fas fa-sort-down" />
									</th>
								</tr>
							</thead>

							<tbody>
								<tr v-for="(item, rowIndex) in items" :key="item.id" @click="onClickDetail(item.id)" style="cursor: pointer;">
									<td
										:key="field.key"
										v-for="(field, fieldIndex) in displayedFields"
										:style="field.is_locked ? calculateLockedColumnStyleTd(field, fieldIndex, rowIndex, 2) : {}"
										:class="[field.tdClass ? field.tdClass(item[field.key], field.key, item) : '', field.is_locked ? 'locked-td' : '', getInspectionExpirationDateClass(item.inspection_expiration_date_flag)]"
									>
										<template v-if="field.key === 'department_id'">
											{{ item.department_names || '' }}
										</template>

										<template v-else-if="field.key === 'no_number_plate'">
											{{ getDepartmentName(item.plate_history[0] ? item.plate_history[0].no_number_plate : '') }}
										</template>

										<template v-else-if="field.key === 'detail'">
											<i class="fas fa-eye" @click.stop="onClickDetail(item.id)" />
										</template>

										<template v-else-if="field.key === 'delete'">
											<i class="fas fa-trash" @click.stop="() => { tempID = item.id; showModalConfirmDeletion = true; }" />
										</template>

										<template v-else-if="field.key === 'license_classification'">
											<span>{{ handleGetCertificateByVehicleTotalWeight(item.vehicle_total_weight) }}</span>
										</template>

										<template v-else>
											{{ isNumericField(field.key) ? formatNumberWithCommas(item[field.key]) : isDateField(field.key) ? formatDateDisplay(item[field.key]) : item[field.key] }}
										</template>
									</td>
								</tr>

								<tr v-if="items.length > 0" class="summary-row">
									<td
										:key="field.key"
										v-for="(field, fieldIndex) in displayedFields"
										:class="[field.thClass, field.is_locked ? 'locked-td' : '', 'sum-th']"
										:style="field.is_locked ? calculateLockedColumnStyleTd(field, fieldIndex, -1, 2) : {}"
									>
										<template v-if="field.key === 'voluntary_premium'">
											<strong>{{ formatNumberWithCommas(calculateSum('voluntary_premium')) }}</strong>
										</template>

										<template v-else-if="field.key === 'maintenance_lease_fee'">
											<strong>{{ formatNumberWithCommas(calculateSum('maintenance_lease_fee')) }}</strong>
										</template>

										<template v-else />
									</td>
								</tr>

								<tr v-if="items.length === 0">
									<td :colspan="fields.length" class="text-center">
										<span>{{ $t("TABLE_EMPTY") }}</span>
									</td>
								</tr>
							</tbody>
						</b-table-simple>
					</template>

					<template v-else>
						<b-table-simple>
							<thead>
								<tr>
									<th class="text-center" style="background-color: #0f0448; color: white;">車両マスターテーブル</th>
								</tr>
							</thead>

							<tbody>
								<tr>
									<td class="text-center" style="border: 1px solid #eeeeee;">列が選択されていません。</td>
								</tr>
							</tbody>
						</b-table-simple>
					</template>
				</div>

				<!-- Pagination -->
				<div v-if="pagination.total_rows > 20 && fields.length > 0" class="vehicle-master__pagination">
					<div class="select-per-page">
						<div>
							<label for="per-page">1ページ毎の表示数</label>
						</div>
						<b-form-select
							id="per-page"
							v-model="pagination.per_page"
							:options="optionsPerPage"
							size="sm"
							@change="handleChangePerPage()"
						/>
					</div>

					<div class="show-pagination">
						<vPagination
							:aria-controls="'table-vehicle-master'"
							:current-page="pagination.current_page"
							:per-page="pagination.per_page"
							:total-rows="pagination.total_rows"
							:next-class="'next'"
							:prev-class="'prev'"
							@currentPageChange="getCurrentPage"
						/>
					</div>
				</div>
			</b-col>
		</div>

		<!-- Confirm Deletion Modal -->
		<b-modal v-model="showModalConfirmDeletion" id="modal-cf" centered no-close-on-backdrop no-close-on-esc hide-header :static="true" header-class="modal-custom-header" content-class="modal-custom-body" footer-class="modal-custom-footer">
			<template #default>
				<span>この車両を削除してもよろしいですか？</span>
			</template>

			<template #modal-footer>
				<b-button class="modal-btn btn-cancel" :disabled="overlay.show" @click="showModalConfirmDeletion = false">
					{{ $t("NO") }}
				</b-button>

				<b-button class="modal-btn btn-apply" :disabled="overlay.show" @click="onClickDelete()">
					{{ $t("YES") }}
				</b-button>
			</template>
		</b-modal>

		<!-- Table Column Setting Modal -->
		<b-modal v-model="showModalSettingTable" id="modal-setting-table" ref="settingTableModal" centered size="xl" scrollable :static="true" no-close-on-esc no-close-on-backdrop>
			<template #modal-title>
				<span>テーブル列の設定</span>
			</template>

			<template #default>
				<div class="d-flex w-100">
					<span>列は左から右、上から下へ順番に並んでいます。</span>
				</div>

				<div class="scrollable-wrapper">
					<div class="draggable-container">
						<div class="draggable-grid">
							<div
								:key="field.key"
								v-for="(field, fieldIndex) in fieldsConfig"
								:class="['drag-th', fieldIndex === 0 ? 'first' : '']"
							>
								<template v-if="field.key === 'action'">
									<div class="div-pointer" @click="handleOpenAddColumnModal()">
										<div :class="[field.is_display ? 'upper-active' : 'upper-inactive']">
											<i class="far fa-plus-square" />
										</div>

										<div :class="[field.is_display ? 'lower-active' : 'lower-inactive', 'd-flex justify-content-center align-items-center']">
											<span class="action-text">新しい列を追加する</span>
										</div>
									</div>
								</template>

								<template v-else>
									<div :class="[field.is_display ? 'upper-active' : 'upper-inactive']">
										<b-form-checkbox
											v-model="field.is_display"
											class="mr-2 th-checkbox"
											size="lg"
											@change="handleFieldDisplayChange(field)"
										/>

										<span class="d-flex" v-if="field.label === 'ETCセットアップﾟ証明番号'" style="font-size: 11px;">{{ field.label }}</span>
										<span v-else style="font-size: 12px;">{{ field.label }}</span>

										<b-button class="th-delete-btn" v-if="field.is_deletable" @click="handleDeleteField(fieldIndex)">
											<i class="fas fa-times" />
										</b-button>
									</div>

									<div :class="[field.is_display ? 'lower-active' : 'lower-inactive', 'd-flex justify-content-center align-items-center']">
										<span v-if="field.position !== (fieldIndex + 1)" class="position-badge original-position">{{ field.position }}</span>
										<span v-if="field.position !== (fieldIndex + 1)" class="arrow-separator"> → </span>
										<span v-if="field.position !== (fieldIndex + 1)" class="position-badge current-position-below">{{ fieldIndex + 1 }}</span>
										<span v-else class="position-badge unchanged-position">{{ field.position }}</span>

										<b-button class="th-lock-btn" @click="handleLockField(fieldIndex)">
											<i v-if="field.is_locked" class="fas fa-lock-alt" />
											<i v-else class="fas fa-lock-open-alt" />
										</b-button>
									</div>
								</template>
							</div>
						</div>
					</div>
				</div>
			</template>

			<template #modal-footer>
				<b-button class="modal-btn btn-cancel" :disabled="overlay.show" @click="showModalSettingTable = false">
					<span>キャンセル</span>
				</b-button>

				<b-button class="modal-btn btn-preview" :disabled="overlay.show" @click="handleTempSaveForPreview()">
					<span>変更をプレビューする</span>
				</b-button>

				<b-button class="modal-btn btn-apply" :disabled="overlay.show" @click="handleSaveSettingTable()">
					<span>変更を保存する</span>
				</b-button>
			</template>
		</b-modal>

		<!-- Column Selection Modal -->
		<b-modal v-model="showColumnModal" id="modal-column-selection" size="xl" centered scrollable>
			<template #modal-title>
				<div class="modal-header-content">
					<span class="modal-title">列を追加</span>
				</div>
			</template>

			<template #default>
				<div class="search-field">
					<b-form-input v-model="search_query" placeholder="検索" @input="handleSearchColumn" />
				</div>

				<div v-if="userColumnSetting.length > 0" class="column-list">
					<div v-for="column in userColumnSetting" :key="column.key" class="column-item">
						<b-form-checkbox v-model="column.is_selected" size="lg" class="column-checkbox">
							<span class="column-checkbox-label">{{ column.label }}</span>
							<span v-if="!column.is_display" style="color: #ff0000;">(非表示)</span>
							<span v-if="column.is_locked" style="color: #0000ff;">(固定)</span>
						</b-form-checkbox>
					</div>
				</div>

				<div v-else class="no-result">
					<span>検索結果が見つかりませんでした。</span>
				</div>
			</template>

			<template #modal-footer>
				<b-button style="min-width: 120px;" variant="secondary" @click="showColumnModal = false">
					<span>キャンセル</span>
				</b-button>

				<b-button style="min-width: 120px;" class="btn-unselect-all" @click="handleUnselectAllColumn()">
					<span>選択解除</span>
				</b-button>

				<b-button style="min-width: 120px;" class="btn-select-all" @click="handleSelectAllColumn()">
					<span>すべて選択</span>
				</b-button>

				<b-button style="min-width: 120px;" variant="primary" @click="applySelectedColumns()">
					<span>適用</span>
				</b-button>
			</template>
		</b-modal>

		<!-- Notification Recipients Setting Modal -->
		<b-modal
			v-model="showNotificationRecipientsModal"
			id="modal-notification-recipients"
			centered
			scrollable
			size="lg"
			:static="true"
			:busy="notificationRecipientsLoading"
			@show="loadNotificationRecipientsData"
			@hidden="closeNotificationRecipientsModal"
		>
			<template #modal-title>
				<span>{{ $t('NOTIFICATION_RECIPIENTS_MODAL.TITLE') }}</span>
			</template>

			<template #default>
				<p class="text-muted small mb-3">{{ $t('NOTIFICATION_RECIPIENTS_MODAL.DESCRIPTION') }}</p>
				<div class="notification-recipients-table-wrap">
					<div class="notification-recipients-table-inner">
						<b-table-simple class="notification-recipients-table">
							<b-thead head-variant="secondary">
								<b-tr>
									<b-th class="notification-recipients-dept-th">{{ $t('NOTIFICATION_RECIPIENTS_MODAL.COLUMN_DEPARTMENT') }}</b-th>
									<b-th class="notification-recipients-th">{{ $t('NOTIFICATION_RECIPIENTS_MODAL.COLUMN_RECIPIENTS') }}</b-th>
								</b-tr>
							</b-thead>
							<b-tbody>
								<b-tr v-for="dept in listDepartment" :key="dept.id">
									<b-td class="notification-recipients-dept-td font-weight-bold align-top pt-3">{{ dept.department_name }}</b-td>
									<b-td class="notification-recipients-td">
										<div class="notification-recipients-select-wrap">
											<v-multiselect
												:value="notificationRecipientsSelectedByDept[dept.id] || []"
												:options="notificationRecipientsCandidates"
												:multiple="true"
												:close-on-select="false"
												:clear-on-select="false"
												:preserve-search="true"
												:show-labels="false"
												placeholder="選択してください"
												label="name"
												track-by="id"
												@input="onNotificationRecipientsChange(dept.id, $event)"
											>
												<template #option="{ option }">
													<b-form-checkbox
														:checked="(notificationRecipientsSelectedByDept[dept.id] || []).some(u => u.id === option.id)"
														disabled
														class="mb-0"
													>
														{{ option.name }}
													</b-form-checkbox>
												</template>
												<template #selection="{ values }">
													<span v-if="values && values.length > 0" class="d-inline-flex flex-wrap gap-1">
														<b-badge v-for="u in values" :key="u.id" variant="secondary" class="mr-1 mb-1">
															{{ u.name }}
															<i class="fas fa-times ml-1 cursor-pointer" @click.stop="removeNotificationRecipient(dept.id, u.id)" />
														</b-badge>
													</span>
												</template>
											</v-multiselect>
										</div>
									</b-td>
								</b-tr>
							</b-tbody>
						</b-table-simple>
					</div>
				</div>
			</template>

			<template #modal-footer>
				<b-button variant="secondary" :disabled="notificationRecipientsLoading" @click="closeNotificationRecipientsModal">
					{{ $t('NOTIFICATION_RECIPIENTS_MODAL.CANCEL') }}
				</b-button>
				<b-button variant="primary" :disabled="notificationRecipientsLoading" @click="saveNotificationRecipients">
					{{ $t('NOTIFICATION_RECIPIENTS_MODAL.SAVE') }}
				</b-button>
			</template>
		</b-modal>
	</b-overlay>
</template>

<script>
import DatePicker from 'vue2-datepicker';
import vMultiselect from 'vue-multiselect';
import vButton from '@/components/atoms/vButton';
import vPagination from '@/components/atoms/vPagination';
import vHeaderPage from '@/components/atoms/vHeaderPage.vue';
import vHeaderFilter from '@/components/atoms/vHeaderFilter';

import CONST_ROLE from '@/const/role';

import { obj2Path } from '@/utils/obj2Path';
import { cleanObj } from '@/utils/handleObj';
import { MakeToast } from '@/utils/MakeToast';
import { handleGetCertificateByVehicleTotalWeight, formatDateDisplay } from './helper/helper';
import { getListVehicle, getListDepartment, deleteVehicle, getUserColumnSetting, postUserColumnSetting, getVehicleDashboard, getDepartmentByDivision } from '@/api/modules/vehicleMaster';
import { getCandidates as getNotificationCandidates, getList as getNotificationRecipientsList, save as saveNotificationRecipients } from '@/api/modules/inspectionNotificationRecipients';

import 'vue2-datepicker/index.css';

const urlAPI = {
    apiGetListVehicle: '/vehicle',
    apiDeleteVehicle: '/vehicle',
    apiGetListDepartment: '/department/list-all',
    apiGetListColumns: '/vehicle/list-columns',
    apiGetUserColumnSetting: '/vehicle/vehicle-style-show',
    apiPostUserColumnSetting: '/vehicle/add-vehicle-style-show',
    apiExportCSV: 'vehicle/download',
    apiGetVehicleDashboard: '/vehicle/dashboard',
    apiGetDepartmentByDivision: '/vehicle/division',
};

export default {
    name: 'VehicleMasterList',
    components: {
        vButton,
        vMultiselect,
        DatePicker,
        vHeaderPage,
        vPagination,
        vHeaderFilter,
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

            activeDepartmentQueryType: null,

            japaneseLang: {
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

            pagination: {
                current_page: 1,
                per_page: 50,
                total_rows: 0,
            },

            filter: this.$store.getters.filterVehicleMaster || {
                department_id: { status: false, value: [] },
                vehicle_no: { status: false, value: '' },
                number_plate: { status: false, value: '' },
                scrap_date: { status: false, value: '' },
                vehicle_inspection_expiry_date: { status: false, value: '' },
                vehicle_scrapped: { status: 1 },
            },
            filterQuery: {
                sort_by: null,
                sort_type: null,
            },

            currentSort: {
                field: null,
                direction: 'asc',
            },

            items: [],

            listDepartment: [],
            departmentDivisions: {
                division_1: [],
                division_2: [],
            },

            hasAccessDetail: [
                CONST_ROLE.CLERKS,
                CONST_ROLE.TL,
                CONST_ROLE.DEPARTMENT_OFFICE_STAFF,
                CONST_ROLE.GENERAL_AFFAIRS,
                CONST_ROLE.ACCOUNTING,
                CONST_ROLE.AM_SM,
                CONST_ROLE.QUALITY_CONTROL,
                CONST_ROLE.SALES,
                CONST_ROLE.SITE_MANAGER,
                CONST_ROLE.HQ_MANAGER,
                CONST_ROLE.DEPARTMENT_MANAGER,
                CONST_ROLE.EXECUTIVE_OFFICER,
                CONST_ROLE.DIRECTOR,
                CONST_ROLE.DX_USER,
                CONST_ROLE.DX_MANAGER,
            ],
            hasAccessCreate: [
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
            hasAccessDelete: [
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

            fields: [],
            fieldsConfig: [],

            userColumnSetting: [],
            originalUserColumnSetting: [],

            search_query: '',
            eventEmitPickerYearMonth: 'VEHICLE_MASTER_PICKER_YEAR_MONTH_CHANGE',

            tempID: null,
            role: this.$store.getters.profile.roles[0] || null,
            originalDepartment: this.$store.getters.profile.department.id || null,

            drag: false,

            showModalSettingTable: false,
            showColumnModal: false,
            showModalConfirmDeletion: false,
            showNotificationRecipientsModal: false,
            notificationRecipientsLoading: false,
            notificationRecipientsCandidates: [],
            notificationRecipientsSelectedByDept: {},

            is_select_all_column: false,
            isLeftShift: false,
            file: '',

            dashboard: {
                totalFilter: 0,
                totalNow: 0,
                totalNext: 0,
            },

            activeDashboardCard: null,
        };
    },
    computed: {
        optionsPerPage() {
            return [
                { value: 20, text: '20' },
                { value: 50, text: '50' },
                { value: 100, text: '100' },
                { value: 250, text: '250' },
                { value: 500, text: '500' },
            ];
        },
        currentPageChange() {
            return this.pagination.current_page;
        },
        lang() {
            return this.$store.getters.language;
        },
        totalFields() {
            return this.fields.length;
        },
        justifyContentClass() {
            if (this.totalFields >= 8) {
                return 'justify-content-space-between';
            } else if (this.totalFields > 0 && this.totalFields < 8) {
                return 'justify-content-start';
            }
            return 'justify-content-start';
        },
        displayedFields() {
            return this.fields.filter(field => field.is_display);
        },
        filteredDepartmentOptions() {
            if (this.role === 'tl' && this.originalDepartment) {
                return this.listDepartment.filter(dept => dept.id === this.originalDepartment);
            }

            return this.listDepartment;
        },
    },
    mounted() {
        window.addEventListener('keydown', this.onKeyDown);
        window.addEventListener('keyup', this.onKeyUp);

        this.$nextTick(() => {
            if (this.$refs.tableContainer) {
                this.$refs.tableContainer.addEventListener('wheel', this.onWheel, { passive: false });
                this.$refs.tableContainer.addEventListener('mousewheel', this.onWheel, { passive: false });
            } else {
                console.warn('Table container ref not found');
            }
        });
    },
    beforeDestroy() {
        window.removeEventListener('keydown', this.onKeyDown);
        window.removeEventListener('keyup', this.onKeyUp);

        if (this.$refs.tableContainer) {
            this.$refs.tableContainer.removeEventListener('wheel', this.onWheel);
            this.$refs.tableContainer.removeEventListener('mousewheel', this.onWheel);
        }
    },
    created() {
        this.handleInitData();
    },
    destroyed() {
        this.destroyedEventBus();
    },
    methods: {
        async handleInitData() {
            try {
                this.overlay.show = true;

                await this.handleGetListDepartment();
                await this.handleGetDepartmentByDivision();
                await this.handleGetUserColumnSetting();

                this.initializeDepartmentFilterForTL();

                await this.handleGetVehicleDashboard();

                await this.createdEventBus();

                this.overlay.show = false;
            } catch (error) {
                console.warn('[handleInitData] Error:', error);
            }
        },
        async handleChangePerPage() {
            await this.$store.dispatch('pagination/setVehicleMasterCP', 1);
            await this.$store.dispatch('pagination/setVehicleMasterPerPage', this.pagination.per_page);
            this.handleGetListVehicle(1);
        },
        async handleGetListVehicle(page, is_force_reset_current_page) {
            this.overlay.show = true;

            const VEHICLE_MASTER_PAGINATION = this.$store.getters.vehicleMasterCP;

            let currentPage = 1;

            if (is_force_reset_current_page) {
                currentPage = 1;
            } else {
                if (VEHICLE_MASTER_PAGINATION) {
                    currentPage = VEHICLE_MASTER_PAGINATION;
                } else {
                    currentPage = page;
                }
            }

            const VEHICLE_MASTER_PER_PAGE = this.$store.getters.vehicle_master_per_page;

            let per_page = 50;

            if (VEHICLE_MASTER_PER_PAGE) {
                per_page = VEHICLE_MASTER_PER_PAGE;
            } else {
                per_page = this.pagination.per_page;
            }

            try {
                let departmentParam = '';
                if (this.role === 'crew') {
                    departmentParam = this.originalDepartment;
                } else if (this.role === 'tl') {
                    departmentParam = this.originalDepartment;
                } else if (this.filter.department_id.value && this.filter.department_id.value.length > 0) {
                    departmentParam = this.filter.department_id.value.map(dept => dept.id).join(',');
                }

                let PARAMS = {
                    page: currentPage,
                    per_page: per_page,
                    number_plate: this.filter.number_plate.value,
                    vehicle_identification_number: this.filter.vehicle_no.value,
                    inspection_expiration_date: this.filter.vehicle_inspection_expiry_date.value,
                    department: departmentParam,
                    hide_scrap_date: this.filter.vehicle_scrapped.status,
                    sort_by: this.filterQuery.sort_by,
                    sort_type: this.filterQuery.sort_type,
                };
                PARAMS = cleanObj(PARAMS);

                const URL = `${urlAPI.apiGetListVehicle}?${obj2Path(PARAMS)}`;

                const response = await getListVehicle(URL, PARAMS);

                if (response) {
                    this.items = response.data.result;

                    for (let i = 0; i < this.items.length; i++) {
                        if (this.items[i].vehicle_department_history && this.items[i].vehicle_department_history.length > 0) {
                            this.items[i].department_names = this.items[i].vehicle_department_history.map(dept => this.getDepartmentName(dept.department_id)).join(', ');
                        } else {
                            this.items[i].department_names = '';
                        }
                        this.items[i].no_number_plate = this.items[i].plate_history[0] ? this.items[i].plate_history[0].no_number_plate : '';
                    }

                    this.pagination.total_rows = response.data.pagination.total_records;
                    this.pagination.current_page = response.data.pagination.current_page;
                    this.pagination.per_page = response.data.pagination.per_page;
                } else {
                    this.items = [];
                }
            } catch (err) {
                this.items = [];
                console.error('Error fetching vehicle list:', err);
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
                console.error('Error fetching department list:', err);
            }
        },
        async handleSaveSettingTable() {
            try {
                this.fields = [];

                await this.handleTempSaveForPreview();

                const result = [];

                const url = `${urlAPI.apiPostUserColumnSetting}`;

                if (this.fields.length) {
                    this.fields.forEach((column, columnIndex) => {
                        if (column.key !== 'action') {
                            result.push({
                                key: column.key,
                                label: column.label,
                                position: columnIndex + 1,
                                is_locked: column.is_locked,
                                is_display: column.is_display,
                                is_selected: column.is_selected,
                                is_deletable: column.is_deletable,
                            });
                        }
                    });
                }

                const response = await postUserColumnSetting(url, result);

                const { code } = response;

                if (code === 200) {
                    this.handleGetUserColumnSetting();
                } else {
                    MakeToast({
                        variant: 'warning',
                        title: this.$t('warning'),
                        content: 'Save Vehicle Style Show Failed',
                    });
                }
            } catch (error) {
                console.warn('[handleSaveSettingTable] Error:', error);
            }
        },
        async handleGetUserColumnSetting() {
            try {
                this.overlay.show = true;

                this.fields = [];
                this.fieldsConfig = [];
                this.userColumnSetting = [];
                this.originalUserColumnSetting = [];

                const url = `${urlAPI.apiGetUserColumnSetting}`;

                const response = await getUserColumnSetting(url);

                const { code, data } = response;

                const listDefaultColumnKey = [
                    'department_name',
                    'vehicle_identification_number',
                    'no_number_plate',
                    'scrap_date',
                ];

                if (this.hasAccessDetail.includes(this.role)) {
                    listDefaultColumnKey.push('detail');
                }
                if (this.hasAccessDelete.includes(this.role)) {
                    listDefaultColumnKey.push('delete');
                }

                if (code === 200) {
                    if (data && data.length) {
                        data.forEach(item => {
                            item.tdClass = this.handleRenderCellClass;
                            item.thClass = this.handleRenderTableThClass(item.label);
                            item.sortable = this.isFieldSortable(item.key);

                            if (item.is_selected) {
                                this.fields.push(item);
                                this.fieldsConfig.push(item);
                            }
                        });

                        const actionColumn = {
                            key: 'action',
                            label: 'action',
                            position: this.fieldsConfig.length + 1,
                            tdClass: this.handleRenderCellClass,
                            thClass: 'text-center table-customer-th',
                            is_deletable: false,
                            is_locked: false,
                            is_display: true,
                            is_selected: true,
                        };

                        this.fieldsConfig.push(actionColumn);
                    }

                    this.userColumnSetting = data;
                    this.originalUserColumnSetting = data;

                    this.setDefaultSorting();
                } else {
                    this.fields = [];
                    this.fieldsConfig = [];
                    this.userColumnSetting = [];
                    this.originalUserColumnSetting = [];
                }
            } catch (error) {
                console.warn('[handleGetUserColumnSetting] Error:', error);
            } finally {
                this.overlay.show = false;
            }
        },
        async handleExportCSV() {
            try {
                this.overlay.show = true;

                let params = {
                    sort_by: this.filterQuery.sort_by,
                    sort_type: this.filterQuery.sort_type,
                };

                if (this.role === 'crew' || this.role === 'tl') {
                    params.department = this.originalDepartment;
                } else if (this.filter.department_id.status && this.filter.department_id.value && this.filter.department_id.value.length > 0) {
                    params.department = this.filter.department_id.value.map(dept => dept.id).join(',');
                }

                if (this.filter.vehicle_no.status) {
                    params.vehicle_identification_number = this.filter.vehicle_no.value;
                }

                if (this.filter.number_plate.status) {
                    params.number_plate = this.filter.number_plate.value;
                }

                if (this.filter.vehicle_inspection_expiry_date.status) {
                    params.inspection_expiration_date = this.filter.vehicle_inspection_expiry_date.value;
                }

                const date = new Date();
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                const hourse = String(date.getHours()).padStart(2, '0');
                const minute = String(date.getMinutes()).padStart(2, '0');
                const second = String(date.getSeconds()).padStart(2, '0');
                const formatDate = `${year}${month}${day}_${hourse}${minute}${second}`;

                params = cleanObj(params);

                const url = `/api/${urlAPI.apiExportCSV}?${obj2Path(params)}`;

                await fetch(url, {
                    headers: {
                        'Accept-Language': this.$store.getters.language,
                        'Authorization': this.$store.getters.token,
                        'accept': 'application/json',
                    },
                }).then(async(res) => {
                    let filename = `車両マスタ_${formatDate}.csv`;
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
                    .catch((error) => {
                        console.log(error);

                        this.$toast.danger({
                            content: this.$t('TOAST_HAVE_ERROR'),
                        });
                    });
            } catch (error) {
                console.log('[handleExportCSV] Error:', error);
            } finally {
                this.overlay.show = false;
            }
        },

        async getCurrentPage(value) {
            if (value) {
                this.pagination.current_page = value;
                await this.$store.dispatch('pagination/setVehicleMasterCP', value);
                this.handleGetListVehicle(value);
            }
        },

        async onClickDelete() {
            this.overlay.show = true;

            try {
                const URL = `${urlAPI.apiDeleteVehicle}/${this.tempID}`;

                const response = await deleteVehicle(URL);

                if (response.code === 200) {
                    this.showModalConfirmDeletion = false;

                    MakeToast({
                        variant: 'success',
                        title: this.$t('SUCCESS'),
                        content: '削除しました',
                    });

                    this.handleGetListVehicle(1);
                } else {
                    MakeToast({
                        variant: 'warning',
                        title: this.$t('warning'),
                        content: 'Delete Vehicle Failed',
                    });
                }
            } catch (error) {
                console.error('Error deleting vehicle:', error);
            }

            this.overlay.show = false;
        },

        async handleGetDepartmentByDivision() {
            try {
                const url = `${urlAPI.apiGetDepartmentByDivision}`;

                const response = await getDepartmentByDivision(url);

                const { code, data } = response;

                if (code === 200) {
                    this.departmentDivisions = data;
                    console.log('Department divisions loaded:', data);
                }
            } catch (error) {
                console.error('Error loading department divisions:', error);
            }
        },
        async handleGetVehicleDashboard() {
            try {
                let departmentParam = '';
                if (this.role === 'crew') {
                    departmentParam = this.originalDepartment;
                } else if (this.role === 'tl') {
                    departmentParam = this.originalDepartment;
                } else if (this.filter.department_id.value && this.filter.department_id.value.length > 0) {
                    departmentParam = this.filter.department_id.value.map(dept => dept.id).join(',');
                }

                let params = {
                    number_plate: this.filter.number_plate.value,
                    vehicle_identification_number: this.filter.vehicle_no.value,
                    inspection_expiration_date: this.filter.vehicle_inspection_expiry_date.value,
                    department: departmentParam,
                    hide_scrap_date: this.filter.vehicle_scrapped.status,
                };
                params = cleanObj(params);

                const url = urlAPI.apiGetVehicleDashboard;
                const response = await getVehicleDashboard(url, params);

                if (response && response.code === 200) {
                    this.dashboard = {
                        totalFilter: response.data.totalFilter || 0,
                        totalNow: response.data.totalNow || 0,
                        totalNext: response.data.totalNext || 0,
                    };
                }
            } catch (err) {
                console.error('Error fetching vehicle dashboard:', err);
                this.dashboard = {
                    totalFilter: 0,
                    totalNow: 0,
                    totalNext: 0,
                };
            }
        },

        addColumn(column) {
            const isExist = this.fieldsConfig.some(field => field.key === column.key);
            if (isExist) {
                return;
            }

            const actionIndex = this.fieldsConfig.findIndex(field => field.key === 'action');
            const newColumn = {
                ...column,
                sortable: column.sortable !== undefined ? column.sortable : this.isFieldSortable(column.key),
            };

            if (actionIndex !== -1) {
                newColumn.position = actionIndex + 1;
                this.fieldsConfig.splice(actionIndex, 0, newColumn);

                for (let i = actionIndex + 1; i < this.fieldsConfig.length; i++) {
                    this.fieldsConfig[i].position = i + 1;
                }
            } else {
                newColumn.position = this.fieldsConfig.length + 1;
                this.fieldsConfig.push(newColumn);
            }

            if (column.key === 'first_registration') {
                this.setDefaultSorting();
            }
        },
        applySelectedColumns() {
            if (this.userColumnSetting.length) {
                const listExistColumn = this.fieldsConfig.map(field => field.key);

                this.userColumnSetting.forEach(column => {
                    if (column.is_selected) {
                        if (!listExistColumn.includes(column.key)) {
                            this.addColumn(column);
                        }
                    } else {
                        const index = this.fieldsConfig.findIndex(field => field.key === column.key);
                        if (index !== -1) {
                            this.fieldsConfig.splice(index, 1);
                        }
                    }
                });
            }

            this.showColumnModal = false;

            this.setDefaultSorting();

            this.scrollToBottom();
        },

        openNotificationRecipientsModal() {
            this.showNotificationRecipientsModal = true;
        },
        async loadNotificationRecipientsData() {
            this.notificationRecipientsLoading = true;
            try {
                const [candidatesRes, listRes] = await Promise.all([
                    getNotificationCandidates(),
                    getNotificationRecipientsList(),
                ]);
                const candidates = (candidatesRes && candidatesRes.data) ? candidatesRes.data.data || candidatesRes.data : [];
                const list = (listRes && listRes.data) ? listRes.data.data || listRes.data : [];
                this.notificationRecipientsCandidates = Array.isArray(candidates) ? candidates : [];
                const selectedByDept = {};
                this.listDepartment.forEach(dept => {
                    this.$set(selectedByDept, dept.id, []);
                });
                list.forEach(item => {
                    const deptId = item.department_id;
                    let user = null;
                    if (item.user && item.user.id) {
                        user = { id: item.user.id, name: item.user.name };
                    } else if (item.user_id && this.notificationRecipientsCandidates.length) {
                        const candidate = this.notificationRecipientsCandidates.find(c => c.id === item.user_id || c.id === Number(item.user_id));
                        if (candidate) {
                            user = { id: candidate.id, name: candidate.name };
                        }
                    }
                    if (!user && item.user_id && deptId && selectedByDept[deptId]) {
                        user = { id: item.user_id, name: `User #${item.user_id}` };
                    }
                    if (deptId && user && selectedByDept[deptId]) {
                        if (!selectedByDept[deptId].some(u => u.id === user.id)) {
                            selectedByDept[deptId].push(user);
                        }
                    }
                });
                this.notificationRecipientsSelectedByDept = selectedByDept;
            } catch (e) {
                MakeToast({ variant: 'danger', title: this.$t('TOAST_HAVE_ERROR'), content: (e && e.message) || 'Failed to load' });
            } finally {
                this.notificationRecipientsLoading = false;
            }
        },
        onNotificationRecipientsChange(deptId, val) {
            this.$set(this.notificationRecipientsSelectedByDept, deptId, Array.isArray(val) ? val : []);
        },
        removeNotificationRecipient(deptId, userId) {
            const arr = this.notificationRecipientsSelectedByDept[deptId];
            if (!Array.isArray(arr)) {
                return;
            }
            this.$set(
                this.notificationRecipientsSelectedByDept,
                deptId,
                arr.filter(u => u.id !== userId)
            );
        },
        async saveNotificationRecipients() {
            const recipients = [];
            Object.keys(this.notificationRecipientsSelectedByDept).forEach(deptId => {
                const users = this.notificationRecipientsSelectedByDept[deptId];
                if (Array.isArray(users)) {
                    users.forEach(u => {
                        if (u && u.id) {
                            recipients.push({ department_id: Number(deptId), user_id: u.id });
                        }
                    });
                }
            });
            this.notificationRecipientsLoading = true;
            try {
                await saveNotificationRecipients(recipients);
                MakeToast({ variant: 'success', title: this.$t('SUCCESS'), content: this.$t('TOAST_HAVE_SUCCESS') });
                this.showNotificationRecipientsModal = false;
            } catch (e) {
                MakeToast({ variant: 'danger', title: this.$t('TOAST_HAVE_ERROR'), content: (e && e.response && e.response.data && e.response.data.message) || e.message || 'Save failed' });
            } finally {
                this.notificationRecipientsLoading = false;
            }
        },
        closeNotificationRecipientsModal() {
            this.showNotificationRecipientsModal = false;
        },

        createdEventBus() {
            this.$bus.on('VEHICLE_MASTER_FILTER_DATA', filter => {
                this.filter = filter;
            });

            this.$bus.on('VEHICLE_MASTER_FILTER_APPLY', () => {
                this.handleGetListVehicle(1, true);
            });

            this.handleGetListVehicle(1);
        },

        destroyedEventBus() {
            this.$bus.off('VEHICLE_MASTER_FILTER_DATA');
            this.$bus.off('VEHICLE_MASTER_FILTER_APPLY');
            this.$bus.off(this.eventEmitPickerYearMonth);
        },

        onClickRegister() {
            this.$router.push({ name: 'VehicleMasterCreate' });
        },
        onClickDetail(id) {
            this.$router.push({ name: 'VehicleMasterDetail', params: { id }});
        },
        async onClickClearAll() {
            const FILER = {
                department_id: this.role === 'tl' ? this.filter.department_id : { status: false, value: [] },
                vehicle_no: { status: false, value: '' },
                number_plate: { status: false, value: '' },
                scrap_date: { status: false, value: '' },
                vehicle_inspection_expiry_date: { status: false, value: '' },
                vehicle_scrapped: { status: 1 },
            };

            this.filter = FILER;
            this.activeDepartmentQueryType = null;
            this.activeDashboardCard = null;

            await this.handleGetVehicleDashboard();
            this.handleGetListVehicle(1, true);
        },
        async onClickApply() {
            this.handleSaveFilter(this.filter);
            await this.handleGetVehicleDashboard();
            this.handleGetListVehicle(1, true);
        },
        onKeyDown(e) {
            if (e.code === 'ShiftLeft' || e.key === 'Shift' || e.keyCode === 16) {
                this.isLeftShift = true;
            }
        },
        onKeyUp(e) {
            if (e.code === 'ShiftLeft' || e.key === 'Shift' || e.keyCode === 16) {
                this.isLeftShift = false;
            }
        },
        onWheel(e) {
            if (this.isLeftShift) {
                e.preventDefault();
                e.stopPropagation();

                const tableScroll = document.getElementsByClassName('table-scroll')[0];
                const tableFixed = document.getElementsByClassName('table-fixed')[0];

                if (tableScroll && tableFixed) {
                    let scrollAmount = 0;

                    if (e.deltaY !== 0) {
                        scrollAmount = e.deltaY > 0 ? 300 : -300;
                    } else if (e.deltaX !== 0) {
                        scrollAmount = e.deltaX > 0 ? 300 : -300;
                    } else {
                        const wheelDelta = e.wheelDelta || -e.deltaY;
                        scrollAmount = wheelDelta > 0 ? -300 : 300;
                    }

                    const currentScrollLeft = tableScroll.scrollLeft;
                    const newScrollLeft = currentScrollLeft + scrollAmount;

                    tableScroll.scrollLeft = newScrollLeft;
                    tableFixed.scrollLeft = newScrollLeft;
                }
            }
        },

        updateVehicleScrappedStatus() {
            this.$store.dispatch('filter/setFilterVehicleMaster', this.filter);
        },

        getDepartmentName(id) {
            const DEPATMENT = this.listDepartment.find(item => item.id === id);
            return DEPATMENT ? DEPATMENT.department_name : id;
        },
        getColumnWidth(field) {
            if (field.thClass.includes('table-customer-th-large')) {
                return 240;
            }

            return 150;
        },

        handleRenderCellClass(value, key, item) {
            if (item['scrap_date_custom'] !== null) {
                const scrapDate = new Date(item['scrap_date_custom']);
                const today = new Date();

                scrapDate.setHours(0, 0, 0, 0);
                today.setHours(0, 0, 0, 0);

                if (scrapDate < today) {
                    return 'text-center darker-bg-td';
                }
            }
            return 'text-center';
        },
        handleChangeNumberPlate() {
            if (!this.filter.number_plate.status) {
                this.filter.number_plate.value = '';
            }
        },
        handleChangeVehicleNo() {
            if (!this.filter.vehicle_no.status) {
                this.filter.vehicle_no.value = '';
            }
        },
        handleChangeDepartmentID() {
            if (this.role === 'tl') {
                return;
            }

            if (!this.filter.department_id.status) {
                this.filter.department_id.value = [];
            }

            this.activeDepartmentQueryType = null;
        },
        handleChangeVehicleInspectionExpiryDate() {
            if (!this.filter.vehicle_inspection_expiry_date.status) {
                this.filter.vehicle_inspection_expiry_date.value = '';
            }
        },
        handleSaveFilter(filter) {
            this.$store.dispatch('filter/setFilterVehicleMaster', filter);
        },
        handleSort(ctx) {
            if (this.currentSort.field === ctx.sortBy) {
                this.currentSort.direction = this.currentSort.direction === 'asc' ? 'desc' : 'asc';
            } else {
                this.currentSort.field = ctx.sortBy;
                this.currentSort.direction = 'asc';
            }

            this.filterQuery.sort_by = this.currentSort.field;
            this.filterQuery.sort_type = this.currentSort.direction;

            this.handleGetListVehicle(1);
        },
        handleFieldDisplayChange(field) {
            this.fieldsConfig.forEach((config) => {
                if (config.key === field.key) {
                    config.is_display = field.is_display;
                }
            });
        },
        handleDeleteField(index) {
            this.fieldsConfig.splice(index, 1);

            this.fieldsConfig.forEach((field, i) => {
                field.position = i + 1;
            });
        },
        handleLockField(index) {
            this.fieldsConfig[index].is_locked = !this.fieldsConfig[index].is_locked;
        },
        handleSelectAllColumn() {
            this.userColumnSetting.forEach(column => {
                column.is_selected = true;
            });

            this.is_select_all_column = true;
        },
        handleUnselectAllColumn() {
            this.userColumnSetting.forEach(column => {
                column.is_selected = false;
            });

            this.is_select_all_column = false;
        },
        handleOpenAddColumnModal() {
            this.userColumnSetting = [...this.originalUserColumnSetting];

            this.userColumnSetting.forEach(column => {
                if (this.isColumnAlreadyAdded(column.key)) {
                    column.is_selected = true;
                } else {
                    column.is_selected = false;
                }
            });

            const isSelected = this.userColumnSetting.some(column => column.is_selected);

            if (isSelected) {
                this.is_select_all_column = true;
            }

            this.showColumnModal = true;
        },
        handleTempSaveForPreview() {
            if (this.fieldsConfig.length) {
                this.fields = [];

                const listExistColumn = this.fields.map(field => field.key);

                this.fieldsConfig.forEach((column) => {
                    if (column.key === 'action') {
                        return;
                    }

                    if (column.is_selected) {
                        if (!listExistColumn.includes(column.key)) {
                            this.fields.push({
                                sortable: column.sortable !== undefined ? column.sortable : this.isFieldSortable(column.key),
                                key: column.key,
                                label: column.label,
                                position: column.position,
                                is_locked: column.is_locked,
                                is_display: column.is_display,
                                is_selected: column.is_selected,
                                is_deletable: column.is_deletable,
                                tdClass: this.handleRenderCellClass,
                                thClass: this.handleRenderTableThClass(column.label),
                            });
                        }
                    } else {
                        const index = this.fields.indexOf(column);
                        if (index !== -1) {
                            this.fields.splice(index, 1);
                        }
                    }
                });
            }

            this.setDefaultSorting();

            this.showModalSettingTable = false;
        },
        handleRenderTableThClass(column_label) {
            let result = 'text-center table-customer-th';

            const listLongTh = [
                '車体番号',
                '車両No',
                'ETCセットアップﾟ証明番号',
                'ETC番号',
                '燃料カード番号➀',
                '燃料カード番号➁',
                '型式',
                'バッテリーサイズ',
                'メンテナンスリース会社',
                'メンテナンスリース料',
                '備考',
                '所有者',
                '自賠会社',
            ];

            const listExtraLongTh = [
                '備考',
            ];

            if (column_label && listLongTh.includes(column_label)) {
                result = 'text-center table-customer-th-large';
            }

            if (column_label && listExtraLongTh.includes(column_label)) {
                result = 'text-center table-customer-th-extra-long';
            }

            return result;
        },
        handleScrollFirstTable() {
            const target = document.getElementsByClassName('table-scroll')[0];
            const source = document.getElementsByClassName('table-fixed')[0];
            target.scrollLeft = source.scrollLeft;
        },
        handleScrollSecondTable() {
            const target = document.getElementsByClassName('table-fixed')[0];
            const source = document.getElementsByClassName('table-scroll')[0];
            target.scrollLeft = source.scrollLeft;
        },
        handleSearchColumn() {
            const searchQuery = this.search_query.toLowerCase();

            if (searchQuery) {
                this.userColumnSetting = this.originalUserColumnSetting.filter(column =>
                    column.label.toLowerCase().includes(searchQuery)
                );
            } else {
                this.userColumnSetting = [...this.originalUserColumnSetting];
            }
        },
        handleGetCertificateByVehicleTotalWeight,
        formatDateDisplay,

        isDateField(fieldKey) {
            const dateFields = [
                'first_registration',
                'inspection_expiration_date',
                'vehicle_delivery_date',
                'scrap_date',
                'start_of_leasing',
                'end_of_leasing',
            ];

            return dateFields.includes(fieldKey);
        },

        calculateLockedColumnStyle(field, index, table_type) {
            if (!field.is_locked) {
                return {};
            }

            let leftPosition = 0;
            let zIndex;

            if (table_type === 1) {
                zIndex = 200;
            } else {
                zIndex = 100;
            }

            for (let i = 0; i < index; i++) {
                const precedingField = this.displayedFields[i];
                if (precedingField && precedingField.is_locked) {
                    const columnWidth = this.getColumnWidth(precedingField);
                    leftPosition += columnWidth;
                    zIndex += 1;
                }
            }

            return {
                left: `${leftPosition}px`,
                zIndex: zIndex,
                position: 'sticky',
            };
        },
        calculateLockedColumnStyleTd(field, index, rowIndex, table_type) {
            if (!field.is_locked) {
                return {};
            }

            let leftPosition = 0;
            let zIndex;

            if (table_type === 1) {
                zIndex = 200;
            } else {
                zIndex = 100;
            }

            for (let i = 0; i < index; i++) {
                const precedingField = this.displayedFields[i];
                if (precedingField && precedingField.is_locked) {
                    const columnWidth = this.getColumnWidth(precedingField);
                    leftPosition += columnWidth;
                    zIndex += 1;
                }
            }

            const baseStyle = {
                left: `${leftPosition}px`,
                zIndex: zIndex,
                position: 'sticky',
                color: '#000000',
            };

            if (rowIndex === -1) {
                baseStyle.backgroundColor = '#0F0448';
                baseStyle.color = '#ffffff';
            } else {
                baseStyle.backgroundColor = rowIndex % 2 === 0 ? '#f2f2f2' : '#ffffff';
            }

            return baseStyle;
        },

        isColumnAlreadyAdded(columnKey) {
            return this.fields.some(field => field.key === columnKey);
        },
        isFieldSortable(fieldKey) {
            const sortableFields = [
                'department_name',
                'vehicle_identification_number',
                'scrap_date',
                'no_number_plate',
                'inspection_expiration_date',
                'first_registration',
                'inspection_expiration_date',
                'voluntary_premium',

                'gate',
                'humidifier',
                'displacement',
                'length',
                'width',
                'height',
                'maximum_loading_capacity',
                'vehicle_total_weight',
                'd1d_not_installed',
                'optional_detail',
                'monthly_mileage',
                'maintenance_lease_fee',
                'door_number',
            ];

            return sortableFields.includes(fieldKey);
        },
        isFirstRegistrationColumnPresent() {
            return this.fields.some(field => field.key === 'first_registration');
        },
        isNumericField(fieldKey) {
            const numericFields = [
                'gate',
                'humidifier',
                'displacement',
                'length',
                'width',
                'height',
                'maximum_loading_capacity',
                'vehicle_total_weight',
                'd1d_not_installed',
                'optional_detail',
                'monthly_mileage',
                'maintenance_lease_fee',
                'door_number',
            ];

            return numericFields.includes(fieldKey);
        },

        scrollToBottom() {
            this.$nextTick(() => {
                if (this.$refs.settingTableModal) {
                    const modalBody = this.$refs.settingTableModal.$el.querySelector('.modal-body');
                    if (modalBody) {
                        modalBody.scrollTop = modalBody.scrollHeight - modalBody.clientHeight;
                    }
                }
            });
        },
        setDefaultSorting() {
            const hadFirstRegistration = this.currentSort.field === 'first_registration';

            if (this.isFirstRegistrationColumnPresent()) {
                this.currentSort.field = 'first_registration';
                this.currentSort.direction = 'asc';
                this.filterQuery.sort_by = 'first_registration';
                this.filterQuery.sort_type = 'asc';
            } else {
                this.currentSort.field = null;
                this.currentSort.direction = 'asc';
                this.filterQuery.sort_by = null;
                this.filterQuery.sort_type = null;
            }

            if (hadFirstRegistration !== (this.currentSort.field === 'first_registration')) {
                this.handleGetListVehicle(1);
            }
        },

        formatNumberWithCommas(value) {
            if (value === null || value === undefined || value === '') {
                return value;
            }

            const num = parseFloat(value);
            if (isNaN(num)) {
                return value;
            }

            return num.toLocaleString();
        },

        calculateSum(columnKey) {
            if (!this.items || this.items.length === 0) {
                return 0;
            }

            return this.items.reduce((sum, item) => {
                const value = item[columnKey];
                if (value === null || value === undefined || value === '') {
                    return sum;
                }
                const num = parseFloat(value);
                return isNaN(num) ? sum : sum + num;
            }, 0);
        },

        handleChangeDepartmentQueryType(type) {
            if (this.role === 'tl') {
                return;
            }

            this.filter.department_id.value = [];
            this.filter.department_id.status = true;
            this.activeDepartmentQueryType = type;

            if (type === 1) {
                this.filter.department_id.value = this.listDepartment;
            } else if (type === 2) {
                if (this.departmentDivisions.division_1 && this.departmentDivisions.division_1.length > 0) {
                    this.filter.department_id.value = this.listDepartment.filter(dept =>
                        this.departmentDivisions.division_1.some(divDept => divDept.id === dept.id)
                    );
                }
            } else if (type === 3) {
                if (this.departmentDivisions.division_2 && this.departmentDivisions.division_2.length > 0) {
                    this.filter.department_id.value = this.listDepartment.filter(dept =>
                        this.departmentDivisions.division_2.some(divDept => divDept.id === dept.id)
                    );
                }
            }
        },

        getSelectedDepartmentCount() {
            return this.filter.department_id.value ? this.filter.department_id.value.length : 0;
        },

        getDivisionInfo(type) {
            if (type === 2 && this.departmentDivisions.division_1) {
                return `(${this.departmentDivisions.division_1.length} 拠点)`;
            } else if (type === 3 && this.departmentDivisions.division_2) {
                return `(${this.departmentDivisions.division_2.length} 拠点)`;
            }
            return '';
        },

        initializeDepartmentFilterForTL() {
            if (this.role === 'tl') {
                const tlDepartment = this.listDepartment.find(dept => dept.id === this.originalDepartment);
                if (tlDepartment) {
                    this.filter.department_id.value = [tlDepartment];
                    this.filter.department_id.status = true;
                }
            }
        },

        handleClickCurrentMonthCard() {
            if (this.activeDashboardCard === 'current') {
                this.filter.vehicle_inspection_expiry_date.value = '';
                this.filter.vehicle_inspection_expiry_date.status = false;
                this.activeDashboardCard = null;
            } else {
                const currentMonth = new Date().toISOString().slice(0, 7);
                this.filter.vehicle_inspection_expiry_date.value = currentMonth;
                this.filter.vehicle_inspection_expiry_date.status = true;
                this.activeDashboardCard = 'current';
            }
            this.onClickApply();
        },

        handleClickNextMonthCard() {
            if (this.activeDashboardCard === 'next') {
                this.filter.vehicle_inspection_expiry_date.value = '';
                this.filter.vehicle_inspection_expiry_date.status = false;
                this.activeDashboardCard = null;
            } else {
                const nextMonth = new Date();
                nextMonth.setMonth(nextMonth.getMonth() + 1);
                const nextMonthString = nextMonth.toISOString().slice(0, 7);
                this.filter.vehicle_inspection_expiry_date.value = nextMonthString;
                this.filter.vehicle_inspection_expiry_date.status = true;
                this.activeDashboardCard = 'next';
            }
            this.onClickApply();
        },

        getInspectionExpirationDateClass(flag) {
            switch (flag) {
            case 1:
                return 'yellow-border';
            case 2:
                return 'blue-border';
            default:
                return '';
            }
        },
    },
};
</script>

<style src="vue-multiselect/dist/vue-multiselect.min.css" />

<style lang="scss" scoped>
@import "@/scss/variables";

::v-deep .mx-datepicker {
    width: calc(100% - 190px) !important;
}

::v-deep .mx-input {
    width: 100% !important;
    height: 38px !important;
    border-top-left-radius: 0px !important;
    border-bottom-left-radius: 0px !important;
}

::v-deep .darker-bg-td {
    color: #FFFFFF !important;
    background-color: #000000 !important;
}

::-webkit-scrollbar {
    width: 10px;
    height: 10px;
}

::-webkit-scrollbar-thumb {
    border-radius: 45px;
}

.fas:hover {
    cursor: pointer;
}

::v-deep .prepend-filter {
    min-width: 150px !important;
}

::v-deep .prepend-filter-text {
    min-width: 125px !important;
}

.text-overlay {
    margin-top: 10px;
}

::v-deep .table-customer-th {
    text-align: center !important;
    background-color: $tolopea !important;
    color: $white !important;
    width: 150px !important;
    min-width: 150px !important;
}

::v-deep .table-customer-th-large {
    text-align: center !important;
    background-color: $tolopea !important;
    color: $white !important;
    width: 240px !important;
    min-width: 240px !important;
}

::v-deep .table-customer-th-extra-long {
    text-align: center !important;
    background-color: $tolopea !important;
    color: $white !important;
    width: 340px !important;
    min-width: 340px !important;
}

::v-deep .sum-th {
    background-color: #0F0448 !important;
}

::v-deep .b-table-sticky-header {
    overflow-x: hidden;
    overflow-y: scroll !important;
    max-height: 650px !important;
}

.vehicle-master {

    &__header,
    &__handle,
    &__table,
    &__filter,
    &__pagination {
        margin-bottom: 20px;
    }

  &__select-year-month {
    margin-bottom: 10px;
  }

  &__dashboard-actions {
    margin-bottom: 20px;
    padding: 20px;
    background-color: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e9ecef;
  }

  &__actions {
    flex-shrink: 0;
  }

    &__table {
        height: 650px;
        margin-top: 30px;

        ::v-deep table {
            width: 100%;
            border-spacing: 0;
            overflow-x: scroll;
            overflow-y: scroll;
            border-collapse: separate;

            thead {
                th {
                    top: 0;
                    color: $white;
                    width: 150px;
                    min-width: 150px;
                    text-align: center;
                    background-color: $tolopea;
                    position: sticky !important;
                }

                th.th-edit,
                th.th-delete {
                    width: 100px;
                }
            }

            tbody {
                tr {
                    &:hover {
                        background-color: $west-side;

                        td {
                            color: $white;
                        }
                    }
                }

                td.td-edit,
                td.td-delete {
                    width: 100px;

                    i {
                        cursor: pointer;
                    }
                }
            }
        }
    }

    &__filter {
        span.text-clear-all {
            cursor: pointer;
            font-weight: 500;
            margin-bottom: 20px;
            border-top: 1px solid $black;
            border-bottom: 1px solid $black;
        }
    }

    .filter-item {
        margin-bottom: 10px;
    }

    .reset-padding-b-col {
        padding-left: 0;
    }

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

::v-deep .multiselect {
  min-height: 38px;
  border: 1px solid #ced4da;
  border-radius: 0.375rem;
  background-color: #fff;

  &.multiselect--disabled {
    background-color: #e9ecef;
    opacity: 0.65;
  }
}

::v-deep .multiselect__tags {
  min-height: 36px;
  padding: 8px 40px 0 8px;
  border: none;
  background: transparent;
}

::v-deep .multiselect__placeholder {
  color: #6c757d;
  margin-bottom: 8px;
  padding-top: 0;
}

::v-deep .multiselect__input {
  border: none;
  background: transparent;
  padding: 0;
  margin: 0;
  height: auto;
}

::v-deep .multiselect__select {
  position: absolute;
  right: 0;
  top: 0;
  width: 40px;
  height: 36px;
  padding: 8px 12px;
  margin: 0;
  text-align: center;
  border-left: 1px solid #ced4da;
  background: #fff;
  cursor: pointer;

  &::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 0;
    height: 0;
    border-left: 5px solid transparent;
    border-right: 5px solid transparent;
    border-top: 5px solid #6c757d;
  }
}

::v-deep .multiselect__content-wrapper {
  border: 1px solid #ced4da;
  border-top: none;
  border-radius: 0 0 0.375rem 0.375rem;
  background: #fff;
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

::v-deep .multiselect__option {
  padding: 8px 12px;
  min-height: 36px;
  line-height: 20px;
  border-bottom: 1px solid #f8f9fa;

  &:hover {
    background: #f8f9fa;
  }

  &.multiselect__option--highlight {
    background: #007bff;
    color: #fff;
  }
}

::v-deep .multiselect {
  min-height: 38px;
  border: 1px solid #ced4da;
  border-radius: 0.375rem;
  background-color: #fff;

  &.multiselect--disabled {
    background-color: #e9ecef;
    opacity: 0.65;
  }
}

::v-deep .multiselect__tags {
  min-height: 36px;
  padding: 8px 40px 0 8px;
  border: none;
  background: transparent;
}

::v-deep .multiselect__placeholder {
  color: #6c757d;
  margin-bottom: 8px;
  padding-top: 0;
}

::v-deep .multiselect__input {
  border: none;
  background: transparent;
  padding: 0;
  margin: 0;
  height: auto;
}

::v-deep .multiselect__select {
  position: absolute;
  right: 0;
  top: 0;
  width: 40px;
  height: 36px;
  padding: 8px 12px;
  margin: 0;
  text-align: center;
  border-left: 1px solid #ced4da;
  background: #fff;
  cursor: pointer;

  &::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 0;
    height: 0;
    border-left: 5px solid transparent;
    border-right: 5px solid transparent;
    border-top: 5px solid #6c757d;
  }
}

::v-deep .multiselect__content-wrapper {
  border: 1px solid #ced4da;
  border-top: none;
  border-radius: 0 0 0.375rem 0.375rem;
  background: #fff;
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

::v-deep .multiselect__option {
  padding: 8px 12px;
  min-height: 36px;
  line-height: 20px;
  border-bottom: 1px solid #f8f9fa;

  &:hover {
    background: #f8f9fa;
  }

  &.multiselect__option--highlight {
    background: #007bff;
    color: #fff;
  }
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

.btn-setting-table {
    height: 36px;
    padding: 0px 20px;
    background-color: $west-side;
    color: $white;

    &:hover {
        opacity: 0.8;
    }

    &:focus {
        background-color: $west-side;
        color: $white;
    }
}

.btn-registration {
    height: 36px;
    padding: 0px 20px;
    background-color: $west-side;
    color: $white;

    &:hover {
        opacity: 0.8;
    }

    &:focus {
        background-color: $west-side;
        color: $white;
    }
}

@media (min-width: 1200px) {
    ::v-deep .modal-xl {
        max-width: 80%;
    }
}

.draggable-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 10px;
    width: 100%;
    justify-items: start;
    justify-content: space-between;
}

.table-fixed {
    top: -10px;
    left: 0px;
    position: absolute;
    z-index: 1005 !important;
    overflow-x: scroll !important;
    overflow-y: scroll !important;
}

.overflow-y-scroll {
    overflow-y: scroll !important;
}

.table-scroll {
    height: 650px;
    overflow-x: hidden;
}

.locked-th {
  z-index: 100;
  position: sticky !important;

    &:hover {
        color: #ffffff;
        background-color: #0F0448;
    }
}

.locked-td {
  z-index: 100;
  position: sticky !important;

    &:hover {
        color: #ffffff !important;
        background-color: #FF8A1F !important;
    }
}

button {
    &:hover {
        opacity: 0.6 !important;
    }
}

.red-border {
  border-color: red !important;
  color: red !important;
  font-weight: bold;
}

.yellow-border {
  border-color: #ffc107 !important;
  color: #856404 !important;
  font-weight: bold;
  background-color: #fff3cd !important;
}

.blue-border {
  border-color: #17a2b8 !important;
  color: #0c5460 !important;
  font-weight: bold;
  background-color: #d1ecf1 !important;
}
</style>

<style lang="scss">
@import "@/scss/variables";

#modal-setting-table___BV_modal_outer_ {
    z-index: 1007 !important;
}

#modal-column-selection___BV_modal_outer_ {
    z-index: 1008 !important;
}

#modal-cf___BV_modal_outer_ {
    z-index: 1006 !important;
}

#modal-notification-recipients .notification-recipients-table-wrap {
    max-height: 420px;
    overflow-y: scroll;
    overflow-x: hidden;
    border: 1px solid #dee2e6;
    border-radius: 0.25rem;
}

#modal-notification-recipients .notification-recipients-table-inner {
    padding-right: 17px;
    box-sizing: border-box;
}

#modal-notification-recipients .notification-recipients-table {
    width: 100%;
    table-layout: fixed;
}

#modal-notification-recipients .notification-recipients-dept-th,
#modal-notification-recipients .notification-recipients-dept-td {
    width: 90px;
    min-width: 90px;
    max-width: 90px;
    box-sizing: border-box;
    padding-right: 8px;
    vertical-align: top;
}

#modal-notification-recipients .notification-recipients-dept-td {
    padding-top: 0.75rem;
}

#modal-notification-recipients .notification-recipients-th,
#modal-notification-recipients .notification-recipients-td {
    width: auto;
    box-sizing: border-box;
    padding-left: 4px;
}

#modal-notification-recipients .notification-recipients-td {
    padding-right: 0;
}

#modal-notification-recipients .notification-recipients-select-wrap {
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
}

#modal-notification-recipients .notification-recipients-select-wrap .multiselect {
    width: 100% !important;
    max-width: 100%;
}

#modal-notification-recipients .notification-recipients-select-wrap .multiselect--active .multiselect__input {
    max-width: 100%;
}

#modal-notification-recipients .notification-recipients-select-wrap .multiselect__option {
    color: #212529;
}

#modal-notification-recipients .notification-recipients-select-wrap .multiselect__option .custom-control-label,
#modal-notification-recipients .notification-recipients-select-wrap .multiselect__option .form-check-label {
    color: #212529;
}

#modal-notification-recipients .notification-recipients-select-wrap .multiselect__option span {
    color: #212529;
}

#modal-notification-recipients .notification-recipients-select-wrap .multiselect__option.multiselect__option--highlight,
#modal-notification-recipients .notification-recipients-select-wrap .multiselect__option.multiselect__option--highlight .custom-control-label,
#modal-notification-recipients .notification-recipients-select-wrap .multiselect__option.multiselect__option--highlight .form-check-label,
#modal-notification-recipients .notification-recipients-select-wrap .multiselect__option.multiselect__option--highlight span {
    color: #fff;
}

#modal-setting-table {
    .modal-body {
        height: 650px;
        overflow-x: auto;
        overflow-y: visible;
    }

    .modal-content {
        overflow: visible;
    }

    .scrollable-wrapper {
        max-width: 100%;
    }

    .draggable-container {
        max-width: 100%;
        padding: 10px 0;
        display: flex;
        flex-wrap: nowrap;
    }

    .drag-th {
        position: relative;
        min-width: 150px;
        display: flex;
        color: $white;
        flex-direction: column;
        border: 1px solid $white;
        margin: 5px;
        width: 100%;

        &.no-drag {
            &:hover {
                cursor: default !important;
            }
        }

        .upper-active {
            height: 34px;
            display: flex;
            padding: 5px;
            align-items: center;
            flex-direction: row;
            justify-content: center;
            background-color: $tolopea;
        }

        .lower-active {
            height: 45px;
            border: 1px solid #DDDDDD;
        }

        .upper-inactive {
            height: 34px;
            display: flex;
            padding: 5px;
            align-items: center;
            flex-direction: row;
            justify-content: center;
            background-color: #656b7f;
        }

        .lower-inactive {
            height: 45px;
            border: 1px solid #656b7f;
        }

        .th-checkbox {
            position: absolute;
            left: 10px;
        }

        .th-plus-btn {
            background-color: transparent;
            border: none;
            padding: 0;

            &:hover {
                cursor: pointer;
            }

            i {
                font-size: 22px;
            }

            .btn-secondary {
                background-color: transparent !important;

                &:hover {
                    opacity: .6;
                    cursor: pointer;
                }
            }
        }

        .th-delete-btn {
            background-color: transparent;
            position: absolute;
            right: 0px;

            &:hover {
                opacity: .6;
                cursor: pointer;
            }

            i {
                color: #FFFFFF;
            }
        }

        .th-lock-btn {
            background-color: transparent;
            position: absolute;
            right: 0px;

            &:hover {
                opacity: .6;
                cursor: pointer;
            }

            i {
                color: #212121;
            }
        }

        .position-badge {
            padding: 2px 7px;
            border-radius: 45px;
            font-size: 12px;
            color: white;
        }

        .current-position {
            background-color: white;
            color: black;
        }

        .current-position-below {
            color: white;
            background-color: #89AC46;
            box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;
        }

        .original-position {
            background-color: #C83F12;
            box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;
        }

        .unchanged-position {
            color: black;
            font-weight: 600;
            background-color: #EEEEEE;
            box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;
        }

        .arrow-separator {
            color: black;
        }

        .action-text {
            color: black;
            font-size: 12px;
        }
    }

    .modal-btn {
        color: $white;
        min-width: 120px;
    }

    .btn-cancel {
        background-color: #656b7f;
        color: $white;

        &:hover {
            opacity: 0.6;
        }
    }

    .btn-apply {
        background-color: $west-side;
        color: $white;

        &:hover {
            opacity: 0.6;
        }
    }

    .btn-reset {
        background-color: #8A0000;
        color: $white;

        &:hover {
            opacity: 0.6;
        }
    }

    .btn-preview {
        background-color: #471396;
        color: $white;

        &:hover {
            opacity: 0.6;
        }
    }

    .div-pointer {
        cursor: pointer;

        &:hover {
            opacity: 0.6;
        }
    }

    .summary-row {
        background-color: #f8f9fa;
        border-top: 2px solid #dee2e6;

        td {
            font-weight: bold;
            color: #495057;
        }
    }
}

#modal-column-selection {
    .modal-title {
        font-size: 20px;
        color: #333333;
        font-weight: 500;
    }

    .modal-header {
        padding: 15px 20px;
    }

    .modal-header-content {
        display: flex;
        flex-direction: column;
        width: 100%;
    }

    .btn-select-all {
        background-color: #28a745;
        color: $white;
        width: 120px;
    }

    .btn-unselect-all {
        background-color: #E14434;
        color: $white;
        width: 120px;
    }

    .modal-body {
        height: 650px;
        overflow-y: auto;
    }

    .select-all-checkbox {
        font-weight: 600;
        color: #28a745;

        .custom-control-label {
            cursor: pointer;
            font-size: 12px;
        }

        .custom-control-input:checked~.custom-control-label::before {
            background-color: #28a745;
            border-color: #28a745;
        }
    }

    .column-list {
        overflow-y: auto;
    }

    .column-item {
        padding: 8px 0;
        border-bottom: 1px solid #e0e0e0;

        &:last-child {
            border-bottom: none;
        }
    }

    .column-checkbox {
        width: 100%;

        .custom-control-label {
            font-weight: 500;
            cursor: pointer;
        }

        &.disabled {
            opacity: 0.6;

            .custom-control-label {
                cursor: not-allowed;
                color: #6c757d;
            }
        }
    }

    .column-checkbox-label {
        font-size: 16px;
    }

    .no-result {
        text-align: center;
        padding: 20px;
        color: #6c757d;
    }
}

#modal-cf {
    .btn-cancel {
        background-color: #656b7f !important;
        color: $white;

        &:hover {
            opacity: 0.6;
        }
    }

    .btn-apply {
        background-color: #8A0000 !important;
        color: $white;

        &:hover {
            opacity: 0.6;
        }
    }
}

.b-overlay {
    z-index: 1006 !important;
}

.vehicle-dashboard {
    .dashboard-title {
        margin-bottom: 15px;

        h5 {
            margin: 0;
            color: #333;
            font-weight: 600;
            font-size: 18px;
        }
    }

    .dashboard-cards {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
    }

    .dashboard-card {
        display: flex;
        align-items: center;
        padding: 15px 20px;
        background-color: #ffffff;
        border-radius: 8px;
        border: 1px solid #e9ecef;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        min-width: 180px;
        transition: all 0.3s ease;
        cursor: pointer;

        &:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        &.warning {
            border-left: 4px solid #ffc107;

            .card-icon {
                color: #ffc107;
            }

            &.active {
                background-color: #fff3cd;
                border-color: #ffc107;
                box-shadow: 0 4px 8px rgba(255, 193, 7, 0.3);
                transform: translateY(-2px);
            }
        }

        &.info {
            border-left: 4px solid #17a2b8;

            .card-icon {
                color: #17a2b8;
            }

            &.active {
                background-color: #d1ecf1;
                border-color: #17a2b8;
                box-shadow: 0 4px 8px rgba(23, 162, 184, 0.3);
                transform: translateY(-2px);
            }
        }

        .card-icon {
            font-size: 24px;
            color: #6c757d;
            margin-right: 15px;
            width: 40px;
            text-align: center;
        }

        .card-content {
            flex: 1;

            .card-number {
                font-size: 24px;
                font-weight: bold;
                color: #333;
                line-height: 1;
                margin-bottom: 5px;
            }

            .card-label {
                font-size: 12px;
                color: #6c757d;
                font-weight: 500;
                line-height: 1;
            }
        }
    }
}

@media (max-width: 768px) {
    .vehicle-master__dashboard-actions {
        flex-direction: column;
        gap: 20px;
    }

    .vehicle-dashboard .dashboard-cards {
        justify-content: center;
    }

    .vehicle-dashboard .dashboard-card {
        min-width: 150px;
        flex: 1;
    }
}

.vehicle-dashboard {
  .dashboard-title {
    margin-bottom: 15px;

    h5 {
      margin: 0;
      color: #333;
      font-weight: 600;
      font-size: 18px;
    }
  }

  .dashboard-cards {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
  }

  .dashboard-card {
    display: flex;
    align-items: center;
    padding: 15px 20px;
    background-color: #ffffff;
    border-radius: 8px;
    border: 1px solid #e9ecef;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    min-width: 180px;
    transition: all 0.3s ease;
    cursor: pointer;

    &:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    &.warning {
      border-left: 4px solid #ffc107;

      .card-icon {
        color: #ffc107;
      }

      &.active {
        background-color: #fff3cd;
        border-color: #ffc107;
        box-shadow: 0 4px 8px rgba(255, 193, 7, 0.3);
        transform: translateY(-2px);
      }
    }

    &.info {
      border-left: 4px solid #17a2b8;

      .card-icon {
        color: #17a2b8;
      }

      &.active {
        background-color: #d1ecf1;
        border-color: #17a2b8;
        box-shadow: 0 4px 8px rgba(23, 162, 184, 0.3);
        transform: translateY(-2px);
      }
    }

    .card-icon {
      font-size: 24px;
      color: #6c757d;
      margin-right: 15px;
      width: 40px;
      text-align: center;
    }

    .card-content {
      flex: 1;

      .card-number {
        font-size: 24px;
        font-weight: bold;
        color: #333;
        line-height: 1;
        margin-bottom: 5px;
      }

      .card-label {
        font-size: 12px;
        color: #6c757d;
        font-weight: 500;
        line-height: 1;
      }
    }
  }
}

@media (max-width: 768px) {
  .vehicle-master__dashboard-actions {
    flex-direction: column;
    gap: 20px;
  }

  .vehicle-dashboard .dashboard-cards {
    justify-content: center;
  }

  .vehicle-dashboard .dashboard-card {
    min-width: 150px;
    flex: 1;
  }
}

.multiselect {
  padding-top: 0px !important;
  padding-bottom: 0px !important;
}

.btn-department-query-type {
  background-color: #6c757d !important;
  color: $white !important;
  border: none !important;
  border-radius: 4px !important;
  padding: 5px 10px !important;
  font-weight: normal !important;
  text-decoration: none !important;
  display: inline-block !important;
  cursor: pointer !important;
  transition: all 0.2s ease-in-out !important;
  opacity: 0.7 !important;

  &:hover {
    opacity: 0.9 !important;
    background-color: #6c757d !important;
    color: $white !important;
    text-decoration: none !important;
  }

  &:focus {
    box-shadow: none !important;
    outline: none !important;
  }
}

.btn-department-query-type.active {
  background-color: #0f0448 !important;
  opacity: 1 !important;
  box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.8), 0 0 0 4px currentColor !important;
  transform: scale(1.05) !important;
  transition: all 0.2s ease-in-out !important;
  z-index: 1 !important;

  &:hover {
    background-color: #0f0448 !important;
    opacity: 0.8 !important;
  }
}

.btn-department-query-type.active:nth-child(1) {
  background-color: #0f0448 !important;
  box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.8), 0 0 0 4px #0f0448 !important;
}

.btn-department-query-type.active:nth-child(2) {
  background-color: #640D5F !important;
  box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.8), 0 0 0 4px #640D5F !important;
}

.btn-department-query-type.active:nth-child(3) {
  background-color: #7D5A50 !important;
  box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.8), 0 0 0 4px #7D5A50 !important;
}

.selected-departments-info {
  padding: 8px 12px;
  background-color: #e3f2fd;
  border: 1px solid #2196f3;
  border-radius: 4px;
  margin-bottom: 10px;

  .text-info {
    color: #1976d2 !important;
    font-weight: 500;
    font-size: 14px;
  }
}

.my-input-group {
  display: flex !important;
  flex-wrap: nowrap !important;
  flex-direction: row !important;
}

.status-filter-department-id:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.multiselect--disabled {
  background-color: #e9ecef !important;
  opacity: 0.65 !important;
  cursor: not-allowed !important;
}

.vehicle-dashboard {
  .dashboard-title {
    margin-bottom: 15px;

    h5 {
      margin: 0;
      color: #333;
      font-weight: 600;
      font-size: 18px;
    }
  }

  .dashboard-cards {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
  }

  .dashboard-card {
    display: flex;
    align-items: center;
    padding: 15px 20px;
    background-color: #ffffff;
    border-radius: 8px;
    border: 1px solid #e9ecef;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    min-width: 180px;
    transition: all 0.3s ease;
    cursor: pointer;

    &:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    &.warning {
      border-left: 4px solid #ffc107;

      .card-icon {
        color: #ffc107;
      }

      &.active {
        background-color: #fff3cd;
        border-color: #ffc107;
        box-shadow: 0 4px 8px rgba(255, 193, 7, 0.3);
        transform: translateY(-2px);
      }
    }

    &.info {
      border-left: 4px solid #17a2b8;

      .card-icon {
        color: #17a2b8;
      }

      &.active {
        background-color: #d1ecf1;
        border-color: #17a2b8;
        box-shadow: 0 4px 8px rgba(23, 162, 184, 0.3);
        transform: translateY(-2px);
      }
    }

    .card-icon {
      font-size: 24px;
      color: #6c757d;
      margin-right: 15px;
      width: 40px;
      text-align: center;
    }

    .card-content {
      flex: 1;

      .card-number {
        font-size: 24px;
        font-weight: bold;
        color: #333;
        line-height: 1;
        margin-bottom: 5px;
      }

      .card-label {
        font-size: 12px;
        color: #6c757d;
        font-weight: 500;
        line-height: 1;
      }
    }
  }
}

@media (max-width: 768px) {
  .vehicle-master__dashboard-actions {
    flex-direction: column;
    gap: 20px;
  }

  .vehicle-dashboard .dashboard-cards {
    justify-content: center;
  }

  .vehicle-dashboard .dashboard-card {
    min-width: 150px;
    flex: 1;
  }
}
</style>
