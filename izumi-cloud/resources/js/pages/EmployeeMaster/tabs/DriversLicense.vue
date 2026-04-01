<template>
	<b-overlay
		:show="overlay.show"
		:variant="overlay.variant"
		:opacity="overlay.opacity"
		:blur="overlay.blur"
		:rounded="overlay.sm"
		:style="`${overlay.show ? `height: 600px;` : ``}`"
	>
		<!-- Template overlay -->
		<template #overlay>
			<div class="text-center">
				<b-icon icon="arrow-clockwise" font-scale="3" animation="spin" />
				<p class="text-loading">{{ $t('PLEASE_WAIT') }}</p>
			</div>
		</template>

		<div class="worker-basic-info">
			<div class="title_block">
				<div class="title_left">
					<vTitle>
						{{ $t('INITIAL_DRIVER_COURSE') }}
					</vTitle>
				</div>
				<div class="title_right">
					<!-- <b-button variant="primary" size="sm">{{ $t('EDIT') }}</b-button> -->
					<!-- <vButton
						:class="'btn-summit-filter'"
						@click.native="handleUpdate()"
						>
						{{ disableInput_surface ? $t('EDIT') : $t('BUTTON.SAVE') }}
						</vButton> -->
					<vButton
						:class="'btn-summit-filter'"
						@click.native="handleShowModalPDF"
					>
						<i class="far fa-upload icon_upload" />
						{{ $t('EDIT') }}
					</vButton>
				</div>
			</div>
			<div class="container_section">
				<div class="section_date">
					<b-row>
						<b-col cols="12">
							<p>{{ $t('LAST_UPDATE') }}</p>
						</b-col>
						<b-col cols="12">
							<b-input v-model="date_upload" type="date" disabled />
						</b-col>
					</b-row>
				</div>
				<div class="section_update_document">
					<b-row>
						<b-col cols="6">
							<p>{{ $t('FRONT') }}</p>
							<div class="display_pdf">
								<iframe
									v-if="pdfUrl_surface"
									:src="pdfUrl_surface + '#toolbar=0&navpanes=0&zoom=page-width'"
									frameborder="0"
									class="pdf-iframe"
								/>
							</div>
							<vButton
								:class="'btn-dowload'"
								@click.native="downloadFromUrl(selectedSurfaceFile)"
							>
								<i class="far fa-download icon-history" />
								{{ $t('DATA_LIST_DETAIL_DOWNLOAD') }}
							</vButton>
						</b-col>
						<b-col cols="6">
							<p>{{ $t('BACK') }}</p>
							<div class="display_pdf">
								<iframe
									v-if="pdfUrl_back"
									:src="pdfUrl_back + '#toolbar=0&navpanes=0&zoom=page-width'"
									frameborder="0"
									class="pdf-iframe"
								/>
							</div>
							<vButton
								:class="'btn-dowload'"
								@click.native="downloadFromUrl(selectedBackFile)"
							>
								<i class="far fa-download icon-history" />
								{{ $t('DATA_LIST_DETAIL_DOWNLOAD') }}
							</vButton>
						</b-col>
					</b-row>
				</div>
			</div>

			<div class="history_section" :style="`${overlay.show ? `display: none;` : ``}`">
				<div class="title_history">
					<vTitle>
						<i class="far fa-clock icon_history" />
						{{ $t('EDIT_HISTORY') }}
					</vTitle>
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
						<template #cell(file_name)="scope">
							<div class="d-flex flex-column">
								{{ scope.item.file_name }}
							</div>
							<div class="d-flex flex-column">
								{{ scope.item.file_name_back }}
							</div>
						</template>
						<template #cell(operation)="scope">
							<div class="d-flex">
								<b-button class="modal-btn btn-cancel" @click="onClickDisplay(scope)">
									<i class="far fa-eye" />
								</b-button>
								<b-button class="modal-btn btn-cancel ml-2" @click="handleDowloadHistory(scope)">
									<i class="far fa-download" />
								</b-button>
								<b-button class="modal-btn btn-cancel ml-2" @click="handleShowModalDelete(scope)">
									<i class="far fa-trash" />
								</b-button>
							</div>
						</template>

						<template #empty>
							<span>{{ $t('TABLE_EMPTY') }}</span>
						</template>
					</b-table>
				</div>

				<!-- Content for update history can be added here -->

			</div>

			<b-row>
				<b-col cols="6">
					<b-button class="button-return-to-list" @click="handleButtonReturnToListClicked()">
						<span>戻る</span>
					</b-button>
				</b-col>
			</b-row>

			<ModalViewPDF
				:pdf-title="$t('DRIVER_LICENSE_PDF_PREVIEW')"
				:pdf-url="license_url"
				:pdf-url-back="license_url_back"
				:is-show-modal.sync="isShowLiscense"
			/>

			<b-modal
				id="modal-delete-driver"
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
				id="modal-add-pdf"
				v-model="modalAddPDF"
				static
				size="lg"
				hide-footer
				no-close-on-esc
				no-close-on-backdrop
			>
				<template #modal-title>
					<div class="modal-title">運転免許証アップロード</div>
					<div class="description-title">表面と裏面のPDFファイルをアップロードしてください。</div>
				</template>
				<b-row class="justify-content-center">
					<b-col cols="8">
						<p>{{ $t('FRONT') }}</p>
						<div
							class="update_first"
							:class="{ 'update_first--dragging': isDraggingSurface }"
							@dragover.prevent
							@dragenter.prevent="onDragEnterSurface"
							@dragleave.prevent="onDragLeaveSurface"
							@drop.prevent="onDropSurface"
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

				<b-row class="justify-content-center">
					<b-col cols="8" class="mt-3">
						<p>{{ $t('BACK') }}</p>
						<div
							class="update_first"
							:class="{ 'update_first--dragging': isDraggingBack }"
							@dragover.prevent
							@dragenter.prevent="onDragEnterBack"
							@dragleave.prevent="onDragLeaveBack"
							@drop.prevent="onDropBack"
						>
							<input
								id="fileUpload_2"
								ref="selectFileInput_2"
								type="file"
								accept=".pdf"
								name="File Upload"
								style="display: none;"
								@change="getFilePDFInput_last"
							>
							<i class="far fa-upload icon_upload" />
							<div class="content_upload">クリックしてファイルを選択、またはドラッグ&ドロップ</div>
							<div class="content_or">PDF形式のみ対応</div>
							<vButton
								:class="'btn-history'"
								@click.native="triggerSelectPDFLast()"
							>
								{{ $t('SELECT_FILE') }}
							</vButton>
							<div class="dropzone__file-name">{{ fileNameCSVInput_2 }}</div>
						</div>
					</b-col>
				</b-row>

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
							@click.native="handleUpdate()"
						>
							{{ $t('BUTTON.SAVE') }}
						</vButton>
					</b-col>
				</b-row>
			</b-modal>

		</div>
	</b-overlay>
