<template>
	<b-overlay
		:show="overlay.show"
		:variant="overlay.variant"
		:opacity="overlay.opacity"
		:blur="overlay.blur"
		:rounded="overlay.rounded"
		:style="`${overlay.show ? `height: 600px;` : ``}`"
	>
		<!-- Template overlay -->
		<template #overlay>
			<div class="text-center">
				<b-icon icon="arrow-clockwise" font-scale="3" animation="spin" />
				<p class="text-loading">{{ $t('PLEASE_WAIT') }}</p>
			</div>
		</template>
		<div class="title">
			<vHeaderPage class="employee-master-list__title-header">
				{{ $t('PDF_UPLOAD') }}
			</vHeaderPage>
		</div>

		<div class="worker-basic-info">
			<div class="title_block">
				<div class="title_left">
					<vFilterDeparment
						:list-file-classification="ListLocation"
						@input="handleFilterDeparment"
					/>
				</div>
				<div class="title_right">
					<vButton
						:class="'btn-summit-filter'"
						@click.native="handleShowModalPDF()"
					>
						<i class="far fa-upload icon_upload" />
						{{ $t('BUTTON.UPLOAD') }}
					</vButton>
				</div>
			</div>
			<div class="container_section" />

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
								<!-- <span>表示</span> -->
							</b-button>
							<b-button class="modal-btn btn-cancel ml-2" @click="handleShowModalAllocation(scope)">
								<i class="far fa-file-import" />
								<!-- <span>振り分け</span> -->
							</b-button>
							<b-button class="modal-btn btn-cancel ml-2" @click="handleShowModalDelete(scope)">
								<i class="far fa-trash" />
								<!-- <span>削除</span> -->
							</b-button>
						</div>
					</template>

					<template #empty>
						<span>{{ $t('TABLE_EMPTY') }}</span>
					</template>
				</b-table>
				<b-row>
					<b-col cols="6">
						<b-button class="button-return-to-list" @click="handleButtonReturnToListClicked()">
							<span>戻る</span>
						</b-button>
					</b-col>
				</b-row>
			</div>

			<ModalViewPDF
				:pdf-title="$t('MODAL_HEALTH_EXAMINATION_RESULT_NOTIFICATION')"
				:pdf-url="detail_url_pdf"
				:is-show-modal.sync="isShowPDFView"
			/>

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
						<b-col cols="10" class="mt-3">
							<span>{{ $t('EMLOYEE_BY_DEPARMENT') }}</span>
							<b-form-select
								id="filter-affiliation-base-value"
								v-model="select_an_employee"
								:options="ListEmployeeByDeparment"
								:value-field="'id'"
								:text-field="'name'"
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
				id="modal-upload-pdf"
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
						<div class="title_select_deparment">
							<p>{{ $t('SELECT_LOCATION') }}</p>
							<b-form-select
								id="select-a-location"
								v-model="select_location"
								:options="ListLocation"
								:value-field="'id'"
								:text-field="'department_name'"
							>
								<template #first>
									<b-form-select-option :value="null">
										{{ $t('PLEASE_SELECT') }}
									</b-form-select-option>
								</template>
							</b-form-select>
						</div>
					</b-col>
				</b-row>

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
							@click.native="handleCreatePDF()"
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

			<div v-if="pagination.vTotalRows > 20" class="employee-master-list__pagination">
				<div class="select-per-page text-left">
					<div>
						<label for="per-page">1ページ毎の表示数</label>
					</div>
					<b-form-select
						id="per-page"
						v-model="pagination.vPerPage"
						:options="isPerPage.options"
						size="sm"
						@change="handleChangePerPage()"
					/>
				</div>

				<div class="show-pagination">
					<vPagination
						:aria-controls="'table-employee-master-list'"
						:current-page="pagination.vCurrentPage"
						:per-page="pagination.vPerPage"
						:total-rows="pagination.vTotalRows"
						:next-class="'next'"
						:prev-class="'prev'"
						@currentPageChange="getCurrentPage"
					/>
				</div>
			</div>
		</div>
	</b-overlay>
</template>

<script>
import vButton from '@/components/atoms/vButton';
import ModalViewPDF from '@/components/template/ModalViewPDF.vue';
import vHeaderPage from '@/components/atoms/vHeaderPage';
import vPagination from '@/components/atoms/vPagination';
import vFilterDeparment from '@/components/organisms/FilterDeparment';
import { getListEmployeePDF, postEmployeePDF, deleteEmployeePDF, getEmployeeByDepartment, postDriverLicense, postDrivingRecord, postAptitude, postHealthExamination } from '@/api/modules/employeePDF';
import { getListDepartment, postPDF } from '@/api/modules/employeeMaster';

