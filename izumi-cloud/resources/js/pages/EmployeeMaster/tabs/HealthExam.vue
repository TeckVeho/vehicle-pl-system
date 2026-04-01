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

		<div class="worker-basic-info">
			<div class="title_block">
				<div class="title_left">
					<vTitle>
						{{ $t('HEALTH_EXAMINATION_RESULT_NOTIFICATION') }}
					</vTitle>
				</div>
				<div class="title_right">
					<!-- <vButton
						:class="'btn-summit-filter'"
						@click.native="handleUpdateDrivingRecord()"
						>
						{{ disableInput ? $t('EDIT') : $t('BUTTON.SAVE') }}
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
				<!-- <div class="section_type">
					<b-row>
					<b-col cols="12">
					<p>{{ $t('DATE_APTITUDE_TEST') }}</p>

					</b-col>
					<b-col cols="12">
					<b-input v-model="date_visit" type="date" />
					</b-col>
					</b-row>
					</div> -->
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
						<b-col cols="12">
							<p>{{ $t('PDF_FILE') }}</p>
							<div class="display_pdf">
								<iframe
									v-if="pdfUrl"
									:src="pdfUrl + '#toolbar=0&navpanes=0&zoom=page-width'"
									frameborder="0"
									class="pdf-iframe"
								/>
							</div>
							<!-- <div class="update_first">
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
								<div class="content_upload">PDFファイルをドラッグ&ドロップ</div>
								<div class="content_or">または</div>
								<vButton
								:class="'btn-history'"
								@click.native="triggerSelectFileInput()"
								>
								{{ $t('SELECT_FILE') }}
								</vButton>
								<div class="dropzone__file-name">{{ fileNameCSVInput }}</div>
								</div> -->
							<vButton
								:class="'btn-dowload'"
								@click.native="downloadFromUrl(selectedFile)"
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
						<template #cell(operation)="scope">
							<div class="d-flex">
								<b-button class="modal-btn btn-cancel" @click="onClickDisplay(scope)">
									<i class="far fa-eye" />
								</b-button>
								<b-button class="modal-btn btn-cancel ml-2" @click="downloadFromUrl(scope.item.file)">
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
				:pdf-title="$t('MODAL_HEALTH_EXAMINATION_RESULT_NOTIFICATION')"
				:pdf-url="health_exam"
				:is-show-modal.sync="isShowHealthExam"
			/>

			<b-modal
				id="modal-delete-health"
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
					<div class="modal-title">健康診断結果通知書アップロード</div>
					<div class="description-title">PDFファイルを選択してアップロードしてください</div>
				</template>

				<div class="section_type">
					<b-row class="justify-content-center">
						<b-col cols="8">
							<p>{{ $t('DATE_APTITUDE_TEST') }}</p>

						</b-col>
						<b-col cols="8">
							<b-input v-model="date_visit_create" type="date" />
						</b-col>
					</b-row>
				</div>

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
							@click.native="handleCloseModalPDF()"
						>
							{{ $t('CLOSE') }}
						</vButton>
						<vButton
							:class="'btn-summit-filter ml-2'"
							@click.native="handleUpdateDrivingRecord()"
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
import { postPDF, updateHealthExaminationResults, deleteHealthExamPDF } from '@/api/modules/employeeMaster';

import 'vue2-datepicker/index.css';

const API_URL = {
    urlUploadData: '/employee/upload-file',
    urlUpdate: '/employee/health-examination-results',
    urlDelete: '/employee/delete-health-examination-file-history',
};

export default {
    name: 'HealthExam',
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
            date_visit: '',
            date_visit_create: '',
            health_exam: '',
            isShowHealthExam: false,
            fileNameCSVInput: null,
            fileNameCSVInput_2: null,
            isShowModalDelete: false,
            fileNameDelete: '',
            ID_delete: '',

            disableInput: true,
            modalAddPDF: false,
            isDragging: false,
            pdfUrl: '',

            selectedFile: null,
            id_file: null,
            file_upload: {},

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
                {
                },
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

        lang() {
            return this.$store.getters.language;
        },
    },
    watch: {
        'data.health_examination_results': {
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
                const res = await deleteHealthExamPDF(`${API_URL.urlDelete}/${this.ID_delete}`);

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
            if (!this.data?.health_examination_results || !Array.isArray(this.data.health_examination_results)) {
                this.items = [];
                this.date_upload = '';
                return;
            }

            const firstResult = this.data.health_examination_results[0];
            const fileHistory = Array.isArray(firstResult?.file_history) ? firstResult.file_history : [];

            this.items = fileHistory.map((item) => {
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

            const firstHistory = fileHistory[0];
            this.selectedFile = firstHistory?.file || null;
            this.pdfUrl = firstHistory?.file?.file_url || '';

            this.date_upload = firstHistory?.updated_at?.split(' ')[0] || '';
        },
        isValidPdf(file) {
            const isPdfMime = file.type === 'application/pdf';
            const isPdfExt = file.name?.toLowerCase().endsWith('.pdf');
            return isPdfMime || isPdfExt;
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
        handleShowModalPDF() {
            this.modalAddPDF = true;
            this.$refs.selectFileInput.value = null;
            this.fileNameCSVInput = null;
            this.date_visit_create = null;
        },
        handleCloseModalPDF() {
            this.modalAddPDF = false;
        },
        triggerSelectFileInput() {
            this.$refs.selectFileInput.click();
        },

        handleButtonReturnToListClicked() {
            this.$router.push({ path: '/master-manager/employee-master' });
        },
        async getFileCSVInput() {
            const fileInput = document.getElementById('fileUpload');
            this.fileNameCSVInput = fileInput.files[0].name;
            this.selectedFile = fileInput.files[0];
            await this.handleUploadPDF(this.selectedFile);
            this.id_file = this.file_upload;
        },
        onClickDisplay(scope) {
            this.isShowHealthExam = true;
            this.health_exam = scope.item.file_url;
            console.log('Display clicked', scope);
        },
        async handleUpdateDrivingRecord() {
            try {
                const params = {
                    employee_id: this.data.id,
                    file_id: this.id_file,
                    date_of_visit: this.date_visit_create,
                };

                const res = await updateHealthExaminationResults(API_URL.urlUpdate, params);

                if (res['code'] === 200) {
                    this.$toast.success({
                        title: '成功',
                        content: this.$t('MESSAGE_APTITUDE_TEST'),
                    });
                    this.modalAddPDF = false;
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
.section_type{
    margin-bottom: 20px;
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