</template>

<script>
// import DatePicker from 'vue2-datepicker';
import vTitle from '@/components/atoms/vTitle.vue';
import vButton from '@/components/atoms/vButton';
import ModalViewPDF from '@/components/template/ModalViewPDF.vue';
import { postPDF, updateDriverLicense, deleteDriverLicensePDF } from '@/api/modules/employeeMaster';

import 'vue2-datepicker/index.css';

const API_URL = {
    urlUploadData: '/employee/upload-file',
    urlUpdate: '/employee/driver-license',
    urlDelete: '/employee/delete-driver-license',
};

export default {
    name: 'DriversLicense',
    components: {
        vTitle,
        vButton,
        ModalViewPDF,
        // DatePicker,
    },
    props: {
        data: {
            type: Object,
            required: true,
        },
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
            date_upload: '',
            modalAddPDF: false,
            isShowLiscense: false,
            license_url: '',
            license_url_back: '',
            fileNameCSVInput: null,
            id_surface: null,
            id_back: null,
            fileNameCSVInput_2: null,
            file_upload: null,
            pdfUrl_surface: null,
            pdfUrl_back: null,

            disableInput_surface: true,
            disableInput_back: true,

            selectedSurfaceFile: null,
            selectedBackFile: null,
            isDraggingSurface: false,
            isDraggingBack: false,

            isShowModalDelete: false,
            fileNameDelete: '',
            ID_delete: '',

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
            items: [
                // {
                //     date: '',
                //     registered_by: '',
                //     file_name: '',
                //     operation: '',
                //     file_name_back: '',
                //     file_url_surface: '',
                //     file_url_back: '',
                // },
            ],
        };
    },
    computed: {

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
                // {
                //     key: 'file_name_back',
                //     sortable: false,
                //     label: this.$t('FILE_NAME_BACK'),
                //     tdClass: 'td-affilitation-base',
                //     thClass: 'th-support-base',
                // },
                {
                    key: 'operation',
                    sortable: false,
                    label: this.$t('OPERATION'),
                    tdClass: 'td-affilitation-base',
                    thClass: 'th-support-base',
                },
            ];
        },

        lang() {
            return this.$store.getters.language;
        },
    },
    watch: {
        'data.driver_licenses': {
            handler(newVal) {
                if (newVal && Array.isArray(newVal)) {
                    this.updateItemsFromData();
                }
            },
            deep: true,
            immediate: false,
        },
    },
    created() {
        console.log('data in DriversLicense:', this.data);
        this.updateItemsFromData();
    },

    methods: {
        handleCloseModalDelete() {
            this.isShowModalDelete = false;
            this.fileNameDelete = '';
        },
        handleShowModalDelete(scope) {
            this.isShowModalDelete = true;
            this.fileNameDelete = scope.item.file_name;
            this.ID_delete = scope.item.id;
        },
        async handleDelete() {
            try {
                const res = await deleteDriverLicensePDF(`${API_URL.urlDelete}/${this.ID_delete}`);

                if (res['code'] === 200) {
                    this.$toast.success({
                        title: '成功',
                        content: this.$t('MESSAGE_DELETE_FILE_SUCCESS'),
                    });
                    this.handleCloseModalDelete();
                    this.$emit('update-success');
                    this.updateItemsFromData();
                } else {
                    this.$toast.danger({
                        content: res.message,
                    });
                }
            } catch (error) {
                console.error('Unexpected error:', error);
            }
        },
        updateItemsFromData() {
            if (!this.data?.driver_licenses || !Array.isArray(this.data.driver_licenses)) {
                this.items = [];
                this.date_upload = '';
                return;
            }

            this.items = this.data.driver_licenses[0].employee_driver_licenses_history.map((item) => {
                return {
                    id: item?.id,
                    date: item?.updated_at,
                    registered_by: item?.user?.name || '',
                    file_name: item?.surface_file?.file_name || '',
                    file_name_back: item?.back_file?.file_name || '',
                    file_url_surface: item?.surface_file?.file_url || '',
                    file_url_back: item?.back_file?.file_url || '',
                    surface_file: item?.surface_file || null,
                    back_file: item?.back_file || null,
                };
            });

            this.pdfUrl_surface = this.data.driver_licenses[0]?.surface_file?.file_url || '';
            this.pdfUrl_back = this.data.driver_licenses[0]?.back_file?.file_url || '';
            this.selectedBackFile = this.data.driver_licenses[0]?.back_file || null;
            this.selectedSurfaceFile = this.data.driver_licenses[0]?.surface_file || null;

            this.date_upload = this.data.driver_licenses.length
                ? this.data.driver_licenses[0].employee_driver_licenses_history[0]?.updated_at?.split(' ')[0] || ''
                : '';
        },

        handleShowModalPDF() {
            this.modalAddPDF = true;
            this.$refs.selectFileInput.value = null;
            this.$refs.selectFileInput_2.value = null;
            this.fileNameCSVInput_2 = null;
            this.fileNameCSVInput = null;
        },

        handleCloseModalPDF() {
            this.modalAddPDF = false;
        },

        onDragEnterSurface(event) {
            event.preventDefault();
            event.stopPropagation();
            this.isDraggingSurface = true;
        },

        handleButtonReturnToListClicked() {
            this.$router.push({ path: '/master-manager/employee-master' });
        },

        onDragLeaveSurface(event) {
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
                this.isDraggingSurface = false;
            }
        },

        onDragEnterBack(event) {
            event.preventDefault();
            event.stopPropagation();
            this.isDraggingBack = true;
        },

        onDragLeaveBack(event) {
            event.preventDefault();
            event.stopPropagation();
            // if (this.disableInput_back) {
            //     return;
            // }

            const rect = event.currentTarget.getBoundingClientRect();
            const { clientX, clientY } = event;

            const isOutside =
                clientX < rect.left ||
                clientX > rect.right ||
                clientY < rect.top ||
                clientY > rect.bottom;

            if (isOutside) {
                this.isDraggingBack = false;
            }
        },

        async onDropSurface(event) {
            event.preventDefault();
            event.stopPropagation();
            this.isDraggingSurface = false;

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

            this.selectedSurfaceFile = file;
            this.fileNameCSVInput = file.name;
            await this.handleUploadPDF(file);
            this.id_surface = this.file_upload;
        },

        async onDropBack(event) {
            event.preventDefault();
            event.stopPropagation();
            this.isDraggingBack = false;

            const files = event.dataTransfer?.files || [];
            if (!files.length) {
                return;
            }

            const file = files[0];

            if (!this.isValidPdf(file)) {
                this.$toast?.danger?.({
                    content: this.$t('MESSAGE_ONLY_PDF_ALLOWED') || 'Only PDF file is allowed',
                });
                return;
            }

            this.fileNameCSVInput_2 = file.name;
            await this.handleUploadPDF(file);
            this.id_back = this.file_upload;
        },

        isValidPdf(file) {
            const isPdfMime = file.type === 'application/pdf';
            const isPdfExt = file.name?.toLowerCase().endsWith('.pdf');
            return isPdfMime || isPdfExt;
        },

        triggerSelectFileInput() {
            this.$refs.selectFileInput.click();
        },
        triggerSelectPDFLast() {
            this.$refs.selectFileInput_2.click();
        },

        async handleDowloadHistory(scope) {
            const { surface_file, back_file } = scope.item;

            let hasFile = false;

            if (surface_file?.file_url) {
                this.downloadFromUrl(surface_file);
                hasFile = true;
            }

            // delay nhẹ chút để trình duyệt “nuốt” kịp 2 download
            if (back_file?.file_url) {
                setTimeout(() => {
                    this.downloadFromUrl(back_file);
                }, 300);
                hasFile = true;
            }

            if (!hasFile) {
                this.$toast?.danger?.({ content: this.$t('NO_FILE_SELECTED') });
            }
        },

        downloadFromUrl(file) {
            if (!file?.file_url) {
                // chỉ toast 1 lần bên ngoài, hoặc log cho debug
                return false;
            }

            const a = document.createElement('a');
            a.href = file.file_url;
            a.download = file.file_name || 'document.pdf';
            document.body.appendChild(a);
            a.click();
            a.remove();
            return true;
        },

        downloadLocalFile(file) {
            if (!file) {
                this.$toast?.danger?.({ content: this.$t('NO_FILE_SELECTED') });
                return;
            }

            const url = URL.createObjectURL(file);
            const a = document.createElement('a');
            a.href = url;
            a.download = file.name || 'document.pdf';
            document.body.appendChild(a);
            a.click();
            a.remove();
            URL.revokeObjectURL(url);
        },

        async handleUpdate() {
            try {
                const params = {
                    employee_id: this.data.id,
                    surface_file_id: this.id_surface,
                    back_file_id: this.id_back,
                };

                const res = await updateDriverLicense(API_URL.urlUpdate, params);

                if (res['code'] === 200) {
                    this.$toast.success({
                        title: '成功',
                        content: this.$t('MESSAGE_DRIVER_LICENSE'),
                    });
                    // this.disableInput_surface = true;
                    // this.disableInput_back = true;
                    this.modalAddPDF = false;
                    // Emit event to parent component to refresh data
                    this.$emit('update-success');
                } else {
                    this.$toast.danger({
                        content: res.message,
                    });
                }
            } catch (error) {
                console.error('Unexpected error:', error);
            }
        },
        async getFilePDFInput_last() {
            const fileInput = document.getElementById('fileUpload_2');
            this.fileNameCSVInput_2 = fileInput.files[0].name;
            this.selectedBackFile = fileInput.files[0];

            console.log('file name last', fileInput.files[0]);
            await this.handleUploadPDF(fileInput.files[0]);
            this.id_back = this.file_upload;
        },
        async getFileCSVInput() {
            const fileInput = document.getElementById('fileUpload');
            this.fileNameCSVInput = fileInput.files[0].name;
            this.selectedSurfaceFile = fileInput.files[0];

            console.log('file name', this.fileNameCSVInput);
            await this.handleUploadPDF(fileInput.files[0]);
            this.id_surface = this.file_upload;
        },
        onClickDisplay(scope) {
            this.isShowLiscense = true;
            this.license_url = scope.item.file_url_surface;
            this.license_url_back = scope.item.file_url_back;
            console.log('Display clicked', scope);
        },

        setLoading(status = true) {
            if ([true, false].includes(status)) {
                this.overlay.show = status;
            }
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
                    // this.$toast.success({
                    //     content: this.$t('MESSAGE_UPLOAD_FILE_SUCCESS'),
                    // });
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
.title_block {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}
.section_update_document{
    margin-top: 20px;
}

.upload-zone-col-left {
  border: 2px dashed #DDDDDD;
}

.upload-zone-col-left:hover {
  cursor: pointer !important;
};

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

.dropzone__file-name {
  font-size: 14px;
  margin-top: 10px;
  color: #333;
  word-break: break-all;
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
    font-weight: 200 !important;
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

</style>