import 'vue2-datepicker/index.css';

const API_URL = {
    urlEmployeePDF: '/employee-pdf-storage',
    urlPostEmployeePDF: '/employee-pdf-storage',
    urlDeleteEmployeePDF: '/employee-pdf-storage',
    urlPostDriveLiscense: '/employee-pdf-storage/driver-license',
    urlPostDrivingRecord: '/employee-pdf-storage/driving-record-certificate',
    urlPostAptitude: '/employee-pdf-storage/aptitude-assessment-form',
    urlHealth: '/employee-pdf-storage/health-examination-results',
    getListDepartment: '/department/list-all',
    urlUploadData: '/employee/upload-file',
    getListEmployeeByDepartment: '/employee/get-employee-by-department-id',
};

export default {
    name: 'UploadPDF',
    components: {
        vHeaderPage,
        vFilterDeparment,
        vButton,
        ModalViewPDF,
        vPagination,
        // DatePicker,
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
            detail_url_pdf: '',
            isShowPDFView: false,
            modalAddPDF: false,

            select_an_employee: null,
            ListEmployeeByDeparment: [],

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
            select_location: null,
            ListLocation: [

            ],
            date_visit_aptitude_test: '',
            date_visit_health_exam: '',

            select_file_Driving: null,
            ListTypeFileDriving: [
                { id: 1, type: '表面' },
                { id: 2, type: '裏面' },
            ],

            items: [],
            isDragging: false,
            fileNameCSVInput: null,
            selectedFile: null,
            isShowModalUploadPDF: false,
            id_file: null,
            file_upload: null,
            file_name_pdf: '',

            isShowModalDelete: false,
            fileNameDelete: '',
            ID_delete: null,
            dataPDF: {},

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
                vCurrentPage: 1,
                vPerPage: 20,
                vTotalRows: 0,
            },
            isPerPage: {
                value: 20,
                options: [
                    { value: 20, text: '20' },
                    { value: 50, text: '50' },
                    { value: 100, text: '100' },
                    { value: 250, text: '250' },
                    { value: 500, text: '500' },
                ],
            },
        };
    },
    computed: {

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
                    key: 'registered_by',
                    sortable: false,
                    label: this.$t('REGISTER_UPLOAD'),
                    tdClass: 'td-affilitation-base',
                    thClass: 'th-affilitation-base',
                },
                {
                    key: 'location',
                    sortable: false,
                    label: this.$t('LOCATION'),
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

        lang() {
            return this.$store.getters.language;
        },
    },
    watch: {
    },
    created() {
        this.handleGetListDepartment();
        this.getListFileUpload();
    },

    methods: {
        handleFilterDeparment(id) {
            this.getListFileUpload(id);
        },
        async handleFileClassification() {
            this.overlay.show = true;
            switch (this.select_file_classification) {
            case 1:
                this.handlePostDriverLiscense();
                break;
            case 2:
                this.handlePostDrivingRecord();
                break;
            case 3:
                this.handlePostAptitude();
                break;
            case 4:
                this.handlePostHealthExam();
                break;
            default:
                break;
            }
            this.modalAddPDF = false;
            this.overlay.show = false;
        },
        async handlePostHealthExam() {
            this.overlay.show = true;
            try {
                const params = {
                    employee_pdf_storage_id: this.dataPDF.id,
                    employee_id: this.select_an_employee,
                    file_id: this.dataPDF.file.id,
                    date_of_visit: this.date_visit_health_exam,
                };
                const response = await postHealthExamination(API_URL.urlHealth, params);
                if (response && response.code === 200) {
                    this.$toast.success({
                        title: '成功',
                        content: this.$t('MESSAGE_HEALTH_EXAMINATION_RESULT_NOTIFICATION'),
                    });
                    this.$emit('update-success');
                    this.getListFileUpload();
                    this.isShowModalUploadPDF = false;
                }
            } catch (error) {
                console.log(error);
            }
            this.overlay.show = false;
        },
        async handlePostAptitude() {
            this.overlay.show = true;
            try {
                const params = {
                    employee_pdf_storage_id: this.dataPDF.id,
                    employee_id: this.select_an_employee,
                    file_id: this.dataPDF.file.id,
                    date_of_visit: this.date_visit_aptitude_test,
                    type: this.select_type_aptitudeTest,
                };
                const response = await postAptitude(API_URL.urlPostAptitude, params);
                if (response && response.code === 200) {
                    this.$toast.success({
                        title: '成功',
                        content: this.$t('MESSAGE_APTITUDE_TEST'),
                    });
                    this.$emit('update-success');
                    this.getListFileUpload();
                    this.isShowModalUploadPDF = false;
                }
            } catch (error) {
                console.log(error);
            }
            this.overlay.show = false;
        },
        async handlePostDrivingRecord() {
            this.overlay.show = true;
            try {
                const params = {
                    employee_pdf_storage_id: this.dataPDF.id,
                    employee_id: this.select_an_employee,
                    file_id: this.dataPDF.file.id,
                };
                const response = await postDrivingRecord(API_URL.urlPostDrivingRecord, params);
                if (response && response.code === 200) {
                    this.$toast.success({
                        title: '成功',
                        content: this.$t('MESSAGE_DRIVING_RECORD'),
                    });
                    this.$emit('update-success');
                    this.getListFileUpload();
                    this.isShowModalUploadPDF = false;
                }
            } catch (error) {
                console.log(error);
            }
            this.overlay.show = false;
        },
        async handlePostDriverLiscense() {
            this.overlay.show = true;
            try {
                const params = {
                    employee_pdf_storage_id: this.dataPDF.id,
                    employee_id: this.select_an_employee,
                };
                if (this.select_file_Driving === 1) {
                    params.surface_file_id = this.dataPDF.file.id;
                    params.back_file_id = null;
                } else if (this.select_file_Driving === 2) {
                    params.back_file_id = this.dataPDF.file.id;
                    params.surface_file_id = null;
                }
                const response = await postDriverLicense(API_URL.urlPostDriveLiscense, params);
                if (response && response.code === 200) {
                    this.$toast.success({
                        title: '成功',
                        content: this.$t('MESSAGE_DRIVER_LICENSE'),
                    });
                    this.$emit('update-success');
                    this.getListFileUpload();
                    this.isShowModalUploadPDF = false;
                }
            } catch (error) {
                console.log(error);
            }
            this.overlay.show = false;
        },
        async handleDelete() {
            this.overlay.show = true;
            try {
                const response = await deleteEmployeePDF(`${API_URL.urlDeleteEmployeePDF}/${this.ID_delete}`);
                if (response && response.code === 200) {
                    this.$toast.success({
                        title: '成功',
                        content: this.$t('MESSAGE_DELETE_FILE_SUCCESS'),
                    });
                    this.$emit('update-success');
                    this.handleCloseModalDelete();
                    this.getListFileUpload();
                } else {
                    this.overlay.show = false;
                }
            } catch (error) {
                console.log(error);
            }
            this.overlay.show = false;
        },
        async handleCreatePDF() {
            this.overlay.show = true;
            try {
                const params = {
                    file_id: this.id_file,
                    department_id: this.select_location,
                };
                const response = await postEmployeePDF(API_URL.urlPostEmployeePDF, params);
                if (response && response.code === 200) {
                    this.$toast.success({
                        title: '成功',
                        content: this.$t('MESSAGE_CREATE_FILE_SUCCESS'),
                    });
                    this.getListFileUpload();
                    this.isShowModalUploadPDF = false;
                }
            } catch (error) {
                console.log(error);
            }
            this.overlay.show = false;
        },
        async handleUploadPDF(file) {
            // this.setLoading(true);
            try {
                const formData = new FormData();
                formData.append('file', file);

                const res = await postPDF(API_URL.urlUploadData, formData);

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
        async handleGetListDepartment() {
            this.overlay.show = true;
            try {
                const { code, data } = await getListDepartment(API_URL.getListDepartment);

                if (code === 200) {
                    this.ListLocation = data;
                } else {
                    this.ListLocation.length = 0;
                }
            } catch (error) {
                console.log(error);
            }
            this.overlay.show = false;
        },
        async handleGetListEmployeeByDepartment(id) {
            this.overlay.show = true;
            try {
                const { code, data } = await getEmployeeByDepartment(`${API_URL.getListEmployeeByDepartment}/${id}`);
                if (code === 200) {
                    this.ListEmployeeByDeparment = data;
                } else {
                    this.ListEmployeeByDeparment.length = 0;
                }
            } catch (error) {
                console.log(error);
            }
            this.overlay.show = false;
        },
        async getListFileUpload(deparment = '') {
            this.overlay.show = true;
            try {
                const params = {
                    page: this.pagination.vCurrentPage,
                    per_page: this.pagination.vPerPage,
                    sort_by: '',
                    sort_type: '',
                    user_id: '',
                    department_id: deparment,
                };
                const response = await getListEmployeePDF(API_URL.urlEmployeePDF, params);
                if (response && response.code === 200) {
                    this.items = response.data.data.map((item) => {
                        return {
                            file_name: item?.file?.file_name || '',
                            date_upload: item?.file?.created_at,
                            registered_by: item?.user?.name || '',
                            location: item?.department?.name,
                            id: item?.id,
                            file_url: item?.file?.file_url || '',
                            file: item?.file || null,
                            department_id: item.department_id,
                        };
                    });
                    this.pagination.vTotalRows = response.data.total;
                    this.pagination.vCurrentPage = response.data.current_page;
                    this.pagination.vPerPage = response.data.per_page;
                }
            } catch (error) {
                console.log(error);
            }
            this.overlay.show = false;
        },
        async handleChangePerPage() {
            await this.$store.dispatch('pagination/setEmployeeUpload', 1);
            await this.$store.dispatch('pagination/setEmployeeUploadPerPage', this.pagination.vPerPage);
            this.getListFileUpload();
        },
        async getCurrentPage(value) {
            if (value) {
                this.pagination.vCurrentPage = value;
                await this.$store.dispatch('pagination/setEmployeeUpload', value);
                this.getListFileUpload();
            }
        },
        handleShowModalPDF() {
            this.isShowModalUploadPDF = true;
            this.select_location = null;
            this.fileNameCSVInput = null;
            this.$refs.selectFileInput.value = null;
        },
        onClickDisplay(scope) {
            this.isShowPDFView = true;
            this.detail_url_pdf = scope.item.file_url;
        },
        async handleShowModalAllocation(scope) {
            this.date_visit_aptitude_test = '';
            this.select_type_aptitudeTest = null;
            this.date_visit_health_exam = '';
            this.select_an_employee = null;
            this.select_file_classification = null;
            this.file_name_pdf = scope.item.file_name;
            console.log('scope.itemdepartment_id', scope.item);
            await this.handleGetListEmployeeByDepartment(scope.item.department_id);
            this.modalAddPDF = true;
            this.dataPDF = scope.item;
        },
        handleShowModalDelete(scope) {
            this.isShowModalDelete = true;
            this.fileNameDelete = scope.item.file_name;
            this.ID_delete = scope.item.id;
        },

        handleCloseModalPDF() {
            this.modalAddPDF = false;
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
        handleCloseModalUploadPDF() {
            this.isShowModalUploadPDF = false;
        },
        handleCloseModalDelete() {
            this.isShowModalDelete = false;
            this.fileNameDelete = '';
        },
        handleButtonReturnToListClicked() {
            this.$router.push({ path: '/master-manager/employee-master' });
        },
    },
};
</script>

<style lang="scss" scoped>
@import '@/scss/variables';
.title {
    font-size: 30px;
    font-weight: 600;
    margin-bottom: 16px;
}
.section_type{
    margin-bottom: 20px;
}
.employee-master-list__title-header {
    margin-bottom: 20px;
}
.title_block {
    display: flex;
    justify-content: flex-start;
    align-items: flex-end;
    margin-bottom: 24px;
    background-color: #fbf7f7;
    padding: 20px;
}
.title_left {
    margin-right: 30px;
    width: 30%;
}
.section_update_document{
    margin-top: 20px;
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

.modal-btn {
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
  border: none;
  display: block;
}
.modal-title {
    font-size: 25px;
    font-weight: 500;
    margin-bottom: 16px;
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

.description-title {
    font-size: 13px;
    font-weight: 300;
    margin-bottom: 16px;
}
.dropzone__file-name {
  font-size: 14px;
  margin-top: 10px;
  color: #333;
  word-break: break-all;
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

.history_section {
    margin-top: 20px;
}

.icon_history {
    font-size: 20px;
    margin-right: 10px;
}
.icon-history {
    margin-right: 8px;
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

.title_history {
    display: flex;
    justify-content: left;
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

            .select-per-page {
				#per-page {
					width: 100px;
				}
			}

			.show-pagination {
				display: flex;
				justify-content: center;
			}

</style>
